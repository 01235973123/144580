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

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
?>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('download_id', Text::_('ESHOP_DOWNLOAD_ID'), Text::_('ESHOP_DOWNLOAD_ID_HELP')); ?>
	</div>
	<div class="controls">
		<input class="input-xxlarge form-control" type="text" name="download_id" id="download_id" size="15" maxlength="250" value="<?php echo $this->config->download_id ?? ''; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('store_name', Text::_('ESHOP_CONFIG_STORE_NAME')); ?>
	</div>
	<div class="controls">
		<input class="input-xxlarge form-control" type="text" name="store_name" id="store_name" size="15" maxlength="250" value="<?php echo $this->config->store_name; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('store_owner', Text::_('ESHOP_CONFIG_STORE_OWNER')); ?>
	</div>
	<div class="controls">
		<input class="input-xxlarge form-control" type="text" name="store_owner" id="store_owner" size="15" maxlength="250" value="<?php echo $this->config->store_owner; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('address', Text::_('ESHOP_CONFIG_ADDRESS')); ?>
	</div>
	<div class="controls">
		<textarea class="input-xxlarge form-control" rows="5" cols="40" name="address" id="address"><?php echo $this->config->address; ?></textarea>					
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('email', Text::_('ESHOP_CONFIG_EMAIL')); ?>
	</div>
	<div class="controls">
		<input class="input-xxlarge form-control" type="text" name="email" id="email" size="15" maxlength="100" value="<?php echo $this->config->email; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('telephone', Text::_('ESHOP_CONFIG_TELEPHONE')); ?>
	</div>
	<div class="controls">
		<input class="input-xxlarge form-control" type="text" name="telephone" id="telephone" size="10" value="<?php echo $this->config->telephone; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('fax', Text::_('ESHOP_CONFIG_FAX')); ?>
	</div>
	<div class="controls">
		<input class="input-xxlarge form-control" type="text" name="fax" id="fax" size="10" maxlength="15" value="<?php echo $this->config->fax; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('introduction_display_on', Text::_('ESHOP_CONFIG_INTRODUCTION_DISPLAY_ON')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['introduction_display_on']; ?>
	</div>
</div>
<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('debug_mode', Text::_('ESHOP_CONFIG_DEBUG_MODE'), Text::_('ESHOP_CONFIG_DEBUG_MODE_HELP')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['debug_mode']; ?>
		</div>
	</div>