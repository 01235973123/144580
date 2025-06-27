<?php
/**
 * @version            5.6.0
 * @package            Joomla
 * @subpackage         Joom Donation
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2009 - 2023 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

abstract class DonationHelperJquery
{
	/**
	 * validate form
	 */
	public static function validateForm()
	{
		static $loaded = false;
		if (!$loaded)
		{
			$document = Factory::getApplication()->getDocument();
			DonationHelper::addStyleSheet( Uri::root() . 'media/com_jdonation/assets/js/validate/css/validationEngine.jquery.css');
			DonationHelper::addScriptDeclaration(
				'
				JD.jQuery(function($) {
				    $.fn.validationEngineLanguage = function(){
				    };
				    $.validationEngineLanguage = {
				        newLang: function(){
				            $.validationEngineLanguage.allRules = {
				                "required": { // Add your regex rules here, you can take telephone as an example
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_FIELD_REQUIRED') . '",
				                    "alertTextCheckboxMultiple": "' .
				Text::_('JD_VALIDATION__SELECT_AN_OPTION') . '",
				                    "alertTextCheckboxe": "' . Text::_('JD_VALIDATION__CHECKBOX_REQUIRED') . '",
				                    "alertTextDateRange": "' . Text::_('JD_VALIDATION_BOTH_DATE_RANGE_FIELD_REQUIRED') . '"
				                },
				                "requiredInFunction": {
				                    "func": function(field, rules, i, options){
				                        return (field.val() == "test") ? true : false;
				                    },
				                    "alertText": "' . Text::_('JD_VALIDATION_FIELD_MUST_EQUAL_TEST') . '"
				                },
				                "dateRange": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_INVALID') . '",
				                    "alertText2": "Date Range"
				                },
				                "dateTimeRange": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_INVALID') . '",
				                    "alertText2": "' . Text::_('JD_VALIDATION_DATE_TIME_RANGE') . '"
				                },
				                "minSize": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_MINIMUM') . '",
				                    "alertText2": " characters required"
				                },
				                "maxSize": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_MAXIMUM') . '",
				                    "alertText2": "' . Text::_('JD_VALIDATION_CHACTERS_ALLOWED') . '"
				                },
								"groupRequired": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_GROUP_REQUIRED') . '"
				                },
				                "min": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_MIN') . '"
				                },
				                "max": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_MAX') . '"
				                },
				                "past": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_DATE_PRIOR_TO') . '"
				                },
				                "future": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_DATE_PAST') . '"
				                },
				                "maxCheckbox": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_MAXIMUM') . '",
				                    "alertText2": "' . Text::_('JD_VALIDATION_OPTION_ALLOW') . '"
				                },
				                "minCheckbox": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_PLEASE_SELECT') . '",
				                    "alertText2": " options"
				                },
				                "equals": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_FIELDS_DO_NOT_MATCH') . '"
				                },
				                "creditCard": {
				                    "regex": "none",
				                    "alertText": "' . Text::_('JD_VALIDATION_INVALID_CREDIT_CARD_NUMBER') . '"
				                },
				                "phone": {
				                    // credit: jquery.h5validate.js / orefalo
				                    "regex": /^([\+][0-9]{1,3}[\ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9\ \.\-\/]{3,20})((x|ext|extension)[\ ]?[0-9]{1,4})?$/,
				                    "alertText": "' . Text::_('JD_VALIDATION_INVALID_PHONE_NUMBER') . '"
				                },
				                "email": {
				                    // HTML5 compatible email regex ( http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
				                    "regex": /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
				                    "alertText": "' . Text::_('JD_VALIDATION_INVALID_EMAIL_ADDRESS') . '"
				                },
				                "integer": {
				                    "regex": /^[\-\+]?\d+$/,
				                    "alertText": "' . Text::_('JD_VALIDATION_NOT_A_VALID_INTEGER') . '"
				                },
				                "number": {
				                    // Number, including positive, negative, and floating decimal. credit: orefalo
				                    "regex": /^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/,
				                    "alertText": "' . Text::_('JD_VALIDATION_INVALID_FLOATING_DECIMAL_NUMBER') . '"
				                },
				                "date": {
				                    //	Check if date is valid by leap year
								"func": function (field) {
									var pattern = new RegExp(/^(\d{4})[\/\-\.](0?[1-9]|1[012])[\/\-\.](0?[1-9]|[12][0-9]|3[01])$/);
									var match = pattern.exec(field.val());
									if (match == null)
									   return false;
		
									var year = match[1];
									var month = match[2]*1;
									var day = match[3]*1;
									var date = new Date(year, month - 1, day); // because months starts from 0.
		
									return (date.getFullYear() == year && date.getMonth() == (month - 1) && date.getDate() == day);
								},
							 	"alertText": "' . Text::_('JD_VALIDATION_INVALID_DATE') . '"
				                },
				                "ipv4": {
				                    "regex": /^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/,
				                    "alertText": "' . Text::_('JD_VALIDATION_INVALID_IP_ADDRESS') . '"
				                },
				                "url": {
				                    "regex": /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/,
				                    "alertText": "' . Text::_('JD_VALIDATION_INVALID_URL') . '"
				                },
				                "onlyNumberSp": {
				                    "regex": /^[0-9\ ]+$/,
				                    "alertText": "' . Text::_('JD_VALIDATION_NUMBER_ONLY') . '"
				                },
				                "onlyLetterSp": {
				                    "regex": /^[a-zA-Z\ \']+$/,
				                    "alertText": "' . Text::_('JD_VALIDATION_LETTERS_ONLY') . '"
				                },
				                "onlyLetterNumber": {
				                    "regex": /^[0-9a-zA-Z]+$/,
				                    "alertText": "' . Text::_('JD_VALIDATION_NO_SPECIAL_CHACTERS_ALLOWED') . '"
				                },
				                // --- CUSTOM RULES -- Those are specific to the demos, they can be removed or changed to your likings
				                "ajaxUserCall": {
				                	"url": "' . DonationHelper::getSiteUrl() . 'index.php?option=com_jdonation&task=validate_username",
				                    // you may want to pass extra data on the ajax call
				                    "extraData": "name=eric",
				                    "alertText": "' . Text::_('JD_VALIDATION_INVALID_USERNAME') . '",
				                },
				                "ajaxEmailCall": {
				                	"url": "' . DonationHelper::getSiteUrl() . 'index.php?option=com_jdonation&task=validate_email",
				                    // you may want to pass extra data on the ajax call			                    
				                    "alertText": "' . Text::_('JD_VALIDATION_INVALID_EMAIL') . '",
				                },
					            //tls warning:homegrown not fielded
				                "dateFormat":{
				                    "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/,
				                    "alertText": "' . Text::_('JD_VALIDATION_INVALID_DATE') . '"
				                },
				                //tls warning:homegrown not fielded
								"dateTimeFormat": {
					                "regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/,
				                    "alertText": "* Invalid Date or Date Format",
				                    "alertText2": "' . Text::_('JD_VALIDATION_EXPECTED_FORMAT') . '",
				                    "alertText3": "mm/dd/yyyy hh:mm:ss AM|PM or ",
				                    "alertText4": "yyyy-mm-dd hh:mm:ss AM|PM"
					            }
				            };
				        }
				    };
				    $.validationEngineLanguage.newLang();
				});
			');
			DonationHelper::addScript( Uri::root() . 'media/com_jdonation/assets/js/noconflict.js');
			DonationHelper::addScript( Uri::root() . 'media/com_jdonation/assets/js/validate/js/jquery.validationEngine.js');
		}
		$loaded = true;
	}

	/**
	 * Method to load the colorbox into the document head
	 *
	 * If debugging mode is on an uncompressed version of colorbox is included for easier debugging.
	 *
	 * @param string $class
	 * @param string $width
	 * @param string $height
	 * @param string $iframe
	 * @param string $inline
	 *
	 * @return  void
	 */
	public static function colorbox($class = 'sr-iframe', $width = '80%', $height = '80%', $iframe = "true", $inline = "false", $scrolling = "true")
	{
		static $loaded = false;
		$siteUrl = DonationHelper::getSiteUrl();
		if (!$loaded)
		{
			HTMLHelper::_('stylesheet', $siteUrl . 'media/com_jdonation/assets/js/colorbox/colorbox.css', false, false);
			HTMLHelper::_('script', $siteUrl . 'media/com_jdonation/assets/js/colorbox/jquery.colorbox.min.js', false, false);

			$activeLanguageTag   = Factory::getApplication()->getLanguage()->getTag();
			$allowedLanguageTags = array('ar-AA', 'bg-BG', 'ca-ES', 'cs-CZ', 'da-DK', 'de-DE', 'el-GR', 'es-ES', 'et-EE',
				'fa-IR', 'fi-FI', 'fr-FR', 'he-IL', 'hr-HR', 'hu-HU', 'it-IT', 'ja-JP', 'ko-KR', 'lv-LV', 'nb-NO', 'nl-NL',
				'pl-PL', 'pt-BR', 'ro-RO', 'ru-RU', 'sk-SK', 'sr-RS', 'sv-SE', 'tr-TR', 'uk-UA', 'zh-CN', 'zh-TW',
			);

			// English is bundled into the source therefore we don't have to load it.
			if (in_array($activeLanguageTag, $allowedLanguageTags))
			{
				HTMLHelper::_('script', $siteUrl . 'media/com_jdonation/assets/js/colorbox/i18n/jquery.colorbox-' . $activeLanguageTag . '.js', false, false);
			}

			$loaded = true;
		}

		if ($class == 'a.edocman-modal')
		{
			$options = array(
				'maxWidth'  => '80%',
				'maxHeight' => '80%',
			);
			$script  = 'JD.jQuery(document).ready(function(jQuery){jQuery("' . $class . '").colorbox(' . self::getJSObject($options) . ');});';
		}
		else
		{
			$options = array(
				'iframe'     => $iframe,
				'fastIframe' => false,
				'inline'     => $inline,
				'width'      => $width,
				'height'     => $height,
				'scrolling'  => $scrolling,
			);
			$script  = 'JD.jQuery(document).ready(function(jQuery){jQuery(".' . $class . '").colorbox(' . self::getJSObject($options) . ');});';
		}

		DonationHelper::addScriptDeclaration($script);
	}

	/**
	 * Convert an array to js object
	 *
	 * @param array $array
	 *
	 * @return string
	 */
	public static function getJSObject(array $array = array())
	{
		$object = '{';

		// Iterate over array to build objects
		foreach ((array) $array as $k => $v)
		{
			if (is_null($v))
			{
				continue;
			}

			if ($v === 'true')
			{
				$v = true;
			}

			if ($v === 'false')
			{
				$v = false;
			}

			if (is_bool($v))
			{
				$object .= ' ' . $k . ': ';
				$object .= ($v) ? 'true' : 'false';
				$object .= ',';
			}
			elseif (!is_array($v) && !is_object($v))
			{
				$object .= ' ' . $k . ': ';
				$object .= (is_numeric($v) || strpos($v, '\\') === 0) ? (is_numeric($v)) ? $v : substr($v, 1) : "'" . $v . "'";
				$object .= ',';
			}
			else
			{
				$object .= ' ' . $k . ': ' . self::getJSObject($v) . ',';
			}
		}

		if (substr($object, -1) == ',')
		{
			$object = substr($object, 0, -1);
		}

		$object .= '}';

		return $object;
	}
}
