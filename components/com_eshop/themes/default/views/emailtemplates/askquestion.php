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

?>
<table style="width: 100%; margin-bottom: 20px;">
	<tr>
		<td width="30%" style="font-size: 12px; text-align: right; padding: 7px;" valign="top"><strong><?php echo Text::_('ESHOP_PRODUCT_NAME'); ?>:</strong></td>
		<td width="70%"><?php echo $product->product_name; ?></td>
	</tr>
	<tr>		
		<td width="30%" style="font-size: 12px; text-align: right; padding: 7px;" valign="top"><strong><?php echo Text::_('ESHOP_MODEL'); ?>:</strong></td>
		<td width="70%"><?php echo $product->product_sku; ?></td>
	</tr>
	<tr>
		<td width="30%" style="font-size: 12px; text-align: right; padding: 7px;" valign="top"><strong><?php echo Text::_('ESHOP_NAME'); ?>:</strong></td>
		<td width="70%"><?php echo $data['name']; ?></td>
	</tr>
	<tr>
		<td width="30%" style="font-size: 12px; text-align: right; padding: 7px;" valign="top"><strong><?php echo Text::_('ESHOP_COMPANY'); ?>:</strong></td>
		<td width="70%"><?php echo $data['company']; ?></td>
	</tr>
	<tr>
		<td width="30%" style="font-size: 12px; text-align: right; padding: 7px;" valign="top"><strong><?php echo Text::_('ESHOP_EMAIL'); ?>:</strong></td>
		<td width="70%"><?php echo $data['email']; ?></td>
	</tr>
	<tr>
		<td width="30%" style="font-size: 12px; text-align: right; padding: 7px;" valign="top"><strong><?php echo Text::_('ESHOP_PHONE'); ?>:</strong></td>
		<td width="70%"><?php echo $data['phone']; ?></td>
	</tr>
	<tr>
		<td width="30%" style="font-size: 12px; text-align: right; padding: 7px;" valign="top"><strong><?php echo Text::_('ESHOP_MESSAGE'); ?>:</strong></td>
		<td width="70%"><?php echo nl2br($data['message']); ?></td>
	</tr>
</table>