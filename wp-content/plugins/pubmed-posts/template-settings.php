<?php
/**
 * Settings template for "Pubmed Posts" Wordpress plugin
 */
 
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit; 
}
 
?>
<div id='pubmed-settings' class='wrap'>
	<div class='icon32' id='pubmed-icon'><br></div>
	<h2><?php _e('PubMed Posts Settings', 'pubmed-posts'); ?></h2>
	<form method='post' action='options.php'>
		<?php settings_fields('pmp_settings_group'); ?>
		<?php do_settings_sections('pmp_settings_page'); ?>
		<p class='submit'>
			<input id='submit' type='submit' name='submit' class='button-primary' value='<?php esc_attr_e("Save Changes"); ?>' />
		</p>
	</form>
</div>