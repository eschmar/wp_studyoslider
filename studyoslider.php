<?php
/*
Plugin Name: Studyo Slider
Plugin URI: https://github.com/eschmar/wp_studyoslider
Description: Custom post type for Slides
Version: 1.0
Author: Marcel Eschmann
Author URI: https://github.com/eschmar
License: MIT
*/


/***************************************************************************
 * ACTIVATE THUMBNAIL SUPPORT
 ***************************************************************************/
add_theme_support( 'post-thumbnails' );


/***************************************************************************
 * CUSTOM SLIDER THUMBNAIL SIZE
 ***************************************************************************/
add_image_size( 'slider-post-list-image', 100, 50, true );


/***************************************************************************
 * I18N / L10N
 ***************************************************************************/
function studyo_slider_i18n() {
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'studyoslider', false, $plugin_dir );
}
add_action('plugins_loaded', 'studyo_slider_i18n');


/***************************************************************************
 * REGISTER CUSTOM POST TYPE
 ***************************************************************************/
function studyo_slider_register() {
	$args = array(
		'labels' => array(
			'name' => '',
			'singular_name'      => __( 'Slide', 'studyoslider' ),
			'add_new'            => __( 'Add New', 'studyoslider' ),
			'add_new_item'       => __( 'Add New Slide', 'studyoslider' ),
			'edit_item'          => __( 'Edit Slide', 'studyoslider' ),
			'new_item'           => __( 'New Slide', 'studyoslider' ),
			'all_items'          => __( 'All Slides', 'studyoslider' ),
			'view_item'          => __( 'View Slide', 'studyoslider' ),
			'search_items'       => __( 'Search Slides', 'studyoslider' ),
			'not_found'          => __( 'No slides found', 'studyoslider' ),
			'not_found_in_trash' => __( 'No slides found in the Trash', 'studyoslider' ), 
			'parent_item_colon'  => '',
			'menu_name'          => 'Slider'
		),
		'description'   => 'Holds all slides',
		'public'        => true,
		'menu_position' => 3,
		'supports'      => array( 'title', 'editor', 'thumbnail' ),
		'has_archive'   => false
	);
	register_post_type( 'slider', $args );	
}
add_action( 'init', 'studyo_slider_register' );


/***************************************************************************
 * REGISTER TAXONOMY
 ***************************************************************************/
function studyo_slider_taxonomies() {
	$args = array(
		'labels' => array(
			'name'              => __( 'Slide Categories', 'studyoslider' ),
			'singular_name'     => __( 'Slide Category', 'studyoslider' ),
			'search_items'      => __( 'Search Slide Categories', 'studyoslider' ),
			'all_items'         => __( 'All Slide Categories', 'studyoslider' ),
			'parent_item'       => __( 'Parent Slide Category', 'studyoslider' ),
			'parent_item_colon' => __( 'Parent Slide Category:', 'studyoslider' ),
			'edit_item'         => __( 'Edit Slide Category', 'studyoslider' ), 
			'update_item'       => __( 'Update Slide Category', 'studyoslider' ),
			'add_new_item'      => __( 'Add New Slide Category', 'studyoslider' ),
			'new_item_name'     => __( 'New Slide Category', 'studyoslider' ),
			'menu_name'         => __( 'Slide Categories', 'studyoslider' ),
		),
		'hierarchical' => true
	);
	register_taxonomy( 'slider_category', 'slider', $args );
}
add_action('init', 'studyo_slider_taxonomies', 0);


/***************************************************************************
 * FILTERABLE BY TAXONOMY
 ***************************************************************************/
function studyo_slider_restrict_manage_posts() {
	global $typenow;
	$taxonomy = $typenow.'_category';
	if( $typenow == "slider" ){
		$filters = array($taxonomy);
		foreach ($filters as $tax_slug) {
			$tax_obj = get_taxonomy($tax_slug);
			$tax_name = $tax_obj->labels->name;
			$terms = get_terms($tax_slug);
			echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
			echo "<option value=''>".__( 'All Categories' )."</option>";
			foreach ($terms as $term) { echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>'; }
			echo "</select>";
		}
	}
}
add_action( 'restrict_manage_posts', 'studyo_slider_restrict_manage_posts' );


/***************************************************************************
 * ADD CUSTOM COLUMNS
 ***************************************************************************/
function studyo_slider_custom_columns( $columns ) {
	// Add new columns
	$columns['slider_order'] = __( 'Order' );
	$columns['slider-category'] = __( 'Category' );
	$columns['featured_image'] = __( 'Featured Image' );

	// Move date column to the end
	$temp = $columns['date'];
	unset($columns['date']);
	$columns['date'] = $temp;

	return $columns;
}
add_filter('manage_edit-slider_columns', 'studyo_slider_custom_columns');


/***************************************************************************
 * ISNERT CUSTOM COLUMNS CONTENT
 ***************************************************************************/
function studyo_slider_custom_columns_content( $column, $post_id ) {
	global $post;
	if ($column == 'featured_image') {
		the_post_thumbnail('slider-post-list-image');
	}else if ($column == 'slider-category') {
		$terms = get_the_terms($post_id, 'slider_category');
		if (empty($terms)) {
			echo '-';
		}else {
			$output = array();
			foreach ($terms as $t) {
				array_push($output, $t->slug);
			}
			echo join(', ', $output);
		}
	}else if ($column == 'slider_order') {
		echo get_post_meta($post_id, 'slider_order', true);
	}
}
add_action( 'manage_slider_posts_custom_column', 'studyo_slider_custom_columns_content', 10, 2 );


/***************************************************************************
 * SORTING META BOX
 ***************************************************************************/
// Add meta box
add_action( 'add_meta_boxes', 'studyo_slider_meta_box' );
function studyo_slider_meta_box() {
    add_meta_box( 
        'slider_order_box',
        __( 'Attributes' ),
        'slider_order_box_content',
        'slider',
        'side'
    );
}

// Content of the meta box
function slider_order_box_content( $post ) {
	wp_nonce_field( plugin_basename( __FILE__ ), 'slider_order_box_content_nonce' );

	$str_order = __('Order');
	$str_order_description = __('Slides are displayed in ascending order.', 'studyoslider');
	$str_caption = __('CSS Classes', 'studyoslider');
	$str_caption_description = __('CSS classes added to this slides\' caption.', 'studyoslider');

	$value = get_post_meta($post->ID, 'slider_order', true);
	$caption = get_post_meta($post->ID, 'slider_caption_classes', true);

	$form =
<<<EOT
	$str_order
	<input type="text" name="slider_order" id="slider_order" value="$value"/><br/>
	<i>$str_order_description</i><br/><br/>
	$str_caption:
	<input type="text" name="slider_caption_classes" id="slider_caption_classes" value="$caption"/><br/>
	<i>$str_caption_description</i><br/><br/>
EOT;

	echo $form;
}

// Save data from metabox
function studyo_slider_meta_box_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	return;

	if ( !wp_verify_nonce( $_POST['slider_order_box_content_nonce'], plugin_basename( __FILE__ ) ) )
	return;

	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
		return;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
		return;
	}

	$slider_order = $_POST['slider_order'];
	$slider_caption_classes = $_POST['slider_caption_classes'];

	update_post_meta( $post_id, 'slider_order', $slider_order );
	update_post_meta( $post_id, 'slider_caption_classes', $slider_caption_classes );
}
add_action( 'save_post', 'studyo_slider_meta_box_save' );


/***************************************************************************
 * OUTPUT SLIDER HTML
 ***************************************************************************/
function studyo_slider_output($slug, $wrap_class = 'flexslider', $ul_class = 'slides', $caption_class = 'flex-caption' ) {

	$args = array(
		'post_type' => 'slider',
		'slider_category' => $slug,
		'order' => 'ASC',
		'meta_key' => 'slider_order',
		'orderby' => 'meta_value_num'
	);

	$slides = new WP_Query($args);

	$output = '<div class="'.$wrap_class.'"><ul class="'.$ul_class.'">';

	if ($slides->have_posts()) {
		while ($slides->have_posts()) {
			$slides->the_post();
			$output .= '<li>';
			$attachement = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
			$output .= '<img src="'.$attachement[0].'" alt="'.get_the_title().'">';
			$content = get_the_content();
			if (!empty($content)) {
				$caption_classes = get_post_meta( get_the_ID(), 'slider_caption_classes', true );
				$output .= '<p class="'.$caption_class.' '.$caption_classes.'">'.$content.'</p>';
			}

			$output .= '</li>';
		}
	}

	$output .= '</ul></div>';
	echo $output;
}


/***************************************************************************
 * CONTEXTUAL HELP (UPPER RIGHT CORNER "HELP")
 ***************************************************************************/
function studyo_slider_contextual_help( $contextual_help, $screen_id, $screen ) { 
	if ( 'edit-slider' == $screen->id ) {

		$contextual_help = '<h2>Output</h2>
		<p>Use this function to output a slider in your template:</p>
		<i>studyo_slider_output($slug, $wrap_class = "flexslider", $ul_class = "slides", $caption_class = "flex-caption" );</i>
		<p><strong>$slug: </strong>Slider Category</p>
		<p><strong>$wrap_class Image: </strong>Add a class to the wrapping div. Default is "flexslider"</p>
		<p><strong>$ul_class: </strong>Add a class to the ul. Default is "slides"</p>
		<p><strong>$caption_class: </strong>Add a class to the caption div. Default is "flex-caption"</p>';

	} elseif ( 'slider' == $screen->id ) {

		$contextual_help = '<h2>Editing Slides</h2>
		<p><strong>Content: </strong>Caption</p>
		<p><strong>Featured Image: </strong>Slide image</p>
		<p><strong>Order: </strong>Ascending order of slides</p>';

	}
	return $contextual_help;
}
add_action( 'contextual_help', 'studyo_slider_contextual_help', 10, 3 );
