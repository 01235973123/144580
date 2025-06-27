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
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('country_id', Text::_('ESHOP_CONFIG_COUNTRY')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['country_id']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('zone_id', Text::_('ESHOP_CONFIG_REGION_STATE')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['zone_id']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('postcode', Text::_('ESHOP_CONFIG_POSTCODE')); ?>
	</div>
	<div class="controls">
		<input class="input-xlarge form-control" type="text" name="postcode" id="postcode" size="15" maxlength="128" value="<?php echo $this->config->postcode ?? ''; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('default_currency_code', Text::_('ESHOP_CONFIG_DEFAULT_CURRENCY')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['default_currency_code']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('auto_update_currency', Text::_('ESHOP_CONFIG_AUTO_UPDATE_CURRENCY')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['auto_update_currency']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<span class="required">*</span><?php echo Text::_('ESHOP_CONFIG_CURRENCY_CONVERT_API_KEY'); ?><br />
		<span class="help"><?php echo Text::_('ESHOP_CONFIG_CURRENCY_CONVERT_API_KEY_HELP'); ?></span>
	</div>
	<div class="controls">
		<input class="input-xlarge form-control" type="text" name="currency_convert_api_key" id="currency_convert_api_key"  value="<?php echo $this->config->currency_convert_api_key ?? 'd3d91dd1c0af62db625b'; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('show_eshop_update', Text::_('ESHOP_SHOW_ESHOP_UPDATE'), Text::_('ESHOP_SHOW_ESHOP_UPDATE_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['show_eshop_update']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('show_eshop_copyright', Text::_('ESHOP_SHOW_ESHOP_COPYRIGHT'), Text::_('ESHOP_SHOW_ESHOP_COPYRIGHT_HELP')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['show_eshop_copyright']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('length_id', Text::_('ESHOP_CONFIG_LENGTH')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['length_id']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('weight_id', Text::_('ESHOP_CONFIG_WEIGHT')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['weight_id']; ?>
	</div>
</div>
