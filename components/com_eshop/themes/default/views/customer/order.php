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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$bootstrapHelper        = $this->bootstrapHelper;
$pullRightClass         = $bootstrapHelper->getClassMapping('pull-right');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');

$user = Factory::getUser();
$language = Factory::getLanguage();
$tag = $language->getTag();

if (!$tag)
{
	$tag = 'en-GB';
}

if(!$this->orderProducts)
{
	?>
	<div class="warning"><?php echo Text::_('ESHOP_ORDER_DOES_NOT_EXITS'); ?></div>
	<?php
}
else
{
	$hasShipping = $this->orderInfor->shipping_method;
	$productFieldsDisplay       = EShopHelper::getConfigValue('product_fields_display', '');
	$productFieldsDisplayArr    = array();
	
	if ($productFieldsDisplay != '')
	{
	    $productFieldsDisplayArr = explode(',', $productFieldsDisplay);
	}
	
	$colspan = 1 + count($productFieldsDisplayArr);
	?>
	<form id="adminForm">
		<table cellpadding="0" cellspacing="0" class="list">
			<thead>
				<tr>
					<td colspan="2" class="left">
						<?php echo Text::_('ESHOP_ORDER_DETAILS'); ?>
					</td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width: 50%;" class="left">
						 <b><?php echo Text::_('ESHOP_ORDER_ID'); ?>: </b>#<?php echo $this->orderInfor->id; ?><br />
						 <b><?php echo Text::_('ESHOP_ORDER_NUMBER'); ?>: </b><?php echo $this->orderInfor->order_number; ?><br />
						 <b><?php echo Text::_('ESHOP_TRANSACTION_ID'); ?>: </b><?php echo $this->orderInfor->transaction_id; ?><br />
	         			 <b><?php echo Text::_('ESHOP_DATE_ADDED'); ?>: </b> <?php echo HTMLHelper::date($this->orderInfor->created_date, EShopHelper::getConfigValue('date_format', 'm-d-Y'), null); ?>
	         		</td>
					<td style="width: 50%;" class="left">
					    <b><?php echo Text::_('ESHOP_PAYMENT_METHOD'); ?>: </b> <?php echo Text::_($this->orderInfor->payment_method_title); ?><br />
					    <b><?php echo Text::_('ESHOP_SHIPPING_METHOD'); ?>: </b> <?php echo Text::_($this->orderInfor->shipping_method_title); ?><br />
					    <b><?php echo Text::_('ESHOP_SHIPPING_TRACKING_NUMBER'); ?>: </b> <?php echo Text::_($this->orderInfor->shipping_tracking_number); ?><br />
					    <b><?php echo Text::_('ESHOP_SHIPPING_TRACKING_URL'); ?>: </b> <a href="<?php echo $this->orderInfor->shipping_tracking_url; ?>" target="_blank"><?php echo $this->orderInfor->shipping_tracking_url; ?></a>
	                </td>
				</tr>
			</tbody>
		</table>
		<table cellpadding="0" cellspacing="0" class="list">
			<thead>
				<tr>
					<td class="left">
						<?php echo Text::_('ESHOP_PAYMENT_ADDRESS'); ?>
					</td>
					<?php
					if ($hasShipping)
					{
						?>
						<td class="left">
							<?php echo Text::_('ESHOP_SHIPPING_ADDRESS'); ?>
						</td>
						<?php
					}
					?>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="left">
						<?php
						echo EShopHelper::getPaymentAddress($this->orderInfor);
						$excludedFields = array('firstname', 'lastname', 'email', 'telephone', 'fax', 'company', 'company_id', 'address_1', 'address_2', 'city', 'postcode', 'country_id', 'zone_id');
						foreach ($this->paymentFields as $field)
						{
							$fieldName = $field->name;
							if (!in_array($fieldName, $excludedFields))
							{
								$fieldValue = $this->orderInfor->{'payment_'.$fieldName};
								if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
								{
									$fieldValue = implode(', ', json_decode($fieldValue));
								}
								if ($fieldValue != '')
								{
									echo '<br />' . Text::_($field->title) . ': ' . $fieldValue;
								}
							}
						}
						?>
					</td>
					<?php
					if ($hasShipping)
					{
						?>
						<td class="left">
							<?php
							echo EShopHelper::getShippingAddress($this->orderInfor);
							foreach ($this->shippingFields as $field)
							{
								$fieldName = $field->name;
								if (!in_array($fieldName, $excludedFields))
								{
									$fieldValue = $this->orderInfor->{'shipping_'.$fieldName};
									if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
									{
										$fieldValue = implode(', ', json_decode($fieldValue));
									}
									if ($fieldValue != '')
									{
										echo '<br />' . Text::_($field->title) . ': ' . $fieldValue;
									}
								}
							}
							?>
						</td>
						<?php
					}
					?>
				</tr>
			</tbody>
		</table>
		<table cellpadding="0" cellspacing="0" class="list">
			<thead>
				<tr>
					<td class="left">
						<?php echo Text::_('ESHOP_PRODUCT_NAME'); ?>
					</td>
					<?php
    				if (in_array('product_image', $productFieldsDisplayArr))
    				{
    				    ?>
    				    <td class="center">
    						<?php echo Text::_('ESHOP_IMAGE'); ?>
    					</td>
    				    <?php
    				}
    				
    				if (in_array('product_sku', $productFieldsDisplayArr))
    				{
    				    ?>
    				    <td class="left">
    						<?php echo Text::_('ESHOP_MODEL'); ?>
    					</td>
    				    <?php
    				}
    				
    				if (in_array('product_quantity', $productFieldsDisplayArr))
    				{
    				    ?>
    				    <td class="left">
    						<?php echo Text::_('ESHOP_QUANTITY'); ?>
    					</td>
    				    <?php
    				}
    				
    				if (in_array('product_custom_message', $productFieldsDisplayArr))
    				{
    					?>
						<td class="left">
							<?php echo Text::_('ESHOP_CUSTOM_MESSAGE'); ?>
						</td>
						<?php
					}
    				?>
					<td class="left">
						<?php echo Text::_('ESHOP_PRICE'); ?>
					</td>
					<td class="left">
						<?php echo Text::_('ESHOP_TOTAL'); ?>
					</td>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($this->orderProducts as $product)
				{
					$options = $product->options;
					?>
					<tr>
						<td class="left">
							<?php
							echo '<b>' . $product->product_name . '</b>';
							for ($i = 0; $n = count($options), $i < $n; $i++)
							{
								echo '<br />- ' . $options[$i]->option_name . ': ' . $options[$i]->option_value . (isset($options[$i]->sku) && $options[$i]->sku != '' ? ' (' . $options[$i]->sku . ')' : '');
							}
							?>
						</td>
						<?php
        				if (in_array('product_image', $productFieldsDisplayArr))
        				{
        				    ?>
        				    <td class="eshop-center-text" style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_IMAGE'); ?>">
    							<img src="<?php echo $product->image; ?>" />
    						</td>
        				    <?php
        				}
        				
        				if (in_array('product_sku', $productFieldsDisplayArr))
        				{
        				    ?>
        				    <td class="left"><?php echo $product->product_sku; ?></td>
        				    <?php
        				}
        				
        				if (in_array('product_quantity', $productFieldsDisplayArr))
        				{
        				    ?>
        				    <td class="left"><?php echo $product->quantity; ?></td>
        				    <?php
        				}
        				
        				if (in_array('product_custom_message', $productFieldsDisplayArr))
        				{
        					?>
							<td class="left"><?php echo $product->product_custom_message; ?></td>
							<?php
						}
        				?>
						<td class="right"><?php echo $product->price; ?></td>
						<td class="right"><?php echo $product->total_price; ?></td>
					</tr>
					<?php
				}
				?>
			</tbody>
			<tfoot>
				<?php
					foreach ($this->orderTotals as $ordertotal)
					{ 
				?>
				<tr>
					<td colspan="<?php echo $colspan; ?>"></td>
					<td class="right">
						<b><?php echo $ordertotal->title?>: </b>
					</td>
					<td class="right">
						<?php echo $ordertotal->text?>
					</td>
				</tr>
				<?php
					} 
				?>
			</tfoot>
		</table>
		
		<h2><?php echo Text::_('ESHOP_ORDER_HISTORY'); ?></h2>
		<table cellpadding="0" cellspacing="0" class="list">
			<thead>
				<tr>
					<td class="left">
						<?php echo Text::_('ESHOP_DATE_ADDED'); ?>
					</td>
					<td class="left">
						<?php echo Text::_('ESHOP_STATUS'); ?>
					</td>
					<td class="left">
						<?php echo Text::_('ESHOP_COMMENT'); ?>
					</td>
					<?php
					if (EShopHelper::getConfigValue('delivery_date'))
					{
						?>
						<td class="left">
							<?php echo Text::_('ESHOP_DELIVERY_DATE'); ?>
						</td>
						<?php
					}
					?>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="left">
						<?php echo HTMLHelper::date($this->orderInfor->created_date, EShopHelper::getConfigValue('date_format', 'm-d-Y'));?>
					</td>
					<td class="left">
						<?php echo EShopHelper::getOrderStatusName($this->orderInfor->order_status_id, $tag); ?>
					</td>
					<td class="left">
						<?php echo nl2br($this->orderInfor->comment); ?>
					</td>
					<?php
					if (EShopHelper::getConfigValue('delivery_date'))
					{
						?>
						<td class="left">
							<?php echo HTMLHelper::date($this->orderInfor->delivery_date, EShopHelper::getConfigValue('date_format', 'm-d-Y')); ?>
						</td>
						<?php
					}
					?>
				</tr>
			</tbody>
		</table>
	</form>
	<div class="no_margin_left">
		<input type="button" value="<?php echo Text::_('ESHOP_BACK'); ?>" id="button-user-orderinfor" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullRightClass; ?>">
	</div>
	<?php
}
?>
<script type="text/javascript">
	Eshop.jQuery(function($){
		$(document).ready(function(){
			$('#button-user-orderinfor').click(function(){
				var url = '<?php echo Route::_(EShopRoute::getViewRoute('customer') . '&layout=orders'); ?>';
				$(location).attr('href',url);
			});
		})
	});
</script>