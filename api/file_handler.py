import os
import pandas as pd
from datetime import datetime
from werkzeug.utils import secure_filename

class FileHandler:
    def __init__(self, upload_folder):
        self.upload_folder = upload_folder
        os.makedirs(upload_folder, exist_ok=True)
        os.makedirs(os.path.join(upload_folder, 'temp'), exist_ok=True)

    def generate_filename(self, original_filename):
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        secure_name = secure_filename(original_filename)
        return f"{timestamp}_{secure_name}"

    def save_file(self, file):
        filename = self.generate_filename(file.filename)
        file_path = os.path.join(self.upload_folder, filename)
        file.save(file_path)
        return {
            'filename': filename,
            'original_filename': file.filename,
            'file_path': file_path
        }

    def read_file(self, file_path):
        try:
            if file_path.endswith('.csv'):
                return pd.read_csv(file_path, dtype=str)
            return pd.read_excel(file_path, dtype=str)
        except Exception as e:
            raise Exception(f"Error reading file: {str(e)}")

    def delete_file(self, file_path):
        try:
            if os.path.exists(file_path):
                os.remove(file_path)
                return True
            return False
        except Exception:
            return False
