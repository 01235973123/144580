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
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$translatable = $this->isMultilingualTranslable;
$requireNameInMultipleLanguages = EShopHelper::getConfigValue('require_name_in_multiple_languages', 1);
$defaultSiteLanguage =  ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

EShopHelper::chosen();
?>
<script type="text/javascript">	
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'label.cancel') {
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
    					if (document.getElementById('label_name_<?php echo $langId; ?>').value == '') {
    						alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
    						document.getElementById('label_name_<?php echo $langId; ?>').focus();
    						return;
    					}
    					<?php
                    }
                    else 
                    {
                       if ($language->lang_code == $defaultSiteLanguage)
                       {
                            ?>
        					if (document.getElementById('label_name_<?php echo $langId; ?>').value == '') {
        						alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
        						document.getElementById('label_name_<?php echo $langId; ?>').focus();
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
				if (form.label_name.value == '') {
					alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
					form.label_name.focus();
					return;
				}
				<?php
			}
			?>
			if (form.label_start_date.value > form.label_end_date.value) {
				alert("<?php echo Text::_('ESHOP_DATE_VALIDATE'); ?>");
				form.label_start_date.focus();
				return;
			}
			Joomla.submitform(pressbutton, form);
		}
	}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo  Text::_('ESHOP_NAME'); ?>
		</div>
		<div class="controls">
			<?php
			if ($translatable)
			{
				foreach ($this->languages as $language)
				{
					$langId = $language->lang_id;
					$langCode = $language->lang_code;
					?>
					<input class="input-xlarge form-control" type="text" name="label_name_<?php echo $langCode; ?>" id="label_name_<?php echo $langId; ?>" size="" maxlength="255" value="<?php echo $this->item->{'label_name_'.$langCode} ?? ''; ?>" />
					<img src="<?php echo Uri::root(); ?>media/com_eshop/flags/<?php echo $this->languageData['flag'][$langCode]; ?>" />
					<br />
					<?php
				}
			}
			else 
			{
				?>
				<input class="input-xlarge form-control" type="text" name="label_name" id="label_name" maxlength="255" value="<?php echo $this->item->label_name; ?>" />
				<?php
			}
			?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_LABEL_STYLE'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['label_style']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_LABEL_POSITION'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['label_position']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_LABEL_BOLD'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['label_bold']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_LABEL_BACKGROUND_COLOR'); ?>
		</div>
		<div class="controls">
			<input type="text" name="label_background_color" class="input-medium form-control color {required:false}" value="<?php echo $this->item->label_background_color; ?>" size="5" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_LABEL_FOREGROUND_COLOR'); ?>
		</div>
		<div class="controls">
			<input type="text" name="label_foreground_color" class="input-medium form-control color {required:false}" value="<?php echo $this->item->label_foreground_color; ?>" size="5" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_LABEL_OPACITY'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['label_opacity']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_ENABLE_IMAGE'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['enable_image']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_LABEL_IMAGE'); ?>
		</div>
		<div class="controls">
			<input type="file" class="input-large form-control" accept="image/*" name="label_image" />								
			<?php
			if (is_file(JPATH_ROOT.'/media/com_eshop/labels/'.$this->item->label_image))
			{
				$imageWidth = $this->item->label_image_width > 0 ? $this->item->label_image_width : EShopHelper::getConfigValue('label_image_width');
				if (!$imageWidth)
					$imageWidth = 40;
				$imageHeight = $this->item->label_image_height > 0 ? $this->item->label_image_height : EShopHelper::getConfigValue('label_image_height');
				if (!$imageHeight)
					$imageHeight = 40;
				$viewImage = File::stripExt($this->item->label_image).'-'.$imageWidth.'x'.$imageHeight.'.'.EShopHelper::getFileExt($this->item->label_image);
				if (is_file(JPATH_ROOT.'/media/com_eshop/labels/resized/'.$viewImage))
				{
					?>
					<img src="<?php echo Uri::root().'media/com_eshop/labels/resized/'.$viewImage; ?>" />
					<?php
				}
				else 
				{
					?>
					<img src="<?php echo Uri::root().'media/com_eshop/labels/'.$this->item->label_image; ?>" height="100" />
					<?php
				}
				?>
				<label class="checkbox">
					<input type="checkbox" class="form-check-input" name="remove_image" value="1" />
					<?php echo Text::_('ESHOP_REMOVE_IMAGE'); ?>
				</label>
				<?php
			}
			?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_LABEL_IMAGE_WIDTH'); ?>
		</div>
		<div class="controls">
			<input type="text" class="input-medium form-control" name="label_image_width" id="label_image_width" value="<?php echo $this->item->label_image_width ? $this->item->label_image_width : 0; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_LABEL_IMAGE_HEIGHT'); ?>
		</div>
		<div class="controls">
			<input type="text" class="input-medium form-control" name="label_image_height" id="label_image_height" value="<?php echo $this->item->label_image_height ? $this->item->label_image_height : 0; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_SELECT_PRODUCTS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['products']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_SELECT_MANUFACTURERS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['manufacturers']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_CATEGORIES'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['categories']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_START_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', $this->item->label_start_date, 'label_start_date', 'label_start_date', '%Y-%m-%d %H:%M', ['showTime' => true]); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_END_DATE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('calendar', $this->item->label_end_date, 'label_end_date', 'label_end_date', '%Y-%m-%d %H:%M', ['showTime' => true]); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_LABEL_OUT_OF_STOCK_PRODUCTS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['label_out_of_stock_products']; ?>
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
		<input type="hidden" name="details_id" value="<?php echo $this->item->{'details_id'} ?? ''; ?>" />
		<?php
	}
	?>
	<input type="hidden" name="task" value="" />
</form>