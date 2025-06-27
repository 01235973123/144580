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
?>
<div id="osm-urls-manage" class="osm-container">
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
		<<?php echo $hTag; ?> class="osm-page-title"><?php echo Text::_('OSM_MY_PAGES') ; ?></<?php echo $hTag; ?>>
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
    <form method="post" name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_osmembership&view=urls&Itemid=' . $this->Itemid); ?>">
    <?php
	if (!empty($this->items))
	{
	?>
        <table class="adminlist <?php echo $bootstrapHelper->getClassMapping('table table-bordered table-striped') ?>" id="adminForm">
            <thead>
            <tr>
                <th class="title"><?php echo Text::_('OSM_PAGE_URL'); ?></th>
            </tr>
            </thead>
            <?php
			if ($this->pagination->total > $this->pagination->limit)
			{
			?>
            <tfoot>
                <tr>
                    <td>
                        <div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
                    </td>
                </tr>
            </tfoot>
            <?php
			}
			?>
            <tbody>
            <?php
				foreach ($this->items as $item)
				{
				?>
                    <tr>
                        <td><a href="<?php echo $item->url ?>" target="_blank"><?php echo $item->title ?: $item->url; ?></a></td>
                    </tr>
                <?php
				}
			?>
            </tbody>
        </table>
    <?php
	}
	?>
    </form>
</div>
