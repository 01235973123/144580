<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JLoader::register('OSMembershipControllerData', JPATH_ROOT . '/components/com_osmembership/controller/data.php');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Membership Pro controller
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipController extends MPFControllerAdmin
{
	use OSMembershipControllerData;
	use MPFControllerDownload;

	/**
	 * Display information
	 */
	public function display($cachable = false, array $urlparams = [])
	{
		if ($this->app->isClient('administrator'))
		{
			// Check and make sure only users with proper permission can access to the page
			$viewName = $this->input->get('view', $this->config['default_view']);
			$this->checkAccessPermission($viewName);

			$wa = $this->app
				->getDocument()
				->getWebAssetManager()
				->registerAndUseStyle(
					'com_osmembership.style',
					'administrator/components/com_osmembership/assets/css/style.css'
				);

			if (version_compare(JVERSION, '5.1.0', 'ge'))
			{
				$wa->registerAndUseStyle(
					'com_osmembership.light',
					'administrator/components/com_osmembership/assets/css/light51.css'
				);
			}
			else
			{
				$wa->registerAndUseStyle(
					'com_osmembership.light',
					'administrator/components/com_osmembership/assets/css/light.css'
				);
			}

			$customCssFile = JPATH_ADMINISTRATOR . '/components/com_osmembership/assets/css/custom.css';

			if (file_exists($customCssFile) && filesize($customCssFile) > 0)
			{
				$wa->registerAndUseStyle(
					'com_osmembership.custom',
					'administrator/components/com_osmembership/assets/css/custom.css',
					['version' => filemtime($customCssFile)]
				);
			}

			$requireJQueryViews = [
				'subscription',
				'groupmember',
			];

			if (in_array($viewName, $requireJQueryViews))
			{
				$wa->useScript('jquery')
					->useScript('jquery-noconflict');
			}

			$wa->addInlineScript('var siteUrl="' . OSMembershipHelper::getSiteUrl() . '";');
		}

		parent::display($cachable, $urlparams);

		if ($this->app->isClient('administrator') && $this->input->getCmd('format', 'html') == 'html')
		{
			OSMembershipHelper::displayCopyRight();
		}
	}

	/**
	 * Download invoice
	 */
	public function download_invoice()
	{
		$id = $this->input->getInt('id');

		$db = Factory::getContainer()->get('db');
		$row = new OSMembershipTableSubscriber($db);

		if (!$row->load($id))
		{
			echo 'Invalid Subscription ID';

			return;
		}

		if (!$row->invoice_number)
		{
			throw new RuntimeException('Sorry. This subscription does not have associated invoice to download');
		}

		$invoicePath = OSMembershipHelper::generateAndReturnInvoicePath($row);
		$this->processDownloadFile($invoicePath);
	}

	/**
	 * Download file uploaded by subscriber
	 */
	public function download_file()
	{
		$filePath = 'media/com_osmembership/upload';
		$fileName = $this->input->getString('file_name', '');
		$inline   = (bool) $this->input->getInt('inline', 0);

		if (file_exists(JPATH_ROOT . '/' . $filePath . '/' . $fileName))
		{
			$this->processDownloadFile(
				JPATH_ROOT . '/' . $filePath . '/' . $fileName,
				OSMembershipHelper::getOriginalFilename($fileName),
				$inline
			);
		}
		else
		{
			$this->setRedirect('index.php?option=com_osmembership', Text::_('OSM_FILE_NOT_EXIST'));
		}
	}

	/**
	 * Check and make sure only user with proper permischeckAccessPermissionsion can access to certain view
	 *
	 * @param $view
	 */
	protected function checkAccessPermission($view)
	{
		if (!OSMembershipHelper::canAccessThisView($view))
		{
			$this->app->enqueueMessage(
				"You don't have permission to access to this section of Membership Pro",
				'error'
			);
			$this->app->redirect('index.php?option=com_osmembership&view=dashboard', 403);
		}
	}
}
