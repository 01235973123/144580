<?php
/**
 * @package            Joomla
 * @subpackage         Membership Pro
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class OSMembershipViewQrcodeHtml extends MPFViewHtml
{
	/**
	 * The flag to mark that this view does not have associate model
	 *
	 * @var bool
	 */
	public $hasModel = false;

	/**
	 * Override prepareView method to check permission
	 *
	 * @throws Exception
	 */
	protected function prepareView()
	{
		$user = Factory::getApplication()->getIdentity();

		if (!$user->authorise('membershippro.subscriptions', 'com_osmembership'))
		{
			if ($user->guest)
			{
				$this->requestLogin();
			}
			else
			{
				$app = Factory::getApplication();
				$app->enqueueMessage(Text::_('NOT_AUTHORIZED'), 'error');
				$app->redirect(Uri::root(), 403);
			}
		}

		parent::prepareView();
	}
}
