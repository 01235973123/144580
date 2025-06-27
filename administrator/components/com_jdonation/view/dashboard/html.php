<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die ();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Updater\Updater;
use Joomla\Component\Installer\Administrator\Model\UpdateModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;

class DonationViewDashboardHtml extends OSFViewHtml
{
	public $hasModel = false;

	protected $statistics;
    protected $donationTimeline;
    protected $topCampaigns;
    protected $endingSoonCampaigns;
    protected $donorLocations;
    protected $recentDonations;
    protected $campaignDistribution;
    protected $hasData;
	protected $updateResult;

	function display()
	{
		//$model = $this->getModel();
		$model = OSFModel::getInstance('Dashboard', 'DonationModel');
        $this->hasData = $model->hasData();
        
        if ($this->hasData) {
			$jnow     = Factory::getDate();
			$endDate  = $jnow->format('Y-m-d');
			$startDate = $jnow->modify('-6 days')->format('Y-m-d');

			// Truyền vào các hàm model
			$this->statistics         = $model->getStatistics($startDate, $endDate);
			$this->donationTimeline   = $model->getDonationTimeline($startDate, $endDate);
			$this->topCampaigns       = $model->getTopCampaigns(5,$startDate, $endDate);
			$this->endingSoonCampaigns= $model->getEndingSoonCampaigns(); // Không cần filter ngày
			$this->donorLocations     = $model->getDonorLocations(10, $startDate, $endDate);
			$this->recentDonations    = $model->getRecentDonations(10, $startDate, $endDate);
			$this->campaignDistribution = $model->getCampaignDistribution($startDate, $endDate);

        }
        
        // Add scripts and styles
        $document = Factory::getApplication()->getDocument();
        $document->addScript('https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js');
        $document->addStyleSheet(Uri::root() . 'administrator/components/com_jdonation/assets/css/dashboard.css');
        $document->addScript(Uri::root() . 'administrator/components/com_jdonation/assets/js/admin-dashboard-default.js');
        
        // Set the toolbar
        $this->addToolbar();
		// Render sub-menu in dashboard
		DonationHelperHtml::renderSubmenu('dashboard');
		$this->updateResult = $this->checkUpdate();
		$this->config = DonationHelper::getConfig();
        // Display the template
        parent::display();
	}

    /**
     * Add toolbar to the view
     */
    protected function addToolbar()
    {
        ToolBarHelper::title(Text::_('JD_DASHBOARD'), 'generic.png');
        $canDo = DonationHelper::getActions();
        if ($canDo->get('core.admin'))
        {
            ToolBarHelper::preferences('com_jdonation');
        }
    }

	/**
	 *
	 * Function to create the buttons view.
	 *
	 * @param string $link  targeturl
	 * @param string $image path to image
	 * @param string $text  image description
	 *
	 */
	protected function quickiconButton($link, $image, $text, $id = null)
	{
		$language = Factory::getApplication()->getLanguage();
		?>
		<div style="float:<?php echo ($language->isRTL()) ? 'right' : 'left'; ?>;" <?php if ($id) echo 'id="' . $id . '"'; ?>>
			<div class="icon">
				<a href="<?php echo $link; ?>" title="<?php echo $text; ?>">
					<?php echo HTMLHelper::_('image', 'administrator/components/com_jdonation/assets/icons/' . $image, $text); ?>
					<span><?php echo $text; ?></span>
				</a>
			</div>
		</div>
	<?php
	}

	/**
	 * Check to see the installed version is up to date or not
	 *
	 * @return int 0 : error, 1 : Up to date, 2 : outof date
	 */
	public function checkUpdate()
	{
		// Get the caching duration.
		$params        = ComponentHelper::getComponent('com_installer')->getParams();
		$cache_timeout = (int) $params->get('cachetimeout', 6);
		$cache_timeout = 3600 * $cache_timeout;

		// Get the minimum stability.
		$minimum_stability = (int) $params->get('minimum_stability', Updater::STABILITY_STABLE);

		/* @var UpdateModel $model */
		$model = Factory::getApplication()->bootComponent('com_installer')->getMVCFactory()
				->createModel('Update', 'Administrator', ['ignore_request' => true]);
		

		$model->purge();

		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('extension_id')
			->from('#__extensions')
			->where('`type` = "package"')
			->where('`element` = "pkg_joomdonation"');
		$db->setQuery($query);
		$eid = (int) $db->loadResult();

		$result['status']  = 0;
		$result['version'] = '';

		if ($eid)
		{
			$ret = Updater::getInstance()->findUpdates($eid, $cache_timeout, $minimum_stability);

			if ($ret)
			{
				$model->setState('list.start', 0);
				$model->setState('list.limit', 0);
				$model->setState('filter.extension_id', $eid);
				$updates          = $model->getItems();
				$result['status'] = 2;

				if (count($updates))
				{
					$result['message'] = Text::sprintf('JD_UPDATE_CHECKING_UPDATEFOUND', $updates[0]->version);
					$result['version'] = $updates[0]->version;
				}
				else
				{
					$result['message'] = Text::sprintf('JD_UPDATE_CHECKING_UPDATEFOUND', null);
				}
			}
			else
			{
				$result['status']  = 1;
				$result['message'] = Text::_('JD_UPDATE_CHECKING_UPTODATE');
			}
		}

		return $result;
	}
}
