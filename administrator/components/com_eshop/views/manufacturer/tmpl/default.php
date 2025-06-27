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
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
$translatable = $this->isMultilingualTranslable;
$requireNameInMultipleLanguages = EShopHelper::getConfigValue('require_name_in_multiple_languages', 1);
$defaultSiteLanguage =  ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
EShopHelper::chosen();

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
		if (pressbutton == 'manufacturer.cancel') {
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
    					if (document.getElementById('manufacturer_name_<?php echo $langId; ?>').value == '') {
    						alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
    						document.getElementById('manufacturer_name_<?php echo $langId; ?>').focus();
    						return;
    					}
    					<?php
                    }
                    else 
                    {
                       if ($language->lang_code == $defaultSiteLanguage)
                       {
                            ?>
        					if (document.getElementById('manufacturer_name_<?php echo $langId; ?>').value == '') {
        						alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
        						document.getElementById('manufacturer_name_<?php echo $langId; ?>').focus();
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
				if (form.manufacturer_name.value == '') {
					alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
					form.manufacturer_name.focus();
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
	echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'manufacturer', array('active' => 'general-page'));
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'manufacturer', 'general-page', Text::_('ESHOP_GENERAL', true));

	if ($translatable)
	{
	    $rootUri = Uri::root();
	    echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'manufacturer-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));
	    
		foreach ($this->languages as $language)
		{
			$langId = $language->lang_id;
			$langCode = $language->lang_code;
			$sef = $language->sef;
			echo HTMLHelper::_($tabApiPrefix . 'addTab', 'manufacturer-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . 'media/com_eshop/flags/' . $sef . '.gif" />');
            ?>
			<div class="control-group">
				<div class="control-label">
					<span class="required">*</span>
					<?php echo  Text::_('ESHOP_NAME'); ?>
				</div>
				<div class="controls">
					<input class="input-xlarge form-control" type="text" name="manufacturer_name_<?php echo $langCode; ?>" id="manufacturer_name_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'manufacturer_name_'.$langCode} ?? ''; ?>" />
				</div>								
			</div>																		
			<div class="control-group">
				<div class="control-label">
					<?php echo  Text::_('ESHOP_ALIAS'); ?>
				</div>
				<div class="controls">
					<input class="input-xlarge form-control" type="text" name="manufacturer_alias_<?php echo $langCode; ?>" id="manufacturer_alias_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'manufacturer_alias_'.$langCode} ?? ''; ?>" />
				</div>								
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo  Text::_('ESHOP_PAGE_TITLE'); ?>
				</div>
				<div class="controls">
					<input class="input-xlarge form-control" type="text" name="manufacturer_page_title_<?php echo $langCode; ?>" id="manufacturer_page_title_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'manufacturer_page_title_'.$langCode} ?? ''; ?>" />
				</div>								
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo  Text::_('ESHOP_PAGE_HEADING'); ?>
				</div>
				<div class="controls">
					<input class="input-xlarge form-control" type="text" name="manufacturer_page_heading_<?php echo $langCode; ?>" id="manufacturer_page_heading_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'manufacturer_page_heading_'.$langCode} ?? ''; ?>" />
				</div>								
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo  Text::_('ESHOP_ALT_IMAGE'); ?>
				</div>
				<div class="controls">
					<input class="input-xlarge form-control" type="text" name="manufacturer_alt_image_<?php echo $langCode; ?>" id="manufacturer_alt_image_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'manufacturer_alt_image_'.$langCode} ?? ''; ?>" />
				</div>								
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('ESHOP_DESCRIPTION'); ?>
				</div>
				<div class="controls">
					<?php echo $editor->display( 'manufacturer_desc_'.$langCode,  $this->item->{'manufacturer_desc_'.$langCode} ?? '' , '100%', '250', '75', '10' ); ?>
				</div>								
			</div>
			<?php
			echo HTMLHelper::_($tabApiPrefix . 'endTab');
		}
		echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
	}
	else
	{
		?>
		<div class="control-group">
			<div class="control-label">
				<span class="required">*</span>
				<?php echo  Text::_('ESHOP_NAME'); ?>
			</div>
			<div class="controls">
				<input class="input-xlarge form-control" type="text" name="manufacturer_name" id="manufacturer_name" size="" maxlength="250" value="<?php echo $this->item->manufacturer_name; ?>" />
			</div>								
		</div>																		
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_ALIAS'); ?>
			</div>
			<div class="controls">
				<input class="input-xlarge form-control" type="text" name="manufacturer_alias" id="manufacturer_alias" size="" maxlength="250" value="<?php echo $this->item->manufacturer_alias; ?>" />
			</div>								
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_PAGE_TITLE'); ?>
			</div>
			<div class="controls">
				<input class="input-xlarge form-control" type="text" name="manufacturer_page_title" id="manufacturer_page_title" size="" maxlength="250" value="<?php echo $this->item->manufacturer_page_title; ?>" />
			</div>								
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_PAGE_HEADING'); ?>
			</div>
			<div class="controls">
				<input class="input-xlarge form-control" type="text" name="manufacturer_page_heading" id="manufacturer_page_heading" size="" maxlength="250" value="<?php echo $this->item->manufacturer_page_heading; ?>" />
			</div>								
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_ALT_IMAGE'); ?>
			</div>
			<div class="controls">
				<input class="input-xlarge form-control" type="text" name="manufacturer_alt_image" id="manufacturer_alt_image" size="" maxlength="250" value="<?php echo $this->item->manufacturer_alt_image; ?>" />
			</div>								
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_DESCRIPTION'); ?>
			</div>
			<div class="controls">
				<?php echo $editor->display( 'manufacturer_desc',  $this->item->manufacturer_desc , '100%', '250', '75', '10' ); ?>
			</div>
		</div>
		<?php
	}
	
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'manufacturer', 'data-page', Text::_('ESHOP_DATA', true));
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_EMAIL'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="manufacturer_email" id="manufacturer_email" size="40" maxlength="250" value="<?php echo $this->item->manufacturer_email; ?>" />
		</div>							
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_URL'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="manufacturer_url" id="manufacturer_url" size="60" value="<?php echo $this->item->manufacturer_url; ?>" />
		</div>							
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_MANUFACTURER_IMAGE'); ?>
		</div>
		<div class="controls">
			<input type="file" class="input-large form-control" accept="image/*" name="manufacturer_image" />
			<?php
				if (is_file(JPATH_ROOT.'/media/com_eshop/manufacturers/'.$this->item->manufacturer_image))
				{
					$viewImage = File::stripExt($this->item->manufacturer_image).'-100x100.'.EShopHelper::getFileExt($this->item->manufacturer_image);
					if (is_file(JPATH_ROOT.'/media/com_eshop/manufacturers/resized/'.$viewImage))
					{
						?>
						<img src="<?php echo Uri::root().'media/com_eshop/manufacturers/resized/'.$viewImage; ?>" />
						<?php
					}
					else 
					{
						?>
						<img src="<?php echo Uri::root().'media/com_eshop/manufacturers/'.$this->item->manufacturer_image; ?>" height="100" />
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
			<?php echo Text::_('ESHOP_CUSTOMERGROUPS'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['manufacturer_customergroups']; ?>
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
		<input type="hidden" name="details_id" value="<?php echo $this->item->{'details_id'} ?? ''; ?>" />
		<?php
	}
	?>
	<input type="hidden" name="task" value="" />
</form>