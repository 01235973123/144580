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

/**
 * Layout variables
 * -----------------
 * @var   array                     $items
 * @var   \Joomla\Registry\Registry $params
 * @var   array                     $subscriptions
 */

JLoader::register('ContentHelperRoute', JPATH_ROOT . '/components/com_content/helpers/route.php');

$config          = OSMembershipHelper::getConfig();
$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$centerClass     = $bootstrapHelper->getClassMapping('center');
?>
<table class="adminlist <?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?>" id="adminForm">
	<thead>
	<tr>
		<th class="title"><?php echo Text::_('OSM_TITLE'); ?></th>
		<th class="title"><?php echo Text::_('OSM_CATEGORY'); ?></th>
		<th class="title <?php echo $centerClass; ?>"><?php echo Text::_('OSM_ACCESSIBLE_ON'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$openArticle                  = $params->get('open_article');
	$releaseArticleOlderThanXDays = (int) $params->get('release_article_older_than_x_days', 0);

	foreach ($items as $item)
	{
		$articleLink  = Route::_(ContentHelperRoute::getArticleRoute($item->id, $item->catid));
		$subscription = $subscriptions[$item->plan_id];
		$date         = Factory::getDate($subscription->active_from_date);
		$date->add(new DateInterval('P' . $item->number_days . 'D'));

		$articleReleased = false;

		if ($releaseArticleOlderThanXDays > 0)
		{
			if ($item->publish_up && $item->publish_up != $this->db->getNullDate())
			{
				$publishedDate = $item->publish_up;
			}
			else
			{
				$publishedDate = $item->created;
			}

			$today         = Factory::getDate();
			$publishedDate = Factory::getDate($publishedDate);
			$numberDays    = $publishedDate->diff($today)->days;

			// This article is older than configured number of days, it can be accessed for free
			if ($today >= $publishedDate && $numberDays >= $releaseArticleOlderThanXDays)
			{
				$articleReleased = true;
			}
		}
		?>
		<tr>
			<td>
				<i class="icon-file"></i>
				<?php
				if ($articleReleased || ($subscription->active_in_number_days >= $item->number_days))
				{
					?>
					<a href="<?php echo $articleLink ?>"<?php echo($openArticle ? '' : ' target="_blank"'); ?>><?php echo $item->title; ?></a>
					<?php
				}
				else
				{
					echo $item->title . ' <span class="label">' . Text::_('OSM_LOCKED') . '</span>';
				}
				?>
			</td>
			<td><?php echo $item->category_title; ?></td>
			<td class="<?php echo $centerClass; ?>">
				<?php echo HTMLHelper::_('date', $date->format('Y-m-d H:i:s'), $config->date_format); ?>
			</td>
		</tr>
		<?php
	}
	?>
	</tbody>
</table>