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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'quote.cancel') {
			Joomla.submitform(pressbutton, form);
			return;
		} else {
			Joomla.submitform(pressbutton, form);
		}
	}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<p><?php echo sprintf(Text::_('ESHOP_QUOTE_INTRO'), $this->item->name, HTMLHelper::_('date', $this->item->created_date, EShopHelper::getConfigValue('date_format', 'm-d-Y'))); ?></p>
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('ESHOP_QUOTE_CUSTOMER_DETAILS'); ?></legend>
		<table class="adminlist table table-bordered" style="text-align: center;">
			<tbody>
				<?php
				if (EShopHelper::getConfigValue('quote_form_name_published', 1))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_NAME');?></strong></td>
				    	<td class="text_left"><?php echo $this->item->name; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_email_published', 1))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_EMAIL');?></strong></td>
				    	<td class="text_left"><a href="mailto: <?php echo $this->item->email; ?>"><?php echo $this->item->email; ?></a></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_company_published', 1))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_COMPANY');?></strong></td>
				    	<td class="text_left"><?php echo $this->item->company; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_telephone_published', 1))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_TELEPHONE');?></strong></td>
				    	<td class="text_left"><?php echo $this->item->telephone; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_address_published', 0))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_ADDRESS');?></strong></td>
				    	<td class="text_left"><?php echo $this->item->address; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_city_published', 0))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_CITY');?></strong></td>
				    	<td class="text_left"><?php echo $this->item->city; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_postcode_published', 0))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_POSTCODE');?></strong></td>
				    	<td class="text_left"><?php echo $this->item->postcode; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_country_published', 0))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_COUNTRY');?></strong></td>
				    	<td class="text_left"><?php echo $this->item->country_name; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_state_published', 0))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_STATE');?></strong></td>
				    	<td class="text_left"><?php echo $this->item->zone_name; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_message_published', 1))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_MESSAGE');?></strong></td>
				    	<td class="text_left"><?php echo $this->item->message; ?></td>
				    </tr>
				    <?php
				}
				?>
			</tbody>
		</table>
		
	</fieldset>
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('ESHOP_QUOTE_QUOTATION_PRODUCTS'); ?></legend>
		<table class="adminlist table table-bordered" style="text-align: center;">
			<thead>
				<tr>
					<th class="text_left" width="40%"><?php echo Text::_('ESHOP_PRODUCT_NAME'); ?></th>
					<th class="text_left" width="15%"><?php echo Text::_('ESHOP_MODEL'); ?></th>
					<th class="text_right" width="15%"><?php echo Text::_('ESHOP_QUANTITY'); ?></th>
					<?php
					if (EShopHelper::showPrice())
					{
						?>
						<th class="text_right" width="15%"><?php echo Text::_('ESHOP_UNIT_PRICE'); ?></th>
						<th class="text_right" width="15%"><?php echo Text::_('ESHOP_TOTAL'); ?></th>
						<?php
					}
					?>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ($this->lists['quote_products'] as $product)
			{
				$options = $product->options;
				?>
				<tr>
					<td class="text_left">
						<?php
						echo '<b>' . $product->product_name . '</b>';
						for ($i = 0; $n = count($options), $i < $n; $i++)
						{
							if ($options[$i]->option_type == 'File' && $options[$i]->option_value != '')
							{
								echo '<br />- ' . $options[$i]->option_name . ': <a href="index.php?option=com_eshop&task=quote.downloadFile&id=' . $options[$i]->id . '">' . htmlentities($options[$i]->option_value) . '</a>';
							}
							else
							{
								echo '<br />- ' . $options[$i]->option_name . ': ' . htmlentities($options[$i]->option_value) . (isset($options[$i]->sku) && $options[$i]->sku != '' ? ' (' . $options[$i]->sku . ')' : '');
							}
						}
						?>
					</td>
					<td class="text_left"><?php echo $product->product_sku; ?></td>
					<td class="text_right"><?php echo $product->quantity; ?></td>
					<?php
					if (EShopHelper::showPrice())
					{
						?>
						<td class="text_right">
							<?php
							if (!$product->product_call_for_price)
							{
								echo $this->currency->format($product->price, $this->item->currency_code, $this->item->currency_exchanged_value);
							}
							?>
						</td>
						<td class="text_right">
							<?php
							if (!$product->product_call_for_price)
							{
								echo $this->currency->format($product->total_price, $this->item->currency_code, $this->item->currency_exchanged_value);
							}
							?>
						</td>
						<?php
					}
					?>
				</tr>
				<?php
			}
			if (EShopHelper::showPrice())
			{
				if (count($this->lists['quote_totals']))
				{
					foreach ($this->lists['quote_totals'] as $total)
					{
						?>
						<tr>
							<td colspan="4" class="text_right"><?php echo $total->title; ?>:</td>
							<td class="text_right"><?php echo $total->text; ?></td>
						</tr>
						<?php	
					}
				}
				else 
				{
					?>
					<tr>
						<td colspan="4" class="text_right"><?php echo Text::_('ESHOP_TOTAL'); ?>:</td>
						<td class="text_right"><?php echo $this->currency->format($this->item->total, $this->item->currency_code, $this->item->currency_exchanged_value); ?></td>
					</tr>
					<?php
				}	
			}
			?>
			</tbody>
		</table>
	</fieldset>
	<div class="clearfix"></div>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="cid[]" value="<?php echo intval($this->item->id); ?>" />
	<input type="hidden" name="task" value="" />
</form>