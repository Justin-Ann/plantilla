import mysql.connector
from mysql.connector import Error
from contextlib import contextmanager
from config import Config

class DatabaseManager:
    @contextmanager
    def get_connection(self):
        connection = None
        try:
            connection = mysql.connector.connect(**Config.DB_CONFIG)
            yield connection
        except Error as e:
            raise Exception(f"Database connection failed: {str(e)}")
        finally:
            if connection and connection.is_connected():
                connection.close()

    def execute_query(self, query, params=None, fetch=False):
        with self.get_connection() as connection:
            cursor = connection.cursor(dictionary=True)
            try:
                cursor.execute(query, params or ())
                if fetch:
                    return cursor.fetchall()
                connection.commit()
                return cursor.lastrowid
            finally:
                cursor.close()
