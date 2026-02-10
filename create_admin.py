#!/usr/bin/env python3
"""Create admin user with proper password hash"""

import pymysql
import bcrypt

DB_HOST = "localhost"
DB_PORT = 3306
DB_USER = "root"
DB_PASSWORD = ""
DB_NAME = "ojt_timelog"

def create_admin():
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
            # Delete existing admin
            cursor.execute("DELETE FROM users WHERE email = 'admin@example.com'")
            conn.commit()
            print("[OK] Deleted existing admin user")
            
            # Generate new password hash for 'Admin@123'
            password = "Admin@123"
            salt = bcrypt.gensalt()
            password_hash = bcrypt.hashpw(password.encode('utf-8'), salt).decode('utf-8')
            print(f"[OK] Generated new password hash: {password_hash}")
            
            # Insert new admin
            cursor.execute("""
                INSERT INTO users (id, email, password_hash, role, is_active, email_verified) 
                VALUES ('550e8400-e29b-41d4-a716-446655440001', 'admin@example.com', %s, 'super_admin', TRUE, TRUE)
            """, (password_hash,))
            conn.commit()
            print("[OK] Created new admin user")
            
            # Verify
            cursor.execute("SELECT id, email, role FROM users WHERE email = 'admin@example.com'")
            user = cursor.fetchone()
            print(f"  User: {user}")
            
    finally:
        conn.close()

if __name__ == "__main__":
    create_admin()
