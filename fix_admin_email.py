#!/usr/bin/env python3
"""Update admin email to use a valid format"""

import pymysql

DB_HOST = "localhost"
DB_PORT = 3306
DB_USER = "root"
DB_PASSWORD = ""
DB_NAME = "ojt_timelog"

def fix_admin_email():
    conn = pymysql.connect(
        host=DB_HOST,
        port=DB_PORT,
        user=DB_USER,
        password=DB_PASSWORD,
        database=DB_NAME,
        charset="utf8mb4"
    )
    
    try:
        with conn.cursor() as cursor:
            # Update the admin email to use a valid format
            cursor.execute("""
                UPDATE users 
                SET email = 'admin@example.com', role = 'super_admin'
                WHERE email IN ('admin@ojt-tlms.test', 'admin@ojt-tlms.local')
            """)
            conn.commit()
            print(f"[OK] Admin email updated. New login:")
            print(f"     Email: admin@example.com")
            print(f"     Password: Admin@123")
    finally:
        conn.close()

if __name__ == "__main__":
    fix_admin_email()
