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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class OSMembershipHelperJquery
{
	public static function loadjQuery()
	{
		static $loaded = false;

		if ($loaded === false)
		{
			$wa = Factory::getApplication()
				->getDocument()
				->getWebAssetManager()
				->useScript('jquery')
				->useScript('jquery-noconflict');

			if (is_file(JPATH_ROOT . '/media/com_osmembership/assets/js/membershipprojq.min.js'))
			{
				$wa->registerAndUseScript(
					'com_osmembership.membershipprojq',
					'media/com_osmembership/assets/js/membershipprojq.min.js'
				);
			}

			$loaded = true;
		}
	}

	/**
	 *
	 * Load colorbox library
	 *
	 */
	public static function colorbox()
	{
		static $loaded;

		if ($loaded === true)
		{
			return;
		}

		self::loadjQuery();

		$wa = Factory::getApplication()
			->getDocument()
			->getWebAssetManager()
			->registerAndUseScript(
				'com_osmembership.jquery.colorbox',
				'media/com_osmembership/assets/js/colorbox/jquery.colorbox.min.js'
			)
			->registerAndUseStyle(
				'com_osmembership.jquery.colorbox',
				'media/com_osmembership/assets/js/colorbox/colorbox.min.css'
			);

		$activeLanguageTag   = Factory::getApplication()->getLanguage()->getTag();
		$allowedLanguageTags = [
			'ar-AA',
			'bg-BG',
			'ca-ES',
			'cs-CZ',
			'da-DK',
			'de-DE',
			'el-GR',
			'es-ES',
			'et-EE',
			'fa-IR',
			'fi-FI',
			'fr-FR',
			'he-IL',
			'hr-HR',
			'hu-HU',
			'it-IT',
			'ja-JP',
			'ko-KR',
			'lv-LV',
			'nb-NO',
			'nl-NL',
			'pl-PL',
			'pt-BR',
			'ro-RO',
			'ru-RU',
			'sk-SK',
			'sr-RS',
			'sv-SE',
			'tr-TR',
			'uk-UA',
			'zh-CN',
			'zh-TW',
		];

		/// English is bundled into the source therefore we don't have to load it.
		if (in_array($activeLanguageTag, $allowedLanguageTags))
		{
			$wa->registerAndUseScript(
				'com_osmembership.jquery.colorbox.activeLanguage',
				'media/com_osmembership/assets/js/colorbox/i18n/jquery.colorbox-' . $activeLanguageTag . '.js'
			);
		}

		$loaded = true;
	}

	/**
	 * validate form
	 */
	public static function validateForm()
	{
		self::loadjQuery();

		if (OSMembershipHelper::isMethodOverridden('OSMembershipHelperOverrideJquery', 'validateForm'))
		{
			OSMembershipHelperOverrideJquery::validateForm();

			return;
		}

		$config = OSMembershipHelper::getConfig();

		$dateFormat = $config->date_field_format ?: '%Y-%m-%d';
		$dateFormat = str_replace('%', '', $dateFormat);

		$humanFormat = str_replace('Y', 'YYYY', $dateFormat);
		$humanFormat = str_replace('m', 'MM', $humanFormat);
		$humanFormat = str_replace('d', 'DD', $humanFormat);

		$separator          = '';
		$possibleSeparators = ['.', '-', '/'];

		foreach ($possibleSeparators as $possibleSeparator)
		{
			if (str_contains($dateFormat, $possibleSeparator))
			{
				$separator = $possibleSeparator;
				break;
			}
		}

		$dateParts = explode($separator, $dateFormat);

		$yearIndex  = array_search('Y', $dateParts);
		$monthIndex = array_search('m', $dateParts);
		$dayIndex   = array_search('d', $dateParts);

		$regex = $dateFormat;
		$regex = str_replace($separator, '[\\' . $separator . ']', $regex);
		$regex = str_replace('d', '(0?[1-9]|[12][0-9]|3[01])', $regex);
		$regex = str_replace('Y', '(\d{4})', $regex);
		$regex = str_replace('m', '(0?[1-9]|1[012])', $regex);
		$regex = 'var pattern = new RegExp(/^' . $regex . '$/);';

		$wa = Factory::getApplication()->getDocument()
			->addScriptOptions('humanFormat', $humanFormat)
			->addScriptOptions('rootUri', Uri::root(true))
			->getWebAssetManager()
			->registerAndUseStyle(
				'com_osmembership.validationEngine',
				'media/com_osmembership/assets/js/validate/css/validationEngine.jquery.min.css'
			);

		$wa->addInlineScript(
			"
			var yearPartIndex = $yearIndex;
			var monthPartIndex = $monthIndex;
			var dayPartIndex = $dayIndex;
			var customDateFormat = '$dateFormat';
			$regex
		"
		);

		$languageItems = [
			'OSM_FIELD_REQUIRED',
			'OSM_PLEASE_SELECT_AN_OPTION',
			'OSM_CHECKBOX_REQUIRED',
			'OSM_BOTH_DATE_RANGE_FIELD_REQUIRED',
			'OSM_FIELD_MUST_EQUAL_TEST',
			'OSM_INVALID',
			'OSM_DATE_TIME_RANGE',
			'OSM_CHARACTERS_REQUIRED',
			'OSM_CHACTERS_ALLOWED',
			'OSM_GROUP_REQUIRED',
			'OSM_MIN',
			'OSM_MAX',
			'OSM_DATE_PRIOR_TO',
			'OSM_DATE_PAST',
			'OSM_MAXIMUM',
			'OSM_MINIMUM',
			'OSM_OPTION_ALLOW',
			'OSM_PLEASE_SELECT',
			'OSM_FIELDS_DO_NOT_MATCH',
			'OSM_INVALID_CREDIT_CARD_NUMBER',
			'OSM_INVALID_PHONE_NUMBER',
			'OSM_INVALID_EMAIL_ADDRESS',
			'OSM_NOT_A_VALID_INTEGER',
			'OSM_INVALID_FLOATING_DECIMAL_NUMBER',
			'OSM_INVALID_DATE',
			'OSM_INVALID_IP_ADDRESS',
			'OSM_INVALID_URL',
			'OSM_NUMBER_ONLY',
			'OSM_LETTERS_ONLY',
			'OSM_NO_SPECIAL_CHACTERS_ALLOWED',
			'OSM_INVALID_USERNAME',
			'OSM_INVALID_EMAIL',
			'OSM_INVALID_PASSWORD',
			'OSM_INVALID_DATE',
			'OSM_EXPECTED_FORMAT',
		];

		foreach ($languageItems as $item)
		{
			Text::script($item, true);
		}

		// Support custom validation rules
		if (is_file(JPATH_ROOT . '/media/com_osmembership/js/custom_validation_rules.js'))
		{
			$wa->registerAndUseScript(
				'com_osmembership.custom_validation_rules',
				'media/com_osmembership/js/custom_validation_rules.js'
			);
		}

		OSMembershipHelperHtml::addOverridableScript(
			'media/com_osmembership/assets/js/validate/js/jquery.validationEngine.lang.min.js'
		);

		$wa->registerAndUseScript(
			'com_osmembership.jquery.validationEngine',
			'media/com_osmembership/assets/js/validate/js/j4.jquery.validationEngine.min.js'
		);
	}

	/**
	 * Equal Heights Plugin
	 * Equalize the heights of elements. Great for columns or any elements
	 * that need to be the same size (floats, etc)
	 */
	public static function equalHeights()
	{
		self::loadjQuery();

		Factory::getApplication()
			->getDocument()
			->getWebAssetManager()
			->registerAndUseScript(
				'com_osmembership.jquery.equalHeights',
				'media/com_osmembership/assets/js/query.equalHeights.min.js'
			);
	}

	/**
	 * Use responsive equal height script to make equal height columns
	 *
	 * @param   string  $selector
	 * @param   int     $minHeight
	 */
	public static function responsiveEqualHeight($selector, $minHeight = 0)
	{
		static $scriptLoaded = false;
		static $loaded = [];

		$wa = Factory::getApplication()
			->getDocument()
			->getWebAssetManager();

		if (!$scriptLoaded)
		{
			$wa->registerAndUseScript(
				'com_osmembership.responsive-auto-height',
				'media/com_osmembership/assets/js/responsive-auto-height.min.js'
			);
		}

		if (isset($loaded[$selector]))
		{
			return true;
		}

		$wa->addInlineScript(
			'
			document.addEventListener("DOMContentLoaded", function() {
				new ResponsiveAutoHeight("' . $selector . '");	
			});
		'
		);

		if ($minHeight > 0)
		{
			$wa->addInlineStyle("$selector {min-height: $minHeight" . 'px}');
		}

		$loaded[$selector] = true;
	}
}
