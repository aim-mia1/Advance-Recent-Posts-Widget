<?php if ( ! defined( 'ABSPATH' ) ) {exit;}

/**
* WC_Shipping_AFR class.
*
* @inherits  WC_Shipping_Method
* @since 1.0.0
* @version 1.1.0
*/
class ADV_Recent_Posts_Widget extends WP_Widget{
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @version 1.0.0
	 */
	function __construct() {
		// Instantiate the parent object
		parent::__construct( false, 'Advance Recent Posts',array('description'=>'Show recent posts from any post type.') );
	}

	/**
	 * Widget Function
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget( $args, $instance ) {

		//var_dump($args);exit;
		// Widget output
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		//echo esc_html__( 'Hello, World!', 'adv-rpw' );
		$adv_recent_post_type = ( ! empty( $instance['posttype'] ) ) ? strip_tags( $instance['posttype'] ) : 'post';
		$adv_recent_post_type_tax = ( ! empty( $instance['posttaxonomies'] ) ) ? strip_tags( $instance['posttaxonomies'] ) : '';
		$adv_recent_post_num = ( ! empty( $instance['noofposts'] ) ) ? strip_tags( $instance['noofposts'] ) : '5';

		$taxquery=array();
		if(!empty($adv_recent_post_type_tax) && strlen($adv_recent_post_type_tax)>0)
		{
			$sel_taxo = explode("-:-",$adv_recent_post_type_tax);

			if(count($sel_taxo)>0)
			{
				foreach($sel_taxo as $seltax)
				{
					$seltaxparts = explode(":",$seltax);
					$taxquery[] = array('taxonomy' => $seltaxparts[0],'terms' => $seltaxparts[1]);
				}
			}
		}

		$args = array(
			'posts_per_page'   => $adv_recent_post_num,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'post_type'        => $adv_recent_post_type,
			'post_status'      => 'publish',
			'suppress_filters' => true,
			'tax_query' => $taxquery
		);
		$posts_array = get_posts( $args ); 
		//var_dump($posts_array);
		echo '<ul id="" class="">';
		foreach($posts_array as $adv_post)
		{
			setup_postdata( $adv_post );
			echo '<li><b>'.esc_html__($adv_post->post_title).'</b><p>'.((strlen($adv_post->post_content)>255)?esc_html__(substr($adv_post->post_content, 0,255)).' <a href="'.get_permalink().'">[Read More]</a>':esc_html__($adv_post->post_content)).'</p></li>';
		}

		echo '</ul>';

		echo $args['after_widget'];

		wp_reset_postdata();
	}

	/**
	 * Update Function
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 *
	 */
	function update( $new_instance, $old_instance ) {
		// Save widget options

		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['posttype'] = ( ! empty( $new_instance['posttype'] ) ) ? strip_tags( $new_instance['posttype'] ) : '';
		$instance['posttaxonomies'] = ( ! empty( $new_instance['posttaxonomies'] ) ) ? strip_tags( implode("-:-",$new_instance['posttaxonomies']) ) : '';
		$instance['noofposts'] = ( ! empty( $new_instance['noofposts'] ) ) ? strip_tags( $new_instance['noofposts'] ) : '';

		return $instance;
	}

	/**
	 * Form Function
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param array $instance Previously saved values from database.
	 */
	function form( $instance ) {
		// Output admin widget options form
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Recent Posts', 'adv-rpw' );?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'adv-rpw' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<?php $cposttype = ! empty( $instance['posttype'] ) ? $instance['posttype'] : esc_html__( '0', 'adv-rpw' ); ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'posttype' ) ); ?>"><?php esc_attr_e( 'Post Type:', 'adv-rpw' ); ?></label> 
			<select name="<?php echo esc_attr( $this->get_field_name( 'posttype' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'posttype' ) ); ?>" style="width:100%">
			<option value="0">Select Post Type</option>
			<?php 
				$post_types = get_post_types(array('public'   => true));
				foreach ($post_types as $post_type ) {
				   echo '<option value="' . esc_attr($post_type) . '"  '.((esc_attr($post_type) == $cposttype)?'selected':'').'>' . esc_html__($post_type) . '</option>';
				}

			?>
			</select>
		</p>
		<script type="application/javascript">
			jQuery(function(){
				jQuery( '#<?php echo esc_attr( $this->get_field_id( 'posttype' ) ); ?>' ).on( 'change',  function () {
			
					var posttype = $(this).val();
			    	jQuery.ajax({
			        	type: 'POST',
			        	url: '<?php echo admin_url('admin-ajax.php'); ?>',
			        	data: {
			        	    'posttype': posttype,
			        	    'action': 'adv_rpw_get_taxonomies' //this is the name of the AJAX method called in WordPress
			        	}, 
			        	success: function (result) {
				           jQuery('.widget-content select#<?php echo esc_attr( $this->get_field_id( 'posttaxonomies' ) );?>').html(result);
				        },
				        error: function () {
				            jQuery('.widget-content select#<?php echo esc_attr( $this->get_field_id( 'posttaxonomies' ) );?>').html('Please try again.');
				        }
				    });
				});
			});
		</script>

		<?php $cposttaxonomies = ! empty( $instance['posttaxonomies'] ) ? $instance['posttaxonomies'] : esc_html__( 0, 'adv-rpw' ); 
		//echo $cposttype; ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'posttaxonomies' ) ); ?>"><?php esc_attr_e( 'Taxonomies:', 'adv-rpw' ); ?></label> 
			<select name="<?php echo esc_attr( $this->get_field_name( 'posttaxonomies' ) ); ?>[]" id="<?php echo esc_attr( $this->get_field_id( 'posttaxonomies' ) ); ?>" style="width:100%" multiple>
			<?php 
			if($cposttype != '0')
			{ 
				$post_type_taxonomies = get_object_taxonomies($cposttype);
				if(count($post_type_taxonomies)>0)
				{
					$sel_tax = explode("-:-",$cposttaxonomies);
					foreach ($post_type_taxonomies as $post_type_taxonomy ) {
						$terms = get_terms( array('taxonomy' => $post_type_taxonomy,'hide_empty' => false) );
						foreach($terms as $term)
					   	{
					   		echo '<option value="' . esc_attr($post_type_taxonomy).':'.esc_attr($term->name). '" '.((in_array(esc_attr($post_type_taxonomy).':'.esc_attr($term->name) , $sel_tax))?'selected':'').'>' . esc_html__($post_type_taxonomy).': '.esc_html__($term->name) . '</option>';
					   	}
					}
				}
				else
				{
					echo '<option value="0">No taxonomies available.</option>';
				}

			?>
			<?php } else {
				echo '<option value="0">Select post type to see taxonomies.</option>';
			}?>
			</select>
		
		</p>

		<?php  $noofposts = ! empty( $instance['noofposts'] ) ? $instance['noofposts'] : esc_html__( '5', 'adv-rpw' );?>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'noofposts' ) ); ?>">Number of posts to show:</label>
		<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'noofposts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'noofposts' ) ); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($noofposts);?>" size="3"></p>
		<?php
	}
}



