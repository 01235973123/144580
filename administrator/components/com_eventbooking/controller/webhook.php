<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Application\CMSWebApplicationInterface;

defined('_JEXEC') or die;

class EventbookingControllerWebhook extends EventbookingController
{
	public function create()
	{
		$name = $this->input->getString('name');

		if (!$name)
		{
			$this->app->enqueueMessage(
				'Please provide name of the plugin which you want to create webhook',
				CMSWebApplicationInterface::MSG_WARNING
			);

			return;
		}

		// Get list of webhooks
		$plugin = EventbookingHelperPayments::getPaymentMethod($name);

		if (!$plugin)
		{
			$this->app->enqueueMessage(
				sprintf('There is no payment plugin with name %s available', $name),
				CMSWebApplicationInterface::MSG_WARNING
			);
		}

		if (is_callable([$plugin, 'createWebhook']))
		{
			$plugin->createWebhook();
			$this->app->enqueueMessage(sprintf('Webhook is created for the payment plugin %s', $name));
		}
	}
}
