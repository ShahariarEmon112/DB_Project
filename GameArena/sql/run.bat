@echo off
REM Connect to pluggable database XEPDB1
E:\DB_project\dbhomeXE\bin\sqlplus.exe -S system/saikat112@localhost:1521/XEPDB1 @"%~dp0create_user.sql"
