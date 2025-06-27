<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2011 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

if (count($tags))
{
	?>
	<p class="TagCloud">
		<?php
		foreach ($tags as $tag)
		{
			?>
			<a title="<?php echo $tag->tag_name; ?>" href="<?php echo $tag->link; ?>" style="font-size: <?php echo $tag->size;?>%; text-decoration: underline;">
				<?php echo $tag->tag_name;?>
			</a>&nbsp;
			<?php
		}
		?>
	</p>
	<?php
}