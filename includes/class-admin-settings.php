<?php
/**
 * Panel de administración y settings
 * 
 * Configuración global del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Replanta_Admin_Settings {
    
    private static $instance = null;
    private $option_name = 'replanta_author_seo_options';
    
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_notices', [$this, 'admin_notices']);
    }
    
    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        add_options_page(
            __('Replanta Author SEO', 'replanta-author-seo'),
            __('Author SEO', 'replanta-author-seo'),
            'manage_options',
            'replanta-author-seo',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Registrar settings
     */
    public function register_settings() {
        register_setting(
            'replanta_author_seo_group',
            $this->option_name,
            [$this, 'sanitize_options']
        );
        
        // Sección General
        add_settings_section(
            'replanta_general_section',
            __('Configuración General', 'replanta-author-seo'),
            [$this, 'render_general_section'],
            'replanta-author-seo'
        );
        
        add_settings_field(
            'enable_schema',
            __('Schema.org', 'replanta-author-seo'),
            [$this, 'render_checkbox_field'],
            'replanta-author-seo',
            'replanta_general_section',
            ['name' => 'enable_schema', 'label' => __('Activar marcado Schema.org', 'replanta-author-seo')]
        );
        
        add_settings_field(
            'enable_audit_box',
            __('Auditoría de Artículo', 'replanta-author-seo'),
            [$this, 'render_checkbox_field'],
            'replanta-author-seo',
            'replanta_general_section',
            ['name' => 'enable_audit_box', 'label' => __('Mostrar bloque de auditoría', 'replanta-author-seo')]
        );
        
        add_settings_field(
            'enable_author_box',
            __('Caja de Autor', 'replanta-author-seo'),
            [$this, 'render_checkbox_field'],
            'replanta-author-seo',
            'replanta_general_section',
            ['name' => 'enable_author_box', 'label' => __('Mostrar caja de autor', 'replanta-author-seo')]
        );
        
        add_settings_field(
            'enable_related_posts',
            __('Artículos Relacionados', 'replanta-author-seo'),
            [$this, 'render_checkbox_field'],
            'replanta-author-seo',
            'replanta_general_section',
            ['name' => 'enable_related_posts', 'label' => __('Mostrar artículos relacionados', 'replanta-author-seo')]
        );
        
        // Sección Organización
        add_settings_section(
            'replanta_org_section',
            __('Información de la Organización', 'replanta-author-seo'),
            [$this, 'render_org_section'],
            'replanta-author-seo'
        );
        
        add_settings_field(
            'organization_name',
            __('Nombre de la Organización', 'replanta-author-seo'),
            [$this, 'render_text_field'],
            'replanta-author-seo',
            'replanta_org_section',
            ['name' => 'organization_name']
        );
        
        add_settings_field(
            'organization_logo',
            __('Logo de la Organización', 'replanta-author-seo'),
            [$this, 'render_image_field'],
            'replanta-author-seo',
            'replanta_org_section',
            ['name' => 'organization_logo']
        );
        
        // Sección Avanzado
        add_settings_section(
            'replanta_advanced_section',
            __('Configuración Avanzada', 'replanta-author-seo'),
            [$this, 'render_advanced_section'],
            'replanta-author-seo'
        );
        
        add_settings_field(
            'reading_speed_wpm',
            __('Velocidad de Lectura', 'replanta-author-seo'),
            [$this, 'render_number_field'],
            'replanta-author-seo',
            'replanta_advanced_section',
            ['name' => 'reading_speed_wpm', 'min' => 100, 'max' => 400, 'suffix' => 'palabras/minuto']
        );
        
        add_settings_field(
            'related_posts_count',
            __('Artículos Relacionados', 'replanta-author-seo'),
            [$this, 'render_number_field'],
            'replanta-author-seo',
            'replanta_advanced_section',
            ['name' => 'related_posts_count', 'min' => 3, 'max' => 12, 'suffix' => 'artículos']
        );
    }
    
    /**
     * Renderizar página de settings
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=replanta-author-seo&tab=general" 
                   class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('General', 'replanta-author-seo'); ?>
                </a>
                <a href="?page=replanta-author-seo&tab=schema" 
                   class="nav-tab <?php echo $active_tab === 'schema' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Schema.org', 'replanta-author-seo'); ?>
                </a>
                <a href="?page=replanta-author-seo&tab=help" 
                   class="nav-tab <?php echo $active_tab === 'help' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Ayuda', 'replanta-author-seo'); ?>
                </a>
            </h2>
            
            <?php if ($active_tab === 'general'): ?>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('replanta_author_seo_group');
                    do_settings_sections('replanta-author-seo');
                    submit_button();
                    ?>
                </form>
            
            <?php elseif ($active_tab === 'schema'): ?>
                <?php $this->render_schema_preview_tab(); ?>
            
            <?php elseif ($active_tab === 'help'): ?>
                <?php $this->render_help_tab(); ?>
            
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Renderizar secciones
     */
    public function render_general_section() {
        echo '<p>' . __('Configuración general de funcionalidades del plugin.', 'replanta-author-seo') . '</p>';
    }
    
    public function render_org_section() {
        echo '<p>' . __('Información de tu organización para el marcado Schema.org.', 'replanta-author-seo') . '</p>';
    }
    
    public function render_advanced_section() {
        echo '<p>' . __('Parámetros avanzados de comportamiento.', 'replanta-author-seo') . '</p>';
    }
    
    /**
     * Renderizar campos
     */
    public function render_checkbox_field($args) {
        $options = get_option($this->option_name, []);
        $value = isset($options[$args['name']]) ? $options[$args['name']] : false;
        ?>
        <label>
            <input type="checkbox" 
                   name="<?php echo esc_attr($this->option_name); ?>[<?php echo esc_attr($args['name']); ?>]" 
                   value="1" 
                   <?php checked($value, 1); ?>>
            <?php echo esc_html($args['label']); ?>
        </label>
        <?php
    }
    
    public function render_text_field($args) {
        $options = get_option($this->option_name, []);
        $value = isset($options[$args['name']]) ? $options[$args['name']] : '';
        ?>
        <input type="text" 
               name="<?php echo esc_attr($this->option_name); ?>[<?php echo esc_attr($args['name']); ?>]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text">
        <?php
    }
    
    public function render_number_field($args) {
        $options = get_option($this->option_name, []);
        $value = isset($options[$args['name']]) ? $options[$args['name']] : '';
        ?>
        <input type="number" 
               name="<?php echo esc_attr($this->option_name); ?>[<?php echo esc_attr($args['name']); ?>]" 
               value="<?php echo esc_attr($value); ?>" 
               min="<?php echo esc_attr($args['min']); ?>"
               max="<?php echo esc_attr($args['max']); ?>"
               class="small-text">
        <span class="description"><?php echo esc_html($args['suffix']); ?></span>
        <?php
    }
    
    public function render_image_field($args) {
        $options = get_option($this->option_name, []);
        $value = isset($options[$args['name']]) ? $options[$args['name']] : '';
        ?>
        <div class="replanta-image-upload">
            <input type="hidden" 
                   name="<?php echo esc_attr($this->option_name); ?>[<?php echo esc_attr($args['name']); ?>]" 
                   value="<?php echo esc_attr($value); ?>" 
                   id="<?php echo esc_attr($args['name']); ?>-input">
            
            <div class="image-preview">
                <?php if ($value): ?>
                    <img src="<?php echo esc_url($value); ?>" alt="" style="max-width: 200px;">
                <?php endif; ?>
            </div>
            
            <button type="button" 
                    class="button replanta-upload-button" 
                    data-target="<?php echo esc_attr($args['name']); ?>-input">
                <?php _e('Seleccionar Imagen', 'replanta-author-seo'); ?>
            </button>
            
            <?php if ($value): ?>
                <button type="button" 
                        class="button replanta-remove-button" 
                        data-target="<?php echo esc_attr($args['name']); ?>-input">
                    <?php _e('Eliminar', 'replanta-author-seo'); ?>
                </button>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Sanitizar opciones
     */
    public function sanitize_options($input) {
        $output = [];
        
        // Checkboxes
        $checkboxes = ['enable_schema', 'enable_audit_box', 'enable_author_box', 'enable_related_posts'];
        foreach ($checkboxes as $checkbox) {
            $output[$checkbox] = !empty($input[$checkbox]) ? 1 : 0;
        }
        
        // Text fields
        if (isset($input['organization_name'])) {
            $output['organization_name'] = sanitize_text_field($input['organization_name']);
        }
        
        // URLs
        if (isset($input['organization_logo'])) {
            $output['organization_logo'] = esc_url_raw($input['organization_logo']);
        }
        
        // Numbers
        if (isset($input['reading_speed_wpm'])) {
            $output['reading_speed_wpm'] = max(100, min(400, intval($input['reading_speed_wpm'])));
        }
        
        if (isset($input['related_posts_count'])) {
            $output['related_posts_count'] = max(3, min(12, intval($input['related_posts_count'])));
        }
        
        return $output;
    }
    
    /**
     * Renderizar tab de schema preview
     */
    private function render_schema_preview_tab() {
        // Obtener último post publicado
        $latest_posts = get_posts([
            'numberposts' => 1,
            'post_status' => 'publish',
        ]);
        
        if (empty($latest_posts)) {
            echo '<div class="notice notice-warning"><p>' . __('No hay posts publicados para previsualizar.', 'replanta-author-seo') . '</p></div>';
            return;
        }
        
        $post = $latest_posts[0];
        $schema_generator = Replanta_Schema_Generator::instance();
        $schema = $schema_generator->generate_article_schema($post->ID);
        
        ?>
        <div class="replanta-schema-preview">
            <h2><?php _e('Vista Previa del Schema', 'replanta-author-seo'); ?></h2>
            <p><?php printf(__('Previsualizando: <strong>%s</strong>', 'replanta-author-seo'), esc_html($post->post_title)); ?></p>
            
            <div class="schema-code">
                <pre><code><?php echo esc_html(json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?></code></pre>
            </div>
            
            <div class="schema-validation">
                <h3><?php _e('Validar Schema', 'replanta-author-seo'); ?></h3>
                <p>
                    <a href="https://search.google.com/test/rich-results?url=<?php echo urlencode(get_permalink($post->ID)); ?>" 
                       target="_blank" 
                       class="button button-primary">
                        <?php _e('Probar en Google Rich Results', 'replanta-author-seo'); ?>
                    </a>
                    <a href="https://validator.schema.org/#url=<?php echo urlencode(get_permalink($post->ID)); ?>" 
                       target="_blank" 
                       class="button">
                        <?php _e('Validar en Schema.org', 'replanta-author-seo'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizar tab de ayuda
     */
    private function render_help_tab() {
        ?>
        <div class="replanta-help">
            <h2><?php _e('Guía de Uso', 'replanta-author-seo'); ?></h2>
            
            <div class="help-section">
                <h3><?php _e('Configuración de Autores', 'replanta-author-seo'); ?></h3>
                <p><?php _e('Para configurar la información de cada autor:', 'replanta-author-seo'); ?></p>
                <ol>
                    <li><?php _e('Ve a <strong>Usuarios > Tu Perfil</strong>', 'replanta-author-seo'); ?></li>
                    <li><?php _e('Desplázate hasta la sección <strong>Información de Autor (SEO)</strong>', 'replanta-author-seo'); ?></li>
                    <li><?php _e('Completa los campos de información profesional y redes sociales', 'replanta-author-seo'); ?></li>
                    <li><?php _e('Guarda los cambios', 'replanta-author-seo'); ?></li>
                </ol>
            </div>
            
            <div class="help-section">
                <h3><?php _e('Schema.org', 'replanta-author-seo'); ?></h3>
                <p><?php _e('El plugin genera automáticamente marcado estructurado para:', 'replanta-author-seo'); ?></p>
                <ul>
                    <li><strong>Article</strong>: <?php _e('Información completa del artículo', 'replanta-author-seo'); ?></li>
                    <li><strong>Person</strong>: <?php _e('Datos del autor con credenciales', 'replanta-author-seo'); ?></li>
                    <li><strong>Organization</strong>: <?php _e('Información del publisher', 'replanta-author-seo'); ?></li>
                </ul>
            </div>
            
            <div class="help-section">
                <h3><?php _e('Shortcodes Disponibles', 'replanta-author-seo'); ?></h3>
                <p><code>[replanta_audit_box post_id="123"]</code> - <?php _e('Muestra el bloque de auditoría', 'replanta-author-seo'); ?></p>
                <p><code>[replanta_author_box post_id="123"]</code> - <?php _e('Muestra la caja de autor', 'replanta-author-seo'); ?></p>
                <p><code>[replanta_related_posts post_id="123" limit="6"]</code> - <?php _e('Muestra artículos relacionados', 'replanta-author-seo'); ?></p>
            </div>
            
            <div class="help-section">
                <h3><?php _e('Soporte', 'replanta-author-seo'); ?></h3>
                <p><?php _e('Para soporte técnico, visita:', 'replanta-author-seo'); ?></p>
                <p><a href="https://github.com/replantadev/replanta-author-seo/issues" target="_blank">GitHub Issues</a></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        $options = get_option($this->option_name, []);
        
        // Avisar si schema está desactivado
        if (empty($options['enable_schema'])) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <?php _e('El marcado Schema.org está desactivado. Actívalo en', 'replanta-author-seo'); ?>
                    <a href="<?php echo admin_url('options-general.php?page=replanta-author-seo'); ?>">
                        <?php _e('Configuración', 'replanta-author-seo'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
}
