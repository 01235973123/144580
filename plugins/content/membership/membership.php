<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgContentMembership extends CMSPlugin implements SubscriberInterface
{
	public static function getSubscribedEvents(): array
	{
		return [
			'onContentAfterSave'   => 'onContentAfterSave',
			'onContentPrepareData' => 'onContentPrepareData',
			'onContentPrepareForm' => 'onContentPrepareForm',
		];
	}

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var \Joomla\Database\DatabaseDriver
	 */
	protected $db;

	/**
	 * @param   Event  $event
	 *
	 * @return    void
	 */
	public function onContentPrepareForm(Event $event): void
	{
		/**
		 * @var Form  $form
		 * @var array $data
		 */
		[$form, $data] = array_values($event->getArguments());

		$name = $form->getName();

		if ($name == 'com_content.article')
		{
			Form::addFormPath(dirname(__FILE__) . '/form');
			$form->loadFile('membership', false);
		}

		Factory::getApplication()->getLanguage()->load('com_osmembership', JPATH_ADMINISTRATOR);
	}

	/**
	 * @param   Event  $event
	 *
	 * @return void
	 * @throws Exception
	 */
	public function onContentAfterSave(Event $event): void
	{
		[$context, $article, $isNew] = array_values($event->getArguments());

		if ($context != 'com_content.article')
		{
			return;
		}

		$articleId = $article->id;
		$data      = $this->app->input->get('jform', [], 'array');

		if ($articleId)
		{
			try
			{
				$db    = $this->db;
				$query = $db->getQuery(true);
				$query->delete('#__osmembership_articles');
				$query->where('article_id = ' . $db->Quote($articleId));
				$db->setQuery($query);
				$db->execute();

				if (!empty($data['plan_ids']))
				{
					$query->clear()
						->insert('#__osmembership_articles')
						->columns('plan_id,article_id');

					foreach ($data['plan_ids'] as $planId)
					{
						$query->values("$planId, $articleId");
					}

					$db->setQuery($query);
					$db->execute();
				}
			}
			catch (Exception $e)
			{
				$this->_subject->setError($e->getMessage());

				return;
			}
		}
	}

	/**
	 * @param   Event  $event
	 *
	 * @return    void
	 */
	public function onContentPrepareData(Event $event): void
	{
		/**
		 * @var string $context
		 * @var object $data
		 */
		[$context, $data] = array_values($event->getArguments());

		if ($context != 'com_content.article' || !is_object($data))
		{
			return;
		}

		$articleId = $data->id ?? 0;

		if ($articleId > 0)
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('plan_id')
				->from('#__osmembership_articles')
				->where('article_id = ' . (int) $articleId);
			$db->setQuery($query);
			$results = $db->loadColumn();
			$data->set('plan_ids', $results);
		}
	}

	/**
	 * Register listeners
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		if (!ComponentHelper::isEnabled('com_osmembership'))
		{
			return;
		}

		if (!$this->app->isClient('administrator'))
		{
			return;
		}

		parent::registerListeners();
	}
}
