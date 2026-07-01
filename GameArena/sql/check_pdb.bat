@echo off
REM First check containers
E:\DB_project\dbhomeXE\bin\sqlplus.exe -S system/saikat112@localhost:1521/XE -L -C "SET PAGESIZE 0 FEEDBACK OFF VERIFY OFF HEADING OFF ECHO OFF" -C "SELECT name, open_mode FROM v$pdbs;" -C "EXIT"
