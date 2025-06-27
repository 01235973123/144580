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
use Joomla\CMS\Uri\Uri;

$bootstrapHelper        = $this->bootstrapHelper;
$rowFluidClass          = $bootstrapHelper->getClassMapping('row-fluid');
$controlGroupClass      = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass      = $bootstrapHelper->getClassMapping('control-label');
$controlsClass          = $bootstrapHelper->getClassMapping('controls');
$pullLeftClass          = $bootstrapHelper->getClassMapping('pull-left');
$inputAppendClass       = $bootstrapHelper->getClassMapping('input-append');
$inputPrependClass      = $bootstrapHelper->getClassMapping('input-prepend');
$imgPolaroid            = $bootstrapHelper->getClassMapping('img-polaroid');
$btnClass				= $bootstrapHelper->getClassMapping('btn');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');

$rootUri = Uri::root(true);
?>
<script src="<?php echo $rootUri; ?>/media/com_eshop/assets/colorbox/jquery.colorbox.js" type="text/javascript"></script>
<?php
if (isset($this->success))
{
	?>
	<div class="success"><?php echo $this->success; ?></div>
	<?php
}
?>
<div class="page-header">
	<h1 class="page-title eshop-title"><?php echo Text::_('ESHOP_QUOTE_CART'); ?></h1>
</div>	
<?php
if (!count($this->quoteData))
{
	?>
	<div class="no-content"><?php echo Text::_('ESHOP_QUOTE_EMPTY'); ?></div>
	<?php
}
else
{
	?>
	<div class="quote-info">
		<table class="table table-responsive table-bordered table-striped">
			<thead>
				<tr>
					<th style="text-align: center;"><?php echo Text::_('ESHOP_REMOVE'); ?></th>
						<th style="text-align: center;"><?php echo Text::_('ESHOP_IMAGE'); ?></th>
					<th><?php echo Text::_('ESHOP_PRODUCT_NAME'); ?></th>
					<th><?php echo Text::_('ESHOP_MODEL'); ?></th>
					<th><?php echo Text::_('ESHOP_QUANTITY'); ?></th>
					<?php
					if (EShopHelper::showPrice())
					{
						?>
						<th><?php echo Text::_('ESHOP_UNIT_PRICE'); ?></th>
						<th><?php echo Text::_('ESHOP_TOTAL'); ?></th>
						<?php
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
				$countProducts = 0;
				foreach ($this->quoteData as $key => $product)
				{
					$countProducts++;
					$optionData = $product['option_data'];
					$viewProductUrl = Route::_(EShopRoute::getProductRoute($product['product_id'], EShopHelper::getProductCategory($product['product_id'])));
					?>
					<tr>
						<td class="eshop-center-text" style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_REMOVE'); ?>">
							<a class="eshop-remove-item-quote" id="<?php echo $key; ?>" style="cursor: pointer;">
								<img alt="<?php echo Text::_('ESHOP_REMOVE'); ?>" title="<?php echo Text::_('ESHOP_REMOVE'); ?>" src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/remove.png" />
							</a>
						</td>
						<td class="muted eshop-center-text" style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_IMAGE'); ?>">
							<a href="<?php echo $viewProductUrl; ?>">
								<img class="<?php echo $imgPolaroid; ?>" src="<?php echo $product['image']; ?>" />
							</a>
						</td>
						<td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_PRODUCT_NAME'); ?>">
							<a href="<?php echo $viewProductUrl; ?>">
								<?php echo $product['product_name']; ?>
							</a>
							<br />	
							<?php
							for ($i = 0; $n = count($optionData), $i < $n; $i++)
							{
								echo '- ' . $optionData[$i]['option_name'] . ': ' . htmlentities($optionData[$i]['option_value']) . (isset($optionData[$i]['sku']) && $optionData[$i]['sku'] != '' ? ' (' . $optionData[$i]['sku'] . ')' : '') . '<br />';
							}
							?>
						</td>
						<td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_MODEL'); ?>"><?php echo $product['product_sku']; ?></td>
						<td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_QUANTITY'); ?>">
							<div class="<?php echo $inputAppendClass; ?> <?php echo $inputPrependClass; ?>">
								<span class="eshop-quantity">
									<input type="hidden" name="key[]" value="<?php echo $key; ?>" />
									<a onclick="quantityUpdate('+', 'quantity_quote_<?php echo $countProducts; ?>', <?php echo EShopHelper::getConfigValue('quantity_step', '1'); ?>);<?php echo EShopHelper::getConfigValue('update_quote_function', 'update_button') == 'quantity_button' ? 'updateQuote();' : ''; ?>" class="<?php echo $btnClass; ?> button-plus" id="quote_<?php echo $countProducts; ?>">+</a>
										<input type="text" class="eshop-quantity-value" value="<?php echo EShopHelper::escape($product['quantity']); ?>" name="quantity[]" id="quantity_quote_<?php echo $countProducts; ?>" />
									<a onclick="quantityUpdate('-', 'quantity_quote_<?php echo $countProducts; ?>', <?php echo EShopHelper::getConfigValue('quantity_step', '1'); ?>);<?php echo EShopHelper::getConfigValue('update_quote_function', 'update_button') == 'quantity_button' ? 'updateQuote();' : ''; ?>" class="<?php echo $btnClass; ?> button-minus" id="quote_<?php echo $countProducts; ?>">-</a>
								</span>
							</div>
						</td>
						<?php
						if (EShopHelper::showPrice())
						{
							?>
							<td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_UNIT_PRICE'); ?>">
								<?php
								if (!$product['product_call_for_price'])
								{
									if (EShopHelper::getConfigValue('include_tax_anywhere', '0'))
									{
										echo $this->currency->format($this->tax->calculate($product['price'], $product['product_taxclass_id'], EShopHelper::getConfigValue('tax')));
									}
									else
									{
										echo $this->currency->format($product['price']);
									}
								}
								?>
							</td>
							<td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_TOTAL'); ?>">
								<?php
								if (!$product['product_call_for_price'])
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
							<?php
						}
						?>
					</tr>
					<?php
				}
				if (EShopHelper::showPrice())
				{
					foreach ($this->totalData as $data)
					{
						?>
						<tr>
							<td colspan="6" style="text-align: right;"><?php echo $data['title']; ?>:</td>
							<td><strong><?php echo $data['text']; ?></strong></td>
						</tr>
						<?php	
					}
				}
				?>
			</tbody>
		</table>
	</div>
	<?php
	if (EShopHelper::getConfigValue('update_quote_function', 'update_button') == 'update_button')
	{
	    ?>
	    <div class="<?php echo $controlGroupClass; ?>" style="text-align: center;">
    		<div class="<?php echo $controlsClass; ?>">
    			<button type="button" class="<?php echo $btnBtnPrimaryClass; ?>" onclick="updateQuote();" id="update-quote"><?php echo Text::_('ESHOP_UPDATE_QUOTE'); ?></button>
    		</div>
    	</div>
	    <?php
	}
	?>
	<div class="<?php echo $rowFluidClass; ?>">
		<legend id="quote-form-title"><?php echo Text::_('ESHOP_QUOTE_FORM'); ?></legend>
		<div id="quote-form-area">
			<form method="post" name="adminForm" id="adminForm" action="index.php" class="form form-horizontal">
				<?php
				if (EShopHelper::getConfigValue('quote_form_name_published', 1))
				{
				    ?>
				    <div class="<?php echo $controlGroupClass; ?>">
    					<label class="<?php echo $controlLabelClass; ?>" for="name">
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_name_required', 1))
    						{
    						    ?>
    						    <span class="required">*</span>
    						    <?php
    						}
    						
    						echo Text::_('ESHOP_QUOTE_NAME'); ?>:
    					</label>
    					<div class="<?php echo $controlsClass; ?> docs-input-sizes">
    						<input type="text" class="input-xlarge form-control" name="name" id="name" value="" />
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_name_required', 1))
    						{
    						    ?>
    						    <span style="display: none;" class="error name-required"><?php echo Text::_('ESHOP_QUOTE_NAME_REQUIRED'); ?></span>
    						    <?php
    						}
    						?>
    					</div>
    				</div>
				    <?php
				}
				
				if (EShopHelper::getConfigValue('quote_form_email_published', 1))
				{
				   ?>
				   <div class="<?php echo $controlGroupClass; ?>">
    					<label class="<?php echo $controlLabelClass; ?>" for="email">
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_email_required', 1))
    						{
    						    ?>
    						    <span class="required">*</span>
    						    <?php
    						}
    						
    						echo Text::_('ESHOP_QUOTE_EMAIL'); ?>:
    					</label>
    					<div class="<?php echo $controlsClass; ?> docs-input-sizes">
    						<input type="text" class="input-xlarge form-control" name="email" id="email" value="" />
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_email_required', 1))
    						{
    						    ?>
    						    <span style="display: none;" class="error email-required"><?php echo Text::_('ESHOP_QUOTE_EMAIL_REQUIRED'); ?></span>
    						    <?php
    						}
    						?>
    						<span style="display: none;" class="error email-invalid"><?php echo Text::_('ESHOP_QUOTE_EMAIL_INVALID'); ?></span>
    					</div>
    				</div>
				   	<?php 
				}
				
				if (EShopHelper::getConfigValue('quote_form_company_published', 1))
				{
				    ?>
					<div class="<?php echo $controlGroupClass; ?>">
    					<label class="<?php echo $controlLabelClass; ?>" for="company">
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_company_required', 0))
    						{
    						    ?>
    						    <span class="required">*</span>
    						    <?php
    						}
    						
    						echo Text::_('ESHOP_QUOTE_COMPANY'); ?>:
    					</label>
    					<div class="<?php echo $controlsClass; ?> docs-input-sizes">
    						<input type="text" class="input-xlarge form-control" name="company" id="company" value="" />
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_company_required', 0))
    						{
    						    ?>
    						    <span style="display: none;" class="error company-required"><?php echo Text::_('ESHOP_QUOTE_COMPANY_REQUIRED'); ?></span>
    						    <?php
    						}
    						?>
    					</div>
    				</div>			   
				   	<?php 
				}
				
				if (EShopHelper::getConfigValue('quote_form_telephone_published', 1))
				{
				    ?>
					<div class="<?php echo $controlGroupClass; ?>">
    					<label class="<?php echo $controlLabelClass; ?>" for="phone">
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_telephone_required', 0))
    						{
    						    ?>
    						    <span class="required">*</span>
    						    <?php
    						}
    						
    						echo Text::_('ESHOP_QUOTE_TELEPHONE'); ?>:
    					</label>
    					<div class="<?php echo $controlsClass; ?> docs-input-sizes">
    						<input type="text" class="input-xlarge form-control" name="telephone" id="telephone" value="" />
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_telephone_required', 0))
    						{
    						    ?>
    						    <span style="display: none;" class="error telephone-required"><?php echo Text::_('ESHOP_QUOTE_TELEPHONE_REQUIRED'); ?></span>
    						    <?php
    						}
    						?>
    					</div>
    				</div>			   
				   	<?php 
				}
		
				if (EShopHelper::getConfigValue('quote_form_address_published', 0))
				{
				    ?>
					<div class="<?php echo $controlGroupClass; ?>">
    					<label class="<?php echo $controlLabelClass; ?>" for="address">
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_address_required', 0))
    						{
    						    ?>
    						    <span class="required">*</span>
    						    <?php
    						}
    						
    						echo Text::_('ESHOP_QUOTE_ADDRESS'); ?>:
    					</label>
    					<div class="<?php echo $controlsClass; ?> docs-input-sizes">
    						<input type="text" class="input-xlarge form-control" name="address" id="address" value="" />
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_address_required', 0))
    						{
    						    ?>
    						    <span style="display: none;" class="error address-required"><?php echo Text::_('ESHOP_QUOTE_ADDRESS_REQUIRED'); ?></span>
    						    <?php
    						}
    						?>
    					</div>
    				</div>					   
				   	<?php 
				}
				
				if (EShopHelper::getConfigValue('quote_form_city_published', 0))
				{
				    ?>
					<div class="<?php echo $controlGroupClass; ?>">
    					<label class="<?php echo $controlLabelClass; ?>" for="city">
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_city_required', 0))
    						{
    						    ?>
    						    <span class="required">*</span>
    						    <?php
    						}
    						
    						echo Text::_('ESHOP_QUOTE_CITY'); ?>:
    					</label>
    					<div class="<?php echo $controlsClass; ?> docs-input-sizes">
    						<input type="text" class="input-xlarge form-control" name="city" id="city" value="" />
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_city_required', 0))
    						{
    						    ?>
    						    <span style="display: none;" class="error city-required"><?php echo Text::_('ESHOP_QUOTE_CITY_REQUIRED'); ?></span>
    						    <?php
    						}
    						?>
    					</div>
    				</div>					   
				   	<?php 
				}
				
				if (EShopHelper::getConfigValue('quote_form_postcode_published', 0))
				{
				    ?>
					<div class="<?php echo $controlGroupClass; ?>">
    					<label class="<?php echo $controlLabelClass; ?>" for="postcode">
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_postcode_required', 0))
    						{
    						    ?>
    						    <span class="required">*</span>
    						    <?php
    						}
    						
    						echo Text::_('ESHOP_QUOTE_POSTCODE'); ?>:
    					</label>
    					<div class="<?php echo $controlsClass; ?> docs-input-sizes">
    						<input type="text" class="input-xlarge form-control" name="postcode" id="postcode" value="" />
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_postcode_required', 0))
    						{
    						    ?>
    						    <span style="display: none;" class="error postcode-required"><?php echo Text::_('ESHOP_QUOTE_POSTCODE_REQUIRED'); ?></span>
    						    <?php
    						}
    						?>
    					</div>
    				</div>					   
				   	<?php 
				}
				
				if (EShopHelper::getConfigValue('quote_form_country_published', 0))
				{
				    ?>
					<div class="<?php echo $controlGroupClass; ?>">
    					<label class="<?php echo $controlLabelClass; ?>" for="country_id">
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_country_required', 0))
    						{
    						    ?>
    						    <span class="required">*</span>
    						    <?php
    						}
    						
    						echo Text::_('ESHOP_QUOTE_COUNTRY'); ?>:
    					</label>
    					<div class="<?php echo $controlsClass; ?>">
							<?php
							echo $this->lists['country_id'];
							
    						if (EShopHelper::getConfigValue('quote_form_country_required', 0))
    						{
    						    ?>
    						    <span style="display: none;" class="error country-required"><?php echo Text::_('ESHOP_QUOTE_COUNTRY_REQUIRED'); ?></span>
    						    <?php
    						}
    						?>
						</div>
    				</div>				   
				   	<?php 
				}
				
				if (EShopHelper::getConfigValue('quote_form_state_published', 0))
				{
				    ?>
					<div class="<?php echo $controlGroupClass; ?>">
    					<label class="<?php echo $controlLabelClass; ?>" for="zone_id">
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_state_required', 0))
    						{
    						    ?>
    						    <span class="required">*</span>
    						    <?php
    						}
    						
    						echo Text::_('ESHOP_QUOTE_STATE'); ?>:
    					</label>
    					<div class="<?php echo $controlsClass; ?>">
							<?php
							echo $this->lists['zone_id'];
							
							if (EShopHelper::getConfigValue('quote_form_state_required', 0))
							{
							    ?>
    						    <span style="display: none;" class="error state-required"><?php echo Text::_('ESHOP_QUOTE_STATE_REQUIRED'); ?></span>
    						    <?php
    						}
    						?>
						</div>
    				</div>						   
    			   	<?php 
    			}
				
				if (EShopHelper::getConfigValue('quote_form_message_published', 1))
				{
				    ?>
					<div class="<?php echo $controlGroupClass; ?>">
    					<label class="<?php echo $controlLabelClass; ?>" for="message">
    						<?php
    						if (EShopHelper::getConfigValue('quote_form_message_required', 1))
    						{
    						    ?>
    						    <span class="required">*</span>
    						    <?php
    						}
    						
    						echo Text::_('ESHOP_QUOTE_MESSAGE'); ?>:
    					</label>
    					<div class="<?php echo $controlsClass; ?> docs-input-sizes">
    						<textarea rows="5" cols="5" name="message" id="message" class="input-xlarge form-control"></textarea>
    						<?php
							if (EShopHelper::getConfigValue('quote_form_state_required', 1))
							{
							    ?>
							    <span style="display: none;" class="error message-required"><?php echo Text::_('ESHOP_QUOTE_MESSAGE_REQUIRED'); ?></span>
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
					<div class="<?php echo $controlGroupClass; ?>">
						<span class="newsletter-interest">
							<input type="checkbox" value="1" name="newsletter_interest" /><?php echo Text::_('ESHOP_NEWSLETTER_INTEREST'); ?>
						</span>
					</div>	
					<?php
				}
				
				if ($this->showCaptcha)
				{
				    if (in_array($this->captchaPlugin, ['recaptcha_invisible', 'recaptcha_v3']))
				    {
				        $style = ' style="display:none;"';
				    }
				    else
				    {
				        $style = '';
				    }
					?>
					<div class="<?php echo $controlGroupClass; ?>">
						<div class="<?php echo $controlLabelClass; ?>"<?php echo $style; ?>>
            				<?php echo Text::_('ESHOP_CAPTCHA'); ?>
							<span class="required">*</span>
						</div>
						<div class="<?php echo $controlsClass; ?>">
							<?php echo $this->captcha; ?>
						</div>
					</div>
					<?php
				}
				?>
				<input type="button" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullLeftClass; ?>" id="button-ask-quote" value="<?php echo Text::_('ESHOP_QUOTE_REQUEST_QUOTE'); ?>" />
				<span class="wait"></span>
			</form>
		</div>
	</div>
	<script type="text/javascript">
		//Function to update quote
		function updateQuote(key)
		{
			Eshop.jQuery(function($){
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					type: 'POST',
					url: siteUrl + 'index.php?option=com_eshop&task=quote.updates<?php echo EShopHelper::getAttachedLangLink(); ?>',
					data: $('.quote-info input[type=\'text\'], .quote-info input[type=\'hidden\']'),
					beforeSend: function() {
						$('#update-quote').attr('disabled', true);
						$('#update-quote').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
					},
					complete: function() {
						$('#update-quote').attr('disabled', false);
						$('.wait').remove();
					},
					success: function() {
						window.location.href = "<?php echo Route::_(EShopRoute::getViewRoute('quote')); ?>";
				  	}
				});
			})
		}
		<?php
		if (EShopHelper::getConfigValue('quote_form_state_published', 0))
		{
			?>
			Eshop.jQuery(function($){
				$('select[name=\'country_id\']').bind('change', function() {
					var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
					$.ajax({
						url: siteUrl + 'index.php?option=com_eshop&task=cart.getZones<?php echo EShopHelper::getAttachedLangLink(); ?>&country_id=' + this.value,
						dataType: 'json',
						beforeSend: function() {
							$('.wait').remove();
							$('select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
						},
						complete: function() {
							$('.wait').remove();
						},
						success: function(json) {
							if (json['postcode_required'] == '1')
							{
								$('#postcode-required').show();
							}
							else
							{
								$('#postcode-required').hide();
							}
							html = '<option value=""><?php echo Text::_('ESHOP_PLEASE_SELECT'); ?></option>';
							if (json['zones'] != '')
							{
								for (var i = 0; i < json['zones'].length; i++)
								{
									html += '<option value="' + json['zones'][i]['id'] + '"';
									html += '>' + json['zones'][i]['zone_name'] + '</option>';
								}
							}
							$('select[name=\'zone_id\']').html(html);
						}
					});
				});
			});
			<?php
		}
		?>
		Eshop.jQuery(function($) {
			//Ajax remove quote item
			$('.eshop-remove-item-quote').bind('click', function() {
				var aTag = $(this);
				var id = aTag.attr('id');
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					type :'POST',
					url: siteUrl + 'index.php?option=com_eshop&task=quote.remove&key=' +  id + '&redirect=1<?php echo EShopHelper::getAttachedLangLink(); ?>',
					beforeSend: function() {
						aTag.attr('disabled', true);
						aTag.after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
					},
					complete: function() {
						aTag.attr('disabled', false);
						$('.wait').remove();
					},
					success : function() {
						window.location.href = '<?php echo Route::_(EShopRoute::getViewRoute('quote')); ?>';
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			});
		});

		Eshop.jQuery(function($){
			$('#button-ask-quote').click(function(){
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					url: siteUrl + 'index.php?option=com_eshop&task=quote.processQuote<?php echo EShopHelper::getAttachedLangLink(); ?>',
					type: 'post',
					data: $('#quote-form-area input[type=\'text\'], #quote-form-area input[type=\'hidden\'], #quote-form-area textarea, #quote-form-area input[type=\'checkbox\']:checked, #quote-form-area select'),
					dataType: 'json',
					beforeSend: function() {
						$('#button-ask-quote').attr('disabled', true);
						$('#button-ask-quote').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
					},
					complete: function() {
						$('#button-ask-quote').attr('disabled', false);
						$('.wait').remove();
					},
					success: function(json) {
						$('.error').remove();
						if (json['return']) {
							window.location.href = json['return'];
						} else if (json['error']) {
							//name error
							if (json['error']['name']) {
								$('#quote-form-area input[name=\'name\']').after('<span class="error">' + json['error']['name'] + '</span>');
							}
							//email error
							if (json['error']['email']) {
								$('#quote-form-area input[name=\'email\']').after('<span class="error">' + json['error']['email'] + '</span>');
							}
							//company error
							if (json['error']['company']) {
								$('#quote-form-area input[name=\'company\']').after('<span class="error">' + json['error']['company'] + '</span>');
							}
							//telephone error
							if (json['error']['telephone']) {
								$('#quote-form-area input[name=\'telephone\']').after('<span class="error">' + json['error']['telephone'] + '</span>');
							}
							//address error
							if (json['error']['address']) {
								$('#quote-form-area input[name=\'address\']').after('<span class="error">' + json['error']['address'] + '</span>');
							}
							//city error
							if (json['error']['city']) {
								$('#quote-form-area input[name=\'city\']').after('<span class="error">' + json['error']['city'] + '</span>');
							}
							//postcode error
							if (json['error']['postcode']) {
								$('#quote-form-area input[name=\'postcode\']').after('<span class="error">' + json['error']['postcode'] + '</span>');
							}
							//country_id error
							if (json['error']['country_id']) {
								$('#quote-form-area select[name=\'country_id\']').after('<span class="error">' + json['error']['country_id'] + '</span>');
							}
							//zone_id error
							if (json['error']['zone_id']) {
								$('#quote-form-area select[name=\'zone_id\']').after('<span class="error">' + json['error']['zone_id'] + '</span>');
							}
							//message error
							if (json['error']['message']) {
								$('#message').after('<span class="error">' + json['error']['message'] + '</span>');
							}
							//captcha error
							if (json['error']['captcha']) {
								$('#dynamic_recaptcha_1').after('<span class="error">' + json['error']['captcha'] + '</span>');
							}
						} else {
							//redirect to complete page
							window.location.href = json['success'];
						}	  
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			});
		});
	</script>
	<?php
}