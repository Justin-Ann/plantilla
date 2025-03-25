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
    try:
        if 'file' not in request.files:
            return jsonify({"success": False, "message": "No file uploaded"}), 400
        
        file = request.files['file']
        
        if file.filename == '':
            return jsonify({"success": False, "message": "No selected file"}), 400
        
        if not allowed_file(file.filename):
            return jsonify({"success": False, "message": "Invalid file format. Only CSV, XLS, and XLSX are allowed."}), 400

        filename = secure_filename(file.filename)
        file_path = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        
        # Save the file first
        file.save(file_path)
        
        try:
            # Process the saved file
            success, message = process_raw_data(file_path)
            
            if success:
                return jsonify({"success": True, "message": message})
            else:
                return jsonify({"success": False, "message": message}), 400
                
        except Exception as e:
            print(f"Error processing file: {str(e)}")
            return jsonify({
                "success": False, 
                "message": f"Error processing file: {str(e)}"
            }), 500
            
    except Exception as e:
        print(f"Upload error: {str(e)}")
        return jsonify({
            "success": False,
            "message": f"Upload error: {str(e)}"
        }), 500


@app.route('/api/upload-basic', methods=['POST'])
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

@app.route('/api/applicants', methods=['GET'])
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

@app.route('/api/uploaded-files', methods=['GET'])
def get_uploaded_files():
    month_year = request.args.get('month_year')
    
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor(dictionary=True)
        
        if month_year:
            cursor.execute("""
                SELECT * FROM uploaded_files 
                WHERE month_year = %s AND status = 'active'
                ORDER BY upload_date DESC
            """, (month_year,))
        else:
            cursor.execute("""
                SELECT * FROM uploaded_files 
                WHERE status = 'active'
                ORDER BY upload_date DESC
            """)
        
        files = cursor.fetchall()
        
        cursor.close()
        connection.close()
        
        return jsonify({'success': True, 'files': files})
    except Error as e:
        if connection:
            connection.close()
        return jsonify({'success': False, 'message': f'Database error: {str(e)}'})

@app.route('/api/upload-with-month', methods=['POST'])
def upload_file_with_month():
    if 'file' not in request.files:
        return jsonify({'success': False, 'message': 'No file part'})
    
    file = request.files['file']
    month_year = request.form.get('month_year')
    
    if not month_year:
        return jsonify({'success': False, 'message': 'Month and year required'})
    
    if file.filename == '':
        return jsonify({'success': False, 'message': 'No selected file'})
    
    if file and allowed_file(file.filename):
        try:
            # Generate unique filename
            timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
            original_filename = secure_filename(file.filename)
            filename = f"{timestamp}_{original_filename}"
            file_path = os.path.join(app.config['UPLOAD_FOLDER'], filename)
            
            # Save file
            file.save(file_path)
            
            # Save file metadata to database
            connection = connect_to_database()
            cursor = connection.cursor()
            
            insert_query = """
            INSERT INTO uploaded_files 
            (filename, original_filename, file_path, month_year, status, user_id)
            VALUES (%s, %s, %s, %s, %s, %s)
            """
            
            cursor.execute(insert_query, (
                filename,
                original_filename,
                file_path,
                month_year,
                'active',
                1  # Default user_id
            ))
            
            connection.commit()
            
            # Process the file
            success, message = process_raw_data(file_path)
            
            if success:
                return jsonify({'success': True, 'message': 'File uploaded and processed successfully'})
            else:
                return jsonify({'success': False, 'message': message})
                
        except Exception as e:
            if connection:
                connection.rollback()
            return jsonify({'success': False, 'message': str(e)})
        finally:
            if connection:
                cursor.close()
                connection.close()
    
    return jsonify({'success': False, 'message': 'Invalid file type'})

@app.route('/api/files/<int:file_id>', methods=['DELETE'])
def delete_file(file_id):
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor(dictionary=True)
        
        # Get file info
        cursor.execute("SELECT * FROM uploaded_files WHERE id = %s", (file_id,))
        file_info = cursor.fetchone()
        
        if not file_info:
            return jsonify({'success': False, 'message': 'File not found'})
        
        # Soft delete by updating status
        cursor.execute("""
            UPDATE uploaded_files 
            SET status = 'deleted' 
            WHERE id = %s
        """, (file_id,))
        
        connection.commit()
        return jsonify({'success': True, 'message': 'File deleted successfully'})
        
    except Error as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

@app.route('/api/files/<int:file_id>', methods=['PUT'])
def update_file(file_id):
    data = request.json
    
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor()
        
        update_query = """
        UPDATE uploaded_files 
        SET month_year = %s
        WHERE id = %s
        """
        
        cursor.execute(update_query, (data.get('month_year'), file_id))
        connection.commit()
        
        return jsonify({'success': True, 'message': 'File updated successfully'})
    except Error as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)