#!/usr/bin/env python3
"""Delete and recreate admin user with correct role"""

import pymysql

DB_HOST = "localhost"
DB_PORT = 3306
DB_USER = "root"
DB_PASSWORD = ""
DB_NAME = "ojt_timelog"

def fix_admin():
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
            
            # Insert new admin with correct role value
            # Note: For MySQL ENUM, we need to use the exact value stored
            # The ENUM stores 'super_admin' (lowercase), but we need to use the uppercase enum name
            cursor.execute("""
                INSERT INTO users (id, email, password_hash, role, is_active, email_verified) 
                VALUES ('550e8400-e29b-41d4-a716-446655440001', 'admin@example.com', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4wqO.1LPMuLqLkzq', 'SUPER_ADMIN', TRUE, TRUE)
            """)
            conn.commit()
            print("[OK] Created new admin user with SUPER_ADMIN role")
            
            # Verify
            cursor.execute("SELECT id, email, role FROM users WHERE email = 'admin@example.com'")
            user = cursor.fetchone()
            print(f"  User: {user}")
            
    finally:
        conn.close()

if __name__ == "__main__":
    fix_admin()
