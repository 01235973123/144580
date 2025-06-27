<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class EShopHtmlHelper
{

	/**
	 *
	 * Function to get Zones Javascript Array
	 * @return string
	 */
	public static function getZonesArrayJs()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('country_id, CONCAT(id, ":", zone_name) AS name')
			->from('#__eshop_zones')
			->where('published = 1');
		$db->setQuery($query);
		$rows   = $db->loadObjectList();
		$states = [];

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row                        = $rows[$i];
			$states[$row->country_id][] = $row->name;
		}

		$stateString = " var stateList = new Array();\n";

		foreach ($states as $countryId => $stateArray)
		{
			$stateString .= " stateList[$countryId] = \"0:" . Text::_('ESHOP_ALL_ZONES') . "," . implode(',', $stateArray) . "\";\n";
		}

		return $stateString;
	}

	/**
	 *
	 * Function to get Countries Options Javascript
	 * @return string
	 */
	public static function getCountriesOptionsJs()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, country_name AS name')
			->from('#__eshop_countries')
			->where('published=1')
			->order('country_name');
		$db->setQuery($query);
		$rows    = $db->loadObjectList();
		$options = "\nvar countriesOptions = '';";

		foreach ($rows as $row)
		{
			$options .= "\ncountriesOptions += \"<option value='$row->id'>$row->name</option>\";";
		}

		$options .= "\n";

		return $options;
	}

	/**
	 *
	 * Function to get Zones Options Javascript
	 *
	 * @param   int  $countryId
	 *
	 * @return string
	 */
	public static function getZonesOptionsJs($countryId)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, zone_name')
			->from('#__eshop_zones')
			->where('country_id=' . intval($countryId))
			->where('published=1')
			->order('zone_name');
		$db->setQuery($query);
		$rows    = $db->loadObjectList();
		$options = "\nvar zonesOptions = '';";

		foreach ($rows as $row)
		{
			$options .= "\nzonesOptions += \"<option value='$row->id'>$row->zone_name</option>\";";
		}

		$options .= "\n";

		return $options;
	}

	/**
	 *
	 * Function to get Taxrate Options Javascript
	 * @return string
	 */
	public static function getTaxrateOptionsJs()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, tax_name')
			->from('#__eshop_taxes')
			->where('published=1');
		$db->setQuery($query);
		$rows    = $db->loadObjectList();
		$options = "\nvar taxrateOptions = '';";

		foreach ($rows as $row)
		{
			$options .= "\ntaxrateOptions += \"<option value='$row->id'>$row->tax_name</option>\";";
		}

		$options .= "\n";

		return $options;
	}

	/**
	 *
	 * Function to get Baseon Options Javascript
	 * @return string
	 */
	public static function getBaseonOptionsJs()
	{
		$options = "\nvar BaseonOptions = '';";
		$options .= "\nBaseonOptions += \"<option value='shipping'>Shipping Address</option>\";";
		$options .= "\nBaseonOptions += \"<option value='payment'>Payment Address</option>\";";
		$options .= "\nBaseonOptions += \"<option value='store'>Store Address</option>\";";
		$options .= "\n";

		return $options;
	}


	/**
	 * Function to render a common layout which is used in different views
	 *
	 * @param   string  $layout  Relative path to the layout file
	 * @param   array   $data    An array contains the data passed to layout for rendering
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public static function loadCommonLayout($layout, $data = [])
	{
		$app        = Factory::getApplication();
		$eshopTheme = EShopHelper::getConfigValue('theme');

		if (is_file($layout))
		{
			$path = $layout;
		}
		elseif (is_file(JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_eshop/' . $layout))
		{
			$path = JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_eshop/' . $layout;
		}
		elseif (is_file(JPATH_ROOT . '/components/com_eshop/themes/' . $eshopTheme . '/views/' . $layout))
		{
			$path = JPATH_ROOT . '/components/com_eshop/themes/' . $eshopTheme . '/views/' . $layout;
		}
		elseif (is_file(JPATH_ROOT . '/components/com_eshop/themes/default/views/' . $layout))
		{
			$path = JPATH_ROOT . '/components/com_eshop/themes/default/views/' . $layout;
		}
		else
		{
			throw new RuntimeException(Text::_('The given shared template path is not exist'));
		}

		// Start an output buffer.
		ob_start();
		extract($data);

		// Load the layout.
		include $path;

		// Get the layout contents.
		$output = ob_get_clean();

		return $output;
	}

	/**
	 *
	 * Public function to get the available tags for a specific message
	 *
	 * @param   string  $messageName
	 *
	 * @return array
	 */
	public static function getAvailableMessageTags($messageName)
	{
		$baseFields = [
			'[ORDER_ID]',
			'[ORDER_NUMBER]',
			'[ORDER_STATUS]',
			'[DATE_ADDED]',
			'[STORE_OWNER]',
			'[STORE_NAME]',
			'[STORE_ADDRESS]',
			'[STORE_TELEPHONE]',
			'[STORE_FAX]',
			'[STORE_EMAIL]',
			'[STORE_URL]',
			'[PAYMENT_METHOD]',
			'[SHIPPING_METHOD]',
			'[CUSTOMER_NAME]',
			'[CUSTOMER_EMAIL]',
			'[CUSTOMER_TELEPHONE]',
			'[COMMENT]',
			'[DELIVERY_DATE]',
			'[PAYMENT_ADDRESS]',
			'[SHIPPING_ADDRESS]',
			'[PRODUCTS_LIST]',
		];

		$baseFields = array_merge($baseFields, self::_getCustomFieldsTags());

		switch ($messageName)
		{
			case 'admin_notification_email_subject':
			case 'customer_guest_notification_email_subject':
				$fields = [
					'[STORE_NAME]',
					'[ORDER_ID]',
					'[ORDER_NUMBER]',
					'[CUSTOMER_NAME]',
				];
				break;
			case 'admin_notification_email':
			case 'guest_notification_email':
			case 'guest_notification_email_with_download':
			case 'offline_payment_guest_notification_email_with_download':
			case 'offline_payment_guest_notification_email':
				$fields = $baseFields;
				break;
			case 'customer_notification_email':
			case 'offline_payment_customer_notification_email':
				$extraFields = [
					'[ORDER_LINK]',
				];
				$fields      = array_merge($baseFields, $extraFields);
				break;
			case 'customer_notification_email_with_download':
			case 'offline_payment_customer_notification_email_with_download':
				$extraFields = [
					'[ORDER_LINK]',
					'[DOWNLOAD_LINK]',
				];
				$fields      = array_merge($baseFields, $extraFields);
				break;
			case 'invoice_layout':
				$extraFields = [
					'[INVOICE_NUMBER]',
				];
				$fields      = array_merge($baseFields, $extraFields);
				break;
			case 'manufacturer_notification_email':
				$extraFields = [
					'[MANUFACTURER_NAME]',
				];
				$fields      = array_merge($baseFields, $extraFields);
				break;
			case 'admin_quote_email':
				$fields = [
					'[NAME]',
					'[EMAIL]',
					'[COMPANY]',
					'[TELEPHONE]',
					'[MESSAGE]',
					'[PRODUCTS_LIST]',
				];
				break;
			case 'admin_quote_email_subject':
				$fields = [
					'[CUSTOMER_NAME]',
				];
				break;
			case 'customer_quote_email':
				$fields = [
					'[PRODUCTS_LIST]',
				];
				break;
			case 'ask_question_notification_email':
			case 'notify_email':
				$fields = [
					'[PRODUCT_NAME]',
					'[PRODUCT_LINK]',
				];
				break;
			case 'notify_email_subject':
				$fields = [
					'[PRODUCT_NAME]',
				];
				break;
			case 'order_status_change_customer':
				$extraFields = [
					'[ORDER_STATUS_FROM]',
					'[ORDER_STATUS_TO]',
					'[ORDER_LINK]',
				];
				$fields      = array_merge($baseFields, $extraFields);
				break;
			case 'order_status_change_guest':
				$extraFields = [
					'[ORDER_STATUS_FROM]',
					'[ORDER_STATUS_TO]',
				];
				$fields      = array_merge($baseFields, $extraFields);
				break;
			case 'order_status_change_subject':
				$fields = [
					'[STORE_NAME]',
					'[ORDER_ID]',
					'[ORDER_NUMBER]',
					'[ORDER_STATUS_FROM]',
					'[ORDER_STATUS_TO]',
				];
				break;
			case 'reminder_email':
				$fields = [
					'[STORE_NAME]',
					'[PRODUCTS_LIST]',
				];
				break;
			case 'shipping_notification_email':
				$fields = [
					'[CUSTOMER_NAME]',
					'[ORDER_ID]',
					'[ORDER_NUMBER]',
					'[SHIPPING_TRACKING_NUMBER]',
					'[SHIPPING_TRACKING_URL]',
					'[SHIPPING_ADDRESS]',
					'[COMMENT]',
				];
				break;
			case 'ask_question_notification_email_subject':
			case 'customer_quote_email_subject':
			case 'manufacturer_notification_email_subject':
			case 'reminder_email_subject':
			case 'shipping_notification_email_subject':
			case 'shop_introduction':
			default:
				$fields = [];
				break;
		}

		return $fields;
	}

	/**
	 *
	 * Private function to get tags for custom fields
	 * @return array
	 */
	static public function _getCustomFieldsTags()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('name, address_type')
			->from('#__eshop_fields')
			->where('published = 1')
			->where('is_core = 0')
			->order('ordering');
		$db->setQuery($query);
		$rows   = $db->loadObjectList();
		$fields = [];

		foreach ($rows as $row)
		{
			switch ($row->address_type)
			{
				case 'B':
					$fields[] = '[PAYMENT_' . strtoupper($row->name) . ']';
					break;
				case 'S':
					$fields[] = '[SHIPPING_' . strtoupper($row->name) . ']';
					break;
				case 'A':
				default:
					$fields[] = '[PAYMENT_' . strtoupper($row->name) . ']';
					$fields[] = '[SHIPPING_' . strtoupper($row->name) . ']';
					break;
			}
		}

		return $fields;
	}

	/**
	 * Get bootstrapped style boolean input
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 */
	public static function getBooleanInput($name, $value)
	{
		HTMLHelper::_('jquery.framework');
		$field = FormHelper::loadFieldType('Radio');

		$element = new SimpleXMLElement('<field />');
		$element->addAttribute('name', $name);

		if (EShopHelper::isJoomla4())
		{
			$element->addAttribute('layout', 'joomla.form.field.radio.switcher');
		}
		else
		{
			$element->addAttribute('class', 'radio btn-group btn-group-yesno');
		}

		$element->addAttribute('default', '0');

		$node = $element->addChild('option', 'JNO');
		$node->addAttribute('value', '0');

		$node = $element->addChild('option', 'JYES');
		$node->addAttribute('value', '1');

		$field->setup($element, $value);

		return $field->input;
	}

	/**
	 * Get label of the field (including tooltip)
	 *
	 * @param           $name
	 * @param           $title
	 * @param   string  $tooltip
	 *
	 * @return string
	 */
	public static function getFieldLabel($name, $title, $tooltip = '')
	{
		$label = '';
		$text  = $title;

		// Build the class for the label.
		$class = !empty($tooltip) ? 'hasTooltip hasTip' : '';

		// Add the opening label tag and main attributes attributes.
		$label .= '<label id="' . $name . '-lbl" for="' . $name . '" class="' . $class . '"';

		// If a description is specified, use it to build a tooltip.
		if (!empty($tooltip))
		{
			$label .= ' title="' . HTMLHelper::tooltipText(trim($text, ':'), $tooltip, 0) . '"';
		}

		$label .= '>' . $text . '</label>';

		return $label;
	}

	/**
	 * Get BootstrapHelper class for admin UI
	 *
	 * @return EShopHelperBootstrap
	 */
	public static function getAdminBootstrapHelper()
	{
		if (EShopHelper::isJoomla4())
		{
			return new EShopHelperBootstrap('4');
		}

		return new EShopHelperBootstrap('2');
	}
	
	/**
	 * Get clean image path
	 *
	 * @param   string  $image
	 */
	public static function getCleanImagePath($image)
	{
		// This command is needed to make sure image contains space still being displayed properly on Joomla 4
		$image = str_replace('%20', ' ', $image);
	
		$pos = strrpos($image, '#');
	
		if ($pos !== false)
		{
			$image = substr($image, 0, $pos);
		}
	
		return $image;
	}
}
