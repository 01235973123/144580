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

if (isset($this->success))
{
	?>
	<div class="success"><?php echo $this->success; ?></div>
	<?php
}
?>
<div class="page-header">
	<h1 class="page-title eshop-title"><?php echo Text::_('ESHOP_MY_ACCOUNT'); ?></h1>
</div>	
<?php
if (EShopHelper::getConfigValue('customer_manage_account', '1') || EShopHelper::getConfigValue('customer_manage_order', '1') || EShopHelper::getConfigValue('customer_manage_download', '1') || EShopHelper::getConfigValue('customer_manage_address', '1'))
{
	?>
	<ul>
		<?php
		if (EShopHelper::getConfigValue('customer_manage_account', '1'))
		{
			?>
			<li>
				<a href="<?php echo Route::_(EShopRoute::getViewRoute('customer').'&layout=account'); ?>">
					<?php echo Text::_('ESHOP_EDIT_ACCOUNT'); ?>
				</a>
			</li>
			<?php
		}
		if (EShopHelper::getConfigValue('customer_manage_order', '1'))
		{
			?>
			<li>
				<a href="<?php echo Route::_(EShopRoute::getViewRoute('customer').'&layout=orders'); ?>">
					<?php echo Text::_('ESHOP_ORDER_HISTORY'); ?>
				</a>
			</li>
			<?php
		}
		if (EShopHelper::getConfigValue('customer_manage_quote', '1'))
		{
			?>
			<li>
				<a href="<?php echo Route::_(EShopRoute::getViewRoute('customer').'&layout=quotes'); ?>">
					<?php echo Text::_('ESHOP_QUOTE_HISTORY'); ?>
				</a>
			</li>
			<?php
		}
		if (EShopHelper::getConfigValue('customer_manage_download', '1'))
		{
			?>
			<li>
				<a href="<?php echo Route::_(EShopRoute::getViewRoute('customer').'&layout=downloads'); ?>">
					<?php echo Text::_('ESHOP_DOWNLOADS'); ?>
				</a>
			</li>			
			<?php
		}
		if (EShopHelper::getConfigValue('customer_manage_address', '1'))
		{
			?>
			<li>
				<a href="<?php echo Route::_(EShopRoute::getViewRoute('customer').'&layout=addresses'); ?>">
					<?php echo Text::_('ESHOP_MODIFY_ADDRESS'); ?>
				</a>
			</li>			
			<?php
		}
		?>
	</ul>
	<?php
}
else 
{
	echo Text::_('ESHOP_CUSTOMER_PAGE_NOT_AVAILABLE');
}