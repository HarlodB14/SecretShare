@echo off
setlocal

REM Starts the scheduler and queue worker in separate windows.
REM Run this file from the project root.

start "Laravel Scheduler" cmd /k "php artisan schedule:work"
start "Laravel Queue Worker" cmd /k "php artisan queue:work --queue=default --tries=3"

echo Scheduler and queue worker started.
echo Keep both windows open while testing expiration.

endlocal

