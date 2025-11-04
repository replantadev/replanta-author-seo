<?php
/**
 * Sistema de upload de avatar personalizado
 * 
 * Permite a los autores subir avatares personalizados sin Gravatar
 */

if (!defined('ABSPATH')) {
    exit;
}

class Replanta_Avatar_Uploader {
    
    private static $instance = null;
    private $meta_key = 'replanta_custom_avatar';
    
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Añadir campo de avatar en perfil de usuario
        add_action('show_user_profile', [$this, 'render_avatar_field']);
        add_action('edit_user_profile', [$this, 'render_avatar_field']);
        
        // Guardar avatar personalizado
        add_action('personal_options_update', [$this, 'save_avatar']);
        add_action('edit_user_profile_update', [$this, 'save_avatar']);
        
        // Reemplazar avatar de WordPress
        add_filter('get_avatar_url', [$this, 'filter_avatar_url'], 10, 3);
        add_filter('get_avatar', [$this, 'filter_avatar_html'], 10, 6);
        
        // Eliminar avatar al borrar usuario
        add_action('delete_user', [$this, 'delete_avatar']);
        
        // Scripts para admin
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    /**
     * Renderizar campo de avatar en perfil
     */
    public function render_avatar_field($user) {
        $avatar_id = get_user_meta($user->ID, $this->meta_key, true);
        $avatar_url = $avatar_id ? wp_get_attachment_url($avatar_id) : '';
        ?>
        <h2><?php _e('Avatar Personalizado', 'replanta-author-seo'); ?></h2>
        <table class="form-table">
            <tr>
                <th>
                    <label for="replanta_custom_avatar"><?php _e('Avatar Personalizado', 'replanta-author-seo'); ?></label>
                </th>
                <td>
                    <div class="replanta-avatar-upload">
                        <input type="hidden" 
                               name="replanta_custom_avatar" 
                               id="replanta_custom_avatar" 
                               value="<?php echo esc_attr($avatar_id); ?>">
                        
                        <div class="avatar-preview" style="margin-bottom: 15px;">
                            <?php if ($avatar_url): ?>
                                <img src="<?php echo esc_url($avatar_url); ?>" 
                                     alt="<?php _e('Avatar', 'replanta-author-seo'); ?>" 
                                     style="max-width: 150px; height: auto; border-radius: 50%;">
                            <?php else: ?>
                                <div class="avatar-placeholder" style="width: 150px; height: 150px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <span style="color: #999; font-size: 14px;"><?php _e('Sin avatar', 'replanta-author-seo'); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <p>
                            <button type="button" class="button replanta-upload-avatar-button">
                                <?php _e('Subir Avatar', 'replanta-author-seo'); ?>
                            </button>
                            
                            <?php if ($avatar_url): ?>
                                <button type="button" class="button replanta-remove-avatar-button">
                                    <?php _e('Eliminar Avatar', 'replanta-author-seo'); ?>
                                </button>
                            <?php endif; ?>
                        </p>
                        
                        <p class="description">
                            <?php _e('Sube una imagen personalizada para usar como avatar en lugar de Gravatar. Tamaño recomendado: 400x400px.', 'replanta-author-seo'); ?>
                        </p>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Guardar avatar personalizado
     */
    public function save_avatar($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }
        
        if (isset($_POST['replanta_custom_avatar'])) {
            $avatar_id = intval($_POST['replanta_custom_avatar']);
            
            if ($avatar_id > 0) {
                update_user_meta($user_id, $this->meta_key, $avatar_id);
            } else {
                delete_user_meta($user_id, $this->meta_key);
            }
        }
    }
    
    /**
     * Filtrar URL de avatar
     */
    public function filter_avatar_url($url, $id_or_email, $args) {
        $user_id = $this->get_user_id($id_or_email);
        
        if (!$user_id) {
            return $url;
        }
        
        $custom_url = self::get_author_avatar_url($user_id);
        
        return $custom_url ? $custom_url : $url;
    }
    
    /**
     * Filtrar HTML de avatar
     */
    public function filter_avatar_html($avatar, $id_or_email, $size, $default, $alt, $args) {
        $user_id = $this->get_user_id($id_or_email);
        
        if (!$user_id) {
            return $avatar;
        }
        
        $custom_url = self::get_author_avatar_url($user_id);
        
        if (!$custom_url) {
            return $avatar;
        }
        
        // Generar HTML del avatar personalizado
        $class = isset($args['class']) ? $args['class'] : 'avatar';
        $class_array = is_array($class) ? $class : explode(' ', $class);
        $class_array[] = 'replanta-custom-avatar';
        
        $avatar_html = sprintf(
            '<img alt="%s" src="%s" srcset="%s" class="%s" height="%d" width="%d" loading="lazy" decoding="async">',
            esc_attr($alt),
            esc_url($custom_url),
            esc_url($custom_url) . ' 2x',
            esc_attr(implode(' ', $class_array)),
            (int) $size,
            (int) $size
        );
        
        return $avatar_html;
    }
    
    /**
     * Obtener user ID desde diferentes formatos
     */
    private function get_user_id($id_or_email) {
        if (is_numeric($id_or_email)) {
            return (int) $id_or_email;
        }
        
        if (is_object($id_or_email) && isset($id_or_email->user_id)) {
            return (int) $id_or_email->user_id;
        }
        
        if (is_string($id_or_email)) {
            $user = get_user_by('email', $id_or_email);
            return $user ? $user->ID : 0;
        }
        
        return 0;
    }
    
    /**
     * Obtener URL del avatar personalizado
     */
    public static function get_author_avatar_url($user_id) {
        $avatar_id = get_user_meta($user_id, 'replanta_custom_avatar', true);
        
        if (!$avatar_id) {
            return false;
        }
        
        $avatar_url = wp_get_attachment_url($avatar_id);
        
        return $avatar_url ? $avatar_url : false;
    }
    
    /**
     * Eliminar avatar al borrar usuario
     */
    public function delete_avatar($user_id) {
        $avatar_id = get_user_meta($user_id, $this->meta_key, true);
        
        if ($avatar_id) {
            wp_delete_attachment($avatar_id, true);
            delete_user_meta($user_id, $this->meta_key);
        }
    }
    
    /**
     * Enqueue scripts para admin
     */
    public function enqueue_admin_scripts($hook) {
        // Solo cargar en páginas de perfil de usuario
        if ($hook !== 'profile.php' && $hook !== 'user-edit.php') {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_script(
            'replanta-avatar-uploader',
            REPLANTA_AUTHOR_SEO_URL . 'assets/js/admin.js',
            ['jquery'],
            REPLANTA_AUTHOR_SEO_VERSION,
            true
        );
        
        wp_localize_script('replanta-avatar-uploader', 'replantaAvatarUploader', [
            'title' => __('Seleccionar Avatar', 'replanta-author-seo'),
            'button' => __('Usar como Avatar', 'replanta-author-seo'),
        ]);
    }
}
