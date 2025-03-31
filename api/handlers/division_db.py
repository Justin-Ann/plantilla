from ..db_manager import DatabaseManager

class DivisionDBHandler:
    def __init__(self):
        self.db = DatabaseManager()

    def get_division_counts(self):
        """Get count of records for each division"""
        query = """
            SELECT d.division_name, COUNT(rd.id) as count
            FROM divisions d
            LEFT JOIN raw_data rd ON rd.division_id = d.id AND rd.is_latest = TRUE
            GROUP BY d.division_name
            ORDER BY d.division_code
        """
        return self.db.execute_query(query, fetch=True)

    def get_division_data(self, division_code, month_year=None):
        """Get all records for a specific division"""
        query = """
            SELECT rd.*, cd.status, cd.remarks
            FROM raw_data rd
            LEFT JOIN clean_data cd ON rd.id = cd.raw_data_id
            WHERE rd.division_id = %s AND rd.is_latest = TRUE
        """
        params = [division_code]
        
        if month_year:
            query += " AND rd.file_id IN (SELECT id FROM uploaded_files WHERE month_year = %s)"
            params.append(month_year)

        return self.db.execute_query(query, params, fetch=True)

    def update_division_record(self, record_id, data):
        """Update a single record in a division"""
        query = """
            UPDATE raw_data 
            SET plantilla_division = %s,
                plantilla_section = %s,
                position_title = %s,
                sg = %s,
                step = %s,
                last_edited = NOW()
            WHERE id = %s AND is_latest = TRUE
        """
        params = [
            data['plantilla_division'],
            data['plantilla_section'],
            data['position_title'],
            data['sg'],
            data['step'],
            record_id
        ]
        return self.db.execute_query(query, params)

    def get_division_status_counts(self, division_code):
        """Get counts of different statuses in a division"""
        query = """
            SELECT cd.status, COUNT(*) as count
            FROM clean_data cd
            JOIN raw_data rd ON cd.raw_data_id = rd.id
            WHERE rd.division_id = %s AND rd.is_latest = TRUE
            GROUP BY cd.status
        """
        return self.db.execute_query(query, [division_code], fetch=True)
