<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

class OSMembershipControllerGroup extends OSMembershipController
{
	use OSMembershipControllerCaptcha;

	/**
	 * Process subscription
	 *
	 * @throws Exception
	 */
	public function process()
	{
		$this->csrfProtection();

		$config = OSMembershipHelper::getConfig();

		$input = $this->input;

		if (!empty($config->use_email_as_username) && !$this->app->getIdentity()->email)
		{
			$input->post->set('username', $input->post->getString('email'));
		}

		if (!$input->post->has('first_name') && !$input->post->has('last_name'))
		{
			$input->post->set('first_name', $input->post->getString('email'));
		}

		// Validate captcha
		$errorMessage = '';

		if (!$this->validateCaptcha($input, $errorMessage))
		{
			$this->app->enqueueMessage($errorMessage, 'warning');
			$this->displayJoinGroupForm($input);

			return;
		}

		/**@var OSMembershipModelGroup $model * */
		$model = $this->getModel();

		// Validate and make sure the group is valid and still allow adding member
		$errors = $model->validate($input);

		if (count($errors))
		{
			// Enqueue the error messages
			foreach ($errors as $error)
			{
				$this->app->enqueueMessage($error, 'error');
			}

			$this->displayJoinGroupForm($input);

			return;
		}

		// OK, data validation success, process adding member to group
		try
		{
			$model->addGroupMember($input);

			$subscriptionCode = $input->getString('subscription_code');
			$joinGroupLink    = OSMembershipHelperRoute::getViewRoute(
					'group',
					$this->app->input->getInt('Itemid')
				) . '&subscription_code=' . $subscriptionCode . '&layout=complete';
			$this->app->redirect(Route::_($joinGroupLink, false));
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
			$this->displayJoinGroupForm($input);

			return;
		}
	}

	/**
	 * Display Join Group form when data validation failed
	 *
	 * @param   MPFInput  $input
	 */
	protected function displayJoinGroupForm($input)
	{
		$input->set('view', 'group');
		$input->set('layout', 'default');
		$input->set('group_id', $input->getString('group_id', ''));
		$input->set('validation_error', 1);
		$this->display();
	}
}
