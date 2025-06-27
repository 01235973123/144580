<?php
/**
 * @package     MPF
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2016 - 2025 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Layout variables
 *
 * @var string $name
 * @var string $title
 * @var string $format
 * @var array  $attribs
 * @var string $description
 */
?>

<div class="control-group">
	<div class="control-label">
		<?php
		if (strlen($description ?? '') > 0)
		{
			echo OSMembershipHelperHtml::getFieldLabel($name, Text::_($title), Text::_($description));
		}
		else
		{
			echo Text::_($title);
		}
		?>
	</div>
	<div class="controls">
		<?php echo HTMLHelper::_('calendar', $this->item->{$name}, $name,  $name, $format, $attribs); ?>
	</div>
</div>
