#!/usr/bin/env python3
"""Check and fix database values"""

import pymysql

DB_HOST = "localhost"
DB_PORT = 3306
DB_USER = "root"
DB_PASSWORD = ""
DB_NAME = "ojt_timelog"

def check_and_fix():
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
            # Check current values
            cursor.execute("SELECT id, email, role FROM users")
            users = cursor.fetchall()
            print("Current users in database:")
            for user in users:
                print(f"  {user}")
            
            # Force update all roles to uppercase
            cursor.execute("""
                UPDATE users 
                SET role = 'SUPER_ADMIN' 
                WHERE role IN ('super_admin', 'SUPER_ADMIN')
            """)
            conn.commit()
            
            cursor.execute("""
                UPDATE users 
                SET role = 'ADMIN' 
                WHERE role IN ('admin', 'ADMIN')
            """)
            conn.commit()
            
            cursor.execute("""
                UPDATE users 
                SET role = 'STUDENT' 
                WHERE role IN ('student', 'STUDENT')
            """)
            conn.commit()
            
            cursor.execute("""
                UPDATE users 
                SET role = 'GUARD' 
                WHERE role IN ('guard', 'GUARD')
            """)
            conn.commit()
            
            print("\nAfter fix:")
            cursor.execute("SELECT id, email, role FROM users")
            users = cursor.fetchall()
            for user in users:
                print(f"  {user}")
            
    finally:
        conn.close()

if __name__ == "__main__":
    check_and_fix()
