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
 * @var   array  $files
 * @var   string $path
 * @var   int    $Itemid
 */

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$centerClass     = $bootstrapHelper->getClassMapping('center');
?>
<table class="adminlist <?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?>"
       id="adminForm">
	<thead>
	<tr>
		<th class="title"><?php echo Text::_('OSM_FILE'); ?></th>
		<th class="<?php echo $centerClass; ?>"><?php echo Text::_('OSM_SIZE'); ?></th>
		<th class="<?php echo $centerClass; ?>"><?php echo Text::_('OSM_DOWNLOAD'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($files as $file)
	{
		$downloadLink = Route::_('index.php?option=com_osmembership&task=download_user_file&file=' . $file . '&Itemid=' . $Itemid);
		?>
		<tr>
			<td><a href="<?php echo $downloadLink ?>"><?php echo $file; ?></a></td>
			<td class="<?php echo $centerClass; ?>"><?php echo OSMembershipHelperHtml::getFormattedFilezize($path . '/' . $file); ?></td>
			<td class="<?php echo $centerClass; ?>">
				<a href="<?php echo $downloadLink; ?>"><i class="<?php echo $bootstrapHelper->getClassMapping('icon-download'); ?>"></i></a>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>


