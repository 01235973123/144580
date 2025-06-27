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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

class plgOSMembershipActiveCampaign extends CMSPlugin implements SubscriberInterface
{
	use MPFEventResult;

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
	 * Make language files will be loaded automatically.
	 *
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	/**
	 * API URL
	 *
	 * @var string
	 */
	private $apiUrl;

	/**
	 * API Token
	 *
	 * @var string
	 */
	private $apiToken;

	public static function getSubscribedEvents(): array
	{
		return [
			'onEditSubscriptionPlan'             => 'onEditSubscriptionPlan',
			'onAfterSaveSubscriptionPlan'        => 'onAfterSaveSubscriptionPlan',
			'onMembershipActive'                 => 'onMembershipActive',
			'onMembershipExpire'                 => 'onMembershipExpire',
			'onAfterCancelRecurringSubscription' => 'onAfterCancelRecurringSubscription',
		];
	}

	/**
	 * Active Campaign constructor.
	 *
	 * @param   \Joomla\Event\DispatcherInterface  $subject
	 * @param   array                              $config
	 */
	public function __construct($subject, $config = [])
	{
		parent::__construct($subject, $config);

		$this->apiUrl   = $this->params->get('api_url', '');
		$this->apiToken = $this->params->get('api_token', '');
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
		$form = ob_get_clean();

		$result = [
			'title' => Text::_('PLG_OSMEMBERSHIP_ACTIVECAMPAGIN_INTEGRATION'),
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

		$params = new Registry($row->params);

		// Prevent PHP notices/warnings
		$keys = [
			'active_list_ids',
			'active_tag_ids',
			'expired_list_ids',
			'expired_tag_ids',
			'cancel_subscription_tag_ids',
		];

		foreach ($keys as $key)
		{
			$params->set($key, implode(',', $data[$key] ?? []));
		}

		$row->params = $params->toString();
		$row->store();
	}

	/**
	 * Run when a membership activated
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipActive(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		if ($row->group_admin_id > 0 && $this->params->get('subscribe_group_members', '1') == '0')
		{
			return;
		}

		$config = OSMembershipHelper::getConfig();

		// In case subscriber doesn't want to subscribe to newsleter, stop
		if ($config->show_subscribe_newsletter_checkbox && empty($row->subscribe_newsletter))
		{
			return;
		}

		$plan = new OSMembershipTablePlan($this->db);
		$plan->load($row->plan_id);
		$params = new Registry($plan->params);

		$listIds = $params->get('active_list_ids', '');
		$tagIds  = $params->get('active_tag_ids', '');

		if ($listIds || $tagIds)
		{
			// First, we need to subscriber to Active Campaign Contact
			$contactData = $this->getContactData($row);
			$response    = $this->makePostRequest('contact/sync', ['contact' => $contactData]);

			if ($response === false)
			{
				return;
			}

			$contact = $response['contact'];

			// Store Contact ID to retrieve it later
			$this->storeContactID($row, $contact['id']);

			// Add lists to contact
			if ($listIds)
			{
				$this->addListsToContact($listIds, $contact['id']);
			}

			// Add tags to contact
			if ($tagIds)
			{
				$this->addTagsToContact($tagIds, $contact['id']);
			}
		}
	}

	/**
	 * Run when a membership expiried die
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipExpire(Event $event): void
	{
		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		if ($row->group_admin_id > 0 && $this->params->get('subscribe_group_members', '1') == '0')
		{
			return;
		}

		if ($row->user_id)
		{
			$activePlans = OSMembershipHelperSubscription::getActiveMembershipPlans($row->user_id, [$row->id]);

			// He renewed his subscription before, so don't remove him from the lists
			if (in_array($row->plan_id, $activePlans))
			{
				return;
			}
		}

		$plan = new OSMembershipTablePlan($this->db);
		$plan->load($row->plan_id);
		$params  = new Registry($plan->params);
		$listIds = trim($params->get('expired_list_ids', ''));
		$tagIds  = trim($params->get('expired_tag_ids', ''));

		if ($listIds || $tagIds)
		{
			$contactId = $this->getContactId($row);

			if ($contactId)
			{
				// Remove lists from contact
				if ($listIds)
				{
					$this->removeListsFromContact($listIds, $contactId);
				}

				if ($tagIds)
				{
					$this->removeTagsFromContact($tagIds, $contactId);
				}
			}
		}
	}

	/**
	 * Remove tags when users cancel their subscription
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onAfterCancelRecurringSubscription(Event $event): void
	{
		[$row] = array_values($event->getArguments());

		$plan = new OSMembershipTablePlan($this->db);
		$plan->load($row->plan_id);
		$params = new Registry($plan->params);
		$tagIds = trim($params->get('cancel_subscription_tag_ids', ''));

		if ($tagIds)
		{
			$contactId = $this->getContactId($row);

			if ($contactId)
			{
				$this->removeTagsFromContact($tagIds, $contactId);
			}
		}
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

	/**
	 * Add lists to contact
	 *
	 * @param   string  $listIds
	 * @param   int     $contactId
	 */
	private function addListsToContact($listIds, $contactId)
	{
		$listIds = explode(',', $listIds);

		foreach ($listIds as $listId)
		{
			$contactList = [
				'contactList' => [
					'list'    => $listId,
					'status'  => 1,
					'contact' => $contactId,
				],
			];

			$this->makePostRequest('contactLists', $contactList);
		}
	}

	/**
	 * Remove lists from contact
	 *
	 * @param   string  $listIds
	 * @param   int     $contactId
	 */
	private function removeListsFromContact($listIds, $contactId)
	{
		$listIds = explode(',', $listIds);

		foreach ($listIds as $listId)
		{
			$contactList = [
				'contactList' => [
					'list'    => $listId,
					'status'  => 2,
					'contact' => $contactId,
				],
			];

			$this->makePostRequest('contactLists', $contactList);
		}
	}

	/**
	 * Add tags to contact
	 *
	 * @param   string  $tagIds
	 * @param   int     $contactId
	 */
	private function addTagsToContact($tagIds, $contactId)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);

		$tagIds = explode(',', $tagIds);

		foreach ($tagIds as $tagId)
		{
			// Delete old data from database
			$query->delete('#__osmembership_activecampaign')
				->where('tag_id = ' . $tagId)
				->where('contact_id = ' . (int) $contactId);
			$db->setQuery($query)
				->execute();

			// Add tag to contact
			$contactTag = [
				'contactTag' => [
					'contact' => $contactId,
					'tag'     => $tagId,
				],
			];

			$response = $this->makePostRequest('contactTags', $contactTag);

			if ($response !== false)
			{
				$contactTagId = $response['contactTag']['id'];

				$query->clear()
					->insert('#__osmembership_activecampaign')
					->columns($db->quoteName(['contact_id', 'tag_id', 'contact_tag_id']))
					->values(implode(',', $db->quote([$contactId, $tagId, $contactTagId])));
				$db->setQuery($query)
					->execute();
			}
		}
	}

	/**
	 * Remove tags from contact
	 *
	 * @param   string  $tagIds
	 * @param   int     $contactId
	 */
	private function removeTagsFromContact($tagIds, $contactId)
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_activecampaign')
			->where('contact_id = ' . (int) $contactId);
		$db->setQuery($query);
		$contactTags = $db->loadObjectList('tag_id');

		$tagIds = explode(',', $tagIds);

		foreach ($tagIds as $tagId)
		{
			if (isset($contactTags[$tagId]))
			{
				$contactTagId = $contactTags[$tagId]->contact_tag_id;

				$this->makeDeleteRequest('contactTags/' . $contactTagId);
			}
		}
	}

	/**
	 * Get Contact ID for a given subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return int
	 */
	private function getContactId($row)
	{
		/* @var \Joomla\Database\DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('params')
			->from('#__osmembership_subscribers');

		if ($row->user_id > 0)
		{
			$query->where('(user_id = ' . $row->user_id . ' OR email = ' . $db->quote($row->email) . ')');
		}
		else
		{
			$query->where('email = ' . $db->quote($row->email));
		}

		$db->setQuery($query);
		$subscriptions = $db->loadObjectList();

		foreach ($subscriptions as $subscription)
		{
			$params = new Registry($subscription->params);

			$contactId = (int) $params->get('ac_contact_id');

			if ($contactId)
			{
				return $contactId;
			}
		}

		return 0;
	}

	/***
	 * Get data to create update contact in Active Campaign
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	private function getContactData($row)
	{
		JLoader::register('OSMembershipModelApi', JPATH_ROOT . '/components/com_osmembership/model/api.php');

		/* @var OSMembershipModelApi $model */
		$model = MPFModel::getTempInstance('Api', 'OSMembershipModel');

		// Get custom fields data
		$data = $model->getSubscriptionData($row->id);

		$contactData = [
			'email'     => $row->email,
			'firstName' => $row->first_name,
			'lastName'  => $row->last_name,
			'phone'     => $row->phone,
		];

		$fieldValues = [];

		foreach ($this->params->get('ac_field_mapping', []) as $fieldMapping)
		{
			if (strlen($fieldMapping->osm_field) && strlen($fieldMapping->ac_field))
			{
				switch ($fieldMapping->osm_field)
				{
					case 'id':
					case 'user_id':
					case 'from_date':
					case 'to_date':
					case 'created_date':
					case 'email':
						$fieldValue = $row->{$fieldMapping->osm_field};
						break;
					case 'username':
						if ($row->user_id)
						{
							$user       = Factory::getUser($row->user_id);
							$fieldValue = $user->username;
						}
						else
						{
							$fieldValue = '';
						}
						break;
					default:
						$fieldValue = $data[$fieldMapping->osm_field] ?? '';
						break;
				}
				$fieldValues[] = [
					'field' => $fieldMapping->ac_field,
					'value' => $fieldValue,
				];
			}
		}

		$contactData['fieldValues'] = $fieldValues;

		return $contactData;
	}

	/**
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   string                       $contactId
	 */
	private function storeContactID($row, $contactId)
	{
		$params = new Registry($row->params);
		$params->set('ac_contact_id', $contactId);
		$row->params = $params->toString();
		$row->store();
	}

	/**
	 * Make get request to ActiveCampaign
	 *
	 * @param   string  $path
	 *
	 * @return  false|array
	 */
	private function makeGetRequest($path)
	{
		if ($this->apiUrl && $this->apiToken)
		{
			$http    = HttpFactory::getHttp();
			$headers = [
				'User-Agent' => 'Membership Pro',
				'Api-Token'  => $this->apiToken,
				'Accept'     => 'application/json',
			];

			$response = $http->get($this->apiUrl . '/' . $path, $headers);

			if ($response->code == 200)
			{
				return json_decode($response->body, true);
			}
		}

		return false;
	}

	/**
	 * Make delete request
	 *
	 * @param   string  $path
	 *
	 * @return bool
	 */
	private function makeDeleteRequest($path)
	{
		if ($this->apiUrl && $this->apiToken)
		{
			$http    = HttpFactory::getHttp();
			$headers = [
				'User-Agent' => 'Membership Pro',
				'Api-Token'  => $this->apiToken,
				'Accept'     => 'application/json',
			];

			$response = $http->delete($this->apiUrl . '/' . $path, $headers);

			if ($response->code == 200)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Make post request to ActiveCampaign
	 *
	 * @param   string  $path
	 * @param   array   $data
	 */
	private function makePostRequest($path, $data)
	{
		if ($this->apiUrl && $this->apiToken)
		{
			$http    = HttpFactory::getHttp();
			$headers = [
				'User-Agent'   => 'Membership Pro',
				'Api-Token'    => $this->apiToken,
				'Content-Type' => 'application/json',
			];

			$response = $http->post($this->apiUrl . '/' . $path, json_encode($data), $headers);

			if (in_array($response->code, [200, 201]))
			{
				return json_decode($response->body, true);
			}
			OSMembershipHelper::logData(
				__DIR__ . '/errors.txt',
				['code' => $response->code, 'body' => $response->body]
			);
		}

		return false;
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   OSMembershipTablePlan  $row
	 */
	private function drawSettingForm($row)
	{
		$listOptions = [];
		$tagOptions  = [];

		$response = $this->makeGetRequest('lists');

		if ($response !== false)
		{
			foreach ($response['lists'] as $list)
			{
				$listOptions[] = HTMLHelper::_('select.option', $list['id'], $list['name']);
			}
		}

		$response = $this->makeGetRequest('tags');

		if ($response !== false)
		{
			foreach ($response['tags'] as $tag)
			{
				$tagOptions[] = HTMLHelper::_('select.option', $tag['id'], $tag['tag']);
			}
		}

		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'form');
	}
}
