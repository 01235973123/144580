<?php
/**
 * @version        1.0
 * @package        OSFramework
 * @subpackage     EShopModel
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

echo HTMLHelper::_('form.token');
?>
<input type="hidden" name="option" value="com_eshop"/>
<input type="hidden" name="cid[]" value="<?php echo intval($this->item->id); ?>" />
<?php
if ($this->isMultilingualTranslable)
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
