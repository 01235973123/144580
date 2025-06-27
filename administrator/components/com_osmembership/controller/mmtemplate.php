<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class OSMembershipControllerMmtemplate extends OSMembershipController
{
	/**
	 * Method to get mass mail template message
	 *
	 * @return void
	 */
	public function getMMTempalteMessage()
	{
		/* @var MPFModelAdmin $model */
		$model      = $this->getModel('Mmtemplate');
		$mmTemplate = $model->getData();

		echo $mmTemplate ? $mmTemplate->message : '';

		$this->app->close();
	}
}
