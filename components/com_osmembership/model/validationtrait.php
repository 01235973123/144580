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
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

trait OSMembershipModelValidationtrait
{
	/**
	 * Validate username
	 *
	 * @param   string  $username
	 * @param   int     $userId
	 *
	 * @return array
	 */
	protected function validateUsername($username, $userId = 0)
	{
		/* @var JDatabaseDriver $db */
		$db          = $this->getDbo();
		$query       = $db->getQuery(true);
		$filterInput = InputFilter::getInstance();
		$errors      = [];

		if ($filterInput->clean($username, 'TRIM') == '')
		{
			$errors[] = Text::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_A_USER_NAME');
		}

		if (
			preg_match('#[<>"\'%;()&\\\\]|\\.\\./#', $username)
			|| StringHelper::strlen($username) < 2
			|| StringHelper::strlen($username) > 150
			|| $filterInput->clean($username, 'TRIM') !== $username
		)
		{
			$errors[] = Text::sprintf('JLIB_DATABASE_ERROR_VALID_AZ09', 2);
		}

		$query->select('COUNT(*)')
			->from('#__users')
			->where('username = ' . $db->quote($username));

		if ($userId > 0)
		{
			$query->where('id != ' . $userId);
		}

		$db->setQuery($query);
		$total = $db->loadResult();

		if ($total)
		{
			$errors[] = Text::_('OSM_INVALID_USERNAME');
		}

		return $errors;
	}

	/**
	 * Validate password
	 *
	 * @param $password
	 *
	 * @return array
	 */
	protected function validatePassword($password)
	{
		$errors = [];

		$params           = ComponentHelper::getParams('com_users');
		$minimumIntegers  = $params->get('minimum_integers');
		$minimumSymbols   = $params->get('minimum_symbols');
		$minimumUppercase = $params->get('minimum_uppercase');
		$minimumLowercase = $params->get('minimum_lowercase');
		$minimumLength    = $params->get('minimum_length');


		// We don't allow white space inside passwords
		$valueTrim   = trim($password);
		$valueLength = strlen($password);

		if (strlen($valueTrim) !== $valueLength)
		{
			$errors[] = Text::_('JFIELD_PASSWORD_SPACES_IN_PASSWORD');
		}

		if (!empty($minimumIntegers))
		{
			$nInts = preg_match_all('/[0-9]/', $password, $imatch);

			if ($nInts < $minimumIntegers)
			{
				$errors[] = Text::plural('JFIELD_PASSWORD_NOT_ENOUGH_INTEGERS_N', $minimumIntegers);
			}
		}

		if (!empty($minimumSymbols))
		{
			$nsymbols = preg_match_all('[\W]', $password, $smatch);

			if ($nsymbols < $minimumSymbols)
			{
				$errors[] = Text::plural('JFIELD_PASSWORD_NOT_ENOUGH_SYMBOLS_N', $minimumSymbols);
			}
		}

		if (!empty($minimumUppercase))
		{
			$nUppercase = preg_match_all('/[A-Z]/', $password, $umatch);

			if ($nUppercase < $minimumUppercase)
			{
				$errors[] = Text::plural('JFIELD_PASSWORD_NOT_ENOUGH_UPPERCASE_LETTERS_N', $minimumUppercase);
			}
		}

		if (!empty($minimumLowercase))
		{
			$nLowercase = preg_match_all('/[a-z]/', $password, $lmatch);

			if ($nLowercase < $minimumLowercase)
			{
				$errors[] = Text::plural('JFIELD_PASSWORD_NOT_ENOUGH_LOWERCASE_LETTERS_N', $minimumLowercase);
			}
		}

		if (!empty($minimumLength) && strlen((string) $password) < $minimumLength)
		{
			$errors[] = Text::plural('JFIELD_PASSWORD_TOO_SHORT_N', $minimumLength);
		}

		return $errors;
	}

	/**
	 * Validate email for user account
	 *
	 * @param   string  $email
	 * @param   bool    $checkExists
	 * @param   int     $userId
	 *
	 * @return array
	 */
	protected function validateEmail($email, $checkExists = true, $userId = 0)
	{
		$filterInput = InputFilter::getInstance();
		$errors      = [];

		// Validate email
		if (empty($email))
		{
			$errors[] = Text::sprintf('OSM_FIELD_NAME_IS_REQUIRED', Text::_('Email'));
		}

		if (($filterInput->clean($email, 'TRIM') == '') || !MailHelper::isEmailAddress($email))
		{
			$errors[] = Text::_('JLIB_DATABASE_ERROR_VALID_MAIL');
		}

		$domains = ComponentHelper::getParams('com_users')->get('domains');

		if ($domains)
		{
			$emailDomain = explode('@', $email);
			$emailDomain = $emailDomain[1];
			$emailParts  = array_reverse(explode('.', $emailDomain));
			$emailCount  = count($emailParts);
			$allowed     = true;

			foreach ($domains as $domain)
			{
				$domainParts = array_reverse(explode('.', $domain->name));
				$status      = 0;

				// Don't run if the email has less segments than the rule.
				if ($emailCount < count($domainParts))
				{
					continue;
				}

				foreach ($emailParts as $key => $emailPart)
				{
					if (!isset($domainParts[$key]) || $domainParts[$key] == $emailPart || $domainParts[$key] == '*')
					{
						$status++;
					}
				}

				// All segments match, check whether to allow the domain or not.
				if ($status === $emailCount)
				{
					if ($domain->rule == 0)
					{
						$allowed = false;
					}
					else
					{
						$allowed = true;
					}
				}
			}

			// If domain is not allowed, fail validation. Otherwise continue.
			if (!$allowed)
			{
				$errors[] = Text::sprintf('JGLOBAL_EMAIL_DOMAIN_NOT_ALLOWED', $emailDomain);
			}
		}

		if ($checkExists)
		{
			/* @var JDatabaseDriver $db */
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(*)')
				->from('#__users')
				->where('email = ' . $db->quote($email));

			if ($userId > 0)
			{
				$query->where('id != ' . $userId);
			}

			$db->setQuery($query);

			$total = $db->loadResult();

			if ($total)
			{
				$errors[] = Text::_('OSM_INVALID_EMAIL');
			}
		}

		return $errors;
	}

	/**
	 * Validate user uploading avatar
	 *
	 * @param   array  $avatar
	 *
	 * @return array
	 */
	protected function validateAvatar($avatar)
	{
		$config         = OSMembershipHelper::getConfig();
		$fileExt        = StringHelper::strtoupper(OSMembershipHelper::getFileExt($avatar['name']));
		$supportedTypes = ['JPG', 'PNG', 'GIF', 'JPEG'];
		$errors         = [];

		if (!in_array($fileExt, $supportedTypes))
		{
			$errors[] = Text::_('OSM_INVALID_AVATAR');
		}

		$imageSizeData = getimagesize($avatar['tmp_name']);

		if ($imageSizeData === false)
		{
			$errors[] = Text::_('OSM_INVALID_AVATAR');
		}
		else
		{
			if ($config->avatar_max_file_size > 0)
			{
				$maxFileSizeInByte = $config->avatar_max_file_size * 1024 * 1024;

				if ($avatar['size'] > $maxFileSizeInByte)
				{
					$errors[] = Text::sprintf('OSM_AVATAR_FILE_SIZE_TOO_LARGE', $config->avatar_max_file_size);
				}
			}

			if ($config->avatar_max_width > 0 || $config->avatar_max_height > 0)
			{
				[$width, $height, $type, $attr] = $imageSizeData;

				if ($width > $config->avatar_max_width)
				{
					$errors[] = Text::sprintf('OSM_AVATAR_WIDTH_TOO_LARGE', $config->avatar_max_width);
				}

				if ($height > $config->avatar_max_height)
				{
					$errors[] = Text::sprintf('OSM_AVATAR_HEIGHT_TOO_LARGE', $config->avatar_max_height);
				}
			}
		}

		return $errors;
	}

	/**
	 * Validate the date which user select for their subscription
	 *
	 * @param   OSMembershipTablePlan  $plan
	 * @param   array                  $data
	 *
	 * @return []
	 */
	protected function validateUserSelectedSubscriptionStartDate($plan, $data)
	{
		$errors                      = [];
		$params                      = new Registry($plan->params);
		$subscriptionStartDateOption = $params->get('subscription_start_date_option', '0');
		$subscriptionStartDateField  = $params->get('subscription_start_date_field');

		if ($subscriptionStartDateOption != 2 || !$subscriptionStartDateField
			|| empty(empty($data[$subscriptionStartDateField])))
		{
			return $errors;
		}

		// Validate and make sure the date which user selected ia
		$config     = OSMembershipHelper::getConfig();
		$dateFormat = $config->date_field_format ?: '%Y-%m-%d';
		$dateFormat = str_replace('%', '', $dateFormat);

		$selectedDate = DateTime::createFromFormat($dateFormat, $data[$subscriptionStartDateField]);

		if (!$selectedDate)
		{
			$errors[] = Text::_('OSM_INVALID_CUSTOM_SUBSCRIPTION_START_DATE');

			return $errors;
		}

		try
		{
			$date = Factory::getDate($selectedDate->format('Y-m-d'), Factory::getApplication()->get('offset'));
		}
		catch (Exception $e)
		{
			$errors[] = Text::_('OSM_INVALID_CUSTOM_SUBSCRIPTION_START_DATE');
		}

		return $errors;
	}
}
