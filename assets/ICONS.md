# Replanta Author SEO - SVG Icons

Los iconos SVG se inyectan automáticamente en el DOM mediante JavaScript (`assets/js/frontend.js`).

## Iconos Incluidos

### Audit Box
- `icon-calendar` - Fechas de publicación
- `icon-clock` - Tiempo de lectura
- `icon-text` - Contador de palabras
- `icon-folder` - Categorías
- `icon-user` - Información del autor
- `icon-update` - Fecha de actualización

### Author Box
- `icon-building` - Organización
- `icon-arrow-right` - Ver más posts

### Related Posts
- `icon-book` - Título de la sección

### Redes Sociales
- `icon-twitter`
- `icon-linkedin`
- `icon-github`
- `icon-facebook`
- `icon-instagram`
- `icon-youtube`

## Uso

Los iconos se utilizan mediante SVG `<use>`:

```html
<svg width="16" height="16" fill="currentColor">
    <use href="#icon-calendar"/>
</svg>
```

## Formato

Todos los iconos son símbolos SVG basados en Feather Icons con algunas adaptaciones custom para social media.

## Personalización

Para añadir nuevos iconos, edita `assets/js/frontend.js` y añade el `<symbol>` correspondiente dentro del SVG principal.
