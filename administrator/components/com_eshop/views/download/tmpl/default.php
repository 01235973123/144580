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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$translatable = $this->isMultilingualTranslable;
$requireNameInMultipleLanguages = EShopHelper::getConfigValue('require_name_in_multiple_languages', 1);
$defaultSiteLanguage =  ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
?>
<script type="text/javascript">	
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'download.cancel') {
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
    					if (document.getElementById('download_name_<?php echo $langId; ?>').value == '') {
    						alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
    						document.getElementById('download_name_<?php echo $langId; ?>').focus();
    						return;
    					}
    					<?php
                    }
                    else 
                    {
                       if ($language->lang_code == $defaultSiteLanguage)
                       {
                            ?>
        					if (document.getElementById('download_name_<?php echo $langId; ?>').value == '') {
        						alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
        						document.getElementById('download_name_<?php echo $langId; ?>').focus();
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
				if (form.download_name.value == '') {
					alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
					form.download_name.focus();
					return;
				}
				<?php
			}
			if ($this->item->filename == '')
			{
				?>
				if (form.file.value == '' && form.existed_file.value == '') {
					alert("<?php echo Text::_('ESHOP_CHOOSE_FILE_TO_UPLOAD'); ?>");
					form.file.focus();
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
					<input class="input-xlarge form-control" type="text" name="download_name_<?php echo $langCode; ?>" id="download_name_<?php echo $langId; ?>" size="" maxlength="255" value="<?php echo $this->item->{'download_name_'.$langCode} ?? ''; ?>" />
					<img src="<?php echo Uri::root(); ?>media/com_eshop/flags/<?php echo $this->languageData['flag'][$langCode]; ?>" />
					<br />
					<?php
				}
			}
			else 
			{
				?>
				<input class="input-xlarge form-control" type="text" name="download_name" id="download_name" maxlength="255" value="<?php echo $this->item->download_name; ?>" />
				<?php
			}
			?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo Text::_('ESHOP_FILE_NAME'); ?>
		</div>
		<div class="controls">
			<input type="file" maxlength="250" size="50" id="file" name="file" class="form-control" value="" />
			<?php
			if ($this->item->filename != '')
			{
				echo '<small>(' . $this->item->filename . ')</small>';
			}
			?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_OVERWRITE_EXISTING_FILE'); ?>
		</div>
		<div class="controls">
			<input type="checkbox" class="form-check-input" id="overwrite" name="overwrite" class="form-check-input" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_SELECT_A_FILE'); ?>
			<span class="help"><?php echo Text::_('ESHOP_SELECT_A_FILE_HELP'); ?></span>
		</div>
		<div class="controls">
			<?php echo $this->lists['existed_file']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_TOTAL_DOWNLOADS_ALLOWED'); ?>
		</div>
		<div class="controls">
			<input type="text" class="input-small form-control" name="total_downloads_allowed" id="total_downloads_allowed" value="<?php echo $this->item->total_downloads_allowed ?? '100'; ?>" />
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