<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class DonationControllerLanguage extends DonationController
{

	public function save()
	{
		$data  = $this->input->getData();
		$model = $this->getModel();
		$model->save($data);
        $limitstart			= $data['limitstart'];
        $limit				= $data['limit'];
        $search				= $data['search'];
        $site               = $data['site'];
		$this->setRedirect('index.php?option=com_jdonation&view=language&limitstart='.$limitstart.'&limit=100&&search='.$search.'&site='.$site);
	}

	/**
	 * Cancel editing translation, redirect to dashboard page
	 *
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_jdonation&view=dashboard');
	}
}