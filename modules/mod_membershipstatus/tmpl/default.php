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

$config = OSMembershipHelper::getConfig();

if (empty($rowSubscriptions))
{
?>
	<p class="text-info"><?php echo Text::_('OSM_NO_ACTIVE_SUBSCRIPTIONS'); ?></p>
<?php
}
else
{
?>
	<ul class="osm-active-plans-list">
		<?php
			$todayDate = Factory::getDate();

			foreach($rowSubscriptions as $rowSubscription)
			{
				if ($rowSubscription->lifetime_membership || $rowSubscription->subscription_to_date  == '2099-12-31 23:59:59')
				{
					$membershipStatus = Text::_('OSM_MEMBERSHIP_STATUS_LIFETIME');
				}
				else
				{
					$expiredDate = Factory::getDate($rowSubscription->subscription_to_date);
					$numberDays = $todayDate->diff($expiredDate)->days;

					if ($todayDate < $expiredDate)
					{
						$membershipStatus = Text::_('OSM_MEMBERSHIP_STATUS_ACTIVE');
					}
					else
					{
						$membershipStatus = Text::_('OSM_MEMBERSHIP_STATUS_EXPIRED');
					}

					$membershipStatus = str_replace('[NUMBER_DAYS]', abs($numberDays), $membershipStatus);
				}

				$membershipStatus = str_replace('[PLAN_TITLE]', $rowSubscription->title, $membershipStatus);
				$membershipStatus = str_replace('[EXPIRED_DATE]', HTMLHelper::_('date', $rowSubscription->subscription_to_date, $config->date_format), $membershipStatus);
			?>
				<li><?php echo $membershipStatus; ?></li>
			<?php
			}
		?>
	</ul>
<?php
}