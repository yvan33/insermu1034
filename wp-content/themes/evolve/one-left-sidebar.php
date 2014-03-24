<?php
/**
 * Template Name: One left sidebar 
 *
 * @package EvoLve
 * @subpackage Template
 */
get_header();
?>

<!--BEGIN #primary .hfeed-->
<div id="primary" class="hfeed col-md-8">
aa

      <?php 
      $evl_breadcrumbs = evl_get_option('evl_breadcrumbs','1');
      if ($evl_breadcrumbs == "1"):     
      if (!is_home() || !is_front_page()): evolve_breadcrumb();
      endif;            
      endif; ?>



    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>

            <!--BEGIN .hentry-->
            <div id="post-<?php the_ID(); ?>" class="<?php semantic_entries(); ?>"> 
                <h1 class="entry-title"><?php if (get_the_title()) {
            the_title();
        } else {
            _e('Untitled', 'evolve');
        } ?></h1>  

        <?php if (current_user_can('edit_post', $post->ID)): ?>

                    <?php edit_post_link(__('EDIT', 'evolve'), '<span class="edit-page">', '</span>'); ?>


        <?php endif; ?>

                <br /><br />

                <!--BEGIN .entry-content .article-->
                <div class="entry-content article">
        <?php the_content(__('READ MORE &raquo;', 'evolve')); ?>
                    <!--END .entry-content .article-->
                    <div class="clearfix"></div> 
                </div>



                <!-- Auto Discovery Trackbacks
        <?php trackback_rdf(); ?>
                -->
                <!--END .hentry-->
            </div>

            <?php $evl_share_this = evl_get_option('evl_share_this', 'single');
            if (($evl_share_this == "all")) {
                evolve_sharethis();
            }
            ?>

        <?php comments_template('', true); ?>

    <?php endwhile;
endif; ?> 


    <!--END #primary .hfeed-->
</div> 
<?php get_sidebar(); ?>

<?php get_footer(); ?>