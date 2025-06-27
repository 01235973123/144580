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
<h1 id="make-notify-title"><?php echo Text::_('ESHOP_PRODUCT_NOTIFY_TITLE'); ?></h1>
<div id="make-notify-area">
	<div class="make-notify-intro"><?php echo sprintf(Text::_('ESHOP_PRODUCT_NOTIFY_DESC'), $this->item->product_name); ?></div>
	<form method="post" name="adminForm" id="adminForm" action="index.php" class="form form-horizontal">
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="email"><span class="required">*</span><?php echo Text::_('ESHOP_EMAIL'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-large" name="notify_email" id="notify_email" value="" />
				<span style="display: none;" class="error email-required"><?php echo Text::_('ESHOP_EMAIL_REQUIRED'); ?></span>
				<span style="display: none;" class="error email-invalid"><?php echo Text::_('ESHOP_EMAIL_INVALID'); ?></span>
			</div>
		</div>			
		<input type="hidden" name="product_id" id="product_id" value="<?php echo $input->getInt('id'); ?>" />
		<input type="button" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullLeftClass; ?>" id="button-make-notify" value="<?php echo Text::_('ESHOP_SUBMIT'); ?>" />
		<span class="wait"></span>
	</form>
</div>
<script type="text/javascript">
	function isValidEmail(emailAddress)
	{
	    var pattern = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
	    return pattern.test(emailAddress);
	}
	Eshop.jQuery(function($){
		$('#button-make-notify').click(function(){
			var contactEmail = $('#notify_email').val();
			var validated = true;
			if(contactEmail == '')
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
			
			if (validated)
			{
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					type :'post',
					url: siteUrl + 'index.php?option=com_eshop&task=product.processNotify<?php echo EShopHelper::getAttachedLangLink(); ?>',
					data: $('#make-notify-area input[type=\'text\'], #make-notify-area input[type=\'hidden\']'),
					beforeSend: function() {
						$('.wait').html('<img src="<?php echo Uri::root(true); ?>/media/com_eshop/assets/images/loading.gif" alt="" />');
					},
					success : function(html) {
						$('#make-notify-area').html('<div>'+html+'</div>');
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		});
	});
</script>
