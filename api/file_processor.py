import pandas as pd
from datetime import datetime
from .error_handler import APIError

class FileProcessor:
    @staticmethod
    def validate_file_headers(df, required_columns):
        missing = [col for col in required_columns if col not in df.columns]
        if missing:
            raise APIError(f"Missing required columns: {', '.join(missing)}")
        return True

    @staticmethod
    def format_date(date_str):
        if not date_str or pd.isna(date_str):
            return None
        try:
            return pd.to_datetime(date_str).strftime('%Y-%m-%d')
        except:
            return None

    @staticmethod
    def format_currency(value):
        try:
            return f"₱{float(value):,.2f}"
        except:
            return None
