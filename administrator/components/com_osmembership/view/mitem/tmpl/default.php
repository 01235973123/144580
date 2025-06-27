<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

$language = Factory::getApplication()->getLanguage();

$translatable = $this->item->translatable && Multilanguage::isEnabled() && count($this->languages);

$editor = OSMembershipHelper::getEditor();
$tags   = OSMembershipHelperHtml::getSupportedTags($this->item->name);
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm" class="form form-horizontal  osm-mitem-form">
	<?php
	if ($translatable)
	{
		echo HTMLHelper::_( 'uitab.startTabSet', 'mitem', ['active' => 'general-page', 'recall' => true]);
		echo HTMLHelper::_( 'uitab.addTab', 'mitem', 'general-page', Text::_('OSM_GENERAL'));
	}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php
				if ($language->hasKey($this->item->title . '_EXPLAIN'))
				{
					$messageDescription = Text::_($this->item->title . '_EXPLAIN');
				}
				else
				{
					$messageDescription = '';
				}

				echo OSMembershipHelperHtml::getFieldLabel($this->item->name, Text::_($this->item->title), $messageDescription);

				if (count($tags))
				{
					$availableTags = '[' . implode(']<br /> [', $tags) . ']';
				?>
                    <p class="osm-available-tags">
		                <?php echo Text::_('OSM_AVAILABLE_TAGS'); ?>: <br /><strong><?php echo $availableTags; ?></strong>
                    </p>
                <?php
				}
			?>
		</div>
		<div class="controls">
            <?php
				if ($this->item->type == 'text')
				{
				?>
                    <input type="text" name="<?php echo $this->item->name; ?>" class="form-control" value="<?php echo $this->escape($this->message->get($this->item->name)); ?>" />
                <?php
				}
				elseif ($this->item->type == 'textarea')
				{
				?>
                    <textarea name="<?php echo $this->item->name; ?>" class="form-control" rows="10"><?php echo $this->message->get($this->item->name); ?></textarea>
                <?php
				}
				else
				{
					echo $editor->display($this->item->name, $this->message->get($this->item->name), '100%', '550', '90', '6');
				}
			?>
		</div>
	</div>
    <?php
		if ($translatable)
		{
			echo HTMLHelper::_( 'uitab.endTab');
			echo HTMLHelper::_( 'uitab.addTab', 'mitem', 'translation-page', Text::_('OSM_TRANSLATION'));
			echo HTMLHelper::_( 'uitab.startTabSet', 'mitem-translation', ['active' => 'translation-page-' . $this->languages[0]->sef, 'recall' => true]);

			$rootUri = Uri::root(true);

			foreach ($this->languages as $language)
			{
				$sef       = $language->sef;
				$inputName = $this->item->name . '_' . $sef;
				echo HTMLHelper::_( 'uitab.addTab', 'mitem-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . '/media/mod_languages/images/' . $language->image . '.gif" />');
			?>
                <div class="control-group">
                    <div class="control-label">
	                    <?php
							echo OSMembershipHelperHtml::getFieldLabel($inputName, Text::_($this->item->title), $messageDescription);

							if (count($tags))
							{
								$availableTags = '[' . implode(']<br /> [', $tags) . ']';
								?>
                                <p class="osm-available-tags">
                                    <?php echo Text::_('OSM_AVAILABLE_TAGS'); ?>:<br /> <strong><?php echo $availableTags; ?></strong>
                                </p>
                                <?php
							}
						?>
                    </div>
                    <div class="controls">
				        <?php
						if ($this->item->type == 'text')
						{
						?>
                            <input type="text" name="<?php echo $inputName; ?>" class="form-control" value="<?php echo $this->escape($this->message->get($inputName)); ?>" />
					    <?php
						}
						elseif ($this->item->type == 'textarea')
						{
						?>
                            <textarea name="<?php echo $inputName; ?>" class="form-control" rows="10"><?php echo $this->message->get($inputName); ?></textarea>
                        <?php
						}
						else
						{
							echo $editor->display($inputName, $this->message->get($inputName), '100%', '180', '90', '6');
						}
						?>
                    </div>
                </div>
            <?php
				echo HTMLHelper::_( 'uitab.endTab');
			}

			echo HTMLHelper::_( 'uitab.endTabSet');
		}
	?>

	<div class="clearfix"></div>
	<?php echo HTMLHelper::_('form.token'); ?>
	<input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value=""/>
</form>