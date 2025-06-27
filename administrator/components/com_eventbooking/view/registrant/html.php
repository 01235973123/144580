<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EventbookingViewRegistrantHtml extends RADViewItem
{
	use EventbookingViewRegistrant;

	/**
	 * Method to prepare data for the view before it is displayed
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$layout = $this->getLayout();

		if ($layout == 'import')
		{
			return;
		}

		// Add necessary javascript library
		Factory::getApplication()
			->getDocument()
			->getWebAssetManager()
			->registerAndUseScript(
				'com_eventbooking.eventbookingjq',
				'media/com_eventbooking/assets/js/eventbookingjq.min.js'
			)
			->registerAndUseScript(
				'com_eventbooking.paymentmethods',
				'media/com_eventbooking/assets/js/paymentmethods.min.js'
			)
			->addInlineScript('var siteUrl="' . EventbookingHelper::getSiteUrl() . '";');

		EventbookingHelper::addLangLinkForAjax();

		$this->prepareViewData();
	}

	/**
	 * Override addToolbar function to allow generating custom buttons for import Registrants feature
	 */
	protected function addToolbar()
	{
		if ($this->getLayout() != 'default')
		{
			return;
		}

		parent::addToolbar();

		if (EventbookingHelperRegistration::canRefundRegistrant($this->item))
		{
			ToolbarHelper::custom('refund', 'delete', 'delete', Text::_('EB_REFUND'), false);
		}
	}
}
