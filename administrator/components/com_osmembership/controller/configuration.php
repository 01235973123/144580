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
 * Membership Pro controller
 *
 * @package        Joomla
 * @subpackage     Membership Pro
 */
class OSMembershipControllerConfiguration extends OSMembershipController
{
	/**
	 * Save configuration
	 */
	public function save()
	{
		$post = $this->input->getData(MPF_INPUT_ALLOWRAW);
		unset($post['option'], $post['task'], $post['view']);
		$post['download_id'] = trim($post['download_id']);
		$model               = $this->getModel('configuration', ['ignore_request' => true]);
		$model->store($post);
		$msg  = Text::_('OSM_CONFIGURATION_SAVED');
		$task = $this->getTask();

		if ($task == 'apply')
		{
			$this->setRedirect('index.php?option=com_osmembership&view=configuration', $msg);
		}
		else
		{
			$this->setRedirect('index.php?option=com_osmembership&view=' . $this->config['default_view'], $msg);
		}
	}

	/**
	 * Redirect back to default view afters users cancel an action
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_osmembership&view=' . $this->config['default_view']);
	}
}
