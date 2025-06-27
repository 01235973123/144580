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
use Joomla\Filesystem\Path;

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$centerClass     = $bootstrapHelper->getClassMapping('center');
$downloadClass   = $bootstrapHelper->getClassMapping('icon-download');
?>
<div id="osm-subscription-history" class="osm-container">
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
		<<?php echo $hTag; ?> class="osm-page-title"><?php echo Text::_('OSM_MY_DOWNLOADS') ; ?></<?php echo $hTag; ?>>
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
	?>
    <form method="post" name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_osmembership&view=documents&Itemid=' . $this->Itemid); ?>">
    <?php
		if ($this->items)
		{
			$documents = $this->items;
			$path      = Path::clean($this->documentsPath . '/');
		?>
            <table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered table-hover'); ?>">
                <thead>
                <tr>
                    <th class="title"><?php echo Text::_('OSM_TITLE'); ?></th>
                    <th class="title"><?php echo Text::_('OSM_DOCUMENT'); ?></th>
                    <th class="<?php echo $centerClass; ?>"><?php echo Text::_('OSM_SIZE'); ?></th>
                    <th class="<?php echo $centerClass; ?>"><?php echo Text::_('OSM_DOWNLOAD'); ?></th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td colspan="<?php echo 4 ; ?>">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
                </tfoot>
                <tbody>
                <?php
				for ($i = 0, $n = count($documents); $i < count($documents); $i++)
				{
					$document     = $documents[$i];
					$downloadLink = Route::_('index.php?option=com_osmembership&task=download_document&id=' . $document->id . '&Itemid=' . $this->Itemid);
					?>
                    <tr>
                        <td><a href="<?php echo $downloadLink ?>"><?php echo $document->title; ?></a></td>
                        <td><?php echo $document->attachment; ?></td>
                        <td class="<?php echo $centerClass; ?>"><?php echo OSMembershipHelperHtml::getFormattedFilezize($path . $document->attachment); ?></td>
                        <td class="<?php echo $centerClass; ?>">
                            <a href="<?php echo $downloadLink; ?>"><i class="<?php echo $downloadClass; ?>"></i></a>
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
            <p class="text-info"><?php echo Text::_('OSM_NO_DOCUMENTS_AVAILABLE'); ?></p>
        <?php
		}
	?>
    </form>
</div>