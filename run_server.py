import os
import sys

# Add project root to Python path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from api.app import app

def setup_directories():
    # Ensure required directories exist
    dirs = ['uploads', 'uploads/temp']
    for dir in dirs:
        os.makedirs(os.path.join(os.path.dirname(__file__), dir), exist_ok=True)

if __name__ == '__main__':
    setup_directories()
    app.run(debug=True, host='0.0.0.0', port=5000)
