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
use Joomla\CMS\Uri\Uri;

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
$translatable = $this->isMultilingualTranslable;

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
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<?php
	$availableTags = EShopHtmlHelper::getAvailableMessageTags($this->item->message_name);
	if ($translatable) {
        $rootUri = Uri::root();
        echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'message-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));

		foreach ($this->languages as $language)
		{
			$langId = $language->lang_id;
			$langCode = $language->lang_code;
			$sef = $language->sef;
			echo HTMLHelper::_($tabApiPrefix . 'addTab', 'message-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . 'media/com_eshop/flags/' . $sef . '.gif" />');
            ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->item->message_title; ?>
					<?php
					if (count($availableTags))
					{
						echo Text::_('ESHOP_MESSAGE_AVAILABLE_TAGS') . ':' . "<br />";
						echo "<b>" . implode("<br />", $availableTags) . "</b>";
					}
					?>
				</div>
				<div class="controls">
					<?php
					if ($this->item->message_type == 'textbox' || strpos($this->item->message_name, '_subject'))
					{
						?>
						<input class="input-xxlarge form-control" type="text" name="message_value_<?php echo $langCode; ?>" id="message_value_<?php echo $langId; ?>" size="" maxlength="255" value="<?php echo $this->item->{'message_value_'.$langCode} ?? ''; ?>" />
						<?php
					}
					else 
					{
						echo $editor->display( 'message_value_'.$langCode,  $this->item->{'message_value_'.$langCode} ?? '' , '100%', '250', '75', '10' );
					}
					?>
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
				<?php echo $this->item->message_title; ?>
				<?php
				if (count($availableTags))
				{
					echo Text::_('ESHOP_MESSAGE_AVAILABLE_TAGS') . ':' . "<br />";
					echo "<b>" . implode("<br />", $availableTags) . "</b>";
				}
				?>
			</div>
			<div class="controls">
				<?php
				if ($this->item->message_type == 'textbox' || strpos($this->item->message_name, '_subject'))
				{
					?>
					<input class="input-xxlarge form-control" type="text" name="message_value" id="message_value" maxlength="255" value="<?php echo $this->item->message_value; ?>" />
					<?php
				}
				else
				{
					echo $editor->display( 'message_value',  $this->item->message_value , '100%', '250', '75', '10' );
				}
				?>
			</div>
		</div>
		<?php
	}
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