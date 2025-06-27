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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseDriver;

class OSMembershipControllerProfile extends OSMembershipController
{
	use MPFControllerDownload;

	/**
	 * Update user profile data
	 */
	public function update()
	{
		$Itemid = $this->input->getInt('Itemid', 0);

		$config = OSMembershipHelper::getConfig();

		if (!empty($config->use_email_as_username) && $this->input->has('email'))
		{
			$this->input->set('username', $this->input->getString('email'));
		}

		/**@var OSMembershipModelProfile $model * */
		$model  = $this->getModel();
		$errors = $model->validateProfileData($this->input);

		if (count($errors))
		{
			foreach ($errors as $error)
			{
				$this->app->enqueueMessage($error, 'error');
			}

			$this->input->set('view', 'profile');
			$this->input->set('layout', 'default');
			$this->display();

			return;
		}

		$data = $this->input->getData();

		$data['id'] = (int) $data['cid'][0];

		try
		{
			$model->updateProfile($data, $this->input);
			$message = Text::_('OSM_YOUR_PROFILE_UPDATED');
			$type    = 'message';
		}
		catch (Exception $e)
		{
			$message = $e->getMessage();
			$type    = 'error';
		}

		//Redirect to the profile page
		$this->setRedirect(
			Route::_('index.php?option=com_osmembership&view=profile&Itemid=' . $Itemid, false),
			$message,
			$type
		);
	}

	/**
	 * Update subscription credit card
	 */
	public function update_card()
	{
		$this->csrfProtection();

		$Itemid = $this->input->getInt('Itemid', 0);
		$data   = $this->input->post->getData();

		/**@var OSMembershipModelProfile $model * */
		$model = $this->getModel();

		try
		{
			$model->updateCard($data);
			$message = Text::_('OSM_CREDITCARD_UPDATED');
			$type    = 'message';
		}
		catch (Exception $e)
		{
			$message = $e->getMessage();
			$type    = 'error';
		}

		//Redirect to the profile page
		$this->setRedirect(
			Route::_('index.php?option=com_osmembership&view=profile&Itemid=' . $Itemid),
			$message,
			$type
		);
	}

	/**
	 * Download member card
	 */
	public function download_member_card()
	{
		$config = OSMembershipHelper::getConfig();
		$user   = $this->app->getIdentity();

		if (!$config->activate_member_card_feature)
		{
			throw new Exception(
				'This feature is not enabled. If you are administrator and want to use it, go to Membership Pro -> Configuration to enable this feature',
				403
			);
		}

		$item = OSMembershipHelperSubscription::getMembershipProfile($user->id);

		if (!$item)
		{
			$this->setRedirect(
				Route::_('index.php?option=com_osmembesrhip&view=profile'),
				Text::_(
					'You need to subscribe for at least one subscription plan in our system to download member card'
				)
			);

			return;
		}

		// Generate member card and save it
		$path = OSMembershipHelper::callOverridableHelperMethod('Subscription', 'generateMemberCard', [$item, $config]);

		$this->processDownloadFile($path, $path, $item->username . '.pdf');
	}

	/**
	 * Download member card
	 */
	public function download_member_plan_card()
	{
		$config = OSMembershipHelper::getConfig();
		$user   = $this->app->getIdentity();

		if (!$config->activate_member_card_feature)
		{
			throw new Exception(
				'This feature is not enabled. If you are administrator and want to use it, go to Membership Pro -> Configuration to enable this feature',
				403
			);
		}

		$item = OSMembershipHelperSubscription::getMembershipProfile($user->id);

		if (!$item)
		{
			$this->setRedirect(
				Route::_('index.php?option=com_osmembership&view=profile'),
				Text::_(
					'You need to subscribe for at least one subscription plan in our system to download member card'
				)
			);

			return;
		}

		$planId = $this->input->getInt('plan_id');

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('a.*, b.username')
			->from('#__osmembership_subscribers AS a')
			->innerJoin('#__users AS b ON a.user_id = b.id')
			->where('a.user_id = ' . $user->id)
			->where('a.plan_id = ' . $planId)
			->where('published IN (1, 2)')
			->order('id');
		$db->setQuery($query);
		$item = $db->loadObject();

		if (!$item)
		{
			$this->setRedirect(
				Route::_('index.php?option=com_osmembership&view=profile'),
				Text::_('You need to have a valid subscription of this plan download member card')
			);

			return;
		}

		// Generate member card and save it
		$path = OSMembershipHelper::callOverridableHelperMethod(
			'Subscription',
			'generatePlanMemberCard',
			[$item, $config]
		);

		$this->processDownloadFile($path, $item->username . '_' . $item->plan_id . '.pdf');
	}
}
