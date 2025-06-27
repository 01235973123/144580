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

$name = explode(' ', $this->user->get('name'));
$firstName = $name[0] ?? '';
$lastName = $name[1] ?? '';
$bootstrapHelper        = $this->bootstrapHelper;
$controlGroupClass      = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass      = $bootstrapHelper->getClassMapping('control-label');
$controlsClass          = $bootstrapHelper->getClassMapping('controls');
$pullLeftClass          = $bootstrapHelper->getClassMapping('pull-left');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');
?>
<div class="page-header">
	<h1 class="page-title eshop-title"><?php echo Text::_('ESHOP_EDIT_ACCOUNT'); ?></h1>
</div>
<form id="adminForm" action="<?php echo Route::_('index.php?option=com_eshop&task=customer.processUser'); ?>" class="form-horizontal" method="post">
	<div id="process-user">
		<div class="<?php echo $controlGroupClass; ?>">
			<label for="firstname" class="<?php echo $controlLabelClass; ?>"><span class="required">*</span><?php echo Text::_('ESHOP_FIRST_NAME'); ?></label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-xlarge form-control" name="firstname" id="firstname" value="<?php echo isset($this->userInfo->firstname) ? EShopHelper::escape($this->userInfo->firstname) : EShopHelper::escape($firstName); ?>">
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label for="lastname" class="<?php echo $controlLabelClass; ?>"><span class="required">*</span><?php echo Text::_('ESHOP_LAST_NAME'); ?></label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-xlarge form-control" name="lastname" id="lastname" value="<?php echo isset($this->userInfo->lastname) ? EShopHelper::escape($this->userInfo->lastname) : EShopHelper::escape($lastName); ?>">
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label for="username" class="<?php echo $controlLabelClass; ?>"><span class="required">*</span><?php echo Text::_('ESHOP_USERNAME'); ?></label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-xlarge form-control" name="username" id="username" value="<?php echo EShopHelper::escape($this->userInfo->username); ?>">
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label for="password1" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_PASSWORD'); ?></label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="password" class="input-xlarge form-control" name="password1" id="password1" value="">
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label for="password2" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_CONFIRM_PASSWORD'); ?></label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="password" class="input-xlarge form-control" name="password2" id="password2" value="">
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label for="email" class="<?php echo $controlLabelClass; ?>"><span class="required">*</span><?php echo Text::_('ESHOP_EMAIL'); ?></label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-xlarge form-control" name="email" id="email" value="<?php echo isset($this->userInfo->email) ? EShopHelper::escape($this->userInfo->email) : EShopHelper::escape($this->user->get('email')); ?>">
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label for="telephone" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_TELEPHONE'); ?></label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-xlarge form-control" name="telephone" id="telephone" value="<?php echo isset($this->userInfo->telephone) ? EShopHelper::escape($this->userInfo->telephone) : ''; ?>">
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label for="fax" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_FAX'); ?></label>
			<div class="<?php echo $controlsClass; ?> docs-input-sizes">
				<input type="text" class="input-xlarge form-control" name="fax" id="fax" value="<?php echo isset($this->userInfo->fax) ? EShopHelper::escape($this->userInfo->fax) : ''; ?>">
			</div>
		</div>
		<?php
		if (isset($this->customergroup_id))
		{
			?>
			<div class="<?php echo $controlGroupClass; ?>">
				<label for="fax" class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('ESHOP_CUSTOMER_GROUP'); ?></label>
				<div class="<?php echo $controlsClass; ?> docs-input-sizes">
					<?php echo $this->customergroup_id; ?>
				</div>
			</div>
			<?php
		}
		elseif (isset($this->default_customergroup_id))
		{
			?>
			<input type="hidden" name="customergroup_id" value="<?php echo $this->default_customergroup_id; ?>" />
			<?php
		}
		?>
	</div>
	<div class="no_margin_left <?php echo $pullLeftClass; ?>">
		<input type="button" value="<?php echo Text::_('ESHOP_BACK'); ?>" id="button-back-user-infor" class="<?php echo $btnBtnPrimaryClass; ?>">
		<input type="button" value="<?php echo Text::_('ESHOP_SAVE'); ?>" id="button-user-infor" class="<?php echo $btnBtnPrimaryClass; ?>">
		<input type="hidden" name="id" value="<?php echo $this->userInfo->id ?? ''; ?>">
	</div>
</form>
<script type="text/javascript">
	Eshop.jQuery(function($){
		$(document).ready(function(){
			$('#button-back-user-infor').click(function(){
				var url = '<?php echo Route::_(EShopRoute::getViewRoute('customer')); ?>';
				$(location).attr('href',url);
			});
		})
	
		//process user
		$('#button-user-infor').on('click', function() {
			var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
			$.ajax({
				url: siteUrl + 'index.php?option=com_eshop&task=customer.processUser<?php echo EShopHelper::getAttachedLangLink(); ?>',
				type: 'post',
				data: $("#adminForm").serialize(),
				dataType: 'json',
				success: function(json) {
						$('.warning, .error').remove();
						if (json['return']) {
							window.location.href = json['return'];
						} else if (json['error']) {
						//Firstname error
						if (json['error']['firstname']) {
							$('#process-user input[name=\'firstname\']').after('<span class="error">' + json['error']['firstname'] + '</span>');
						}
						//Lastname error
						if (json['error']['lastname']) {
							$('#process-user input[name=\'lastname\']').after('<span class="error">' + json['error']['lastname'] + '</span>');
						}
						//Username error
						if (json['error']['username']) {
							$('#process-user input[name=\'username\']').after('<span class="error">' + json['error']['username'] + '</span>');
						}
						if (json['error']['username_existed']) {
							$('#process-user input[name=\'username\']').after('<span class="error">' + json['error']['username_existed'] + '</span>');
						}
						//Password error
						if (json['error']['password']) {
							$('#process-user input[name=\'password1\']').after('<span class="error">' + json['error']['password'] + '</span>');
						}
						//Confirm password error
						if (json['error']['confirm']) {
							$('#process-user input[name=\'password2\']').after('<span class="error">' + json['error']['confirm'] + '</span>');
						}
						//Email validate
						if (json['error']['email']) {
							$('#process-user input[name=\'email\']').after('<span class="error">' + json['error']['email'] + '</span>');
						}
						//Email error
						if (json['error']['email_existed']) {
							$('#process-user input[name=\'email\']').after('<span class="error">' + json['error']['email_existed'] + '</span>');
						}
							
					} else {
						$('.error').remove();
						$('.warning, .error').remove();
						
					}	  
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});	
		});

		
	});
</script>