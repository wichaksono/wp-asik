<?php

if (!function_exists('rm_query_ver')) {
    /**
     * Remove Query '?' in stylesheet uri and script uri
     */
    function rm_query_ver($src)
    {
        if (strpos($src, '?ver=')) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }

    add_filter('style_loader_src', 'rm_query_ver', 10, 2);
    add_filter('script_loader_src', 'rm_query_ver', 10, 2);
}

if (!function_exists('disable_wp_emojicons')) {
    function disable_wp_emojicons() 
    {
        // all actions related to emojis
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
        remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

        // filter to remove TinyMCE emojis
        add_filter( 'tiny_mce_plugins', 'disable_emojicons_tinymce' );
    }
    
    add_action('init', 'disable_wp_emojicons');
}

if (!function_exists('jetpack_photon_url')) {
    /**
     * [replace_with_cdn_iwp description]
     * @param  [type] $content [description]
     * @return [type]          [description]
     */
    function replace_with_cdn_iwp($content)
    {
        $content = preg_replace('/src\=(\")\/\//i', 'src="//i0.wp.com/', $content);
        $content = preg_replace('/src\=(\')\/\//i', 'src=\'//i0.wp.com/', $content);
        $content = preg_replace('/src\=(\")(https?\:|http?\:)\/\//i', 'src="//i0.wp.com/', $content);
        $content = preg_replace('/src\=(\')(https?\:|http?\:)\/\//i', 'src=\'//i0.wp.com/', $content);
        return $content;
    }
    add_action('the_content', 'replace_with_cdn_iwp');

    /**
     * [replace_thumb_cdn_iwp description]
     */
    function replace_thumb_cdn_iwp($html, $post_id, $post_thumbnail_id, $size, $attr)
    {
        $id  = get_post_thumbnail_id();
        $src = wp_get_attachment_image_src($id, $size);
        $src = str_replace(array('http://','https://'), '//i1.wp.com/', $src[0]);
        $alt = get_the_title($id);
        $class = (isset($attr['class']))? $attr['class'] : '';
        
        if (strlen(trim($src)) == '') {
            return $html;
        } else {
            $html = '<img src="' . $src . '" alt="' . $alt . '" class="'.$class.'" itemprop="image"/>';
            return $html;    
        }
    }
    add_filter('post_thumbnail_html', 'replace_thumb_cdn_iwp', 99, 5);
}

if (!function_exists('sharedto')) {
    /**
     * function to create icon share post to some medsos
     * @use <?php sharedto();?> take on single.php
     * @return [print]              [icon]
     */
    function sharedto()
    {
        $url = get_the_permalink();
        $facebook  = 'https://www.facebook.com/sharer/sharer.php?u='.$url;
        $facebook  = "window.open('{$facebook}', '_blank', 'location=yes,width=350,scrollbars=no,status=no')";
        $twitter   = 'https://twitter.com/home?status='.$url;
        $twitter   = "window.open('{$twitter}', '_blank', 'location=yes,width=350,scrollbars=no,status=no')";
        $gplus     = 'https://plus.google.com/share?url='.$url;
        $gplus     = "window.open('{$gplus}', '_blank', 'location=yes,width=500,scrollbars=no,status=no')";

        $share = '<div class="ico-share">';
        $share .= '<a class="amethyst-flat-button" onclick="'.$facebook.'">facebook</a>';
        $share .= '<a class="wisteria-flat-button" onclick="'.$twitter.'">twitter</a>';
        $share .= '<a class="alizarin-flat-button" onclick="'.$gplus.'">google+</a>';
        $share .= '</div>';
        echo __($share);
    }
}

if (!function_exists('breadcrumb_nav')) {
    /**
     * [breadcrumb_nav description]
     * @return [type] [description]
     */
    function breadcrumb_nav($arg = array())
    {
        if (!get_set_value('Breadcrumb')) {
            return false;
        }

        $default = array(
            'class' => '',
            'id'=>'breadcrumb'
            );
        if (is_array($arg)) {
            $default = array_merge($default,$arg);
        }
        if (!wp_is_mobile()) {
            echo '<ol itemscope class="'.$default['class'].'" id="'.$default['id'].'" itemtype="http://schema.org/BreadcrumbList">';
            if (!is_home()) {
                echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
                echo '<a itemprop="item" href="'.home_url().'"><span itemprop="name"><i class="fa fa-home fa-lg"></i></span></a>';
                echo '<meta itemprop="position" content="1" /></li>';
                if (is_category() || is_single()) {
                    $category = get_the_category();
                    echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
                    echo '<a itemprop="item" href="'.get_category_link($category[0]->cat_ID).'"><span itemprop="name">'.$category[0]->name.'</span></a>';
                    echo '<meta itemprop="position" content="2" /></li>';
                    if (is_single()) {
                        echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
                        echo '<a itemprop="item" href="'.get_the_permalink().'"><span itemprop="name">'.get_the_title().'</span></a>';
                        echo '<meta itemprop="position" content="3" /></li>';
                    }
                } elseif (is_page()) {
                    echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
                    echo '<a itemprop="item" href="'.get_the_permalink().'"><span itemprop="name">'.get_the_title().'</span></a>';
                    echo '<meta itemprop="position" content="2" /></li>';
                } elseif (is_tag()) {
                    echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
                    echo '<a itemprop="item" href="#"><span itemprop="name">'.single_tag_title("", false).'</span></a>';
                    echo '<meta itemprop="position" content="2" /></li>';
                } elseif (is_day()) {
                    echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">Archive for&nbsp;';
                    echo '<a itemprop="item" href="#"><span itemprop="name">'.get_the_time('D, d M Y').'</span></a>';
                    echo '<meta itemprop="position" content="2" /></li>';
                } elseif (is_month()) {
                    echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">Archive for&nbsp;';
                    echo '<a itemprop="item" href="#"><span itemprop="name">'.get_the_time('M Y').'</span></a>';
                    echo '<meta itemprop="position" content="2" /></li>';
                } elseif (is_year()) {
                    echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">Archive for&nbsp;';
                    echo '<a itemprop="item" href="#"><span itemprop="name">'.get_the_time('Y').'</span></a>';
                    echo '<meta itemprop="position" content="2" /></li>';
                } elseif (is_author()) {
                    echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
                    echo '<a itemprop="item" href="#"><span itemprop="name">Author Archive</span></a>';
                    echo '<meta itemprop="position" content="2" /></li>';
                } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) {
                    echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
                    echo '<a itemprop="item" href="#"><span itemprop="name">Blog Archives</span></a>';
                    echo '<meta itemprop="position" content="2" /></li>';
                } elseif (is_search()) {
                    echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
                    echo '<a itemprop="item" href="#"><span itemprop="name">Search Results</span></a>';
                    echo '<meta itemprop="position" content="2" /></li>';
                }
            }
                echo '</ol>';
        }
    }
}

if (!function_exists('related_post')) {
    /**
     * [Related_post description]
     */
    function related_post()
    {
        if (!get_set_value('RelatedPost')) {
            return false;
        }
      
        global $post;
        $numberposts = 5;
        $return = false;
        if (get_set_value('RelatedBy') == 'tag') {
            $tags = wp_get_post_tags( $post->ID );
            $tag_arr = '';
            foreach( $tags as $tag ) {
                $tag_arr .= $tag->slug . ',';
            }
            $tag_arr = rtrim($tag_arr,',');
            $args = array(
                'tag' => $tag_arr,
                'post_type'=>'post',
                'numberposts' => $numberposts , /* You can change this to show more */
                'post__not_in' => array($post->ID)
            );

            $return = true;

        } else {
            $categories = wp_get_post_categories( $post->ID );
            $tag_arr ='';

            $cats = '';
            foreach ($categories as $c) {
                $cat = get_category($c);
                $cats = $cat->slug. ',';
            }
            $slug = rtrim($cats, ',');
            $args = array(
                'category_name' => $slug,
                'post_type' => 'post',
                'numberposts'=> $numberposts ,
                'post__not_in'=> array($post->ID)
                );
            $return = true;
        }

        if ($return) {
            $related_posts = get_posts( $args );
            if($related_posts) {
            echo '<h4>Related Posts</h4>';
            echo '<ul id="joints-related-posts">';
                foreach ( $related_posts as $post ) : setup_postdata( $post ); ?>
                    <li class="related_post">
                        <a class="entry-unrelated" href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                        <?php get_template_part( 'partials/content', 'byline' ); ?>
                    </li>
                <?php endforeach; }
                }
        wp_reset_postdata();
        echo '</ul>';
    }
}
?>
