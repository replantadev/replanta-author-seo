<?php
/**
 * Plugin Name: Replanta Author SEO
 * Plugin URI: https://replanta.net
 * Description: Sistema completo de autoría SEO con Schema.org Article+Author+Organization, auditoría de artículos, related posts inteligente y gestión avanzada de autores
 * Version: 1.2.1
 * Author: Replanta
 * Author URI: https://replanta.net
 * License: GPL v2 or later
 * Text Domain: replanta-author-seo
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Constantes del plugin
define('REPLANTA_AUTHOR_SEO_VERSION', '1.2.1');
define('REPLANTA_AUTHOR_SEO_FILE', __FILE__);
define('REPLANTA_AUTHOR_SEO_DIR', plugin_dir_path(__FILE__));
define('REPLANTA_AUTHOR_SEO_URL', plugin_dir_url(__FILE__));
define('REPLANTA_AUTHOR_SEO_BASENAME', plugin_basename(__FILE__));

// Autoload con Composer
if (file_exists(REPLANTA_AUTHOR_SEO_DIR . 'vendor/autoload.php')) {
    require_once REPLANTA_AUTHOR_SEO_DIR . 'vendor/autoload.php';
}

// Cargar archivos principales
require_once REPLANTA_AUTHOR_SEO_DIR . 'includes/class-author-fields.php';
require_once REPLANTA_AUTHOR_SEO_DIR . 'includes/class-schema-generator.php';
require_once REPLANTA_AUTHOR_SEO_DIR . 'includes/class-article-audit.php';
require_once REPLANTA_AUTHOR_SEO_DIR . 'includes/class-related-posts.php';
require_once REPLANTA_AUTHOR_SEO_DIR . 'includes/class-admin-settings.php';
require_once REPLANTA_AUTHOR_SEO_DIR . 'includes/class-avatar-uploader.php';

/**
 * Clase principal del plugin
 */
class Replanta_Author_SEO {
    
    private static $instance = null;
    
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Inicializar componentes
        add_action('plugins_loaded', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Activación/Desactivación
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // Sistema de actualizaciones
        $this->init_updater();
    }
    
    public function init() {
        // Inicializar módulos
        Replanta_Author_Fields::instance();
        Replanta_Schema_Generator::instance();
        Replanta_Article_Audit::instance();
        Replanta_Related_Posts::instance();
        Replanta_Admin_Settings::instance();
        Replanta_Avatar_Uploader::instance();
        
        // Cargar traducciones
        load_plugin_textdomain('replanta-author-seo', false, dirname(REPLANTA_AUTHOR_SEO_BASENAME) . '/languages');
    }
    
    public function enqueue_assets() {
        // CSS Frontend
        wp_enqueue_style(
            'replanta-author-seo',
            REPLANTA_AUTHOR_SEO_URL . 'assets/css/frontend.css',
            [],
            REPLANTA_AUTHOR_SEO_VERSION
        );
        
        // JS Frontend (si necesario)
        wp_enqueue_script(
            'replanta-author-seo',
            REPLANTA_AUTHOR_SEO_URL . 'assets/js/frontend.js',
            ['jquery'],
            REPLANTA_AUTHOR_SEO_VERSION,
            true
        );
    }
    
    public function enqueue_admin_assets($hook) {
        // Solo en páginas del plugin y perfil de usuario
        if (strpos($hook, 'replanta-author-seo') !== false || $hook === 'profile.php' || $hook === 'user-edit.php') {
            wp_enqueue_style(
                'replanta-author-seo-admin',
                REPLANTA_AUTHOR_SEO_URL . 'assets/css/admin.css',
                [],
                REPLANTA_AUTHOR_SEO_VERSION
            );
            
            wp_enqueue_script(
                'replanta-author-seo-admin',
                REPLANTA_AUTHOR_SEO_URL . 'assets/js/admin.js',
                ['jquery'],
                REPLANTA_AUTHOR_SEO_VERSION,
                true
            );
            
            // Media uploader para avatares
            wp_enqueue_media();
        }
    }
    
    public function activate() {
        // Crear opciones por defecto
        $default_options = [
            'enable_schema' => 1,
            'enable_audit_box' => 1,
            'enable_related_posts' => 1,
            'enable_author_box' => 1,
            'organization_name' => get_bloginfo('name'),
            'organization_logo' => '',
            'organization_url' => home_url(),
            'related_posts_count' => 3,
            'reading_speed_wpm' => 200,
        ];
        
        add_option('replanta_author_seo_options', $default_options);
        
        // Log activación
        error_log('[Replanta Author SEO] Plugin activado v' . REPLANTA_AUTHOR_SEO_VERSION);
    }
    
    public function deactivate() {
        // No eliminar opciones por si se reactiva
        error_log('[Replanta Author SEO] Plugin desactivado');
    }
    
    /**
     * Sistema de actualizaciones con yahnis-elsts/plugin-update-checker
     */
    private function init_updater() {
        if (class_exists('YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory')) {
            $updateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
                'https://github.com/replantadev/replanta-author-seo',
                __FILE__,
                'replanta-author-seo'
            );
            
            // Habilitar releases
            $updateChecker->getVcsApi()->enableReleaseAssets();
        }
    }
}

// Inicializar plugin
function replanta_author_seo() {
    return Replanta_Author_SEO::instance();
}

replanta_author_seo();
