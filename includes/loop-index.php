<?php
global $avia_config, $post_loop_count;


if(empty($post_loop_count)) $post_loop_count = 1;
$blog_style = !empty($avia_config['blog_style']) ? $avia_config['blog_style'] : avia_get_option('blog_style','multi-big');
if(is_single()) $blog_style = avia_get_option('single_post_style','single-big');
$blog_content = !empty($avia_config['blog_content']) ? $avia_config['blog_content'] : "content";
$initial_id = avia_get_the_ID();

// check if we got posts to display:
if (have_posts()) :

	while (have_posts()) : the_post();

	/*
     * get the current post id, the current post class and current post format
 	 */

	$current_post = array();
	$current_post['post_loop_count']= $post_loop_count;
	$current_post['the_id']	   	= get_the_ID();
	$current_post['parity']	   	= $post_loop_count % 2 ? 'odd' : 'even';
	$current_post['last']      	= count($wp_query->posts) == $post_loop_count ? " post-entry-last " : "";
	$current_post['post_class'] 	= "post-entry-".$current_post['the_id']." post-loop-".$post_loop_count." post-parity-".$current_post['parity'].$current_post['last']." ".$blog_style;
	$current_post['post_format'] 	= get_post_format() ? get_post_format() : 'standard';
	$current_post['post_layout']	= avia_layout_class('main', false);

	/*
     * retrieve slider, title and content for this post,...
     */
    $size = strpos($blog_style, 'big') ? (strpos($current_post['post_layout'], 'sidebar') !== false) ? 'entry_with_sidebar' : 'entry_without_sidebar' : 'square';
    if(!empty($avia_config['preview_mode']) && !empty($avia_config['image_size']) && $avia_config['preview_mode'] == 'custom') $size = $avia_config['image_size'];
	$current_post['slider']  	= get_the_post_thumbnail($current_post['the_id'], $size);
	
	if(is_single($initial_id) && get_post_meta( $current_post['the_id'], '_avia_hide_featured_image', true ) ) $current_post['slider'] = "";
	
	
	$current_post['title']   	= get_the_title();
	$current_post['content'] 	= $blog_content == "content" ? get_the_content(__('Read more','avia_framework').'<span class="more-link-arrow">  &rarr;</span>') : get_the_excerpt();
	$current_post['content'] 	= $blog_content == "excerpt_read_more" ? $current_post['content'].'<div class="read-more-link"><a href="'.get_permalink().'" class="more-link">'.__('Read more','avia_framework').'<span class="more-link-arrow">  &rarr;</span></a></div>' : $current_post['content'];
	$current_post['before_content'] = "";

	/*
     * ...now apply a filter, based on the post type... (filter function is located in includes/helper-post-format.php)
     */
	$current_post	= apply_filters( 'post-format-'.$current_post['post_format'], $current_post );
	$with_slider    = empty($current_post['slider']) ? "" : "with-slider";
	/*
     * ... last apply the default wordpress filters to the content
     */
	$current_post['content'] = str_replace(']]>', ']]&gt;', apply_filters('the_content', $current_post['content'] ));

	/*
	 * Now extract the variables so that $current_post['slider'] becomes $slider, $current_post['title'] becomes $title, etc
	 */
	extract($current_post);








	/*
	 * render the html:
	 */

	echo "<article class='".implode(" ", get_post_class('post-entry post-entry-type-'.$post_format . " " . $post_class . " ".$with_slider))."' ".avia_markup_helper(array('context' => 'entry','echo'=>false)).">";

        //default link for preview images
        $link = !empty($url) ? $url : get_permalink();
        
        //preview image description
        $featured_img_desc = the_title_attribute('echo=0');

        //on single page replace the link with a fullscreen image
        if(is_singular())
        {
            $link = avia_image_by_id(get_post_thumbnail_id(), 'large', 'url');
        }


        //echo preview image
        if(strpos($blog_style, 'big') !== false)
        {
            if($slider) $slider = '<a href="'.$link.'" title="'.$featured_img_desc.'">'.$slider.'</a>';
            if($slider) echo '<div class="big-preview '.$blog_style.'">'.$slider.'</div>';
        }

        if(!empty($before_content))
            echo '<div class="big-preview '.$blog_style.'">'.$before_content.'</div>';

        echo "<div class='blog-meta'>";

        $blog_meta_output = "";
        $icon =  '<span class="iconfont" '.av_icon_string($post_format).'></span>';

            if(strpos($blog_style, 'multi') !== false)
            {
                $gravatar = "";
                $link = get_post_format_link($post_format);
                if($post_format == 'standard')
                {
                	$author_name = apply_filters('avf_author_name', get_the_author_meta('display_name', $post->post_author), $post->post_author);
			$author_email = apply_filters('avf_author_email', get_the_author_meta('email', $post->post_author), $post->post_author);
                	
			$gravatar_alt = esc_html($author_name);
			$gravatar = get_avatar($author_email, '81', "blank", $gravatar_alt);
			$link = get_author_posts_url($post->post_author);
                }

                $blog_meta_output = "<a href='{$link}' class='post-author-format-type'><span class='rounded-container'>".$gravatar.$icon."</span></a>";
            }
            else if(strpos($blog_style, 'small')  !== false)
            {
                $blog_meta_output = "<a href='{$link}' class='small-preview' title='{$featured_img_desc}'>".$slider.$icon."</a>";
            }

        echo apply_filters('avf_loop_index_blog_meta', $blog_meta_output);

        echo "</div>";

        echo "<div class='entry-content-wrapper clearfix {$post_format}-content'>";
            echo '<header class="entry-content-header">';
                echo $title;

                echo "<span class='post-meta-infos'>";
                $markup = avia_markup_helper(array('context' => 'entry_time','echo'=>false));
                echo "<time class='date-container minor-meta' $markup>".get_the_time(get_option('date_format'))."</time>";
                echo "<span class='text-sep text-sep-date'>/</span>";



                    if ( get_comments_number() != "0" || comments_open() ){

                    echo "<span class='comment-container minor-meta'>";
                    comments_popup_link(  "0 ".__('Comments','avia_framework'),
                                          "1 ".__('Comment' ,'avia_framework'),
                                          "% ".__('Comments','avia_framework'),'comments-link',
                                          "".__('Comments Disabled','avia_framework'));
                    echo "</span>";
                    echo "<span class='text-sep text-sep-comment'>/</span>";
                    }


                    $taxonomies  = get_object_taxonomies(get_post_type($the_id));
                    $cats = '';
                    $excluded_taxonomies =  apply_filters('avf_exclude_taxonomies', array('post_tag','post_format'), get_post_type($the_id), $the_id);

                    if(!empty($taxonomies))
                    {
                        foreach($taxonomies as $taxonomy)
                        {
                            if(!in_array($taxonomy, $excluded_taxonomies))
                            {
                                $cats .= get_the_term_list($the_id, $taxonomy, '', ', ','').' ';
                            }
                        }
                    }

                    if(!empty($cats))
                    {
                        echo '<span class="blog-categories minor-meta">'.__('in','avia_framework')." ";
                        echo $cats;
                        echo '</span><span class="text-sep text-sep-cat">/</span>';
                    }


                    echo '<span class="blog-author minor-meta">'.__('by','avia_framework')." ";
                    echo '<span class="entry-author-link" '.avia_markup_helper(array('context' => 'author_name','echo'=>false)).'>';
                    echo '<span class="vcard author"><span class="fn">';
                    the_author_posts_link();
                    echo '</span></span>';
                    echo '</span>';
                    echo '</span>';

                    echo '<span class="text-sep text-sep-last-updated">/</span>';
                    echo '<span class="last-updated minor-meta">'.__('Last Updated:', 'avia_framework')." ";
                    echo "<time class='date-container minor-meta updated' $markup>".get_the_modified_time(get_option('date_format'))."</time>";

                echo '</span>';
            echo '</header>';


            // echo the post content
            echo '<div class="entry-content" '.avia_markup_helper(array('context' => 'entry_content','echo'=>false)).'>';
            echo $content;
            echo '</div>';

            echo '<footer class="entry-footer">';

            $avia_wp_link_pages_args = apply_filters('avf_wp_link_pages_args', array(
                                                                                    'before' =>'<nav class="pagination_split_post">'.__('Pages:','avia_framework'),
                                                                                    'after'  =>'</nav>',
                                                                                    'pagelink' => '<span>%</span>',
                                                                                    'separator'        => ' ',
                                                                                    ));

            wp_link_pages($avia_wp_link_pages_args);

            if(is_single() && !post_password_required())
            {
            	//tags on single post
            	if(has_tag())
            	{
                	echo '<span class="blog-tags minor-meta">';
                	the_tags('<strong>'.__('Tags:','avia_framework').'</strong><span> ');
                	echo '</span></span>';
            	}
            	
            	//share links on single post
            	avia_social_share_links();
   
            }
            
            do_action('ava_after_content', $the_id, 'post');

            echo '</footer>';

        echo "<div class='post_delimiter'></div>";
        echo "</div>";
        echo "<div class='post_author_timeline'></div>";
	echo "</article>";

	$post_loop_count++;
	endwhile;
	else:

?>

    <article class="entry">
        <header class="entry-content-header">
            <h1 class='post-title entry-title'><?php _e('Nothing Found', 'avia_framework'); ?></h1>
        </header>

        <p class="entry-content" <?php avia_markup_helper(array('context' => 'entry_content')); ?>><?php _e('Sorry, no posts matched your criteria', 'avia_framework'); ?></p>

        <footer class="entry-footer"></footer>
    </article>

<?php

	endif;

	if(empty($avia_config['remove_pagination'] ))
	{
		echo "<div class='{$blog_style}'>".avia_pagination('', 'nav')."</div>";
	}
?>
