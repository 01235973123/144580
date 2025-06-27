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
use Joomla\CMS\Uri\Uri;

$bootstrapHelper        = $this->bootstrapHelper;
$controlGroupClass      = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass      = $bootstrapHelper->getClassMapping('control-label');
$controlsClass          = $bootstrapHelper->getClassMapping('controls');
$pullRightClass         = $bootstrapHelper->getClassMapping('pull-right');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');

if (EShopHelper::getConfigValue('display_privacy_policy', 'payment_method_step') == 'confirm_step')
{
    if (EShopHelper::getConfigValue('show_privacy_policy_checkbox') || (isset($this->checkoutTermsLink) && $this->checkoutTermsLink != ''))
    {
        ?>
        <script src="<?php echo Uri::root(true); ?>/media/com_eshop/assets/colorbox/jquery.colorbox.js" type="text/javascript"></script>
        <script type="text/javascript">
        	Eshop.jQuery(document).ready(function($){			
        		$(".colorbox").colorbox({
        			overlayClose: true,
        			opacity: 0.5,
        			width: '90%',
        			maxWidth: '900px',
        		});
        	});
        </script>
        <?php
    }
}

if (isset($this->success))
{
	?>
	<div class="success"><?php echo $this->success; ?></div>
	<?php
}

$productFieldsDisplay       = EShopHelper::getConfigValue('product_fields_display', '');
$productFieldsDisplayArr    = array();

if ($productFieldsDisplay != '')
{
    $productFieldsDisplayArr = explode(',', $productFieldsDisplay);
}

$colspan = 2 + count($productFieldsDisplayArr);
?>
<div class="cart-info">
	<table class="table table-responsive table-bordered table-striped">
		<thead>
			<tr>
				<th><?php echo Text::_('ESHOP_PRODUCT_NAME'); ?></th>
				<?php
				if (in_array('product_image', $productFieldsDisplayArr))
				{
				    ?>
				    <th style="text-align: center;"><?php echo Text::_('ESHOP_IMAGE'); ?></th>
				    <?php
				}
				
				if (in_array('product_sku', $productFieldsDisplayArr))
				{
				    ?>
				    <th><?php echo Text::_('ESHOP_MODEL'); ?></th>
				    <?php
				}
				
				if (in_array('product_quantity', $productFieldsDisplayArr))
				{
				    ?>
				    <th><?php echo Text::_('ESHOP_QUANTITY'); ?></th>
				    <?php
				}
				
				if (in_array('product_custom_message', $productFieldsDisplayArr))
				{
					?>
				    <th><?php echo Text::_('ESHOP_CUSTOM_MESSAGE'); ?></th>
				    <?php
				}
				?>
				<th><?php echo Text::_('ESHOP_UNIT_PRICE'); ?></th>
				<th><?php echo Text::_('ESHOP_TOTAL'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($this->cartData as $product)
			{
				$optionData = $product['option_data'];
				$viewProductUrl = Route::_(EShopRoute::getProductRoute($product['product_id'], EShopHelper::getProductCategory($product['product_id'])));
				?>
				<tr>
					<td data-content="<?php echo Text::_('ESHOP_PRODUCT_NAME'); ?>">
						<a href="<?php echo $viewProductUrl; ?>">
							<?php echo $product['product_name']; ?>
						</a><br />	
						<?php
						for ($i = 0; $n = count($optionData), $i < $n; $i++)
						{
							echo '- ' . $optionData[$i]['option_name'] . ': ' . htmlentities($optionData[$i]['option_value']) . (isset($optionData[$i]['sku']) && $optionData[$i]['sku'] != '' ? ' (' . $optionData[$i]['sku'] . ')' : '') . '<br />';
						}
						?>
					</td>
					<?php
					if (in_array('product_image', $productFieldsDisplayArr))
					{
					    ?>
					    <td class="muted eshop-center-text" style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_IMAGE'); ?>">
							<a href="<?php echo $viewProductUrl; ?>">
								<img src="<?php echo $product['image']; ?>" />
							</a>
						</td>
					    <?php
					}
					
					if (in_array('product_sku', $productFieldsDisplayArr))
					{
					    ?>
					    <td data-content="<?php echo Text::_('ESHOP_MODEL'); ?>"><?php echo $product['product_sku']; ?></td>
					    <?php
					}
					
					if (in_array('product_quantity', $productFieldsDisplayArr))
					{
					    ?>
					    <td data-content="<?php echo Text::_('ESHOP_QUANTITY'); ?>">
    						<?php echo $product['quantity']; ?>
    					</td>
					    <?php
					}
					
					if (in_array('product_custom_message', $productFieldsDisplayArr))
					{
						?>
					    <td data-content="<?php echo Text::_('ESHOP_CUSTOM_MESSAGE'); ?>">
    						<?php echo $product['product_custom_message']; ?>
    					</td>
					    <?php
					}
					?>
					<td data-content="<?php echo Text::_('ESHOP_UNIT_PRICE'); ?>">
						<?php
						if (EShopHelper::getConfigValue('include_tax_anywhere', '0'))
						{
							echo $this->currency->format($this->tax->calculate($product['price'], $product['product_taxclass_id'], EShopHelper::getConfigValue('tax')));
						}
						else
						{
							echo $this->currency->format($product['price']);
						}
						?>
					</td>
					<td data-content="<?php echo Text::_('ESHOP_TOTAL'); ?>">
						<?php
						if (EShopHelper::showPrice())
						{
							if (EShopHelper::getConfigValue('include_tax_anywhere', '0'))
							{
								echo $this->currency->format($this->tax->calculate($product['total_price'], $product['product_taxclass_id'], EShopHelper::getConfigValue('tax')));
							}
							else
							{
								echo $this->currency->format($product['total_price']);
							}
						}
						?>
					</td>
				</tr>
				<?php
			}
			foreach ($this->totalData as $data)
			{
				?>
				<tr>
					<td colspan="<?php echo $colspan; ?>" style="text-align: right;"><?php echo $data['title']; ?>:</td>
					<td><strong><?php echo $data['text']; ?></strong></td>
				</tr>
				<?php	
			}
			?>
		</tbody>
	</table>
</div>
<?php
if ($this->total > 0)
{
	if ($this->paymentClass->getName() != 'os_squareup' && $this->paymentClass->getName() != 'os_squarecard')
	{
		?>
		<div class="eshop-payment-information">
			<?php echo $this->paymentClass->renderPaymentInformation($this->privacyPolicyArticleLink, $this->checkoutTermsLink); ?>
		</div>	
		<?php
	}
}
else 
{
    $Itemid = Factory::getApplication()->input->getInt('Itemid', 0);
     
    if (!$Itemid)
    {
        $Itemid = EShopRoute::getDefaultItemId();
    }
	?>
	<script type="text/javascript">
		function validateCheckoutData()
		{
        	form = document.getElementById('payment_method_form');
            <?php
            if (EShopHelper::getConfigValue('display_privacy_policy', 'payment_method_step') == 'confirm_step')
            {
                if (EShopHelper::getConfigValue('show_privacy_policy_checkbox'))
                {
                    ?>
                    if (!form.privacy_policy_agree.checked)
                    {
                        alert("<?php echo Text::_('ESHOP_AGREE_PRIVACY_POLICY_ERROR'); ?>");
                        form.privacy_policy_agree.focus();
                        return false;
        			}
                    <?php
                }
                
                if (EShopHelper::getConfigValue('checkout_terms'))
                {
                    ?>
                    if (!form.checkout_terms_agree.checked)
                    {
                        alert("<?php echo Text::_('ESHOP_ERROR_CHECKOUT_TERMS_AGREE'); ?>");
                        form.checkout_terms_agree.focus();
                        return false;
        			}
                    <?php
                }
            }
            ?>
            form.submit();
		}
	</script>
	<form action="<?php echo EShopHelper::getSiteUrl(); ?>index.php?option=com_eshop&task=checkout.processOrder&Itemid=<?php echo $Itemid; ?>" method="post" name="payment_method_form" id="payment_method_form" class="form form-horizontal">
		<?php
        if (EShopHelper::getConfigValue('display_privacy_policy', 'payment_method_step') == 'confirm_step')
        {
            if (EShopHelper::getConfigValue('show_privacy_policy_checkbox'))
            {
                ?>
                <div class="<?php echo $controlGroupClass; ?> eshop-privacy-policy">
                	<div class="<?php echo $controlLabelClass; ?>">
                    	<?php
                    	if (isset($this->privacyPolicyArticleLink) && $this->privacyPolicyArticleLink != '')
                    	{
                    	    ?>
                    	    <a class="colorbox cboxElement" href="<?php echo $this->privacyPolicyArticleLink; ?>"><?php echo Text::_('ESHOP_PRIVACY_POLICY'); ?></a>
                    	    <?php
                    	}
                    	else 
                    	{
                    	    echo Text::_('ESHOP_PRIVACY_POLICY');
                    	}
                    	?>
                	</div>
                	<div class="<?php echo $controlsClass; ?>">
                		<input type="checkbox" name="privacy_policy_agree" value="1" />
            			<?php
            			$agreePrivacyPolicyMessage = Text::_('ESHOP_AGREE_PRIVACY_POLICY_MESSAGE');
            
            			if (strlen($agreePrivacyPolicyMessage))
            			{
            			?>
                            <div class="eshop-agree-privacy-policy-message alert alert-info"><?php echo $agreePrivacyPolicyMessage;?></div>
            			<?php
            			}
            			?>
                	</div>
                </div>
                <?php
            }
            
            if (EShopHelper::getConfigValue('acymailing_integration') || EShopHelper::getConfigValue('mailchimp_integration'))
            {
                ?>
                <div class="<?php echo $controlGroupClass; ?> eshop-newsletter-interest">
                	<label for="textarea" class="checkbox">
                		<input type="checkbox" value="1" name="newsletter_interest" /><?php echo Text::_('ESHOP_NEWSLETTER_INTEREST'); ?>
                	</label>
                </div>
                <?php
            }
            
            if (isset($this->checkoutTermsLink) && $this->checkoutTermsLink != '')
            {
                ?>
                <div class="<?php echo $controlGroupClass; ?> eshop-checkout-terms">
                	<label for="textarea" class="checkbox">
                		<input type="checkbox" value="1" name="checkout_terms_agree" <?php echo $this->checkout_terms_agree ?: ''; ?>/>
            			<?php echo Text::_('ESHOP_CHECKOUT_TERMS_AGREE'); ?>&nbsp;<a class="colorbox cboxElement" href="<?php echo $this->checkoutTermsLink; ?>"><?php echo Text::_('ESHOP_CHECKOUT_TERMS_AGREE_TITLE'); ?></a>
                	</label>
                </div>
                <?php
            }
        }
        ?>
		<div class="no_margin_left">
			<div class="no_margin_left">
				<input id="button-confirm" type="button" onclick="validateCheckoutData();" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullRightClass; ?>" value="<?php echo Text::_('ESHOP_CONFIRM_ORDER'); ?>" />
			</div>
		</div>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
	<?php
}