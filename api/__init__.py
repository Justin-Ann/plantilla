# Initialize API package and expose key components
from .app import app
from .data_processor import process_raw_data
from .file_handler import FileHandler
from .error_handler import APIError, handle_error
from .db_manager import DatabaseManager

__all__ = ['app', 'process_raw_data', 'FileHandler', 'APIError', 'handle_error', 'DatabaseManager']
