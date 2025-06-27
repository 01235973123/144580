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

?>
<div class="control-group" style="margin-left: 15px;">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('send_from', Text::_('ESHOP_CONFIG_SEND_FROM'), Text::_('ESHOP_CONFIG_SEND_FROM_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['send_from']; ?>
	</div>
</div>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_ORDER_NOTIFICATION'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('order_alert_mail', Text::_('ESHOP_CONFIG_ORDER_ALERT_MAIL_ENABLE'), Text::_('ESHOP_CONFIG_ORDER_ALERT_MAIL_ENABLE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['order_alert_mail']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('order_alert_mail_admin', Text::_('ESHOP_CONFIG_ORDER_ALERT_MAIL_ADMIN'), Text::_('ESHOP_CONFIG_ORDER_ALERT_MAIL_ADMIN_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['order_alert_mail_admin']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('order_cancel_mail_admin', Text::_('ESHOP_CONFIG_ORDER_CANCEL_MAIL_ADMIN'), Text::_('ESHOP_CONFIG_ORDER_CANCEL_MAIL_ADMIN_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['order_cancel_mail_admin']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('order_failure_mail_admin', Text::_('ESHOP_CONFIG_ORDER_FAILURE_MAIL_ADMIN'), Text::_('ESHOP_CONFIG_ORDER_FAILURE_MAIL_ADMIN_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['order_failure_mail_admin']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('order_alert_mail_manufacturer', Text::_('ESHOP_CONFIG_ORDER_ALERT_MAIL_MANUFACTURER'), Text::_('ESHOP_CONFIG_ORDER_ALERT_MAIL_MANUFACTURER_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['order_alert_mail_manufacturer']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('order_alert_mail_customer', Text::_('ESHOP_CONFIG_ORDER_ALERT_MAIL_CUSTOMER'), Text::_('ESHOP_CONFIG_ORDER_ALERT_MAIL_CUSTOMER_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['order_alert_mail_customer']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('order_reply_to_customer', Text::_('ESHOP_CONFIG_ORDER_REPLY_TO_CUSTOMER'), Text::_('ESHOP_CONFIG_ORDER_REPLY_TO_CUSTOMER_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['order_reply_to_customer']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('alert_emails', Text::_('ESHOP_CONFIG_ALERT_MAILS'), Text::_('ESHOP_CONFIG_ALERT_MAILS_HELP')); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="alert_emails" id="alert_emails" size="100" maxlength="250" value="<?php echo $this->config->alert_emails ?? ''; ?>" />
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_QUOTE_NOTIFICATION'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_alert_mail', Text::_('ESHOP_CONFIG_QUOTE_ALERT_MAIL_ENABLE'), Text::_('ESHOP_CONFIG_QUOTE_ALERT_MAIL_ENABLE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['quote_alert_mail']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_alert_mail_admin', Text::_('ESHOP_CONFIG_QUOTE_ALERT_MAIL_ADMIN'), Text::_('ESHOP_CONFIG_QUOTE_ALERT_MAIL_ADMIN_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['quote_alert_mail_admin']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_alert_mail_customer', Text::_('ESHOP_CONFIG_QUOTE_ALERT_MAIL_CUSTOMER'), Text::_('ESHOP_CONFIG_QUOTE_ALERT_MAIL_CUSTOMER_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['quote_alert_mail_customer']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('quote_alert_emails', Text::_('ESHOP_CONFIG_QUOTE_ALERT_MAILS'), Text::_('ESHOP_CONFIG_QUOTE_ALERT_MAILS_HELP')); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="quote_alert_emails" id="quote_alert_emails" size="100" maxlength="250" value="<?php echo $this->config->quote_alert_emails ?? ''; ?>" />
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_PRODUCT_NOTIFICATION'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('product_alert_ask_question', Text::_('ESHOP_CONFIG_PRODUCT_ALERT_ASK_QUESTION'), Text::_('ESHOP_CONFIG_PRODUCT_ALERT_ASK_QUESTION_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['product_alert_ask_question']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('product_alert_review', Text::_('ESHOP_CONFIG_PRODUCT_ALERT_REVIEW'), Text::_('ESHOP_CONFIG_PRODUCT_ALERT_REVIEW_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['product_alert_review']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('product_alert_emails', Text::_('ESHOP_CONFIG_PRODUCT_ALERT_MAILS'), Text::_('ESHOP_CONFIG_PRODUCT_ALERT_MAILS_HELP')); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="product_alert_emails" id="product_alert_emails" size="100" maxlength="250" value="<?php echo $this->config->product_alert_emails ?? ''; ?>" />
		</div>
	</div>
</fieldset>