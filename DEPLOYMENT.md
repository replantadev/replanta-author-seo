# Instrucciones de Deployment - Replanta Author SEO

## 📦 Crear Release para Distribución

### 1. Preparar Versión

```bash
# 1. Actualizar versión en archivos principales
# - replanta-author-seo.php (Version: x.x.x)
# - replanta-author-seo.php (REPLANTA_AUTHOR_SEO_VERSION)
# - README.md (Changelog)

# 2. Commit cambios
git add .
git commit -m "Release v1.0.0"
git push origin main
```

### 2. Crear Tag y Release en GitHub

```bash
# Crear tag
git tag -a v1.0.0 -m "Release v1.0.0 - Initial release"
git push origin v1.0.0
```

En GitHub:
1. Ve a **Releases > Draft a new release**
2. Tag: `v1.0.0`
3. Title: `v1.0.0 - Initial Release`
4. Descripción: Copiar changelog
5. Adjuntar ZIP del plugin
6. **Publish release**

### 3. Generar ZIP del Plugin

```bash
# Método 1: Manual
cd wp-content/plugins
zip -r replanta-author-seo.zip replanta-author-seo/ \
  -x "*.git*" \
  -x "*node_modules*" \
  -x "*.DS_Store" \
  -x "*composer.lock"

# Método 2: Script automatizado
cd replanta-author-seo
bash create-release.sh
```

### Script de Release Automático

Crea `create-release.sh`:

```bash
#!/bin/bash

# Variables
PLUGIN_SLUG="replanta-author-seo"
VERSION=$(grep "Version:" replanta-author-seo.php | awk '{print $3}')
BUILD_DIR="build"
DEST_DIR="$BUILD_DIR/$PLUGIN_SLUG"

# Limpiar build anterior
rm -rf $BUILD_DIR
mkdir -p $DEST_DIR

# Copiar archivos necesarios
rsync -av --progress ./ $DEST_DIR \
  --exclude=".git" \
  --exclude=".gitignore" \
  --exclude="node_modules" \
  --exclude="build" \
  --exclude="*.log" \
  --exclude=".DS_Store" \
  --exclude="composer.lock" \
  --exclude="create-release.sh"

# Instalar dependencias de producción
cd $DEST_DIR
composer install --no-dev --optimize-autoloader

# Volver y crear ZIP
cd ../../
zip -r "$PLUGIN_SLUG-v$VERSION.zip" $PLUGIN_SLUG/

echo "✅ Release creado: $PLUGIN_SLUG-v$VERSION.zip"
```

## 🚀 Deployment a WordPress

### Opción 1: Via Dashboard

1. WordPress Admin > Plugins > Añadir nuevo
2. Subir plugin
3. Seleccionar `replanta-author-seo.zip`
4. Activar

### Opción 2: Via SFTP

```bash
# 1. Subir carpeta completa
sftp usuario@servidor.com
put -r replanta-author-seo /wp-content/plugins/

# 2. Instalar dependencias en servidor
ssh usuario@servidor.com
cd /ruta/wordpress/wp-content/plugins/replanta-author-seo
composer install --no-dev

# 3. Activar plugin
wp plugin activate replanta-author-seo
```

### Opción 3: Via Git (Desarrollo)

```bash
# En el servidor
cd wp-content/plugins
git clone https://github.com/replantadev/replanta-author-seo.git
cd replanta-author-seo
composer install --no-dev
wp plugin activate replanta-author-seo
```

## 🔄 Configurar Auto-Updates

El plugin ya incluye auto-actualización desde GitHub. Los usuarios recibirán notificaciones automáticamente cuando haya nuevas releases.

### Verificar Auto-Updates

```php
// En replanta-author-seo.php
$updateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
    'https://github.com/replantadev/replanta-author-seo',
    __FILE__,
    'replanta-author-seo'
);
```

### Forzar Comprobación Manual

```bash
# Via WP-CLI
wp transient delete update_plugins
wp plugin update replanta-author-seo
```

## 🧪 Testing Pre-Release

### 1. Test Local

```bash
# Instalar en entorno local
wp plugin install replanta-author-seo.zip --activate

# Verificar funcionalidad
wp eval "var_dump(class_exists('Replanta_Author_SEO'));"
```

### 2. Test Schema.org

1. Publicar post de prueba
2. Visitar URL del post
3. Ver código fuente y buscar `<script type="application/ld+json">`
4. Copiar JSON
5. Validar en https://validator.schema.org/

### 3. Test Styles

```bash
# Verificar que CSS se carga
curl -I https://tu-sitio.com/wp-content/plugins/replanta-author-seo/assets/css/frontend.css
```

## 📋 Checklist de Release

Antes de publicar nueva versión:

- [ ] Actualizar número de versión en `replanta-author-seo.php`
- [ ] Actualizar constante `REPLANTA_AUTHOR_SEO_VERSION`
- [ ] Añadir changelog en `README.md`
- [ ] Ejecutar `composer install --no-dev`
- [ ] Probar en entorno local
- [ ] Validar Schema.org
- [ ] Verificar que CSS/JS cargan correctamente
- [ ] Crear tag en Git
- [ ] Crear Release en GitHub con ZIP
- [ ] Probar auto-actualización desde WordPress
- [ ] Verificar compatibilidad con última versión de WordPress

## 🐛 Rollback

Si hay problemas con una release:

```bash
# Opción 1: Desactivar plugin
wp plugin deactivate replanta-author-seo

# Opción 2: Volver a versión anterior
cd wp-content/plugins/replanta-author-seo
git checkout v0.9.0
composer install --no-dev

# Opción 3: Eliminar y reinstalar versión anterior
wp plugin delete replanta-author-seo
wp plugin install https://github.com/replantadev/replanta-author-seo/releases/download/v0.9.0/replanta-author-seo.zip --activate
```

## 📊 Monitoreo Post-Deploy

```bash
# Verificar logs de errores
tail -f /ruta/wordpress/wp-content/debug.log | grep replanta

# Verificar queries SQL (Query Monitor plugin)
wp plugin install query-monitor --activate

# Verificar performance
wp plugin install p3-profiler --activate
```

## 🔐 Seguridad

Antes de deploy en producción:

```bash
# Verificar permisos
chmod 755 replanta-author-seo
chmod 644 replanta-author-seo/*.php

# Escanear vulnerabilidades
wp plugin verify-checksums replanta-author-seo
```

---

## 🎯 Quick Deploy Guide

```bash
# 1. Preparar release
git add .
git commit -m "Release v1.0.0"
git push origin main
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0

# 2. Crear ZIP
bash create-release.sh

# 3. Subir a GitHub Releases
# (Manual via interfaz web)

# 4. Los usuarios recibirán auto-update
```

---

**Importante**: Siempre testear en staging antes de producción.
