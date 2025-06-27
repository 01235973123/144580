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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$this->loadFormValidator();

$translatable = $this->isMultilingualTranslable;
$requireNameInMultipleLanguages = EShopHelper::getConfigValue('require_name_in_multiple_languages', 1);
$defaultSiteLanguage =  ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

/* @var \Joomla\CMS\Document\HtmlDocument $document */
$document = Factory::getApplication()->getDocument();
$document->addScriptDeclaration('cancelTask', 'attribute.cancel')
	->addScript(Uri::root(true).'/administrator/components/com_eshop/assets/js/formvalidation.js', [], ['defer' => true]);
?>
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

					if ($requireNameInMultipleLanguages || $langCode === $defaultSiteLanguage)
					{
						$requiredClass = ' required';
					}
					else
					{
						$requiredClass  = '';
					}
					?>
					<input class="input-xlarge form-control<?php echo $requiredClass; ?>" type="text" name="attribute_name_<?php echo $langCode; ?>" id="attribute_name_<?php echo $langId; ?>" size="" maxlength="255" value="<?php echo $this->item->{'attribute_name_'.$langCode} ?? ''; ?>" />
					<img src="<?php echo Uri::root(); ?>media/com_eshop/flags/<?php echo $this->languageData['flag'][$langCode]; ?>" />
					<br />
					<?php
				}
			}
			else 
			{
			?>
				<input class="input-xlarge form-control required" type="text" name="attribute_name" id="attribute_name" maxlength="255" value="<?php echo $this->item->attribute_name; ?>" />
			<?php
			}
			?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_ATTRIBUTEGROUP'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['attributegroups']; ?>
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
	<?php $this->renderFormHiddenVariables(); ?>
</form>