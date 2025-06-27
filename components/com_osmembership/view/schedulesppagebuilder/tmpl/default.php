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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$centerClass  = $bootstrapHelper->getClassMapping('center');
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
		<<?php echo $hTag; ?> class="osm-page-title"><?php echo Text::_('OSM_MY_SCHEDULE_SP_PAGE_BUILDER_PAGES') ; ?></<?php echo $hTag; ?>>
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
    <form method="post" name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_osmembership&view=schedulesppagebuilder&Itemid=' . $this->Itemid); ?>">
    <?php
	if (!empty($this->items))
	{
		$items         = $this->items;
		$subscriptions = $this->subscriptions;
		$config        = $this->config;

		JLoader::register('SppagebuilderHelperRoute', JPATH_ROOT . '/components/com_sppagebuilder/helpers/route.php');
	?>
        <table class="adminlist <?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?>" id="adminForm">
            <thead>
            <tr>
                <th class="title"><?php echo Text::_('OSM_TITLE'); ?></th>
                <th class="title <?php echo $centerClass; ?>"><?php echo Text::_('OSM_ACCESSIBLE_ON'); ?></th>
            </tr>
            </thead>
            <?php
			if ($this->pagination->total > $this->pagination->limit)
			{
			?>
                <tfoot>
                <tr>
                    <td colspan="2">
                        <div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
                    </td>
                </tr>
                </tfoot>
            <?php
			}
			?>
            <tbody>
            <?php
            /* @var \Joomla\Database\DatabaseDriver $db */
			$db = Factory::getContainer()->get('db');
			$openPages = $this->openPages;
			$releasePagesOlderThanXDays = $this->releasePagesOlderThanXDays;

			foreach ($items as $item)
			{
				$pagesLink  = Route::_(SppagebuilderHelperRoute::getPageRoute($item->id, '0'));
				$subscription = $subscriptions[$item->plan_id];
				$date         = Factory::getDate($subscription->active_from_date);
				$date->add(new DateInterval('P' . $item->number_days . 'D'));

				$pagesReleased = false;

				if ($releasePagesOlderThanXDays > 0)
				{
					if ($item->created_on && $item->created_on != $this->db->getNullDate())
					{
						$publishedDate = $item->created_on;
					}

					$today         = Factory::getDate();
					$publishedDate = Factory::getDate($publishedDate);
					$numberDays    = $publishedDate->diff($today)->days;

					// This article is older than configured number of days, it can be accessed for free
					if ($today >= $publishedDate && $numberDays >= $releasePagesOlderThanXDays)
					{
						$pagesReleased = true;
					}
				}
				?>
                <tr>
                    <td>
                        <i class="icon-file"></i>
                        <?php
						if ($pagesReleased || ($subscription->active_in_number_days >= $item->number_days))
						{
							?>
                            <a href="<?php echo $pagesLink ?>"<?php echo($openPages ? '' : ' target="_blank"'); ?>><?php echo $item->title; ?></a>
                            <?php
						}
						else
						{
							echo $item->title . ' <span class="label">' . Text::_('OSM_LOCKED') . '</span>';
						}
						?>
                    </td>
                    <td class="<?php echo $centerClass; ?>">
                        <?php echo HTMLHelper::_('date', $date->format('Y-m-d H:i:s'), $config->date_format); ?>
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
        <p class="text-info"><?php echo Text::_('OSM_NO_SCHEDULE_SPPAGEBUILDER'); ?></p>
    <?php
	}
	?>
    </form>
</div>