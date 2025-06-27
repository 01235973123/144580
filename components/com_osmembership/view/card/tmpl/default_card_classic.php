<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/* @var OSMembershipHelperBootstrap $bootstrapHelper */
$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
?>
<div class="<?php echo $controlGroupClass; ?> payment_information" id="tr_card_number">
	<div class="<?php echo $controlLabelClass; ?>">
		<label><?php echo  Text::_('AUTH_CARD_NUMBER'); ?><span class="required">*</span></label>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<input type="text" name="x_card_num" class="form-control validate[required,creditCard]" value="<?php echo $this->escape($this->input->post->getAlnum('x_card_num'));?>" size="20" />
	</div>
</div>
<div class="<?php echo $controlGroupClass; ?> payment_information" id="tr_exp_date">
	<div class="<?php echo $controlLabelClass; ?>">
		<label>
			<?php echo Text::_('AUTH_CARD_EXPIRY_DATE'); ?><span class="required">*</span>
		</label>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<?php echo $this->lists['exp_month'] . '  /  ' . $this->lists['exp_year'] ; ?>
	</div>
</div>
<div class="<?php echo $controlGroupClass; ?> payment_information" id="tr_cvv_code">
	<div class="<?php echo $controlLabelClass; ?>">
		<label>
			<?php echo Text::_('AUTH_CVV_CODE'); ?><span class="required">*</span>
		</label>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<input type="text" name="x_card_code" class="validate[required,custom[number]] form-control input-small" value="<?php echo $this->escape($this->input->post->getString('x_card_code')); ?>" size="20" />
	</div>
</div>
<div class="<?php echo $controlGroupClass; ?> payment_information" id="tr_card_holder_name">
	<div class="<?php echo $controlLabelClass; ?>">
		<label>
			<?php echo Text::_('OSM_CARD_HOLDER_NAME'); ?><span class="required">*</span>
		</label>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<input type="text" name="card_holder_name" class="validate[required] form-control"  value="<?php echo $this->input->post->getString('card_holder_name'); ?>" size="40" />
	</div>
</div>
