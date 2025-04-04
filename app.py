from flask import Flask, jsonify, request
from flask_cors import CORS
import mysql.connector

app = Flask(__name__)
CORS(app)

db_config = {
    'host': 'localhost',
    'user': 'tequila',
    'password': '0225',
    'database': 'plantilla_management'
}

@app.route('/api/health-check')
def health_check():
    return jsonify({'status': 'ok'})

# Add more routes and API endpoints...

if __name__ == '__main__':
    app.run(debug=True)
