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
