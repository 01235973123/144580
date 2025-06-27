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
use Joomla\CMS\Uri\Uri;

$bootstrapHelper        = $this->bootstrapHelper;
$controlGroupClass      = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass      = $bootstrapHelper->getClassMapping('control-label');
$controlsClass          = $bootstrapHelper->getClassMapping('controls');
$pullRightClass         = $bootstrapHelper->getClassMapping('pull-right');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');

$Itemid = Factory::getApplication()->input->getInt('Itemid', 0);

if (!$Itemid)
{
    $Itemid = EShopRoute::getDefaultItemId();
}

if (isset($this->shipping_methods))
{
	?>
	<div>
		<p><?php echo Text::_('ESHOP_SHIPPING_METHOD_TITLE'); ?></p>
		<?php
		$firstShippingOption = true;
		foreach ($this->shipping_methods as $shippingMethod)
		{
			?>
			<div>
				<strong><?php echo $shippingMethod['title']; ?></strong><br />
				<?php
				foreach ($shippingMethod['quote'] as $quote)
				{
					$checkedStr = ' ';
					if ($quote['name'] == $this->shipping_method)
					{
						$checkedStr = ' checked = "checked" ';
					}
					else 
					{
						if ($firstShippingOption)
						{
							$checkedStr = ' checked = "checked" ';
						}
					}
					$firstShippingOption = false;
					?>
					<label class="radio">
						<input type="radio" class="form-check-input" value="<?php echo $quote['name']; ?>" name="shipping_method" <?php echo $checkedStr; ?>/>
						<?php echo $quote['desc'] . ($quote['text'] != '' ? ' (' . $quote['text'] . ')' : ''); ?>
					</label>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}
else 
{
	?>
	<div class="no-shipping-method"><?php echo Text::_('ESHOP_NO_SHIPPING_METHOD_AVAILABLE'); ?></div>
	<?php
}
if (EShopHelper::getConfigValue('delivery_date'))
{
	?>
	<script language="JavaScript" type="text/javascript">
		<?php
		if (version_compare(JVERSION, '3.6.9', 'ge'))
		{
			?>
			elements = document.querySelectorAll(".field-calendar");
			for (i = 0; i < elements.length; i++) {
				JoomlaCalendar.init(elements[i]);
			}
			<?php
		}
		else 
		{
			?>
			Calendar.setup({
				// Id of the input field
				inputField: "delivery_date",
				// Format of the input field
				ifFormat: "%Y-%m-%d",
				// Trigger for the calendar (button ID)
				button: "delivery_date_img",
				// Alignment (defaults to "Bl")
				align: "Tl",
				singleClick: true,
				firstDay: 0
			});
			<?php
		}
		?>
	</script>
	<br />
	<div class="<?php echo $controlGroupClass; ?>">
		<label for="delivery_date" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_DELIVERY_DATE'); ?></label>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo HTMLHelper::_('calendar', $this->delivery_date ?: '', 'delivery_date', 'delivery_date', '%Y-%m-%d'); ?>
		</div>
	</div>
	<?php
}

$displayComment = EShopHelper::getConfigValue('display_comment', '45');

if ( $displayComment== '4' || $displayComment == '45')
{
    ?>
    <div class="<?php echo $controlGroupClass; ?>">
    	<label for="comment" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_COMMENT_ORDER'); ?></label>
    	<div class="<?php echo $controlsClass; ?>">
    		<textarea rows="8" id="comment" class="input-xlarge form-control" name="comment"><?php echo $this->comment; ?></textarea>
    	</div>
    </div>
    <?php
}
?>
<div class="no_margin_left">
	<input type="button" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullRightClass; ?>" id="button-shipping-method" value="<?php echo Text::_('ESHOP_CONTINUE'); ?>" />
</div>
<script type="text/javascript">
	//Shipping Method
	Eshop.jQuery(function($){
		$('#button-shipping-method').click(function(){
			var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
			$.ajax({
				url: siteUrl + 'index.php?option=com_eshop&task=checkout.processShippingMethod<?php echo EShopHelper::getAttachedLangLink(); ?>',
				type: 'post',
				data: $('#shipping-method input[type=\'radio\']:checked, #shipping-method textarea, #shipping-method input[type=\'text\']'),
				dataType: 'json',
				beforeSend: function() {
					$('#button-shipping-method').attr('disabled', true);
					$('#button-shipping-method').after('<span class="wait">&nbsp;<img src="<?php echo Uri::root(true) ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
				},	
				complete: function() {
					$('#button-shipping-method').attr('disabled', false);
					$('.wait').remove();
				},			
				success: function(json) {
					$('.warning, .error').remove();
					if (json['return']) {
						window.location.href = json['return'];
					} else if (json['error']) {
						if (json['error']['warning']) {
							$('#shipping-method .checkout-content').prepend('<div class="warning" style="display: none;">' + json['error']['warning'] + '</div>');
							$('.warning').fadeIn('slow');
						}
					} else if (json['total'] > 0) {
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=payment_method&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>&Itemid=<?php echo $Itemid; ?>',
							dataType: 'html',
							success: function(html) {
								$('#payment-method .checkout-content').html(html);
								$('#shipping-method .checkout-content').slideUp('slow');
								$('#payment-method .checkout-content').slideDown('slow');
								$('#shipping-method .checkout-heading a').remove();
								$('#payment-method .checkout-heading a').remove();
								$('#shipping-method .checkout-heading').append('<a><?php echo Text::_('ESHOP_EDIT'); ?></a>');
								$('html, body').animate({scrollTop: $('#eshop-main-container').offset().top - 10 }, 'slow');
							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
					} else {
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=confirm&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&Itemid=<?php echo $Itemid; ?>',
							dataType: 'html',
							success: function(html) {
								$('#confirm .checkout-content').html(html);
								$('#shipping-method .checkout-content').slideUp('slow');
								$('#confirm .checkout-content').slideDown('slow');
								$('#shipping-method .checkout-heading a').remove();
								$('#payment-method .checkout-heading a').remove();
								$('#shipping-method .checkout-heading').append('<a><?php echo Text::_('ESHOP_EDIT'); ?></a>');
								$('html, body').animate({scrollTop: $('#eshop-main-container').offset().top - 10 }, 'slow');
							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});
	})
</script>