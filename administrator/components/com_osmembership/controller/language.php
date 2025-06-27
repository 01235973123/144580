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

class OSMembershipControllerLanguage extends OSMembershipController
{
	public function save()
	{
		$data = $this->input->getData();
		$model = $this->getModel();
		$model->save($data);

		$task = $this->getTask();
		$this->setMessage(Text::_('Translation Saved'));

		if ($task == 'apply')
		{
			$lang = $data['filter_language'];
			$item = $data['filter_item'];
			$this->setRedirect(
				'index.php?option=com_osmembership&view=language&filter_language=' . $lang . '&filter_item=' . $item
			);
		}
		else
		{
			$this->setRedirect('index.php?option=com_osmembership&view=language&view=dashboard');
		}
	}

	/**
	 * Cancel language items editing, redirect to dashboard page
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_osmembership&view=dashboard');
	}
}
