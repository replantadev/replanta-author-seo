<?php
/**
 * Gestión de campos personalizados de autor
 * 
 * Añade campos extendidos al perfil de usuario para mejorar SEO de autoría
 */

if (!defined('ABSPATH')) {
    exit;
}

class Replanta_Author_Fields {
    
    private static $instance = null;
    
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Añadir campos al perfil de usuario
        add_action('show_user_profile', [$this, 'render_author_fields']);
        add_action('edit_user_profile', [$this, 'render_author_fields']);
        
        // Guardar campos
        add_action('personal_options_update', [$this, 'save_author_fields']);
        add_action('edit_user_profile_update', [$this, 'save_author_fields']);
        
        // Añadir columnas en listado de usuarios
        add_filter('manage_users_columns', [$this, 'add_user_columns']);
        add_filter('manage_users_custom_column', [$this, 'render_user_column'], 10, 3);
    }
    
    /**
     * Campos personalizados de autor
     */
    public function get_author_fields() {
        return [
            'job_title' => [
                'label' => __('Título Profesional', 'replanta-author-seo'),
                'type' => 'text',
                'description' => __('Ej: Desarrollador Full Stack, Editor en Jefe, etc.', 'replanta-author-seo'),
                'placeholder' => __('Tu título profesional', 'replanta-author-seo'),
            ],
            'organization' => [
                'label' => __('Organización', 'replanta-author-seo'),
                'type' => 'text',
                'description' => __('Empresa u organización donde trabajas', 'replanta-author-seo'),
                'placeholder' => __('Nombre de tu empresa', 'replanta-author-seo'),
            ],
            'bio_extended' => [
                'label' => __('Biografía Extendida', 'replanta-author-seo'),
                'type' => 'textarea',
                'description' => __('Bio detallada para SEO (se añade a la bio de WordPress)', 'replanta-author-seo'),
                'placeholder' => __('Cuéntanos más sobre ti...', 'replanta-author-seo'),
            ],
            'credentials' => [
                'label' => __('Credenciales y Logros', 'replanta-author-seo'),
                'type' => 'textarea',
                'description' => __('Títulos académicos, certificaciones, premios (uno por línea)', 'replanta-author-seo'),
                'placeholder' => __("Máster en Desarrollo Web - Universidad X\nCertificación Google Analytics\nPremio Mejor Blog 2024", 'replanta-author-seo'),
            ],
            'expertise_areas' => [
                'label' => __('Áreas de Expertise', 'replanta-author-seo'),
                'type' => 'text',
                'description' => __('Temas en los que eres experto (separados por comas)', 'replanta-author-seo'),
                'placeholder' => __('WordPress, JavaScript, SEO, Marketing Digital', 'replanta-author-seo'),
            ],
            'website_url' => [
                'label' => __('Sitio Web Personal', 'replanta-author-seo'),
                'type' => 'url',
                'description' => __('URL de tu sitio web o portfolio', 'replanta-author-seo'),
                'placeholder' => 'https://tudominio.com',
            ],
            // Redes sociales
            'twitter_url' => [
                'label' => __('Twitter/X', 'replanta-author-seo'),
                'type' => 'url',
                'description' => __('URL completa de tu perfil', 'replanta-author-seo'),
                'placeholder' => 'https://twitter.com/tuusuario',
            ],
            'linkedin_url' => [
                'label' => __('LinkedIn', 'replanta-author-seo'),
                'type' => 'url',
                'description' => __('URL completa de tu perfil', 'replanta-author-seo'),
                'placeholder' => 'https://linkedin.com/in/tuusuario',
            ],
            'github_url' => [
                'label' => __('GitHub', 'replanta-author-seo'),
                'type' => 'url',
                'description' => __('URL completa de tu perfil', 'replanta-author-seo'),
                'placeholder' => 'https://github.com/tuusuario',
            ],
            'facebook_url' => [
                'label' => __('Facebook', 'replanta-author-seo'),
                'type' => 'url',
                'description' => '',
                'placeholder' => 'https://facebook.com/tuusuario',
            ],
            'instagram_url' => [
                'label' => __('Instagram', 'replanta-author-seo'),
                'type' => 'url',
                'description' => '',
                'placeholder' => 'https://instagram.com/tuusuario',
            ],
            'youtube_url' => [
                'label' => __('YouTube', 'replanta-author-seo'),
                'type' => 'url',
                'description' => '',
                'placeholder' => 'https://youtube.com/@tucanal',
            ],
        ];
    }
    
    /**
     * Renderizar campos en el perfil de usuario
     */
    public function render_author_fields($user) {
        ?>
        <h2><?php _e('Información de Autoría SEO', 'replanta-author-seo'); ?></h2>
        <p class="description">
            <?php _e('Estos campos mejoran el SEO de autoría y se usan en Schema.org markup.', 'replanta-author-seo'); ?>
        </p>
        
        <table class="form-table replanta-author-fields">
            <?php foreach ($this->get_author_fields() as $field_key => $field): ?>
                <tr>
                    <th scope="row">
                        <label for="replanta_<?php echo esc_attr($field_key); ?>">
                            <?php echo esc_html($field['label']); ?>
                        </label>
                    </th>
                    <td>
                        <?php 
                        $value = get_user_meta($user->ID, 'replanta_' . $field_key, true);
                        
                        if ($field['type'] === 'textarea'): ?>
                            <textarea 
                                name="replanta_<?php echo esc_attr($field_key); ?>" 
                                id="replanta_<?php echo esc_attr($field_key); ?>"
                                rows="5"
                                cols="30"
                                class="regular-text"
                                placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                            ><?php echo esc_textarea($value); ?></textarea>
                        <?php else: ?>
                            <input 
                                type="<?php echo esc_attr($field['type']); ?>" 
                                name="replanta_<?php echo esc_attr($field_key); ?>" 
                                id="replanta_<?php echo esc_attr($field_key); ?>"
                                value="<?php echo esc_attr($value); ?>"
                                class="regular-text"
                                placeholder="<?php echo esc_attr($field['placeholder']); ?>"
                            />
                        <?php endif; ?>
                        
                        <?php if (!empty($field['description'])): ?>
                            <p class="description"><?php echo esc_html($field['description']); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }
    
    /**
     * Guardar campos personalizados
     */
    public function save_author_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }
        
        foreach ($this->get_author_fields() as $field_key => $field) {
            $meta_key = 'replanta_' . $field_key;
            
            if (isset($_POST[$meta_key])) {
                $value = $_POST[$meta_key];
                
                // Sanitización según tipo
                if ($field['type'] === 'url') {
                    $value = esc_url_raw($value);
                } elseif ($field['type'] === 'textarea') {
                    $value = sanitize_textarea_field($value);
                } else {
                    $value = sanitize_text_field($value);
                }
                
                update_user_meta($user_id, $meta_key, $value);
            }
        }
    }
    
    /**
     * Añadir columnas al listado de usuarios
     */
    public function add_user_columns($columns) {
        $columns['replanta_job_title'] = __('Título', 'replanta-author-seo');
        $columns['replanta_organization'] = __('Organización', 'replanta-author-seo');
        return $columns;
    }
    
    /**
     * Renderizar columnas personalizadas
     */
    public function render_user_column($output, $column_name, $user_id) {
        if ($column_name === 'replanta_job_title') {
            $job_title = get_user_meta($user_id, 'replanta_job_title', true);
            return !empty($job_title) ? esc_html($job_title) : '—';
        }
        
        if ($column_name === 'replanta_organization') {
            $organization = get_user_meta($user_id, 'replanta_organization', true);
            return !empty($organization) ? esc_html($organization) : '—';
        }
        
        return $output;
    }
    
    /**
     * Obtener todas las redes sociales de un autor
     */
    public static function get_author_social_links($user_id) {
        $socials = [];
        $social_fields = ['twitter_url', 'linkedin_url', 'github_url', 'facebook_url', 'instagram_url', 'youtube_url'];
        
        foreach ($social_fields as $field) {
            $url = get_user_meta($user_id, 'replanta_' . $field, true);
            if (!empty($url)) {
                $socials[$field] = $url;
            }
        }
        
        return $socials;
    }
    
    /**
     * Obtener datos completos del autor para Schema
     */
    public static function get_author_data($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return null;
        }
        
        return [
            'name' => $user->display_name,
            'description' => $user->description,
            'bio_extended' => get_user_meta($user_id, 'replanta_bio_extended', true),
            'job_title' => get_user_meta($user_id, 'replanta_job_title', true),
            'organization' => get_user_meta($user_id, 'replanta_organization', true),
            'credentials' => get_user_meta($user_id, 'replanta_credentials', true),
            'expertise_areas' => get_user_meta($user_id, 'replanta_expertise_areas', true),
            'website_url' => get_user_meta($user_id, 'replanta_website_url', true),
            'social_links' => self::get_author_social_links($user_id),
            'email' => $user->user_email,
            'url' => get_author_posts_url($user_id),
        ];
    }
}
