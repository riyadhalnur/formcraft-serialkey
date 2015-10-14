<?php

	/*
	Plugin Name: FormCraft SerialKey Add-On
	Plugin URI: http://formcraft-wp.com/addons/serialkey/
	Description: Serial key validatior Add-on for FormCraft
	Author: Riyadh Al Nur
	Author URI: http://verticalaxisbd.com/
	Version: 1.0.0
	Text Domain: formcraft-serialkey
	*/

	// Tell FormCraft our add-on exists
	add_action('formcraft_addon_init', 'serialkey_addon');
	function serialkey_addon()
	{
		register_formcraft_addon( 'serialkey_addon_settings', 0, 'SerialKey Addon', 'SerialKeyController');
	}

	// We load our JavaScript file on the form editor page
	add_action('formcraft_addon_scripts', 'serialkey_addon_scripts');
	function serialkey_addon_scripts() {
		wp_enqueue_script('fc-serialkey-addon-js', plugins_url( 'serialkey_form_builder.js', __FILE__ ));
	}

	// We show a simple text field in the add-on's settings
	function serialkey_addon_settings() {
		echo "<input style='margin: 20px 10%; width: 80%' placeholder='Serial key validator URL' type='text' ng-model='Addons.SerialKeyAddon.server'>";
	}

	// We hook into form submissions to check the submitted form data, and throw an error if
	add_action('formcraft_before_save', 'serialkey_addon_hook', 10, 4);
	function serialkey_addon_hook($filtered_content, $form_meta, $raw_content, $integrations)
	{
		global $fc_final_response;
		$serialkey_addon_settings = formcraft_get_addon_data('SerialKeyAddon', $filtered_content['Form ID']);

		if (!empty($serialkey_addon_settings['server'])) {
			$remote_server = $serialkey_addon_settings['server'];
		}

		foreach ($raw_content as $key => $value) {
			if ($value['label'] == 'Serial') {
				$encodedKey = rawurlencode($value['value']);
				$response = wp_remote_get($remote_server"?key=".$encodedKey);

				if (is_wp_error($response)) {
					$fc_final_response['failed'] = $response->get_error_message();
				}

				$response = json_decode($response['body'], 1);

				if ($response==NULL || empty($response)) {
					$fc_final_response['errors'][$value['identifier']] = "There was an error. Please try again.";
				} else if (isset($response['failed'])) {
					$fc_final_response['failed'] = "Serial key is invalid";
				}
			}
		}
	}
	?>
