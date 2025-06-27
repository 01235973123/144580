<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Router\Route;
?>
<div class="eshop-category<?php echo $params->get( 'moduleclass_sfx' ) ?>">
	<ul>
		<?php
		foreach ($categories as $category)
		{
			if ($showNumberProducts)
			{
				$numberProducts = ' (' . EShopHelper::getNumCategoryProducts($category->id, true) . ')';
			}
			else
			{
				$numberProducts = '';
			}
			?>
			<li>
				<?php
				$active = $category->id == $parentCategoryId ? ' class="active"' : '';
				?>
				<a href="<?php echo Route::_(EShopRoute::getCategoryRoute($category->id)); ?>"<?php echo $active; ?>><?php echo $category->category_name . $numberProducts; ?></a>
				<?php
				if ($showChildren && $category->childCategories)
				{
				?>
					<ul>
					<?php
					foreach ($category->childCategories as $childCategory)
					{
						if ($showNumberProducts)
						{
							$numberProducts = ' (' . EShopHelper::getNumCategoryProducts($childCategory->id, true) . ')';
						}
						else
						{
							$numberProducts = '';
						}
						?>
						<li>
							<?php
							$active = $childCategory->id == $childCategoryId ? 'class="active"' : '';
							?>
							<a href="<?php echo Route::_(EShopRoute::getCategoryRoute($childCategory->id)); ?>" <?php echo $active; ?>> - <?php echo $childCategory->category_name . $numberProducts; ?></a>
						</li>
					<?php
					}
					?>
					</ul>
				<?php
				}
				?>
			</li>
			<?php
		}
		?>
	</ul>
</div>