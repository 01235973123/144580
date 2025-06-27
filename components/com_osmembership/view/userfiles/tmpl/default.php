<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$centerClass     = $bootstrapHelper->getClassMapping('center');
?>
<div id="osm-my-files" class="osm-container">
    <?php
		if ($this->params->get('show_page_heading', 1))
		{
			if ($this->input->getInt('hmvc_call'))
			{
				$hTag = 'h2';
			}
			else
			{
				$hTag = 'h1';
			}
		?>
	        <<?php echo $hTag; ?> class="osm-page-title"><?php echo $this->params->get('page_heading') ?: Text::_('OSM_MY_FILES'); ?></<?php echo $hTag; ?>>
	    <?php
		}

		if (OSMembershipHelper::isValidMessage($this->params->get('intro_text')))
		{
		?>
	        <div class="osm-description osm-page-intro-text <?php echo $bootstrapHelper->getClassMapping('clearfix'); ?>">
				<?php echo HTMLHelper::_('content.prepare', $this->params->get('intro_text')); ?>
	        </div>
	    <?php
		}

		if ($this->files)
		{

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
				foreach ($this->files as $file)
				{
					$downloadLink = Route::_('index.php?option=com_osmembership&task=download_user_file&file=' . $file . '&Itemid=' . $this->Itemid);
					?>
					<tr>
						<td><a href="<?php echo $downloadLink ?>"><?php echo $file; ?></a></td>
						<td class="<?php echo $centerClass; ?>"><?php echo OSMembershipHelperHtml::getFormattedFilezize($this->path . '/' . $file); ?></td>
						<td class="<?php echo $centerClass; ?>">
							<a href="<?php echo $downloadLink; ?>"><i class="<?php echo $bootstrapHelper->getClassMapping('icon-download'); ?>"></i></a>
						</td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
        <?php
		}
		else
		{
		?>
			<p class="text-info osm-my-files-empty"><?php echo Text::_('OSM_USER_FILES_NO_FILES_AVAILABLE'); ?></p>
		<?php
		}
	?>
</div>