<?php

/**
* Plugin Name: Advance Recent Posts Widget
* Description: Advance Recent Posts Widget for blog posts, pages and all types of custom post types.
* Author: M Ishtiaq Awan
* Version: 1.0.0
* Author URI: https://www.allshorevirtualstaffing.com/resume/?dev_id=740770587&dev_name=Ishtiaq%20A.
* Copyright: 2017 AllShoreVirtualStaffing.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
* Text Domain: adv-rpw
*
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ADV_RPW_VERSION', '1.0.0' );
define( 'ADV_RPW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );


class ADV_Recent_Posts_Widget_Init {
		 /**
		 * Plugin's Instance.
		 *
		 * @access private
		 * @since 1.0.0
		 * @var ADV_Recent_Posts_Widget_Init
		 */		
		private static $instance;

		/**
		 * Get the class instance
		 *
		 * @access public
		 * @since 1.0.0
		 * @return mixed ADV_Recent_Posts_Widget_Init instance
		 */
		public static function get_instance() {
			return null === self::$instance ? ( self::$instance = new self ) : self::$instance;
		}

		/**
		 * Class constructor
		 *
		 * @access public
		 * @since 1.0.0
		 *
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'load_textdomain' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_links' ) );
			
			add_action( 'wp_ajax_adv_rpw_get_taxonomies', array( $this, 'adv_rpw_get_taxonomies' ) );
			add_action( 'wp_ajax_nopriv_adv_rpw_get_taxonomies', array( $this, 'adv_rpw_get_taxonomies' ) );
			
			add_action( 'widgets_init', array($this,'ADV_RPW_Register'));			
		}

		
		/**
		 * Localisation
		 *
		 * @access public
		 * @since 1.0.0
		 *
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'adv-rpw', false, basename( dirname( __FILE__ ) ) . '/languages' );
		}

		/**
		* Plugin page links.
		 *
		 * @access public
		 * @since 1.0.0
		 * @param mixed $links plugin links
		 * @return mixed $links plugin links
		*/
		public function plugin_links( $links ) {
			$plugin_links = array(
				'<a href="http://www.datumsquare.com/#contact">' . __( 'Support', 'adv-rpw' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Register Advance Recent Posts Widget
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function ADV_RPW_Register( ) {
			include_once( ADV_RPW_PLUGIN_DIR . '/includes/class-advance-rpw.php' );
			register_widget( 'ADV_Recent_Posts_Widget');
		}

		/**
		 * Get Taxonomies for selected post type
		 *
		 * @access public
		 * @since 1.0.0
		 *
		 */
		public function adv_rpw_get_taxonomies() {
			ob_clean();

			$ptype = $_POST['posttype'];

			$post_type_taxonomies = get_object_taxonomies($ptype,'objects');
			if(count($post_type_taxonomies)>0)
			{
				foreach ($post_type_taxonomies as $post_type_taxonomy ) {
					$terms = get_terms( array('taxonomy' => $post_type_taxonomy->name,'hide_empty' => false) );
					$opts_group='';
					foreach($terms as $term)
				   	{
				   		$opts_group .= '<option value="' . esc_attr($post_type_taxonomy->name).':'.esc_attr($term->slug). '">' . esc_html__($term->name) . '</option>';
				   	}

				   	if(!empty($opts_group))
				   	{
				   		echo '<optgroup label="'.esc_html__($post_type_taxonomy->label).'">'.$opts_group.'</optgroup>';
				   	}
				}
			}
			else
			{
				echo '<option value="0">No taxonomy found in "'.$ptype.'"</option>';
			}

			wp_die();
		}
	}

	add_action( 'plugins_loaded' , array( 'ADV_Recent_Posts_Widget_Init', 'get_instance' ), 0 );