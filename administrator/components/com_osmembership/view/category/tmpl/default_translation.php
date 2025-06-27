<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Layout variable
 *
 * @var Editor $editor
 */

echo HTMLHelper::_( 'uitab.addTab', 'category', 'translation-page', Text::_('OSM_TRANSLATION'));
echo HTMLHelper::_( 'uitab.startTabSet', 'category-translation',
	['active' => 'translation-page-' . $this->languages[0]->sef, 'recall' => true]);
$rootUri = Uri::root(true);

foreach ($this->languages as $language)
{
	$sef = $language->sef;
	echo HTMLHelper::_( 'uitab.addTab', 'category-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . '/media/mod_languages/images/' . $language->image . '.gif" />');
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="title_<?php echo $sef; ?>" id="title_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'title_' . $sef}; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_ALIAS'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="alias_<?php echo $sef; ?>" id="alias_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'alias_' . $sef}; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_DESCRIPTION'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display('description_' . $sef, $this->item->{'description_' . $sef}, '100%', '250', '75', '10') ; ?>
		</div>
	</div>
	<?php
	echo HTMLHelper::_( 'uitab.endTab');
}

echo HTMLHelper::_( 'uitab.endTabSet');
echo HTMLHelper::_( 'uitab.endTab');