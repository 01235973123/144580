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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$input  = Factory::getApplication()->input;
if(count($this->products))
{
	$descriptionMaxChars = $input->getInt('description_max_chars');
	foreach ($this->products as $product)
	{
		$viewProductUrl = Route::_(EShopRoute::getProductRoute($product->id, EShopHelper::getProductCategory($product->id)));
		?>
		<li>
			<a href="<?php echo $viewProductUrl; ?>">
				<img alt="<?php echo $product->product_name; ?>" src="<?php echo $product->image; ?>" />
			</a>
			<div>
				<a href="<?php echo $viewProductUrl; ?>"><?php echo $product->product_name; ?></a><br />
				<span><?php echo EShopHelper::substring($product->product_short_desc, $descriptionMaxChars, '...'); ?></span>
			</div>
		</li>
		<?php
	}
}
else
{
	?>
	<li><?php echo Text::_('ESHOP_NO_PRODUCTS'); ?></li>
	<?php 
}