<?php
/**
 * 
 * Register WP Testimonial Rotator 
 */

//check widget active or not

add_action('wp_head','wpt_load_inline_js');

class WptTestimonialsTidget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'wp_testimonial_widget', // Base ID
			__( 'WP Testimonial Widget', 'mrwebsolution' ), // Name
			array( 'description' => __( 'A Testimonial Widget', 'mrwebsolution' ), ) // Args
		);
		add_action( 'widgets_init', array(&$this, 'wpt_testiomonials_init' ) );
	  }
	
        function wpt_testimonials_widget() {
            $widget_ops = array('description' => __('Display auto rutate testimonials in your sidebar', 'WP Testimonial Rotator'));
            $this->WP_Widget('wpt_testimonials', __('WP Testimonial'), $widget_ops);
        }
       
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
        function widget($args, $instance) {
            extract($args);
            $title = esc_attr($instance['title']);

            echo $before_widget.$before_title.$title.$after_title;
            
                get_wpt_testimonials_content($instance);

            echo $after_widget;
        }

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
        function update($new_instance, $old_instance) {
            if (!isset($new_instance['submit'])) {
                return false;
            }
            $instance = $old_instance;
            $instance['title'] = strip_tags($new_instance['title']);
            
            $instance['wt_view_all'] 		= ( ! empty( $new_instance['wt_view_all'] ) ) ? strip_tags( $new_instance['wt_view_all'] ) : '';
            $instance['wt_view_all_link'] 		= ( ! empty( $new_instance['wt_view_all_link'] ) ) ? strip_tags( $new_instance['wt_view_all_link'] ) : '';
            
            return $instance;
        }

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
        function form($instance) {
            global $wpdb;
            $instance = wp_parse_args((array) $instance, array('title' => __('WP Testimonials', 'wpt_testimonials')));
            $title = esc_attr($instance['title']);
            $wt_view_all 			= ! empty( $instance['wt_view_all'] ) ? $instance['wt_view_all'] : __( '', 'mrwebsolution' );
            $wt_view_all_link 			= ! empty( $instance['wt_view_all_link'] ) ? $instance['wt_view_all_link'] : __( '', 'mrwebsolution' );
    ?>

    <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'Simple Testimonial Widget'); ?>
    <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" class="widefat" value="<?php echo $title; ?>" /></label></p>
    
    <p>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'wt_view_all' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'wt_view_all' ) ); ?>" type="checkbox" value="1" <?php checked( $wt_view_all, 1 ); ?>>
		<label for="<?php echo esc_attr( $this->get_field_id( 'wt_view_all' ) ); ?>"><?php _e( esc_attr( 'Show View All Button' ) ); ?> </label> 
		</p>
        <p>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'wt_view_all_link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'wt_view_all_link' ) ); ?>" type="text" value="<?php echo $wt_view_all_link; ?>" placeholder="define view all page link here">
		</p>
    

    <input type="hidden" id="<?php echo $this->get_field_id('submit'); ?>" name="<?php echo $this->get_field_name('submit'); ?>" value="1" />
    <?php
        }
        
     ### Function: Init Simple Testiomonial Rotator Widget
    static  function wpt_testiomonials_init() {
        register_widget('WptTestimonialsTidget');
    }
   }
 // init WptTestimonialsTidget
 if(class_exists('WptTestimonialsTidget')):
 $WptTestimonialsTidget = new WptTestimonialsTidget;
 endif;  
/*
 * Load jQuery code in header   
 */

function wpt_load_inline_js()
{
	$getOptions =get_wpt_testimonials_options();
	$delayTime= isset($getOptions['wpt_speed']) ? $getOptions['wpt_speed'] : '5000';
	$jscnt ="<script>
		jQuery(function($) {
			jQuery('#wpt_testimonial > div:gt(0)').hide();
			setInterval(function() { 
			  jQuery('#wpt_testimonial > div:first')
			    .fadeOut(1000)
			    .next()
			    .fadeIn(1000)
			    .end()
			    .appendTo('#wpt_testimonial')
			},  ".$delayTime.")
		})
	</script>";
	
	echo $jscnt;
	}	   

function get_wpt_testimonials_content($instance) {
/** Get Testimonial Content*/

$getOptions =get_wpt_testimonials_options();
$wpt_sortBy = isset($getOptions['wpt_sortby']) ? $getOptions['wpt_sortby']: 'title';
$wpt_orderby = isset($getOptions['wpt_orderby']) ? $getOptions['wpt_orderby']: 'ASC';

$wpt_query = new WP_Query('post_type=wpt_testimonial&post_status=publish&orderby='.$wpt_sortBy.'&order='.$wpt_orderby);

$effect= isset($getOptions['wpt_effect']) && $getOptions['wpt_effect']!='' ? $getOptions['wpt_effect'] : 'fade';
$delayTime= isset($getOptions['wpt_speed']) && $getOptions['wpt_speed']!=''? $getOptions['wpt_speed'] : 5000;
$content_limit= isset($getOptions['wpt_content_limit']) && $getOptions['wpt_content_limit']!='' ? $getOptions['wpt_content_limit'] : 400;
 // Restore global post data stomped by the_post().
$script="<script type='text/javascript'>
jQuery(document).ready(function($) {
    jQuery('#wptTestimonialsWidget').cycle({
        fx: '".$effect."', // choose your transition type, ex: fade, scrollUp, scrollRight, shuffle
        speed:".$delayTime.", 
		delay:0,
		/*fit:true,*/
		
     });
});
</script>"; 
 
$wptContent='<div id="wptWidget" class="wptWidget">'; 
$wptContent.=$script;
$wptContent.='<div id="wptTestimonialsWidget" class="wptTestimonial">';
if( $wpt_query->have_posts() ) {
  while ($wpt_query->have_posts()) : $wpt_query->the_post();

  if(get_post_meta(get_the_ID(), '_wpt_testimonial_url', true)==''): 
			 //get author title
			 $authorName=get_the_title();
			 else:
			$authorName='<a href="'.get_post_meta(get_the_ID(), '_wpt_testimonial_url', true).'" target="_blank">'.get_the_title().'</a>';
			 endif;
		
 if(get_post_meta(get_the_ID(), '_wpt_testimonial_designation', true)!=''): 
 $authorDesignation='<span class="designation">'.get_post_meta(get_the_ID(), '_wpt_testimonial_designation', true).'</span>';
 else:
 $authorDesignation='';
 endif; 
 	 
  $wptContent.='<blockquote>';
    
  $wptContent.='<p><span class="laquo">&nbsp;</span>'.((strlen(strip_tags(get_the_content())) > $content_limit) ? substr(strip_tags(get_the_content()),0,(int)$content_limit).'...': strip_tags(get_the_content())).'<span class="raquo">&nbsp;</span></p>';

  $wptContent.='<cite>'.$authorName.$authorDesignation.'</cite>';
			  
  $wptContent.='</blockquote>';
  
  endwhile;
} 
wp_reset_query();
$wptContent.='</div>';

$wt_view_all  	= ! empty($instance['wt_view_all']) ? $instance['wt_view_all'] : '';
$wt_view_all_link  	= ! empty($instance['wt_view_all_link']) ? $instance['wt_view_all_link'] : '';
if($wt_view_all && $wt_view_all_link!=''): 
$wptContent.='<div class="view-all"><a href="'.$wt_view_all_link.'">View All</a></div>';
endif; 
$wptContent.='</div>';
echo $wptContent;
}
?>
