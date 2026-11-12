@echo off
REM Start the RentFlow Laravel app.
REM
REM   Log in: dev@example.test / secret123   (owner)
REM           ct@example.test / secret123   (caretaker)
REM           rt@example.test / secret123   (renter)
REM
REM Serves the current checkout at http://127.0.0.1:8000.
REM For Apache, configure apache-rentflow.conf with this checkout's path.
REM
REM Press Ctrl+C to stop.

setlocal
set "DIR=%~dp0laravel\backend"
set "FRONTEND=%~dp0laravel\frontend"
set "PHP=php"

where php >NUL 2>NUL
if errorlevel 1 (
    if exist "C:\xampp\php\php.exe" (
        set "PHP=C:\xampp\php\php.exe"
        set "PATH=C:\xampp\php;%PATH%"
    ) else (
        echo  ERROR: PHP was not found. Add PHP to PATH or install XAMPP.
        pause
        exit /b 1
    )
)

if exist "%ProgramData%\ComposerSetup\bin" (
    set "PATH=%ProgramData%\ComposerSetup\bin;%PATH%"
)

echo.
echo  Fallback server: http://127.0.0.1:8000
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
    echo  ERROR: Laravel dependencies missing. Run:
    echo      cd /d "%DIR%"
    echo      composer install
    pause
    exit /b 1
)

if not exist "%DIR%\public\build\manifest.json" (
    where npm >NUL 2>NUL
    if errorlevel 1 (
        echo  ERROR: frontend assets are missing and npm was not found.
        echo  Install Node.js, then run npm install and npm run build in:
        echo      "%FRONTEND%"
        pause
        exit /b 1
    )
    echo  Building frontend assets...
    cd /d "%FRONTEND%"
    call npm run build
    if errorlevel 1 (
        echo  ERROR: frontend build failed.
        pause
        exit /b 1
    )
)

cd /d "%DIR%"
"%PHP%" artisan serve --host=127.0.0.1 --port=8000