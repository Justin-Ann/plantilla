# app.py
from flask import Flask, request, jsonify, send_file, redirect, render_template
import os
import pandas as pd
from werkzeug.utils import secure_filename
# Modified import to avoid circular import issues
import sys
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from data_processor import process_raw_data
import mysql.connector
from mysql.connector import Error
from datetime import datetime
from flask_cors import CORS    

app = Flask(__name__, static_folder='../static', template_folder='../templates')
app.config['UPLOAD_FOLDER'] = '../uploads'
app.config['ALLOWED_EXTENSIONS'] = {'xlsx', 'xls', 'csv'}
CORS(app)
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

ALLOWED_EXTENSIONS = {'csv', 'xls', 'xlsx'}

def allowed_file(filename):
    return '.' in filename and \
           filename.rsplit('.', 1)[1].lower() in app.config['ALLOWED_EXTENSIONS']

'''def connect_to_database():
    try:
        connection = mysql.connector.connect(
            host='localhost',
            database='plantilla_management',
            user='tequila',
            password='0225'
        )
        if connection.is_connected():
            print("Connected to database successfully")
            return connection
    except Error as e:
        print(f"Error connecting to MySQL: {e}")
        return None'''

def connect_to_database():
    try:
        connection = mysql.connector.connect(
            host='localhost',
            database='plantilla_management',
            user='tequila',
            password='0225'
        )
        if connection.is_connected():
            print("Connected to database successfully")
            
            # Check if 'is_latest' column exists in 'raw_data' table
            cursor = connection.cursor()
            cursor.execute("""
                SELECT COUNT(*) 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_NAME = 'raw_data' AND COLUMN_NAME = 'is_latest'
            """)
            column_exists = cursor.fetchone()[0]

            # If 'is_latest' column does not exist, add it
            if not column_exists:
                cursor.execute("ALTER TABLE raw_data ADD COLUMN is_latest BOOLEAN DEFAULT FALSE")
                print("Added 'is_latest' column to 'raw_data' table.")
            
            cursor.close()
            return connection

    except Error as e:
        print(f"Error connecting to MySQL: {e}")
        return None


# Root route to handle the homepage
@app.route('/')
def home():
    return redirect('/index.html')

# Route to serve static HTML files
@app.route('/<path:path>')
def serve_static(path):
    # This will serve files from the static folder (HTML, CSS, JS)
    return app.send_static_file(path)

'''@app.route('/upload', methods=['POST'])
def upload_file():
    if 'file' not in request.files:
        return jsonify({"error": "No file uploaded"}), 400
    
    file = request.files['file']
    
    if file.filename == '':
        return jsonify({"error": "No selected file"}), 400
    
    if not allowed_file(file.filename):
        return jsonify({"error": "Invalid file format. Only CSV, XLS, and XLSX are allowed."}), 400

    filename = secure_filename(file.filename)

    try:
        if filename.endswith('.csv'):
            df = pd.read_csv(file)
        else:
            df = pd.read_excel(file)

        processed_data = process_raw_data(df)
        return jsonify(processed_data)

    except Exception as e:
        return jsonify({"error": str(e)}), 500'''

@app.route('/upload', methods=['POST'])
def upload_file():
    if 'file' not in request.files:
        return jsonify({"error": "No file uploaded"}), 400
    
    file = request.files['file']
    
    if file.filename == '':
        return jsonify({"error": "No selected file"}), 400
    
    if not allowed_file(file.filename):
        return jsonify({"error": "Invalid file format. Only CSV, XLS, and XLSX are allowed."}), 400

    filename = secure_filename(file.filename)

    try:
        if filename.endswith('.csv'):
            df = pd.read_csv(file)
        else:
            df = pd.read_excel(file)

        # 🔴 FIX: Replace NaN values with None (NULL in MySQL)
        df = df.where(pd.notna(df), None)

        # 🔴 FIX: Convert all columns to string to avoid accidental NaN issues
        df = df.astype(str)

        processed_data = process_raw_data(df)
        return jsonify(processed_data)

    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route('/api/upload', methods=['POST'])
def api_upload_file():
    if 'file' not in request.files:
        return jsonify({'success': False, 'message': 'No file part'})
    
    file = request.files['file']
    if file.filename == '':
        return jsonify({'success': False, 'message': 'No selected file'})
    
    if file and allowed_file(file.filename):
        filename = secure_filename(file.filename)
        file_path = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        file.save(file_path)
        
        try:
            success, message = process_raw_data(file_path)
            return jsonify({'success': success, 'message': message})
        except Exception as e:
            return jsonify({'success': False, 'message': str(e)})
    
    return jsonify({'success': False, 'message': 'File type not allowed'})

@app.route('/api/raw-data', methods=['GET'])
def get_raw_data():
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor(dictionary=True)
        cursor.execute("SELECT * FROM raw_data WHERE is_latest = TRUE")
        data = cursor.fetchall()
        
        # Convert datetime objects to strings
        for row in data:
            for key, value in row.items():
                if isinstance(value, datetime):
                    row[key] = value.strftime('%Y-%m-%d')
        
        cursor.close()
        connection.close()
        
        return jsonify({'success': True, 'data': data})
    except Error as e:
        if connection:
            connection.close()
        return jsonify({'success': False, 'message': f'Database error: {str(e)}'})

@app.route('/api/clean-data', methods=['GET'])
def get_clean_data():
    status = request.args.get('status', None)
    
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor(dictionary=True)
        
        if status:
            cursor.execute("SELECT * FROM clean_data WHERE status = %s", (status,))
        else:
            cursor.execute("SELECT * FROM clean_data")
        
        data = cursor.fetchall()
        
        # Convert datetime objects to strings
        for row in data:
            for key, value in row.items():
                if isinstance(value, datetime):
                    row[key] = value.strftime('%Y-%m-%d')
        
        cursor.close()
        connection.close()
        
        return jsonify({'success': True, 'data': data})
    except Error as e:
        if connection:
            connection.close()
        return jsonify({'success': False, 'message': f'Database error: {str(e)}'})

@app.route('/api/clean-data/<int:id>', methods=['PUT'])
def update_clean_data(id):
    data = request.json
    
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor()
        
        update_query = """
        UPDATE clean_data SET 
            remarks = %s,
            date_published = %s,
            status = %s
        WHERE id = %s
        """
        
        date_published = None
        if 'date_published' in data and data['date_published']:
            date_published = data['date_published']
        
        cursor.execute(update_query, (
            data.get('remarks'),
            date_published,
            data.get('status'),
            id
        ))
        
        connection.commit()
        cursor.close()
        connection.close()
        
        return jsonify({'success': True, 'message': 'Data updated successfully'})
    except Error as e:
        if connection:
            connection.close()
        return jsonify({'success': False, 'message': f'Database error: {str(e)}'})

'''@app.route('/api/applicants', methods=['GET'])
def get_applicants():
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor(dictionary=True)
        cursor.execute("SELECT * FROM applicants")
        data = cursor.fetchall()
        
        # Convert datetime objects to strings
        for row in data:
            for key, value in row.items():
                if isinstance(value, datetime):
                    row[key] = value.strftime('%Y-%m-%d')
        
        cursor.close()
        connection.close()
        
        return jsonify({'success': True, 'data': data})
    except Error as e:
        if connection:
            connection.close()
        return jsonify({'success': False, 'message': f'Database error: {str(e)}'})'''

# Add these routes to app.py

@app.route('/api/applicants/<int:id>', methods=['GET'])
def get_applicant(id):
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor(dictionary=True)
        cursor.execute("SELECT * FROM applicants WHERE id = %s", (id,))
        data = cursor.fetchone()
        
        if not data:
            cursor.close()
            connection.close()
            return jsonify({'success': False, 'message': 'Applicant not found'})
        
        # Convert datetime objects to strings
        for key, value in data.items():
            if isinstance(value, datetime):
                data[key] = value.strftime('%Y-%m-%d')
        
        cursor.close()
        connection.close()
        
        return jsonify({'success': True, 'data': data})
    except Error as e:
        if connection:
            connection.close()
        return jsonify({'success': False, 'message': f'Database error: {str(e)}'})

@app.route('/api/applicants/<int:id>', methods=['PUT'])
def update_applicant(id):
    data = request.json
    
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor()
        
        update_query = """
        UPDATE applicants SET 
            fullname = %s,
            sex = %s,
            position_title = %s,
            techcode = %s,
            date_of_birth = %s,
            date_last_promotion = %s,
            date_last_increment = %s,
            date_of_longevity = %s,
            appointment_status = %s,
            plantilla_no = %s
        WHERE id = %s
        """
        
        cursor.execute(update_query, (
            data.get('fullname'),
            data.get('sex'),
            data.get('position_title'),
            data.get('techcode'),
            data.get('date_of_birth'),
            data.get('date_last_promotion'),
            data.get('date_last_increment'),
            data.get('date_of_longevity'),
            data.get('appointment_status'),
            data.get('plantilla_no'),
            id
        ))
        
        connection.commit()
        cursor.close()
        connection.close()
        
        return jsonify({'success': True, 'message': 'Applicant updated successfully'})
    except Error as e:
        if connection:
            connection.close()
        return jsonify({'success': False, 'message': f'Database error: {str(e)}'})

@app.route('/api/applicants/search', methods=['GET'])
def search_applicants():
    search_term = request.args.get('term', '')
    
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor(dictionary=True)
        
        search_query = """
        SELECT * FROM applicants
        WHERE fullname LIKE %s
        """
        
        cursor.execute(search_query, (f"%{search_term}%",))
        data = cursor.fetchall()
        
        # Convert datetime objects to strings
        for row in data:
            for key, value in row.items():
                if isinstance(value, datetime):
                    row[key] = value.strftime('%Y-%m-%d')
        
        cursor.close()
        connection.close()
        
        return jsonify({'success': True, 'data': data})
    except Error as e:
        if connection:
            connection.close()
        return jsonify({'success': False, 'message': f'Database error: {str(e)}'})

@app.route('/api/applicants', methods=['POST'])
def add_applicant():
    data = request.json
    
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor()
        
        insert_query = """
        INSERT INTO applicants (
            fullname, sex, position_title, techcode, date_of_birth,
            date_last_promotion, date_last_increment, date_of_longevity,
            appointment_status, plantilla_no
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        cursor.execute(insert_query, (
            data.get('fullname'),
            data.get('sex'),
            data.get('position_title'),
            data.get('techcode'),
            data.get('date_of_birth'),
            data.get('date_last_promotion'),
            data.get('date_last_increment'),
            data.get('date_of_longevity'),
            data.get('appointment_status'),
            data.get('plantilla_no')
        ))
        
        connection.commit()
        cursor.close()
        connection.close()
        
        return jsonify({'success': True, 'message': 'Applicant added successfully'})
    except Error as e:
        if connection:
            connection.close()
        return jsonify({'success': False, 'message': f'Database error: {str(e)}'})

@app.route('/api/export-clean-data', methods=['GET'])
def export_clean_data():
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor(dictionary=True)
        cursor.execute("SELECT * FROM clean_data")
        data = cursor.fetchall()
        
        cursor.close()
        connection.close()
        
        df = pd.DataFrame(data)
        export_path = os.path.join(app.config['UPLOAD_FOLDER'], 'clean_data_export.xlsx')
        df.to_excel(export_path, index=False)
        
        return send_file(export_path, as_attachment=True)
    except Error as e:
        if connection:
            connection.close()
        return jsonify({'success': False, 'message': f'Database error: {str(e)}'})

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)