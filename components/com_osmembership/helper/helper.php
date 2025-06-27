<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use CB\Database\Table\UserTable;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Component\Users\Site\Model\RegistrationModel;
use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use OSSolution\MembershipPro\Admin\Event\Subscribe\CheckCanSubscribeToPlan;

class OSMembershipHelper
{
	/**
	 * Helper method to print debug backtrace, use for debugging purpose when it's needed
	 *
	 * @return void
	 */
	public static function printDebugBackTrace()
	{
		$traces = debug_backtrace();

		foreach ($traces as $trace)
		{
			echo $trace['file'] . ':' . $trace['line'] . '<br />';
		}
	}

	/**
	 * Get configuration data and store in config object
	 *
	 * @return MPFConfig
	 */
	public static function getConfig()
	{
		static $config;

		if ($config === null)
		{
			$config = new MPFConfig('#__osmembership_configs');

			if (!$config->date_field_format)
			{
				$config->set('date_field_format', '%Y-%m-%d');
			}

			if ($config->enable_captcha == 2)
			{
				$config->enable_captcha = 1;
			}

			if ($config->export_data_format === 'xls')
			{
				$config->export_data_format = 'xlsx';
			}

			$config->set('synchronize_data', '1');
		}

		return $config;
	}

	/**
	 * Helper method to determine if we are in Joomla 4
	 *
	 * @return bool
	 */
	public static function isJoomla4()
	{
		return version_compare(JVERSION, '4.0.0', 'ge');
	}

	/**
	 * Helper method to determine if we are in Joomla 5
	 *
	 * @return bool
	 */
	public static function isJoomla5()
	{
		return version_compare(JVERSION, '4.4.99', '>');
	}

	/**
	 * Method to get file extension
	 *
	 * @param   string  $file
	 *
	 * @return string
	 */
	public static function getFileExt($file)
	{
		// Use framework code if available
		if (is_callable([File::class, 'getExt']))
		{
			return File::getExt($file);
		}

		// String manipulation should be faster than pathinfo() on newer PHP versions.
		$dot = strrpos($file, '.');

		if ($dot === false)
		{
			return '';
		}

		$ext = substr($file, $dot + 1);

		// Extension cannot contain slashes.
		if (strpos($ext, '/') !== false || (DIRECTORY_SEPARATOR === '\\' && strpos($ext, '\\') !== false))
		{
			return '';
		}

		return $ext;
	}

	/**
	 * Check if a method is overrided in a child class
	 *
	 * @param $class
	 * @param $method
	 *
	 * @return bool
	 */
	public static function isMethodOverridden($class, $method)
	{
		if (class_exists($class) && method_exists($class, $method))
		{
			$reflectionMethod = new ReflectionMethod($class, $method);

			if ($reflectionMethod->getDeclaringClass()->getName() == $class)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Method to call a static overridable helper method
	 *
	 * @param   string  $helper
	 * @param   string  $method
	 * @param   array   $methodArgs
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public static function callOverridableHelperMethod($helper, $method, $methodArgs = [])
	{
		$callableMethods = [];

		if (strtolower($helper) == 'helper')
		{
			$helperMethod = 'OSMembershipHelper::' . $method;
		}
		else
		{
			$helperMethod = 'OSMembershipHelper' . ucfirst($helper) . '::' . $method;
		}

		$callableMethods[] = $helperMethod;

		$callableMethods[] = 'OSMembershipHelperOverride' . ucfirst($helper) . '::' . $method;

		foreach (array_reverse($callableMethods) as $callable)
		{
			if (is_callable($callable))
			{
				return call_user_func_array($callable, $methodArgs);
			}
		}

		throw new Exception(sprintf('Method %s does not exist in the helper %s', $method, $helper));
	}

	/**
	 * Get specify config value
	 *
	 * @param   string  $key
	 *
	 * @return mixed
	 */
	public static function getConfigValue($key, $default = null)
	{
		$config = static::getConfig();

		if (isset($config->{$key}))
		{
			return $config->{$key};
		}

		return $default;
	}

	/**
	 * Apply some fixes for request data
	 *
	 * @return void
	 */
	public static function prepareRequestData()
	{
		//Remove cookie vars from request data
		$cookieVars = array_keys($_COOKIE);

		if (count($cookieVars))
		{
			foreach ($cookieVars as $key)
			{
				if (!isset($_POST[$key]) && !isset($_GET[$key]))
				{
					unset($_REQUEST[$key]);
				}
			}
		}

		if (isset($_REQUEST['start']) && !isset($_REQUEST['limitstart']))
		{
			$_REQUEST['limitstart'] = $_REQUEST['start'];
		}

		if (!isset($_REQUEST['limitstart']))
		{
			$_REQUEST['limitstart'] = 0;
		}

		// Fix PayPal IPN sending to wrong URL
		if (!empty($_POST['txn_type']) && empty($_REQUEST['task']) && empty($_REQUEST['view']))
		{
			$_REQUEST['payment_method'] = 'os_paypal';

			if (!empty($_POST['subscr_id']) || strpos($_POST['txn_type'], 'subscr_'))
			{
				$_REQUEST['task'] = 'recurring_payment_confirm';
			}
			else
			{
				$_REQUEST['task'] = 'payment_confirm';
			}
		}
	}

	/**
	 * Get page params of the given view
	 *
	 * @param   MenuItem  $active
	 * @param   array     $views
	 *
	 * @return Registry
	 */
	public static function getViewParams($active, $views)
	{
		if ($active && isset($active->query['view']) && in_array($active->query['view'], $views))
		{
			return $active->getParams();
		}

		return new Registry();
	}

	/**
	 * Get editor use for Membership Pro
	 *
	 * @return Editor
	 * @throws Exception
	 */
	public static function getEditor()
	{
		$config = OSMembershipHelper::getConfig();

		$editor = $config->get('editor');

		if (!$editor || !PluginHelper::isEnabled('editors', $editor))
		{
			$editor = Factory::getApplication()->get('editor');
		}

		return Editor::getInstance($editor);
	}

	/**
	 * Get sef of current language
	 *
	 * @param   string  $tag
	 *
	 * @return void
	 */
	public static function addLangLinkForAjax($tag = '')
	{
		$langLink = '';

		if (Multilanguage::isEnabled())
		{
			if (empty($tag) || $tag == '*')
			{
				$tag = Factory::getApplication()->getLanguage()->getTag();
			}

			$languages = LanguageHelper::getLanguages('lang_code');

			if (isset($languages[$tag]))
			{
				$langLink = '&lang=' . $languages[$tag]->sef;
			}
		}

		Factory::getApplication()
			->getDocument()
			->getWebAssetManager()
			->addInlineScript(
				'var langLinkForAjax="' . $langLink . '";'
			);
	}

	/**
	 * Get lang to append to an URL
	 *
	 * @return string
	 */
	public static function getLangLink()
	{
		$langLink = '';

		if (Multilanguage::isEnabled())
		{
			$languages = LanguageHelper::getLanguages('lang_code');
			$langLink  = '&lang=' . $languages[Factory::getApplication()->getLanguage()->getTag()]->sef;
		}

		return $langLink;
	}

	/**
	 * Method to request user login before they can access to thsi page
	 *
	 * @param   string  $viewName
	 * @param   string  $msg  The redirect message
	 *
	 * @throws Exception
	 */
	public static function requestLogin($viewName, $msg = 'OSM_PLEASE_LOGIN')
	{
		$app    = Factory::getApplication();
		$active = $app->getMenu()->getActive();

		$option = $active->query['option'] ?? '';
		$view   = $active->query['view'] ?? '';

		if ($option == 'com_osmembership' && $view == strtolower($viewName))
		{
			$returnUrl = 'index.php?Itemid=' . $active->id;
		}
		else
		{
			$returnUrl = Uri::getInstance()->toString();
		}

		$url = Route::_('index.php?option=com_users&view=login&return=' . base64_encode($returnUrl), false);

		$app->enqueueMessage(Text::_($msg));
		$app->redirect($url);
	}

	/**
	 * Check if a field is published
	 *
	 * @param   string  $name
	 *
	 * @return bool
	 */
	public static function isFieldPublished(string $name): bool
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__osmembership_fields')
			->where('name = ' . $db->quote($name))
			->where('published = 1');
		$db->setQuery($query);

		return $db->loadResult() > 0;
	}

	/**
	 * Check to see if the given user only has unique subscription plan
	 *
	 * @param $userId
	 *
	 * @return bool
	 */
	public static function isUniquePlan($userId)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('DISTINCT plan_id')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $userId)
			->where('published <= 2');
		$db->setQuery($query);
		$planIds = $db->loadColumn();

		if (count($planIds) == 1)
		{
			return true;
		}

		return false;
	}

	/**
	 * Helper method to check to see where the subscription can be cancelled
	 *
	 * @param $row
	 *
	 * @return bool
	 */
	public static function canCancelSubscription($row)
	{
		$user   = Factory::getApplication()->getIdentity();
		$userId = $user->id;

		if ($row
			&& (($row->user_id == $userId && $userId) || $user->authorise(
					'membershippro.subscriptions',
					'com_osmembership'
				))
			&& !$row->recurring_subscription_cancelled)
		{
			return true;
		}

		return false;
	}

	/**
	 * Helper method to check to see where the subscription can be cancelled
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public static function canRefundSubscription($row)
	{
		if ($row
			&& $row->gross_amount > 0
			&& $row->payment_method
			&& $row->transaction_id
			&& !$row->refunded
			&& Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_osmembership'))
		{
			$method = OSMembershipHelper::loadPaymentMethod($row->payment_method);

			if (method_exists($method, 'supportRefundPayment') && $method->supportRefundPayment())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get list of custom fields belong to com_users
	 *
	 * @return array
	 */
	public static function getUserFields()
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('id, name')
			->from('#__fields')
			->where($db->quoteName('context') . '=' . $db->quote('com_users.user'))
			->where($db->quoteName('state') . ' = 1');
		$db->setQuery($query);

		return $db->loadObjectList('name');
	}

	/**
	 * Load payment method object
	 *
	 * @param   string  $name
	 *
	 * @return MPFPayment
	 * @throws Exception
	 */
	public static function loadPaymentMethod($name)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_plugins')
			->where('published = 1')
			->where('name = ' . $db->quote($name));
		$db->setQuery($query);
		$row = $db->loadObject();

		if ($row && file_exists(JPATH_ROOT . '/components/com_osmembership/plugins/' . $row->name . '.php'))
		{
			require_once JPATH_ROOT . '/components/com_osmembership/plugins/' . $name . '.php';

			$params = new Registry($row->params);

			/* @var MPFPayment $method */
			$method = new $name($params);
			$method->setTitle($row->title);

			return $method;
		}

		throw new Exception(sprintf('Payment method %s not found', $name));
	}

	/**
	 * Check if transaction ID processed before
	 *
	 * @param $transactionId
	 *
	 * @return bool
	 */

	public static function isTransactionProcessed($transactionId)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__osmembership_subscribers')
			->where('transaction_id = ' . $db->quote($transactionId));
		$db->setQuery($query);
		$total = (int) $db->loadResult();

		return $total > 0;
	}

	/**
	 * Helper function to extend subscription of a user when a recurring payment happens
	 *
	 * @param   int     $id
	 * @param   string  $transactionId
	 * @param   string  $subscriptionId
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public static function extendRecurringSubscription($id, $transactionId = null, $subscriptionId = null)
	{
		/* @var OSMembershipModelApi $model */
		$model = MPFModel::getTempInstance('Api', 'OSMembershipModel');
		$model->renewRecurringSubscription($id, $subscriptionId, $transactionId);
	}

	/**
	 * Get total plans of a category (and it's sub-categories)
	 *
	 * @param $categoryId
	 *
	 * @return int
	 */
	public static function countPlans($categoryId)
	{
		$user = Factory::getApplication()->getIdentity();
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('id, parent_id')
			->from('#__osmembership_categories')
			->where('published = 1')
			->whereIn('access', $user->getAuthorisedViewLevels());
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$children = [];

		// first pass - collect children
		if (count($rows))
		{
			foreach ($rows as $v)
			{
				$pt   = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : [];
				array_push($list, $v->id);
				$children[$pt] = $list;
			}
		}

		$queues        = [$categoryId];
		$allCategories = [$categoryId];

		while (count($queues))
		{
			$id = array_pop($queues);
			if (isset($children[$id]))
			{
				$allCategories = array_merge($allCategories, $children[$id]);
				$queues        = array_merge($queues, $children[$id]);
			}
		}

		$query->clear()
			->select('COUNT(*)')
			->from('#__osmembership_plans')
			->where('published = 1')
			->whereIn('access', $user->getAuthorisedViewLevels())
			->whereIn('category_id', $allCategories);
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Calculate to see the sign up button should be displayed or not
	 *
	 * @param   object  $row
	 *
	 * @return bool
	 */
	public static function canSubscribe($row)
	{
		$user = Factory::getApplication()->getIdentity();

		if ($user->id)
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);

			if ($row->recurring_subscription)
			{
				$activePlanIds = OSMembershipHelperSubscription::getActiveMembershipPlans();

				if (in_array($row->id, $activePlanIds))
				{
					$query->clear()
						->select('*')
						->from('#__osmembership_subscribers')
						->where('plan_id = ' . $row->id)
						->where('user_id = ' . $user->id)
						->where('published = 1')
						->order('id DESC');
					$db->setQuery($query);
					$rowSubscription = $db->loadObject();

					if ($rowSubscription && $rowSubscription->subscription_id && !str_contains(
							$rowSubscription->payment_method,
							'os_offline'
						))
					{
						return false;
					}
				}
			}

			if (!$row->enable_renewal)
			{
				$query->clear()
					->select('COUNT(*)')
					->from('#__osmembership_subscribers')
					->where('(email = ' . $db->quote($user->email) . ' OR user_id = ' . (int) $user->id . ')')
					->where('plan_id =' . $row->id)
					->where('(published != 0 OR gross_amount = 0 OR payment_method LIKE "os_offline%")');

				$db->setQuery($query);
				$total = (int) $db->loadResult();

				if ($total)
				{
					return false;
				}
			}

			$config = OSMembershipHelper::getConfig();

			$numberDaysBeforeRenewal = (int) $config->number_days_before_renewal;

			if ($numberDaysBeforeRenewal)
			{
				//Get max date
				$query->clear()
					->select('MAX(to_date)')
					->from('#__osmembership_subscribers')
					->where(
						'user_id=' . (int) $user->id . ' AND plan_id=' . $row->id . ' AND (published=1 OR (published = 0 AND payment_method LIKE "os_offline%"))'
					);
				$db->setQuery($query);
				$maxDate = $db->loadResult();

				if ($maxDate)
				{
					$expiredDate = Factory::getDate($maxDate);
					$todayDate   = Factory::getDate();
					$diff        = $expiredDate->diff($todayDate);
					$numberDays  = $diff->days;

					if ($expiredDate > $todayDate && $numberDays > $numberDaysBeforeRenewal)
					{
						return false;
					}
				}
			}
		}

		PluginHelper::importPlugin('osmembership');

		$event = new CheckCanSubscribeToPlan([
			'row' => $row,
		]);

		$results = Factory::getApplication()->triggerEvent($event->getName(), $event);

		if (in_array(false, $results, true))
		{
			return false;
		}

		return true;
	}

	/*
     * Check to see whether the current user can browse users list
     */
	public static function canBrowseUsersList()
	{
		$user = Factory::getApplication()->getIdentity();

		if ($user->authorise('membershippro.subscriptions', 'com_osmembership'))
		{
			return true;
		}

		$config = OSMembershipHelper::getConfig();

		if (!$config->enable_select_existing_users)
		{
			return false;
		}

		$canManage = OSMembershipHelper::getManageGroupMemberPermission();

		return $canManage > 0;
	}

	/**
	 * Get manage group members permission
	 *
	 * @param   array  $addNewMemberPlanIds
	 *
	 * @return int
	 */
	public static function getManageGroupMemberPermission(&$addNewMemberPlanIds = [])
	{
		if (!PluginHelper::isEnabled('osmembership', 'groupmembership'))
		{
			Factory::getApplication()->enqueueMessage(
				'Please enable plugin Membership Pro - Group Membership Plugin to use this feature',
				'notice'
			);

			return 0;
		}

		$userId = Factory::getApplication()->getIdentity()->id;

		if (!$userId)
		{
			return 0;
		}

		/* @var DatabaseDriver $db */
		$db = Factory::getContainer()->get('db');

		// Check if this user is a group members
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $userId)
			->where('group_admin_id > 0');
		$db->setQuery($query);
		$total = $db->loadResult();

		if ($total)
		{
			return 0;
		}

		[
			$managePlanIds,
			$addNewMemberPlanIds
		] = OSMembershipHelperGroupmembership::getGroupMembershipSubscriptionsForUser($userId);

		if (count($addNewMemberPlanIds) > 0)
		{
			return 2;
		}
		elseif (count($managePlanIds) > 0)
		{
			return 1;
		}

		return 0;
	}

	/**
	 * Method to check to see whether the current user can access to the current view
	 *
	 * @param   string  $view
	 *
	 * @return bool
	 */
	public static function canAccessThisView($view)
	{
		$user   = Factory::getApplication()->getIdentity();
		$access = true;

		switch ($view)
		{
			case 'categories':
			case 'category':
				$access = $user->authorise('membershippro.categories', 'com_osmembership');
				break;
			case 'plans':
			case 'plan':
				$access = $user->authorise('membershippro.plans', 'com_osmembership');
				break;
			case 'subscriptions':
			case 'subscription':
			case 'reports':
			case 'subscribers':
			case 'subscriber':
			case 'groupmembers':
			case 'groupmember':
			case 'import':
				$access = $user->authorise('membershippro.subscriptions', 'com_osmembership');
				break;
			case 'configuration':
			case 'plugins':
			case 'plugin':
			case 'taxes':
			case 'tax':
			case 'countries':
			case 'country':
			case 'states':
			case 'state':
			case 'message':
				$access = $user->authorise('core.admin', 'com_osmembership');
				break;
			case 'fields':
			case 'field':
				$access = $user->authorise('membershippro.fields', 'com_osmembership');
				break;
			case 'coupons':
			case 'coupon':
				$access = $user->authorise('membershippro.coupons', 'com_osmembership');
				break;
		}

		return $access;
	}

	/**
	 * Try to fix ProfileID for user if it was lost for some reasons - for example, admin delete
	 *
	 * @param $userId
	 *
	 * @return bool
	 */
	public static function fixProfileId($userId)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . (int) $userId)
			->order('id DESC');
		$db->setQuery($query);
		$id = (int) $db->loadResult();

		if ($id)
		{
			// Make this record as profile ID
			$query->clear()
				->update('#__osmembership_subscribers')
				->set('is_profile = 1')
				->set('profile_id =' . $id)
				->where('id = ' . $id);
			$db->setQuery($query);
			$db->execute();

			// Mark all other records of this user has profile_id = ID of this record
			$query->clear()
				->update('#__osmembership_subscribers')
				->set('profile_id = ' . $id)
				->where('user_id = ' . $userId)
				->where('id != ' . $id);
			$db->setQuery($query);
			$db->execute();

			return true;
		}

		return false;
	}

	/**
	 * This function is used to check to see whether we need to update the database to support multilingual or not
	 *
	 * @return bool
	 */
	public static function isSyncronized()
	{
		/* @var DatabaseDriver $db */
		$db             = Factory::getContainer()->get('db');
		$fields         = array_keys($db->getTableColumns('#__osmembership_plans'));
		$extraLanguages = self::getLanguages();

		if (count($extraLanguages))
		{
			foreach ($extraLanguages as $extraLanguage)
			{
				$prefix = $extraLanguage->sef;

				if (!in_array('alias_' . $prefix, $fields) || !in_array('user_renew_email_subject_' . $prefix, $fields))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Check to see whether the system need to create invoice for this subscription record or not
	 *
	 *
	 * @param $row
	 *
	 * @return bool
	 */
	public static function needToCreateInvoice($row)
	{
		$config    = OSMembershipHelper::getConfig();
		$published = (int) $row->published;

		if ($row->gross_amount > 0
			&& ($published === 1 || !$config->generated_invoice_for_paid_subscription_only))
		{
			return true;
		}

		return false;
	}

	/**
	 * Convert payment amount to USD currency in case the currency is not supported by the payment gateway
	 *
	 * @param $amount
	 * @param $currency
	 *
	 * @return float
	 */
	public static function convertAmountToUSD($amount, $currency)
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideHelper', 'convertAmountToUSD'))
		{
			return OSMembershipHelperOverrideHelper::convertAmountToUSD($amount, $currency);
		}

		$session = Factory::getApplication()->getSession();

		if ($session->get('exchange_rate_' . $currency))
		{
			$rate = (float) $session->get('exchange_rate_' . $currency);
		}
		else
		{
			$config = OSMembershipHelper::getConfig();
			$appId  = $config->get('open_exchange_rates_app_id') ?: '1638b610affe4de19e61af489ea767d1';
			$url    = 'https://openexchangerates.org/api/latest.json?app_id=' . $appId . '&base=USD';

			$headers = [
				'User-Agent' => 'Membership Pro ' . OSMembershipHelper::getInstalledVersion(),
			];

			$http     = HttpFactory::getHttp();
			$response = $http->get($url, $headers);
			$rate     = (float) $config->get('exchange_rate', 1);

			if ($response->code == 200)
			{
				$jsonResponse = json_decode($response->body);

				if ($jsonResponse && isset($jsonResponse->rates->{$currency}))
				{
					$rate = (float) $jsonResponse->rates->{$currency};
				}
			}

			if ($rate <= 0)
			{
				$rate = 1;
			}

			$session->set('exchange_rate_' . $currency, $rate);
		}

		return round($amount / $rate, 2);
	}

	/**
	 * Builds an exchange rate from the response content.
	 *
	 * @param   string  $content
	 *
	 * @return float
	 *
	 * @throws Exception
	 */
	protected static function buildExchangeRate($content)
	{
		$document = new DOMDocument();

		if (false === @$document->loadHTML('<?xml encoding="utf-8" ?>' . $content))
		{
			throw new Exception('The page content is not loadable');
		}

		$xpath = new DOMXPath($document);
		$nodes = $xpath->query('//span[@id="knowledge-currency__tgt-amount"]');

		if (1 !== $nodes->length)
		{
			$nodes = $xpath->query('//div[@class="vk_ans vk_bk" or @class="dDoNo vk_bk"]');
		}

		if (1 !== $nodes->length)
		{
			throw new Exception('The currency is not supported or Google changed the response format');
		}

		$nodeContent = $nodes->item(0)->textContent;

		// Beware of "3 417.36111 Colombian pesos", with a non breaking space
		$bid = strtr($nodeContent, ["\xc2\xa0" => '']);

		if (str_contains($bid, ' '))
		{
			$bid = strstr($bid, ' ', true);
		}
		// Does it have thousands separator?
		if (strpos($bid, ',') && strpos($bid, '.'))
		{
			$bid = str_replace(',', '', $bid);
		}

		if (!is_numeric($bid))
		{
			throw new Exception('The currency is not supported or Google changed the response format');
		}

		return $bid;
	}

	/**
	 * Check to see whether the return value is a valid date format
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public static function isValidDate($value)
	{
		// basic date format yyyy-mm-dd
		$expr = '/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/D';

		return preg_match($expr, $value, $match) && checkdate($match[2], $match[3], $match[1]);
	}

	/**
	 * Calculate subscription fees based on input parameter
	 *
	 * @param   OSMembershipTablePlan  $rowPlan  the object which contains information about the plan
	 * @param   MPFForm                $form     The form object which is used to calculate extra fee
	 * @param   array                  $data     The post data
	 * @param   MPFConfig              $config
	 * @param   string                 $paymentMethod
	 *
	 * @return array
	 */
	public static function calculateSubscriptionFee($rowPlan, $form, $data, $config, $paymentMethod = null)
	{
		if (!$rowPlan->recurring_subscription)
		{
			return self::callOverridableHelperMethod(
				'Subscription',
				'calculateOnetimeSubscriptionFee',
				[$rowPlan, $form, $data, $config, $paymentMethod]
			);
		}

		return self::callOverridableHelperMethod(
			'Subscription',
			'calculateRecurringSubscriptionFee',
			[$rowPlan, $form, $data, $config, $paymentMethod]
		);
	}

	/**
	 * Helper function to determine tax rate is based on country or not
	 *
	 * @return bool
	 */

	public static function isCountryBaseTax()
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('DISTINCT(country)')
			->from('#__osmembership_taxes')
			->where('published = 1');
		$db->setQuery($query);
		$countries       = $db->loadColumn();
		$numberCountries = count($countries);

		if ($numberCountries > 1)
		{
			return true;
		}
		elseif ($numberCountries == 1 && strlen($countries[0]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Get list of countries which has tax based on state
	 *
	 * @return string
	 */
	public static function getTaxStateCountries()
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('DISTINCT(country)')
			->from('#__osmembership_taxes')
			->where('`state` != ""')
			->where('published = 1');
		$db->setQuery($query);

		return implode(',', $db->loadColumn());
	}

	/**
	 * Calculate tax rate for the plan
	 *
	 * @param   int     $planId
	 * @param   string  $country
	 * @param   string  $state
	 * @param   int     $vies
	 *
	 * @return float
	 */
	public static function calculateTaxRate($planId, $country = '', $state = '', $vies = 2)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		if (empty($country))
		{
			$country = self::getConfigValue('default_country');
		}

		$query->select('rate')
			->from('#__osmembership_taxes')
			->where('published = 1')
			->where('plan_id = ' . (int) $planId)
			->where('(country = "" OR country = ' . $db->quote($country) . ')');

		if ($state)
		{
			$query->where('(state = "" OR state = ' . $db->quote($state) . ')')
				->order('`state` DESC');
		}
		else
		{
			$query->where('state = ""');
		}

		$query->order('country DESC');

		if ($vies != 2)
		{
			$query->where('vies = ' . (int) $vies);
		}

		$db->setQuery($query);
		$rowRate = $db->loadObject();

		if ($rowRate)
		{
			return $rowRate->rate;
		}

		// Try to find a record with all plans
		$query->clear()
			->select('rate')
			->from('#__osmembership_taxes')
			->where('published = 1')
			->where('plan_id = 0')
			->where('(country = "" OR country = ' . $db->quote($country) . ')');

		if ($state)
		{
			$query->where('(state = "" OR state = ' . $db->quote($state) . ')')
				->order('`state` DESC');
		}
		else
		{
			$query->where('state = ""');
		}

		$query->order('country DESC');

		if ($vies != 2)
		{
			$query->where('vies = ' . (int) $vies);
		}

		$db->setQuery($query);
		$rowRate = $db->loadObject();

		if ($rowRate)
		{
			return $rowRate->rate;
		}

		// If no tax rule found, return 0
		return 0;
	}

	/**
	 * Calculate max taxrate for the plan
	 *
	 * @param   int     $planId
	 * @param   string  $country
	 * @param   string  $state
	 * @param   int     $vies
	 * @param   bool    $useDefaultCountryIfEmpty
	 *
	 * @return int
	 */
	public static function calculateMaxTaxRate(
		$planId,
		$country = '',
		$state = '',
		$vies = 2,
		$useDefaultCountryIfEmpty = true
	) {
		if (empty($country) && $useDefaultCountryIfEmpty)
		{
			$country = self::getConfigValue('default_country');
		}

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('rate')
			->from('#__osmembership_taxes')
			->where('published = 1')
			->where('plan_id = ' . $planId)
			->order('`rate` DESC');

		if ($country)
		{
			$query->where('(country = "" OR country = ' . $db->quote($country) . ')')
				->order('country DESC');
		}

		if ($state)
		{
			$query->where('(state = "" OR state = ' . $db->quote($state) . ')')
				->order('`state` DESC');
		}

		if ($vies != 2)
		{
			$query->where('vies = ' . (int) $vies);
		}

		$db->setQuery($query);
		$rowRate = $db->loadObject();

		if ($rowRate)
		{
			return $rowRate->rate;
		}
		// Try to find a record with all plans
		$query->clear()
			->select('rate')
			->from('#__osmembership_taxes')
			->where('published = 1')
			->where('plan_id = 0')
			->order('`rate` DESC');

		if ($country)
		{
			$query->where('(country = "" OR country = ' . $db->quote($country) . ')')
				->order('country DESC');
		}

		if ($state)
		{
			$query->where('(state = "" OR state = ' . $db->quote($state) . ')')
				->order('`state` DESC');
		}

		if ($vies != 2)
		{
			$query->where('vies = ' . (int) $vies);
		}

		$db->setQuery($query);
		$rowRate = $db->loadObject();

		if ($rowRate)
		{
			return $rowRate->rate;
		}


		// If no tax rule found, return 0
		return 0;
	}

	/**
	 * Get list of fields used to display on subscription form for a plan
	 *
	 * @param   int     $planId
	 * @param   bool    $loadCoreFields
	 * @param   string  $language
	 * @param   string  $action
	 * @param   string  $view
	 *
	 * @return mixed
	 */
	public static function getProfileFields(
		$planId,
		$loadCoreFields = true,
		$language = null,
		$action = null,
		$view = null,
		$userId = null
	) {
		static $cache = [];

		$cacheKey = md5(serialize(func_get_args()));

		if (!array_key_exists($cacheKey, $cache))
		{
			if ($userId > 0)
			{
				$user = Factory::getUser((int) $userId);

				// Prevent error when passing an invalid User Id
				if ($user->id === null)
				{
					$user = Factory::getApplication()->getIdentity();
				}
			}
			else
			{
				$user = Factory::getApplication()->getIdentity();
			}

			/* @var DatabaseDriver $db */
			$db          = Factory::getContainer()->get('db');
			$query       = $db->getQuery(true);
			$planId      = (int) $planId;
			$fieldSuffix = self::getFieldSuffix($language);
			$negPlanId   = -1 * $planId;
			$query->select('*')
				->from('#__osmembership_fields')
				->where('published = 1');

			if ($planId > 0)
			{
				$query->where(
					'(plan_id = 0 OR id IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $planId . ' OR plan_id < 0))'
				)
					->where(
						'id NOT IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $negPlanId . ')'
					);
			}
			else
			{
				$query->where('plan_id = 0');
			}

			if (!$user->authorise('core.admin', 'com_osmembership')
				&& !$user->authorise('membershippro.subscriptions', 'com_osmembership'))
			{
				$query->whereIn('access', $user->getAuthorisedViewLevels());
			}

			if ($fieldSuffix)
			{
				require_once JPATH_ROOT . '/components/com_osmembership/helper/database.php';

				OSMembershipHelperDatabase::getMultilingualFields(
					$query,
					[
						'title',
						'description',
						'values',
						'default_values',
						'depend_on_options',
						'place_holder',
						'prompt_text',
						'validation_error_message',
					],
					$fieldSuffix
				);
			}

			if (!$loadCoreFields)
			{
				$query->where('is_core = 0');
			}

			// Hide the fields which are setup to be hided on membership renewal
			if ($action == 'renew')
			{
				$query->where('hide_on_membership_renewal = 0');
			}

			if ($view == 'register')
			{
				$query->where('show_on_subscription_form = 1');
			}

			if ($view == 'payment')
			{
				$query->where('show_on_subscription_payment = 1');
			}

			$query->order('ordering');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			foreach ($rows as $row)
			{
				$row->lannguage   = $language;
				$row->fieldSuffix = $fieldSuffix;
			}

			$cache[$cacheKey] = $rows;
		}

		return $cache[$cacheKey];
	}

	/**
	 * Get list of fields used to display on subscriptions or members list page
	 *
	 * @param   int     $planId
	 * @param   bool    $loadCoreFields
	 * @param   array   $wheres
	 * @param   string  $language
	 *
	 * @return mixed
	 */
	public static function getCustomFieldsForPlans($planId, $loadCoreFields = true, $wheres = [], $language = null)
	{
		static $cache = [];

		$cacheKey = md5(serialize(func_get_args()));

		if (!array_key_exists($cacheKey, $cache))
		{
			$user = Factory::getApplication()->getIdentity();
			/* @var DatabaseDriver $db */
			$db          = Factory::getContainer()->get('db');
			$query       = $db->getQuery(true);
			$planId      = (int) $planId;
			$fieldSuffix = self::getFieldSuffix($language);
			$negPlanId   = -1 * $planId;
			$query->select('*')
				->from('#__osmembership_fields')
				->where('published = 1');

			if ($planId > 0)
			{
				$query->where(
					'(plan_id = 0 OR id IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $planId . ' OR plan_id < 0))'
				)
					->where(
						'id NOT IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $negPlanId . ')'
					);
			}

			if (!$user->authorise('core.admin', 'com_osmembership')
				&& !$user->authorise('membershippro.subscriptions', 'com_osmembership'))
			{
				$query->whereIn('access', $user->getAuthorisedViewLevels());
			}

			if ($fieldSuffix)
			{
				require_once JPATH_ROOT . '/components/com_osmembership/helper/database.php';

				OSMembershipHelperDatabase::getMultilingualFields(
					$query,
					[
						'title',
						'description',
						'values',
						'default_values',
						'depend_on_options',
						'place_holder',
						'prompt_text',
						'validation_error_message',
					],
					$fieldSuffix
				);
			}

			if (!$loadCoreFields)
			{
				$query->where('is_core = 0');
			}

			foreach ($wheres as $where)
			{
				$query->where($where);
			}

			$query->order('ordering');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			foreach ($rows as $row)
			{
				$row->lannguage   = $language;
				$row->fieldSuffix = $fieldSuffix;
			}

			$cache[$cacheKey] = $rows;
		}

		return $cache[$cacheKey];
	}

	/**
	 * Get Login redirect url for the subscriber
	 *
	 * @return string
	 */
	public static function getLoginRedirectUrl()
	{
		$redirectUrl = '';
		$activePlans = OSMembershipHelperSubscription::getActiveMembershipPlans();

		if (count($activePlans) > 1)
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->select('login_redirect_menu_id')
				->from('#__osmembership_plans')
				->whereIn('id', $activePlans)
				->where('login_redirect_menu_id > 0')
				->order('price DESC, ordering ASC');
			$db->setQuery($query);
			$loginRedirectMenuId = $db->loadResult();

			if ($loginRedirectMenuId)
			{
				if (Multilanguage::isEnabled())
				{
					$langAssociations = Associations::getAssociations(
						'com_menus',
						'#__menu',
						'com_menus.item',
						$loginRedirectMenuId,
						'id',
						'',
						''
					);

					$langCode = Factory::getApplication()->getLanguage()->getTag();

					if (isset($langAssociations[$langCode]))
					{
						$loginRedirectMenuId = $langAssociations[$langCode]->id;
					}
				}

				if ($item = Factory::getApplication()->getMenu()->getItem($loginRedirectMenuId))
				{
					$redirectUrl = 'index.php?Itemid=' . $loginRedirectMenuId;

					if (Multilanguage::isEnabled() && $item->language && $item->language != '*')
					{
						$redirectUrl .= '&lang=' . $item->language;
					}
				}
			}
		}

		return $redirectUrl;
	}

	/**
	 * Get profile data of one user
	 *
	 * @param   object  $rowProfile
	 * @param   array   $rowFields
	 *
	 * @return array
	 */
	public static function getProfileData($rowProfile, $planId, $rowFields)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$data  = [];
		$query->select('a.name, b.field_value')
			->from('#__osmembership_fields AS a')
			->innerJoin('#__osmembership_field_value AS b ON a.id = b.field_id')
			->where('b.subscriber_id = ' . $rowProfile->id);
		$db->setQuery($query);
		$fieldValues = $db->loadObjectList('name');

		foreach ($rowFields as $rowField)
		{
			if ($rowField->is_core)
			{
				$data[$rowField->name] = $rowProfile->{$rowField->name};
			}
			elseif (isset($fieldValues[$rowField->name]))
			{
				$data[$rowField->name] = $fieldValues[$rowField->name]->field_value;
			}
		}

		return $data;
	}

	/**
	 * Synchronize data for hidden fields on membership renewal
	 *
	 * @param $row
	 * @param $data
	 *
	 * @return bool
	 */
	public static function synchronizeHiddenFieldsData($row, &$data)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_subscribers')
			->where('profile_id = ' . $row->profile_id)
			->where('plan_id = ' . $row->plan_id)
			->where('id != ' . $row->id)
			->where('(published >= 1 OR payment_method="os_offline")')
			->where('act != "renew"')
			->order('id');
		$db->setQuery($query);
		$rowProfile = $db->loadObject();

		if ($rowProfile)
		{
			// Get the fields which are hided
			$negPlanId = -1 * $row->plan_id;
			$query->clear()
				->select('*')
				->from('#__osmembership_fields')
				->where('published = 1')
				->where('hide_on_membership_renewal = 1')
				->whereIn('access', Factory::getApplication()->getIdentity()->getAuthorisedViewLevels())
				->where(
					'(plan_id = 0 OR id IN (SELECT field_id FROM #__osmembership_field_plan WHERE plan_id = ' . $row->plan_id . ' OR (plan_id < 0 AND plan_id != ' . $negPlanId . ')))'
				);

			$db->setQuery($query);
			$hidedFields = $db->loadObjectList();

			$hideFieldsData = OSMembershipHelper::getProfileData($rowProfile, 0, $hidedFields);

			if (count(($hideFieldsData)))
			{
				$data = array_merge($data, $hideFieldsData);

				foreach ($hidedFields as $field)
				{
					$fieldName = $field->name;

					if ($field->is_core && isset($data[$fieldName]))
					{
						$row->{$fieldName} = $rowProfile->{$fieldName};
					}
				}

				$row->store();
			}
		}

		return true;
	}

	public static function syncronizeProfileData($row, $data)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		$query->select('id')
			->from('#__osmembership_subscribers')
			->where('profile_id=' . (int) $row->profile_id)
			->where('id !=' . (int) $row->id);
		$db->setQuery($query);
		$subscriptionIds = $db->loadColumn();

		if (count($subscriptionIds))
		{
			if ($row->user_id && OSMembershipHelper::isUniquePlan($row->user_id))
			{
				$planId = $row->plan_id;
			}
			else
			{
				$planId = 0;
			}

			$rowFields = OSMembershipHelper::getProfileFields($planId, false);
			$form      = new MPFForm($rowFields);
			$form->storeData($row->id, $data);

			$query->clear()
				->select('name')
				->from('#__osmembership_fields')
				->where('is_core=1 AND published = 1');
			$db->setQuery($query);
			$coreFields    = $db->loadColumn();
			$coreFieldData = [];

			foreach ($coreFields as $field)
			{
				if (isset($data[$field]))
				{
					$coreFieldData[$field] = $data[$field];
				}
				else
				{
					$coreFieldData[$field] = '';
				}
			}

			foreach ($subscriptionIds as $subscriptionId)
			{
				$rowSubscription = new OSMembershipTableSubscriber($db);
				$rowSubscription->load($subscriptionId);
				$rowSubscription->bind($coreFieldData);
				$rowSubscription->store();
				$form->storeData($subscriptionId, $data);
			}
		}
	}

	/**
	 * Get information about subscription plans of a user
	 *
	 * @param   int  $profileId
	 *
	 * @return array
	 */
	public static function getSubscriptions($profileId)
	{
		JLoader::register(
			'OSMembershipHelperSubscription',
			JPATH_ROOT . '/components/com_osmembership/helper/subscription.php'
		);

		return OSMembershipHelperSubscription::getSubscriptions($profileId);
	}

	/**
	 * Get the email messages used for sending emails
	 *
	 * @return MPFConfig
	 */
	public static function getMessages()
	{
		static $message;

		if ($message === null)
		{
			$message = new MPFConfig('#__osmembership_messages', 'message_key', 'message');
		}

		return $message;
	}

	/**
	 * Set plan messages data from the messages configured inside category
	 *
	 * @param   OSMembershipTablePlan      $plan
	 * @param   OSMembershipTableCategory  $category
	 * @param   array                      $keys
	 * @param   string                     $fieldSuffix
	 *
	 * @return void
	 */
	public static function setPlanMessagesDataFromCategory($plan, $category, $keys = [], $fieldSuffix = '')
	{
		if (!$category)
		{
			return;
		}

		// Set multilingual messages
		if ($fieldSuffix)
		{
			foreach ($keys as $key)
			{
				$key = $key . $fieldSuffix;

				if (!OSMembershipHelper::isValidMessage($plan->{$key}) && !empty($category->{$key}))
				{
					$plan->{$key} = $category->{$key};
				}
			}
		}
		else
		{
			foreach ($keys as $key)
			{
				if (!OSMembershipHelper::isValidMessage($plan->{$key}) && !empty($category->{$key}))
				{
					$plan->{$key} = $category->{$key};
				}
			}
		}
	}

	/**
	 * Get field suffix used in sql query
	 *
	 * @param   string  $activeLanguage
	 *
	 * @return string
	 */
	public static function getFieldSuffix($activeLanguage = null)
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideHelper', 'getFieldSuffix'))
		{
			return OSMembershipHelperOverrideHelper::getFieldSuffix($activeLanguage);
		}

		$prefix = '';

		if ($activeLanguage !== '*' && Multilanguage::isEnabled())
		{
			if (!$activeLanguage)
			{
				$activeLanguage = Factory::getApplication()->getLanguage()->getTag();
			}

			if ($activeLanguage != self::getDefaultLanguage())
			{
				$languages = LanguageHelper::getLanguages('lang_code');

				if (isset($languages[$activeLanguage]))
				{
					$prefix = '_' . $languages[$activeLanguage]->sef;
				}
			}
		}

		return $prefix;
	}

	/**
	 * Function to get all available languages except the default language
	 * @return array languages object list
	 */
	public static function getLanguages()
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideHelper', 'getLanguages'))
		{
			return OSMembershipHelperOverrideHelper::getLanguages();
		}

		$languages = LanguageHelper::getLanguages('lang_code');
		unset($languages[self::getDefaultLanguage()]);

		return array_values($languages);
	}

	/**
	 * Get front-end default language
	 * @return string
	 */
	public static function getDefaultLanguage()
	{
		$params = ComponentHelper::getParams('com_languages');

		return $params->get('site', 'en-GB');
	}

	/**
	 * Synchronize Membership Pro database to support multilingual
	 *
	 * @return void
	 */
	public static function setupMultilingual()
	{
		$languages = self::getLanguages();

		if (count($languages))
		{
			/* @var DatabaseDriver $db */
			$db                  = Factory::getContainer()->get('db');
			$categoryTableFields = array_keys($db->getTableColumns('#__osmembership_categories'));
			$planTableFields     = array_keys($db->getTableColumns('#__osmembership_plans'));
			$fieldTableFields    = array_keys($db->getTableColumns('#__osmembership_fields'));
			$countryTableFields  = array_keys($db->getTableColumns('#__osmembership_countries'));

			$config = OSMembershipHelper::getConfig();

			foreach ($languages as $language)
			{
				$prefix = $language->sef;

				#Process for #__osmembership_categories table
				$varcharFields = [
					'alias',
					'title',
				];

				foreach ($varcharFields as $varcharField)
				{
					$fieldName = $varcharField . '_' . $prefix;

					if (!in_array($fieldName, $categoryTableFields))
					{
						$sql = "ALTER TABLE  `#__osmembership_categories` ADD  `$fieldName` VARCHAR( 255 );";
						$db->setQuery($sql);
						$db->execute();
					}
				}

				$textFields = [
					'description',
				];

				foreach ($textFields as $textField)
				{
					$fieldName = $textField . '_' . $prefix;

					if (!in_array($fieldName, $categoryTableFields))
					{
						$sql = "ALTER TABLE  `#__osmembership_categories` ADD  `$fieldName` TEXT NULL;";
						$db->setQuery($sql);
						$db->execute();
					}
				}

				#Process for #__osmembership_plans table
				if ($config->activate_super_simple_multilingual)
				{
					$varcharFields = [
						'alias',
						'title',
					];
				}
				elseif ($config->activate_simple_multilingual)
				{
					$varcharFields = [
						'alias',
						'title',
						'page_title',
						'page_heading',
						'meta_keywords',
						'meta_description',
					];
				}
				else
				{
					$varcharFields = [
						'alias',
						'title',
						'page_title',
						'page_heading',
						'meta_keywords',
						'meta_description',
						'user_email_subject',
						'subscription_approved_email_subject',
						'user_renew_email_subject',
					];
				}

				foreach ($varcharFields as $varcharField)
				{
					$fieldName = $varcharField . '_' . $prefix;

					if (!in_array($fieldName, $planTableFields))
					{
						$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `$fieldName` VARCHAR( 255 );";
						$db->setQuery($sql);

						try
						{
							$db->execute();
						}
						catch (\RuntimeException $e)
						{
							Factory::getApplication()->enqueueMessage($sql, CMSApplicationInterface::MSG_ERROR);

							return;
						}
					}
				}

				if ($config->activate_simple_multilingual)
				{
					$textFields = [
						'short_description',
						'description',
					];
				}
				else
				{
					$textFields = [
						'short_description',
						'description',
						'subscription_form_message',
						'user_email_body',
						'user_email_body_offline',
						'subscription_approved_email_body',
						'thanks_message',
						'thanks_message_offline',
						'user_renew_email_body',
						'user_renew_email_body_offline',
						'renew_thanks_message',
						'renew_thanks_message_offline',
						'user_upgrade_email_body',
						'user_upgrade_email_body_offline',
						'upgrade_thanks_message',
						'upgrade_thanks_message_offline',
						'card_layout',
					];
				}

				foreach ($textFields as $textField)
				{
					$fieldName = $textField . '_' . $prefix;

					if (!in_array($fieldName, $planTableFields))
					{
						$sql = "ALTER TABLE  `#__osmembership_plans` ADD  `$fieldName` TEXT NULL;";
						$db->setQuery($sql);

						try
						{
							$db->execute();
						}
						catch (\RuntimeException $e)
						{
							Factory::getApplication()->enqueueMessage($sql, CMSApplicationInterface::MSG_ERROR);

							return;
						}
					}
				}

				#Process for #__osmembership_fields table
				$varcharFields = [
					'title',
					'place_holder',
					'prompt_text',
					'validation_error_message',
				];

				foreach ($varcharFields as $varcharField)
				{
					$fieldName = $varcharField . '_' . $prefix;

					if (!in_array($fieldName, $fieldTableFields))
					{
						$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `$fieldName` VARCHAR( 255 );";
						$db->setQuery($sql);
						try
						{
							$db->execute();
						}
						catch (\RuntimeException $e)
						{
							Factory::getApplication()->enqueueMessage($sql, CMSApplicationInterface::MSG_ERROR);

							return;
						}
					}
				}

				$textFields = [
					'description',
					'values',
					'default_values',
					'fee_values',
					'depend_on_options',
				];

				foreach ($textFields as $textField)
				{
					$fieldName = $textField . '_' . $prefix;

					if (!in_array($fieldName, $fieldTableFields))
					{
						$sql = "ALTER TABLE  `#__osmembership_fields` ADD  `$fieldName` TEXT NULL;";
						$db->setQuery($sql);

						try
						{
							$db->execute();
						}
						catch (\RuntimeException $e)
						{
							Factory::getApplication()->enqueueMessage($sql, CMSApplicationInterface::MSG_ERROR);

							return;
						}
					}
				}

				$varcharFields = [
					'name',
				];

				foreach ($varcharFields as $varcharField)
				{
					$fieldName = $varcharField . '_' . $prefix;

					if (!in_array($fieldName, $countryTableFields))
					{
						$sql = "ALTER TABLE  `#__osmembership_countries` ADD  `$fieldName` VARCHAR( 255 ) DEFAULT '';";
						$db->setQuery($sql);

						try
						{
							$db->execute();
						}
						catch (\RuntimeException $e)
						{
							Factory::getApplication()->enqueueMessage($sql, CMSApplicationInterface::MSG_ERROR);

							return;
						}
					}
				}
			}
		}
	}

	/**
	 * Load bootstrap lib
	 */
	public static function loadBootstrap($loadJs = true)
	{
		$config = self::getConfig();

		if ($loadJs)
		{
			HTMLHelper::_('bootstrap.framework');
		}

		// Only load twitter bootstrap css if it is configured to use Bootstrap 2 or Bootstrap 5
		if ($config->load_twitter_bootstrap_in_frontend !== '0' && in_array(
				$config->get('twitter_bootstrap_version', 2),
				[2, 5]
			))
		{
			HTMLHelper::_('bootstrap.loadCss');
		}
	}

	/**
	 * Get Itemid of OS Membership Componnent
	 *
	 * @return int
	 */
	public static function getItemid()
	{
		JLoader::register('OSMembershipHelperRoute', JPATH_ROOT . '/components/com_osmembership/helper/route.php');

		return OSMembershipHelperRoute::getDefaultMenuItem();
	}

	/**
	 * This function is used to find the link to possible views in the component
	 *
	 * @param   array  $views
	 *
	 * @return string|NULL
	 */
	public static function getViewUrl($views = [])
	{
		$app       = Factory::getApplication();
		$menus     = $app->getMenu('site');
		$component = ComponentHelper::getComponent('com_osmembership');
		$items     = $menus->getItems('component_id', $component->id);

		foreach ($views as $view)
		{
			$viewUrl = 'index.php?option=com_osmembership&view=' . $view;

			foreach ($items as $item)
			{
				if (str_contains($item->link, $viewUrl))
				{
					if (!str_contains($item->link, 'Itemid='))
					{
						return Route::_($item->link . '&Itemid=' . $item->id);
					}

					return Route::_($item->link);
				}
			}
		}

		return;
	}

	/**
	 * Get country code
	 *
	 * @param   string  $countryName
	 *
	 * @return string
	 */
	public static function getCountryCode($countryName)
	{
		if (!$countryName)
		{
			$config      = self::getConfig();
			$countryName = $config->default_country;
		}

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('country_2_code')
			->from('#__osmembership_countries')
			->where('LOWER(name) = ' . $db->quote(StringHelper::strtolower($countryName)));
		$db->setQuery($query);
		$countryCode = $db->loadResult();

		if (!$countryCode)
		{
			$countryCode = 'US';
		}

		return $countryCode;
	}

	/***
	 * Get state full name
	 *
	 * @param $country
	 * @param $stateCode
	 *
	 * @return string
	 */
	public static function getStateName($country, $stateCode)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		if (!$country)
		{
			$config  = self::getConfig();
			$country = $config->default_country;
		}

		$query->select('a.state_name')
			->from('#__osmembership_states AS a')
			->innerJoin('#__osmembership_countries AS b ON a.country_id = b.id')
			->where('b.name = ' . $db->quote($country))
			->where('a.state_2_code = ' . $db->quote($stateCode));

		$db->setQuery($query);
		$state = $db->loadResult();

		return $state ?: $stateCode;
	}

	/**
	 * Get state_2_code of a state
	 *
	 * @param   string  $country
	 * @param   string  $state
	 *
	 * @return string
	 */
	public static function getStateCode($country, $state)
	{
		if (!$country)
		{
			$config  = self::getConfig();
			$country = $config->default_country;
		}

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('a.state_2_code')
			->from('#__osmembership_states AS a')
			->innerJoin('#__osmembership_countries AS b ON a.country_id = b.id')
			->where('b.name = ' . $db->quote($country))
			->where('a.state_name = ' . $db->quote($state));

		$db->setQuery($query);

		return $db->loadResult() ?: $state;
	}

	/**
	 * Get translated country
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return string
	 */
	public static function getTranslatedCountryName($row)
	{
		static $cache = [];

		if (!array_key_exists($row->id, $cache))
		{
			$fieldSuffix = static::getFieldSuffix($row->language);

			$translatedCountry = $row->country;

			if ($fieldSuffix && $row->country)
			{
				/* @var DatabaseDriver $db */
				$db    = Factory::getContainer()->get('db');
				$query = $db->getQuery(true)
					->select($db->quoteName('name' . $fieldSuffix))
					->from('#__osmembership_countries')
					->where('name = ' . $db->quote($row->country));
				$db->setQuery($query);
				$translatedCountry = $db->loadResult() ?: $row->country;
			}

			$cache[$row->id] = $translatedCountry;
		}

		return $cache[$row->id];
	}

	/**
	 * Load language from main component
	 */
	public static function loadLanguage()
	{
		static $loaded;

		if (!$loaded)
		{
			$lang = Factory::getApplication()->getLanguage();
			$tag  = $lang->getTag();

			if (!$tag)
			{
				$tag = 'en-GB';
			}

			$lang->load('com_osmembershipcommon', JPATH_ADMINISTRATOR, $tag);
			$lang->load('com_osmembership', JPATH_ROOT, $tag);
			$loaded = true;
		}
	}

	/**
	 * Load frontend language file for the subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	public static function loadSubscriptionLanguage($row)
	{
		$tag = $row->language;

		// Use site default language
		if (!$tag || $tag == '*')
		{
			$tag = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}

		$lang = Factory::getApplication()->getLanguage();

		$lang->load('com_osmembershipcommon', JPATH_ADMINISTRATOR, $tag);
		$lang->load('com_osmembership', JPATH_ROOT, $tag);
	}

	/**
	 * Method to get unique code for a field in #__eb_registrants table
	 *
	 * @param   string  $fieldName
	 * @param   string  $table
	 * @param   int     $length
	 *
	 * @return string
	 */
	public static function getUniqueCodeForField(
		$fieldName = 'transaction_id',
		$table = '#__osmemberhsip_subscribers',
		$length = 16
	) {
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		while (true)
		{
			$uniqueCode = UserHelper::genRandomPassword($length);
			$query->clear()
				->select('COUNT(*)')
				->from($table)
				->where($db->quoteName($fieldName) . ' = ' . $db->quote($uniqueCode));
			$db->setQuery($query);
			$total = $db->loadResult();

			if (!$total)
			{
				break;
			}
		}

		return $uniqueCode;
	}

	/**
	 * Display copy right information
	 */
	public static function displayCopyRight()
	{
		echo '<div class="copyright" style="text-align: center;margin-top: 5px;"><a href="http://joomdonation.com/joomla-extensions/membership-pro-joomla-membership-subscription.html" target="_blank"><strong>Membership Pro</strong></a> version ' . self::getInstalledVersion(
			) . ', Copyright (C) 2012-' . date(
				'Y'
			) . ' <a href="http://joomdonation.com" target="_blank"><strong>Ossolution Team</strong></a></div>';
	}

	public static function validateEngine()
	{
		$config     = self::getConfig();
		$dateFormat = $config->date_field_format ?: '%Y-%m-%d';
		$dateFormat = str_replace('%', '', $dateFormat);
		$dateNow    = HTMLHelper::_('date', Factory::getDate(), $dateFormat);
		//validate[required,custom[integer],min[-5]] text-input
		$validClass = [
			'validate[required]',
			'validate[required,custom[integer]]',
			'validate[required,custom[number]]',
			'validate[required,custom[email]]',
			'validate[required,custom[url]]',
			'validate[required,custom[phone]]',
			"validate[custom[date],past[$dateNow]]",
			'validate[required,custom[ipv4]]',
			'validate[required,minSize[6]]',
			'validate[required,maxSize[12]]',
			'validate[required,custom[integer],min[-5]]',
			'validate[required,custom[integer],max[50]]',
		];

		return json_encode($validClass);
	}

	public static function validateRules()
	{
		$config     = self::getConfig();
		$dateFormat = $config->date_field_format ?: '%Y-%m-%d';
		$dateFormat = str_replace('%', '', $dateFormat);
		$dateNow    = HTMLHelper::_('date', Factory::getDate(), $dateFormat);

		return [
			'',
			'custom[integer]',
			'custom[number]',
			'custom[email]',
			'custom[url]',
			'custom[phone]',
			'custom[date]',
			"custom[date],past[$dateNow]",
			"custom[date],future[$dateNow]",
			'custom[ipv4]',
			'custom[ipv6]',
			'minSize[6]',
			'maxSize[12]',
			'custom[integer],min[-5]',
			'custom[integer],max[50]]',
			'minCheckbox[2]',
			'maxCheckbox[2]',
			'custom[onlyNumberSp]',
			'custom[onlyLetterSp]',
			'custom[onlyLetterNumber]',
			'equals[FIELD_NAME]',
		];
	}

	/**
	 * Get exclude group ids of group members
	 *
	 * @return array
	 */
	public static function getGroupMemberExcludeGroupIds()
	{
		$plugin          = PluginHelper::getPlugin('osmembership', 'groupmembership');
		$params          = new Registry($plugin->params);
		$excludeGroupIds = $params->get('exclude_group_ids', [7, 8]);

		if (is_string($excludeGroupIds))
		{
			$excludeGroupIds = explode(',', $excludeGroupIds);
		}

		return ArrayHelper::toInteger($excludeGroupIds);
	}

	/**
	 * Get active membership plans
	 */
	public static function getActiveMembershipPlans($userId = 0, $excludes = [])
	{
		JLoader::register(
			'OSMembershipHelperSubscription',
			JPATH_ROOT . '/components/com_osmembership/helper/subscription.php'
		);

		return OSMembershipHelperSubscription::getActiveMembershipPlans($userId, $excludes);
	}

	/**
	 * Get total subscriptions based on status
	 *
	 * @param   int  $planId
	 * @param   int  $status
	 *
	 * @return int
	 */
	public static function countSubscribers($planId = 0, $status = -1)
	{
		$config = OSMembershipHelper::getConfig();

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__osmembership_subscribers');

		if ($planId)
		{
			$query->where('plan_id = ' . $planId);
		}

		if ($status != -1)
		{
			$query->where('published = ' . $status);
		}

		if (!$config->get('show_incomplete_payment_subscriptions', 1))
		{
			$query->where('(published != 0 OR payment_method LIKE "os_offline%" OR gross_amount = 0)');
		}

		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Check to see whether the current user can renew his membership using the given option
	 *
	 * @param   int  $renewOptionId
	 *
	 * @return bool
	 */
	public static function canRenewMembership($renewOptionId, $fromSubscriptionId)
	{
		return true;
	}

	/**
	 * Check to see whether the current user can upgrade his membership using the upgraded option
	 *
	 * @param   int  $upgradeOptionId
	 *
	 * @return bool
	 */
	public static function canUpgradeMembership($upgradeOptionId, $fromSubscriptionId)
	{
		return true;
	}

	/**
	 * Upgrade a membership
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	public static function processUpgradeMembership($row)
	{
		JLoader::register(
			'OSMembershipHelperSubscription',
			JPATH_ROOT . '/components/com_osmembership/helper/subscription.php'
		);

		OSMembershipHelperSubscription::processUpgradeMembership($row);
	}

	/**
	 * Get next membership id for this subscriber
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return int
	 */
	public static function getMembershipId($row = null)
	{
		$config = OSMembershipHelper::getConfig();

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('MAX(membership_id)')
			->from('#__osmembership_subscribers');

		if ($config->reset_membership_id)
		{
			$currentYear = date('Y');
			$query->where('YEAR(created_date) = ' . $currentYear)
				->where('is_profile = 1');
		}
		$db->setQuery($query);

		$membershipId = (int) $db->loadResult();
		$membershipId++;

		return max($membershipId, (int) $config->membership_id_start_number);
	}

	/**
	 * Get the invoice number for this subscription record
	 */
	public static function getInvoiceNumber($row)
	{
		$config = self::getConfig();

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('MAX(invoice_number)')
			->from('#__osmembership_subscribers');

		$currentYear       = date('Y');
		$row->invoice_year = $currentYear;

		if ($config->reset_invoice_number)
		{
			$query->where('invoice_year = ' . $currentYear);
		}

		$db->setQuery($query);
		$invoiceNumber = (int) $db->loadResult();
		$invoiceNumber++;

		return max($invoiceNumber, (int) $config->invoice_start_number);
	}

	/**
	 * Format invoice number
	 *
	 * @param $row
	 * @param $config
	 *
	 * @return mixed|string
	 */
	public static function formatInvoiceNumber($row, $config)
	{
		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideHelper', 'formatInvoiceNumber'))
		{
			return OSMembershipHelperOverrideHelper::formatInvoiceNumber($row, $config);
		}

		$date = Factory::getDate($row->created_date);

		if ($row->invoice_year)
		{
			$invoicePrefix = str_replace('[YEAR]', $row->invoice_year, $config->invoice_prefix);
		}
		elseif ($row->created_date)
		{
			$invoicePrefix = str_replace('[YEAR]', $date->format('Y'), $config->invoice_prefix);
		}
		else
		{
			$invoicePrefix = str_replace('[YEAR]', 0, $config->invoice_prefix);
		}

		$invoicePrefix = str_replace('[MONTH]', $date->format('m'), $invoicePrefix);
		$invoicePrefix = str_replace('[YEAR_LAST_2_DIGITS]', $date->format('y'), $invoicePrefix);
		$invoicePrefix = str_replace('[DATE]', $date->format($config->date_format), $invoicePrefix);

		return $invoicePrefix . str_pad($row->invoice_number, $config->invoice_number_length ?: 4, '0', STR_PAD_LEFT);
	}

	/**
	 * Format Membership Id
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param                                $config
	 *
	 * @return string
	 */
	public static function formatMembershipId($row, $config)
	{
		// If membership_id is not set for the subscription, make sense to return an empty string
		if (!$row->membership_id)
		{
			return '';
		}

		if (!$row->is_profile)
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->select('created_date')
				->from('#__osmembership_subscribers')
				->where('id = ' . (int) $row->profile_id);
			$db->setQuery($query);
			$createdDate = $db->loadResult();
		}
		else
		{
			$createdDate = $row->created_date;
		}

		$idPrefix = str_replace('[YEAR]', HTMLHelper::_('date', $createdDate, 'Y'), $config->membership_id_prefix);
		$idPrefix = str_replace('[MONTH]', HTMLHelper::_('date', $createdDate, 'm'), $idPrefix);

		if ($config->membership_id_length)
		{
			return $idPrefix . str_pad($row->membership_id, (int) $config->membership_id_length, '0', STR_PAD_LEFT);
		}

		return $idPrefix . $row->membership_id;
	}

	public static function getSubscriptionInvoiceOutput($row)
	{
		static $plans = [];

		$fieldSuffix = '';

		if (!isset($plans[$row->plan_id . '.' . $row->language]))
		{
			$fieldSuffix                                 = OSMembershipHelper::getFieldSuffix($row->language);
			$plans[$row->plan_id . '.' . $row->language] = OSMembershipHelperDatabase::getPlan(
				$row->plan_id,
				$fieldSuffix
			);
		}

		$rowPlan = $plans[$row->plan_id . '.' . $row->language];
		$config  = self::getConfig();

		if (self::isValidMessage($rowPlan->invoice_layout))
		{
			$invoiceOutput = $rowPlan->invoice_layout;
		}
		elseif ($fieldSuffix && OSMembershipHelper::isValidMessage($config->{'invoice_format' . $fieldSuffix}))
		{
			$invoiceOutput = $config->{'invoice_format' . $fieldSuffix};
		}
		else
		{
			$invoiceOutput = $config->invoice_format;
		}

		if ($config->invoice_container && str_contains($config->invoice_container, '[INVOICE_CONTENT]'))
		{
			$invoiceOutput = str_replace('[INVOICE_CONTENT]', $invoiceOutput, $config->invoice_container);
		}

		$replaces = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$row, $config]);

		$replaces['invoice_date'] = HTMLHelper::_('date', $row->created_date, $config->date_format);

		if ($row->published == 0)
		{
			$invoiceStatus = Text::_('OSM_INVOICE_STATUS_PENDING');
		}
		elseif (in_array($row->published, [1, 2]))
		{
			$invoiceStatus = Text::_('OSM_INVOICE_STATUS_PAID');
		}
		elseif ($row->published == 3)
		{
			$invoiceStatus = Text::_('OSM_INVOICE_STATUS_CANCELLED_PENDING');
		}
		elseif ($row->published == 4)
		{
			$invoiceStatus = Text::_('OSM_INVOICE_STATUS_CANCELLED_REFUNDED');
		}
		else
		{
			$invoiceStatus = Text::_('');
		}

		$replaces['setup_fee']              = self::formatCurrency($row->setup_fee, $config, $rowPlan->currency_symbol);
		$replaces['invoice_status']         = $invoiceStatus;
		$replaces['item_quantity']          = 1;
		$replaces['item_amount']            = $replaces['item_sub_total'] = self::formatCurrency(
			$row->amount,
			$config,
			$rowPlan->currency_symbol
		);
		$replaces['discount_amount']        = self::formatCurrency(
			$row->discount_amount,
			$config,
			$rowPlan->currency_symbol
		);
		$replaces['sub_total']              = self::formatCurrency(
			$row->amount + $row->setup_fee - $row->discount_amount,
			$config,
			$rowPlan->currency_symbol
		);
		$replaces['tax_amount']             = self::formatCurrency(
			$row->tax_amount,
			$config,
			$rowPlan->currency_symbol
		);
		$replaces['payment_processing_fee'] = self::formatCurrency(
			$row->payment_processing_fee,
			$config,
			$rowPlan->currency_symbol
		);
		$replaces['total_amount']           = self::formatCurrency(
			$row->gross_amount,
			$config,
			$rowPlan->currency_symbol
		);
		$replaces['tax_rate']               = self::formatAmount($row->tax_rate, $config) . '%';

		switch ($row->act)
		{
			case 'renew':
				$itemName = Text::_('OSM_PAYMENT_FOR_RENEW_SUBSCRIPTION');
				$itemName = str_replace('[PLAN_TITLE]', $rowPlan->title, $itemName);
				break;
			case 'upgrade':
				$itemName = Text::_('OSM_PAYMENT_FOR_UPGRADE_SUBSCRIPTION');
				$itemName = str_replace('[PLAN_TITLE]', $rowPlan->title, $itemName);

				/* @var DatabaseDriver $db */
				$db    = Factory::getContainer()->get('db');
				$query = $db->getQuery(true)
					->select('a.title')
					->from('#__osmembership_plans AS a')
					->innerJoin('#__osmembership_upgraderules AS b ON a.id = b.from_plan_id')
					->where('b.id = ' . $row->upgrade_option_id);
				$db->setQuery($query);
				$fromPlanTitle = $db->loadResult();
				$itemName      = str_replace('[FROM_PLAN_TITLE]', $fromPlanTitle, $itemName);
				break;
			default:
				$itemName = Text::_('OSM_PAYMENT_FOR_SUBSCRIPTION');
				$itemName = str_replace('[PLAN_TITLE]', $rowPlan->title, $itemName);
				break;
		}

		$replaces['item_name'] = $itemName;

		if ($row->published == 0)
		{
			$Itemid = OSMembershipHelperRoute::findView('payment', static::getItemid());
			$link   = Uri::root(
				) . 'index.php?option=com_osmembership&view=payment&transaction_id=' . $row->transaction_id . '&Itemid=' . $Itemid;

			$replaces['payment_link_url'] = $link;
			$replaces['payment_link']     = '<a href="' . $link . '">' . $link . '</a>';
		}
		else
		{
			$replaces['payment_link']     = '';
			$replaces['payment_link_url'] = '';
		}

		// Support [SUBSCRIPTION_DETAIL] tag
		$replaces['SUBSCRIPTION_DETAIL'] = OSMembershipHelper::getEmailContent($config, $row, false, 'register');

		$invoiceOutput = self::replaceUpperCaseTags($invoiceOutput, $replaces);

		return OSMembershipHelperHtml::processConditionalText($invoiceOutput);
	}

	/**
	 * Generate invoice PDF
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return  string
	 */
	public static function generateInvoicePDF($row)
	{
		self::loadSubscriptionLanguage($row);

		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideHelper', 'generateInvoicePDF'))
		{
			if (!class_exists('TCPDF'))
			{
				require_once JPATH_ROOT . '/components/com_osmembership/tcpdf/tcpdf.php';
			}

			return OSMembershipHelperOverrideHelper::generateInvoicePDF($row);
		}

		$config = OSMembershipHelper::getConfig();

		$invoiceNumber = self::formatInvoiceNumber($row, $config);
		$invoiceOutput = OSMembershipHelper::callOverridableHelperMethod(
			'Helper',
			'getSubscriptionInvoiceOutput',
			[$row]
		);

		//Filename
		$filePath = JPATH_ROOT . '/media/com_osmembership/invoices/' . File::makeSafe($invoiceNumber . '.pdf');

		OSMembershipHelperPDF::generatePDFFile($invoiceOutput, $filePath, ['type' => 'invoice', 'title' => 'Invoice']);

		return $filePath;
	}

	/**
	 * Generate PDF file contains exported subscriptions
	 *
	 * @param   array  $rows
	 * @param   array  $fields
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function generateSubscriptionsPDF($rows, $fields)
	{
		$pdfOutput = OSMembershipHelperHtml::loadSharedLayout(
			'common/tmpl/subscriptions_pdf.php',
			['rows' => $rows, 'fields' => $fields]
		);

		$pdfOutput = OSMembershipHelperHtml::processConditionalText($pdfOutput);

		//Filename
		$filePath = JPATH_ROOT . '/media/com_osmembership/subscriptions.pdf';

		OSMembershipHelperPdf::generatePDFFile(
			$pdfOutput,
			$filePath,
			['type' => 'subscriptions', 'title' => 'Subscriptions List']
		);

		return $filePath;
	}

	/**
	 * Generate invoice and return the path of the generated invoice
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return string
	 */
	public static function generateAndReturnInvoicePath($row)
	{
		$path = self::generateInvoicePDF($row);

		// Backward compatible check in case the generateInvoicePDF is overriden and no path returned
		if ($path === null)
		{
			$invoiceStorePath = JPATH_ROOT . '/media/com_osmembership/invoices/';
			$config           = self::getConfig();
			$invoiceNumber    = self::formatInvoiceNumber($row, $config);
			$path             = $invoiceStorePath . $invoiceNumber . '.pdf';
		}

		return $path;
	}

	/**
	 * Method to generate invoices PDF for multiple subscriptions
	 *
	 * @param   array  $rows
	 *
	 * @return string
	 */
	public static function generateSubscriptionsInvoices($rows)
	{
		OSMembershipHelper::loadLanguage();

		$pdfContents = [];

		foreach ($rows as $row)
		{
			$pdfContents[] = self::getSubscriptionInvoiceOutput($row);
		}

		$filename = File::makeSafe('subscriptions_invoices_' . Factory::getDate()->toSql() . '.pdf');
		$filePath = JPATH_ROOT . '/media/com_osmembership/invoices/' . $filename;

		OSMembershipHelperPdf::generatePDFFile(
			$pdfContents,
			$filePath,
			['type' => 'invoice', 'title' => 'Subscriptions Invoices']
		);

		return $filePath;
	}

	/**
	 * Get the original filename, without the timestamp prefix at the beginning
	 *
	 * @param   string  $filename
	 *
	 * @return string
	 */
	public static function getOriginalFilename($filename)
	{
		$pos = strpos($filename, '_');

		if ($pos !== false)
		{
			$timeInFilename = (int) substr($filename, 0, $pos);

			if ($timeInFilename > 5000)
			{
				$filename = substr($filename, $pos + 1);
			}
		}

		return $filename;
	}

	/**
	 * Convert relative URls to absolute URls
	 *
	 * @param   string  $content
	 *
	 * @return string
	 */
	public static function convertImgTags($content)
	{
		$siteUrl = Uri::root();

		// Replace none SEF URLs by absolute SEF URLs
		if (str_contains($content, 'href="index.php?'))
		{
			preg_match_all('#href="index.php\?([^"]+)"#m', $content, $matches);

			foreach ($matches[1] as $urlQueryString)
			{
				$content = str_replace(
					'href="index.php?' . $urlQueryString . '"',
					'href="' . Route::link('site', 'index.php?' . $urlQueryString, 0, true) . '"',
					$content
				);
			}
		}

		// Replace relative urls, image urls with absolute Urls
		$protocols  = '[a-zA-Z0-9\-]+:';
		$attributes = ['href=', 'src='];

		foreach ($attributes as $attribute)
		{
			if (str_contains($content, $attribute))
			{
				$regex = '#\s' . $attribute . '"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';

				$content = preg_replace($regex, ' ' . $attribute . '"' . $siteUrl . '$1"', $content);
			}
		}

		return $content;
	}

	/**
	 * Build list of tags which will be used on emails & messages
	 *
	 * @param $row
	 * @param $config
	 *
	 * @return array
	 */
	public static function buildTags($row, $config)
	{
		/* @var DatabaseDriver $db */
		$db          = Factory::getContainer()->get('db');
		$query       = $db->getQuery(true);
		$fieldSuffix = OSMembershipHelper::getFieldSuffix($row->language);
		$rowPlan     = OSMembershipHelperDatabase::getPlan($row->plan_id, $fieldSuffix);

		if ($row->state)
		{
			$row->state = self::getStateName($row->country, $row->state);
		}

		$replaces                    = [];
		$replaces['id']              = $row->id;
		$replaces['user_id']         = $row->user_id;
		$replaces['profile_id']      = $row->profile_id;
		$replaces['name']            = trim($row->first_name . ' ' . $row->last_name);
		$replaces['country_code']    = self::getCountryCode($row->country);
		$replaces['subscription_id'] = $row->subscription_id;
		$replaces['amount']          = self::formatAmount($row->amount, $config);
		$replaces['discount_amount'] = self::formatAmount($row->discount_amount, $config);
		$replaces['tax_amount']      = self::formatAmount($row->tax_amount, $config);

		// Special support for Canadian Tax
		$replaces['TPS_TAX'] = self::formatAmount($row->tax_amount * 5 / 14.975, $config);
		$replaces['TVQ_TAX'] = self::formatAmount($row->tax_amount * 9.975 / 14.975, $config);

		$replaces['gross_amount']           = self::formatAmount($row->gross_amount, $config);
		$replaces['payment_processing_fee'] = self::formatAmount($row->payment_processing_fee, $config);
		$replaces['currency']               = $rowPlan->currency_symbol ?: $config->currency_symbol;

		$replaces['amount_with_currency']                 = self::formatCurrency(
			$row->amount,
			$config,
			$rowPlan->currency_symbol
		);
		$replaces['discount_amount_with_currency']        = self::formatCurrency(
			$row->discount_amount,
			$config,
			$rowPlan->currency_symbol
		);
		$replaces['tax_amount_with_currency']             = self::formatCurrency(
			$row->tax_amount,
			$config,
			$rowPlan->currency_symbol
		);
		$replaces['gross_amount_with_currency']           = self::formatCurrency(
			$row->gross_amount,
			$config,
			$rowPlan->currency_symbol
		);
		$replaces['payment_processing_fee_with_currency'] = self::formatCurrency(
			$row->payment_processing_fee,
			$config,
			$rowPlan->currency_symbol
		);

		$replaces['tax_rate']                  = self::formatAmount($row->tax_rate, $config);
		$replaces['from_date']                 = HTMLHelper::_('date', $row->from_date, $config->date_format);
		$replaces['to_date']                   = HTMLHelper::_('date', $row->to_date, $config->date_format);
		$replaces['created_date']              = HTMLHelper::_('date', $row->created_date, $config->date_format);
		$replaces['created_hour']              = HTMLHelper::_('date', $row->created_date, 'H');
		$replaces['created_minute']            = HTMLHelper::_('date', $row->created_date, 'i');
		$replaces['date']                      = HTMLHelper::_('date', 'Now', $config->date_format);
		$replaces['end_date']                  = $replaces['to_date'];
		$replaces['published']                 = $row->published;
		$replaces['payment_method_name']       = $row->payment_method;
		$replaces['subscription_code']         = $row->subscription_code;
		$replaces['renew_option_id']           = (int) $row->renew_option_id;
		$replaces['upgrade_option_id']         = (int) $row->upgrade_option_id;
		$replaces['upgrade_from_plan_to_plan'] = '';

		$upgradeOption = null;

		if ($row->upgrade_option_id > 0)
		{
			$upgradeOption = OSMembershipHelperDatabase::getUpgradeRule($row->upgrade_option_id);
		}

		if ($upgradeOption)
		{
			$replaces['upgrade_from_plan_to_plan'] = $upgradeOption->from_plan_id . '_' . $upgradeOption->to_plan_id;
		}

		if ($row->payment_method && $method = OSMembershipHelperPayments::loadPaymentMethod($row->payment_method))
		{
			$replaces['payment_method'] = Text::_($method->title);
		}
		else
		{
			$replaces['payment_method'] = '';
		}

		if ($row->vies_registered)
		{
			$replaces['vies_registered'] = Text::_('OSM_VIES_REGISTERED');
		}
		else
		{
			$replaces['vies_registered'] = '';
		}

		$Itemid = OSMembershipHelperRoute::findView('payment', static::getItemid());

		$replaces['payment_link'] = Route::link(
			'site',
			'index.php?option=com_osmembership&view=payment&transaction_id=' . $row->transaction_id . '&Itemid=' . $Itemid,
			false,
			0,
			true
		);

		if ((int) $row->payment_date)
		{
			$replaces['payment_date'] = HTMLHelper::_('date', $row->payment_date, $config->date_format);
		}
		else
		{
			$replaces['payment_date'] = '';
		}

		if ($row->tax_amount == 0)
		{
			$replaces['free_tax_rate_text'] = Text::_('OSM_FREE_TAX_RATE_TEXT');
		}
		else
		{
			$replaces['free_tax_rate_text'] = '';
		}

		// Support avatar tags
		if ($row->avatar && file_exists(JPATH_ROOT . '/media/com_osmembership/avatars/' . $row->avatar))
		{
			$replaces['avatar'] = '<img class="oms-avatar" src="media/com_osmembership/avatars/' . $row->avatar . '"/>';
		}
		else
		{
			$replaces['avatar'] = '';
		}

		if ($row->username && $row->user_password)
		{
			$replaces['username'] = $row->username;
			//Password
			$replaces['password'] = OSMembershipHelperCryptor::decrypt($row->user_password);
		}
		elseif ($row->username)
		{
			$replaces['username'] = $row->username;
		}
		elseif ($row->user_id)
		{
			$query->select('username')
				->from('#__users')
				->where('id = ' . (int) $row->user_id);
			$db->setQuery($query);
			$replaces['username'] = $db->loadResult();
		}
		else
		{
			$replaces['username'] = '';
		}

		if ($row->coupon_id)
		{
			$query->clear()
				->select($db->quoteName('code'))
				->from('#__osmembership_coupons')
				->where('id = ' . $row->coupon_id);
			$db->setQuery($query);
			$replaces['coupon_code'] = $db->loadResult();
		}
		else
		{
			$replaces['coupon_code'] = '';
		}

		$replaces['transaction_id'] = $row->transaction_id;
		$replaces['membership_id']  = self::formatMembershipId($row, $config);

		// Support [ITEM_NAME] tag
		switch ($row->act)
		{
			case 'renew':
				$itemName = Text::_('OSM_PAYMENT_FOR_RENEW_SUBSCRIPTION');
				$itemName = str_replace('[PLAN_TITLE]', $rowPlan->title, $itemName);
				break;
			case 'upgrade':
				$itemName = Text::_('OSM_PAYMENT_FOR_UPGRADE_SUBSCRIPTION');
				$itemName = str_replace('[PLAN_TITLE]', $rowPlan->title, $itemName);

				$query->clear()
					->select('a.title')
					->from('#__osmembership_plans AS a')
					->innerJoin('#__osmembership_upgraderules AS b ON a.id = b.from_plan_id')
					->where('b.id = ' . $row->upgrade_option_id);
				$db->setQuery($query);
				$fromPlanTitle = $db->loadResult();
				$itemName      = str_replace('[FROM_PLAN_TITLE]', $fromPlanTitle, $itemName);
				break;
			default:
				$itemName = Text::_('OSM_PAYMENT_FOR_SUBSCRIPTION');
				$itemName = str_replace('[PLAN_TITLE]', $rowPlan->title, $itemName);
				break;
		}

		$replaces['act']       = $row->act ?: 'subscribe';
		$replaces['item_name'] = $itemName;

		if ($row->invoice_number > 0)
		{
			$replaces['invoice_number'] = self::formatInvoiceNumber($row, $config);
		}
		else
		{
			$replaces['invoice_number'] = '';
		}

		if ($row->formatted_invoice_number)
		{
			$replaces['formatted_invoice_number'] = $row->formatted_invoice_number;
		}
		else
		{
			$replaces['formatted_invoice_number'] = '';
		}

		if ($row->refunded)
		{
			$replaces['refunded'] = Text::_('JYES');
		}
		else
		{
			$replaces['refunded'] = Text::_('JNO');
		}

		switch ($row->published)
		{
			case 0 :
				$replaces['subscription_status'] = Text::_('OSM_PENDING');
				break;
			case 1 :
				$replaces['subscription_status'] = Text::_('OSM_ACTIVE');
				break;
			case 2 :
				$replaces['subscription_status'] = Text::_('OSM_EXPIRED');
				break;
			case 3 :
				$replaces['subscription_status'] = Text::_('OSM_CANCELLED_PENDING');
				break;
			case 4 :
				$replaces['subscription_status'] = Text::_('OSM_CANCELLED_REFUNDED');
				break;
			default:
				$replaces['subscription_status'] = 'Unknown';
				break;
		}

		switch ($row->plan_subscription_status)
		{
			case 0 :
				$replaces['plan_subscription_status'] = Text::_('OSM_PENDING');
				break;
			case 1 :
				$replaces['plan_subscription_status'] = Text::_('OSM_ACTIVE');
				break;
			case 2 :
				$replaces['plan_subscription_status'] = Text::_('OSM_EXPIRED');
				break;
			case 3 :
				$replaces['plan_subscription_status'] = Text::_('OSM_CANCELLED_PENDING');
				break;
			case 4 :
				$replaces['plan_subscription_status'] = Text::_('OSM_CANCELLED_REFUNDED');
				break;
			default:
				$replaces['plan_subscription_status'] = 'Unknown';
				break;
		}

		// Add support for payment status

		if ($row->published == 0)
		{
			$replaces['payment_status'] = Text::_('OSM_PAYMENT_PENDING');
		}
		elseif ($row->published == 1)
		{
			$replaces['payment_status'] = Text::_('OSM_PAYMENT_PAID');
		}
		elseif (in_array($row->published, [3, 4]))
		{
			$replaces['payment_status'] = Text::_('OSM_PAYMENT_CANCELLED');
		}
		else
		{
			$replaces['payment_status'] = '';
		}

		// Add total payment amount field
		if ($rowPlan->recurring_subscription && $rowPlan->number_payments > 0)
		{
			$totalPaymentAmount               = $rowPlan->number_payments * $row->gross_amount;
			$replaces['total_payment_amount'] = static::formatCurrency(
				$totalPaymentAmount,
				$config,
				$rowPlan->currency_symbol
			);
		}
		else
		{
			$replaces['total_payment_amount'] = static::formatCurrency(
				$row->gross_amount,
				$config,
				$rowPlan->currency_symbol
			);
		}

		if ($rowPlan->number_group_members > 0 || $rowPlan->number_members_field)
		{
			$joinGroupLink = OSMembershipHelperRoute::getViewRoute(
					'group',
					OSMembershipHelper::getItemid()
				) . '&group_id=' . $row->subscription_code;

			$replaces['join_group_link'] = Route::link('site', $joinGroupLink, false, 0, true);

			$query->clear()
				->select('*')
				->from('#__osmembership_subscribers')
				->where('plan_id = ' . $row->plan_id)
				->where('group_admin_id = ' . $row->user_id)
				->order('id');
			$db->setQuery($query);
			$rowMembers = $db->loadObjectList();

			$replaces['group_members'] = OSMembershipHelperHtml::loadCommonLayout(
				'common/tmpl/group_members.php',
				['rowMembers' => $rowMembers]
			);
		}
		else
		{
			$replaces['join_group_link'] = '';
			$replaces['group_members']   = '';
		}

		// Support for name of custom field in tags
		$query->clear()
			->select('field_id, field_value')
			->from('#__osmembership_field_value')
			->where('subscriber_id = ' . $row->id);
		$db->setQuery($query);
		$rowValues = $db->loadObjectList('field_id');

		$query->clear()
			->select('id, name, is_core, fieldtype, fee_field')
			->from('#__osmembership_fields AS a')
			->where('a.published = 1');
		$db->setQuery($query);
		$rowFields = $db->loadObjectList();

		for ($i = 0, $n = count($rowFields); $i < $n; $i++)
		{
			$rowField = $rowFields[$i];

			if ($rowField->is_core)
			{
				$replaces[$rowField->name] = $row->{$rowField->name};
			}
			elseif (isset($rowValues[$rowField->id]))
			{
				$fieldValue = $rowValues[$rowField->id]->field_value;

				if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
				{
					$fieldValue = implode(', ', json_decode($fieldValue));
				}

				if ($fieldValue)
				{
					switch ($rowField->fieldtype)
					{
						case 'Date':
							try
							{
								$fieldValue = HTMLHelper::_('date', $fieldValue, $config->date_format, null);
							}
							catch (Exception $e)
							{
								// Do nothing;
							}
							break;
						case 'File':
							if ($fieldValue && OSMembershipHelper::isImageFilename($fieldValue))
							{
								$imgSrc     = Uri::root(true) . '/media/com_osmembership/upload/' . $fieldValue;
								$fieldValue = '<img src="' . $imgSrc . '" class="osm-uploaded-image"/>';
							}
							break;
						case 'Textarea':
							$fieldValue = nl2br($fieldValue);
							break;
					}
				}

				$replaces[$rowField->name] = $fieldValue;
			}
			else
			{
				$replaces[$rowField->name] = '';
			}
		}

		// Build plan replaced tags
		$replaces['plan_short_description'] = $rowPlan->short_description;
		$replaces['plan_description']       = $rowPlan->description;
		$replaces['plan_id']                = $rowPlan->id;
		$replaces['plan_title']             = $rowPlan->title;
		$replaces['plan_alias']             = $rowPlan->alias;
		$replaces['plan_price']             = static::formatAmount($rowPlan->price, $config);

		if ($rowPlan->lifetime_membership)
		{
			$replaces['plan_duration'] = Text::_('OSM_LIFETIME');
		}
		else
		{
			$replaces['plan_duration'] = OSMembershipHelper::callOverridableHelperMethod(
				'Subscription',
				'getDurationText',
				[$rowPlan->subscription_length, $rowPlan->subscription_length_unit]
			);
		}

		$Itemid = OSMembershipHelperRoute::getPlanMenuId(
			$rowPlan->id,
			$rowPlan->category_id,
			OSMembershipHelperRoute::getDefaultMenuItem()
		);

		if ($rowPlan->category_id > 0)
		{
			$query->clear()
				->select($db->quoteName('title' . $fieldSuffix))
				->from('#__osmembership_categories')
				->where('id = ' . $rowPlan->category_id);
			$db->setQuery($query);

			$replaces['category'] = $db->loadResult();
			$url                  = 'index.php?option=com_osmembership&view=plan&catid=' . $rowPlan->category_id . '&id=' . $rowPlan->id . '&Itemid=' . $Itemid;
		}
		else
		{
			$replaces['category'] = '';
			$url                  = 'index.php?option=com_osmembership&view=plan&catid=' . $rowPlan->category_id . '&id=' . $rowPlan->id . '&Itemid=' . $Itemid;
		}

		$replaces['PLAN_URL'] = Route::link('site', $url, false, 0, true);

		// Custom Fields
		if (file_exists(JPATH_ROOT . '/components/com_osmembership/fields.xml')
			&& filesize(JPATH_ROOT . '/components/com_osmembership/fields.xml') > 0)
		{
			$registry = new Registry($rowPlan->custom_fields);

			foreach ($registry->toArray() as $key => $value)
			{
				if (!isset($replaces[$key]))
				{
					$replaces[$key] = $value;
				}
			}
		}

		$params              = new Registry($row->params);
		$replaces['user_ip'] = $params->get('user_ip');

		if ($row->country)
		{
			$replaces['country'] = static::getTranslatedCountryName($row);
		}

		// Common tags
		if ($config->common_tags)
		{
			$commonTags = json_decode($config->common_tags, true);

			foreach ($commonTags as $commonTag)
			{
				$tagName = $commonTag['name'];

				if (!is_string($tagName))
				{
					continue;
				}

				$tagName = self::normalizeCommonTagName($tagName);

				$replaces[$tagName] = $commonTag['value'];
			}
		}

		// Custom Fee Fields Fee Values
		$fieldsFeeValues = (array) $params->get('fields_fee_values', []);

		foreach ($fieldsFeeValues as $fieldName => $feeValue)
		{
			$replaces[$fieldName . '_fee_value'] = static::formatAmount($feeValue, $config);
		}

		foreach ($rowFields as $rowField)
		{
			if ($rowField->fee_field && !isset($replaces[$rowField->name . '_fee_value']))
			{
				$replaces[$rowField->name . '_fee_value'] = static::formatAmount(0, $config);
			}
		}

		// Set none-scalar value to empty string to prevent warnings or fatal error
		foreach ($replaces as $key => $value)
		{
			if (!is_scalar($value))
			{
				$replaces[$key] = '';
			}
		}

		if (!array_key_exists('language', $replaces))
		{
			$replaces['language'] = $row->language;
		}

		$replaces['eb_coupon_code']          = '';
		$replaces['eb_coupon_amount']        = '';
		$replaces['eb_coupon_discount_type'] = '';

		if ($row->eb_coupon_id > 0)
		{
			$query->clear()
				->select('*')
				->from('#__eb_coupons')
				->where('id = ' . $row->eb_coupon_id);
			$db->setQuery($query);
			$rowCoupon = $db->loadObject();

			if ($rowCoupon)
			{
				$replaces['eb_coupon_code']   = $rowCoupon->code;
				$replaces['eb_coupon_amount'] = OSMembershipHelper::formatAmount($rowCoupon->discount, $config);

				if (ComponentHelper::isEnabled('com_eventbooking'))
				{
					require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

					$ebConfig = \EventbookingHelper::getConfig();

					if ($rowCoupon->coupon_type == 0)
					{
						$replaces['eb_coupon_discount_type'] = '%';
					}
					else
					{
						$replaces['eb_coupon_discount_type'] = $ebConfig->currency_symbol;
					}
				}
			}
		}

		return $replaces;
	}

	/***
	 * Method to build tags for replacing in SMS message
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return array
	 */
	public static function buildSMSTags($row)
	{
		$config = OSMembershipHelper::getConfig();

		$replaces = [];

		$replaces['plan_id'] = $row->plan_id;

		if (isset($row->plan_title))
		{
			$replaces['plan_title'] = $row->plan_title;
		}

		$fields = [
			'id',
			'first_name',
			'last_name',
			'organization',
			'address',
			'address2',
			'city',
			'zip',
			'state',
			'country',
			'phone',
			'fax',
			'email',
			'comment',
		];

		foreach ($fields as $field)
		{
			$replaces[$field] = $row->{$field};
		}

		$replaces['from_date']    = HTMLHelper::_('date', $row->from_date, $config->date_format);
		$replaces['to_date']      = HTMLHelper::_('date', $row->to_date, $config->date_format);
		$replaces['created_date'] = HTMLHelper::_('date', $row->created_date, $config->date_format);
		$replaces['end_date']     = $replaces['expire_date'] = $replaces['to_date'];

		if ($row->act == 'upgrade')
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true)
				->select('a.title')
				->from('#__osmembership_plans AS a')
				->innerJoin('#__osmembership_upgraderules AS b ON a.id = b.from_plan_id')
				->where('b.id = ' . (int) $row->upgrade_option_id);
			$db->setQuery($query);

			$replaces['from_plan_title'] = $db->loadResult();
		}

		return $replaces;
	}

	/**
	 * Send email to super administrator and user
	 *
	 * @param   object  $row
	 * @param   object  $config
	 */
	public static function sendEmails($row, $config)
	{
		OSMembershipHelperMail::sendEmails($row, $config);
	}

	/**
	 * Send email to subscriber to inform them that their membership approved (and activated)
	 *
	 * @param   object  $row
	 */
	public static function sendMembershipApprovedEmail($row)
	{
		OSMembershipHelperMail::sendMembershipApprovedEmail($row);
	}

	/**
	 * Send confirmation email to subscriber and notification email to admin when a recurring subscription cancelled
	 *
	 * @param $row
	 * @param $config
	 */
	public static function sendSubscriptionCancelEmail($row, $config)
	{
		OSMembershipHelperMail::sendSubscriptionCancelEmail($row, $config);
	}

	/**
	 * Send notification emailt o admin when someone update his profile
	 *
	 * @param $row
	 * @param $config
	 */
	public static function sendProfileUpdateEmail($row, $config)
	{
		OSMembershipHelperMail::sendProfileUpdateEmail($row, $config);
	}

	/**
	 * Format currency based on config parametters
	 *
	 * @param   Float   $amount
	 * @param   Object  $config
	 * @param   string  $currencySymbol
	 *
	 * @return string
	 */
	public static function formatCurrency($amount, $config, $currencySymbol = null)
	{
		$decimals      = isset($config->decimals) ? (int) $config->decimals : 2;
		$dec_point     = $config->dec_point ?? '.';
		$thousands_sep = $config->thousands_sep ?? ',';
		$symbol        = $currencySymbol ?: $config->currency_symbol;

		return $config->currency_position ? (number_format(
				(float) $amount,
				$decimals,
				$dec_point,
				$thousands_sep
			) . $symbol) : ($symbol .
			number_format((float) $amount, $decimals, $dec_point, $thousands_sep));
	}

	public static function formatAmount($amount, $config)
	{
		$decimals      = isset($config->decimals) ? (int) $config->decimals : 2;
		$dec_point     = $config->dec_point ?? '.';
		$thousands_sep = $config->thousands_sep ?? ',';

		return number_format((float) $amount, $decimals, $dec_point, $thousands_sep);
	}

	/**
	 * Get detail information of the subscription
	 *
	 * @param   MPFConfig                    $config
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   bool                         $toAdmin
	 * @param   string                       $view
	 *
	 * @return string
	 */
	public static function getEmailContent($config, $row, $toAdmin = false, $view = null)
	{
		/* @var DatabaseDriver $db */
		$db          = Factory::getContainer()->get('db');
		$query       = $db->getQuery(true);
		$fieldSuffix = self::getFieldSuffix($row->language);
		$query->select($db->quoteName('title' . $fieldSuffix, 'title'))
			->select('lifetime_membership')
			->select('currency, currency_symbol')
			->from('#__osmembership_plans')
			->where('id = ' . $row->plan_id);
		$db->setQuery($query);
		$plan = $db->loadObject();

		$data                       = [];
		$data['planTitle']          = $plan->title;
		$data['lifetimeMembership'] = $plan->lifetime_membership;
		$data['config']             = $config;
		$data['row']                = $row;
		$data['toAdmin']            = $toAdmin;

		$data['currencySymbol'] = $plan->currency_symbol ?: $plan->currency;

		if ($row->payment_method == 'os_offline_creditcard')
		{
			$cardNumber          = Factory::getApplication()->getInput()->getString('x_card_num');
			$last4Digits         = substr($cardNumber, strlen($cardNumber) - 4);
			$data['last4Digits'] = $last4Digits;
		}

		if ($row->user_id)
		{
			$query->clear()
				->select('username')
				->from('#__users')
				->where('id = ' . $row->user_id);
			$db->setQuery($query);
			$username         = $db->loadResult();
			$data['username'] = $username;
		}

		if ($row->username && $row->user_password)
		{
			$data['username'] = $row->username;
			//Password
			$data['password'] = OSMembershipHelperCryptor::decrypt($row->user_password);
		}

		if ($row->user_id > 0)
		{
			$userId = $row->user_id;
		}
		else
		{
			$userId = null;
		}

		$rowFields = OSMembershipHelper::getProfileFields(
			$row->plan_id,
			true,
			$row->language,
			$row->act,
			$view,
			$userId
		);
		$formData  = OSMembershipHelper::getProfileData($row, $row->plan_id, $rowFields);
		$form      = new MPFForm($rowFields);
		$form->setData($formData)->bindData();
		$form->buildFieldsDependency(false);
		$form->handleFieldsDependOnPaymentMethod($row->payment_method);
		$data['form'] = $form;

		$params = ComponentHelper::getParams('com_users');

		if (!$params->get('sendpassword', 1) && isset($data['password']))
		{
			unset($data['password']);
		}

		return OSMembershipHelperHtml::loadSharedLayout('emailtemplates/tmpl/email.php', $data);
	}

	/**
	 * Get recurring frequency from subscription length
	 *
	 * @param   int  $subscriptionLength
	 *
	 * @return array
	 */
	public static function getRecurringSettingOfPlan($subscriptionLength)
	{
		if (($subscriptionLength >= 365) && ($subscriptionLength % 365 == 0))
		{
			$frequency = 'Y';
			$length    = $subscriptionLength / 365;
		}
		elseif (($subscriptionLength >= 30) && ($subscriptionLength % 30 == 0))
		{
			$frequency = 'M';
			$length    = $subscriptionLength / 30;
		}
		elseif (($subscriptionLength >= 7) && ($subscriptionLength % 7 == 0))
		{
			$frequency = 'W';
			$length    = $subscriptionLength / 7;
		}
		else
		{
			$frequency = 'D';
			$length    = $subscriptionLength;
		}

		return [$frequency, $length];
	}

	/**
	 * Create an user account based on the entered data
	 *
	 * @param $data
	 *
	 * @return int
	 * @throws Exception
	 */
	public static function saveRegistration($data)
	{
		$config = OSMembershipHelper::getConfig();

		if (!empty($config->use_cb_api))
		{
			return static::userRegistrationCB(
				$data['first_name'],
				$data['last_name'],
				$data['email'],
				$data['username'],
				$data['password1']
			);
		}

		//Need to load com_users language file
		$lang = Factory::getApplication()->getLanguage();
		$tag  = $lang->getTag();

		if (!$tag)
		{
			$tag = 'en-GB';
		}

		$lang->load('com_users', JPATH_ROOT, $tag);
		$userData             = [];
		$userData['username'] = $data['username'];
		$userData['name']     = trim($data['first_name'] . ' ' . $data['last_name']);
		$userData['password'] = $userData['password1'] = $userData['password2'] = $data['password1'];
		$userData['email']    = $userData['email1'] = $userData['email2'] = $data['email'];
		$sendActivationEmail  = $config->send_activation_email;

		if ($sendActivationEmail)
		{
			Form::addFormPath(JPATH_ROOT . '/components/com_users/forms');

			/* @var RegistrationModel $model */
			$model = Factory::getApplication()->bootComponent('com_users')
				->getMVCFactory()->createModel('Registration', 'Site', ['ignore_request' => true]);

			$result = $model->register($userData);

			if ($result === false)
			{
				self::logCreateUserError($userData, $model->getError());
			}

			// User is successfully saved, we will return user id based on username
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__users')
				->where('username=' . $db->quote($data['username']));

			$db->setQuery($query);
			$userId = (int) $db->loadResult();

			if (!$userId)
			{
				throw new Exception($model->getError());
			}

			return $userId;
		}

		$params         = ComponentHelper::getParams('com_users');
		$userActivation = $params->get('useractivation');

		if (($userActivation == 1) || ($userActivation == 2))
		{
			$userData['activation'] = ApplicationHelper::getHash(UserHelper::genRandomPassword());
			$userData['block']      = 1;
		}

		$userData['groups']   = [];
		$userData['groups'][] = $params->get('new_usertype', 2);
		$user                 = new User();

		if (!$user->bind($userData))
		{
			self::logCreateUserError($userData, $user->getError());

			throw new Exception(Text::sprintf('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError()));
		}

		// Store the data.
		if (!$user->save())
		{
			self::logCreateUserError($userData, $user->getError());

			throw new Exception(Text::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()));
		}

		return $user->id;
	}

	/**
	 * Helper method to allow logging error message when user account could not be created
	 *
	 * @param   array   $data
	 * @param   string  $errorMessage
	 *
	 * @return void
	 */
	public static function logCreateUserError($data, $errorMessage)
	{
		unset($data['password'], $data['password1'], $data['password2']);

		// Log the error
		OSMembershipHelper::logData(
			JPATH_ROOT . '/components/com_osmembership/user_creation_error.txt',
			$data,
			$errorMessage
		);
	}

	/**
	 * Use CB API for saving user account
	 *
	 * @param       $firstName
	 * @param       $lastName
	 * @param       $email
	 * @param       $username
	 * @param       $password
	 *
	 * @return int
	 */
	public static function userRegistrationCB($firstName, $lastName, $email, $username, $password)
	{
		global $_CB_framework, $_PLUGINS, $ueConfig;

		include_once JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php';
		cbimport('cb.html');
		cbimport('cb.plugins');

		$approval     = $ueConfig['reg_admin_approval'];
		$confirmation = ($ueConfig['reg_confirmation']);
		$user         = new UserTable();

		$user->set('username', $username);
		$user->set('email', $email);
		$user->set('name', trim($firstName . ' ' . $lastName));
		$user->set('gids', [(int) $_CB_framework->getCfg('new_usertype')]);
		$user->set('sendEmail', 0);
		$user->set('registerDate', $_CB_framework->getUTCDate());
		$user->set('password', $user->hashAndSaltPassword($password));
		$user->set('registeripaddr', cbGetIPlist());

		if ($approval == 0)
		{
			$user->set('approved', 1);
		}
		else
		{
			$user->set('approved', 0);
		}

		if ($confirmation == 0)
		{
			$user->set('confirmed', 1);
		}
		else
		{
			$user->set('confirmed', 0);
		}

		if (($user->get('confirmed') == 1) && ($user->get('approved') == 1))
		{
			$user->set('block', 0);
		}
		else
		{
			$user->set('block', 1);
		}

		$_PLUGINS->trigger('onBeforeUserRegistration', [&$user, &$user]);

		if ($user->store())
		{
			if ($user->get('confirmed') == 0)
			{
				$user->store();
			}

			$messagesToUser = activateUser($user, 1, 'UserRegistration');

			$_PLUGINS->trigger('onAfterUserRegistration', [&$user, &$user, true]);

			return $user->get('id');
		}

		return 0;
	}

	/**
	 * Get base URL of the site
	 *
	 * @return mixed|string
	 * @throws Exception
	 */
	public static function getSiteUrl()
	{
		$uri  = Uri::getInstance();
		$base = $uri->toString(['scheme', 'host', 'port']);

		if (str_contains(php_sapi_name(), 'cgi') && !ini_get('cgi.fix_pathinfo') && !empty($_SERVER['REQUEST_URI']))
		{
			$script_name = $_SERVER['PHP_SELF'];
		}
		else
		{
			$script_name = $_SERVER['SCRIPT_NAME'];
		}

		$path = rtrim(dirname($script_name), '/\\');

		if ($path)
		{
			$siteUrl = $base . $path . '/';
		}
		else
		{
			$siteUrl = $base . '/';
		}

		if (Factory::getApplication()->isClient('administrator'))
		{
			$adminPos = strrpos($siteUrl, 'administrator/');
			$siteUrl  = substr_replace($siteUrl, '', $adminPos, 14);
		}

		$config = self::getConfig();

		if ($config->use_https)
		{
			$siteUrl = str_replace('http://', 'https://', $siteUrl);
		}

		return $siteUrl;
	}

	/**
	 * Try to determine the best match url which users should be redirected to when they access to restricted resource
	 *
	 * @param $planIds
	 *
	 * @return string
	 */
	public static function getRestrictionRedirectUrl($planIds)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		// Get category of the first plan
		$query->select('category_id')
			->from('#__osmembership_plans')
			->where('id = ' . (int) $planIds[0]);
		$db->setQuery($query);
		$categoryId = (int) $db->loadResult();

		$needles = [];

		if (count($planIds) == 1)
		{
			$planId = $planIds[0];

			$Itemid = OSMembershipHelperRoute::getPlanMenuId($planId, $categoryId, OSMembershipHelper::getItemid());

			return Route::_(
				'index.php?option=com_osmembership&view=plan' . ($categoryId > 0 ? '&catid=' . $categoryId : '') . '&id=' . $planId . '&Itemid=' . $Itemid
			);
		}
		elseif ($categoryId > 0)
		{
			// If the category contains all the plans here, we will find menu item linked to that category
			$query->clear()
				->select('id')
				->from('#__osmembership_plans')
				->where('category_id = ' . $categoryId)
				->where('published = 1');
			$db->setQuery($query);
			$categoryPlanIds = $db->loadColumn();

			if (count(array_diff($planIds, $categoryPlanIds)) == 0)
			{
				$needles['plans']      = [$categoryId];
				$needles['categories'] = [$categoryId];
			}
		}

		if (count($needles))
		{
			require_once JPATH_ROOT . '/components/com_osmembership/helper/route.php';

			$menuItemId = OSMembershipHelperRoute::findItem($needles);

			if ($menuItemId)
			{
				return Route::_('index.php?Itemid=' . $menuItemId);
			}
		}

		return '';
	}

	/**
	 * Get redirect URL for plugin
	 *
	 * @param   Registry  $params
	 * @param   array     $planIds
	 *
	 * @return string
	 */
	public static function getPluginRestrictionRedirectUrl($params, $planIds)
	{
		// Try to find the best redirect URL
		$redirectUrl = OSMembershipHelper::callOverridableHelperMethod('Helper', 'getRestrictionRedirectUrl', [$planIds]
		);

		if (empty($redirectUrl))
		{
			$redirectUrl = $params->get(
				'redirect_url',
				OSMembershipHelper::getViewUrl(['categories', 'plans', 'plan', 'register'])
			);
		}

		if (!$redirectUrl)
		{
			$redirectUrl = Uri::root();
		}

		$redirectUri = Uri::getInstance($redirectUrl);
		$redirectUri->setVar('filter_plan_ids', implode(',', $planIds));

		return $redirectUri->toString();
	}

	/**
	 * Get required Plan Ids
	 *
	 * @param   array  $requiredPlanIds
	 *
	 * @return string
	 */
	public static function getContentRestrictedMessages($requiredPlanIds)
	{
		$message     = OSMembershipHelper::getMessages();
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		if (strlen($message->{'content_restricted_message' . $fieldSuffix}))
		{
			$msg = $message->{'content_restricted_message' . $fieldSuffix};
		}
		else
		{
			$msg = $message->content_restricted_message;
		}

		$msg = str_replace('[PLAN_TITLES]', static::getPlanTitles($requiredPlanIds), $msg);

		return $msg;
	}

	/**
	 * Get required plan titles
	 *
	 * @param   array  $planIds
	 *
	 * @return string
	 */
	public static function getPlanTitles($planIds)
	{
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select($db->quoteName('title' . $fieldSuffix, 'title'))
			->from('#__osmembership_plans')
			->whereIn('id', $planIds)
			->where('published = 1')
			->order('ordering');
		$db->setQuery($query);

		return implode(' ' . Text::_('OSM_OR') . ' ', $db->loadColumn());
	}

	/**
	 * Generate User Input Select
	 *
	 * @param   int  $userId
	 * @param   int  $subscriberId
	 *
	 * @return string
	 */
	public static function getUserInput($userId, $subscriberId, $fieldName = 'user_id')
	{
		$field = FormHelper::loadFieldType('User');

		$element = new SimpleXMLElement('<field />');
		$element->addAttribute('name', $fieldName);
		$element->addAttribute('class', 'readonly');

		if (!$subscriberId)
		{
			$element->addAttribute('onchange', 'populateSubscriberData();');
		}

		$field->setup($element, $userId);

		$input = $field->input;

		if (Factory::getApplication()->isClient('site'))
		{
			$script   = [];
			$script[] = '	function jSelectUser_user_id(id, title) {';
			$script[] = '			document.getElementById("user_id").value = title; ';
			$script[] = '			document.getElementById("user_id_id").value = id; ';

			if (!$subscriberId)
			{
				$script[] = 'populateSubscriberData()';
			}

			$script[] = '		Joomla.Modal.getCurrent().close();';
			$script[] = '	}';

			Factory::getApplication()
				->getDocument()
				->getWebAssetManager()
				->addInlineScript(implode("\n", $script));

			$input = str_replace('com_users', 'com_osmembership', $input);
		}

		return $input;
	}

	/**
	 * Check if the given message entered via HTML editor has actual data
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	public static function isValidMessage($string)
	{
		if (!is_string($string) || strlen($string) === 0)
		{
			return false;
		}

		$string = strip_tags($string, '<img>');

		// Remove none printable characters
		$string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $string);

		// Remove &nbsp; characters, usually added by editor by mistake
		$string = str_replace('&nbsp;', '', $string);

		$string = trim($string);

		if (strlen($string) > 10)
		{
			return true;
		}

		return false;
	}

	/**
	 * Pass field-payment method data to JS
	 *
	 * @param   array  $rowFields
	 *
	 * @return void
	 */
	public static function passFieldPaymentMethodDataToJS($rowFields)
	{
		$document = Factory::getApplication()->getDocument();

		$document->getWebAssetManager()
			->useScript('core');

		$allFields           = [];
		$paymentMethodFields = [];

		foreach ($rowFields as $rowField)
		{
			if (!$rowField->payment_method)
			{
				continue;
			}

			$allFields[]                                      = $rowField->name;
			$paymentMethodFields[$rowField->payment_method][] = $rowField->name;
		}

		$document->addScriptOptions('mp_all_payment_method_fields', $allFields);

		foreach ($paymentMethodFields as $paymentMethod => $fields)
		{
			$document->addScriptOptions('mp_' . $paymentMethod . '_fields', $fields);
		}
	}

	/**
	 * Get documents path
	 *
	 * @return string
	 */
	public static function getDocumentsPath()
	{
		$documentsPath = JPATH_ROOT . '/media/com_osmembership/documents';

		$plugin = PluginHelper::getPlugin('osmembership', 'documents');

		if (is_string($plugin->params))
		{
			$params = new Registry($plugin->params);
		}
		elseif ($plugin->params instanceof Registry)
		{
			$params = $plugin->params;
		}
		else
		{
			$params = new Registry();
		}

		$path = $params->get('documents_path', 'media/com_osmembership/documents');

		if (is_dir(Path::clean(JPATH_ROOT . '/' . $path)))
		{
			$documentsPath = JPATH_ROOT . '/' . $path;
		}
		elseif (is_dir(Path::clean($path)))
		{
			$documentsPath = $path;
		}

		return $documentsPath;
	}

	/**
	 * Get all dependencies custom fields of a given field
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public static function getAllDependencyFields($id)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		$queue  = [$id];
		$fields = [$id];

		while (count($queue))
		{
			$masterFieldId = array_pop($queue);

			//Get list of dependency fields of this master field
			$query->clear()
				->select('id')
				->from('#__osmembership_fields')
				->where('depend_on_field_id = ' . $masterFieldId);
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if (count($rows))
			{
				foreach ($rows as $row)
				{
					$queue[]  = $row->id;
					$fields[] = $row->id;
				}
			}
		}

		return $fields;
	}

	/**
	 * Get current version of Membership Pro installed on the site
	 *
	 * @return string
	 */
	public static function getInstalledVersion()
	{
		return '4.2.2';
	}

	/**
	 * Execute queries from the given file
	 *
	 * @param   string  $file
	 */
	public static function executeSqlFile($file)
	{
		/* @var DatabaseDriver $db */
		$db      = Factory::getContainer()->get('db');
		$sql     = file_get_contents($file);
		$queries = $db->splitSql($sql);

		foreach ($queries as $query)
		{
			$query = trim($query);

			if ($query != '' && $query[0] != '#')
			{
				$db->setQuery($query)
					->execute();
			}
		}
	}

	/**
	 * Method to encrypt a string
	 *
	 * @param   string  $string
	 *
	 * @return string
	 */
	public static function encrypt($string)
	{
		return OSMembershipHelperCryptor::encrypt($string);
	}

	/**
	 * Method to decrypt a string
	 *
	 * @param $string
	 *
	 * @return string
	 */
	public static function decrypt($string)
	{
		return OSMembershipHelperCryptor::decrypt($string);
	}

	/**
	 * Get hased field name to store the time which form started to be rendered
	 *
	 * @return string
	 */
	public static function getHashedFieldName()
	{
		$app = Factory::getApplication();

		$siteName = $app->get('sitename');
		$secret   = $app->get('secret');

		return md5('OSM' . $siteName . $secret);
	}

	/**
	 * Get duration
	 *
	 * @param   string  $duration
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public static function getDateDuration($duration)
	{
		$timezone = Factory::getApplication()->get('offset');

		switch ($duration)
		{
			case 'today':
				$date = Factory::getDate('now', $timezone);
				$date->setTime(0, 0, 0);
				$fromDate = $date->toSql(false);
				$date     = Factory::getDate('now', $timezone);
				$date->setTime(23, 59, 59);
				$toDate = $date->toSql(false);
				break;
			case 'yesterday':
				$date = Factory::getDate('now', $timezone);
				$date->modify('-1 day');
				$date->setTime(0, 0, 0);
				$fromDate = $date->toSql(false);
				$date     = Factory::getDate('now', $timezone);
				$date->setTime(23, 59, 59);
				$date->modify('-1 day');
				$toDate = $date->toSql(false);
				break;
			case 'this_week':
				$date   = Factory::getDate('now', $timezone);
				$monday = $date->modify('Monday this week');
				$monday->setTime(0, 0, 0);
				$fromDate = $monday->toSql(false);
				$date     = Factory::getDate('now', $timezone);
				$sunday   = $date->modify('Sunday this week');
				$sunday->setTime(23, 59, 59);
				$toDate = $sunday->toSql(false);
				break;
			case 'last_week':
				$date   = Factory::getDate('now', $timezone);
				$monday = $date->modify('Monday last week');
				$monday->setTime(0, 0, 0);
				$fromDate = $monday->toSql(false);
				$date     = Factory::getDate('now', $timezone);
				$sunday   = $date->modify('Sunday last week');
				$sunday->setTime(23, 59, 59);
				$toDate = $sunday->toSql(false);
				break;
			case 'this_month':
				$date = Factory::getDate('now', $timezone);
				$date->setDate($date->year, $date->month, 1);
				$date->setTime(0, 0, 0);
				$fromDate = $date->toSql(false);
				$date     = Factory::getDate('now', $timezone);
				$date->setDate($date->year, $date->month, $date->daysinmonth);
				$date->setTime(23, 59, 59);
				$toDate = $date->toSql(false);
				break;
			case 'last_month':
				$date = Factory::getDate('first day of last month', $timezone);
				$date->setTime(0, 0, 0);
				$fromDate = $date->toSql(false);
				$date     = Factory::getDate('last day of last month', $timezone);
				$date->setTime(23, 59, 59);
				$toDate = $date->toSql(false);
				break;
			case 'this_year':
				// This year
				$date = Factory::getDate('now', $timezone);
				$date->setDate($date->year, 1, 1);
				$date->setTime(0, 0, 0);
				$fromDate = $date->toSql(false);
				$date     = Factory::getDate('now', $timezone);
				$date->setDate($date->year, 12, 31);
				$date->setTime(23, 59, 59);
				$toDate = $date->toSql(false);
				break;
			case 'last_year':
				$date = Factory::getDate('now', $timezone);
				$date->setDate($date->year - 1, 1, 1);
				$date->setTime(0, 0, 0);
				$date->setTimezone(new DateTimeZone('UCT'));
				$fromDate = $date->toSql(true);
				$date     = Factory::getDate('now', $timezone);
				$date->setDate($date->year - 1, 12, 31);
				$date->setTime(23, 59, 59);
				$date->setTimezone(new DateTimeZone('UCT'));
				$toDate = $date->toSql(true);
				break;
			case 'last_7_days':
				$date = Factory::getDate('now', $timezone);
				$date->modify('-7 days');
				$date->setTime(0, 0, 0);
				$fromDate = $date->toSql(false);
				$date     = Factory::getDate('now', $timezone);
				$date->setTime(23, 59, 59);
				$toDate = $date->toSql(false);
				break;
			case 'last_30_days':
				$date = Factory::getDate('now', $timezone);
				$date->setTime(0, 0, 0);
				$fromDate = $date->toSql(false);
				$date     = Factory::getDate('now', $timezone);
				$date->modify('-30 days');
				$date->setTime(23, 59, 59);
				$toDate = $date->toSql(false);
				break;
			default:
				$fromDate = '';
				$toDate   = '';
				break;
		}

		return [$fromDate, $toDate];
	}

	/**
	 * Helper method to write data to a log file, for debugging purpose
	 *
	 * @param   string  $logFile
	 * @param   array   $data
	 * @param   string  $message
	 */
	public static function logData($logFile, $data = [], $message = null)
	{
		$text = '[' . gmdate('m/d/Y g:i A') . '] - ';

		foreach ($data as $key => $value)
		{
			$text .= "$key=$value, ";
		}

		$text .= $message;

		$fp = fopen($logFile, 'a');
		fwrite($fp, $text . "\n\n");
		fclose($fp);
	}


	/**
	 * Download invoice of a subscription record
	 *
	 * @param   int  $id
	 */
	public static function downloadInvoice($id)
	{
		OSMembershipHelperLegacy::downloadInvoice($id);
	}

	/**
	 * Process download a file
	 *
	 * @param   string  $filePath        Full path to the file which will be downloaded
	 * @param   string  $filename        Name of the file
	 * @param   bool    $detectFilename  Whether detect filename automatically or not
	 */
	public static function processDownload($filePath, $filename, $detectFilename = false)
	{
		OSMembershipHelperLegacy::processDownload($filePath, $filename, $detectFilename);
	}

	/**
	 * Display warning if the site is using PHP older than required version
	 *
	 * @return void
	 * @throws Exception
	 */
	public static function displayPHPVersionWarning()
	{
		$config        = self::getConfig();
		$minPHPVersion = '7.4.0';

		if (version_compare(PHP_VERSION, $minPHPVersion, '<')
			&& $config->get('display_php_version_warning', 1)
		)
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('OSM_RAISE_PHP_VERSION_WARNING', PHP_VERSION, $minPHPVersion),
				'warning'
			);
		}
	}

	/**
	 * Get allowed file types
	 *
	 * @param ?string  $allowedExtensions
	 *
	 * @return array
	 */
	public static function getAllowedFileTypes($allowedExtensions = null)
	{
		if (!$allowedExtensions)
		{
			$config            = OSMembershipHelper::getConfig();
			$allowedExtensions = $config->allowed_file_types;
		}

		if (!$allowedExtensions)
		{
			$allowedExtensions = 'doc|docx|ppt|pptx|pdf|zip|rar|bmp|gif|jpg|jepg|png|swf|zipx';
		}

		if (str_contains($allowedExtensions, ','))
		{
			$allowedExtensions = explode(',', $allowedExtensions);
		}
		else
		{
			$allowedExtensions = explode('|', $allowedExtensions);
		}

		$allowedExtensions = array_map('trim', $allowedExtensions);

		return array_map('strtolower', $allowedExtensions);
	}

	/**
	 * Make sure the common tag which user entered is invalid format
	 *
	 * @param   string  $tagName
	 *
	 * @return string
	 */
	public static function normalizeCommonTagName(string $tagName)
	{
		if (str_starts_with($tagName, '['))
		{
			$tagName = substr($tagName, 1);
		}

		if (substr($tagName, -1) == ']')
		{
			$tagName = substr($tagName, 0, -1);
		}

		return $tagName;
	}

	/**
	 * Method to check if the given filename is image
	 *
	 * @param   string  $filename
	 *
	 * @return bool
	 */
	public static function isImageFilename(string $filename): bool
	{
		$fileExt = OSMembershipHelper::getFileExt($filename);

		return in_array($fileExt, ['png', 'jpg', 'jpeg', 'gif', 'webp']);
	}

	/**
	 * Replaces tags from subject with it's value
	 *
	 * @param   string  $subject
	 * @param   array   $replaces
	 *
	 * @return string
	 */
	public static function replaceCaseInsensitiveTags($subject, $replaces)
	{
		if ($subject === null || trim($subject) === '')
		{
			return $subject;
		}

		foreach ($replaces as $key => $value)
		{
			$value = self::ensureValueIsString($value);

			$subject = str_ireplace("[$key]", $value, $subject);
		}

		return $subject;
	}

	/**
	 * Replaces tags from subject with it's value
	 *
	 * @param   string  $subject
	 * @param   array   $replaces
	 *
	 * @return string
	 */
	public static function replaceUpperCaseTags($subject, $replaces)
	{
		if ($subject === null || trim($subject) === '')
		{
			return $subject;
		}

		foreach ($replaces as $key => $value)
		{
			$key   = strtoupper($key);
			$value = self::ensureValueIsString($value);

			$subject = str_replace("[$key]", $value, $subject);
		}

		return $subject;
	}

	/**
	 * Method to make sure $value is a string
	 *
	 * @param   mixed  $value
	 *
	 * @return string
	 */
	public static function ensureValueIsString($value)
	{
		if (!is_scalar($value))
		{
			return '';
		}

		return $value ?? '';
	}
}
