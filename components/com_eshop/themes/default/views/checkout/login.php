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
$span6Class             = $bootstrapHelper->getClassMapping('span6');
$controlGroupClass      = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass      = $bootstrapHelper->getClassMapping('control-label');
$controlsClass          = $bootstrapHelper->getClassMapping('controls');
$pullLeftClass          = $bootstrapHelper->getClassMapping('pull-left');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');
$usersMenuItemStr       = EShopHelper::getUsersMenuItemStr();

$rootUri = Uri::root(true);
$Itemid  = Factory::getApplication()->input->getInt('Itemid', 0);

if (!$Itemid)
{
    $Itemid = EShopRoute::getDefaultItemId();
}
?>
<div class="<?php echo $span6Class; ?> no_margin_left">
	<?php
	if (EShopHelper::getCheckoutType() != 'guest_only')
	{
		?>
		<h4><?php echo Text::_('ESHOP_CHECKOUT_NEW_CUSTOMER'); ?></h4>
		<p><?php echo Text::_('ESHOP_CHECKOUT_NEW_CUSTOMER_INTRO'); ?></p>
		<label class="radio"><input type="radio" class="form-check-input" value="register" name="account" checked="checked" /> <?php echo Text::_('ESHOP_REGISTER_ACCOUNT'); ?></label>
		<?php
	}
	if (EShopHelper::getCheckoutType() != 'registered_only')
	{
		?>
		<label class="radio"><input type="radio" class="form-check-input" value="guest" name="account" <?php if (EShopHelper::getCheckoutType() == 'guest_only') echo 'checked="checked"'; ?> /> <?php echo Text::_('ESHOP_GUEST_CHECKOUT'); ?></label>
		<?php
	}
	?>
	<input type="button" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullLeftClass; ?>" id="button-account" value="<?php echo Text::_('ESHOP_CONTINUE'); ?>" />
</div>
<?php
if (EShopHelper::getCheckoutType() != 'guest_only')
{
	?>
	<div id="login" class="<?php echo $span6Class; ?>">
		<h4><?php echo Text::_('ESHOP_REGISTERED_CUSTOMER'); ?></h4>
		<p><?php echo Text::_('ESHOP_REGISTERED_CUSTOMER_INTRO'); ?></p>
		<fieldset>
			<div class="<?php echo $controlGroupClass; ?>">
				<label for="username" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_USERNAME'); ?></label>
				<div class="<?php echo $controlsClass; ?>">
					<input type="text" placeholder="<?php echo Text::_('ESHOP_USERNAME_INTRO'); ?>" id="username" name="username" class="input-xlarge form-control" />
				</div>
			</div>
			<div class="<?php echo $controlGroupClass; ?>">
				<label for="password" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_PASSWORD'); ?></label>
				<div class="<?php echo $controlsClass; ?>">
					<input type="password" placeholder="<?php echo Text::_('ESHOP_PASSWORD_INTRO'); ?>" id="password" name="password" class="input-xlarge form-control" />
				</div>
			</div>
			<label class="checkbox" for="remember">
				<input type="checkbox" alt="<?php echo Text::_('ESHOP_REMEMBER_ME'); ?>" value="yes" class="form-check-input" name="remember" id="remember" /><?php echo Text::_('ESHOP_REMEMBER_ME'); ?>
			</label>
			<ul>
				<li>
					<a href="<?php echo Route::_('index.php?option=com_users&view=reset' . $usersMenuItemStr); ?>">
					<?php echo Text::_('ESHOP_FORGOT_YOUR_PASSWORD'); ?></a>
				</li>
				<li>
					<a href="<?php echo Route::_('index.php?option=com_users&view=remind' . $usersMenuItemStr); ?>">
					<?php echo Text::_('ESHOP_FORGOT_YOUR_USERNAME'); ?></a>
				</li>
			</ul>
			<input type="button" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullLeftClass; ?>" id="button-login" value="<?php echo Text::_('ESHOP_CONTINUE'); ?>" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</fieldset>
	</div>
	<?php
}
?>
<script type="text/javascript">
	//Script to change Payment Address heading when changing checkout options between Register and Guest
	Eshop.jQuery(document).ready(function($){
		$('#checkout-options .checkout-content input[name=\'account\']').click(function(){
			if ($(this).val() == 'register') {
				$('#payment-address .checkout-heading').html('<?php echo Text::_('ESHOP_CHECKOUT_STEP_2_REGISTER'); ?>');
			} else {
				$('#payment-address .checkout-heading').html('<?php echo Text::_('ESHOP_CHECKOUT_STEP_2_GUEST'); ?>');
			}
		});

		//Checkout options - will run if user choose Register Account or Guest Checkout
		$('#button-account').click(function(){
			var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
			$.ajax({
				url: siteUrl + 'index.php?option=com_eshop&view=checkout&layout=' + $('input[name=\'account\']:checked').attr('value') + '&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&Itemid=<?php echo $Itemid; ?>',
				dataType: 'html',
				beforeSend: function() {
					$('#button-account').attr('disabled', true);
					$('#button-account').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
				},
				complete: function() {
					$('#button-account').attr('disabled', false);
					$('.wait').remove();
				},
				success: function(html) {
					$('#payment-address .checkout-content').html(html);
					$('#checkout-options .checkout-content').slideUp('slow');
					$('#payment-address .checkout-content').slideDown('slow');
					$('.checkout-heading a').remove();
					$('#checkout-options .checkout-heading').append('<a><?php echo Text::_('ESHOP_EDIT'); ?></a>');
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});

		
		//Login - will run if user choose login with an existed account
		$('#button-login').click(function(){
			var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
			$.ajax({
				url: siteUrl + 'index.php?option=com_eshop&task=checkout.login<?php echo EShopHelper::getAttachedLangLink(); ?>',
				type: 'post',
				data: $('#checkout-options #login :input'),
				dataType: 'json',
				beforeSend: function() {
					$('#button-login').attr('disabled', true);
					$('#button-login').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
				},	
				complete: function() {
					$('#button-login').attr('disabled', false);
					$('.wait').remove();
				},				
				success: function(json) {
					$('.warning, .error').remove();
					if (json['return']) {
						window.location.href = json['return'];
					} else if (json['error']) {
						$('#checkout-options .checkout-content').prepend('<div class="warning" style="display: none;">' + json['error']['warning'] + '</div>');
						$('.warning').fadeIn('slow');
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});
	});
</script>