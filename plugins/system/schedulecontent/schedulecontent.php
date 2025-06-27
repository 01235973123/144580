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
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php'))
{
	return;
}

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

class plgSystemScheduleContent extends CMSPlugin implements SubscriberInterface
{
	use MPFEventResult;

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var \Joomla\Database\DatabaseDriver
	 */
	protected $db;

	/**
	 * Make language files will be loaded automatically.
	 *
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterRoute'                => 'onAfterRoute',
			'onEditSubscriptionPlan'      => 'onEditSubscriptionPlan',
			'onAfterSaveSubscriptionPlan' => 'onAfterSaveSubscriptionPlan',
			'onContentPrepare'            => 'onContentPrepare',
			'onProfileDisplay'            => 'onProfileDisplay',
		];
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

		parent::registerListeners();
	}

	/**
	 * Render setting form
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onEditSubscriptionPlan(Event $event): void
	{
		/* @var OSMembershipTablePlan $row */
		[$row] = array_values($event->getArguments());

		if (!$this->isExecutable())
		{
			return;
		}

		ob_start();
		$this->drawSettingForm($row);
		$form = ob_get_contents();
		ob_end_clean();

		$result = [
			'title' => Text::_('OSM_SCHEULE_CONTENT_MANAGER'),
			'form'  => $form,
		];

		$this->addResult($event, $result);
	}

	/**
	 * Store setting into database, in this case, use params field of plans table
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onAfterSaveSubscriptionPlan(Event $event): void
	{
		/**
		 * @var string                $context
		 * @var OSMembershipTablePlan $row
		 * @var array                 $data
		 * @var                       $isNew
		 */
		[$context, $row, $data, $isNew] = array_values($event->getArguments());

		if (!$this->isExecutable())
		{
			return;
		}

		$scheduleContents   = $data['schedulecontent'] ?? [];
		$scheduleContentIds = [];
		$ordering           = 1;

		foreach ($scheduleContents as $scheduleContent)
		{
			if (empty($scheduleContent['article_id']))
			{
				continue;
			}


			$rowScheduleContent = new OSMembershipTableSchedulecontent($this->db);

			$rowScheduleContent->bind($scheduleContent);

			// Prevent item being moved to new plan on save as copy

			if ($isNew)
			{
				$rowScheduleContent->id = 0;
			}

			$rowScheduleContent->plan_id  = $row->id;
			$rowScheduleContent->ordering = $ordering++;
			$rowScheduleContent->store();
			$scheduleContentIds[] = $rowScheduleContent->id;
		}

		if (!$isNew)
		{
			$db    = $this->db;
			$query = $db->getQuery(true);
			$query->delete('#__osmembership_schedulecontent')
				->where('plan_id = ' . $row->id);

			if (count($scheduleContentIds))
			{
				$query->whereNotIn('id', $scheduleContentIds);
			}

			$db->setQuery($query)
				->execute();
		}
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
		$this->drawScheduleContent($row);
		$form = ob_get_contents();
		ob_end_clean();

		$result = [
			'title' => Text::_('OSM_MY_SCHEDULE_CONTENT'),
			'form'  => $form,
		];

		$this->addResult($event, $result);
	}

	/**
	 * Set access-view for schedule article
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onContentPrepare(Event $event): void
	{
		[$context, $article, $params, $page] = array_values($event->getArguments());

		if ($context != 'com_content.article')
		{
			return;
		}

		// Narrow down access
		if ($params->get('access-view'))
		{
			$params->set('access-view', $this->canAccess($article->id));
		}
	}

	/**
	 * Method to check if current user can access to schedule article
	 *
	 * @param   int  $articleId
	 *
	 * @return  bool
	 */
	private function canAccess($articleId)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_schedulecontent')
			->where('article_id = ' . $articleId);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (empty($rows))
		{
			return true;
		}

		$releaseArticleOlderThanXDays = (int) $this->params->get('release_article_older_than_x_days', 0);

		if ($releaseArticleOlderThanXDays > 0)
		{
			$query->clear()
				->select('*')
				->from('#__content')
				->where('id = ' . $articleId);
			$db->setQuery($query);
			$rowArticle = $db->loadObject();

			if ($rowArticle->publish_up && $rowArticle->publish_up != $db->getNullDate())
			{
				$publishedDate = $rowArticle->publish_up;
			}
			else
			{
				$publishedDate = $rowArticle->created;
			}

			$today         = Factory::getDate();
			$publishedDate = Factory::getDate($publishedDate);
			$numberDays    = $publishedDate->diff($today)->days;

			// This article is older than configured number of days, it can be accessed for free
			if ($today >= $publishedDate && $numberDays >= $releaseArticleOlderThanXDays)
			{
				return true;
			}
		}

		$subscriptions = OSMembershipHelper::callOverridableHelperMethod('Subscription', 'getUserSubscriptionsInfo');

		foreach ($rows as $row)
		{
			if (isset($subscriptions[$row->plan_id]))
			{
				$subscription = $subscriptions[$row->plan_id];

				if ($subscription->active_in_number_days >= $row->number_days)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Protect access to articles
	 *
	 * @return void
	 * @throws Exception
	 */
	public function onAfterRoute(Event $event): void
	{
		if (!$this->app->isClient('site'))
		{
			return;
		}

		$user = $this->app->getIdentity();

		if ($user->authorise('core.admin'))
		{
			return;
		}

		$option = $this->app->input->getCmd('option');
		$view   = $this->app->input->getCmd('view');

		if ($option != 'com_content' || $view != 'article')
		{
			return;
		}

		$articleId = $this->app->input->getInt('id');

		if (!$this->canAccess($articleId))
		{
			if (!$user->id)
			{
				// Redirect user to login page
				$this->app->redirect(Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Uri::getInstance()->toString())));
			}
			else
			{
				OSMembershipHelper::loadLanguage();

				if ($redirectMenuItem = $this->params->get('redirect_menu_item'))
				{
					if (Multilanguage::isEnabled())
					{
						$associations = Associations::getAssociations('com_menus', '#__menu', 'com_menus.item', $redirectMenuItem, 'id', '', '');
						$langCode     = Factory::getApplication()->getLanguage()->getTag();

						if (isset($associations[$langCode]))
						{
							$redirectMenuItem = $associations[$langCode]->id;
						}

						$this->app->enqueueMessage(Text::_('OSM_SCHEDULE_CONTENT_LOCKED'), 'warning');
						$this->app->redirect(Route::_('index.php?Itemid=' . $redirectMenuItem));
					}
				}
				else
				{
					throw new Exception(Text::_('OSM_SCHEDULE_CONTENT_LOCKED'), 403);
				}
			}
		}
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   OSMembershipTablePlan  $row
	 */
	private function drawSettingForm($row)
	{
		$numberArticlesEachTime      = $this->params->get('number_new_articles_each_time', 10);
		$form                        = Form::getInstance('schedulecontent', JPATH_ROOT . '/plugins/system/schedulecontent/form/schedulecontent.xml');
		$formData['schedulecontent'] = [];

		// Load existing schedule articles for this plan
		if ($row->id)
		{
			$db    = $this->db;
			$query = $db->getQuery(true)
				->select('*')
				->from('#__osmembership_schedulecontent')
				->where('plan_id = ' . $row->id)
				->order('ordering');
			$db->setQuery($query);

			foreach ($db->loadObjectList() as $scheduleContent)
			{
				$formData['schedulecontent'][] = [
					'id'           => $scheduleContent->id,
					'article_id'   => $scheduleContent->article_id,
					'number_days'  => $scheduleContent->number_days,
					'release_date' => $scheduleContent->release_date ?? '',
				];
			}
		}

		for ($i = 0; $i < $numberArticlesEachTime; $i++)
		{
			$formData['schedulecontent'][] = [
				'id '          => 0,
				'article_id'   => 0,
				'number_days'  => '',
				'release_date' => '',
			];
		}

		$form->bind($formData);

		foreach ($form->getFieldset() as $field)
		{
			echo $field->input;
		}
	}

	/**
	 * Display Display List of Documents which the current subscriber can download from his subscription
	 *
	 * @param   object  $row
	 */
	private function drawScheduleContent($row)
	{
		$subscriptions = OSMembershipHelper::callOverridableHelperMethod('Subscription', 'getUserSubscriptionsInfo');

		if (empty($subscriptions))
		{
			return;
		}

		$accessiblePlanIds = array_keys($subscriptions);

		$db    = $this->db;
		$query = $db->getQuery(true);
		$query->select('a.id, a.catid, a.title, a.alias, a.hits, a.created, a.publish_up, c.title AS category_title, b.plan_id, b.number_days')
			->from('#__content AS a')
			->innerJoin('#__categories AS c ON a.catid = c.id')
			->innerJoin('#__osmembership_schedulecontent AS b ON a.id = b.article_id')
			->whereIn('b.plan_id', $accessiblePlanIds)
			->where('a.state = 1')
			->order('plan_id')
			->order('b.number_days')
			->order('b.ordering');
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if (empty($items))
		{
			return;
		}

		echo OSMembershipHelperHtml::loadCommonLayout(
			'plugins/tmpl/schedulecontent.php',
			['items' => $items, 'subscriptions' => $subscriptions, 'params' => $this->params]
		);
	}

	/**
	 * Method to check if the plugin is executable
	 *
	 * @return bool
	 */
	private function isExecutable()
	{
		if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		return true;
	}
}
