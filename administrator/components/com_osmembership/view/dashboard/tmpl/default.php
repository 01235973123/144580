<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('OSM_DASHBOARD'), 'generic.png');

$app = Factory::getApplication();

$app->getDocument()
	->getWebAssetManager()
	->useScript('core');

$user            = $app->getIdentity();
$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
        <div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
            <div id="cpanel">
                <?php
				if (OSMembershipHelper::canAccessThisView('configuration'))
				{
					$this->quickiconButton('index.php?option=com_osmembership&view=configuration', 'icon-48-config.png', Text::_('OSM_CONFIGURATION'));
				}

				if (OSMembershipHelper::canAccessThisView('categories'))
				{
					$this->quickiconButton('index.php?option=com_osmembership&view=categories', 'icon-48-categories.png', Text::_('OSM_PLAN_CATEGORIES'));
				}

				if (OSMembershipHelper::canAccessThisView('plans'))
				{
					$this->quickiconButton('index.php?option=com_osmembership&view=plans', 'icon-48-package.png', Text::_('OSM_SUBSCRIPTION_PLANS'));
				}

				if ($user->authorise('membershippro.subscriptions', 'com_osmembership'))
				{
					$this->quickiconButton('index.php?option=com_osmembership&view=subscriptions', 'icon-48-subscribers.png', Text::_('OSM_SUBSCRIPTIONS'));
					$this->quickiconButton('index.php?option=com_osmembership&view=subscribers', 'icon-48-profiles.png', Text::_('OSM_SUBSCRIBERS'));
					$this->quickiconButton('index.php?option=com_osmembership&view=groupmembers', 'icon-48-profiles.png', Text::_('OSM_GROUPMEMBERS'));
				}

				if (OSMembershipHelper::canAccessThisView('fields'))
				{
					$this->quickiconButton('index.php?option=com_osmembership&view=fields', 'icon-48-fields.png', Text::_('OSM_CUSTOM_FIELDS'));
				}

				if (OSMembershipHelper::canAccessThisView('taxes'))
				{
					$this->quickiconButton('index.php?option=com_osmembership&view=taxes', 'icon-48-taxrules.png', Text::_('OSM_TAX_RULES'));
				}

				if (OSMembershipHelper::canAccessThisView('coupons'))
				{
					$this->quickiconButton('index.php?option=com_osmembership&view=coupons', 'icon-48-coupons.png', Text::_('OSM_COUPONS'));
				}

				if ($user->authorise('core.admin', 'com_osmembership'))
				{
					$this->quickiconButton('index.php?option=com_osmembership&view=mitems', 'icon-48-mail.png', Text::_('OSM_EMAIL_MESSAGES'));
					$this->quickiconButton('index.php?option=com_osmembership&view=plugins', 'icon-48-payments-plugin.png', Text::_('OSM_PAYMENT_PLUGINS'));
				}

				if ($user->authorise('core.admin', 'com_osmembership'))
				{
					$this->quickiconButton('index.php?option=com_osmembership&view=language', 'icon-48-language.png', Text::_('OSM_TRANSLATION'));
				}

				if ($user->authorise('membershippro.subscriptions', 'com_osmembership'))
				{
					$this->quickiconButton('index.php?option=com_osmembership&task=subscription.export', 'icon-48-export.png', Text::_('OSM_EXPORT_SUBSCRIBERS'));
				}

				if (OSMembershipHelper::canAccessThisView('countries'))
				{
					$this->quickiconButton('index.php?option=com_osmembership&view=countries', 'icon-48-countries.png', Text::_('OSM_COUNTRIES'));
				}

				if (OSMembershipHelper::canAccessThisView('states'))
				{
					$this->quickiconButton('index.php?option=com_osmembership&view=states', 'icon-48-states.png', Text::_('OSM_STATES'));
				}

				if ($user->authorise('core.admin', 'com_osmembership'))
				{
					$link = 'index.php?option=com_osmembership';

					switch ($this->updateResult['status'])
					{
						case 0:
							$icon = 'icon-48-deny.png';
							$text = Text::_('OSM_UPDATE_CHECKING_ERROR');
							break;
						case 1:
							$icon = 'icon-48-jupdate-uptodate.png';
							$text = $this->updateResult['message'];
							break;
						case 2:
							$icon =  'icon-48-jupdate-updatefound.png';
							$text = $this->updateResult['message'];
							$link = 'index.php?option=com_installer&view=update';
							break;
						default:
							$icon = 'icon-48-download.png';
							$text = Text::_('OSM_UPDATE_CHECKING');
							break;
					}

					$this->quickiconButton($link, $icon, $text, 'update-check');
				}
				?>
            </div>
        </div>
        <div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
            <?php
			echo HTMLHelper::_('bootstrap.startAccordion', 'statistics_pane', ['active' => 'statistic']);
			echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('OSM_STATISTICS'), 'statistic');
			echo $this->loadTemplate('statistics');
			echo HTMLHelper::_('bootstrap.endSlide');
			echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('OSM_LATEST_SUBSCRIPTIONS'), 'subscriptions');
			echo $this->loadTemplate('subscriptions');
			echo HTMLHelper::_('bootstrap.endSlide');

			if ($user->authorise('core.admin', 'com_osmembership'))
			{
				echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('OSM_USEFUL_LINKS'), 'links_panel');
				echo $this->loadTemplate('useful_links');
				echo HTMLHelper::_('bootstrap.endSlide');
			}

			echo HTMLHelper::_('bootstrap.endAccordion');
			?>
        </div>
		<div class="osm-daily-sales <?php echo $bootstrapHelper->getClassMapping('span12');?>" style="margin-top: 30px;">
			<h3><?php echo Text::_('OSM_LAST_30_DAYS_SALES_CHART'); ?></h3>
			<div style="margin: 0 40%;"><?php echo $this->lists['daily_plan_id']; ?></div>
			<?php echo $this->loadTemplate('daily_sales_chart'); ?>
		</div>
        <div class="osm-sales <?php echo $bootstrapHelper->getClassMapping('span12');?>" style="margin-top: 30px;">
	        <h3><?php echo Text::_('OSM_LAST_13_MONTHS_SALES_CHART'); ?></h3>
	        <div style="margin: 0 40%;"><?php echo $this->lists['plan_id']; ?></div>
			<?php echo $this->loadTemplate('chart'); ?>
        </div>
    </div>
	<input type="hidden" name="option" value=""/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
</form>

<style>
	#statistics_pane {
		margin: 0 !important
	}
</style>
