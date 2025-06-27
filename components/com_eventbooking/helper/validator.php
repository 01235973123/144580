<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingHelperValidator
{
	/**
	 * Validate duplicate registration, return true if pass
	 *
	 * @param   int     $eventId
	 * @param   int     $userId
	 * @param   string  $email
	 *
	 * @return bool
	 */
	public static function validateDuplicateRegistration($eventId, $userId, $email): bool
	{
		$config = EventbookingHelper::getConfig();
		$event  = EventbookingHelperDatabase::getEvent($eventId);

		EventbookingHelper::overrideGlobalConfig($config, $event);

		if ($event->prevent_duplicate_registration === '')
		{
			$preventDuplicateRegistration = $config->prevent_duplicate_registration;
		}
		else
		{
			$preventDuplicateRegistration = $event->prevent_duplicate_registration;
		}

		if ($preventDuplicateRegistration && ($userId || $email))
		{
			$eventIsFull                      = false;
			$numberAwaitingPaymentRegistrants = EventbookingHelperRegistration::countAwaitingPaymentRegistrations(
				$event
			);

			if ($event->event_capacity && (($event->total_registrants + $numberAwaitingPaymentRegistrants) >= $event->event_capacity))
			{
				$eventIsFull = true;
			}

			/* @var DatabaseDriver $db */
			$db = Factory::getContainer()->get(DatabaseInterface::class);

			$query = $db->getQuery(true)
				->select('COUNT(id)')
				->from('#__eb_registrants')
				->where('event_id = ' . $eventId);

			if ($email)
			{
				$query->where('email = ' . $db->quote($email));
			}
			else
			{
				$query->where('user_id = ' . $userId);
			}

			// Check if user joined waiting list
			if ($eventIsFull)
			{
				$query->where('published = 3');
			}
			else
			{
				$query->where('(published = 1 OR (published = 0 AND payment_method LIKE "os_offline%"))');
			}

			$db->setQuery($query);
			$total = $db->loadResult();

			if ($total)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check to see if email is already used by other user
	 *
	 * @param   string  $email
	 *
	 * @return bool
	 */
	public static function emailAlreadyTaken($email): bool
	{
		/* @var DatabaseDriver $db */
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__users')
			->where('email = ' . $db->quote($email));
		$db->setQuery($query);

		return $db->loadResult() > 0;
	}

	/**
	 * Method to validate username
	 *
	 * @param   string  $username
	 *
	 * @return array
	 */
	public static function validateUsername($username)
	{
		$filterInput = InputFilter::getInstance();

		$errors = [];

		if (empty($username))
		{
			$errors[] = Text::sprintf('EB_FORM_FIELD_IS_REQURED', Text::_('EB_USERNAME'));
		}

		if ($filterInput->clean($username, 'TRIM') == '')
		{
			$errors[] = Text::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_A_USER_NAME');
		}

		if (preg_match('#[<>"\'%;()&\\\\]|\\.\\./#', $username) || strlen(utf8_decode($username)) < 2
			|| $filterInput->clean($username, 'TRIM') !== $username
		)
		{
			$errors[] = Text::sprintf('JLIB_DATABASE_ERROR_VALID_AZ09', 2);
		}

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__users')
			->where('username = ' . $db->quote($username));
		$db->setQuery($query);
		$total = $db->loadResult();

		if ($total)
		{
			$errors[] = Text::_('EB_VALIDATION_INVALID_USERNAME');
		}

		return $errors;
	}

	/**
	 * Method to validate password
	 *
	 * @param   string  $password
	 *
	 * @return array
	 */
	public static function validatePassword($password): array
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
	 * Validate email domain according to restriction settings in Joomla Users Management
	 *
	 * @param   string  $email
	 *
	 * @return bool
	 */
	public static function validateEmailDomain($email): bool
	{
		$allowed = true;
		$domains = ComponentHelper::getParams('com_users')->get('domains');

		if ($domains)
		{
			$emailDomain = explode('@', $email);
			$emailDomain = $emailDomain[1];
			$emailParts  = array_reverse(explode('.', $emailDomain));
			$emailCount  = count($emailParts);

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
		}

		return $allowed;
	}
}
