<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

class OSMembershipControllerPayment extends OSMembershipController
{
	use OSMembershipControllerCaptcha;

	/**
	 * Process payment for a subscription
	 */
	public function process()
	{
		// Check token
		$this->csrfProtection();

		// Validate input data
		$errors = [];

		// Validate captcha
		if (!$this->validateCaptcha($this->input))
		{
			$errors[] = Text::_('OSM_INVALID_CAPTCHA_ENTERED');
		}

		$data = $this->input->post->getData();

		if (count($errors))
		{
			foreach ($errors as $error)
			{
				$this->app->enqueueMessage($error, 'error');
			}

			$this->input->set('captcha_invalid', 1);
			$this->input->set('view', 'payment');
			$this->input->set('layout', 'default');
			$this->display();

			return;
		}

		/* @var OSMembershipModelPayment $model */
		$model = $this->getModel('payment');

		$model->processSubscriptionPayment($data);
	}
}
