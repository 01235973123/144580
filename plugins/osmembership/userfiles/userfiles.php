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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgOSMembershipUserfiles extends CMSPlugin implements SubscriberInterface
{
	use MPFEventResult;

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	public static function getSubscribedEvents(): array
	{
		return [
			'onProfileDisplay' => 'onProfileDisplay',
		];
	}

	/**
	 * Render setting form
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onProfileDisplay(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		ob_start();
		$this->drawFiles($row);
		$form = ob_get_contents();
		ob_end_clean();

		$result = [
			'title' => Text::_('OSM_MY_FILES'),
			'form'  => $form,
		];

		$this->addResult($event, $result);
	}

	/**
	 * Display Display List of Documents which the current subscriber can download from his subscription
	 *
	 * @param   object  $row
	 */
	private function drawFiles($row)
	{
		JLoader::register('OSMembershipModelUserfiles', JPATH_ROOT . '/components/com_osmembership/model/userfiles.php');

		/* @var OSMembershipModelUserfiles $model */
		$model = MPFModel::getTempInstance('Userfiles', 'OSMembershipModel');

		[$path, $files] = $model->getData();

		if ($this->params->get('sort_direction'))
		{
			rsort($files);
		}

		$Itemid = $this->app->input->getInt('Itemid');

		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'default');
	}
}
