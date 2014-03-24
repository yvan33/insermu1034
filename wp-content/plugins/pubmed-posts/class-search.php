<?php
/**
 * Search widget class for "PubMed Posts" Wordpress plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit; 
}

class PubMedPostsSearch extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'pubmed-widget', 'description' => __( "A search form for PubMed posts") );
		parent::__construct('pmp_search', __('PubMed Posts Search'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$show_reset = empty($instance['reset']) ? true : false;
		
		// Set display classes
		if (empty($_POST['pubmed-search'])) {
			// Widget settings
			if (empty($instance['advanced'])) {
				$search_type = 'simple';
				$show_advanced = '';
				$hide_advanced = ' pubmed-hide';
			} else {
				$search_type = 'advanced';
				$show_advanced = ' pubmed-hide';
				$hide_advanced = '';
			}
		} else {
			// User settings
			if ('simple' == $_POST['pubmed-search']) {
				$search_type = 'simple';
				$show_advanced = '';
				$hide_advanced = ' pubmed-hide';
			} else {
				$search_type = 'advanced';
				$show_advanced = ' pubmed-hide';
				$hide_advanced = '';
			}			
		}

		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		
		// User values
		$pubmed_keyword = empty($_POST['pubmed-keyword']) ? '' : sanitize_text_field($_POST['pubmed-keyword']);
		$pubmed_pmid = empty($_POST['pubmed-pmid']) ? '' : sanitize_text_field($_POST['pubmed-pmid']);
		$article_abstract = empty($_POST['article-abstract']) ? '' : sanitize_text_field($_POST['article-abstract']);
		$article_affiliation = empty($_POST['article-affiliation']) ? '' : sanitize_text_field($_POST['article-affiliation']);
		$article_authors = empty($_POST['article-authors']) ? '' : sanitize_text_field($_POST['article-authors']);
		$article_title = empty($_POST['article-title']) ? '' : sanitize_text_field($_POST['article-title']);
		$journal_title = empty($_POST['journal-title']) ? '' : sanitize_text_field($_POST['journal-title']);
		$journal_volume = empty($_POST['journal-volume']) ? '' : sanitize_text_field($_POST['journal-volume']);
		$journal_year = empty($_POST['journal-year']) ? '' : sanitize_text_field($_POST['journal-year']);
		$post_tags_or = empty($_POST['post-tags-or']) ? '' : sanitize_text_field($_POST['post-tags-or']);
		$post_tags_or = stripslashes($post_tags_or);
		$post_tags_and = empty($_POST['post-tags-and']) ? '' : sanitize_text_field($_POST['post-tags-and']);
		$post_tags_and = stripslashes($post_tags_and);		
		$post_tags_not = empty($_POST['post-tags-not']) ? '' : sanitize_text_field($_POST['post-tags-not']);
		$post_tags_not = stripslashes($post_tags_not);
		
?>
		<form class='pubmed-form search-form' action='<?php echo esc_url( home_url( '/' ) ); ?>' method='post'>
			<input class='pubmed-search' type='hidden' name='pubmed-search' value='<?php echo $search_type; ?>' />
			<div class='pubmed-fields'>
				<div class='pubmed-keyword<?php echo $show_advanced; ?>'>
					<input type='text' name='pubmed-keyword' value='<?php echo $pubmed_keyword; ?>' />
				</div>			
				<div class='pubmed-advanced<?php echo $hide_advanced; ?>'>
					<div>
						<label><?php _e('Article Abstract', 'pubmed-posts'); ?>:</label>
						<input type='text' name='article-abstract' value='<?php echo $article_abstract; ?>' />
					</div>					
					<div>
						<label><?php _e('Article Affiliation', 'pubmed-posts'); ?>:</label>
						<input type='text' name='article-affiliation' value='<?php echo $article_affiliation; ?>' />
					</div>					
					<div>
						<label><?php _e('Article Authors', 'pubmed-posts'); ?>:</label>
						<input type='text' name='article-authors' value='<?php echo $article_authors; ?>' />
					</div>	
					<div>
						<label><?php _e('Article Title', 'pubmed-posts'); ?>:</label>
						<input type='text' name='article-title' value='<?php echo $article_title; ?>' />
					</div>	
					<div>
						<label><?php _e('Journal Title', 'pubmed-posts'); ?>:</label>
						<input type='text' name='journal-title' value='<?php echo $journal_title; ?>' />
					</div>	
					<div>
						<label><?php _e('Journal Volume', 'pubmed-posts'); ?>:</label>
						<input type='text' name='journal-volume' value='<?php echo $journal_volume; ?>' />
					</div>					
					<div>
						<label><?php _e('Journal Year', 'pubmed-posts'); ?>:</label>
						<input type='text' name='journal-year' value='<?php echo $journal_year; ?>' />
					</div>	
					<div>
						<label><?php _e('PMID', 'pubmed-posts'); ?>:</label>
						<input type='text' name='pubmed-pmid' value='<?php echo $pubmed_pmid; ?>' />
					</div>		
					<div>
						<div>
							<label><?php _e('Tags', 'pubmed-posts'); ?>:</label>
							<label class='pubmed-boolean pubmed-question'><?php _e('OR', 'pubmed-posts'); ?></label>
							<textarea class='post-tags' name='post-tags-or' rows='1'><?php echo $post_tags_or; ?></textarea>
						</div>						
						<div>
							<label><?php _e('Tags', 'pubmed-posts'); ?>:</label>
							<label class='pubmed-boolean pubmed-tick'><?php _e('AND', 'pubmed-posts'); ?></label>
							<textarea class='post-tags' name='post-tags-and' rows='1'><?php echo $post_tags_and; ?></textarea>
						</div>					
						<div>
							<label><?php _e('Tags', 'pubmed-posts'); ?>:</label>
							<label class='pubmed-boolean pubmed-cross'><?php _e('NOT', 'pubmed-posts'); ?></label>
							<textarea class='post-tags' name='post-tags-not' rows='1'><?php echo $post_tags_not; ?></textarea>
						</div>
					</div>
				</div>	
			</div>		
			<div class='pubmed-controls'>
				<span class='pubmed-show-advanced<?php echo $show_advanced; ?>'><?php _e('Advanced', 'pubmed-posts'); ?></span>
				<span class='pubmed-hide-advanced<?php echo $hide_advanced; ?>'><?php _e('Hide', 'pubmed-posts'); ?></span>
				<div class='pubmed-buttons'>
					<?php if ($show_reset) : ?>
					<input class='pubmed-reset search-submit' type='button' value='<?php _e('Reset', 'pubmed-posts'); ?>' />
					<?php endif; ?>
					<input class='search-submit' type='submit' value='<?php _e('Search', 'pubmed-posts'); ?>' />
				</div>
			</div>
		</form>
<?php
		echo $after_widget;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'advanced' => false, 'reset' => false) );
		$title = $instance['title'];
		$advanced = empty($instance['advanced']) ? '' : 'checked="checked"';
		$reset = empty($instance['reset']) ? '' : 'checked="checked"';
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?>: 
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('advanced'); ?>" name="<?php echo $this->get_field_name('advanced'); ?>" value="1" <?php echo $advanced; ?> />
			<label for="<?php echo $this->get_field_id('advanced'); ?>"> <?php _e('Show advanced search'); ?> 
			</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('reset'); ?>" name="<?php echo $this->get_field_name('reset'); ?>" value="1" <?php echo $reset; ?> />
			<label for="<?php echo $this->get_field_id('reset'); ?>"> <?php _e('Hide reset button'); ?>
			</label>
		</p>		
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => '', 'advanced' => '', 'reset' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['advanced'] = strip_tags($new_instance['advanced']);
		$instance['reset'] = strip_tags($new_instance['reset']);
		return $instance;
	}

}	

// End Class