<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

class plgInstallerEShop extends CMSPlugin
{
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		$uri        = Uri::getInstance($url);
		$host       = $uri->getHost();
		$validHosts = ['joomdonation.com'];
		if (!in_array($host, $validHosts))
		{
			return true;
		}
		$documentId = $uri->getVar('document_id');
		if ($documentId != 145)
		{
			return true;
		}
		// Get Download ID and append it to the URL
		require_once JPATH_ROOT . '/components/com_eshop/helpers/helper.php';
		// Append the Download ID to the download URL
		if (EShopHelper::getConfigValue('download_id') != '')
		{
			$uri->setVar('download_id', EShopHelper::getConfigValue('download_id'));
			$url = $uri->toString();
			// Append domain to URL for logging
			$siteUri = Uri::getInstance();
			$uri->setVar('domain', $siteUri->getHost());
			$uri->setVar('php_version', PHP_VERSION);
			$uri->setVar('joomla_version', JVERSION);
			$uri->setVar('version', EShopHelper::getInstalledVersion());
			$url = $uri->toString();
		}

		return true;
	}
}