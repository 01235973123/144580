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

$centerClass   = $this->bootstrapHelper->getClassMapping('center');
$defaultItemId = OSMembershipHelper::getItemid();
$userId        = Factory::getApplication()->getIdentity()->id;
?>
<table class="<?php echo $this->bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?>">
	<thead>
	<tr>
		<th>
			<?php echo Text::_('OSM_PLAN') ?>
		</th>
		<th width="20%" class="<?php echo $centerClass; ?>">
			<?php echo Text::_('OSM_ACTIVATE_TIME') ; ?>
		</th>
		<th width="20%" class="<?php echo $centerClass; ?>">
			<?php echo Text::_('OSM_SUBSCRIPTION_STATUS'); ?>
		</th>
        <?php
			if ($this->showDownloadMemberCard)
			{
			?>
                <th width="20%" class="<?php echo $centerClass; ?>">
		            <?php echo Text::_('OSM_MEMBER_CARD'); ?>
                </th>
            <?php
			}
		?>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach($this->subscriptions as $subscription)
	{
		$Itemid = OSMembershipHelperRoute::getPlanMenuId($subscription->id, $subscription->category_id, $defaultItemId);

		if ($subscription->category_id)
		{
			$url = Route::_('index.php?option=com_osmembership&view=plan&catid=' . $subscription->category_id . '&id=' . $subscription->id . '&Itemid=' . $Itemid);
		}
		else
		{
			$url = Route::_('index.php?option=com_osmembership&view=plan&id=' . $subscription->id . '&Itemid=' . $Itemid);
		}
	?>
		<tr>
			<td>
				<a href="<?php echo $url ?>" target="_blank" class="osm-plan-link"><?php echo $subscription->title; ?></a>
				<?php
				if ($subscription->number_group_members > 0
					&& $this->config->get('show_join_group_link')
					&& OSMembershipHelperSubscription::isGroupAdmin($userId, $subscription->id))
				{
					$joinGroupLink = OSMembershipHelperRoute::getViewRoute('group', $defaultItemId) . '&group_id=' . $subscription->subscriptions[0]->subscription_code;
					$joinGroupLink = Route::link('site', $joinGroupLink, false, 0, true);
				?>
					<br />
					<span class="osm-join-link-label"><?php echo Text::_('OSM_JOIN_GROUP_LINK'); ?></span>: <a href="<?php echo $joinGroupLink ?>" target="_blank"><?php echo $joinGroupLink; ?></a>
				<?php
				}
				?>
			</td>
			<td class="<?php echo $centerClass; ?>">
				<strong><?php echo HTMLHelper::_('date', $subscription->subscription_from_date, $this->config->date_format); ?></strong> <?php echo Text::_('OSM_TO'); ?>
				<strong>
					<?php
					if ($subscription->lifetime_membership || $subscription->subscription_to_date  == '2099-12-31 23:59:59')
					{
						echo Text::_('OSM_LIFETIME');
					}
					else
					{
						echo HTMLHelper::_('date', $subscription->subscription_to_date, $this->config->date_format);
					}
					?>
				</strong>
			</td>
			<td class="<?php echo $centerClass; ?>">
				<?php
				switch ($subscription->subscription_status)
				{
					case 0 :
						echo Text::_('OSM_PENDING');
						break ;
					case 1 :
						echo Text::_('OSM_ACTIVE');
						break ;
					case 2 :
						echo Text::_('OSM_EXPIRED');
						break ;
					case 3 :
						echo Text::_('OSM_CANCELLED_PENDING');
						break ;
					case 4 :
						echo Text::_('OSM_CANCELLED_REFUNDED');
						break ;
				}

				if (!$subscription->recurring_cancelled && $this->config->get('enable_user_cancel_subscription', 1)
					&& $subscription->subscription_status == 1 && $subscription->subscription_id)
				{
				?>
					<a class="btn btn-danger osm-btn-cancel-subscription" href="javascript:cancelSubscription('<?php echo $subscription->subscription_id;  ?>');"><?php echo Text::_('OSM_CANCEL_SUBSCRIPTION'); ?></a>
				<?php
				}

				if ($subscription->recurring_cancelled)
				{
					echo '<br /><span class="text-error">' . Text::_('OSM_RECURRING_CANCELLED') . '</span>';
				}
				elseif($subscription->subscription_id)
				{
					$subscriptionInfo = OSMembershipHelperSubscription::getSubscription($subscription->subscription_id);
					$method = OSMembershipHelperPayments::getPaymentMethod($subscriptionInfo->payment_method);

					if (method_exists($method, 'updateCard'))
					{
					?>
						<a href="<?php echo Route::_('index.php?option=com_osmembership&view=card&subscription_id=' . $subscription->subscription_id . '&Itemid=' . $this->Itemid); ?>" class="btn btn-primary osm-btn-update-card"><?php echo Text::_('OSM_UPDATE_CARD');  ?></a>
					<?php
					}
				}
				?>
			</td>
            <?php
			if ($this->showDownloadMemberCard)
			{
			?>
                <td class="center">
                    <?php
						if ($subscription->show_download_member_card)
						{
						?>
                            <a class="download-member-card-link" href="<?php echo Text::_('index.php?option=com_osmembership&task=profile.download_member_plan_card&plan_id=' . $subscription->id . '&Itemid=' . $this->Itemid); ?>"><strong><?php echo Text::_('OSM_DOWNLOAD'); ?></strong></a>
                        <?php
						}
					?>
                </td>
            <?php
			}
			?>
		</tr>
	<?php
	}
	?>
	</tbody>
</table>
