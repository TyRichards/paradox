<?php
/**
 * Convert a string to a slug
 *
 * @param string $str Input string
 * @return string Valid URL string
 */
function zilla_to_slug($str)
{
	$str = strtolower(trim($str));
	$str = preg_replace('/[^a-z0-9-]/', '-', $str);
	$str = preg_replace('/-+/', "-", $str);
	return $str;
}

/**
 * Generate the labels for custom post types
 *
 * @param string $singular The singular post type name
 * @param string $plural The plural post type name
 * @return array Array of labels
 */
function zilla_post_type_labels( $singular, $plural = '' )
{
    if( $plural == '') $plural = $singular .'s';
    
    return array(
        'name' => _x( $plural, 'post type general name', 'zilla' ),
        'singular_name' => _x( $singular, 'post type singular name', 'zilla' ),
        'add_new' => __( 'Add New', 'zilla' ),
        'add_new_item' => sprintf( __( 'Add New %s', 'zilla' ), $singular),
        'edit_item' => sprintf( __( 'Edit %s', 'zilla' ), $singular),
        'new_item' => sprintf( __( 'New %s', 'zilla' ), $singular),
        'view_item' => sprintf( __( 'View %s', 'zilla' ), $singular),
        'search_items' => sprintf( __( 'Search %s', 'zilla' ), $plural),
        'not_found' =>  sprintf( __( 'No %s found', 'zilla' ), $plural),
        'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'zilla' ), $plural),
        'parent_item_colon' => ''
    );
}

/**
 * Generate the labels for custom taxonomies
 *
 * @param string $singular The singular taxonomy name
 * @param string $plural The plural taxonomy name
 * @return array Array of labels
 */
function zilla_taxonomy_labels( $singular, $plural = '' )
{
    if( $plural == '') $plural = $singular .'s';
    
    return array(
        'name' => _x( $plural, 'taxonomy general name', 'zilla' ),
        'singular_name' => _x( $singular, 'taxonomy singular name', 'zilla' ),
        'search_items' => sprintf( __( 'Search %s', 'zilla' ), $plural),
        'popular_items' => sprintf( __( 'Popular %s', 'zilla' ), $plural),
        'all_items' => sprintf( __( 'All %s', 'zilla' ), $plural),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => sprintf( __( 'Edit %s', 'zilla' ), $singular),
        'update_item' => sprintf( __( 'Update %s', 'zilla' ), $singular),
        'add_new_item' => sprintf( __( 'Add New %s', 'zilla' ), $singular),
        'new_item_name' => sprintf( __( 'New %s Name', 'zilla' ), $singular),
        'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'zilla' ), $plural),
        'add_or_remove_items' => sprintf( __( 'Add or remove %s', 'zilla' ), $plural),
        'choose_from_most_used' => sprintf( __( 'Choose from the most used %s', 'zilla' ), $plural)
    ); 
}

/**
 * Are there any third party SEO plugins active
 *
 * @return bool True is other plugin is detected
 */
function zilla_is_third_party_seo()
{
	include_once ABSPATH .'wp-admin/includes/plugin.php';
	
	if( is_plugin_active('headspace2/headspace.php') ) return true;
	if( is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php') ) return true;
	if( is_plugin_active('wordpress-seo/wp-seo.php') ) return true;
	
	return false;
}

?>