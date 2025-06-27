<?php
/**
 * @package            Joomla
 * @subpackage         Membership Pro
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2012 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

trait OSMembershipControllerDisplay
{
	/**
	 * Method to display a view
	 *
	 * @param   bool   $cachable
	 * @param   array  $urlparams
	 *
	 * @return MPFController
	 * @throws Exception
	 */
	public function display($cachable = false, array $urlparams = [])
	{
		$user = $this->app->getIdentity();

		$viewName = $this->input->get('view', $this->config['default_view']);

		$requireLoginViews = ['subscribers', 'subscriber', 'subscriptions', 'subscription', 'mplans', 'mplan'];

		// Ask user to login first if the view requires login
		if (!$user->id && in_array($viewName, $requireLoginViews))
		{
			OSMembershipHelper::requestLogin($viewName);
		}

		switch ($viewName)
		{
			case 'subscribers':
			case 'subscriber':
				if (!$user->authorise('membershippro.subscriptions', 'com_osmembership'))
				{
					throw new Exception(Text::_('OSM_DO_NOT_HAVE_SUBSCRIPTIONS_MANAGEMENT_PERMISSION'), 403);
				}
				break;
			case 'mplans':
				if (!$user->authorise('membershippro.plans', 'com_osmembership'))
				{
					throw new Exception(Text::_('OSM_DO_NOT_HAVE_PLANS_MANAGEMENT_PERMISSION'), 403);
				}
				break;
			case 'mplan':
				$id         = $this->input->getInt('id', 0);
				$data['id'] = $id;

				if ($id)
				{
					$canDo = $this->allowEdit($data);
				}
				else
				{
					$canDo = $this->allowAdd($data);
				}

				if (!$user->authorise('membershippro.plans', 'com_osmembership') || !$canDo)
				{
					throw new Exception(Text::_('OSM_DO_NOT_HAVE_PLANS_MANAGEMENT_PERMISSION'), 403);
				}

				break;
		}

		$config = OSMembershipHelper::getConfig();

		$wa = $this->app->getDocument()
			->getWebAssetManager();

		if ($config->load_twitter_bootstrap_in_frontend !== '0'
			&& in_array($config->get('twitter_bootstrap_version', 2), [2, 5]))
		{
			$wa->useStyle('bootstrap.css');
		}

		$wa->registerAndUseStyle(
			'com_osmembership.style',
			'media/com_osmembership/assets/css/style.min.css',
			['version' => OSMembershipHelper::getInstalledVersion()]
		);

		$customCssFile = JPATH_ROOT . '/media/com_osmembership/assets/css/custom.css';

		if (file_exists($customCssFile) && filesize($customCssFile) > 0)
		{
			$wa->registerAndUseStyle(
				'com_osmembership.custom',
				'media/com_osmembership/assets/css/custom.css',
				['version' => filemtime($customCssFile)]
			);
		}

		$requireJQueryViews = [
			'register',
			'payment',
			'card',
			'group',
			'groupmember',
			'profile',
			'subscriber',
			'renewmembership',
			'upgrademembership',
		];

		if (in_array($viewName, $requireJQueryViews))
		{
			OSMembershipHelperJquery::loadjQuery();
		}

		return parent::display($cachable, $urlparams);
	}
}
