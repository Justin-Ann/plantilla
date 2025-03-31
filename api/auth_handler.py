from flask import jsonify
import jwt
from datetime import datetime, timedelta
import smtplib
from email.mime.text import MIMEText
from config import Config

class AuthHandler:
    @staticmethod
    def send_verification_email(email, token):
        msg = MIMEText(f'Click here to verify your email: {Config.BASE_URL}/verify/{token}')
        msg['Subject'] = 'Verify Your Email'
        msg['From'] = Config.MAIL_SENDER
        msg['To'] = email
        
        with smtplib.SMTP(Config.MAIL_SERVER, Config.MAIL_PORT) as server:
            server.starttls()
            server.login(Config.MAIL_USERNAME, Config.MAIL_PASSWORD)
            server.send_message(msg)

    @staticmethod
    def generate_token(user_id):
        return jwt.encode(
            {
                'user_id': user_id,
                'exp': datetime.utcnow() + timedelta(days=1)
            },
            Config.SECRET_KEY,
            algorithm='HS256'
        )

    @staticmethod
    def verify_token(token):
        try:
            return jwt.decode(token, Config.SECRET_KEY, algorithms=['HS256'])
        except:
            return None
