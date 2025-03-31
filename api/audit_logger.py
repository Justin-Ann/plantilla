from .db_manager import DatabaseManager

class AuditLogger:
    def __init__(self):
        self.db = DatabaseManager()

    def log_action(self, user_id, action, details, ip_address):
        """Log an action to the audit log"""
        query = """
            INSERT INTO audit_log (user_id, action, details, ip_address)
            VALUES (%s, %s, %s, %s)
        """
        params = [user_id, action, details, ip_address]
        return self.db.execute_query(query, params)

    def get_user_actions(self, user_id, limit=10):
        """Get recent actions for a specific user"""
        query = """
            SELECT * FROM audit_log 
            WHERE user_id = %s 
            ORDER BY timestamp DESC 
            LIMIT %s
        """
        return self.db.execute_query(query, [user_id, limit], fetch=True)
