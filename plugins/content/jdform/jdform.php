<?php
/**
 * @version        3.7
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2016 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

/**
 * Process Prepare content
 *
 * Method is called by the view
 *
 * @param    object         The article object.  Note $article->text is also available
 * @param    object         The article params
 * @param    int            The 'page' number
 */
class plgContentJdForm extends CMSPlugin
{
	function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		if (Factory::getApplication()->getName() != 'site')
		{
			return;
		}
		if (strpos($article->text, 'jdform') === false)
		{
			return true;
		}
		$regex         = "#{jdform (\d+)}#s";
		$article->text = preg_replace_callback($regex, array($this, '_replaceDonationForm'), $article->text);

		return true;
	}

	/**
	 * Show donation form based on campaign id
	 *
	 * @param unknown_type $matches
	 */
	function _replaceDonationForm($matches)
	{
		error_reporting(0);
		$campaignId = $matches[1];
		include JPATH_ADMINISTRATOR . '/components/com_jdonation/config.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_jdonation/loader.php';
		DonationHelper::loadLanguage();
		$request = array('view' => 'donation', 'campaign_id' => $campaignId, 'content_plugin' => 1, 'Itemid' => DonationHelper::getItemid());
		$input   = new OSFInput($request);
		ob_start();
		//Execute the controller
		OSFController::getInstance('com_jdonation', $input, $jdConfig)->execute();

		return ob_get_clean();
	}
}
