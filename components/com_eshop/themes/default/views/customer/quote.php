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

if(!$this->quoteProducts)
{
	?>
	<div class="warning"><?php echo Text::_('ESHOP_QUOTE_DOES_NOT_EXITS'); ?></div>
	<?php
}
else
{
	?>
	<form id="adminForm">
		<table cellpadding="0" cellspacing="0" class="list">
			<thead>
				<tr>
					<td colspan="2" class="left">
						<?php echo Text::_('ESHOP_QUOTE_DETAILS'); ?>
					</td>
				</tr>
			</thead>
			<tbody>
				<tr>
			    	<td class="text_left"><strong><?php echo Text::_('ESHOP_QUOTE_ID');?></strong></td>
			    	<td class="text_left">#<?php echo $this->quoteInfor->id; ?></td>
			    </tr>
				<?php
				if (EShopHelper::getConfigValue('quote_form_name_published', 1))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_NAME');?></strong></td>
				    	<td class="text_left"><?php echo $this->quoteInfor->name; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_email_published', 1))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_EMAIL');?></strong></td>
				    	<td class="text_left"><a href="mailto: <?php echo $this->quoteInfor->email; ?>"><?php echo $this->quoteInfor->email; ?></a></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_company_published', 1))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_COMPANY');?></strong></td>
				    	<td class="text_left"><?php echo $this->quoteInfor->company; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_telephone_published', 1))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_TELEPHONE');?></strong></td>
				    	<td class="text_left"><?php echo $this->quoteInfor->telephone; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_address_published', 0))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_ADDRESS');?></strong></td>
				    	<td class="text_left"><?php echo $this->quoteInfor->address; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_city_published', 0))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_CITY');?></strong></td>
				    	<td class="text_left"><?php echo $this->quoteInfor->city; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_postcode_published', 0))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_POSTCODE');?></strong></td>
				    	<td class="text_left"><?php echo $this->quoteInfor->postcode; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_country_published', 0))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_COUNTRY');?></strong></td>
				    	<td class="text_left"><?php echo $this->quoteInfor->country_name; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_state_published', 0))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_STATE');?></strong></td>
				    	<td class="text_left"><?php echo $this->quoteInfor->zone_name; ?></td>
				    </tr>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_message_published', 1))
				{
				    ?>
				    <tr>
				    	<td class="text_left"><strong><?php echo Text::_('ESHOP_MESSAGE');?></strong></td>
				    	<td class="text_left"><?php echo $this->quoteInfor->message; ?></td>
				    </tr>
				    <?php
				}
				?>
			</tbody>
		</table>
		<table cellpadding="0" cellspacing="0" class="list">
			<thead>
				<tr>
					<td class="left">
						<?php echo Text::_('ESHOP_PRODUCT_NAME'); ?>
					</td>
					<td class="left">
    					<?php echo Text::_('ESHOP_MODEL'); ?>
    				</td>
    				<td class="left">
    						<?php echo Text::_('ESHOP_QUANTITY'); ?>
    				</td>
					<?php
					if (EShopHelper::showPrice())
					{
						?>
						<td class="left">
							<?php echo Text::_('ESHOP_PRICE'); ?>
						</td>
						<td class="left">
							<?php echo Text::_('ESHOP_TOTAL'); ?>
						</td>
						<?php
					}
    				?>
				</tr>
			</thead>
			<tbody>
				<?php
				$colspan = 1;
				
				foreach ($this->quoteProducts as $product)
				{
					$options = $product->quoteOptions;
					?>
					<tr>
						<td class="left">
							<?php
							echo '<b>' . $product->product_name . '</b>';
							
							if (!empty($options))
							{
								for ($i = 0; $n = count($options), $i < $n; $i++)
								{
									echo '<br />- ' . $options[$i]->option_name . ': ' . $options[$i]->option_value . (isset($options[$i]->sku) && $options[$i]->sku != '' ? ' (' . $options[$i]->sku . ')' : '');
								}	
							}
							?>
						</td>
						<td class="left"><?php echo $product->product_sku; ?></td>
						<td class="left"><?php echo $product->quantity; ?></td>
						<?php
						if (EShopHelper::showPrice())
						{
							$colspan = 3;
							?>
							<td class="right"><?php echo $product->price; ?></td>
							<td class="right"><?php echo $product->total_price; ?></td>
							<?php
						}
        				?>
					</tr>
					<?php
				}
				?>
			</tbody>
			<tfoot>
				<?php
				foreach ($this->quoteTotals as $quotetotal)
				{ 
				?>
				<tr>
					<td colspan="<?php echo $colspan; ?>"></td>
					<td class="right">
						<b><?php echo $quotetotal->title?>: </b>
					</td>
					<td class="right">
						<?php echo $quotetotal->text?>
					</td>
				</tr>
				<?php
				}
				?>
			</tfoot>
		</table>
	</form>
	<div class="no_margin_left">
		<input type="button" value="<?php echo Text::_('ESHOP_BACK'); ?>" id="button-user-quoteinfor" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullRightClass; ?>">
	</div>
	<?php
}
?>
<script type="text/javascript">
	Eshop.jQuery(function($){
		$(document).ready(function(){
			$('#button-user-quoteinfor').click(function(){
				var url = '<?php echo Route::_(EShopRoute::getViewRoute('customer') . '&layout=quotes'); ?>';
				$(location).attr('href',url);
			});
		})
	});
</script>