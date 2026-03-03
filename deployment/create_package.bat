@echo off
REM Deployment Package Creator for Windows
REM Creates a deployment zip file for Hostinger

echo ========================================
echo Laravel Deployment Package Creator
echo ========================================
echo.

set "PACKAGE_NAME=ojt_timelog_deploy_%date:~-4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%"
set "PACKAGE_NAME=%PACKAGE_NAME: =0%"
set "DEPLOY_DIR=deploy_temp"

echo Creating deployment package...
echo Package: %PACKAGE_NAME%
echo.

REM Create temporary deployment directory
if exist "%DEPLOY_DIR%" (
    echo Removing old deployment directory...
    rmdir /s /q "%DEPLOY_DIR%"
)

mkdir "%DEPLOY_DIR%"
mkdir "%DEPLOY_DIR%\app"
mkdir "%DEPLOY_DIR%\bootstrap"
mkdir "%DEPLOY_DIR%\config"
mkdir "%DEPLOY_DIR%\database"
mkdir "%DEPLOY_DIR%\database\migrations"
mkdir "%DEPLOY_DIR%\database\seeders"
mkdir "%DEPLOY_DIR%\public"
mkdir "%DEPLOY_DIR%\resources"
mkdir "%DEPLOY_DIR%\resources\views"
mkdir "%DEPLOY_DIR%\resources\views\layouts"
mkdir "%DEPLOY_DIR%\resources\views\admin"
mkdir "%DEPLOY_DIR%\resources\views\auth"
mkdir "%DEPLOY_DIR%\resources\views\guard"
mkdir "%DEPLOY_DIR%\resources\views\student"
mkdir "%DEPLOY_DIR%\routes"
mkdir "%DEPLOY_DIR%\storage"
mkdir "%DEPLOY_DIR%\storage\app"
mkdir "%DEPLOY_DIR%\storage\framework"
mkdir "%DEPLOY_DIR%\storage\logs"

echo Copying files...
xcopy /E /I /Y app "%DEPLOY_DIR%\app"
xcopy /E /I /Y bootstrap "%DEPLOY_DIR%\bootstrap"
xcopy /E /I /Y config "%DEPLOY_DIR%\config"
xcopy /E /I /Y database "%DEPLOY_DIR%\database"
xcopy /E /I /Y public "%DEPLOY_DIR%\public" ^
  /EXCLUDE:deployment\deploy_exclude.txt
xcopy /E /I /Y resources "%DEPLOY_DIR%\resources"
xcopy /E /I /Y routes "%DEPLOY_DIR%\routes"

REM Copy essential files
copy artisan "%DEPLOY_DIR%\"
copy composer.json "%DEPLOY_DIR%\"
copy .gitattributes "%DEPLOY_DIR%\"

REM Copy deployment files
copy deployment\.env.production "%DEPLOY_DIR%\.env.production"
copy deployment\.htaccess "%DEPLOY_DIR%\public\.htaccess"

echo.
echo Creating zip package...
powershell -Command "Compress-Archive -Path '%DEPLOY_DIR%\*' -DestinationPath '%PACKAGE_NAME%.zip' -Force"

echo.
echo Cleaning up...
rmdir /s /q "%DEPLOY_DIR%"

echo.
echo ========================================
echo Deployment package created successfully!
echo ========================================
echo.
echo Package file: %PACKAGE_NAME%.zip
echo.
echo Upload this file to your Hostinger server,
extract it, and follow the deployment guide.
echo.
echo Next steps:
echo 1. Upload %PACKAGE_NAME%.zip to server
echo 2. Extract to your domain's public folder
echo 3. Follow DEPLOYMENT_GUIDE.md instructions
echo.
pause
