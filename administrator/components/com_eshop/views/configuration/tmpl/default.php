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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);
EShopHelper::chosen();

$document = Factory::getApplication()->getDocument();
$document->addStyleDeclaration(".hasTip{display:block !important}");
ToolbarHelper::title(Text::_('ESHOP_CONFIGURATION'), 'generic.png');
ToolbarHelper::apply('configuration.save');
ToolbarHelper::cancel('configuration.cancel');

$canDo	= EShopHelper::getActions();

if ($canDo->get('core.admin'))
{
	ToolbarHelper::preferences('com_eshop');
}

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));

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
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'configuration.cancel') {
			Joomla.submitform(pressbutton, form);
			return;
		} else {
			//Validate the entered data before submittings
			if (form.catalog_limit.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_DEFAULT_ITEMS_PER_PAGE'); ?>");
				form.catalog_limit.focus();
				return;
			}
			if (form.items_per_row.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_DEFAULT_ITEMS_PER_ROW'); ?>");
				form.items_per_row.focus();
				return;
			}
			if (form.image_category_width.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_CATEGORY_IMAGE_WIDTH'); ?>");
				form.image_category_width.focus();
				return;
			}
			if (form.image_category_height.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_CATEGORY_IMAGE_HEIGHT'); ?>");
				form.image_category_height.focus();
				return;
			}
			if (form.image_thumb_width.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_PRODUCT_IMAGE_THUMB_WIDTH'); ?>");
				form.image_thumb_width.focus();
				return;
			}
			if (form.image_thumb_height.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_PRODUCT_IMAGE_THUMB_HEIGHT'); ?>");
				form.image_thumb_height.focus();
				return;
			}
			if (form.image_popup_width.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_PRODUCT_IMAGE_POPUP_WIDTH'); ?>");
				form.image_popup_width.focus();
				return;
			}
			if (form.image_popup_height.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_PRODUCT_IMAGE_POPUP_HEIGHT'); ?>");
				form.image_popup_height.focus();
				return;
			}
			if (form.image_list_width.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_PRODUCT_IMAGE_LIST_WIDTH'); ?>");
				form.image_list_width.focus();
				return;
			}
			if (form.image_list_height.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_PRODUCT_IMAGE_LIST_HEIGHT'); ?>");
				form.image_list_height.focus();
				return;
			}
			if (form.image_additional_width.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_ADDITIONAL_PRODUCT_IMAGE_WIDTH'); ?>");
				form.image_additional_width.focus();
				return;
			}
			if (form.image_additional_height.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_ADDITIONAL_PRODUCT_IMAGE_HEIGHT'); ?>");
				form.image_additional_height.focus();
				return;
			}
			if (form.image_related_width.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_RELATED_PRODUCT_IMAGE_WIDTH'); ?>");
				form.image_related_width.focus();
				return;
			}
			if (form.image_related_height.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_RELATED_PRODUCT_IMAGE_HEIGHT'); ?>");
				form.image_related_height.focus();
				return;
			}
			if (form.image_compare_width.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_COMPARE_IMAGE_WIDTH'); ?>");
				form.image_compare_width.focus();
				return;
			}
			if (form.image_compare_height.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_COMPARE_IMAGE_HEIGHT'); ?>");
				form.image_compare_height.focus();
				return;
			}
			if (form.image_wishlist_width.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_WISH_LIST_IMAGE_WIDTH'); ?>");
				form.image_wishlist_width.focus();
				return;
			}
			if (form.image_wishlist_height.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_WISH_LIST_IMAGE_HEIGHT'); ?>");
				form.image_wishlist_height.focus();
				return;
			}
			if (form.image_cart_width.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_CART_IMAGE_WIDTH'); ?>");
				form.image_cart_width.focus();
				return;
			}
			if (form.image_cart_height.value == '') {
				alert("<?php echo Text::_('ESHOP_CONFIG_ENTER_CART_IMAGE_HEIGHT'); ?>");
				form.image_cart_height.focus();
				return;
			}
			Joomla.submitform(pressbutton, form);
		}
	}
</script>
<div class="row-fluid">
	<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-horizontal">
		<?php
		echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'configuration', array('active' => 'general-page'));

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'general-page', Text::_('ESHOP_CONFIG_GENERAL', true));
		echo $this->loadTemplate('general');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'local-page', Text::_('ESHOP_CONFIG_LOCAL', true));
		echo $this->loadTemplate('local');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'option-page', Text::_('ESHOP_CONFIG_OPTION', true));
		echo $this->loadTemplate('option');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'image-page', Text::_('ESHOP_CONFIG_IMAGE', true));
		echo $this->loadTemplate('image');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'layout-page', Text::_('ESHOP_CONFIG_LAYOUT', true));
		echo $this->loadTemplate('layout');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'invoice-page', Text::_('ESHOP_CONFIG_INVOICE', true));
		echo $this->loadTemplate('invoice');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'sorting-page', Text::_('ESHOP_CONFIG_SORTING', true));
		echo $this->loadTemplate('sorting');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'social-page', Text::_('ESHOP_CONFIG_SOCIAL', true));
		echo $this->loadTemplate('social');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'mail-page', Text::_('ESHOP_CONFIG_MAIL', true));
		echo $this->loadTemplate('mail');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');
		
		if (EShopHelper::getConfigValue('product_custom_fields'))
		{
		    echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'product-fields', Text::_('ESHOP_CONFIG_PRODUCT_FIELDS', true));
		    echo $this->loadTemplate('product_fields');
		    echo HTMLHelper::_($tabApiPrefix . 'endTab');
		}
		
		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'custom-css', Text::_('ESHOP_CONFIG_CUSTOM_CSS', true));
		echo $this->loadTemplate('custom_css');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
		?>
		<input type="hidden" name="option" value="com_eshop" />
		<input type="hidden" name="task" value="" />
		<div class="clearfix"></div>
	</form>
</div>