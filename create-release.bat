@echo off
echo ========================================
echo Creando Release de Replanta Author SEO
echo ========================================
echo.

REM Leer la version del archivo principal
set VERSION=1.2.1
echo Version: %VERSION%
echo.

REM Crear directorio temporal
if exist release-temp rmdir /s /q release-temp
mkdir release-temp
mkdir release-temp\replanta-author-seo

echo Copiando archivos...

REM Copiar archivos necesarios
xcopy /E /I /Y assets release-temp\replanta-author-seo\assets
xcopy /E /I /Y includes release-temp\replanta-author-seo\includes
xcopy /E /I /Y vendor release-temp\replanta-author-seo\vendor
copy /Y replanta-author-seo.php release-temp\replanta-author-seo\
copy /Y README.md release-temp\replanta-author-seo\
copy /Y CHANGELOG.md release-temp\replanta-author-seo\
copy /Y composer.json release-temp\replanta-author-seo\

echo.
echo Creando ZIP...

REM Crear ZIP (requiere PowerShell)
powershell -command "Compress-Archive -Path 'release-temp\replanta-author-seo' -DestinationPath 'replanta-author-seo-v%VERSION%.zip' -Force"

echo.
echo Limpiando temporales...
rmdir /s /q release-temp

echo.
echo ========================================
echo ZIP creado: replanta-author-seo-v%VERSION%.zip
echo ========================================
echo.
echo SIGUIENTE PASO:
echo 1. Ve a https://github.com/replantadev/replanta-author-seo/releases/new
echo 2. Selecciona el tag: v%VERSION%
echo 3. Titulo: Release v%VERSION%
echo 4. Descripcion: Copia el changelog de CHANGELOG.md
echo 5. Adjunta el archivo: replanta-author-seo-v%VERSION%.zip
echo 6. Publica el release
echo.
pause
