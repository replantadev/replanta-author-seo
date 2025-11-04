<?php
/**
 * Sistema de artículos relacionados inteligente
 * 
 * Encuentra posts relacionados por tags, categorías y autor
 */

if (!defined('ABSPATH')) {
    exit;
}

class Replanta_Related_Posts {
    
    private static $instance = null;
    
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Añadir related posts al contenido
        add_filter('the_content', [$this, 'add_related_posts']);
        
        // Registrar widget
        add_action('widgets_init', [$this, 'register_widget']);
        
        // Shortcode
        add_shortcode('replanta_related_posts', [$this, 'related_posts_shortcode']);
    }
    
    /**
     * Añadir related posts automáticamente
     */
    public function add_related_posts($content) {
        if (!is_single() || !is_main_query()) {
            return $content;
        }
        
        $options = get_option('replanta_author_seo_options', []);
        
        if (empty($options['enable_related_posts'])) {
            return $content;
        }
        
        $related_html = $this->render_related_posts(get_the_ID());
        
        // Insertar al final del contenido
        return $content . $related_html;
    }
    
    /**
     * Obtener posts relacionados con scoring
     */
    public function get_related_posts($post_id, $limit = 6) {
        $post = get_post($post_id);
        if (!$post) {
            return [];
        }
        
        // Obtener taxonomías
        $tags = wp_get_post_tags($post_id, ['fields' => 'ids']);
        $categories = wp_get_post_categories($post_id);
        $author_id = $post->post_author;
        
        // Construir query base
        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'post__not_in' => [$post_id],
            'posts_per_page' => 50, // Obtenemos más para scorear
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => true,
        ];
        
        // Si hay tags o categorías, filtrar por ellas
        if (!empty($tags) || !empty($categories)) {
            $tax_query = ['relation' => 'OR'];
            
            if (!empty($tags)) {
                $tax_query[] = [
                    'taxonomy' => 'post_tag',
                    'field' => 'term_id',
                    'terms' => $tags,
                ];
            }
            
            if (!empty($categories)) {
                $tax_query[] = [
                    'taxonomy' => 'category',
                    'field' => 'term_id',
                    'terms' => $categories,
                ];
            }
            
            $args['tax_query'] = $tax_query;
        } else {
            // Sin tags ni categorías, buscar por autor
            $args['author'] = $author_id;
        }
        
        $query = new WP_Query($args);
        $posts = $query->posts;
        
        // Scorear posts
        $scored_posts = [];
        foreach ($posts as $related_post) {
            $score = $this->calculate_post_score($post_id, $related_post->ID, $tags, $categories, $author_id);
            $scored_posts[] = [
                'post' => $related_post,
                'score' => $score,
            ];
        }
        
        // Ordenar por score
        usort($scored_posts, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        // Retornar los mejores
        $result = array_slice($scored_posts, 0, $limit);
        
        return array_map(function($item) {
            return $item['post'];
        }, $result);
    }
    
    /**
     * Calcular score de relevancia
     */
    private function calculate_post_score($original_post_id, $related_post_id, $original_tags, $original_categories, $original_author) {
        $score = 0;
        
        // Tags compartidos: 3 puntos por tag
        $related_tags = wp_get_post_tags($related_post_id, ['fields' => 'ids']);
        $shared_tags = array_intersect($original_tags, $related_tags);
        $score += count($shared_tags) * 3;
        
        // Categorías compartidas: 2 puntos por categoría
        $related_categories = wp_get_post_categories($related_post_id);
        $shared_categories = array_intersect($original_categories, $related_categories);
        $score += count($shared_categories) * 2;
        
        // Mismo autor: 1 punto
        $related_post = get_post($related_post_id);
        if ($related_post->post_author == $original_author) {
            $score += 1;
        }
        
        // Bonus por recencia (posts más nuevos primero)
        $post_date = strtotime($related_post->post_date);
        $days_old = (time() - $post_date) / DAY_IN_SECONDS;
        if ($days_old < 30) {
            $score += 2;
        } elseif ($days_old < 90) {
            $score += 1;
        }
        
        return $score;
    }
    
    /**
     * Renderizar related posts
     */
    public function render_related_posts($post_id, $limit = null) {
        $options = get_option('replanta_author_seo_options', []);
        
        if ($limit === null) {
            $limit = !empty($options['related_posts_count']) ? intval($options['related_posts_count']) : 6;
        }
        
        $related_posts = $this->get_related_posts($post_id, $limit);
        
        if (empty($related_posts)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="replanta-related-posts">
            <div class="related-header">
                <h3 class="related-title">
                    <svg width="24" height="24" fill="currentColor"><use href="#icon-book"/></svg>
                    <?php _e('Artículos Relacionados', 'replanta-author-seo'); ?>
                </h3>
            </div>
            
            <div class="related-grid">
                <?php foreach ($related_posts as $related_post): 
                    $thumbnail_url = get_the_post_thumbnail_url($related_post->ID, 'medium');
                    $author_data = Replanta_Author_Fields::get_author_data($related_post->post_author);
                    $reading_time = $this->calculate_reading_time($related_post->post_content);
                ?>
                    <article class="related-post-item">
                        <?php if ($thumbnail_url): ?>
                        <div class="related-thumbnail">
                            <a href="<?php echo esc_url(get_permalink($related_post->ID)); ?>">
                                <img src="<?php echo esc_url($thumbnail_url); ?>" 
                                     alt="<?php echo esc_attr($related_post->post_title); ?>"
                                     loading="lazy">
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="related-content">
                            <h4 class="related-post-title">
                                <a href="<?php echo esc_url(get_permalink($related_post->ID)); ?>">
                                    <?php echo esc_html($related_post->post_title); ?>
                                </a>
                            </h4>
                            
                            <div class="related-excerpt">
                                <?php echo esc_html(wp_trim_words($related_post->post_excerpt ?: $related_post->post_content, 20)); ?>
                            </div>
                            
                            <div class="related-meta">
                                <span class="meta-author">
                                    <svg width="14" height="14" fill="currentColor"><use href="#icon-user"/></svg>
                                    <?php echo esc_html($author_data['name']); ?>
                                </span>
                                <span class="meta-separator">•</span>
                                <span class="meta-date">
                                    <svg width="14" height="14" fill="currentColor"><use href="#icon-calendar"/></svg>
                                    <?php echo esc_html(get_the_date('', $related_post->ID)); ?>
                                </span>
                                <span class="meta-separator">•</span>
                                <span class="meta-reading-time">
                                    <svg width="14" height="14" fill="currentColor"><use href="#icon-clock"/></svg>
                                    <?php printf(_n('%s min', '%s min', $reading_time, 'replanta-author-seo'), $reading_time); ?>
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
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
        
        return max(1, $minutes);
    }
    
    /**
     * Shortcode para related posts
     */
    public function related_posts_shortcode($atts) {
        $atts = shortcode_atts([
            'post_id' => get_the_ID(),
            'limit' => 6,
        ], $atts);
        
        return $this->render_related_posts($atts['post_id'], $atts['limit']);
    }
    
    /**
     * Registrar widget
     */
    public function register_widget() {
        register_widget('Replanta_Related_Posts_Widget');
    }
}

/**
 * Widget de artículos relacionados
 */
class Replanta_Related_Posts_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'replanta_related_posts',
            __('Replanta - Artículos Relacionados', 'replanta-author-seo'),
            [
                'description' => __('Muestra artículos relacionados con el actual', 'replanta-author-seo'),
            ]
        );
    }
    
    public function widget($args, $instance) {
        if (!is_single()) {
            return;
        }
        
        $title = !empty($instance['title']) ? $instance['title'] : __('Artículos Relacionados', 'replanta-author-seo');
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 6;
        
        echo $args['before_widget'];
        
        if ($title) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        $related_posts_instance = Replanta_Related_Posts::instance();
        echo $related_posts_instance->render_related_posts(get_the_ID(), $limit);
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $limit = !empty($instance['limit']) ? $instance['limit'] : 6;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php _e('Título:', 'replanta-author-seo'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>">
                <?php _e('Número de artículos:', 'replanta-author-seo'); ?>
            </label>
            <input class="tiny-text" 
                   id="<?php echo esc_attr($this->get_field_id('limit')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('limit')); ?>" 
                   type="number" 
                   step="1" 
                   min="1" 
                   max="12"
                   value="<?php echo esc_attr($limit); ?>">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? intval($new_instance['limit']) : 6;
        return $instance;
    }
}
