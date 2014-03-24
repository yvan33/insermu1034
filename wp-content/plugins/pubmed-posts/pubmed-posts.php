<?php
/*
Plugin Name: PubMed Posts
Plugin URI: http://wordpress.org/plugins/pubmed-posts/
Description: This plugin adds a dashboard widget that creates posts from <a href='http://www.ncbi.nlm.nih.gov/pubmed/'>PubMed</a> articles, plus a search widget that finds posts with specific article data.
Version: 1.1.1
Author: sydcode
Author URI: http://profiles.wordpress.org/sydcode
License: GPLv2 or later

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Load PubMed article class
if ( !class_exists('PubMedPostsArticle') ) {
	require(dirname(__FILE__)  . '/class-article.php');
}

// Load search widget class
if ( !class_exists('PubMedPostsSearch') ) {
	require(dirname(__FILE__)  . '/class-search.php');
}

// Load main class
if ( class_exists('PubMedPosts') ) {
	add_action( 'plugins_loaded', array(PubMedPosts::get_instance(), 'setup') );
}

class PubMedPosts {

	protected static $instance = null;
	const PLUGIN_SLUG = 'pubmed-posts';
	const PLUGIN_VERSION = '1.1.1';
	const DEFAULT_TEMPLATE = <<<HTML
<p>[article_authors]</p>
<p>[journal_citation]</p>
<p>PMID: [pmid_link]</a></p>
<h2>Abstract</h2>
<p>[article_abstract]</p>
HTML;

	/**
	 * Constructor
	 */
	function __construct() {}
	
	/**
	 * Get Instance
	 * @return object
	 */	
	public static function get_instance() {
		null === self::$instance and self::$instance = new self;
	 	return self::$instance;
	}
	
	/**
	 * Setup
	 */	
	public function setup() {
		// Actions
		add_action('admin_init', array($this, 'admin_init'));
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
		add_action('admin_menu', array($this, 'settings_menu'));		
		add_action('pre_get_posts', array($this, 'search_posts'), 99);
		add_action('widgets_init', array($this, 'register_search_widget'));
		add_action('wp_dashboard_setup', array($this, 'dashboard_add_widget'));
		add_action('wp_ajax_pubmed-posts', array($this, 'posts_callback'));
		add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
		
		// Filters
		add_filter('author_link', array($this, 'author_link'), 10, 3);
		add_filter('get_the_author_display_name', array($this, 'show_authors'));
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
		add_filter('the_author', array($this, 'show_authors'));
	}	
	
	/**
	 * Register settings
	 */
	public function admin_init() {
		register_setting('pmp_settings_group', 'pmp_settings');
		add_settings_section('pmp_settings_section', '', array($this, 'settings_section'), 'pmp_settings_page');
	}
	
	/**
	 * Load front script and stylesheet
	 */
	public function frontend_scripts() {
		// jQuery TextExt plugin
		$this->load_textext_plugin();
		
		// PubMed Posts stylesheet
		$deps = array(
			'textext-core', 
			'textext-tags', 
			'textext-arrow', 
			'textext-prompt', 
			'textext-autocomplete',
		); 		
		$url = plugins_url('style.css', __FILE__);
		wp_enqueue_style('pubmed-front', $url, $deps, self::PLUGIN_VERSION);	
		
		// PubMed posts script
		$deps = array(
			'jquery', 
			'textext-core', 
			'textext-tags', 
			'textext-arrow', 
			'textext-prompt', 
			'textext-autocomplete',
		);		
		$url = plugins_url('script.js', __FILE__);
		wp_enqueue_script('pubmed-front', $url, $deps, self::PLUGIN_VERSION);	
		
		// Get current tags for autocomplete
		$args = array(
			'hide_empty' => false,
			'fields' => 'names'
		);
		$tags = get_tags($args);
		$tags = json_encode($tags);
		
		// Output data
		$variables = array(
			'tags' => $tags, 
			'tagsText' => __('Tags (press enter to select)', 'pubmed-posts')			
		);
		wp_localize_script('pubmed-front', 'pubMedPosts', $variables);		
	}		

	/**
	 * Load admin scripts and stylesheets
	 */
	public function admin_scripts() {
		// jQuery MultiSelect plugin
		$url = plugins_url('smoothness/jquery-ui-1.10.3.custom.min.css', __FILE__);
		wp_enqueue_style('jquery-smoothness', $url, array(), '1.10.3');	
		
		$url = plugins_url('multiselect/jquery.multiselect.css', __FILE__);
		wp_enqueue_style('jquery-multiselect', $url, array(), '1.13');	
		
		$url = plugins_url('multiselect/jquery.multiselect.min.js', __FILE__);
		wp_enqueue_script('jquery-multiselect', $url, array('jquery', 'jquery-ui-core', 'jquery-ui-widget'), '1.13');		
		
		// jQuery TextExt plugin
		$this->load_textext_plugin();
		
		// PubMed Posts stylesheet
		$deps = array(
			'jquery-smoothness',
			'jquery-multiselect',
			'textext-core', 
			'textext-tags', 
			'textext-arrow', 
			'textext-prompt', 
			'textext-autocomplete',
		); 
		$url = plugins_url('admin.css', __FILE__);
		wp_enqueue_style('pubmed-admin', $url, $deps, self::PLUGIN_VERSION);
		
		// PubMed Posts script
		$deps = array(
			'jquery', 
			'jquery-multiselect',
			'textext-core', 
			'textext-tags', 
			'textext-arrow', 
			'textext-prompt', 
			'textext-autocomplete',
		); 
		$url = plugins_url('admin.js', __FILE__);
		wp_enqueue_script('pubmed-admin', $url, $deps, self::PLUGIN_VERSION);		
	
		// Get categories for autocomplete list
		$categories = array();
		$args = array(
			'hide_empty' => false
		);
		$objects = get_categories($args);
		foreach ($objects as $category) {
			$item = array(
				'id' => $category->term_id, 
				'name' => $category->cat_name
			);
			$categories[] = $item;
		}
		$categories = json_encode($categories);
		
		// Get current tags for autocomplete
		$args = array(
			'hide_empty' => false,
			'fields' => 'names'
		);
		$tags = get_tags($args);
		$tags = json_encode($tags);
		
		// Output data
		$variables = array(
			'categories' => $categories,
			'tags' => $tags, 
			'PMIDText' => __('PMIDs (separate with commas)', 'pubmed-posts'),
			'selectedText' => __('Categories (# of # selected)', 'pubmed-posts'),
			'noneSelectedText' => __('Categories', 'pubmed-posts'),
			'tagsText' => __('Tags (press enter to select)', 'pubmed-posts')
		);
		wp_localize_script('pubmed-admin', 'pubMedPosts', $variables);
	}
	
	/**
	 * Load stylesheets and scripts for jQuery TextExt plugin
	 */
	public function load_textext_plugin() {
		$url = plugins_url('textext/css/textext.core.css', __FILE__);
		wp_enqueue_style('textext-core', $url, array(), '1.3.1');
		
		$url = plugins_url('textext/css/textext.plugin.tags.css', __FILE__);
		wp_enqueue_style('textext-tags', $url, array(), '1.3.1');
		
		$url = plugins_url('textext/css/textext.plugin.arrow.css', __FILE__);
		wp_enqueue_style('textext-arrow', $url, array(), '1.3.1');
		
		$url = plugins_url('textext/css/textext.plugin.prompt.css', __FILE__);
		wp_enqueue_style('textext-prompt', $url, array(), '1.3.1');
		
		$url = plugins_url('textext/css/textext.plugin.autocomplete.css', __FILE__);
		wp_enqueue_style('textext-autocomplete', $url, array(), '1.3.1');
		
		$url = plugins_url('textext/js/textext.core.js', __FILE__);
		wp_enqueue_script('textext-core', $url, array('jquery'), '1.3.1');	
		
		$url = plugins_url('textext/js/textext.plugin.tags.js', __FILE__);
		wp_enqueue_script('textext-tags', $url, array('jquery'), '1.3.1');	
		
		$url = plugins_url('textext/js/textext.plugin.arrow.js', __FILE__);
		wp_enqueue_script('textext-arrow', $url, array('jquery'), '1.3.1');	
		
		$url = plugins_url('textext/js/textext.plugin.prompt.js', __FILE__);
		wp_enqueue_script('textext-prompt', $url, array('jquery'), '1.3.1');	
		
		$url = plugins_url('textext/js/textext.plugin.autocomplete.js', __FILE__);
		wp_enqueue_script('textext-autocomplete', $url, array('jquery'), '1.3.1');	
	}	

	/**
	 * Create dashboard widget
	 */
	public function dashboard_add_widget() {
		$title = '<span class="pubmed-icon-small">' . __('PubMed Posts', 'pubmed-posts') . '</span>';
		add_meta_box(
			'pubmed-dashboard', 
			$title, 
			array($this, 'dashboard_widget_template'), 
			get_current_screen(), 
			'normal', 
			'high'
		);
	}

	/**
	 * Create markup for dashboard widget
	 * @return string
	 */
	public function dashboard_widget_template() {
		ob_start();
		include('template-dashboard.php');
		ob_end_flush();
	}
	
	/**
	 * Create markup for dashboard widget
	 * @return string
	 */
	public function register_search_widget() {
		if (class_exists('PubMedPostsSearch')) {
			register_widget('PubMedPostsSearch');
		}
	}	
	
	/**
	 * Handle AJAX callback for posts
	 * @return string
	 */
	public function posts_callback() {
		// Security check
		if (!wp_verify_nonce($_POST['nonce'], 'pubmed-submit')) {
			wp_die( __('Security check failed', 'pubmed-posts') );
		}
		
		// Get data
		$action = empty($_POST['action']) ? '' : sanitize_text_field($_POST['action']);
		$status = empty($_POST['status']) ? '' : sanitize_text_field($_POST['status']);
		$pmid = empty($_POST['pmid']) ? '' : sanitize_text_field($_POST['pmid']);
		$pmid = explode(',', $pmid);
		$categories = empty($_POST['categories']) ? '' : sanitize_text_field($_POST['categories']);
		$categories = json_decode(stripslashes($categories), false);
		$tags = empty($_POST['tags']) ? '' : sanitize_text_field($_POST['tags']);
		$tags = json_decode(stripslashes($tags), false);

		// Create posts
		foreach ($pmid as $id) {
			if (!empty($id)) {
				$id = trim($id);
				if (is_numeric($id)) {
					$messages[] = '<p>' . $this->create_post($id, $status, $categories, $tags) . '</p>';
				} else {
					$messages[] = '<p>' . $id . ' - ' . __('Invalid identifier', 'pubmed-posts') . '.</p>';
				}
			}
		}
		
		// Send status messages
		echo implode($messages);		
		die();
	}	
	
	/**
	 * Search for PubMed posts
	 * @param object $query
	 * @return object
	 */
	public function search_posts($query) {	
		// Check for PubMed search
		if (!isset($_POST['pubmed-search'])) {
			return $query;
		}
		if ('simple' == $_POST['pubmed-search']) {
			// Simple Search
			if (!empty($_POST['pubmed-keyword'])) {
				$query->set('s', $_POST['pubmed-keyword']);
			}
		} else {
			// Advanced search
			
			// Meta query
			$meta_query = array();
			$meta_fields = array(
				'pubmed-pmid' => 'PMID',
				'article-abstract' => 'Article Abstract',
				'article-affiliation' => 'Article Affiliation',
				'article-authors' => 'Article Authors',				
				'article-title' => 'Article Title',
				'journal-title' => 'Journal Title',
				'journal-volume' => 'Journal Volume',
				'journal-year' => 'Journal Year',
			);		
			$like_fields = array(
				'article-abstract',
				'article-affiliation',
				'article-authors',
				'article-title',
				'journal-title',
			);	
				foreach ($meta_fields as $name => $key) {
				if (!empty($_POST[$name])) {
					$element = array(
						'key' => $key,
						'value' => $_POST[$name],
					);
					if (in_array($name, $like_fields)) {
						$element['compare'] = 'LIKE';
					}
					$meta_query[] = $element;
				}
			}	
			$query->set('meta_query', $meta_query);
			
			// Tags query
			$tax_query = array();
			$tax_fields = array(
				'post-tags-or' => 'IN',
				'post-tags-and' => 'AND',
				'post-tags-not' => 'NOT IN',
			);			
			foreach ($tax_fields as $name => $operator) {
				if (isset($_POST[$name])) {
					$tags = json_decode(stripslashes($_POST[$name]));
					if (!empty($tags)) {
						$tags = array_map('trim', $tags);
						$element = array(
							'taxonomy' => 'post_tag',
							'field' => 'slug',
							'terms' => $tags,			
							'operator' => $operator,
						);
						$tax_query[] = $element;
					}
				}
			}
			$query->set('tax_query', $tax_query);
		}
		return $query;
	}
	
	/**
	 * Create post from PubMed article
	 * @param string $pmid
	 * @param string $status
	 * @param array $tags
	 * @return string
	 */
	public function create_post($pmid, $status, $categories, $tags) {
		// Check if post must be updated
		$settings = get_option('pmp_settings');
		$post_update = empty($settings['update']) ? false : true;
		$post_ID = $this->get_article_post($pmid);
		if (!$post_update && $post_ID) {
			// Exit if update is off and post exists
			return $pmid . ' - ' . __('Post already exists', 'pubmed-posts') . '.';
		}

		// Get PubMed article
		try {
			$article = new PubMedPostsArticle($pmid);
		} catch (Exception $e) {
			return $pmid . ' - ' . __('Article not found', 'pubmed-posts') . '.';
		}
		$data = $article->getData();
		
		// Expand abstract sections
		$article_abstract = '';
		if (!empty($data['article_abstract'])) {
			foreach ($data['article_abstract'] as $section) {
				$label = '';
				foreach($section->attributes() as $key => $value) {
					if ('Label' == $key) {
						$label = '<strong>' . $section['Label'] . ': </strong>';
					}
				}
				$article_abstract .= '<p>' . $label . $section . '</p>';
			}
		}		
		
		// Build post content by parsing template tags
		$content = empty($settings['template']) ? self::DEFAULT_TEMPLATE : $settings['template'];
		foreach ($data as $key => $value) {
			$key = '[' . $key . ']';
			if ('[article_abstract]' == $key) {
				$value = $article_abstract;
			} else {
				$value = empty($value) ? '' : $value;
			}
			$content = str_replace($key, $value, $content);
		}
		if ( empty($data['pmid']) || empty($data['article_url']) ) {
			$content = str_replace('[pmid_link]', '', $content);
		} else {
			$pmid_link = sprintf("<a href='%s' title=''>%s</a>", $data['article_url'], $data['pmid']);
			$content = str_replace('[pmid_link]', $pmid_link, $content); 
		}
		
		// Create post data
		$post_data = array(
			'post_title' => $data['article_title'],
			'post_content' => $content,
			'post_status' => $status
		);
		$date = $this->post_date($data);		
		if (!empty($date)) {	
			$post_data['post_date'] = $date;
		}
		
		// Update post or create new post
		$append_taxonomy = empty($settings['replace']) ? true : false;				
		if ($post_ID) {
			$new_post = false;
			$post_data['ID'] = $post_ID;
			$post_ID = wp_update_post($post_data);
			if (empty($post_ID)) {
				return $pmid . ' - ' . __('Failed to update post', 'pubmed-posts') . '.';
			}
			if ($append_taxonomy) {
				// Append current categories
				$post_categories = wp_get_post_categories($post_ID);
				$categories = array_unique( array_merge($categories, $post_categories) );
			}
		} else {
			$new_post = true;
			$post_ID = wp_insert_post($post_data);
		}

		// Set metadata, categories and tags 
		$this->save_metadata($post_ID, $data);
		wp_set_post_categories($post_ID, $categories);
		wp_set_post_tags($post_ID, $tags, $append_taxonomy);
		
		// Create status messsage
		$post_url = get_permalink($post_ID);
		$post_edit_url = get_edit_post_link($post_ID);
		$post_links = "<a href='" . $post_url . "'>" . __('View post', 'pubmed-posts') . "</a> | ";
		$post_links .= "<a href='" . $post_edit_url . "'>" . __('Edit post', 'pubmed-posts') . "</a>";
		if ($status == 'draft') {
			if ($new_post) {
				$message = __('Post draft created', 'pubmed-posts'); 
			} else {
				$message = __('Post draft updated', 'pubmed-posts'); 
			}
		} else { 
			if ($new_post) {
				$message = __('Post published', 'pubmed-posts'); 
			} else {
				$message = __('Post updated', 'pubmed-posts'); 
			}
		}
		return $pmid . ' - ' . $message  . '. ' . $post_links; 
	}
	
	/**
	 * Get date to use for post date
	 * @param array $data
	 * @return string
	 */	
	public function post_date($data) {
		$settings = get_option('pmp_settings');
		$selected = empty($settings['dates']) ? '' : $settings['dates'];
		if ('1' == $selected) {
			// Support for old date setting
			$selected = 'article_date';
		}
		$date = empty($data[$selected]) ? '' : $data[$selected];
		if (!empty($date)) {
			// Validate date
			$time = strtotime($date);
			if ($time === false) {
				return '';
			}
		}
		return $date;
	}
	
	/**
	 * Save post metadata for article
	 * @param int $post_ID
	 * @param array $data
	 */
	public function save_metadata($post_ID, $data) {	
		delete_post_meta($post_ID, 'Article Abstract');
		foreach ( (array) $data['article_abstract'] as $abstract) {
			add_post_meta($post_ID, 'Article Abstract', $abstract, false);
		}	
		update_post_meta($post_ID, 'Article Affiliation', $data['article_affiliation']);		
		update_post_meta($post_ID, 'Article Authors', $data['article_authors']);
		update_post_meta($post_ID, 'Article Date', $data['article_date']);		
		update_post_meta($post_ID, 'Article Pagination', $data['article_pagination']);
		update_post_meta($post_ID, 'Article Title', $data['article_title']);
		update_post_meta($post_ID, 'Article URL', $data['article_url']);
		update_post_meta($post_ID, 'Date Completed', $data['date_completed']);
		update_post_meta($post_ID, 'Date Created', $data['date_created']);
		update_post_meta($post_ID, 'Date Revised', $data['date_revised']);			
		update_post_meta($post_ID, 'Journal Abbreviation', $data['journal_abbreviation']);
		update_post_meta($post_ID, 'Journal Citation', $data['journal_citation']);
		update_post_meta($post_ID, 'Journal Date', $data['journal_date']);
		update_post_meta($post_ID, 'Journal Day', $data['journal_day']);
		update_post_meta($post_ID, 'Journal Issue', $data['journal_issue']);
		update_post_meta($post_ID, 'Journal Month', $data['journal_month']);
		update_post_meta($post_ID, 'Journal Title', $data['journal_title']);
		update_post_meta($post_ID, 'Journal Volume', $data['journal_volume']);
		update_post_meta($post_ID, 'Journal Year', $data['journal_year']);
		update_post_meta($post_ID, 'PMID', $data['pmid']);
	}

	/**
	 * Get ID of post for a PubMed article
	 * @param string $pmid
	 * @return integer	
	 */
	public function get_article_post($pmid) {
		global $wpdb;
		$pmid = trim($pmid);
		$sql = $wpdb->prepare("
			SELECT post_id
			FROM $wpdb->postmeta
			WHERE meta_key = 'PMID'
			AND meta_value = '%s'
		", $pmid);
		$results = $wpdb->get_results($sql);
		if (empty($results)) {
			return 0;
		} else {
			return $results[0]->post_id;
		}
	}
	
	/**
	 * Build options tree for categories dropdown
	 * @param int $parent
	 * @param int $depth
	 * @return string
	 */	
	public function categories_options($parent = 0, $depth = 0) {
		$args = array(
			'hide_empty' => false,
			'parent' => $parent,
		);
		$categories = get_categories($args);
		$class = empty($depth) ? '' : "class='pubmed-indent" . $depth . "'";
		foreach ($categories as $category) {
			printf("<option %s value='%d'>%s</option>", $class, $category->term_id, $category->name);
			$this->categories_options($category->term_id, $depth + 1);
		}
	}	

	/**
	 * Show PubMed authors
	 * @param string $name
	 * @return string
	 */
	public function show_authors($name) {
		global $post;
		$settings = get_option('pmp_settings');
		if (!empty($settings['authors'])) {
			$authors = get_post_meta($post->ID, 'Article Authors', true);
			if (!empty($authors)) {
				return $authors;
			}
			// Support for version 1.0
			$authors = get_post_meta($post->ID, 'pubmed-article-authors', true);
			if (!empty($authors)) {
				return $authors;
			}
			// Support for beta version
			$authors = get_post_meta($post->ID, 'authors', true);	
			if (!empty($authors)) {
				return $authors;
			}			
		}
		return $name;
	}
	
	/**
	 * Create action link
	 * @param array $links
	 * @return array	
	 */	
	public function plugin_action_links($links) { 
		$link = "<a href='options-general.php?page=" . self::PLUGIN_SLUG . "'>Settings</a>"; 
		array_unshift($links, $link); 
		return $links; 
	}	
	
	/**
	 * Change author link
	 * @param string $link
	 * @param string $author_id
	 * @param string $author_nicename
	 * @return string	
	 */
	public function author_link($link, $author_id, $author_nicename) {
		$settings = get_option('pmp_settings');
		if (!empty($settings['authors'])) {
			$link = '';
		}
		return $link;
	}

	/**
	 * Create settings menu
	 */
	public function settings_menu() {
		add_options_page(
			__('PubMed Posts Settings', 'pubmed-posts'),
			__('PubMed Posts', 'pubmed-posts'), 
			'manage_options', 
			self::PLUGIN_SLUG, 
			array($this, 'settings_page')
		);
	}

	/**
	 * Load template for settings page
	 * @return string	 
	 */
	public function settings_page() {
		if (!current_user_can('manage_options')) { 
			wp_die( __('You do not have sufficient permissions to access this page') );
		} else {
			include('template-settings.php');
		}
	}

	/**
	 * Register settings fields 
	 */
	public function settings_section() {
		add_settings_field(
			'pmp_settings_update', 
			__('Update Post', 'pubmed-posts'), 
			array($this, 'settings_update'),
			'pmp_settings_page', 
			'pmp_settings_section'
		); 	
		add_settings_field(
			'pmp_settings_replace', 
			__('Replace Taxonomy', 'pubmed-posts'), 
			array($this, 'settings_replace'),
			'pmp_settings_page', 
			'pmp_settings_section'
		); 	
		add_settings_field(
			'pmp_settings_authors', 
			__('Post Author', 'pubmed-posts'), 
			array($this, 'settings_authors'),
			'pmp_settings_page', 
			'pmp_settings_section'
		); 
		add_settings_field(
			'pmp_settings_dates', 
			__('Post Date', 'pubmed-posts'), 
			array($this, 'settings_dates'),
			'pmp_settings_page', 
			'pmp_settings_section'
		); 
		add_settings_field(
			'pmp_settings_template', 
			__('Post Template', 'pubmed-posts'), 
			array($this, 'settings_template'),
			'pmp_settings_page', 
			'pmp_settings_section'
		); 
	}
	
	/**
	 * Settings field for update post
	 * @return string	 
	 */	
	public function settings_update() {
		$settings = get_option('pmp_settings');
		$checked = empty($settings['update']) ? '' : "checked='checked'";
		?>
		<input id='pubmed-update' type='checkbox' name='pmp_settings[update]' value='1' <?php echo $checked; ?> />
		<label for='pubmed-update'><?php _e('Update existing post', 'pubmed-posts'); ?></label> 
		<p class='description'><?php _e('Content and custom fields will be updated if post exists', 'pubmed-posts'); ?>.</p>
		<?php
	}	
	
	/**
	 * Settings field for replace taxonomy
	 * @return string	 
	 */	
	public function settings_replace() {
		$settings = get_option('pmp_settings');
		$checked = empty($settings['replace']) ? '' : "checked='checked'";
		?>
		<input id='pubmed-replace' type='checkbox' name='pmp_settings[replace]' value='1' <?php echo $checked; ?> />
		<label for='pubmed-replace'><?php _e('Replace post taxonomy', 'pubmed-posts'); ?></label> 
		<p class='description'><?php _e('New categories and tags will replace existing ones for post', 'pubmed-posts'); ?>.</p>
		<?php
	}		

	/**
	 * Settings field for authors
	 * @return string	 
	 */	
	public function settings_authors() {
		$settings = get_option('pmp_settings');
		$checked = empty($settings['authors']) ? '' : "checked='checked'";
		?>
		<input id='pubmed-authors' type='checkbox' name='pmp_settings[authors]' value='1' <?php echo $checked; ?> />
		<label for='pubmed-authors'><?php _e('Show article authors', 'pubmed-posts'); ?></label> 
		<p class='description'><?php _e('All posts will show the article authors instead of post author', 'pubmed-posts'); ?>.</p>
		<?php
	}
	
	/**
	 * Settings field for dates
	 * @return string	 
	 */	
	public function settings_dates() {
		$settings = get_option('pmp_settings');
		$selected = empty($settings['dates']) ? '' : $settings['dates'];
		?>
		<select id='pubmed-dates' name='pmp_settings[dates]'>
			<option value=''><?php _e('Today', 'pubmed-posts'); ?></option>
			<option value='article_date' <?php selected($selected, 'article_date');?>><?php _e('Article Published', 'pubmed-posts'); ?></option>
			<option value='journal_date' <?php selected($selected, 'journal_date');?>><?php _e('Journal Published', 'pubmed-posts'); ?></option>
			<option value='date_created' <?php selected($selected, 'date_created');?>><?php _e('PubMed Record Created', 'pubmed-posts'); ?></option>
			<option value='date_completed' <?php selected($selected, 'date_completed');?>><?php _e('PubMed Record Completed', 'pubmed-posts'); ?></option>
			<option value='date_revised' <?php selected($selected, 'date_revised');?>><?php _e('PubMed Record Revised', 'pubmed-posts'); ?></option>
		</select>
		<p class='description'><?php _e('New posts will be set to this date', 'pubmed-posts'); ?>.</p>
		<?php
	}

	/**
	 * Settings field for article template
	 * @return string	 
	 */	
	public function settings_template() {
		$settings = get_option('pmp_settings');
		$template = empty($settings['template']) ? self::DEFAULT_TEMPLATE : $settings['template'];
		?>
		<textarea id='pubmed-template' type='checkbox' name='pmp_settings[template]'><?php echo $template; ?></textarea>
		<p class='description'><?php _e('Create a template for new posts using HTML and the following tags', 'pubmed-posts'); ?>.</p>
		<p>
			<code>[pmid], [pmid_link], [date_created], [date_completed], [date_revised],	[article_url], [article_title], [article_date], 
			[article_authors], [article_abstract], [article_pagination], [article_affiliation], [journal_title], [journal_abbreviation], 
			[journal_volume], [journal_issue], [journal_date], [journal_citation]</code>
		</p>
		<?php
	}

} // End Class