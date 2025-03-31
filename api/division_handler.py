from .db_manager import DatabaseManager

class DivisionHandler:
    def __init__(self):
        self.db = DatabaseManager()

    def get_divisions(self):
        """Get all divisions with their codes"""
        query = """
            SELECT division_code, division_name 
            FROM divisions 
            ORDER BY division_code
        """
        return self.db.execute_query(query, fetch=True)

    def get_division_data(self, division_code, month_year=None):
        """Get data for specific division"""
        query = """
            SELECT rd.* 
            FROM raw_data rd
            JOIN uploaded_files uf ON rd.file_id = uf.id 
            WHERE rd.plantilla_division_definition = (
                SELECT division_name 
                FROM divisions 
                WHERE division_code = %s
            )
            AND rd.is_latest = TRUE
        """
        params = [division_code]
        
        if month_year:
            query += " AND uf.month_year = %s"
            params.append(month_year)
        
        query += " ORDER BY rd.plantilla_no"
        return self.db.execute_query(query, params, fetch=True)

    def update_division_data(self, division_code, data):
        """Update data for a specific division"""
        try:
            success = True
            message = "Data updated successfully"
            
            for row in data:
                query = """
                    UPDATE raw_data 
                    SET plantilla_division_definition = %s,
                        last_edited = NOW()
                    WHERE id = %s 
                    AND is_latest = TRUE
                """
                self.db.execute_query(query, [row['division_name'], row['id']])
                
            return success, message
        except Exception as e:
            return False, str(e)
