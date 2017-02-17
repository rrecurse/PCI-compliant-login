<?php
/**
 * Plugin Name: PCI Compliant Login Page
 * Description: Disable login password autocomplete and change username field type to password
 * Version: 1.0
 * Author: cdebellis
 * License: WTFPL
 */

// # change a couple of login labels and defaults
// # conditional mobile "Remember Me" behavior (show only if mobile)
function pci_login_defaults( $defaults ){
	$defaults['label_username'] = __('Access Number');
	$defaults['label_remember'] = __( 'Stay Logged In' );
	// # added condition to show "Remember Me" checkbox only if client is mobile.
	$defaults['remember'] = (wp_is_mobile() ? __(true) : __(false));
	$defaults['value_remember'] = (wp_is_mobile() ? __(true) : __(false));
	return $defaults;
}
add_filter( 'login_form_defaults', 'pci_login_defaults' );


// # Output Buffering the entire WP process, capturing the final output for manipulation.
ob_start();

add_action('shutdown', function() {

	global $template;
	$the_login_page = 'login.php';
	
	// # detect if current page is login - do not run function if so.
	if($the_login_page == basename($template)) {

    	$final = '';
    	// # We'll need to get the number of ob levels we're in, so that we can iterate over each, collecting
    	// # that buffer's output into the final output.
    	$levels = ob_get_level();

    	for ($i = 0; $i < $levels; $i++) {
        	$final .= ob_get_clean();
    	}

    	// # Apply any filters to the final output
    	echo apply_filters('final_output', $final);
	}
}, 0);


// # function to auto-tab to password feild after username feild reaches maxlength
function jumpToPasswd(){
	if(!is_user_logged_in() && is_page_template('page-templates/login.php')){
       	echo '<script>
			jQuery("input").bind("input", function() {
				var $this = jQuery(this);
				setTimeout(function() {
					if($this.val().length >= parseInt($this.attr("maxlength"),10)) {
						jQuery("#pass").focus().select();
					}
				}, 0);
			});
		</script>';
	}
}
add_action( 'wp_footer', 'jumpToPasswd' );

add_filter('final_output', function($output) {

	// # filter out HTML comment tags.
	$output = preg_replace('/<!--(.|s)*?-->/', '', $output);

	// # find and replace user input element and add attributes.
	$output = str_replace(array('id="user"','type="text"'), array('id="user" autocomplete="off" maxlength="9" autofocus','type="password"'), $output);

	return $output;
});

?>