<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Layout variables
 * -----------------
 * @var   array $items
 */

JLoader::register('SppagebuilderHelperRoute', JPATH_ROOT . '/components/com_sppagebuilder/helpers/route.php');

$displayedPageIds = [];
$bootstrapHelper  = OSMembershipHelperBootstrap::getInstance();
$centerClass      = $bootstrapHelper->getClassMapping('center');
?>
<table class="adminlist <?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?>" id="adminForm">
	<thead>
	<tr>
		<th class="title"><?php echo Text::_('OSM_TITLE'); ?></th>
		<th class="title"><?php echo Text::_('OSM_CATEGORY'); ?></th>
		<th class="<?php echo $centerClass; ?>"><?php echo Text::_('OSM_HITS'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php

	foreach ($items as $item)
	{
		if (in_array($item->id, $displayedPageIds))
		{
			continue;
		}

		$displayedPageIds[] = $item->id;

		$pageLink = Route::_(SppagebuilderHelperRoute::getPageRoute($item->id));
		?>
		<tr>
			<td><a href="<?php echo $pageLink ?>"><?php echo $item->title; ?></a></td>
			<td><?php echo $item->category_title; ?></td>
			<td class="<?php echo $centerClass; ?>">
				<?php echo $item->hits; ?>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>
