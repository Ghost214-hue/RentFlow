@echo off
REM Start the RentFlow Laravel app.
REM
REM   Log in: dev@example.test / secret123   (owner)
REM           ct@example.test / secret123   (caretaker)
REM           rt@example.test / secret123   (renter)
REM
REM PREFERRED: Apache on http://127.0.0.1:8080
REM   Apache is threaded, so a browser's parallel requests are handled at once.
REM   The vhost is installed at C:\xampp\apache\conf\extra\httpd-rentflow.conf
REM   and is included from httpd.conf. To activate it, restart Apache from the
REM   XAMPP Control Panel (right-click it, Run as Administrator).
REM
REM FALLBACK: artisan serve on http://127.0.0.1:8000
REM   PHP's built-in server is SINGLE-THREADED and Windows cannot fork workers
REM   for it, so a page's parallel asset requests serialise behind one another
REM   and can hit PHP's 30 second limit ("Maximum execution time of 30 seconds
REM   exceeded"). It works, but it is not as reliable under load.
REM
REM Press Ctrl+C to stop.

setlocal
set PHP=C:\tools\php83\php.exe
set DIR=C:\xampp\htdocs\RentFlow\laravel

echo.
echo  Fallback server: http://127.0.0.1:8000
echo  Preferred server: http://127.0.0.1:8080  (Apache, threaded)
echo  Sign in: dev@example.test / secret123
echo.

REM Make sure MariaDB is up; XAMPP usually has it running already.
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I "mysqld.exe" >NUL
if errorlevel 1 (
    echo  WARNING: mysqld.exe is not running. Start MySQL in the XAMPP control
    echo           panel, otherwise the app will fail to load any page.
    echo.
)

if not exist "%DIR%\vendor\autoload.php" (
    echo  ERROR: dependencies missing. Run:
    echo      cd C:\xampp\htdocs\RentFlow\laravel
    echo      C:\tools\php83\php.exe C:\tools\php83\composer.phar install
    pause
    exit /b 1
)

if not exist "%DIR%\public\build\manifest.json" (
    echo  Building frontend assets...
    cd /d "%DIR%"
    call npm run build
)

cd /d "%DIR%"
"%PHP%" artisan serve --host=127.0.0.1 --port=8000