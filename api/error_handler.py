from flask import jsonify

class APIError(Exception):
    def __init__(self, message, status_code=400):
        self.message = message
        self.status_code = status_code

def handle_error(error):
    response = jsonify({
        'success': False,
        'message': str(error),
        'status_code': getattr(error, 'status_code', 500)
    })
    response.status_code = getattr(error, 'status_code', 500)
    return response
