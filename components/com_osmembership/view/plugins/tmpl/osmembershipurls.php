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

/**
 * Layout variables
 * -----------------
 * @var   array $urls
 */

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
?>
<table class="adminlist <?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?>" id="adminForm">
	<thead>
	<tr>
		<th class="title"><?php echo Text::_('OSM_PAGE_URL'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($urls as $url)
	{
	?>
		<tr>
			<td>
				<a href="<?php echo $url->url ?>" target="_blank"><?php echo $url->title ?: $url->url; ?></a>
			</td>
		</tr>
	<?php
	}
	?>
	</tbody>
</table>
