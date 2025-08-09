@echo off
if "%1" == "h" goto begin
start /min "" /B "%~f0" h
exit /b
:begin
cd /d C:\Development\php\matchoracle-be
php artisan schedule:run >> storage/logs/scheduler.log 2>&1