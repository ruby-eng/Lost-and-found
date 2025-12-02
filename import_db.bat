@echo off
cd C:\xampp\mysql\bin
mysql -u root < C:\xampp\htdocs\Lost-and-found\database.sql
echo.
echo Database imported successfully!
pause
