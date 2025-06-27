<?php
use Joomla\CMS\Factory;
/**
 * @version    SVN: <svn_id>
 * @package    JDonation
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2023 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');

/**
 * IntegrationsHelper form controller class.
 *
 * @package     JGive
 * @subpackage  com_jdonation
 * @since       1.6.7
 */
class JdontionIntegrationsHelper
{

	/** Function for  load Script
	 *
	 * @param   File  $script  Script
	 *
	 * @return  void
	 */
	public function loadScriptOnce($script)
	{
		$doc = Factory::getApplication()->getDocument();
		$flg = 0;

		foreach ($doc->_scripts as $name => $ar)
		{
			if ($name == $script)
			{
				$flg = 1;
			}
		}

		if ($flg == 0)
		{
			DonationHelper::addScript($script);
		}
	}
}
