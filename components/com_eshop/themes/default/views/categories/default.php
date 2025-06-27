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

if (isset($this->warning))
{
?>
	<div class="warning"><?php echo $this->warning; ?></div>
<?php
}
//Display Shop Instroduction
if (EShopHelper::getMessageValue('shop_introduction') != '' && EShopHelper::getConfigValue('introduction_display_on', 'front_page') == 'categories_page')
{
	?>
	<div class="eshop-shop-introduction"><?php echo EShopHelper::getMessageValue('shop_introduction'); ?></div>
	<?php
}
if (count($this->items)) 
{
	if ($this->params->get('show_page_heading'))
	{
	?>
		<div class="page-header">
			<h1 class="page-title eshop-title"><?php echo $this->params->get('page_heading'); ?></h1>
		</div>	
	<?php
	}
	?>
	<div class="eshop-categories-list"><?php echo EShopHtmlHelper::loadCommonLayout('common/categories.php', array ('categories' => $this->items, 'categoriesPerRow' => $this->categoriesPerRow, 'bootstrapHelper' => $this->bootstrapHelper)); ?></div>
	<?php
	if ($this->pagination->total > $this->pagination->limit) 
	{
	?>
		<div class="pagination">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php
	}
}