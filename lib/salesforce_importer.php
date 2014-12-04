<?php

/*
add_action('admin_menu', 'sfwtli_menu');

function sfwtli_menu() {
	add_management_page('Salesforce Web to Lead Import', 'Salesforce Import', 'manage_options', __FILE__, 'sfwtli_importer');
}

function sfwtli_importer_page(){

	echo '<div class="wrap">';
	echo '<h2>Salesforce Web to Lead Form Importer</h2>';

	sfwtli_importer_ui();

	echo '</div>';

}
*/

function sfwtli_importer_ui(){

		if( isset( $_POST['sfw2l_form_data'] ) && $_POST['sfw2l_form_data'] ){

			$delim = '';

			if( isset( $_POST['delim'] ) )
				$delim =  stripslashes( $_POST['delim'] );

			if( !$delim	)
				$delim = '<br>';

				//echo '<code>'.esc_attr($delim).'</code>';

			$form = $_POST['sfw2l_form_data'];

			include_once('simple_html_dom.php');

			//find last comment " -->" and chop of junk at beginning
			//$chop = strrpos($form, '-->') + 3;

			//clean up SF form
			//$fields = stripslashes( substr($form, $chop) );
			$fields = stripslashes( $form );
			$fields = trim( str_replace( array('<input type="submit" name="submit">','</form>'), '', $fields ) );
			$fields = str_replace( array("\r","\n"),'',$fields);

			$html = str_get_html( $fields );

			// remove comments
			$comments = $html->find('comment');
			foreach( $comments as $comment ){
				$field = $comment;
				$fields = str_replace( $field, '', $fields );
			}

			$html = str_get_html( $fields );

			// grab then remove hidden fields
			$hidden_fields = $html->find('input[type=hidden]');
			foreach( $hidden_fields as $hidden_field ){
				$field = $hidden_field;
				$fields = str_replace( $field, '', $fields );
			}

			//isolate fields
			$fields = explode( $delim, $fields );

			//echo '<pre>'.esc_attr( print_r( $fields, 1 ) ).'</pre>';

			// process fields
			foreach( $fields as $field ){

				$label = '';
				$options = array();

				//skip empties
				if( !$field ) continue;

				// parse field html
				$html = str_get_html( $field );

				// label
				foreach($html->find('label') as $element){
					$label = $element->innertext; // built in field
				}

				if( !$label ){
					$label = trim( strip_tags( $html->plaintext ) );
				}

				// custom fields are not wrapped in labels for some reason
				if( !$label )
					$label = sfwtli_get_label( $field ); // custom field

				// trim trailing : from label
				if( substr( $label, -1 ) == ':' )
					$label = substr( $label, 0, -1 );

				// id aka name
				$id = '';
				foreach($html->find('*[id]') as $element){
					$id = $element->id;
				}

				// value
				$value = '';
				foreach($html->find('*[value]') as $element){
					$value = $element->value;
				}

				// Options
				foreach($html->find('*[value]') as $element){
					$options[] = array( 'name' => $element->innertext, 'value' => $element->value );
				}

				// Determine field type
				if( strpos( $field, '<select' ) !== false ){
					if( strpos( $field, 'multiple="multiple"' ) !== false ){
						$type = 'multi-select';
					}else{
						$type = 'select';
					}
					$value = '';
				}elseif(  strpos( $field, 'type="checkbox"' ) !== false  ){
					$type = 'checkbox';
				}elseif(  strpos( $field, '<textarea' ) !== false  ){
					$type = 'textarea';
				}elseif(  strpos( $field, 'type="hidden"' ) !== false  ){
					$type = ''; // skip and add to end later
				}elseif(  strpos( $field, 'type="submit"' ) !== false  ){
					$type = '';
				}else{
					$type = 'text';
				}

				//debug
/*
				echo esc_attr($field).'<br>';
				echo '<b>type= '.$type.'</b><br>';
				echo '<b>label= '.$label.'</b><br>';
				echo '<b>id = '.$id.'</b><br>';
				echo '<pre>options='.print_r($options,1).'</pre><br>';
				echo '<hr>';
*/

				if( ( $type == 'multi-select' || $type == 'select' ) && $options ){
					$options = sfwtli_format_options( $options );
				}else{
					$options = '';
				}

				if( $type )
					$field_data[$id] = array('type' => $type, 'value' => $value, 'label' => $label, 'show' => true, 'required' => false, 'opts' => $options );

			}

			// add hidden fields to end, and sort out special fields

			foreach( $hidden_fields as $hidden_field ){

				$html = str_get_html( $hidden_field );

				// id aka name
				$id = '';
				foreach($html->find('*[name]') as $element){
					$id = $element->name;
				}

				// value
				$value = '';
				foreach($html->find('*[value]') as $element){
					$value = $element->value;
				}

				$field_data[$id] = array('type' => 'hidden', 'value' => $value, 'label' => '', 'show' => true, 'required' => false, 'opts' => '' );

			}

			//echo '<pre>'.print_r( $field_data, 1 ).'</pre>';
			$form_id = sfwtli_save_form( $field_data );

			echo '<p>Imported to form #'.$form_id.' </p><p><a class="button-primary" href="'.add_query_arg('id', $form_id, admin_url( 'options-general.php?page=salesforce-wordpress-to-lead&tab=form' ) ).'">Edit</a></p>';

			// find all link
			//foreach($html->find('input') as $e)
			    //echo $e->name . '<br>';

	}else{

		echo '<p>Generate your web to lead form at Salesforce, paste the HTML code below, then automatically import it into WordPress to Lead in a single click!</p>';
		echo '<p>In SalesForce: <b>Setup &gt; Customize &gt; Leads &gt; Web-to-Lead &gt; Create Web-to-Lead Form</b></p>';

		if( isset( $_POST['sfw2l_form_data'] ) && ! $_POST['sfw2l_form_data'] )
			echo "<div id='message' class='error'><p>".__('Please enter a form  to import!','salesforce')."</p></div>";

		echo '<form method="post" action="">';
			echo '<p><textarea name="sfw2l_form_data" cols="64" rows="24"></textarea></p>';

			echo '<p>Field Delimiter: <input type="text" name="delim" class="" value=""><br>
					<i><small>' . __( "Leave blank for SalesForce generated form import.<br> If you're importing a form that's been modified, choose something that appears between every field (add it if needed). e.g. <code>&lt;/div&gt;</code>", 'salesforce' ) . '</small></i></p>';

			echo '<p><input type="submit" class="button-primary" value="Import To New Form"></p>';
		echo '</form>';

	}

}

function sfwtli_get_label( $field ){

	$lt = strpos( $field, '<');
	return trim( substr($field, 0, $lt ) );

}

function sfwtli_format_options( $options ){

	$formatted = array();

	foreach( $options as $option )
		$formatted[] =  $option['name'].'|'.$option['value'];

	return implode("\n", $formatted);

}

function sfwtli_save_form( $field_data ){

	// data format
	// array( 'first_name' 	=> array('type' => 'text', 'label' => 'First name', 'show' => true, 'required' => true), )

	$Salesforce_Admin = new Salesforce_Admin();

	// get form option data
	$options  = get_option( $Salesforce_Admin->optionname );

	// get last id and increment
	end( $options['forms'] );
	$form_id = key( $options['forms'] ) + 1;

	// get default form data
	$options['forms'][$form_id] = $Salesforce_Admin->default_form();

	$options['forms'][$form_id]['form_name'] = 'My Imported Lead Form '.date('Y-m-d h:i:s');

	// overwrite defaults with import data
	$options['forms'][$form_id]['inputs'] = $field_data;

	update_option( $Salesforce_Admin->optionname, $options );

	return $form_id;

}