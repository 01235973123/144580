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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.core');
HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);
EShopHelper::chosen();

$document = Factory::getApplication()->getDocument();
$document->addStyleDeclaration(".hasTip{display:block !important}");

$translatable = $this->isMultilingualTranslable;

echo $this->loadTemplate('javascript');

if (EShopHelper::isJoomla4())
{
    $tabApiPrefix = 'uitab.';
}
else
{
    HTMLHelper::_('behavior.tabstate');

    $tabApiPrefix = 'bootstrap.';
}
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form form-horizontal">
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'product', array('active' => 'general-page'));

	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'general-page', Text::_('ESHOP_GENERAL', true));
	echo $this->loadTemplate('general');
	echo HTMLHelper::_($tabApiPrefix . 'endTab');

	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'data-page', Text::_('ESHOP_DATA', true));
	echo $this->loadTemplate('data');
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
	
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'inventory-page', Text::_('ESHOP_INVENTORY', true));
	echo $this->loadTemplate('inventory');
	echo HTMLHelper::_($tabApiPrefix . 'endTab');

	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'attributes-page', Text::_('ESHOP_ATTRIBUTES', true));
	echo $this->loadTemplate('attributes');
	echo HTMLHelper::_($tabApiPrefix . 'endTab');

	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'options-page', Text::_('ESHOP_OPTIONS', true));
	echo $this->loadTemplate('options');
	echo HTMLHelper::_($tabApiPrefix . 'endTab');

	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'discounts-page', Text::_('ESHOP_DISCOUNT', true));
	echo $this->loadTemplate('discounts');
	echo HTMLHelper::_($tabApiPrefix . 'endTab');

	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'special-page', Text::_('ESHOP_SPECIAL', true));
	echo $this->loadTemplate('special');
	echo HTMLHelper::_($tabApiPrefix . 'endTab');

	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'images-page', Text::_('ESHOP_IMAGES', true));
	echo $this->loadTemplate('images');
	echo HTMLHelper::_($tabApiPrefix . 'endTab');

	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'attachments-page', Text::_('ESHOP_ATTACHMENTS', true));
	echo $this->loadTemplate('attachments');
	echo HTMLHelper::_($tabApiPrefix . 'endTab');

	if (EShopHelper::getConfigValue('acymailing_integration') && ComponentHelper::isEnabled('com_acymailing'))
	{
		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'acymailing-page', Text::_('ESHOP_ACYMAILING', true));
		echo $this->loadTemplate('acymailing');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');
	}

	if (EShopHelper::getConfigValue('mailchimp_integration') && EShopHelper::getConfigValue('api_key_mailchimp') != '')
	{
		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'mailchimp-page', Text::_('ESHOP_MAILCHIMP', true));
		echo $this->loadTemplate('mailchimp');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');
	}

	if (EShopHelper::getConfigValue('product_custom_fields'))
	{
		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'custom_fields-page', Text::_('ESHOP_EXTRA_INFORMATION', true));
		echo $this->loadTemplate('custom_fields');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');
	}

	if (count($this->plugins))
	{
		$count = 0;

		foreach ($this->plugins as $plugin)
		{
			$count++;
			echo HTMLHelper::_($tabApiPrefix . 'addTab', 'product', 'tab_' . $count, Text::_($plugin['title'], true));
			echo $plugin['form'];
			echo HTMLHelper::_($tabApiPrefix . 'endTab');
		}
	}

	echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
	?>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="cid[]" value="<?php echo intval($this->item->id); ?>" />
	<?php
	if ($translatable)
	{
		foreach ($this->languages as $language)
		{
			$langCode = $language->lang_code;
		?>
			<input type="hidden" name="details_id_<?php echo $langCode; ?>" value="<?php echo intval($this->item->{'details_id_' . $langCode} ?? ''); ?>" />
		<?php
		}
	}
	elseif ($this->translatable)
	{
	?>
		<input type="hidden" name="details_id" value="<?php echo intval($this->item->{'details_id'} ?? ''); ?>" />
	<?php
	}
	?>
	<input type="hidden" name="task" value="" />
	<div id="date_html_container" style="display: none;">
		<?php echo HTMLHelper::_('calendar', '', 'tmp_date_picker_name', 'tmp_date_picker_id', '%Y-%m-%d %H:%M', ['class' => 'input-medium', 'showTime' => true]); ?>
	</div>
</form>