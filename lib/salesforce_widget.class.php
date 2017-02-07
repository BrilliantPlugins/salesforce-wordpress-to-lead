<?php
class Salesforce_WordPress_to_Lead_Widgets extends WP_Widget {

	function Salesforce_WordPress_to_Lead_Widgets() {
		$widget_ops = array( 'classname' => 'salesforce', 'description' => __('Displays a WordPress-to-Lead for Salesforce Form','salesforce') );
		$control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'salesforce' );
		parent::__construct( 'salesforce', 'Salesforce', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		echo $before_widget;
		$title = apply_filters('widget_title', $instance['title'] );
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		if ( !empty($instance['desc']) && empty($_POST) ) {
			echo '<p>' . $instance['desc'] . '</p>';
		}
		$is_sidebar = true;
		echo do_shortcode('[salesforce form="'.$instance['form'].'" sidebar="true"]');
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		foreach ( array('title', 'desc', 'form') as $val ) {
			$instance[$val] = strip_tags( $new_instance[$val] );
		}
		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title' => 'Contact Us',
			'desc' 	=> 'Contact us using the form below',
			'form' 	=> 1,
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e("Title"); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:90%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'desc' ); ?>"><?php _e("Introduction"); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'desc' ); ?>" name="<?php echo $this->get_field_name( 'desc' ); ?>" value="<?php echo $instance['desc']; ?>" style="width:90%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'form' ); ?>"><?php _e("Form"); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'form' ); ?>" name="<?php echo $this->get_field_name( 'form' ); ?>">
				<?php
				$sfoptions = get_option('salesforce2');

				foreach($sfoptions['forms'] as $key=>$value){

					echo '<option value="'.$key.'"';
					if( $instance['form'] == $key)
						echo ' selected="selected"';
					echo '>'.$value['form_name'].'</option>';


				}
				?>
			</select>
		</p>


	<?php
	}
}