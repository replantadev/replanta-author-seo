# Replanta Author SEO

Plugin de WordPress para mejorar el SEO de autores con Schema.org completo, auditoría de artículos, cajas de autor y artículos relacionados inteligentes.

## 🚀 Características

- **Schema.org Completo**: Marcado estructurado de Article, Person y Organization
- **Auditoría de Artículos**: Información de publicación, tiempo de lectura, palabras
- **Caja de Autor**: Biografía extendida, credenciales, redes sociales
- **Artículos Relacionados**: Sistema inteligente por tags, categorías y autor
- **Avatar Personalizado**: Sistema de upload sin depender de Gravatar
- **Auto-actualización**: Sistema de actualizaciones automáticas desde GitHub

## 📋 Requisitos

- WordPress 5.8 o superior
- PHP 7.4 o superior
- Composer (para desarrollo)

## 🔧 Instalación

### Instalación Manual

1. Descarga la última versión desde [Releases](https://github.com/replantadev/replanta-author-seo/releases)
2. Sube el archivo ZIP a WordPress en `Plugins > Añadir nuevo > Subir plugin`
3. Activa el plugin
4. Configura en `Ajustes > Author SEO`

### Instalación con Composer

```bash
composer require yahnis-elsts/plugin-update-checker
```

## ⚙️ Configuración

### 1. Configuración General

Ve a **Ajustes > Author SEO** y configura:

- ✅ Activar Schema.org
- ✅ Mostrar bloque de auditoría
- ✅ Mostrar caja de autor
- ✅ Mostrar artículos relacionados

### 2. Información de la Organización

Configura tu organización para el Schema.org Publisher:

- **Nombre de la Organización**: Tu empresa/blog
- **Logo**: Logo oficial (400x400px recomendado)

### 3. Configuración de Autores

Cada autor debe completar su perfil en **Usuarios > Tu Perfil**:

#### Información Profesional
- Cargo/Puesto
- Organización
- Biografía extendida
- Credenciales y logros
- Áreas de expertise
- Sitio web

#### Redes Sociales
- Twitter
- LinkedIn
- GitHub
- Facebook
- Instagram
- YouTube

### 4. Avatar Personalizado

En **Usuarios > Tu Perfil > Avatar Personalizado**:

1. Click en "Subir Avatar"
2. Selecciona imagen (400x400px recomendado)
3. Guarda cambios

El avatar reemplaza automáticamente a Gravatar.

## 📖 Uso

### Shortcodes Disponibles

#### Bloque de Auditoría
```php
[replanta_audit_box]
[replanta_audit_box post_id="123"]
```

#### Caja de Autor
```php
[replanta_author_box]
[replanta_author_box post_id="123"]
```

#### Artículos Relacionados
```php
[replanta_related_posts]
[replanta_related_posts post_id="123" limit="6"]
```

### Inserción Automática

Por defecto, si las opciones están activadas:

- **Audit Box**: Se inserta al inicio del contenido
- **Author Box**: Se inserta al final del contenido
- **Related Posts**: Se inserta al final del contenido

### Uso Programático

```php
// Obtener datos de autor
$author_data = Replanta_Author_Fields::get_author_data($user_id);

// Obtener avatar personalizado
$avatar_url = Replanta_Avatar_Uploader::get_author_avatar_url($user_id);

// Obtener posts relacionados
$related_posts_instance = Replanta_Related_Posts::instance();
$related = $related_posts_instance->get_related_posts($post_id, 6);
```

## 🎨 Schema.org

El plugin genera automáticamente tres tipos de Schema:

### Article Schema
```json
{
  "@type": "Article",
  "headline": "Título del artículo",
  "description": "Descripción",
  "datePublished": "2024-01-01",
  "dateModified": "2024-01-02",
  "author": {...},
  "publisher": {...},
  "image": {...},
  "keywords": ["tag1", "tag2"],
  "wordCount": 1500,
  "commentCount": 10
}
```

### Person Schema (Author)
```json
{
  "@type": "Person",
  "name": "Nombre Autor",
  "url": "https://sitio.com/author/nombre",
  "description": "Biografía",
  "jobTitle": "Cargo",
  "worksFor": {...},
  "sameAs": ["https://twitter.com/...", "https://linkedin.com/..."],
  "image": "https://sitio.com/avatar.jpg",
  "knowsAbout": ["SEO", "WordPress"]
}
```

### Organization Schema (Publisher)
```json
{
  "@type": "Organization",
  "name": "Mi Organización",
  "url": "https://sitio.com",
  "logo": {
    "@type": "ImageObject",
    "url": "https://sitio.com/logo.png"
  }
}
```

## 🧪 Validación de Schema

Valida el marcado generado en:

- [Google Rich Results Test](https://search.google.com/test/rich-results)
- [Schema.org Validator](https://validator.schema.org/)

## 🎯 Algoritmo de Artículos Relacionados

El sistema calcula un score por cada post:

- **Tags compartidos**: +3 puntos por tag
- **Categorías compartidas**: +2 puntos por categoría
- **Mismo autor**: +1 punto
- **Recencia**: +2 puntos (<30 días), +1 punto (<90 días)

Los posts con mayor score se muestran primero.

## 🔄 Actualizaciones

El plugin se actualiza automáticamente desde GitHub:

1. Se verifica la última release en GitHub
2. Si hay nueva versión, aparece en WordPress
3. Click en "Actualizar" instala la nueva versión

### Comprobación Manual

```bash
# En el servidor
cd wp-content/plugins/replanta-author-seo
git pull origin main
```

## 🛠️ Desarrollo

### Estructura del Proyecto

```
replanta-author-seo/
├── assets/
│   ├── css/
│   │   ├── frontend.css
│   │   └── admin.css
│   └── js/
│       ├── frontend.js
│       └── admin.js
├── includes/
│   ├── class-author-fields.php
│   ├── class-schema-generator.php
│   ├── class-article-audit.php
│   ├── class-related-posts.php
│   ├── class-admin-settings.php
│   └── class-avatar-uploader.php
├── vendor/
├── composer.json
├── replanta-author-seo.php
└── README.md
```

### Instalación para Desarrollo

```bash
# Clonar repositorio
git clone https://github.com/replantadev/replanta-author-seo.git

# Instalar dependencias
cd replanta-author-seo
composer install

# Activar en WordPress
wp plugin activate replanta-author-seo
```

### Coding Standards

El plugin sigue WordPress Coding Standards:

```bash
# Verificar código
phpcs --standard=WordPress replanta-author-seo.php includes/

# Auto-fix
phpcbf --standard=WordPress replanta-author-seo.php includes/
```

## 🐛 Debugging

### Modo Debug

Añade en `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Verificar Schema

1. Ve a **Ajustes > Author SEO > Schema.org**
2. Revisa la vista previa del Schema
3. Click en "Validar en Schema.org"

### Comprobar Assets

```bash
# Verificar que CSS/JS se cargan
curl -I https://tu-sitio.com/wp-content/plugins/replanta-author-seo/assets/css/frontend.css
```

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama feature (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Changelog

### v1.0.0 - 2024-01-XX
- ✨ Release inicial
- Schema.org completo (Article + Person + Organization)
- Auditoría de artículos
- Caja de autor con credenciales
- Artículos relacionados inteligentes
- Sistema de avatar personalizado
- Auto-actualización desde GitHub

## 📄 Licencia

GPL v2 o superior

## 👨‍💻 Autor

**Replanta Dev**
- GitHub: [@replantadev](https://github.com/replantadev)

## 🙏 Créditos

- [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) by Yahnis Elsts
- Schema.org por [Schema.org Community](https://schema.org/)

## 📧 Soporte

Para reportar bugs o solicitar features:

- [GitHub Issues](https://github.com/replantadev/replanta-author-seo/issues)

---

Hecho con ❤️ por [Replanta](https://replanta.com)
