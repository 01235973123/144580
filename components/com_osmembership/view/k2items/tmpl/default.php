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
<div id="osm-my-k2items" class="osm-container">
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
	    <<?php echo $hTag; ?> class="osm-page-title"><?php echo Text::_('OSM_MY_K2_ITMES') ; ?></<?php echo $hTag; ?>>
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

	if (!empty($this->items))
	{
		JLoader::register('K2HelperRoute', JPATH_ROOT . '/components/com_k2/helpers/route.php');
	?>
        <table class="adminlist <?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?>" id="adminForm">
            <thead>
            <tr>
                <th class="title"><?php echo Text::_('OSM_TITLE'); ?></th>
                <th class="title"><?php echo Text::_('OSM_CATEGORY'); ?></th>
                <th class="<?php echo $centerClass; ?>"><?php echo Text::_('OSM_HITS'); ?></th>
            </tr>
            </thead>
            <?php
			if ($this->pagination->total > $this->pagination->limit)
			{
			?>
            <tfoot>
                <tr>
                    <td colspan="3">
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
					$k2itemLink = Route::_(K2HelperRoute::getItemRoute($item->id, $item->catid));
				?>
                    <tr>
                        <td><a href="<?php echo $k2itemLink ?>"><?php echo $item->title; ?></a></td>
                        <td><?php echo $item->category_name; ?></td>
                        <td class="<?php echo $centerClass; ?>">
                            <?php echo $item->hits; ?>
                        </td>
                    </tr>
                <?php
				}
			?>
            </tbody>
        </table>
    <?php
	}
	?>
</div>