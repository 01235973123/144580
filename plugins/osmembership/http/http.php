<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

defined('_JEXEC') or die;

class plgOSMembershipHttp extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Database object.
	 *
	 * @var DatabaseDriver
	 */
	protected $db;

	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterStoreSubscription' => 'onAfterStoreSubscription',
			'onMembershipActive'       => 'onMembershipActive',
			'onMembershipExpire'       => 'onMembershipExpire',
		];
	}

	/**
	 * Subscription Store
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onAfterStoreSubscription(Event $event): void
	{
		if (!in_array(0, (array) $this->params->get('send_webhook_on', [1])))
		{
			return;
		}

		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		if (!str_contains($row->payment_method, 'os_offline'))
		{
			return;
		}

		$this->sendRequest($row, 'onAfterStoreSubscription');
	}

	/**
	 * Subscription Active
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipActive(Event $event): void
	{
		if (!in_array(1, (array) $this->params->get('send_webhook_on', [1])))
		{
			return;
		}

		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		$this->sendRequest($row, 'onMembershipActive');
	}

	/**
	 * Subscription Expired
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onMembershipExpire(Event $event): void
	{
		if (!in_array(2, (array) $this->params->get('send_webhook_on', [1])))
		{
			return;
		}

		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		$this->sendRequest($row, 'onMembershipExpire');
	}

	/**
	 * Send HTTP request
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   string                       $event
	 *
	 * @return void
	 */
	private function sendRequest($row, $event): void
	{
		$url = $this->params->get('url');

		if (!$url)
		{
			return;
		}

		JLoader::register('OSMembershipModelApi', JPATH_ROOT . '/components/com_osmembership/model/api.php');

		$config = OSMembershipHelper::getConfig();
		$db     = $this->db;
		$query  = $db->getQuery(true);

		/* @var OSMembershipModelApi $model */
		$model = MPFModel::getTempInstance('Api', 'OSMembershipModel');

		// Get custom fields data
		$data = $model->getSubscriptionData($row->id);

		// Add other subscription related data
		$subscriptionRelatedFields = [
			'id',
			'plan_id',
			'user_id',
			'profile_id',
			'from_date',
			'to_date',
			'created_date',
			'subscription_id',
			'amount',
			'discount_amount',
			'tax_amount',
			'gross_amount',
			'payment_processing_fee',
			'transaction_id',
			'tax_rate',
			'payment_method',
			'coupon_id',
			'vies_registered',
			'coupon_id',
			'published',
			'email',// Should not be needed here, just a special case for a customer
		];

		foreach ($subscriptionRelatedFields as $field)
		{
			$data[$field] = $row->{$field};
		}

		// Other calculated fields
		$data['country_code'] = OSMembershipHelper::getCountryCode($row->country);

		if ($row->username)
		{
			$data['username'] = $row->username;
		}
		elseif ($row->user_id)
		{
			$query->select('username')
				->from('#__users')
				->where('id = ' . (int) $row->user_id);
			$db->setQuery($query);
			$data['username'] = $db->loadResult();
		}
		else
		{
			$data['username'] = '';
		}

		if ($row->coupon_id)
		{
			$query->clear()
				->select($db->quoteName('code'))
				->from('#__osmembership_coupons')
				->where('id = ' . $row->coupon_id);
			$db->setQuery($query);
			$data['coupon_code'] = $db->loadResult();
		}
		else
		{
			$data['coupon_code'] = '';
		}

		$data['membership_id']  = OSMembershipHelper::formatMembershipId($row, $config);
		$data['invoice_number'] = OSMembershipHelper::formatInvoiceNumber($row, $config);

		// Get plans related information
		$rowPlan = OSMembershipHelperDatabase::getPlan($row->plan_id);

		$data['plan_id']    = $rowPlan->id;
		$data['plan_title'] = $rowPlan->title;
		$data['plan_alias'] = $rowPlan->alias;
		$data['plan_price'] = $rowPlan->price;

		if ($rowPlan->category_id > 0)
		{
			$fieldSuffix = OSMembershipHelper::getFieldSuffix($row->language);

			$query->clear()
				->select($db->quoteName('title' . $fieldSuffix))
				->from('#__osmembership_categories')
				->where('id = ' . $rowPlan->category_id);
			$db->setQuery($query);

			$data['category'] = $db->loadResult();
		}
		else
		{
			$data['category'] = '';
		}

		if ($row->published == 0)
		{
			$data['subscription_status'] = 'Pending';
		}
		elseif ($row->published == 1)
		{
			$data['subscription_status'] = 'Active';
		}
		elseif ($row->published == 2)
		{
			$data['subscription_status'] = 'Expired';
		}
		else
		{
			$data['subscription_status'] = 'Unknown';
		}

		$data['event'] = $event;

		// OK, we will now make a post request with json data
		try
		{
			$http = HttpFactory::getHttp();

			$data = $this->getMappedData($data);

			if ($this->params->get('content_type', 'application/json') === 'application/json')
			{
				$data = json_encode($data);
			}

			$http->post($url, $data, $this->getRequestHeaders());
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Get request headers
	 *
	 * @return array
	 */
	private function getRequestHeaders(): array
	{
		$headers['Content-Type'] = $this->params->get('content_type', 'application/json');

		foreach ($this->params->get('headers', []) as $header)
		{
			if (is_string($header->name) && is_string($header->value) && strlen(trim($header->name)) > 0 && strlen(
					trim($header->value)
				) > 0)
			{
				$headers[$header->name] = $header->value;
			}
		}

		return $headers;
	}

	/**
	 * Method to get request data in the mapped keys
	 *
	 * @param   array  $data
	 *
	 * @return array
	 */
	private function getMappedData(array $data): array
	{
		foreach ($this->params->get('fields_mapping', []) as $fieldMapping)
		{
			if (is_string($fieldMapping->original_field_name)
				&& is_string($fieldMapping->new_field_name)
				&& strlen(trim($fieldMapping->original_field_name)) > 0
				&& strlen(trim($fieldMapping->new_field_name)) > 0)
			{
				if (array_key_exists(trim($fieldMapping->original_field_name), $data))
				{
					$data[trim($fieldMapping->new_field_name)] = $data[trim($fieldMapping->original_field_name)];
					unset($data[trim($fieldMapping->original_field_name)]);
				}
			}
		}

		return $data;
	}
}