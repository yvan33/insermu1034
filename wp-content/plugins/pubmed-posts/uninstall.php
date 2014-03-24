<?php
/**
 * Uninstall "PubMed Posts" Wordpress plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit; 
}

if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

delete_option('pmp_settings');

// End