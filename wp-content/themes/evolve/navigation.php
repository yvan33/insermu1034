<?php
/**
 * Template: Navigation.php 
 *
 * @package EvoLve
 * @subpackage Template
 */

if ( is_singular() and !is_page() ) { ?>
<!--BEGIN .navigation-links-->
<div class="navigation-links single-page-navigation clearfix row">
	<div class="col-sm-6 col-md-6 nav-previous"><?php previous_post_link( '%link', '<div class="btn btn-left icon-arrow-left icon-big">%title</div>' ); ?></div>
	<div class="col-sm-6 col-md-6 nav-next"><?php next_post_link( '%link', '<div class="btn btn-right icon-arrow-right icon-big">%title</div>' ); ?></div>
<!--END .navigation-links-->
</div>
<div class="clearfix"></div> 
<?php } else { ?>
<!--BEGIN .navigation-links-->
<div class="navigation-links page-navigation clearfix">
<?php if (function_exists('wp_pagenavi')) : ?>
        <?php wp_pagenavi(); ?>
    <?php else: ?>
	<div class="col-sm-6 col-md-6 nav-next"><?php previous_posts_link( __( '<div class="btn btn-left icon-arrow-left icon-big">Newer Entries</div>', 'evolve' ) ); ?></div>
  <div class="col-sm-6 col-md-6 nav-previous"><?php next_posts_link( __( '<div class="btn btn-right icon-arrow-right icon-big">Older Entries</div>', 'evolve' ) ); ?></div>
  <?php endif; ?>
<!--END .navigation-links-->
</div>
<div class="clearfix"></div> 

<?php } ?>