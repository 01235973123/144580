<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$span = intval(12 / $subCategoriesPerRow);
$rowFluidClass          = $bootstrapHelper->getClassMapping('row-fluid');
$spanClass              = $bootstrapHelper->getClassMapping('span' . $span);
?>
<div class="<?php echo $rowFluidClass; ?>">
	<?php
	if (EShopHelper::getConfigValue('sub_categories_layout') == 'list_with_only_link')
	{
		?>
		<h4><?php echo Text::_('ESHOP_REFINE_SEARCH'); ?></h4>
	    <ul>
			<?php 
			foreach ($subCategories as $subCategory)
			{
				?>
				<li>
					<h5>
						<a href="<?php echo Route::_(EShopRoute::getCategoryRoute($subCategory->id)); ?>">
							<?php echo $subCategory->category_name; ?>
						</a>
					</h5>
				</li>
				<?php
			}
			?> 
	    </ul>
		<?php
	}
	else 
	{
		$count = 0;
		foreach ($subCategories as $subCategory) 
		{
			$subCategoryUrl = Route::_(EShopRoute::getCategoryRoute($subCategory->id));
			?>
			<div class="<?php echo $spanClass; ?>">
				<div class="eshop-category-wrap">
		        	<div class="image">
					<a href="<?php echo $subCategoryUrl; ?>" title="<?php echo $subCategory->category_page_title != '' ? $subCategory->category_page_title : $subCategory->category_name; ?>">
						<img src="<?php echo $subCategory->image; ?>" alt="<?php echo $subCategory->category_alt_image != '' ? $subCategory->category_alt_image : $subCategory->category_name; ?>" />	            
					</a>
		            </div>
					<div class="eshop-info-block">
						<h5>
							<a href="<?php echo $subCategoryUrl; ?>" title="<?php echo $subCategory->category_page_title != '' ? $subCategory->category_page_title : $subCategory->category_name; ?>">
								<?php echo $subCategory->category_name; ?>
							</a>
						</h5>
					</div>
				</div>	
			</div>
			<?php
			$count++;
			if ($count % $subCategoriesPerRow == 0 && $count < count($subCategories))
			{
			?>
				</div><div class="<?php echo $rowFluidClass; ?>">
			<?php
			}
		}
	}
	?>
</div>
<hr />