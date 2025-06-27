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

/**
 * OSMembership Plugin controller
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipControllerPlugin extends OSMembershipController
{
	/**
	 * Install the payment plugin from selected package
	 */
	public function install()
	{
		$model = $this->getModel('plugin', ['ignore_request' => true]);
		try
		{
			$model->install($this->input);
			$this->setMessage(Text::_('Plugin installed'));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}
		$this->setRedirect($this->getViewListUrl());
	}

	/**
	 * Uninstall the selected payment plugin
	 */
	public function uninstall()
	{
		$model    = $this->getModel('plugin', ['ignore_request' => true]);
		$cid      = $this->input->get('cid', [], 'array');
		$pluginId = (int) $cid[0];
		try
		{
			$model->uninstall($pluginId);
			$this->setMessage(Text::_('The plugin was successfully uninstalled'));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}
		$this->setRedirect($this->getViewListUrl());
	}
}
