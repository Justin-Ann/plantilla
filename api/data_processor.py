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

#new
def parse_date(date_str):
    """Parses a date string into a date object, handling different formats."""
    if pd.isna(date_str) or not isinstance(date_str, str):
        return None
    for fmt in ('%Y-%m-%d', '%m/%d/%Y'):
        try:
            return datetime.strptime(date_str, fmt).date()
        except ValueError:
            continue
    return None


def process_raw_data(file_path):
    try:
        # Read Excel/CSV file
        if file_path.endswith('.xlsx') or file_path.endswith('.xls'):
            df = pd.read_excel(file_path)
        elif file_path.endswith('.csv'):
            df = pd.read_csv(file_path)
        else:
            return False, "Unsupported file format. Please use Excel or CSV."
        
        # Check required columns
        required_columns = [
            'PLANTILLA NO.', 'PLANTILLADIVISION', 'PLANTILLA SECTIONDEFINITION', 
            'EQUIVALENTDIVISION', 'POSITION TITLE', 'ITEM NUMBER', 'SG', 
            'DATEVACATED', 'VACATED DUE TO', 'VACATED BY'
        ]
        
        missing_columns = [col for col in required_columns if col not in df.columns]
        if missing_columns:
            return False, f"Missing required columns: {', '.join(missing_columns)}"
        
        # Connect to database
        connection = connect_to_database()
        if not connection:
            return False, "Database connection failed"
        
        cursor = connection.cursor()
        
        # Mark all existing records as not latest
        cursor.execute("UPDATE raw_data SET is_latest = FALSE")
        connection.commit()
        
        # Insert new raw data
        for _, row in df.iterrows():
            insert_query = """
            INSERT INTO raw_data (
                plantilla_no, plantilla_division, plantilla_sectiondefinition, 
                equivalent_division, position_title, item_number, sg, 
                date_vacated, vacated_due_to, vacated_by
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """
            
            date_vacated = None
            if not pd.isna(row['DATEVACATED']):
                if isinstance(row['DATEVACATED'], str):
                    try:
                        date_vacated = parse_date(row['DATEVACATED'], '%Y-%m-%d').date()
                    except ValueError:
                        try:
                            date_vacated = parse_date(row['DATEVACATED'], '%m/%d/%Y').date()
                        except ValueError:
                            date_vacated = None
                else:
                    date_vacated = row['DATEVACATED']
            
            cursor.execute(insert_query, (
                row['PLANTILLA NO.'],
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
        
        # Create clean data from raw data
        cursor.execute("""
        INSERT INTO clean_data (
            plantilla_no, plantilla_division, plantilla_sectiondefinition, 
            equivalent_division, position_title, item_number, sg, 
            date_vacated, vacated_due_to, vacated_by, remarks, status, raw_data_id
        )
        SELECT 
            plantilla_no, plantilla_division, plantilla_sectiondefinition, 
            equivalent_division, position_title, item_number, sg, 
            date_vacated, vacated_due_to, vacated_by, 'On-process', 'On-process', id
        FROM raw_data
        WHERE is_latest = TRUE
        """)
        
        connection.commit()
        cursor.close()
        connection.close()
        
        return True, "Data processed successfully"
        
    except Exception as e:
        print(f"Error processing data: {e}")
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