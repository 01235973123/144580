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
use Joomla\CMS\Uri\Uri;

$input  = Factory::getApplication()->input;
$bootstrapHelper        = $this->bootstrapHelper;
$controlGroupClass      = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass      = $bootstrapHelper->getClassMapping('control-label');
$controlsClass          = $bootstrapHelper->getClassMapping('controls');
$pullLeftClass          = $bootstrapHelper->getClassMapping('pull-left');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');
?>
<h1 id="price-match-title"><?php echo Text::_('ESHOP_PRICE_MATCH_TITLE'); ?></h1>
<div class="price-match-intro"><?php echo sprintf(Text::_('ESHOP_PRICE_MATCH_INTRO'), $this->item->product_name); ?></div>
<div id="price-match-area">
	<form method="post" name="priceMatchForm" id="priceMatchForm" action="index.php" class="form form-horizontal">
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="name"><span class="required">*</span><?php echo Text::_('ESHOP_NAME'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-large form-control" name="name" id="name" value="" />
				<span style="display: none;" class="error name-required"><?php echo Text::_('ESHOP_NAME_REQUIRED'); ?></span>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="email"><span class="required">*</span><?php echo Text::_('ESHOP_EMAIL'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-large form-control" name="email" id="email" value="" />
				<span style="display: none;" class="error email-required"><?php echo Text::_('ESHOP_EMAIL_REQUIRED'); ?></span>
				<span style="display: none;" class="error email-invalid"><?php echo Text::_('ESHOP_EMAIL_INVALID'); ?></span>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="product_sku"><?php echo Text::_('ESHOP_PRODUCT_SKU'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" readonly class="input-large form-control" name="product_sku" id="product_sku" value="<?php echo $input->getString('product_sku'); ?>" />
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="product_price"><?php echo Text::_('ESHOP_PRODUCT_PRICE'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" readonly class="input-large form-control" name="product_price" id="product_price" value="<?php echo $input->getString('product_price'); ?>" />
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="price_to_match"><span class="required">*</span><?php echo sprintf(Text::_('ESHOP_PRICE_TO_MATCH'), $this->currency->getCurrencyCode()); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-large form-control" name="price_to_match" id="price_to_match" value="" />
				<span style="display: none;" class="error price-to-match-required"><?php echo Text::_('ESHOP_PRICE_TO_MATCH_REQUIRED'); ?></span>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="price_to_match_url"><span class="required">*</span><?php echo Text::_('ESHOP_PRICE_TO_MATCH_URL'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-large form-control" name="price_to_match_url" id="price_to_match_url" value="" />
				<span style="display: none;" class="error price-to-match-url-required"><?php echo Text::_('ESHOP_PRICE_TO_MATCH_URL_REQUIRED'); ?></span>
			</div>
		</div>
		<input type="hidden" name="product_id" id="product_id" value="<?php echo $input->getInt('id'); ?>" />
		<input type="button" class="<?php echo $btnBtnPrimaryClass; ?>" id="button-price-match" value="<?php echo Text::_('ESHOP_SUBMIT'); ?>" />
		<span class="wait"></span>
	</form>
</div>
<div class="price-match-footer"><?php echo Text::_('ESHOP_PRICE_MATCH_FOOTER'); ?></div>
<script type="text/javascript">
	function isValidEmail(emailAddress)
	{
	    var pattern = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
	    return pattern.test(emailAddress);
	}
	Eshop.jQuery(function($){
		$('#button-price-match').click(function(){
			$('#success').hide();
			var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			var form = document.priceMatchForm;
			var contactName = priceMatchForm.name.value;
			var contactEmail = priceMatchForm.email.value;
			var priceToMatch = priceMatchForm.price_to_match.value;
			var priceToMatchUrl = priceMatchForm.price_to_match_url.value;

			var validated = true;
			
			if (contactName == '')
			{
				validated = false;
				$('.name-required').show();
			}
			else
			{
				$('.name-required').hide();
			}
			
			if (contactEmail == '')
			{
				validated = false;
				$('.email-required').show();
			}
			else if (!isValidEmail(contactEmail))
			{
				validated = false;
				$('.email-required').hide();
				$('.email-invalid').show();
			}
			else
			{
				$('.email-required').hide();
				$('.email-invalid').hide();
			}

			if (priceToMatch == '')
			{
				validated = false;
				$('.price-to-match-required').show();
			}
			else
			{
				$('.price-to-match-required').hide();
			}

			if (priceToMatchUrl == '')
			{
				validated = false;
				$('.price-to-match-url-required').show();
			}
			else
			{
				$('.price-to-match-url-required').hide();
			}
	
			if (validated)
			{
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					type :'POST',
					url: siteUrl + 'index.php?option=com_eshop&task=product.processPriceMatch<?php echo EShopHelper::getAttachedLangLink(); ?>',
					data: $('#price-match-area input[type=\'text\'], #price-match-area input[type=\'hidden\'], #price-match-area input[type=\'radio\']:checked, #price-match-area input[type=\'checkbox\']:checked, #price-match-area select, #price-match-area textarea'),
					beforeSend: function() {
						$('.wait').html('<img src="<?php echo Uri::root(true); ?>/media/com_eshop/assets/images/loading.gif" alt="" />');
					},
					success : function(html) {
						$('#price-match-area').html('<div class="success"><?php echo Text::_('ESHOP_PRICE_MATCH_SUCCESSFULLY')?></div>');
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		});
	});
</script>