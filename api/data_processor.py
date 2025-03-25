# data_processor.py
import pandas as pd
import mysql.connector
from mysql.connector import Error
import sys
import os
from datetime import datetime

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
            return connection
    except Error as e:
        print(f"Error connecting to MySQL: {e}")
        return None

def parse_date(date_str):
    """Parses a date string into a date object, handling different formats."""
    if pd.isna(date_str) or not isinstance(date_str, str) or date_str == 'None':
        return None
    
    date_str = date_str.strip()
    if not date_str:
        return None
        
    for fmt in ('%Y-%m-%d', '%m/%d/%Y', '%d/%m/%Y', '%m-%d-%Y', '%d-%m-%Y'):
        try:
            return datetime.strptime(date_str, fmt).date()
        except ValueError:
            continue
    
    return None

def process_raw_data(file_path):
    connection = None
    try:
        # Read Excel/CSV file
        if isinstance(file_path, str):
            if file_path.endswith('.xlsx') or file_path.endswith('.xls'):
                df = pd.read_excel(file_path)
            elif file_path.endswith('.csv'):
                df = pd.read_csv(file_path)
            else:
                return False, "Unsupported file format. Please use Excel or CSV."
        else:
            # If file_path is a DataFrame (from direct upload)
            df = file_path

        # Validate required columns
        required_columns = [
            'PLANTILLA NO.', 'PLANTILLADIVISION', 'PLANTILLA SECTIONDEFINITION', 
            'EQUIVALENTDIVISION', 'POSITION TITLE', 'ITEM NUMBER', 'SG', 
            'DATEVACATED', 'VACATED DUE TO', 'VACATED BY'
        ]
        
        missing_columns = [col for col in required_columns if col not in df.columns]
        if missing_columns:
            return False, f"Missing required columns: {', '.join(missing_columns)}"

        # Replace NaN values with None
        df = df.where(pd.notna(df), None)
        
        # Convert all columns to string except dates
        for col in df.columns:
            if col != 'DATEVACATED':
                df[col] = df[col].astype(str)

        # Connect to database
        connection = connect_to_database()
        if not connection:
            return False, "Database connection failed"
        
        print("Processing data...")  # Debug log
        
        # Mark all existing records as not latest
        cursor = connection.cursor()
        cursor.execute("UPDATE raw_data SET is_latest = FALSE")
        
        # Process each row
        for _, row in df.iterrows():
            # 1. Insert into raw_data
            plantilla_no = row['PLANTILLA NO.']
            date_vacated = parse_date(row['DATEVACATED']) if row['DATEVACATED'] and row['DATEVACATED'] != 'None' else None
            
            insert_raw_query = """
            INSERT INTO raw_data (
                plantilla_no, plantilla_division, plantilla_sectiondefinition, 
                equivalent_division, position_title, item_number, sg, 
                date_vacated, vacated_due_to, vacated_by, is_latest
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, TRUE)
            """
            
            cursor.execute(insert_raw_query, (
                plantilla_no,
                row['PLANTILLADIVISION'],
                row['PLANTILLA SECTIONDEFINITION'],
                row['EQUIVALENTDIVISION'],
                row['POSITION TITLE'],
                row['ITEM NUMBER'],
                row['SG'],
                date_vacated,
                row['VACATED DUE TO'],
                row['VACATED BY']
            ))
            
            raw_data_id = cursor.lastrowid
            
            # 2. Insert or update clean_data
            upsert_clean_query = """
            INSERT INTO clean_data (
                plantilla_no, plantilla_division, plantilla_sectiondefinition, 
                equivalent_division, position_title, item_number, sg, 
                date_vacated, vacated_due_to, vacated_by, remarks, status, raw_data_id
            )
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 'On-process', 'On-process', %s)
            ON DUPLICATE KEY UPDATE
                plantilla_division = VALUES(plantilla_division),
                plantilla_sectiondefinition = VALUES(plantilla_sectiondefinition),
                equivalent_division = VALUES(equivalent_division),
                position_title = VALUES(position_title),
                item_number = VALUES(item_number),
                sg = VALUES(sg),
                date_vacated = VALUES(date_vacated),
                vacated_due_to = VALUES(vacated_due_to),
                vacated_by = VALUES(vacated_by),
                remarks = 'On-process',
                status = 'On-process',
                raw_data_id = VALUES(raw_data_id)
            """
            
            cursor.execute(upsert_clean_query, (
                plantilla_no,
                row['PLANTILLADIVISION'],
                row['PLANTILLA SECTIONDEFINITION'],
                row['EQUIVALENTDIVISION'],
                row['POSITION TITLE'],
                row['ITEM NUMBER'],
                row['SG'],
                date_vacated,
                row['VACATED DUE TO'],
                row['VACATED BY'],
                raw_data_id
            ))
            
            # 3. Insert or update applicants
            # Check if we have required applicant fields
            applicant_fields = {
                'fullname': row.get('FULLNAME', row.get('VACATED BY', None)),
                'sex': row.get('SEX', None),
                'position_title': row.get('POSITION TITLE', None),
                'techcode': row.get('TECHCODE', None),
                'date_of_birth': parse_date(row.get('DATE OF BIRTH', None)),
                'date_last_promotion': parse_date(row.get('DATELASTPROMOTION', None)),
                'date_last_increment': parse_date(row.get('DATELAST INCREMENT', None)),
                'date_of_longevity': parse_date(row.get('DATE OFLONGEVITY', None)),
                'appointment_status': row.get('APPOINTMENT STATUS', 'PERMANENT')
            }
            
            if applicant_fields['fullname'] and applicant_fields['fullname'] != 'None':
                upsert_applicant_query = """
                INSERT INTO applicants (
                    fullname, sex, position_title, techcode, date_of_birth,
                    date_last_promotion, date_last_increment, date_of_longevity,
                    appointment_status, plantilla_no
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                ON DUPLICATE KEY UPDATE
                    sex = COALESCE(VALUES(sex), sex),
                    position_title = COALESCE(VALUES(position_title), position_title),
                    techcode = COALESCE(VALUES(techcode), techcode),
                    date_of_birth = COALESCE(VALUES(date_of_birth), date_of_birth),
                    date_last_promotion = COALESCE(VALUES(date_last_promotion), date_last_promotion),
                    date_last_increment = COALESCE(VALUES(date_last_increment), date_last_increment),
                    date_of_longevity = COALESCE(VALUES(date_of_longevity), date_of_longevity),
                    appointment_status = COALESCE(VALUES(appointment_status), appointment_status)
                """
                
                try:
                    cursor.execute(upsert_applicant_query, (
                        applicant_fields['fullname'],
                        applicant_fields['sex'],
                        applicant_fields['position_title'],
                        applicant_fields['techcode'],
                        applicant_fields['date_of_birth'],
                        applicant_fields['date_last_promotion'],
                        applicant_fields['date_last_increment'],
                        applicant_fields['date_of_longevity'],
                        applicant_fields['appointment_status'],
                        plantilla_no
                    ))
                except Error as e:
                    # Handle the case where plantilla_no doesn't exist in clean_data yet
                    print(f"Error adding applicant: {e}")
                    # Try inserting without the foreign key constraint
                    alt_insert_query = """
                    INSERT INTO applicants (
                        fullname, sex, position_title, techcode, date_of_birth,
                        date_last_promotion, date_last_increment, date_of_longevity,
                        appointment_status
                    ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                    """
                    cursor.execute(alt_insert_query, (
                        applicant_fields['fullname'],
                        applicant_fields['sex'],
                        applicant_fields['position_title'],
                        applicant_fields['techcode'],
                        applicant_fields['date_of_birth'],
                        applicant_fields['date_last_promotion'],
                        applicant_fields['date_last_increment'],
                        applicant_fields['date_of_longevity'],
                        applicant_fields['appointment_status']
                    ))
        
        connection.commit()
        cursor.close()
        connection.close()
        
        return True, "Data processed successfully"
        
    except pd.errors.EmptyDataError:
        return False, "The uploaded file is empty"
    except pd.errors.ParserError as e:
        return False, f"Error parsing file: {str(e)}"
    except Exception as e:
        print(f"Error processing data: {str(e)}")  # Debug log
        if connection and connection.is_connected():
            connection.rollback()
            connection.close()
        return False, f"Error processing data: {str(e)}"

# When called directly from command line
if __name__ == "__main__":
    if len(sys.argv) > 1:
        file_path = sys.argv[1]
        success, message = process_raw_data(file_path)
        print(message)
        sys.exit(0 if success else 1)
    else:
        print("Usage: python data_processor.py <file_path>")
        sys.exit(1)