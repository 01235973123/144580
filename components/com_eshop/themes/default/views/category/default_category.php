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

use Joomla\CMS\Router\Route;

$bootstrapHelper        = $this->bootstrapHelper;
$rowFluidClass          = $bootstrapHelper->getClassMapping('row-fluid');
$span4Class             = $bootstrapHelper->getClassMapping('span4');
$span6Class             = $bootstrapHelper->getClassMapping('span6');
$span8Class             = $bootstrapHelper->getClassMapping('span8');
$span12Class            = $bootstrapHelper->getClassMapping('span12');
$pullLeftClass          = $bootstrapHelper->getClassMapping('pull-left');
$pullRightClass          = $bootstrapHelper->getClassMapping('pull-right');
$imgPolaroid            = $bootstrapHelper->getClassMapping('img-polaroid');

if (EShopHelper::getConfigValue('show_categories_nav') && (is_object($this->categoriesNavigation[0]) || is_object($this->categoriesNavigation[1])))
{
	?>
	<div class="<?php echo $rowFluidClass; ?>">
		<div class="<?php echo $span6Class; ?> eshop-pre-nav">
			<?php
			if (is_object($this->categoriesNavigation[0]))
			{
				?>
				<a class="<?php echo $pullLeftClass; ?>" href="<?php echo Route::_(EShopRoute::getCategoryRoute($this->categoriesNavigation[0]->id)); ?>" title="<?php echo $this->categoriesNavigation[0]->category_page_title != '' ? $this->categoriesNavigation[0]->category_page_title : $this->categoriesNavigation[0]->category_name; ?>">
					<?php echo $this->categoriesNavigation[0]->category_name; ?>
				</a>
				<?php
			}
			?>
		</div>
		<div class="<?php echo $span6Class; ?> eshop-next-nav">
			<?php
			if (is_object($this->categoriesNavigation[1]))
			{
				?>
				<a class="<?php echo $pullRightClass; ?>" href="<?php echo Route::_(EShopRoute::getCategoryRoute($this->categoriesNavigation[1]->id)); ?>" title="<?php echo $this->categoriesNavigation[1]->category_page_title != '' ? $this->categoriesNavigation[1]->category_page_title : $this->categoriesNavigation[1]->category_name; ?>">
					<?php echo $this->categoriesNavigation[1]->category_name; ?>
				</a>
				<?php
			}
			?>
		</div>
	</div>
	<?php
}
?>
<div class="page-header">
	<h1 class="page-title eshop-title"><?php echo $this->category->category_page_heading != '' ? $this->category->category_page_heading : $this->category->category_name; ?></h1>
</div>	
<?php
if (EShopHelper::getConfigValue('show_category_image') || EShopHelper::getConfigValue('show_category_desc'))
{
	?>
	<div class="<?php echo $rowFluidClass; ?>">
		<?php
		if (EShopHelper::getConfigValue('show_category_image'))
		{
			?>
			<div class="<?php echo $span4Class; ?>">
				<img class="<?php echo $imgPolaroid; ?>" src="<?php echo $this->category->image; ?>" title="<?php echo $this->category->category_page_title != '' ? $this->category->category_page_title : $this->category->category_name; ?>" alt="<?php echo $this->category->category_alt_image != '' ? $this->category->category_alt_image : $this->category->category_name; ?>" />
			</div>
			<?php
		}
		if (EShopHelper::getConfigValue('show_category_desc'))
		{
			?>
			<div class="<?php echo (EShopHelper::getConfigValue('show_category_image') ? $span8Class : $span12Class); ?>"><?php echo $this->category->category_desc; ?></div>
			<?php
		}
		?>
	</div>
	<hr />
	<?php
}