# Changelog - Replanta Author SEO

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

# Changelog

## [1.2.1] - 2024-11-06

### Fixed
- **CRITICAL**: Ahora sí se añade el Schema Person completo al @graph cuando RankMath está activo
- Si RankMath no incluye Person schema, lo añadimos nosotros con todos los datos enriquecidos
- El Article schema ahora apunta correctamente al @id del Person (#person)
- Añadido método `generate_complete_author_schema()` para crear Person desde cero

### Changed
- Mejorada lógica de detección: primero enriquece Person existente, si no existe lo crea completo
- El flag `$author_schema_found` controla si añadir Person al @graph

## [1.2.0] - 2024-11-05

### Added
- **Compatibilidad completa con RankMath SEO**: Integración inteligente que enriquece el Schema de RankMath sin duplicar contenido
- Detección automática de RankMath activo
- Filtro `rank_math/json_ld` para enriquecer el Person Schema de RankMath con todos nuestros datos
- Enriquecimiento de Article Schema de RankMath (wordCount, articleSection)

### Changed
- El plugin ahora adapta su comportamiento según RankMath esté activo o no
- Si RankMath está activo: Enriquece su Schema existente (sin duplicados)
- Si RankMath NO está activo: Genera Schema completo propio

### Technical
- Método `is_rankmath_active()` para detectar RankMath
- Método `enrich_rankmath_schema()` para integrar con filtro de RankMath
- Método `enrich_author_schema()` para añadir nuestros datos Person al Schema de RankMath
- Prioridad 99 en filtro RankMath para ejecutar después de RankMath

## [1.1.0] - 2024-11-05

### ✨ Mejorado

#### Schema.org Person Ultra-Completo
- **@id y @context**: Identificadores únicos para el autor
- **hasOccupation**: Schema de ocupación profesional completo
- **hasCredential**: Credenciales educativas y profesionales como array de EducationalOccupationalCredential
- **interactionStatistic**: Contador de artículos publicados con WriteAction
- **breadcrumb**: Navegación estructurada BreadcrumbList del autor
- **contactPoint**: Punto de contacto profesional si email público disponible
- **alumniOf**: Marca de profesional verificado si tiene 3+ redes sociales
- **mainEntityOfPage**: ProfilePage si tiene website personal
- **disambiguatingDescription**: Descripción clara del título profesional
- **affiliation**: Afiliación a organización adicional
- **image**: Avatar con dimensiones (400x400) y caption
- **award**: Credenciales como premios y logros
- **nationality** y **gender**: Campos opcionales para mayor personalización

#### Diseño con Paleta Replanta
- **Variables CSS**: Implementación completa de la paleta Replanta
  - `--rep-green: #93F1C9` - Accents
  - `--rep-forest: #1E2F23` - Textos principales
  - `--rep-teal: #41999F` - Links, botones, iconos
  - `--rep-mint: #92F1CB` - Highlights y borders
  - `--rep-bg-light: #F7FBF9` - Backgrounds suaves
  - `--rep-text-secondary: #3B4B45` - Texto secundario
  - `--rep-text-tertiary: #547065` - Texto terciario
  - `--rep-border: #E6F3EF` - Bordes sutiles

#### Audit Box Minimal - Estilo Periódico
- **Sin backgrounds invasivos**: Eliminado gradiente morado/azul
- **Bordes sutiles**: Solo border-top y border-bottom con `rep-border`
- **Padding reducido**: De 24px a 12px vertical
- **Header oculto**: Sin título para look ultra-minimal
- **Typography pequeña**: 13px en todo el bloque
- **Layout inline**: Items fluyen horizontalmente como metadata
- **Iconos discretos**: 14px con opacity 0.5 en `rep-teal`
- **Responsive mejorado**: Stack vertical en mobile

#### Author Box con Paleta Replanta
- **Avatar border**: `rep-mint` (#92F1CB) en lugar de blanco
- **Job title color**: `rep-teal` en lugar de azul genérico
- **Botón CTA**: Background `rep-teal` con hover a `rep-forest`
- **Credentials border**: `rep-teal` en borde izquierdo
- **Expertise background**: Tint de `rep-green` con opacity
- **Social links hover**: `rep-teal` con sombra suave

#### Related Posts con Teal
- **Header border**: `rep-mint` en lugar de azul
- **Hover border**: `rep-mint` en cards
- **Title hover**: `rep-teal` en lugar de azul
- **Icons color**: `rep-teal` en metadata

### 🎨 Cambios de Diseño

- Eliminados todos los colores azules/morados genéricos
- Implementada paleta corporativa Replanta en todos los componentes
- Audit box transformado en diseño editorial minimal
- Mejora de contraste con `rep-forest` para textos principales
- Borders y backgrounds más sutiles y profesionales

## [1.0.0] - 2024-01-XX

### ✨ Añadido

#### Schema.org Completo
- Generación automática de Schema.org JSON-LD
- **Article Schema**: headline, description, datePublished, dateModified, keywords, wordCount, commentCount
- **Person Schema** (Author): name, url, jobTitle, worksFor, sameAs (redes sociales), image, knowsAbout
- **Organization Schema** (Publisher): name, url, logo
- Integración completa con datos del autor
- Validación compatible con Google Rich Results

#### Sistema de Campos de Autor
- 12 campos personalizados para autores:
  - **Profesionales**: job_title, organization, bio_extended, credentials, expertise_areas, website_url
  - **Redes Sociales**: twitter_url, linkedin_url, github_url, facebook_url, instagram_url, youtube_url
- Interfaz en perfil de usuario
- Sanitización y validación de URLs
- Columnas personalizadas en lista de usuarios (cargo, organización)
- Métodos estáticos para obtener datos: `get_author_data()`, `get_author_social_links()`

#### Auditoría de Artículos
- Bloque visual con información del artículo
- **Datos mostrados**:
  - Fecha de publicación
  - Fecha de última modificación
  - Tiempo de lectura estimado (palabras ÷ WPM configurable)
  - Contador de palabras
  - Categorías
  - Autor con cargo
- Diseño moderno con gradiente morado/azul
- Responsive mobile-first
- Inserción automática al inicio del contenido
- Shortcode: `[replanta_audit_box]`

#### Caja de Autor Avanzada
- Información completa del autor después del contenido
- **Componentes**:
  - Avatar personalizado (120x120px, circular)
  - Nombre con enlace a archivo de autor
  - Cargo y organización
  - Biografía extendida
  - Lista de credenciales y logros
  - Áreas de expertise destacadas
  - Enlaces a redes sociales con iconos SVG
  - Botón "Ver todos los artículos de [Autor]"
- Diseño en card con borde y sombra
- Responsive con layout flexible
- Shortcode: `[replanta_author_box]`

#### Artículos Relacionados Inteligentes
- Sistema de scoring avanzado para relevancia
- **Algoritmo de puntuación**:
  - Tags compartidos: +3 puntos por tag
  - Categorías compartidas: +2 puntos por categoría
  - Mismo autor: +1 punto
  - Recencia: +2 puntos (<30 días), +1 punto (<90 días)
- Grid responsive de artículos (2-3 columnas)
- **Información por artículo**:
  - Thumbnail con aspect ratio 16:9
  - Título con hover effect
  - Excerpt de 20 palabras
  - Metadata: autor, fecha, tiempo de lectura
- Hover effects con elevación y zoom en imagen
- Widget de WordPress incluido
- Shortcode: `[replanta_related_posts limit="6"]`

#### Sistema de Avatar Personalizado
- Upload de avatares sin depender de Gravatar
- Integración con Media Library de WordPress
- **Funcionalidades**:
  - Subir imagen desde perfil de usuario
  - Preview en tiempo real (150x150px circular)
  - Botón de eliminación
  - Fallback automático a Gravatar si no hay avatar custom
  - Reemplazo en todas las funciones de WordPress (`get_avatar`, `get_avatar_url`)
- Eliminación automática al borrar usuario
- Tamaño recomendado: 400x400px

#### Panel de Administración
- Página de configuración en Ajustes > Author SEO
- **3 Tabs**:
  - General: Activar/desactivar funcionalidades
  - Schema.org: Vista previa del Schema con validadores
  - Ayuda: Documentación integrada
- **Opciones configurables**:
  - enable_schema (Schema.org on/off)
  - enable_audit_box (Auditoría on/off)
  - enable_author_box (Caja de autor on/off)
  - enable_related_posts (Related posts on/off)
  - organization_name (Nombre del publisher)
  - organization_logo (Logo del publisher)
  - reading_speed_wpm (Velocidad de lectura: 100-400 WPM)
  - related_posts_count (Número de relacionados: 3-12)
- Vista previa de Schema en tiempo real
- Enlaces directos a validadores de Google y Schema.org
- Admin notices para configuraciones incompletas

#### Assets y Estilos
- **CSS Frontend** (frontend.css):
  - Audit box con gradiente y backdrop filter
  - Author box con card design moderno
  - Related posts con grid responsive
  - Transiciones y hover effects suaves
  - Mobile-first responsive design
  - BEM methodology
- **CSS Admin** (admin.css):
  - Estilos para avatar uploader
  - Estilos para image uploader genérico
  - Schema preview con syntax highlighting
  - Help page con secciones colapsables
- **JS Frontend** (frontend.js):
  - Lazy loading de imágenes relacionadas
  - Smooth scroll para enlaces internos
  - Analytics tracking para author box clicks
  - SVG icons inline (18 iconos incluidos)
- **JS Admin** (admin.js):
  - Media uploader integration para avatar
  - Media uploader genérico para settings
  - Preview en tiempo real
  - Confirmación de eliminación

#### Auto-Actualización
- Sistema completo con yahnis-elsts/plugin-update-checker v5.6
- Comprobación automática desde GitHub releases
- Notificaciones nativas de WordPress
- Actualización con un click desde dashboard
- Compatible con multi-site

#### Documentación
- README.md completo con ejemplos de uso
- DEPLOYMENT.md con guía de publicación
- ICONS.md con documentación de iconos SVG
- Comentarios PHPDoc en todo el código
- Changelog detallado

#### Compatibilidad
- WordPress 5.8+
- PHP 7.4+
- Multisite compatible
- Gutenberg compatible
- Classic Editor compatible
- WPML/Polylang ready

### 🛠️ Técnico

#### Arquitectura
- Patrón Singleton para todas las clases
- Separación de responsabilidades (SRP)
- Namespace seguido: `Replanta\AuthorSEO`
- Autoload PSR-4 con Composer
- Hooks de WordPress optimizados

#### Performance
- Lazy loading de imágenes
- No-queries en WP_Query para related posts
- Cache-busting con versión del plugin
- Minificación lista para producción
- Menos de 100KB total (sin vendor)

#### Seguridad
- Sanitización de todos los inputs
- Validación de URLs con `esc_url_raw`
- Nonces en formularios de admin
- Capability checks (`manage_options`, `edit_user`)
- XSS protection con `esc_html`, `esc_attr`
- SQL injection prevention (uso de WP_Query)

#### SEO
- Schema.org completo y validado
- Marcado semántico HTML5
- Meta tags OpenGraph listos
- Velocidad optimizada
- Mobile-first design
- Accessibility (ARIA labels)

### 📦 Dependencias

- yahnis-elsts/plugin-update-checker: ^5.0
- WordPress: >=5.8
- PHP: >=7.4

### 🎯 Estructura de Archivos

```
replanta-author-seo/
├── assets/
│   ├── css/
│   │   ├── admin.css (179 líneas)
│   │   └── frontend.css (434 líneas)
│   ├── js/
│   │   ├── admin.js (145 líneas)
│   │   └── frontend.js (166 líneas)
│   └── ICONS.md
├── includes/
│   ├── class-admin-settings.php (465 líneas)
│   ├── class-article-audit.php (245 líneas)
│   ├── class-author-fields.php (287 líneas)
│   ├── class-avatar-uploader.php (234 líneas)
│   ├── class-related-posts.php (343 líneas)
│   └── class-schema-generator.php (242 líneas)
├── vendor/ (Composer)
├── .gitignore
├── CHANGELOG.md
├── composer.json
├── composer.lock
├── DEPLOYMENT.md
├── README.md
└── replanta-author-seo.php (177 líneas)
```

**Total**: ~2,917 líneas de código (sin vendor)

### 🚀 Próximas Versiones

#### v1.1.0 (Planificado)
- [ ] Soporte para Custom Post Types
- [ ] Integración con ACF
- [ ] Export/Import de configuración
- [ ] Breadcrumbs Schema
- [ ] FAQ Schema

#### v1.2.0 (Planificado)
- [ ] Análisis de contenido SEO
- [ ] Sugerencias de artículos relacionados en editor
- [ ] Dashboard widget con estadísticas
- [ ] Integración con Google Analytics

---

## Notas de Versión

### Compatibilidad Probada
- WordPress 6.4.x ✅
- PHP 8.2 ✅
- PHP 7.4 ✅

### Breaking Changes
- Ninguno (primera versión)

### Migraciones
- No requiere migraciones

### Instrucciones de Actualización
1. Backup de la base de datos
2. Actualizar plugin vía dashboard
3. Verificar que Schema.org sigue funcionando
4. Regenerar opciones si es necesario

---

**Fecha de Release**: Pendiente de publicación

[1.0.0]: https://github.com/replantadev/replanta-author-seo/releases/tag/v1.0.0
