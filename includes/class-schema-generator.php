<?php
/**
 * Generador de Schema.org JSON-LD
 * 
 * Crea markup completo para Article, Author (Person), Organization y más
 */

if (!defined('ABSPATH')) {
    exit;
}

class Replanta_Schema_Generator {
    
    private static $instance = null;
    
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Inyectar schema en el head
        add_action('wp_head', [$this, 'output_schema'], 1);
    }
    
    /**
     * Output del schema en el head
     */
    public function output_schema() {
        if (!is_single() && !is_page()) {
            return;
        }
        
        $options = get_option('replanta_author_seo_options', []);
        
        if (empty($options['enable_schema'])) {
            return;
        }
        
        $schema = $this->generate_article_schema(get_the_ID());
        
        if ($schema) {
            echo "\n<!-- Replanta Author SEO - Schema.org Markup -->\n";
            echo '<script type="application/ld+json">' . "\n";
            echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            echo "\n</script>\n";
        }
    }
    
    /**
     * Generar schema completo de Article
     */
    public function generate_article_schema($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return null;
        }
        
        $author_id = $post->post_author;
        $author_data = Replanta_Author_Fields::get_author_data($author_id);
        
        $options = get_option('replanta_author_seo_options', []);
        
        // Schema base del artículo
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title($post_id),
            'description' => $this->get_post_excerpt($post_id),
            'datePublished' => get_the_date('c', $post_id),
            'dateModified' => get_the_modified_date('c', $post_id),
            'author' => $this->generate_author_schema($author_data, $author_id),
            'publisher' => $this->generate_publisher_schema($options),
        ];
        
        // Imagen destacada
        if (has_post_thumbnail($post_id)) {
            $image_id = get_post_thumbnail_id($post_id);
            $image_data = wp_get_attachment_image_src($image_id, 'full');
            
            if ($image_data) {
                $schema['image'] = [
                    '@type' => 'ImageObject',
                    'url' => $image_data[0],
                    'width' => $image_data[1],
                    'height' => $image_data[2],
                ];
            }
        }
        
        // URL del artículo
        $schema['url'] = get_permalink($post_id);
        $schema['mainEntityOfPage'] = [
            '@type' => 'WebPage',
            '@id' => get_permalink($post_id),
        ];
        
        // Categorías como keywords
        $categories = get_the_category($post_id);
        $tags = get_the_tags($post_id);
        $keywords = [];
        
        if ($categories) {
            foreach ($categories as $cat) {
                $keywords[] = $cat->name;
            }
        }
        if ($tags) {
            foreach ($tags as $tag) {
                $keywords[] = $tag->name;
            }
        }
        
        if (!empty($keywords)) {
            $schema['keywords'] = implode(', ', $keywords);
        }
        
        // Número de palabras
        $word_count = str_word_count(strip_tags($post->post_content));
        if ($word_count > 0) {
            $schema['wordCount'] = $word_count;
        }
        
        // Lenguaje
        $schema['inLanguage'] = get_bloginfo('language');
        
        // Tipo específico de artículo
        $schema['articleSection'] = !empty($categories) ? $categories[0]->name : 'General';
        
        // Interacciones
        $comments_count = wp_count_comments($post_id);
        if ($comments_count->approved > 0) {
            $schema['commentCount'] = $comments_count->approved;
            $schema['interactionStatistic'] = [
                '@type' => 'InteractionCounter',
                'interactionType' => 'https://schema.org/CommentAction',
                'userInteractionCount' => $comments_count->approved,
            ];
        }
        
        return apply_filters('replanta_author_seo_article_schema', $schema, $post_id);
    }
    
    /**
     * Generar schema del autor (Person)
     */
    private function generate_author_schema($author_data, $author_id) {
        if (!$author_data) {
            return null;
        }
        
        $schema = [
            '@type' => 'Person',
            'name' => $author_data['name'],
            'url' => $author_data['url'],
        ];
        
        // Descripción
        $description = $author_data['description'];
        if (!empty($author_data['bio_extended'])) {
            $description .= ' ' . $author_data['bio_extended'];
        }
        if (!empty($description)) {
            $schema['description'] = trim($description);
        }
        
        // Título profesional
        if (!empty($author_data['job_title'])) {
            $schema['jobTitle'] = $author_data['job_title'];
        }
        
        // Organización
        if (!empty($author_data['organization'])) {
            $schema['worksFor'] = [
                '@type' => 'Organization',
                'name' => $author_data['organization'],
            ];
        }
        
        // Redes sociales (sameAs)
        if (!empty($author_data['social_links'])) {
            $same_as = array_values($author_data['social_links']);
            if (!empty($author_data['website_url'])) {
                $same_as[] = $author_data['website_url'];
            }
            $schema['sameAs'] = $same_as;
        }
        
        // Avatar personalizado
        $avatar_url = Replanta_Avatar_Uploader::get_author_avatar_url($author_id);
        if ($avatar_url) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => $avatar_url,
            ];
        }
        
        // Áreas de expertise
        if (!empty($author_data['expertise_areas'])) {
            $schema['knowsAbout'] = array_map('trim', explode(',', $author_data['expertise_areas']));
        }
        
        return apply_filters('replanta_author_seo_author_schema', $schema, $author_id);
    }
    
    /**
     * Generar schema del publisher (Organization)
     */
    private function generate_publisher_schema($options) {
        $schema = [
            '@type' => 'Organization',
            'name' => !empty($options['organization_name']) ? $options['organization_name'] : get_bloginfo('name'),
            'url' => !empty($options['organization_url']) ? $options['organization_url'] : home_url(),
        ];
        
        // Logo
        if (!empty($options['organization_logo'])) {
            $schema['logo'] = [
                '@type' => 'ImageObject',
                'url' => $options['organization_logo'],
            ];
        }
        
        return $schema;
    }
    
    /**
     * Obtener excerpt del post
     */
    private function get_post_excerpt($post_id) {
        $post = get_post($post_id);
        
        if (!empty($post->post_excerpt)) {
            return wp_strip_all_tags($post->post_excerpt);
        }
        
        // Generar excerpt automático
        $content = wp_strip_all_tags($post->post_content);
        return wp_trim_words($content, 30, '...');
    }
}
