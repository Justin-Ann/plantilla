import os

class Config:
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    UPLOAD_FOLDER = os.path.join(BASE_DIR, 'uploads')
    ALLOWED_EXTENSIONS = {'xlsx', 'xls', 'csv'}
    MAX_CONTENT_LENGTH = 16 * 1024 * 1024  # 16MB max file size

    DB_CONFIG = {
        'host': 'localhost',
        'database': 'plantilla_management',
        'user': 'tequila',
        'password': '0225'
    }

    # Dropdown choices
    VACATED_REASONS = [
        'PROMOTION',
        'COMPULSORY RETIREMENT',
        'RESIGNATION',
        'SWAPPING OF ITEM',
        'TRANSFER'
    ]

    APPOINTMENT_STATUS = [
        'TEMPORARY',
        'PERMANENT'
    ]

    CLEAN_DATA_STATUS = [
        'On-process',
        'On-hold',
        'Not yet for filling'
    ]

    # Add API endpoints configuration
    API_ENDPOINTS = {
        'raw_data': '/api/raw-data',
        'clean_data': '/api/clean-data',
        'files': '/api/files',
        'applicants': '/api/applicants',
        'dashboard': '/api/dashboard'
    }

    # Add file processing configuration
    FILE_PROCESSING = {
        'chunk_size': 1000,
        'max_rows': 50000,
        'timeout': 300  # 5 minutes
    }
