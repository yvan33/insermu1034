<?php
/**
 * Template Name: People
 *
 * @package EvoLve
 * @subpackage Template
 */

get_header();
$first = "";
?>


       <?php 
       
       global $authordata;
       $xyz = ""; 
       $options = get_option('evolve'); 
 		   $evl_layout = evl_get_option('evl_layout','2cl'); 
	     $evl_post_layout = evl_get_option('evl_post_layout','two');  
 		   $evl_nav_links = evl_get_option('evl_nav_links','after'); 
 		   $evl_header_meta = evl_get_option('evl_header_meta','single_archive'); 
       $evl_excerpt_thumbnail = evl_get_option('evl_excerpt_thumbnail','0'); 
	     $evl_share_this = evl_get_option('evl_share_this','single'); 
 	     $evl_post_links = evl_get_option('evl_post_links','after'); 
 	     $evl_similar_posts = evl_get_option('evl_similar_posts','disable'); 
       
       if ($evl_layout == "1c") {  
       $imagewidth = "960";
       } elseif ($evl_layout == "2cl" || $evl_layout == "2cr") {
	     $imagewidth = "620";
       } else {
       $imagewidth = "506";
       }
?>
<!--Contenu du trombinoscope--> 
       
<h1> Trombinoscope </h1>
<div class="row" style="margin-left: 0;margin-right: 0;">
<div class="col-md-4" style="height: 230px;background-color: green;"></div>
<div class="col-md-4" style="height: 230px;background-color: red;"></div>
<div class="col-md-4" style="height: 230px;background-color: green;"></div>
</div>
<br/>
<div class="row" style="margin-left: 0;margin-right: 0;">
<div class="col-md-4" style="height: 230px;background-color: green;"></div>
<div class="col-md-4" style="height: 230px;background-color: red;"></div>
<div class="col-md-4" style="height: 230px;background-color: green;"></div>
</div>

<?php get_footer(); ?>

