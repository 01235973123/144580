<?php

defined('_JEXEC') or die;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

class plgInstallerJdonation extends CMSPlugin
{
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		$uri = Uri::getInstance($url);
		$host       = $uri->getHost();
		$validHosts = array('joomdonation.com','www.joomdonation.com');
		if (!in_array($host, $validHosts))
		{
			return true;
		}
		$documentId = $uri->getVar('document_id');
		if ($documentId != 55)
		{
			return true;
		}
		// Get Download ID and append it to the URL
		require_once JPATH_ROOT . '/components/com_jdonation/helper/helper.php';
		$config = DonationHelper::getConfig();
		// Append the Download ID to the download URL
		if (!empty($config->download_id))
		{
			$uri->setVar('download_id', $config->download_id);
			$url = $uri->toString();
			// Append domain to URL for logging
			$siteUri = Uri::getInstance();
			$uri->setVar('domain', $siteUri->getHost());
			$uri->setVar('version', DonationHelper::getInstalledVersion());
			$url = $uri->toString();
		}
		return true;
	}
}
?>
