# app.py
from flask import Flask, request, jsonify, send_file, redirect, render_template
from io import BytesIO
import os
import pandas as pd
from werkzeug.utils import secure_filename
import sys

# Add project root to Python path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

# Update imports with correct paths
from api.data_processor import process_raw_data
from api.file_handler import FileHandler
from api.error_handler import APIError, handle_error
from api.db_manager import DatabaseManager
from config import Config

from datetime import datetime
from flask_cors import CORS    
import mysql.connector
from mysql.connector import Error

app = Flask(__name__, static_folder='../static', template_folder='../templates')
app.config.from_object(Config)
file_handler = FileHandler(app.config['UPLOAD_FOLDER'])
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
            # If status filter is provided
            cursor.execute("""
                SELECT * FROM clean_data 
                WHERE status = %s
                ORDER BY plantilla_no
            """, (status,))
        else:
            # Get all records
            cursor.execute("""
                SELECT * FROM clean_data 
                ORDER BY plantilla_no
            """)
        
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
    try:
        data = request.json
        connection = connect_to_database()
        if not connection:
            return jsonify({'success': False, 'message': 'Database connection failed'})
        
        cursor = connection.cursor()
        
        update_query = """
        UPDATE clean_data 
        SET remarks = %s,
            date_published = %s,
            status = %s
        WHERE id = %s
        """
        
        cursor.execute(update_query, (
            data.get('remarks'),
            data.get('date_published'),
            data.get('status'),
            id
        ))
        
        connection.commit()
        return jsonify({'success': True, 'message': 'Record updated successfully'})
        
    except Exception as e:
        if connection:
            connection.rollback()
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

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
    
    try:
        connection = connect_to_database()
        if not connection:
            return jsonify({'success': False, 'message': 'Database connection failed'})
            
        cursor = connection.cursor(dictionary=True)
        
        # Search in both applicants and raw_data tables
        cursor.execute("""
            SELECT * FROM (
                SELECT id, fullname, sex, position_title, date_of_birth, 
                       date_last_promotion, date_last_increment, date_of_longevity,
                       appointment_status, plantilla_no, 'applicant' as source
                FROM applicants 
                WHERE fullname LIKE %s
                UNION ALL
                SELECT id, fullname, sex, position_title, date_of_birth,
                       date_last_promotion, date_last_increment, date_of_longevity,
                       appointment_status, plantilla_no, 'raw_data' as source
                FROM raw_data 
                WHERE fullname LIKE %s AND is_latest = TRUE
            ) combined_results
            ORDER BY fullname
        """, (f"%{search_term}%", f"%{search_term}%"))
        
        data = cursor.fetchall()
        
        # Convert datetime objects to strings
        for row in data:
            for key, value in row.items():
                if isinstance(value, datetime):
                    row[key] = value.strftime('%Y-%m-%d')
        
        return jsonify({'success': True, 'data': data})
        
    except Error as e:
        return jsonify({'success': False, 'message': f'Database error: {str(e)}'})
    finally:
        if connection:
            cursor.close()
            connection.close()

@app.route('/api/applicants', methods=['POST'])
def add_applicant():
    try:
        data = request.json
        connection = connect_to_database()
        if not connection:
            return jsonify({'success': False, 'message': 'Database connection failed'})
        
        cursor = connection.cursor()
        
        insert_query = """
        INSERT INTO applicants (
            fullname, sex, position_title, techcode,
            date_of_birth, date_last_promotion, date_last_increment,
            date_of_longevity, appointment_status, plantilla_no
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        values = (
            data.get('fullname'),
            data.get('sex'),
            data.get('position_title'),
            data.get('techcode'),
            data.get('date_of_birth') or None,
            data.get('date_last_promotion') or None,
            data.get('date_last_increment') or None,
            data.get('date_of_longevity') or None,
            data.get('appointment_status'),
            data.get('plantilla_no') or None
        )
        
        cursor.execute(insert_query, values)
        connection.commit()
        return jsonify({'success': True, 'message': 'Applicant added successfully'})
        
    except Exception as e:
        if connection:
            connection.rollback()
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

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
            # If month_year is specified, get files for that month
            cursor.execute("""
                SELECT * FROM uploaded_files 
                WHERE month_year = %s AND status = 'active'
                ORDER BY upload_date DESC
            """, (month_year,))
        else:
            # If no month_year, get all active files
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
    
    try:
        # Save and process file using FileHandler
        file_info = file_handler.save_file(file)
        
        # Save file metadata and process
        connection = connect_to_database()
        cursor = connection.cursor()
        
        cursor.execute("""
            INSERT INTO uploaded_files 
            (filename, original_filename, file_path, month_year, status, processing_status)
            VALUES (%s, %s, %s, %s, %s, %s)
        """, (
            file_info['filename'],
            file_info['original_filename'],
            file_info['file_path'],
            month_year,
            'active',
            'processing'
        ))
        
        file_id = cursor.lastrowid
        connection.commit()
        
        # Process the file
        success, message = process_raw_data(file_info['file_path'], file_id)
        
        if success:
            cursor.execute("""
                UPDATE uploaded_files 
                SET processing_status = 'completed'
                WHERE id = %s
            """, (file_id,))
            connection.commit()
            return jsonify({'success': True, 'message': message})
        else:
            cursor.execute("""
                UPDATE uploaded_files 
                SET processing_status = 'failed',
                    error_message = %s
                WHERE id = %s
            """, (message, file_id))
            connection.commit()
            return jsonify({'success': False, 'message': message})
            
    except Exception as e:
        if connection:
            connection.rollback()
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

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

@app.route('/api/files/<int:file_id>/content', methods=['GET'])
def get_file_content(file_id):
    try:
        connection = connect_to_database()
        cursor = connection.cursor(dictionary=True)
        cursor.execute("SELECT * FROM uploaded_files WHERE id = %s", (file_id,))
        file_info = cursor.fetchone()
        
        if not file_info:
            return jsonify({'success': False, 'message': 'File not found'})
        
        # Convert upload_date to string format
        if file_info.get('upload_date'):
            file_info['upload_date'] = file_info['upload_date'].strftime('%Y-%m-%d %H:%M:%S')
        
        # Read and process file content
        try:
            if file_info['file_path'].endswith('.csv'):
                df = pd.read_csv(file_info['file_path'], dtype=str)  # Read all columns as strings
            else:
                df = pd.read_excel(file_info['file_path'], dtype=str)  # Read all columns as strings
            
            # Replace NaN/None with empty string
            df = df.fillna('')
            
            # Try to identify and format date columns
            date_columns = ['DATEVACATED', 'DATE OF BIRTH', 'DATE LAST PROMOTION', 
                          'DATE LAST INCREMENT', 'DATE OF LONGEVITY']
            
            for col in df.columns:
                if (col in date_columns):
                    df[col] = df[col].apply(lambda x: format_date_string(x) if x else '')
            
            return jsonify({
                'success': True,
                'data': df.to_dict('records'),
                'file_info': file_info
            })
        except Exception as e:
            print(f"Error reading file: {str(e)}")  # Add debug logging
            return jsonify({'success': False, 'message': f'Error reading file: {str(e)}'})
            
    except Exception as e:
        print(f"Error in get_file_content: {str(e)}")  # Add debug logging
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

def format_date_string(date_str):
    """Helper function to format date strings"""
    if not date_str or not isinstance(date_str, str):
        return ''
    
    date_str = date_str.strip()
    if not date_str:
        return ''
    
    try:
        # Try different date formats
        for fmt in ('%Y-%m-%d', '%m/%d/%Y', '%d/%m/%Y', '%m-%d-%Y', '%d-%m-%Y'):
            try:
                return datetime.strptime(date_str, fmt).strftime('%Y-%m-%d')
            except ValueError:
                continue
        
        # If no format matches, return the original string
        return date_str
    except Exception:
        return date_str

@app.route('/api/files/<int:file_id>/content', methods=['PUT'])
def update_file_content(file_id):
    connection = connect_to_database()
    if not connection:
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = connection.cursor(dictionary=True)  # Set cursor to return dictionaries
        
        # First check if last_modified column exists
        cursor.execute("""
            SELECT COUNT(*) as count
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'uploaded_files' 
            AND COLUMN_NAME = 'last_modified'
            AND TABLE_SCHEMA = DATABASE()
        """)
        
        result = cursor.fetchone()
        if result['count'] == 0:
            # Add last_modified column if it doesn't exist
            cursor.execute("""
                ALTER TABLE uploaded_files 
                ADD COLUMN last_modified TIMESTAMP NULL DEFAULT NULL
            """)
            connection.commit()
        
        # Update last_modified timestamp
        cursor.execute("""
            UPDATE uploaded_files 
            SET last_modified = CURRENT_TIMESTAMP 
            WHERE id = %s
        """, (file_id,))
        
        # Get file info
        cursor.execute("SELECT * FROM uploaded_files WHERE id = %s", (file_id,))
        file_info = cursor.fetchone()
        
        if not file_info:
            return jsonify({'success': False, 'message': 'File not found'})
        
        # Get updated content
        data = request.json
        df = pd.DataFrame(data['content'])
        
        # Save updated content
        if file_info['file_path'].endswith('.csv'):
            df.to_csv(file_info['file_path'], index=False)
        else:
            df.to_excel(file_info['file_path'], index=False)
        
        # Process the updated file
        success, message = process_raw_data(file_info['file_path'])
        
        if success:
            connection.commit()
            return jsonify({'success': True, 'message': 'File updated and processed successfully'})
        else:
            connection.rollback()
            return jsonify({'success': False, 'message': message})
            
    except Exception as e:
        if connection:
            connection.rollback()
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

# Add endpoint for getting applicant details
@app.route('/api/applicants/<int:id>', methods=['GET'])
def get_applicant(id):
    try:
        connection = connect_to_database()
        cursor = connection.cursor(dictionary=True)
        cursor.execute("SELECT * FROM applicants WHERE id = %s", (id,))
        applicant = cursor.fetchone()
        
        if not applicant:
            return jsonify({'success': False, 'message': 'Applicant not found'})
        
        # Convert dates to string format
        date_fields = ['date_of_birth', 'date_last_promotion', 'date_last_increment', 'date_of_longevity']
        for field in date_fields:
            if applicant[field]:
                applicant[field] = applicant[field].strftime('%Y-%m-%d')
                
        return jsonify({'success': True, 'data': applicant})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

# Add endpoint for updating applicant
@app.route('/api/applicants/<int:id>', methods=['PUT'])
def update_applicant(id):
    try:
        data = request.json
        connection = connect_to_database()
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
        
        values = (
            data.get('fullname'),
            data.get('sex'),
            data.get('position_title'),
            data.get('techcode'),
            data.get('date_of_birth') or None,
            data.get('date_last_promotion') or None,
            data.get('date_last_increment') or None,
            data.get('date_of_longevity') or None,
            data.get('appointment_status'),
            data.get('plantilla_no') or None,
            id
        )
        
        cursor.execute(update_query, values)
        connection.commit()
        return jsonify({'success': True, 'message': 'Applicant updated successfully'})
        
    except Exception as e:
        if connection:
            connection.rollback()
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

@app.route('/api/files/<int:file_id>/download', methods=['GET'])
def download_file(file_id):
    try:
        download_type = request.args.get('type', 'raw')  # Default to raw if not specified
        connection = connect_to_database()
        cursor = connection.cursor(dictionary=True)
        
        # Get file info
        cursor.execute("SELECT * FROM uploaded_files WHERE id = %s", (file_id,))
        file_info = cursor.fetchone()
        
        if not file_info:
            return jsonify({'success': False, 'message': 'File not found'}), 404

        try:
            df = None
            if download_type == 'raw':
                # Get absolute file path
                abs_file_path = os.path.abspath(file_info['file_path'])
                if not os.path.exists(abs_file_path):
                    # Try relative path
                    rel_path = os.path.join(app.config['UPLOAD_FOLDER'], os.path.basename(file_info['file_path']))
                    if os.path.exists(rel_path):
                        abs_file_path = rel_path
                    else:
                        return jsonify({'success': False, 'message': 'File not found on server'}), 404
                
                # Read original file
                if abs_file_path.endswith('.csv'):
                    df = pd.read_csv(abs_file_path)
                else:
                    df = pd.read_excel(abs_file_path)
            else:
                # Get clean data for this file
                cursor.execute("""
                    SELECT cd.*
                    FROM clean_data cd
                    JOIN raw_data rd ON cd.raw_data_id = rd.id
                    WHERE rd.is_latest = TRUE
                    ORDER BY cd.plantilla_no
                """)
                clean_data = cursor.fetchall()
                if clean_data:
                    df = pd.DataFrame(clean_data)
                else:
                    return jsonify({'success': False, 'message': 'No clean data found'}), 404

            if df is None:
                return jsonify({'success': False, 'message': 'Could not read file data'}), 500

            # Create temporary file
            temp_filename = f"{download_type}_data_{file_id}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.xlsx"
            temp_filepath = os.path.join(app.config['UPLOAD_FOLDER'], temp_filename)
            
            # Export to Excel
            df.to_excel(temp_filepath, index=False, engine='openpyxl')
            
            try:
                return send_file(
                    temp_filepath,
                    mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    as_attachment=True,
                    download_name=f"{os.path.splitext(file_info['original_filename'])[0]}_{download_type}.xlsx"
                )
            finally:
                # Clean up temp file after sending
                if os.path.exists(temp_filepath):
                    try:
                        os.remove(temp_filepath)
                    except:
                        pass
                        
        except Exception as e:
            print(f"Error processing file: {str(e)}")
            return jsonify({'success': False, 'message': 'Error processing file'}), 500
            
    except Exception as e:
        print(f"Error in download_file: {str(e)}")
        return jsonify({'success': False, 'message': str(e)}), 500
    finally:
        if connection:
            cursor.close()
            connection.close()

@app.route('/api/clean-data/export', methods=['GET'])
def export_clean_data_excel():
    try:
        connection = connect_to_database()
        if not connection:
            return jsonify({'success': False, 'message': 'Database connection failed'}), 500

        cursor = connection.cursor(dictionary=True)
        try:
            # Get all clean data
            cursor.execute("""
                SELECT 
                    cd.plantilla_no,
                    cd.plantilla_division,
                    cd.position_title,
                    cd.sg,
                    cd.remarks,
                    cd.date_published,
                    cd.status,
                    cd.date_vacated,
                    cd.vacated_due_to,
                    cd.vacated_by
                FROM clean_data cd
                WHERE cd.raw_data_id IN (
                    SELECT id FROM raw_data WHERE is_latest = TRUE
                )
                ORDER BY cd.plantilla_no
            """)
            data = cursor.fetchall()
            
            if not data:
                return jsonify({'success': False, 'message': 'No data available to export'}), 404

            # Create DataFrame and export to Excel
            df = pd.DataFrame(data)
            
            # Create temp directory if it doesn't exist
            temp_dir = os.path.join(app.config['UPLOAD_FOLDER'], 'temp')
            os.makedirs(temp_dir, exist_ok=True)
            
            # Create temporary file
            temp_filename = f"clean_data_export_{datetime.now().strftime('%Y%m%d_%H%M%S')}.xlsx"
            temp_filepath = os.path.join(temp_dir, temp_filename)
            
            # Export to Excel with proper formatting
            df.to_excel(temp_filepath, index=False, engine='openpyxl')

            return send_file(
                temp_filepath,
                mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                as_attachment=True,
                download_name=temp_filename
            )
            
        except Exception as e:
            print(f"Error executing query or creating file: {str(e)}")
            return jsonify({'success': False, 'message': 'Error generating export file'}), 500
        finally:
            cursor.close()
            
    except Exception as e:
        print(f"Error in export: {str(e)}")
        return jsonify({'success': False, 'message': str(e)}), 500
    finally:
        if connection:
            connection.close()

@app.route('/api/dashboard/status-counts', methods=['GET'])
def get_status_counts():
    connection = connect_to_database()
    cursor = connection.cursor(dictionary=True)
    
    try:
        # Count status across all monthly files
        cursor.execute("""
            SELECT status, COUNT(*) as count 
            FROM clean_data cd
            JOIN raw_data rd ON cd.raw_data_id = rd.id
            JOIN uploaded_files uf ON rd.file_id = uf.id
            WHERE uf.status = 'active'
            GROUP BY cd.status
        """)
        
        counts = {row['status']: row['count'] for row in cursor.fetchall()}
        return jsonify({'success': True, 'counts': counts})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

@app.route('/api/raw-data/edit/<int:file_id>', methods=['GET'])
def get_raw_data_edit(file_id):
    connection = connect_to_database()
    try:
        cursor = connection.cursor(dictionary=True)
        cursor.execute("""
            SELECT * FROM raw_data 
            WHERE file_id = %s AND is_latest = TRUE
            ORDER BY plantilla_no
        """, (file_id,))
        
        data = cursor.fetchall()
        return jsonify({'success': True, 'data': data})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

@app.route('/api/dashboard/recent-files', methods=['GET'])
def get_recent_files():
    connection = connect_to_database()
    cursor = connection.cursor(dictionary=True)
    
    try:
        cursor.execute("""
            SELECT id, original_filename, upload_date, last_modified 
            FROM uploaded_files 
            WHERE status = 'active'
            ORDER BY COALESCE(last_modified, upload_date) DESC 
            LIMIT 5 
        """)
        
        files = cursor.fetchall()
        
        # Convert datetime objects to ISO format strings
        for file in files:
            if file['last_modified']:
                file['last_modified'] = file['last_modified'].isoformat()
            if file['upload_date']:
                file['upload_date'] = file['upload_date'].isoformat()
                
        return jsonify({'success': True, 'files': files})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

@app.route('/api/files/search', methods=['GET'])
def search_files():
    term = request.args.get('term', '')
    connection = connect_to_database()
    cursor = connection.cursor(dictionary=True)
    
    try:
        cursor.execute("""
            SELECT * FROM uploaded_files 
            WHERE original_filename LIKE %s 
            AND status = 'active'
            ORDER BY upload_date DESC
        """, (f'%{term}%',))
        
        files = cursor.fetchall()
        return jsonify({'success': True, 'data': files})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

@app.route('/api/health-check', methods=['GET'])
def health_check():
    try:
        connection = connect_to_database()
        if connection:
            connection.close()
            return jsonify({'success': True, 'message': 'Server is running'})
        return jsonify({'success': False, 'message': 'Database connection failed'}), 500
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)}), 500

@app.route('/api/export/<type>', methods=['GET'])
def export_data(type):
    if type not in ['raw', 'clean']:
        return jsonify({'success': False, 'message': 'Invalid export type'})
        
    connection = connect_to_database()
    cursor = connection.cursor(dictionary=True)
    
    try:
        if type == 'raw':
            query = "SELECT * FROM raw_data WHERE is_latest = TRUE"
        else:
            query = "SELECT * FROM clean_data"
            
        cursor.execute(query)
        data = cursor.fetchall()
        
        df = pd.DataFrame(data)
        output = BytesIO()
        df.to_excel(output, index=False)
        output.seek(0)
        
        return send_file(
            output,
            mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            as_attachment=True,
            download_name=f'{type}_data_export.xlsx'
        )
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        if connection:
            cursor.close()
            connection.close()

from .auth_handler import AuthHandler

@app.route('/api/auth/register', methods=['POST'])
def register():
    data = request.json
    connection = connect_to_database()
    cursor = connection.cursor()
    
    try:
        # Check if email exists
        cursor.execute("SELECT id FROM users WHERE email = %s", (data['email'],))
        if cursor.fetchone():
            return jsonify({'success': False, 'message': 'Email already registered'})
        
        # Create verification token
        token = AuthHandler.generate_token(data['email'])
        
        # Insert user
        cursor.execute("""
            INSERT INTO users (username, email, password, role, verification_token)
            VALUES (%s, %s, %s, %s, %s)
        """, (data['username'], data['email'], data['password'], 'user', token))
        
        connection.commit()
        
        # Send verification email
        AuthHandler.send_verification_email(data['email'], token)
        
        return jsonify({'success': True, 'message': 'Registration successful. Please verify your email.'})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        cursor.close()
        connection.close()

from .handlers.division_db import DivisionDBHandler

@app.route('/api/divisions/counts', methods=['GET'])
def get_division_counts():
    try:
        handler = DivisionDBHandler()
        counts = handler.get_division_counts()
        return jsonify({'success': True, 'counts': counts})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/divisions/<int:division_code>/data', methods=['GET'])
def get_division_data(division_code):
    month_year = request.args.get('month_year')
    try:
        handler = DivisionDBHandler()
        data = handler.get_division_data(division_code, month_year)
        return jsonify({'success': True, 'data': data})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/divisions/<int:division_code>/status', methods=['GET'])
def get_division_status(division_code):
    try:
        handler = DivisionDBHandler()
        counts = handler.get_division_status_counts(division_code)
        return jsonify({'success': True, 'counts': counts})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/applicants/filter', methods=['GET'])
def filter_applicants():
    month = request.args.get('month')
    division = request.args.get('division')
    
    query = """
        SELECT * FROM applicants 
        WHERE 1=1
    """
    params = []
    
    if month:
        query += " AND MONTH(date_orig_appt) = %s"
        params.append(month)
    
    if division:
        query += " AND division_id = %s"
        params.append(division)
        
    try:
        connection = connect_to_database()
        cursor = connection.cursor(dictionary=True)
        cursor.execute(query, params)
        data = cursor.fetchall()
        return jsonify({'success': True, 'data': data})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})

@app.route('/api/divisions/<int:division_code>/export', methods=['GET'])
def export_division_data(division_code):
    month_year = request.args.get('month_year')
    try:
        handler = DivisionDBHandler()
        data = handler.get_division_data(division_code, month_year)
        
        # Create Excel file
        df = pd.DataFrame(data)
        output = BytesIO()
        df.to_excel(output, index=False, engine='openpyxl')
        output.seek(0)
        
        # Get division name
        division_name = handler.get_division_name(division_code)
        filename = f"{division_name}_{month_year or 'all'}_export.xlsx"
        
        return send_file(
            output,
            mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            as_attachment=True,
            download_name=filename
        )
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)