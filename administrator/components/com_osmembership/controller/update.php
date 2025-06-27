<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;

class OSMembershipControllerUpdate extends MPFController
{
	/**
	 * Update db scheme when users upgrade from old version to new version
	 *
	 * @return void
	 */
	public function update()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/install.osmembership.php';

		com_osmembershipInstallerScript::createTablesIfNotExist();
		com_osmembershipInstallerScript::synchronizeDBSchema();
		com_osmembershipInstallerScript::setupDefaultData();
		com_osmembershipInstallerScript::enableRequiredPlugin('update');
		com_osmembershipInstallerScript::createIndexes();

		if (is_file(JPATH_ADMINISTRATOR . '/manifests/packages/pkg_osmembership.xml'))
		{
			// Insert update site
			$tmpInstaller = new Installer();
			$tmpInstaller->setPath('source', JPATH_ADMINISTRATOR . '/manifests/packages');
			$file     = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_osmembership.xml';
			$manifest = $tmpInstaller->isManifest($file);

			if (!is_null($manifest))
			{
				/* @var DatabaseDriver $db */
				$db    = Factory::getContainer()->get('db');
				$query = $db->getQuery(true)
					->select($db->quoteName('extension_id'))
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('name') . ' = ' . $db->quote($manifest->name))
					->where($db->quoteName('type') . ' = ' . $db->quote($manifest['type']))
					->where($db->quoteName('state') . ' != -1');
				$db->setQuery($query);

				$eid = (int) $db->loadResult();

				if ($eid && $manifest->updateservers)
				{
					// Set the manifest object and path
					$tmpInstaller->manifest = $manifest;
					$tmpInstaller->setPath('manifest', $file);

					// Load the extension plugin (if not loaded yet).
					PluginHelper::importPlugin('extension', 'joomla');

					// Fire the onExtensionAfterUpdate
					$this->app->triggerEvent('onExtensionAfterUpdate', ['installer' => $tmpInstaller, 'eid' => $eid]);
				}
			}
		}

		if (Multilanguage::isEnabled())
		{
			OSMembershipHelper::callOverridableHelperMethod('Helper', 'setupMultilingual');
		}

		$this->setRedirect(
			'index.php?option=com_osmembership&view=dashboard',
			'Successfully updating database schema to latest version'
		);
	}
}
