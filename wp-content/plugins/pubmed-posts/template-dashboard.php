<?php
/**
 * Dashboard widget template for "PubMed Posts" Wordpress plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit; 
}

?>
<?php wp_nonce_field('pubmed-submit', 'pubmed-nonce'); ?>
<div class='pubmed-message'></div>
<div class='pubmed-row'>
	<input id='pubmed-pmid' class='pubmed-text' type='text' name='pubmed-pmid' value='<?php _e('PMIDs (separate with commas)', 'pubmed-posts'); ?>' />
</div>
<div class='pubmed-row'>
	<textarea id='pubmed-tags' class='pubmed-text' name='pubmed-tags' rows='1'></textarea>
</div>
<div class='pubmed-row'>
	<select id='pubmed-categories' class='pubmed-text' name='pubmed-categories'>
		<?php echo $this->categories_options(); ?>
	</select>
</div>	
<div class='pubmed-buttons'>
	<input class='button pubmed-draft' type='button' class='button' value='<?php _e('Save Draft', 'pubmed-posts'); ?>' />			
	<input class='button pubmed-reset' type='button' class='button' value='<?php _e('Reset', 'pubmed-posts'); ?>' />
	<input class='button-primary pubmed-publish' type='button' class='button-primary' value='<?php _e('Publish', 'pubmed-posts'); ?>' />
	<img class='pubmed-waiting' alt='' src='<?php echo admin_url("/images/wpspin_light.gif"); ?>' />
</div>
<?php // END