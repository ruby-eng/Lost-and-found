import subprocess
import os

# Read the SQL file
sql_file = r"C:\xampp\htdocs\Lost-and-found\database.sql"

with open(sql_file, 'r') as f:
    sql_content = f.read()

# Connect and execute SQL
try:
    # Try to import using mysql command
    process = subprocess.Popen(
        [r"C:\xampp\mysql\bin\mysql.exe", "-u", "root"],
        stdin=subprocess.PIPE,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        text=True
    )
    stdout, stderr = process.communicate(input=sql_content)
    
    if stderr:
        print(f"Error: {stderr}")
    else:
        print("Database imported successfully!")
        print(stdout)
except Exception as e:
    print(f"Error: {e}")
