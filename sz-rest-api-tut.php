<?php
/**
 * Plugin Name: SZ WP REST API Tutorial
 * Description: It's an example of WP REST API usage for search with autocomplete with vanilla JS
 * Plugin URI:  
 * Author:      Sabrina Zeidan
 * Author URI:  https://sabrinazeidan.com
 * License:     GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

defined( 'ABSPATH' ) or die();

class SZ_WP_REST_API_Tut { 
    function __construct() {		
        add_action( 'admin_menu', array( $this, 'admin_menu' ) ); //Add an item to the Tools menu		
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'plugin_actions_links' ) ); //Add a link on Plugins page
		
		add_action( 'rest_api_init', array( $this, 'rest_api_init') ); // Initialize the REST API routes.		
    } 
 
    public function admin_menu() {
           $hook = add_management_page( 'SZ WP REST API', 'SZ WP REST API', 'manage_options', 'sz-search', array( $this, 'admin_page_content' ), '' ); //Add a page to the Tools menu
           add_action( "load-$hook", array( $this, 'admin_page_load' ) );//hook to load stuff on that page
    }
 	function plugin_actions_links( array $actions ) {
			return array_merge( array(
			'sz-search'    => sprintf('<a href="%s">%s</a>', esc_url( admin_url( 'tools.php?page=sz-search' ) ),esc_html__( 'See it', '' ))), $actions );
	}
    function admin_page_load() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) ); // Load needed JavaScript and CSS		
    }
	
	function enqueue_scripts() {
			wp_enqueue_style( 'awesomplete-css', plugin_dir_url( __FILE__ ) . 'awesomplete/awesomplete.css'); //Awesomplete widget
			wp_enqueue_script('awesomplete-js', plugin_dir_url( __FILE__ ) . 'awesomplete/awesomplete.js'); //Awesomplete widget
	     	wp_enqueue_script('sz-search', plugin_dir_url( __FILE__ ) . 'search.js', array('awesomplete-js')); //Our Awesomplete settings are here
			wp_localize_script('sz-search', 'szsearch', array(
				'search_url' => home_url( '/wp-json/sz-search/search?term=' ), // URL to access REST API endpoint
				'nonce' => wp_create_nonce('wp_rest') ) //For authorization
			);	
	}
	
	function rest_api_init() { 
		register_rest_route( 'sz-search', '/search', array( 
			'methods'  => 'GET',
			'callback' => array( $this, 'sz_rest_api_search'), //exactly how we search
			'permission_callback' => function( WP_REST_Request $request ) { return current_user_can( 'manage_options' ); } //Restrict endpoint to  internal calls 
		) );
	}
	
	function sz_rest_api_search( WP_REST_Request $request ) {
		$search_term = $request->get_param( 'term' );//Our input from the field
		if ( empty( $search_term ) ) {
			return;
		}	
			//The way we're gonna search
			$args = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page'   => 3,
				'fields'   => 'ids',
				's'             => $search_term,
				'no_found_rows' => true,  
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				);
			$the_query = new WP_Query( $args );								
			$this_blog_found_posts = $the_query->posts;
				$temp = array();
				foreach( $this_blog_found_posts as $key => $post_id) { 
					$temp = array(
						'ID' => $post_id,
						'permalink' => get_permalink($post_id),
						'label' => get_the_title($post_id),
						);
					$posts[] = $temp;											
				}	
		
			if (!empty($posts)) return $posts;
	}	
	
    function admin_page_content() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html_x( 'SZ WP REST API Search with autocomplete with Vanilla JavaScript', 'admin page title', 'sz-wp-rest-api-search' ) . '</h1>';		
		echo '<p>This is a search field intended to demostrate a result of WP REST API. Does it work? :)</br>';
		echo '<p><input type="text" size="80" id="sz-search-field" name="sz-search-field" value="" placeholder="Start typing the title of the post...">';//Our search field
		echo '<br><input type="hidden" size="80" id="sz_result_id" name="sz_result_id" value="">';//Hidden field to pass post ID
		echo '<br><input type="hidden" size="80" id="sz_result_permalink" name="sz_result_permalink" value="">';//Hidden field to pass post permalink
		echo '</div>';
    }
}
new SZ_WP_REST_API_Tut(); //Let's do it!