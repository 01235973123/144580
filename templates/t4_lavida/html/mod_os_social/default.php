<?php
/**
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="os-social">
	<ul>
		<?php 
		if($params->get('facebook','') != ""){
		?>
		<li><span class="above">Facebook</span><a target="_blank" href="<?php echo $params->get('facebook'); ?>"><i class="fab fa-facebook-f below "></i></a></li>
		<?php } ?>
		<?php 
		if($params->get('twitter','') != ""){
		?>
		<li><span class="above">Twitter</span><a target="_blank" href="<?php echo $params->get('twitter'); ?>"><i class="fab fa-twitter below "></i></a></li>
		<?php } ?>
		<?php 
		if($params->get('google','') != ""){
		?>
		<li><span class="above">Google+</span><a target="_blank" href="<?php echo $params->get('google'); ?>"><i class="fab fa-google-plus below "></i></a></li>
		<?php } ?>
		<?php 
		if($params->get('linkedin','') != ""){
		?>
		<li><span class="above">Linkedin</span><a target="_blank" href="<?php echo $params->get('linkedin'); ?>"><i class="fab fa-linkedin below "></i></a></li>
		<?php } ?>
		<?php 
		if($params->get('youtube','') != ""){
		?>
        <li><span class="above">youtube</span><a target="_blank" href="<?php echo $params->get('youtube'); ?>"><i class="fab fa-youtube below "></i></a></li>
        <?php } ?>
		<?php 
		if($params->get('pinterest','') != ""){
		?>
        <li><span class="above">pinterest</span><a target="_blank" href="<?php echo $params->get('pinterest'); ?>"><i class="fab fa-pinterest below "></i></a></li>
        <?php } ?>
		<?php 
		if($params->get('dribbble','') != ""){
		?>
        <li><span class="above">dribbble</span><a target="_blank" href="<?php echo $params->get('dribbble'); ?>"><i class="fab fa-dribbble below "></i></a></li>
        <?php } ?>
		<?php 
		if($params->get('feedburner','') != ""){
		?>
        <li><span class="above">feedburner</span><a target="_blank" href="<?php echo $params->get('feedburner'); ?>"><i class="fas fa-rss below "></i></a></li>
        <?php } ?>
		<?php 
		if($params->get('instagram','') != ""){
		?>
        <li><span class="above">instagram</span><a target="_blank" href="<?php echo $params->get('instagram'); ?>"><i class="fab fa-instagram below "></i></a></li>
        <?php } ?>
	</ul>
</div>
