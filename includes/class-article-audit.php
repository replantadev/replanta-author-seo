<?php
/**
 * Bloque de auditoría de artículo
 * 
 * Muestra información de publicación, autor, tiempo de lectura, etc.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Replanta_Article_Audit {
    
    private static $instance = null;
    
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Añadir audit box al contenido
        add_filter('the_content', [$this, 'add_audit_box']);
        
        // Añadir author box al contenido
        add_filter('the_content', [$this, 'add_author_box']);
        
        // Shortcodes
        add_shortcode('replanta_audit_box', [$this, 'audit_box_shortcode']);
        add_shortcode('replanta_author_box', [$this, 'author_box_shortcode']);
    }
    
    /**
     * Añadir audit box automáticamente
     */
    public function add_audit_box($content) {
        if (!is_single() || !is_main_query()) {
            return $content;
        }
        
        $options = get_option('replanta_author_seo_options', []);
        
        if (empty($options['enable_audit_box'])) {
            return $content;
        }
        
        $audit_html = $this->render_audit_box(get_the_ID());
        
        // Insertar al inicio del contenido
        return $audit_html . $content;
    }
    
    /**
     * Añadir author box automáticamente
     */
    public function add_author_box($content) {
        if (!is_single() || !is_main_query()) {
            return $content;
        }
        
        $options = get_option('replanta_author_seo_options', []);
        
        if (empty($options['enable_author_box'])) {
            return $content;
        }
        
        $author_html = $this->render_author_box(get_the_ID());
        
        // Insertar al final del contenido
        return $content . $author_html;
    }
    
    /**
     * Renderizar audit box
     */
    public function render_audit_box($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return '';
        }
        
        $author_id = $post->post_author;
        $author_data = Replanta_Author_Fields::get_author_data($author_id);
        
        // Calcular tiempo de lectura
        $reading_time = $this->calculate_reading_time($post->post_content);
        
        // Contar palabras
        $word_count = str_word_count(strip_tags($post->post_content));
        
        // Fecha de publicación y modificación
        $published_date = get_the_date('', $post_id);
        $modified_date = get_the_modified_date('', $post_id);
        
        // Categorías
        $categories = get_the_category($post_id);
        $category_names = array_map(function($cat) {
            return $cat->name;
        }, $categories);
        
        ob_start();
        ?>
        <div class="replanta-audit-box">
            <div class="audit-header">
                <span class="audit-icon">📊</span>
                <h3 class="audit-title"><?php _e('Información del Artículo', 'replanta-author-seo'); ?></h3>
            </div>
            
            <div class="audit-content">
                <div class="audit-meta">
                    <div class="audit-item">
                        <span class="audit-label">
                            <svg width="16" height="16" fill="currentColor"><use href="#icon-calendar"/></svg>
                            <?php _e('Publicado:', 'replanta-author-seo'); ?>
                        </span>
                        <span class="audit-value">
                            <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                                <?php echo esc_html($published_date); ?>
                            </time>
                        </span>
                    </div>
                    
                    <?php if ($published_date !== $modified_date): ?>
                    <div class="audit-item">
                        <span class="audit-label">
                            <svg width="16" height="16" fill="currentColor"><use href="#icon-update"/></svg>
                            <?php _e('Actualizado:', 'replanta-author-seo'); ?>
                        </span>
                        <span class="audit-value">
                            <time datetime="<?php echo esc_attr(get_the_modified_date('c', $post_id)); ?>">
                                <?php echo esc_html($modified_date); ?>
                            </time>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="audit-item">
                        <span class="audit-label">
                            <svg width="16" height="16" fill="currentColor"><use href="#icon-clock"/></svg>
                            <?php _e('Tiempo de lectura:', 'replanta-author-seo'); ?>
                        </span>
                        <span class="audit-value">
                            <?php printf(_n('%s minuto', '%s minutos', $reading_time, 'replanta-author-seo'), $reading_time); ?>
                        </span>
                    </div>
                    
                    <div class="audit-item">
                        <span class="audit-label">
                            <svg width="16" height="16" fill="currentColor"><use href="#icon-text"/></svg>
                            <?php _e('Palabras:', 'replanta-author-seo'); ?>
                        </span>
                        <span class="audit-value"><?php echo number_format_i18n($word_count); ?></span>
                    </div>
                    
                    <?php if (!empty($category_names)): ?>
                    <div class="audit-item">
                        <span class="audit-label">
                            <svg width="16" height="16" fill="currentColor"><use href="#icon-folder"/></svg>
                            <?php _e('Categorías:', 'replanta-author-seo'); ?>
                        </span>
                        <span class="audit-value"><?php echo esc_html(implode(', ', $category_names)); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($author_data['job_title'])): ?>
                    <div class="audit-item">
                        <span class="audit-label">
                            <svg width="16" height="16" fill="currentColor"><use href="#icon-user"/></svg>
                            <?php _e('Autor:', 'replanta-author-seo'); ?>
                        </span>
                        <span class="audit-value">
                            <?php echo esc_html($author_data['name']); ?>
                            <span class="author-title">(<?php echo esc_html($author_data['job_title']); ?>)</span>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderizar author box
     */
    public function render_author_box($post_id) {
        $author_id = get_post_field('post_author', $post_id);
        $author_data = Replanta_Author_Fields::get_author_data($author_id);
        
        if (!$author_data) {
            return '';
        }
        
        // Avatar
        $avatar_url = Replanta_Avatar_Uploader::get_author_avatar_url($author_id);
        if (!$avatar_url) {
            $avatar_url = get_avatar_url($author_id, ['size' => 120]);
        }
        
        ob_start();
        ?>
        <div class="replanta-author-box">
            <div class="author-box-header">
                <div class="author-avatar">
                    <img src="<?php echo esc_url($avatar_url); ?>" 
                         alt="<?php echo esc_attr($author_data['name']); ?>"
                         width="120" height="120">
                </div>
                <div class="author-info">
                    <h3 class="author-name">
                        <a href="<?php echo esc_url($author_data['url']); ?>">
                            <?php echo esc_html($author_data['name']); ?>
                        </a>
                    </h3>
                    <?php if (!empty($author_data['job_title'])): ?>
                        <p class="author-job-title"><?php echo esc_html($author_data['job_title']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($author_data['organization'])): ?>
                        <p class="author-organization">
                            <svg width="16" height="16" fill="currentColor"><use href="#icon-building"/></svg>
                            <?php echo esc_html($author_data['organization']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="author-box-content">
                <?php if (!empty($author_data['description'])): ?>
                    <p class="author-bio"><?php echo wp_kses_post(wpautop($author_data['description'])); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($author_data['bio_extended'])): ?>
                    <p class="author-bio-extended"><?php echo wp_kses_post(wpautop($author_data['bio_extended'])); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($author_data['credentials'])): ?>
                    <div class="author-credentials">
                        <h4><?php _e('Credenciales y Logros', 'replanta-author-seo'); ?></h4>
                        <ul>
                            <?php 
                            $credentials = explode("\n", $author_data['credentials']);
                            foreach ($credentials as $credential): 
                                if (trim($credential)):
                            ?>
                                <li><?php echo esc_html(trim($credential)); ?></li>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($author_data['expertise_areas'])): ?>
                    <div class="author-expertise">
                        <strong><?php _e('Experto en:', 'replanta-author-seo'); ?></strong>
                        <?php echo esc_html($author_data['expertise_areas']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($author_data['social_links'])): ?>
                    <div class="author-social-links">
                        <?php foreach ($author_data['social_links'] as $network => $url): 
                            $network_name = str_replace('_url', '', $network);
                        ?>
                            <a href="<?php echo esc_url($url); ?>" 
                               class="social-link social-<?php echo esc_attr($network_name); ?>"
                               target="_blank" rel="noopener noreferrer"
                               aria-label="<?php echo esc_attr(ucfirst($network_name)); ?>">
                                <svg width="20" height="20" fill="currentColor">
                                    <use href="#icon-<?php echo esc_attr($network_name); ?>"/>
                                </svg>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="author-posts-link">
                    <a href="<?php echo esc_url($author_data['url']); ?>" class="btn-author-posts">
                        <?php printf(__('Ver todos los artículos de %s', 'replanta-author-seo'), esc_html($author_data['name'])); ?>
                        <svg width="16" height="16" fill="currentColor"><use href="#icon-arrow-right"/></svg>
                    </a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Calcular tiempo de lectura
     */
    private function calculate_reading_time($content) {
        $options = get_option('replanta_author_seo_options', []);
        $wpm = !empty($options['reading_speed_wpm']) ? intval($options['reading_speed_wpm']) : 200;
        
        $word_count = str_word_count(strip_tags($content));
        $minutes = ceil($word_count / $wpm);
        
        return max(1, $minutes); // Mínimo 1 minuto
    }
    
    /**
     * Shortcode para audit box
     */
    public function audit_box_shortcode($atts) {
        $atts = shortcode_atts([
            'post_id' => get_the_ID(),
        ], $atts);
        
        return $this->render_audit_box($atts['post_id']);
    }
    
    /**
     * Shortcode para author box
     */
    public function author_box_shortcode($atts) {
        $atts = shortcode_atts([
            'post_id' => get_the_ID(),
        ], $atts);
        
        return $this->render_author_box($atts['post_id']);
    }
}
