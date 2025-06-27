<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Updater\Updater;
use Joomla\Component\Installer\Administrator\Model\UpdateModel;
use Joomla\Database\DatabaseDriver;

class OSMembershipViewDashboardHtml extends MPFViewHtml
{
	public $hasModel = false;

	/**
	 * Store update result
	 *
	 * @var array
	 */
	protected $updateResult = [];

	/**
	 * Latest subscriptions
	 *
	 * @var array
	 */
	protected $subscriptions;

	/**
	 * Component config
	 *
	 * @var MPFConfig
	 */
	protected $config;

	/**
	 * Statistic data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Last 13 months sales data
	 *
	 * @var array|array[]
	 */
	protected $sales;

	/**
	 * Daily sales data
	 *
	 * @var array|array[]
	 */
	protected $dailySales = [];

	/**
	 * Select list array
	 *
	 * @var array
	 */
	protected $lists;

	/**
	 * Display the view
	 *
	 * @return void
	 */
	public function display()
	{
		$this->subscriptions = MPFModel::getTempInstance('Subscriptions', 'OSMembershipModel')
			->limitstart(0)
			->limit(10)
			->filter_order('tbl.created_date')
			->filter_order_Dir('DESC')
			->getData();
		$this->config        = OSMembershipHelper::getConfig();
		$this->data          = OSMembershipModelSubscriptions::getStatisticsData();
		$this->sales         = OSMembershipModelSubscriptions::getLast12MonthSales();
		$this->dailySales    = MPFModel::getTempInstance('Subscriptions', 'OSMembershipModel')
			->getDailySalesStatistic();

		// Get list of plans
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('id, title')
			->from('#__osmembership_plans')
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);

		$planId      = $this->input->getInt('plan_id', 0);
		$dailyPlanId = $this->input->getInt('daily_plan_id', 0);

		$options                = [];
		$options[]              = HTMLHelper::_('select.option', 0, Text::_('OSM_ALL_PLANS'), 'id', 'title');
		$options                = array_merge($options, $db->loadObjectList());
		$this->lists['plan_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'plan_id',
			'onchange="reloadSalesChart();" class="form-select"',
			'id',
			'title',
			$planId
		);

		$this->lists['daily_plan_id'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'daily_plan_id',
			'onchange="reloadDailySalesChart();" class="form-select"',
			'id',
			'title',
			$dailyPlanId
		);

		// Render sub-menu in dashboard
		OSMembershipHelperHtml::renderSubmenu('dashboard');

		// Check update information
		$this->checkUpdate();

		if ($this->updateResult['status'] == 2)
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf(
					'OSM_UPDATE_AVAILABLE',
					'index.php?option=com_installer&view=update',
					$this->updateResult['version']
				)
			);
		}

		// Display warning if users are using PHP older than 7.4.0
		OSMembershipHelper::displayPHPVersionWarning();

		parent::display();
	}

	/**
	 * Function to create the buttons view.
	 *
	 * @param   string  $link   targeturl
	 * @param   string  $image  path to image
	 * @param   string  $text   image description
	 */
	protected function quickiconButton($link, $image, $text, $id = null)
	{
		$language = Factory::getApplication()->getLanguage();
		?>
        <div
                style="float:<?php
				echo ($language->isRTL()) ? 'right' : 'left'; ?>;" <?php
		if ($id)
		{
			echo 'id="' . $id . '"';
		} ?>>
            <div class="icon">
                <a class="lh-1" href="<?php
				echo $link; ?>">
					<?php
					echo HTMLHelper::_(
						'image',
						'administrator/components/com_osmembership/assets/icons/' . $image,
						$text
					); ?>
                    <span><?php
						echo $text; ?></span>
                </a>
            </div>
        </div>
		<?php
	}

	protected function checkUpdate()
	{
		// Get the caching duration.
		$component     = ComponentHelper::getComponent('com_installer');
		$params        = $component->params;
		$cache_timeout = $params->get('cachetimeout', 6);
		$cache_timeout = 3600 * $cache_timeout;

		// Get the minimum stability.
		$minimum_stability = $params->get('minimum_stability', Updater::STABILITY_STABLE);

		/* @var UpdateModel $model */
		$model = Factory::getApplication()->bootComponent('com_installer')->getMVCFactory()
			->createModel('Update', 'Administrator', ['ignore_request' => true]);

		$model->purge();

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('extension_id')
			->from('#__extensions')
			->where('`type` = "package"')
			->where('`element` = "pkg_osmembership"');
		$db->setQuery($query);
		$eid = (int) $db->loadResult();

		$result['status'] = 0;

		if ($eid)
		{
			$ret = Updater::getInstance()->findUpdates($eid, $cache_timeout, $minimum_stability);

			if ($ret)
			{
				$model->setState('list.start', 0);
				$model->setState('list.limit', 0);
				$model->setState('filter.extension_id', $eid);
				$updates           = $model->getItems();
				$result['status']  = 2;
				$result['version'] = '';

				if (count($updates))
				{
					$result['message'] = Text::sprintf('OSM_UPDATE_CHECKING_UPDATEFOUND', $updates[0]->version);
					$result['version'] = $updates[0]->version;
				}
				else
				{
					$result['message'] = Text::sprintf('OSM_UPDATE_CHECKING_UPDATEFOUND', null);
				}
			}
			else
			{
				$result['status']  = 1;
				$result['message'] = Text::_('OSM_UPDATE_CHECKING_UPTODATE');
			}
		}

		$this->updateResult = $result;
	}
}
