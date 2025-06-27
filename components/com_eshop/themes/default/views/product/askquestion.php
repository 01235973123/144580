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
<h1 id="ask-question-title"><?php echo Text::_('ESHOP_ASK_QUESTION'); ?></h1>
<div class="ask-question-intro"><?php echo sprintf(Text::_('ESHOP_ASK_QUESTION_INTRO'), $this->item->product_name); ?></div>
<div id="ask-question-area">
	<form method="post" name="askQuestionForm" id="askQuestionForm" action="index.php" class="form form-horizontal">
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
			<label class="<?php echo $controlLabelClass; ?>" for="company"><?php echo Text::_('ESHOP_COMPANY'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-large form-control" name="company" id="company" value="" />
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="phone"><?php echo Text::_('ESHOP_PHONE'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-large form-control" name="phone" id="phone" value="" />
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="message"><span class="required">*</span><?php echo Text::_('ESHOP_MESSAGE'); ?>:</label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<textarea rows="5" cols="5" name="message" id="message" class="input-large form-control"></textarea>
				<span style="display: none;" class="error message-required"><?php echo Text::_('ESHOP_MESSAGE_REQUIRED'); ?></span>
			</div>
		</div>
		<input type="hidden" name="product_id" id="product_id" value="<?php echo $input->getInt('id'); ?>" />
		<input type="button" class="<?php echo $btnBtnPrimaryClass; ?>" id="button-ask-question" value="<?php echo Text::_('ESHOP_SUBMIT'); ?>" />
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
		$('#button-ask-question').click(function(){
			$('#success').hide();
			var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			var form = document.askQuestionForm;
			var contactName = askQuestionForm.name.value;
			var contactEmail = askQuestionForm.email.value;
			var contactMessage = askQuestionForm.message.value;

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
			
			if (contactMessage == '')
			{
				validated = false;
				$('.message-required').show();
			}
			else
			{
				$('.message-required').hide();
			}
	
			if (validated)
			{
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					type :'POST',
					url: siteUrl + 'index.php?option=com_eshop&task=product.processAskQuestion<?php echo EShopHelper::getAttachedLangLink(); ?>',
					data: $('#ask-question-area input[type=\'text\'], #ask-question-area input[type=\'hidden\'], #ask-question-area input[type=\'radio\']:checked, #ask-question-area input[type=\'checkbox\']:checked, #ask-question-area select, #ask-question-area textarea'),
					beforeSend: function() {
						$('.wait').html('<img src="<?php echo Uri::root(true); ?>/media/com_eshop/assets/images/loading.gif" alt="" />');
					},
					success : function(html) {
						$('#ask-question-area').html('<div class="success"><?php echo Text::_('ESHOP_ASK_QUESTION_SUCCESSFULLY')?></div>');
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		});
	});
</script>