<?php
//
//	A simple PHP CAPTCHA script
//
//		Copyright 2011 by Cory LaViska for A Beautiful Site, LLC.
//
//		Website: http://abeautifulsite.net/blog/2011/01/a-simple-php-captcha-script/
//
//		Dual-licensed under the MIT License and the GNU GPL. Please choose the 
//		license that best suits your needs. This software is free of charge and 
//		may be used for both personal and commercial applications.
//
//		MIT License: http://en.wikipedia.org/wiki/MIT_License
//
//		GNU GPL: http://en.wikipedia.org/wiki/GNU_General_Public_License
//
//
//	Dependencies:
//
//		- Requires PHP GD library
//		- Background images must be in PNG format
//		- Fonts can be either TTF or OTF format
//		- Uses the $_SESSION['_CAPTCHA'] namespace
//
//
//	Usage:
//
//		$_SESSION['captcha'] = captcha( array(
//			'code' => '',
//			'min_length' => 5,
//			'max_length' => 5,
//			'png_backgrounds' => array('default.png', ...),
//			'fonts' => array('times_new_yorker.ttf', ...),
//			'characters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
//			'min_font_size' => 24,
//			'max_font_size' => 30,
//			'color' => '#000',
//			'angle_min' => 0,
//			'angle_max' => 15,
//			'shadow' => true,
//			'shadow_color' => '#CCC',
//			'shadow_offset_x' => -2,
//			'shadow_offset_y' => 2
//		));
//
//		Note: Everything is optional; the function may also be called without any arguments.
//	
//
//	The above usage will result in these values:
//
//		$_SESSION['captcha']['code'] = [CAPTCHA code]
//		$_SESSION['captcha']['image_src'] = [image src attribute]
//
//	To display the CAPTCHA image:
//
//		<img src="$_SESSION['captcha']['image_src']" alt="CAPTCHA security code" />
//
//
if( !function_exists('captcha_config') ){
	function captcha_config(){
	
		// Default values
		$captcha_config = array(
			'code' => '',
			'min_length' => 4,
			'max_length' => 4,
			'png_backgrounds' => array(dirname(__FILE__) . '/default.png'),
			'fonts' => array(dirname(__FILE__) . '/times_new_yorker.ttf'),
			'characters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
			'min_font_size' => 24,
			'max_font_size' => 30,
			'color' => '#000',
			'angle_min' => 0,
			'angle_max' => 10,
			'shadow' => true,
			'shadow_color' => '#CCC',
			'shadow_offset_x' => -2,
			'shadow_offset_y' => 2
		);
		
		return $captcha_config;
	
	}
}

if( !function_exists('captcha') ){

	function captcha($config = array()) {
		
		// Check for GD library
		if( !function_exists('gd_info') ) {
			throw new Exception('Required GD library is missing');
		}
		
		$captcha_config = captcha_config();
		
	/*
		// Overwrite defaults with custom config values
		if( is_array($config) ) {
			foreach( $config as $key => $value ) $captcha_config[$key] = $value;
		}
		
		// Restrict certain values
		if( $captcha_config['min_length'] < 1 ) $captcha_config['min_length'] = 1;
		if( $captcha_config['angle_min'] < 0 ) $captcha_config['angle_min'] = 0;
		if( $captcha_config['angle_max'] > 10 ) $captcha_config['angle_max'] = 10;
		if( $captcha_config['angle_max'] < $captcha_config['angle_min'] ) $captcha_config['angle_max'] = $captcha_config['angle_min'];
		if( $captcha_config['min_font_size'] < 10 ) $captcha_config['min_font_size'] = 10;
		if( $captcha_config['max_font_size'] < $captcha_config['min_font_size'] ) $captcha_config['max_font_size'] = $captcha_config['min_font_size'];
	*/
		
		// Use milliseconds instead of seconds
		srand(microtime() * 100);
		
		// Generate CAPTCHA code if not set by user
		if( empty($captcha_config['code']) ) {
			$captcha_config['code'] = '';
			$length = rand($captcha_config['min_length'], $captcha_config['max_length']);
			while( strlen($captcha_config['code']) < $length ) {
				$captcha_config['code'] .= substr($captcha_config['characters'], rand() % (strlen($captcha_config['characters'])), 1);
			}
		}
		
		// Generate image src
		$image_src = substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])) . '?_CAPTCHA&amp;t=' . urlencode(microtime());
		$image_src = '/' . ltrim(preg_replace('/\\\\/', '/', $image_src), '/');
		
		//$_SESSION['_CAPTCHA']['config'] = serialize($captcha_config);
		
		return array(
			'code' => $captcha_config['code'],
			'image_src' => $image_src
		);
		
	}
	
	
	if( !function_exists('hex2rgb') ) {
		function hex2rgb($hex_str, $return_string = false, $separator = ',') {
			$hex_str = preg_replace("/[^0-9A-Fa-f]/", '', $hex_str); // Gets a proper hex string
			$rgb_array = array();
			if( strlen($hex_str) == 6 ) {
				$color_val = hexdec($hex_str);
				$rgb_array['r'] = 0xFF & ($color_val >> 0x10);
				$rgb_array['g'] = 0xFF & ($color_val >> 0x8);
				$rgb_array['b'] = 0xFF & $color_val;
			} elseif( strlen($hex_str) == 3 ) {
				$rgb_array['r'] = hexdec(str_repeat(substr($hex_str, 0, 1), 2));
				$rgb_array['g'] = hexdec(str_repeat(substr($hex_str, 1, 1), 2));
				$rgb_array['b'] = hexdec(str_repeat(substr($hex_str, 2, 1), 2));
			} else {
				return false;
			}
			return $return_string ? implode($separator, $rgb_array) : $rgb_array;
		}
	}
	
	
	// Draw the image
	if( isset($_GET['_CAPTCHA']) ) {
		
		//session_start();
		include($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
		
		$captcha_config = captcha_config();
		
		//$captcha_config = unserialize($_SESSION['_CAPTCHA']['config']);
		//unset($_SESSION['_CAPTCHA']);
		
		// Use milliseconds instead of seconds
		srand(microtime() * 100);
		
		// Pick random background, get info, and start captcha
		$background = $captcha_config['png_backgrounds'][rand(0, count($captcha_config['png_backgrounds']) -1)];
		
		list($bg_width, $bg_height, $bg_type, $bg_attr) = getimagesize($background);
	
		// Get code from Transient and Discard
		$hash = ereg_replace('[^A-Za-z0-9]', '', $_GET['hash']);
		$code = get_transient( $hash );
		//delete_transient( $hash );
		
		$captcha = imagecreatefrompng($background);
		
		$color = hex2rgb($captcha_config['color']);
		$color = imagecolorallocate($captcha, $color['r'], $color['g'], $color['b']);
		
		// Determine text angle
		$angle = rand( $captcha_config['angle_min'], $captcha_config['angle_max'] ) * (rand(0, 1) == 1 ? -1 : 1);
		
		// Select font randomly
		$font = $captcha_config['fonts'][rand(0, count($captcha_config['fonts']) - 1)];
		
		// Verify font file exists
		if( !file_exists($font) ) throw new Exception('Font file not found: ' . $font);
		
		//Set the font size.
		$font_size = rand($captcha_config['min_font_size'], $captcha_config['max_font_size']);
		$text_box_size = imagettfbbox($font_size, $angle, $font, $code);
		
		// Determine text position
		$box_width = abs($text_box_size[6] - $text_box_size[2]);
		$box_height = abs($text_box_size[5] - $text_box_size[1]);
		$text_pos_x_min = 0;
		$text_pos_x_max = ($bg_width) - ($box_width);
		$text_pos_x = rand($text_pos_x_min, $text_pos_x_max);			
		$text_pos_y_min = $box_height;
		$text_pos_y_max = ($bg_height) - ($box_height / 2);
		$text_pos_y = rand($text_pos_y_min, $text_pos_y_max);
		
		// Draw shadow
		if( $captcha_config['shadow'] ){
			$shadow_color = hex2rgb($captcha_config['shadow_color']);
		 	$shadow_color = imagecolorallocate($captcha, $shadow_color['r'], $shadow_color['g'], $shadow_color['b']);
			imagettftext($captcha, $font_size, $angle, $text_pos_x + $captcha_config['shadow_offset_x'], $text_pos_y + $captcha_config['shadow_offset_y'], $shadow_color, $font, $captcha_config['code']);	
		}
		
		
		//echo $code;
		
		// Draw text
		imagettftext($captcha, $font_size, $angle, $text_pos_x, $text_pos_y, $color, $font, $code);	
		
		// Output image
		header("Content-type: image/png");
		imagepng($captcha);
		
	}
}