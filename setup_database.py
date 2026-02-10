#!/usr/bin/env python3
"""Initialize the database schema for OJT Time Logging System"""

import pymysql

# Database connection settings
DB_HOST = "localhost"
DB_PORT = 3306
DB_USER = "root"
DB_PASSWORD = ""  # WAMP default: no password
DB_NAME = "ojt_timelog"

def init_database():
    """Create database and tables"""
    
    # First connect without database to create it
    conn = pymysql.connect(
        host=DB_HOST,
        port=DB_PORT,
        user=DB_USER,
        password=DB_PASSWORD,
        charset="utf8mb4"
    )
    
    try:
        with conn.cursor() as cursor:
            # Create database
            cursor.execute(f"CREATE DATABASE IF NOT EXISTS {DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")
            print(f"[OK] Database '{DB_NAME}' created or already exists")
            
        conn.commit()
        conn.close()
        
        # Now connect to the database and create tables
        conn = pymysql.connect(
            host=DB_HOST,
            port=DB_PORT,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME,
            charset="utf8mb4"
        )
        
        with conn.cursor() as cursor:
            # Read and execute schema
            with open("backend/migrations/001_init_schema.sql", "r", encoding="utf-8") as f:
                sql = f.read()
            
            # Split by CREATE TABLE and execute each statement
            statements = sql.split(";")
            for statement in statements:
                statement = statement.strip()
                if statement and not statement.startswith("--") and "CREATE DATABASE" not in statement:
                    try:
                        cursor.execute(statement)
                        conn.commit()
                    except Exception as e:
                        if "already exists" in str(e).lower():
                            continue
                        print(f"Warning: {e}")
            
            print("[OK] Database schema initialized successfully")
            
            # Create default admin user if not exists
            cursor.execute("SELECT id FROM users WHERE email = 'admin@ojt-tlms.test'")
            if not cursor.fetchone():
                # Password: Admin@123 (hashed)
                cursor.execute("""
                    INSERT INTO users (id, email, password_hash, role, is_active, email_verified) 
                    VALUES (UUID(), 'admin@ojt-tlms.test', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4wqO.1LPMuLqLkzq', 'super_admin', TRUE, TRUE)
                """)
                conn.commit()
                print("[OK] Default admin user created (email: admin@ojt-tlms.test, password: Admin@123)")
            else:
                print("[OK] Admin user already exists")
                
    finally:
        conn.close()

if __name__ == "__main__":
    init_database()
