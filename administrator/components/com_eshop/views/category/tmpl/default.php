<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	ESshop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.core');
HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);
EShopHelper::chosen();

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
$translatable = $this->isMultilingualTranslable;
$requireNameInMultipleLanguages = EShopHelper::getConfigValue('require_name_in_multiple_languages', 1);
$defaultSiteLanguage =  ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

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
		if (pressbutton == 'category.cancel') {
			Joomla.submitform(pressbutton, form);
			return;				
		} else {
			//Validate the entered data before submitting
			<?php
			if ($translatable)
			{
				foreach ($this->languages as $language)
				{
					$langId = $language->lang_id;
					
					if ($requireNameInMultipleLanguages)
					{
					    ?>
					    if (document.getElementById('category_name_<?php echo $langId; ?>').value == '') {
							alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
							document.getElementById('category_name_<?php echo $langId; ?>').focus();
							return;
						}
    					<?php
                    }
                    else 
                    {
                       if ($language->lang_code == $defaultSiteLanguage)
                       {
                           ?>
                           if (document.getElementById('category_name_<?php echo $langId; ?>').value == '') {
                               alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
                               document.getElementById('category_name_<?php echo $langId; ?>').focus();
                               return;
                            }
           					<?php
                       }
                    }
				}
			}
			else
			{
				?>
				if (form.category_name.value == '') {
					alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
					form.category_name.focus();
					return;
				}
				<?php
			}
			?>
			Joomla.submitform(pressbutton, form);
		}
	}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form form-horizontal">
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'category', array('active' => 'general-page'));
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'category', 'general-page', Text::_('ESHOP_GENERAL', true));

	if ($translatable)
	{
        $rootUri = Uri::root();
        echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'category-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));
	    
		foreach ($this->languages as $language)
		{
			$langId		= $language->lang_id;
			$langCode	= $language->lang_code;
			$sef		= $language->sef;
			
			echo HTMLHelper::_($tabApiPrefix . 'addTab', 'category-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . 'media/com_eshop/flags/' . $language->sef . '.gif" />');

			// Set nameSuffix and idSuffix for the class before loading sub-layout
			$this->nameSuffix = '_' . $langCode;
			$this->idSuffix   = '_' . $langId;

			echo $this->loadTemplate('data');

			echo HTMLHelper::_($tabApiPrefix . 'endTab');
		}
		echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
	}
	else
	{
		echo $this->loadTemplate('data');
	}

	echo HTMLHelper::_($tabApiPrefix . 'endTab');
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'category', 'data-page', Text::_('ESHOP_DATA', true));
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_PARENT_CATEGORY'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['category_parent_id']; ?>
		</div>							
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_CATEGORY_IMAGE'); ?>
		</div>
		<div class="controls">
			<input type="file" class="input-large form-control" accept="image/*" name="category_image" />								
			<?php
				if (is_file(JPATH_ROOT.'/media/com_eshop/categories/'.$this->item->category_image))
				{
					$viewImage = File::stripExt($this->item->category_image).'-100x100.'.EShopHelper::getFileExt($this->item->category_image);
					if (is_file(JPATH_ROOT.'/media/com_eshop/categories/resized/'.$viewImage))
					{
						?>
						<img src="<?php echo Uri::root(true).'/media/com_eshop/categories/resized/'.$viewImage; ?>" />
						<?php
					}
					else 
					{
						?>
						<img src="<?php echo Uri::root(true).'/media/com_eshop/categories/'.$this->item->category_image; ?>" height="100" />
						<?php
					}
					?>
					<div class="form-check">
						<input type="checkbox" class="form-check-input" name="remove_image" id="remove_image" value="1" />
            			<label class="form-check-label" for="remove_image">
            				<?php echo Text::_('ESHOP_REMOVE_IMAGE'); ?>
            			</label>
        			</div>
					<?php
				}
			?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_CATEGORY_LAYOUT'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['category_layout']; ?>
		</div>
	</div>
	<div class="control-group">
        <div class="control-label">
            <?php echo EShopHtmlHelper::getFieldLabel('default_sorting', Text::_('ESHOP_CONFIG_PRODUCT_DEFAULT_SORTING')); ?>
        </div>
        <div class="controls">
            <?php echo $this->lists['default_sorting']; ?>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_CUSTOMERGROUPS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['category_customergroups']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_CART_MODE_CUSTOMERGROUPS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['category_cart_mode_customergroups']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_PRODUCTS_PER_PAGE'); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="products_per_page" id="products_per_page" size="" maxlength="250" value="<?php echo $this->item->products_per_page ? $this->item->products_per_page : 0; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_PRODUCTS_PER_ROW'); ?>
		</div>
		<div class="controls">
			<input class="input-small form-control" type="text" name="products_per_row" id="products_per_row" size="" maxlength="250" value="<?php echo $this->item->products_per_row ? $this->item->products_per_row : 0; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_QUANTITY_BOX'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['quantity_box']; ?>
		</div>							
	</div>				
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_PUBLISHED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['published']; ?>
		</div>							
	</div>
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
	echo HTMLHelper::_($tabApiPrefix . 'endTabSet');

	$this->renderFormHiddenVariables();
	?>
</form>