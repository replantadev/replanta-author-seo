@echo off
REM Script de creación de release para Replanta Author SEO
REM Windows Batch Version

echo ========================================
echo Replanta Author SEO - Build Script
echo ========================================
echo.

REM Variables
set PLUGIN_SLUG=replanta-author-seo
set BUILD_DIR=build
set TEMP_DIR=%BUILD_DIR%\%PLUGIN_SLUG%

REM Leer versión del plugin
for /f "tokens=2" %%a in ('findstr /c:"Version:" replanta-author-seo.php') do set VERSION=%%a

echo Plugin: %PLUGIN_SLUG%
echo Version: %VERSION%
echo.

REM Limpiar build anterior
echo [1/5] Limpiando build anterior...
if exist %BUILD_DIR% rmdir /s /q %BUILD_DIR%
mkdir %BUILD_DIR%
mkdir %TEMP_DIR%

REM Copiar archivos
echo [2/5] Copiando archivos del plugin...
xcopy /E /I /Y assets %TEMP_DIR%\assets
xcopy /E /I /Y includes %TEMP_DIR%\includes
xcopy /Y *.php %TEMP_DIR%\
xcopy /Y *.md %TEMP_DIR%\
xcopy /Y composer.json %TEMP_DIR%\

REM Instalar dependencias de producción
echo [3/5] Instalando dependencias de Composer...
cd %TEMP_DIR%
call composer install --no-dev --optimize-autoloader --quiet
cd ..\..

REM Limpiar archivos innecesarios
echo [4/5] Limpiando archivos innecesarios...
del /q %TEMP_DIR%\VERIFICACION.md
del /q %TEMP_DIR%\DEPLOYMENT.md
del /q %TEMP_DIR%\assets\ICONS.md

REM Crear ZIP
echo [5/5] Creando archivo ZIP...
powershell Compress-Archive -Path %TEMP_DIR%\* -DestinationPath %BUILD_DIR%\%PLUGIN_SLUG%-v%VERSION%.zip -Force

echo.
echo ========================================
echo Build completado!
echo ========================================
echo.
echo Archivo creado: %BUILD_DIR%\%PLUGIN_SLUG%-v%VERSION%.zip
echo.
echo Proximos pasos:
echo 1. Probar el plugin en WordPress local
echo 2. Validar Schema.org
echo 3. Subir a GitHub Releases
echo.
pause
