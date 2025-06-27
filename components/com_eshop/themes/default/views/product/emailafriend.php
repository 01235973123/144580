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
<h1 id="email-a-friend-title"><?php echo Text::_('ESHOP_EMAIL_A_FRIEND'); ?></h1>
<div class="email-a-friend-intro"><?php echo sprintf(Text::_('ESHOP_EMAIL_A_FRIEND_INTRO'), $this->item->product_name); ?></div>
<br />
<div id="email-a-friend-area">
	<form method="post" name="adminForm" id="adminForm" action="index.php" class="form form-horizontal">
		<div><b><?php echo Text::_('ESHOP_SEND_EMAIL_SENDER'); ?></b></div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="sender_name"><span class="required">*</span><?php echo Text::_('ESHOP_SENDER_NAME'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-large form-control" name="sender_name" id="sender_name" value="" />
				<span style="display: none;" class="error sender-name-required"><?php echo Text::_('ESHOP_SENDER_NAME_REQUIRED'); ?></span>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="sender_email"><span class="required">*</span><?php echo Text::_('ESHOP_SENDER_EMAIL'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-large form-control" name="sender_email" id="sender_email" value="" />
				<span style="display: none;" class="error sender-email-required"><?php echo Text::_('ESHOP_SENDER_EMAIL_REQUIRED'); ?></span>
				<span style="display: none;" class="error sender-email-invalid"><?php echo Text::_('ESHOP_SENDER_EMAIL_INVALID'); ?></span>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="message"><span class="required">*</span><?php echo Text::_('ESHOP_SENDER_MESSAGE'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<textarea rows="5" cols="5" name="message" id="message" class="input-large form-control"></textarea>
				<span style="display: none;" class="error message-required"><?php echo Text::_('ESHOP_SENDER_MESSAGE_REQUIRED'); ?></span>
			</div>
		</div>
		<div><b><?php echo Text::_('ESHOP_SEND_EMAIL_INVITEE'); ?></b></div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="invitee_name"><span class="required">*</span><?php echo Text::_('ESHOP_INVITEE_NAME'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-large form-control" name="invitee_name" id="invitee_name" value="" />
				<span style="display: none;" class="error invitee-name-required"><?php echo Text::_('ESHOP_INVITEE_NAME_REQUIRED'); ?></span>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="sender_email"><span class="required">*</span><?php echo Text::_('ESHOP_INVITEE_EMAIL'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-large form-control" name="invitee_email" id="invitee_email" value="" />
				<span style="display: none;" class="error invitee-email-required"><?php echo Text::_('ESHOP_INVITEE_EMAIL_REQUIRED'); ?></span>
				<span style="display: none;" class="error invitee-email-invalid"><?php echo Text::_('ESHOP_INVITEE_EMAIL_INVALID'); ?></span>
			</div>
		</div>
		<input type="hidden" name="product_id" id="product_id" value="<?php echo $input->getInt('id'); ?>" />
		<input type="button" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullLeftClass; ?>" id="button-email-a-friend" value="<?php echo Text::_('ESHOP_SUBMIT'); ?>" />
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
		$('#button-email-a-friend').click(function(){
			$('#success').hide();
			var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			var senderName = $('#sender_name').val();
			var senderEmail = $('#sender_email').val();
			var message = $('#message').val();
			var inviteeName = $('#invitee_name').val();
			var inviteeEmail = $('#invitee_email').val();
			var validated = true;
			if(senderName == '')
			{
				validated = false;
				$('.sender-name-required').show();
			}
			else
			{
				$('.sender-name-required').hide();
			}
			
			if(senderEmail == '')
			{
				validated = false;
				$('.sender-email-required').show();
			}
			else if (!isValidEmail(senderEmail))
			{
				validated = false;
				$('.sender-email-required').hide();
				$('.sender-email-invalid').show();
			}
			else
			{
				$('.sender-email-required').hide();
				$('.sender-email-invalid').hide();
			}
			
			if (message == '')
			{
				validated = false;
				$('.message-required').show();
			}
			else
			{
				$('.message-required').hide();
			}

			if(inviteeName == '')
			{
				validated = false;
				$('.invitee-name-required').show();
			}
			else
			{
				$('.invitee-name-required').hide();
			}

			if(inviteeEmail == '')
			{
				validated = false;
				$('.invitee-email-required').show();
			}
			else if (!isValidEmail(senderEmail))
			{
				validated = false;
				$('.invitee-email-required').hide();
				$('.invitee-email-invalid').show();
			}
			else
			{
				$('.invitee-email-required').hide();
				$('.invitee-email-invalid').hide();
			}
	
			if (validated)
			{
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					type :'POST',
					url: siteUrl + 'index.php?option=com_eshop&task=product.processEmailAFriend<?php echo EShopHelper::getAttachedLangLink(); ?>',
					data: $('#email-a-friend-area input[type=\'text\'], #email-a-friend-area input[type=\'hidden\'], #email-a-friend-area input[type=\'radio\']:checked, #email-a-friend-area input[type=\'checkbox\']:checked, #email-a-friend-area select, #email-a-friend-area textarea'),
					beforeSend: function() {
						$('.wait').html('<img src="<?php echo Uri::root(true); ?>/media/com_eshop/assets/images/loading.gif" alt="" />');
					},
					success : function(html) {
						$('#email-a-friend-area').html('<div class="success"><?php echo Text::_('ESHOP_EMAIL_A_FRIEND_SUCCESSFULLY')?></div>');
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		});
	});
</script>