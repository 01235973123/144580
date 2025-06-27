<?php

/**
 * @version        5.9.5
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Dang Thuc Dam
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Table\Table;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\CMS\Form\FormHelper;
use Joomla\String\StringHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Mail\MailHelper;
use Joomla\Database\DatabaseQuery;

class DonationHelper
{

	/**
	 * Display copy right information
	 *
	 */
	public static function displayCopyRight()
	{
		//echo '<div class="clearfix"></div><div class="copyright" style="text-align:center;margin-top: 5px;"><a href="http://joomdonation.com/components/joom-donation.html" target="_blank"><strong>Joom Donation</strong></a> version <strong>' . self::getInstalledVersion() . '</strong>, Copyright (C) 2010 - ' .
		//	date('Y') . ' <a href="http://joomdonation.com" target="_blank"><strong>Ossolution Team</strong></a></div>';
			
		?>
		<div style="text-align:center; padding: 16px 0; font-size: 14px; color: #888;">
			<span style="font-weight:600; color:#2d89ef;">
				Joom Donation <span style="font-size:13px; font-weight:400;">v<?php echo self::getInstalledVersion(); ?></span>
			</span>
			&nbsp;|&nbsp;
			<span style="color:#444;">&copy; 2010–<?php echo date('Y'); ?></span>
			&nbsp;|&nbsp;
			<a href="https://joomdonation.com" style="color:#2d89ef; text-decoration:none; font-weight:500;">
				Ossolution Team
			</a>
		</div>

		<?php
	}

	/**
	 * Return the current installed version
	 *
	 * @return string
	 */
	public static function getInstalledVersion()
	{
		return '6.0';
	}

    /**
     * Gets a list of the actions that can be performed.
     *
     * @return JObject
     */
    public static function getActions()
    {
        $user   = Factory::getApplication()->getIdentity();
        $result = new CMSObject();
        $actions = array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete');
        foreach ($actions as $action)
        {
            $result->set($action, $user->authorise($action, 'com_jdonation'));
        }
        return $result;
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
	 * @param   string  $alternativeHelper
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public static function callOverridableHelperMethod($helper, $method, $methodArgs = [], $alternativeHelper = null)
	{
		$callableMethods = [];

		if (strtolower($helper) == 'helper')
		{
			$helperMethod = 'DonationHelper::' . $method;
		}
		else
		{
			$helperMethod = 'DonationHelper' . ucfirst($helper) . '::' . $method;
		}

		$callableMethods[] = $helperMethod;

		if ($alternativeHelper)
		{
			$callableMethods[] = 'DonationHelperOverride' . ucfirst($alternativeHelper) . '::' . $method;
		}

		$callableMethods[] = 'DonationHelperOverride' . ucfirst($helper) . '::' . $method;

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
	 * Get configuration data and store in config object
	 *
	 * @return object
	 */
	public static function getConfig($nl2br = false)
	{
		static $config;
		if (!$config)
		{
			$db     = Factory::getContainer()->get('db');
			$query  = $db->getQuery(true);
			$config = new stdClass();
			$query->select('*')
				->from('#__jd_configs');
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row   = $rows[$i];
				$key   = $row->config_key;
				$value = stripslashes($row->config_value);
				if ($nl2br)
				{
					$value = nl2br($value);
				}
				$config->$key = $value;
			}
		}

		return $config;
	}

	/**
	 * Get specify config value
	 *
	 * @param string $key
	 */
	public static function getConfigValue($key)
	{
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('config_value')
			->from('#__jd_configs')
			->where('config_key = ' . $db->quote($key));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 *
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
	 * Get URL of the site, using for Ajax request
	 */
	public static function getSiteUrl()
	{
		$uri  = Uri::getInstance();
		$base = $uri->toString(array('scheme', 'host', 'port'));
		if (strpos(php_sapi_name(), 'cgi') !== false && !ini_get('cgi.fix_pathinfo') && !empty($_SERVER['REQUEST_URI']))
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
			return $base . $path . '/';
		}
		else
		{
			return $base . '/';
		}
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
		static $rate = null;

		if ($rate === null)
		{
			$rate = self::get_conversion($currency,'USD');
		}

		if ($rate > 0)
		{
			$amount = $amount * $rate;
		}

		return round($amount, 2);
	}

    /**
     * This function is used to convert amount to default currency
     * @param $amount
     * @param $currency
     */
	public static function convertAmountToDefaultCurrency($amount, $currency){
	    $config = self::getConfig();
	    $rate = self::get_conversion($currency,$config->currency);
        if ($rate > 0)
        {
            $amount = $amount * $rate;
        }
        return round($amount, 2);
    }

	/**
	 * Get list of form fields using on donation form
	 *
	 * @param null $activeLanguage
	 *
	 * @return mixed
	 */
	public static function getFormFields($activeLanguage = null, $isEmail = false)
	{
		$db          = Factory::getContainer()->get('db');
		$user        = Factory::getApplication()->getIdentity();
		$query       = $db->getQuery(true);
		$fieldSuffix = DonationHelper::getFieldSuffix($activeLanguage);
		$query->select('*')
			->from('#__jd_fields')
			->where('published = 1')
			->where('`access` in (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		if($isEmail == true)
		{
			$query->where('`fieldtype` <> "Message"');
		}
		$query->order('ordering');

		if ($fieldSuffix)
		{
			self::getMultilingualFields($query, array('title', 'description', 'values', 'default_values','place_holder'), $fieldSuffix);
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 *
	 * Get data from donation record
	 *
	 * @param $row
	 *
	 * @return array
	 */
	public static function getDonationData($row, $rowFields, $includeFileUpload = true, $donationForm = true)
	{
		$config = self::getConfig();
		$data  = [];
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('a.name, a.fieldtype, b.field_value')
			->from('#__jd_fields AS a')
			->innerJoin('#__jd_field_value AS b ON a.id = b.field_id ')
			->where('donor_id=' . (int) $row->id);
		$db->setQuery($query);
		$fieldValues = $db->loadObjectList('name');

		foreach ($rowFields as $rowField)
		{
			if (!$includeFileUpload && $rowField->fieldtype == 'File')
			{
				continue;
			}
			//do not show custom field: Message in Email and Invoice
			if($rowField->fieldtype == 'Message' && ! $donationForm)
			{
				continue;
			}
			if ($rowField->is_core)
			{
				$data[$rowField->name] = $row->{$rowField->name};
				if(!$config->populate_from_previous_donation && $donationForm)
				{
					unset($data[$rowField->name]);
				}
			}
			else
			{
				if (isset($fieldValues[$rowField->name]))
				{
					$data[$rowField->name] = $fieldValues[$rowField->name]->field_value;
				}
				if(!$config->populate_from_previous_donation && $donationForm)
				{
					unset($data[$rowField->name]);
				}
			}
		}

		return $data;
	}

	/**
	 * Get form data to used on donation form
	 *
	 * @param $rowFields
	 * @param $userId
	 * @param $config
	 *
	 * @return array
	 */
	public static function getFormData($rowFields, $userId, $config, $includeFileUpload = true, $donationForm = false)
	{
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$data  = [];
		if ($userId)
		{
			if ($config->cb_integration == 1)
			{
				$syncronizer = new OSFSynchronizerCommunitybuilder();
				$mappings    = [];
				foreach ($rowFields as $rowField)
				{
					if ($rowField->field_mapping)
					{
						$mappings[$rowField->name] = $rowField->field_mapping;
					}
				}
				$data = $syncronizer->getData($userId, $mappings);
			}
			elseif ($config->cb_integration == 2)
			{
				$syncronizer = new OSFSynchronizerJomsocial();
				$mappings    = [];
				foreach ($rowFields as $rowField)
				{
					if ($rowField->field_mapping)
					{
						$mappings[$rowField->name] = $rowField->field_mapping;
					}
				}
				$data = $syncronizer->getData($userId, $mappings);
			}
			elseif ($config->cb_integration == 3)
			{
				$syncronizer = new OSFSynchronizerJoomla();
				$mappings    = [];
				foreach ($rowFields as $rowField)
				{
					if ($rowField->field_mapping)
					{
						$mappings[$rowField->name] = $rowField->field_mapping;
					}
				}
				$data = $syncronizer->getData($userId, $mappings);
			}
			elseif ($config->cb_integration == 4) //easy social
            {
                $syncronizer = new OSFSynchronizerEasysocial();
                $mappings    = [];
                foreach ($rowFields as $rowField)
                {
                    if ($rowField->field_mapping)
                    {
                        $mappings[$rowField->name] = $rowField->field_mapping;
                    }
                }
                $data = $syncronizer->getData($userId, $mappings);
            }
            elseif ($config->cb_integration == 5) //easy social
            {
                $syncronizer = new OSFSynchronizerEasyprofile();
                $mappings    = [];
                foreach ($rowFields as $rowField)
                {
                    if ($rowField->field_mapping)
                    {
                        $mappings[$rowField->name] = $rowField->field_mapping;
                    }
                }
                $data = $syncronizer->getData($userId, $mappings);
            }
			else
			{
				$query->select('*')
					->from('#__jd_donors')
					->where('user_id=' . (int) $userId)
					->order('id DESC');
				$db->setQuery($query, 0, 1);
				$rowDonor = $db->loadObject();
				if ($rowDonor)
				{
					$data = self::getDonationData($rowDonor, $rowFields, $includeFileUpload, $donationForm);
				}
			}
		}

		return $data;
	}

	/**
	 * Store custom fields data
	 *
	 * @param $donorId
	 *
	 * @param $data
	 */
	public static function storeFormData($donorId, $data)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$app		   = Factory::getApplication();
		$db            = Factory::getContainer()->get('db');
		$query         = $db->getQuery(true);
		$rowFieldValue = Table::getInstance('Fieldvalue', 'DonationTable');
		$query->delete('#__jd_field_value')->where('donor_id=' . (int) $donorId);
		$db->setQuery($query);
		$db->execute();

		$config       = self::getConfig();
		$uploadFolder = 'media/com_jdonation/files';
		$pathUpload   = JPATH_ROOT . '/' . $uploadFolder;
		if (!is_dir(Path::clean($pathUpload)))
		{
			Folder::create($pathUpload);
		}
		$allowedExtensions = $config->allowed_extensions;
		if (!$allowedExtensions)
		{
			$allowedExtensions = 'doc, docx, ppt, pptx, pdf, zip, rar, jpg, jpeg, png, zipx, mp4';
		}
		$allowedExtensions = explode(',', $allowedExtensions);
		$allowedExtensions = array_map('trim', $allowedExtensions);
		$rowFields         = self::getFormFields();
		foreach ($rowFields as $rowField)
		{
			if ($rowField->is_core)
			{
				continue;
			}

			if ($rowField->fieldtype == 'File')
			{
				$name = $rowField->name;
				// If there are field, we need to upload the file to server and save it !
				if (is_uploaded_file($_FILES[$name]['tmp_name']))
				{
					if ($_FILES[$name]['name'] != '')
					{
						$fileName = $_FILES[$name]['name'];
						$fileExt  = File::getFileExt($fileName);
						if (in_array(strtolower($fileExt), $allowedExtensions))
						{
							$canUpload = true;

							if ($config->upload_max_file_size > 0)
							{
								$maxFileSizeInByte = $config->upload_max_file_size * 1024 * 1024;

								if ($_FILES[$name]['size'] > $maxFileSizeInByte)
								{
									$app->enqueueMessage( Text::sprintf('JD_FILE_SIZE_TOO_LARGE', $config->upload_max_file_size . 'MB'));
									$canUpload     = false;
								}
							}
							if($canUpload)
							{
								$fileName = File::makeSafe($fileName);
								if (is_file(Path::clean($pathUpload . '/' . $fileName)))
								{
									$targetFileName = time() . '_' . $fileName;
								}
								else
								{
									$targetFileName = $fileName;
								}
								if (version_compare(JVERSION, '3.4.4', 'ge'))
								{
									File::upload($_FILES[$name]['tmp_name'], $pathUpload . '/' . $targetFileName, false, true);
								}
								else
								{
									File::upload($_FILES[$name]['tmp_name'], $pathUpload . '/' . $targetFileName);
								}
								$data[$name] = $targetFileName;
							}
						}
					}
				}
				else
				{
					if($data['current_'.$name] != "")
					{
						$data[$name] = $data['current_'.$name];
					}
				}
				
			}
			
			if (isset($data[$rowField->name]))
			{
				$fieldValue              = $data[$rowField->name];
				$rowFieldValue->id       = 0;
				$rowFieldValue->field_id = $rowField->id;
				$rowFieldValue->donor_id = $donorId;
				if (is_array($fieldValue))
				{
					$fieldValue = json_encode($fieldValue);
				}
				$rowFieldValue->field_value = $fieldValue;
				$rowFieldValue->store();
			}
		}
	}

	/**
	 *
	 *
	 * @return string
	 */
	public static function validateEngine()
	{
		$dateNow    = HTMLHelper::_('date', Factory::getDate(), 'Y/m/d');
		$validClass = array(
			"",
			"validate[custom[integer]]",
			"validate[custom[number]]",
			"validate[custom[email]]",
			"validate[custom[url]]",
			"validate[custom[phone]]",
			"validate[custom[date],past[$dateNow]]",
			"validate[custom[ipv4]]",
			"validate[minSize[6]]",
			"validate[maxSize[12]]",
			"validate[custom[integer],min[-5]]",
			"validate[custom[integer],max[50]]");

		return json_encode($validClass);
	}

	public static function getUserInput($userId, $fieldName = 'user_id')
	{
		if (version_compare(JVERSION, '3.5', 'le')){
			// Initialize variables.
			$html = [];
			$link = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=user_id';
			// Initialize some field attributes.
			$attr = ' class="inputbox"';
			// Load the modal behavior script.
			HTMLHelper::_('behavior.modal', 'a.modal_user_id');
			// Build the script.
			$script   = [];
			$script[] = '	function jSelectUser_user_id(id, title) {';
			$script[] = '		var old_id = document.getElementById("user_id").value;';
			$script[] = '		if (old_id != id) {';
			$script[] = '			document.getElementById("' . $fieldName . '").value = id;';
			$script[] = '			document.getElementById("user_id_name").value = title;';
			$script[] = '			populateUserData();';
			$script[] = '		}';
			$script[] = '		SqueezeBox.close();';
			$script[] = '	}';
			// Add the script to the document head.
			DonationHelper::addScriptDeclaration(implode("\n", $script));
			// Load the current username if available.
			$table = Table::getInstance('user');
			if ($userId)
			{
				$table->load($userId);
			}
			else
			{
				$table->name = '';
			}
			// Create a dummy text field with the user name.
			$html[] = '<div class="fltlft">';
			$html[] = '	<input type="text" id="user_id_name"' . ' value="' . htmlspecialchars($table->name, ENT_COMPAT, 'UTF-8') . '"' .
				' disabled="disabled"' . $attr . ' />';
			$html[] = '</div>';
			// Create the user select button.
			$html[] = '<div class="button2-left">';
			$html[] = '<div class="blank">';
			$html[] = '<a class="modal_user_id" title="' . Text::_('JLIB_FORM_CHANGE_USER') . '"' . ' href="' . $link . '"' .
				' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
			$html[] = '	' . Text::_('JLIB_FORM_CHANGE_USER') . '</a>';
			$html[] = '</div>';
			$html[] = '</div>';
			// Create the real field, hidden, that stored the user id.
			$html[] = '<input type="hidden" id="' . $fieldName . '" name="' . $fieldName . '" value="' . $userId . '" />';

			return implode("\n", $html);
		}
		else
		{
			$field = FormHelper::loadFieldType('User');
			$element = new SimpleXMLElement('<field />');
			$element->addAttribute('name', 'user_id');
			$element->addAttribute('class', 'readonly');
			
			$element->addAttribute('onchange', 'populateUserData();');
			
			$field->setup($element, $userId);
			return $field->input;
		}
	}

	/**
	 * Build the tags replacement array which will be used to replace tags in messages
	 *
	 * @param $row    The donation record
	 * @param $config The configuration data
	 *
	 * @return array An array contains replacement tags in key => value format
	 */
	public static function buildReplaceTags($row, $config, $loadCss = false, $toAdmin = false)
	{
		$db          = Factory::getContainer()->get('db');
		$query       = $db->getQuery(true);
		$fieldSuffix = DonationHelper::getFieldSuffix($row->language);
		$replaces    = [];
		$query->select($db->quoteName('title' . $fieldSuffix))
			->from('#__jd_campaigns')
			->where('id=' . (int) $row->campaign_id);
		$db->setQuery($query);
		$replaces['campaign']        = $db->loadResult();

		if($row->currency_code == "")
		{
			$show_symbol = 1;
		}
		elseif($row->currency_code == $config->currency)
		{
			$show_symbol = 1;
		}
		else
		{
			$show_symbol = 0;
		}
		if($show_symbol == 1)
		{
			$replaces['amount']      = DonationHelperHtml::formatAmount($config, $row->amount);
		}
		else
		{
			$replaces['amount']      = DonationHelperHtml::formatAmount($config, $row->amount , $row->currency_code, 0);
		}
		$replaces['payment_processing_fee'] = DonationHelperHtml::formatAmount($config, $row->payment_fee, $row->currency_code);

		$final_amount = (float) $row->amount + (float) $row->payment_fee;
		$replaces['final_amount'] = DonationHelperHtml::formatAmount($config, $final_amount, $row->currency_code);

		$donationDetail              = self::getEmailContent($config, $row, $loadCss, $toAdmin);
		$replaces['donation_detail'] = self::convertImgTags($donationDetail);
		if ($row->donation_type == 'R')
		{
			$replaces['donation_type'] = Text::_('JD_RECURRING');
		}
		else
		{
			$replaces['donation_type'] = Text::_('JD_ONETIME');
		}
		$replaces['date']            = date($config->date_format);
		$replaces['donation_date']   = HTMLHelper::_('date', $row->created_date, $config->date_format);
		$replaces['id']              = $row->id;
		$replaces['transaction_id']  = $row->transaction_id;
		$replaces['dedicate_type']   = self::getDedicateType($row->dedicate_type);
		$replaces['honoree_name']    = $row->dedicate_name;
		$decrypted					 = "";
		if($row->payment_method == 'os_jd_offline_creditcard' && $row->params)
		{
			require_once JPATH_ROOT . '/components/com_jdonation/helper/encrypt.php';
			$ccEncryption					= new CreditCardEncryption();
			$params							= new Registry($row->params);
			$last_cc_characters				= $params->get('last_characters');
			$decrypted						= $ccEncryption->decrypt($last_cc_characters);
		}
		$replaces['last4Digits']			= $decrypted;

		if($row->invoice_number != "" && $row->invoice_number != "0")
		{
			$replaces['invoice_number']    = $row->invoice_number;
		}
		if($row->hide_me == 1)
		{
			$replaces['anonymous_donation'] = Text::_('JD_YES');
		}
		else
		{
			$replaces['anonymous_donation'] = Text::_('JD_NO');
		}
		$replaces['user_ip']         = self::get_ip_address();
		$replaces['user']            = $row->first_name." ".$row->last_name;

		if($row->published == 1)
        {
            $replaces['paid']        = Text::_('JD_PAID');
        }
        else
        {
            $replaces['paid']        = Text::_('JD_UNPAID');
        }

		if($row->gift_aid == 1)
		{
			$replaces['gift_aid']        = Text::_('JYES');
		}
		else
		{
			$replaces['gift_aid']        = Text::_('JNO');
		}

		$method = os_jdpayments::getPaymentMethod($row->payment_method);
		if ($method)
		{
			$replaces['payment_method']  = Text::_($method->getTitle());
		}
		else
		{
			$replaces['payment_method']  = "";
		}
		//Get custom fields
		$rowFields = self::getFormFields($row->language, true);
		$fields    = [];
		for ($i = 0, $n = count($rowFields); $i < $n; $i++)
		{
			$rowField = $rowFields[$i];
			if ($rowField->is_core)
			{
				$replaces[strtoupper($rowField->name)] = $row->{$rowField->name};
			}
			else
			{
				$fields[$rowField->id] = $rowField->name;
			}
		}

		$query->clear();
		$query->select('field_id, field_value')
			->from('#__jd_field_value')
			->where('donor_id=' . (int) $row->id);
		$db->setQuery($query);
		$rowValues = $db->loadObjectList();

		if(count($rowValues) == 0 && $row->subscr_id != "")
		{
			$db->setQuery("Select id from #__jd_donors where subscr_id = ".$db->quote($row->subscr_id)." order by id asc limit 1");
			$id = $db->loadResult();

			$db->setQuery("Select * from #__jd_field_value where donor_id = '$id'");
			$fields = $db->loadObjectList();
			if(count($fields))
			{
				foreach($fields as $field)
				{
					$db->setQuery("Insert into #__jd_field_value (id,field_id,donor_id,field_value) values (NULL,'$field->field_id','$row->id','$field->field_value')");
					$db->execute();
				}

				$query->clear();
				$query->select('field_id, field_value')
					->from('#__jd_field_value')
					->where('donor_id=' . (int) $row->id);
				$db->setQuery($query);
				$rowValues = $db->loadObjectList();
			}
		} 
		$values    = [];
		for ($i = 0, $n = count($rowValues); $i < $n; $i++)
		{
			$rowValue                    = $rowValues[$i];
			$values[$rowValue->field_id] = $rowValue->field_value;
		}
		if (count($values))
		{
			foreach ($values as $key => $value)
			{
				$replaces[strtoupper($fields[$key])] = $value;
			}
		}

		return $replaces;
	}

	/**
	 * Create a user account
	 *
	 * @param array $data
	 *
	 * @return int Id of created user
	 */
	public static function saveRegistration($data)
	{
		//Need to load com_users language file
		$lang = Factory::getApplication()->getLanguage();
		$tag  = $lang->getTag();
		if (!$tag)
		{
			$tag = 'en-GB';
		}
		$lang->load('com_users', JPATH_ROOT, $tag);
		$data['name']     = $data['first_name'] . ' ' . $data['last_name'];
		$data['password'] = $data['password2'] = $data['password1'];
		$data['email1']   = $data['email2'] = $data['email'];

		//require_once JPATH_ROOT . '/components/com_users/models/registration.php';
		//$model = new UsersModelRegistration();
		//$model->register($data);

		if (self::isJoomla4())
		{
			Form::addFormPath(JPATH_ROOT . '/components/com_users/forms');

			/* @var \Joomla\Component\Users\Site\Model\RegistrationModel $model */
			$model = Factory::getApplication()->bootComponent('com_users')
				->getMVCFactory()->createModel('Registration', 'Site', ['ignore_request' => true]);
			$model->register($data);
		}
		else
		{
			require_once JPATH_ROOT . '/components/com_users/models/registration.php';

			if (Multilanguage::isEnabled())
			{
				Form::addFormPath(JPATH_ROOT . '/components/com_users/models/forms');
				Form::addFieldPath(JPATH_ROOT . '/components/com_users/models/fields');
			}

			$model = new UsersModelRegistration();
			$model->register($data);
		}


		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__users')
			->where('username=' . $db->quote($data['username']));
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Check if the payment method is enabled or not
	 *
	 * @param $paymentMethod string Name of payment method
	 *
	 * @return bool True if the payment method is enabled, otherwise false
	 */
	public static function isPaymentMethodEnabled($paymentMethod)
	{
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__jd_payment_plugins')
			->where('published = 1')
			->where('name=' . $db->quote($paymentMethod));
		$db->setQuery($query);
		$total = (int) $db->loadResult();
		if ($total)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Load jquery used in the extension
	 *
	 */
	public static function loadJQuery()
	{
		HTMLHelper::_('jquery.framework');
	}

	/**
	 * Load twitter bootstrap framework
	 *
	 * @param bool $loadJs
	 */
	public static function loadBootstrap($loadJs = true)
	{
		$document = Factory::getApplication()->getDocument();
		if ($loadJs)
		{
			HTMLHelper::_('jquery.framework');
			//$document->addScript(JUri::root(true) . '/media/com_jdonation/assets/bootstrap/js/jquery.min.js');
			//$document->addScript(JUri::root(true) . '/media/com_jdonation/assets/bootstrap/js/jquery-noconflict.js');
			DonationHelper::addScript(Uri::root() . 'media/com_jdonation/assets/bootstrap/js/bootstrap.min.js');
		}
		if (self::isJoomla4())
		{
			HTMLHelper::_('bootstrap.loadCss');
		}
		else
		{
			DonationHelper::addStyleSheet(Uri::root() . 'media/com_jdonation/assets/bootstrap/css/bootstrap.css');
			DonationHelper::addStyleSheet(Uri::root() . 'media/com_jdonation/assets/bootstrap/css/bootstrap.min.css');
			DonationHelper::addStyleSheet(Uri::root() . 'media/com_jdonation/assets/bootstrap/css/bootstrap-responsive.min.css');
		}
	}

	/**
	 * Get Itemid of Joom Donation
	 *
	 * @return int
	 */
	public static function getItemid()
	{
		$app       = Factory::getApplication();
		$menus     = $app->getMenu('site');
		$component = ComponentHelper::getComponent('com_jdonation');
		$items     = $menus->getItems('component_id', $component->id);
		$views     = array('campaigns', 'donation');
		foreach ($views as $view)
		{
			$viewUrl = 'index.php?option=com_jdonation&view=' . $view;
			foreach ($items as $item)
			{
				if (strpos($item->link, $viewUrl) !== false)
				{
					return $item->id;
				}
			}
		}

		return 0;
	}

	/**
	 * Load language from main component
	 *
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
			$lang->load('com_jdonation', JPATH_ROOT, $tag);
			$loaded = true;
		}
	}

	/**
	 * Get country code
	 *
	 * @param string $countryName
	 *
	 * @return string
	 */
	public static function getCountryCode($countryName)
	{
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('country_2_code')
			->from('#__jd_countries')
			->where('LOWER(name) = ' . $db->quote(StringHelper::strtolower($countryName)));
		$db->setQuery($query);
		$countryCode = $db->loadResult();
		if (!$countryCode)
		{
			$countryCode = 'US';
		}

		return $countryCode;
	}

	/**
	 * Get state_2_code
	 *
	 * @param string $country
	 * @param string $stateName
	 *
	 * @return string
	 */
	public static function getStateCode($country, $stateName)
	{
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		if (!$country)
		{
			$config  = self::getConfig();
			$country = $config->default_country;
		}

		$query->select('a.state_2_code')
			->from('#__jd_states AS a')
			->innerJoin('#__jd_countries AS b ON a.country_id = b.country_id')
			->where('b.name = ' . $db->quote($country))
			->where('a.state_name = ' . $db->quote($stateName));

		$db->setQuery($query);
		$state = $db->loadResult();

		return $state ? $state : $stateName;
	}

    /**
     * This email is used to send email to administrator when new campaign is added
     * @param $id
     */
	static function sendNewCampaign($id)
    {
        if($id > 0)
        {
            $db                 = Factory::getContainer()->get('db');
            $config             = self::getConfig();
            if ($config->from_email)
            {
                $fromEmail = $config->from_email;
            }
            else
            {
                $fromEmail = Factory::getConfig()->get('mailfrom');
            }
            $user               = Factory::getApplication()->getIdentity();
            $query              = $db->getQuery(true);
            $query->select('title')->from('#__jd_campaigns')->where('id = '.$id);
			$db->setQuery($query);
            $campaign_title     = $db->loadResult();
            $siteName           = Factory::getConfig()->get('sitename');
            $subject            = $config->new_campaign_email_subject;
            $body               = $config->new_campaign_email_body;
            $body               = str_replace("[CAMPAIGN_TITLE]", $campaign_title, $body);
            $body               = str_replace("[OWNER]", $user->name, $body);
            $url                = Route::_("index.php?option=com_jdonation&task=campaign.id&id=".$id);
            $url                = Uri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')).'administrator/'.$url;
            $link               = "<a href='".$url."'>".$url."</a>";
            $body               = str_replace("[CAMPAIGN_LINK]", $link, $body);
            $mailer             = Factory::getMailer();
            if ($config->notification_emails == '')
            {
                $notificationEmails = $fromEmail;
            }
            else
            {
                $notificationEmails = $config->notification_emails;
            }
            $notificationEmails = str_replace(' ', '', $notificationEmails);
            $emails             = explode(',', $notificationEmails);
            // Add attachment
            for ($i = 0, $n = count($emails); $i < $n; $i++)
            {
                $email          = $emails[$i];
                $mailer->ClearAllRecipients();
				try
				{
					$mailer->sendMail($fromEmail, $siteName, $email, $subject, $body, 1);
				}
				catch (Exception $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
				}
            }
        }
    }

	/**
	 * Send notification emails to administrators and confirmation email to donor
	 *
	 * @param object $row
	 * @param object $config
	 */
	public static function sendEmails($row, $config, $data = [])
	{
		$db          = Factory::getContainer()->get('db');
		$query       = $db->getQuery(true);
		$mailer      = Factory::getMailer();
		$fieldSuffix = DonationHelper::getFieldSuffix($row->language);
		if ($fieldSuffix)
		{
			
			$configFields = array(
				'user_email_subject',
				'user_email_body_offline',
				'user_email_body_offline_received',
				'user_email_body'
			);
			DonationHelper::getMultilingualConfigData($config, $fieldSuffix, $configFields);
			
		}

		$siteName = Factory::getConfig()->get('sitename');
		$replaces = self::buildReplaceTags($row, $config, true);
		// Override email message
		if ($row->campaign_id)
		{
			$query->select('*')
				->from('#__jd_campaigns')
				->where('id = ' . (int) $row->campaign_id);
			$db->setQuery($query);

			if ($fieldSuffix)
			{
				if((int)$config->simple_language == 0)
				{
					$campaignFields = array(
						'title',
						'user_email_subject',
						'user_email_body',
						'user_email_body_offline',
						'user_email_body_offline_received'
					);
				}
				else
				{
					$campaignFields = array(
						'title'
					);
				}
				DonationHelper::getMultilingualFields($query, $campaignFields, $fieldSuffix);
			}

			$rowCampaign = $db->loadObject();
			if ($rowCampaign->user_email_subject)
			{
				$config->user_email_subject = $rowCampaign->user_email_subject;
			}
			if ($rowCampaign->user_email_body)
			{
				$config->user_email_body = "<HTML>".$rowCampaign->user_email_body."</HTML>";
			}
			if ($rowCampaign->notification_emails)
			{
				$config->notification_emails = $rowCampaign->notification_emails;
			}
			if($rowCampaign->user_email_body_offline)
			{
				$config->user_email_body_offline = $rowCampaign->user_email_body_offline;
			}
			if($rowCampaign->user_email_body_offline_received)
			{
				$config->user_email_body_offline_received = $rowCampaign->user_email_body_offline_received;
			}
		}

        if ($row->campaign_id && $rowCampaign->from_name != '')
        {
            $fromName = $rowCampaign->from_name;
        }
        elseif ($config->from_name)
        {
            $fromName = $config->from_name;
        }
        else
        {
            $fromName = Factory::getConfig()->get('fromname');
        }

        if ($row->campaign_id && $rowCampaign->from_email != '')
        {
            $fromEmail = $rowCampaign->from_email;
        }
        elseif ($config->from_email)
        {
            $fromEmail = $config->from_email;
        }
        else
        {
            $fromEmail = Factory::getConfig()->get('mailfrom');
        }

		//Notification email send to user
		$subject = $config->user_email_subject;
		if ($row->payment_method == 'os_offline')
		{
			if($row->published == 0)
			{
				$body = $config->user_email_body_offline;
			}
			elseif($row->published == 1)
			{
				$body = $config->user_email_body_offline_received;
			}
		}
		else
		{
			$body = $config->user_email_body;
		}
		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$body    = str_replace("[$key]", $value, $body);
			$subject = str_replace("[$key]", $value, $subject);
		}
		$body        = self::convertImgTags($body);
		$attachments = [];
		if ($config->activate_donation_receipt_feature && $config->send_receipt_via_email && $row->published == 1)
		{
            if (!$row->invoice_number)
            {
                $row->invoice_number = self::getInvoiceNumber($row);
                $row->store();
            }
            $invoiceNumber = self::formatInvoiceNumber($row->invoice_number, $config, $row);
            self::generateInvoicePDF($row);
			$attachments[] = JPATH_ROOT . '/media/com_jdonation/receipts/' .$invoiceNumber . '.pdf';
		}
		try
		{
			$replyArr = [];
			if ($rowCampaign->reply_email)
			{
				$replyArr[] = $rowCampaign->reply_email;
			}
			elseif($config->reply_email != '')
			{
				$replyArr[] = $config->reply_email;
			}
			if($body != "" && $subject != "")
			{
				$mailer->sendMail($fromEmail, $fromName, $row->email, $subject, $body, 1, null, null, $attachments, $replyArr);
			}
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}
		//log Emails
		if($body != "" && $subject != "")
		{
			static::logEmails(array($row->email), $subject, $body, 1, 'confirmation');
		}

		$mailer->ClearAttachments();
		if ($row->receive_user_id)
		{
			//Send a CC email to administrator
			$query->clear();
			$query->select('*')
				->from('#__users')
				->where('id = ' . (int) $row->receive_user_id);
			$db->setQuery($query);
			$rowUser = $db->loadObject();
			if ($rowUser)
			{
				$subject      = $config->member_email_subject;
				$body         = $config->member_email_body;
				$receiveEmail = $rowUser->email;
				$name         = $rowUser->name;
				$body         = str_replace('[NAME]', $name, $body);
				$body         = str_replace('[SITE_NAME]', $siteName, $body);
				foreach ($replaces as $key => $value)
				{
					$key     = strtoupper($key);
					$body    = str_replace("[$key]", $value, $body);
					$subject = str_replace("[$key]", $value, $subject);
				}
				$mailer->ClearAllRecipients();
				try
				{
					$replyArr = [];
					if ($rowCampaign->reply_email)
					{
						$replyArr[] = $rowCampaign->reply_email;
					}
					elseif($config->reply_email != '')
					{
						$replyArr[] = $config->reply_email;
					}
					if($body != "" && $subject != "")
					{
						$mailer->sendMail($fromEmail, $fromName, $receiveEmail, $subject, $body, 1 , null, null, null, $replyArr);
					}
				}
				catch (Exception $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
				}
				//log Emails
				static::logEmails(array($receiveEmail), $subject, $body, 1, 'member_email');
			}
		}

		//Send attachments to admin emails
		if ($config->send_attachment_to_admin_email)
		{
			$query->clear();
			$query->select('a.id, a.name, a.fieldtype')
				->from('#__jd_fields AS a')
				->where('a.published = 1')
				->where('fieldtype = "File"');
			$db->setQuery($query);
			$rowFields       = $db->loadObjectList();
			$attachmentsPath = JPATH_ROOT . '/media/com_jdonation/files';
			for ($i = 0, $n = count($rowFields); $i < $n; $i++)
			{
				$rowField  = $rowFields[$i];
				$fieldName = strtoupper($rowField->name);
				if (isset($replaces[$fieldName]))
				{
					$fileName = $replaces[$fieldName];
					if ($fileName && file_exists($attachmentsPath . '/' . $fileName))
					{
						$pos = strpos($fileName, '_');
						if ($pos !== false)
						{
							$originalFilename = substr($fileName, $pos + 1);
						}
						else
						{
							$originalFilename = $fileName;
						}
						$mailer->addAttachment($attachmentsPath . '/' . $fileName, $originalFilename);
					}
				}
			}
		}
		//send campaign owner notification email
		if($config->use_campaign && $row->campaign_id > 0)
		{
			$user_id = self::getCampaignOwner($row->campaign_id);
			if((int)$user_id > 0)
			{
				$owner								= Factory::getUser($user_id);
				$ownerEmail							= $owner->email;
				$campaignOwnerNotificationSbj		= $config->campaign_owner_notification;
				$campaignOwnerNotificationBody		= $config->campaign_owner_notification_body;
				if($campaignOwnerNotificationSbj != "" && $campaignOwnerNotificationBody != "")
				{
					$campaignOwnerNotificationBody	= "<HTML>".$campaignOwnerNotificationBody."</HTML>";
					$replaces						= self::buildReplaceTags($row, $config, true, true);
					foreach ($replaces as $key => $value)
					{
						$key     = strtoupper($key);
						$campaignOwnerNotificationBody      = str_replace("[$key]", $value, $campaignOwnerNotificationBody);
						$campaignOwnerNotificationSbj		= str_replace("[$key]", $value, $campaignOwnerNotificationSbj);
					}
					try
					{
						if($campaignOwnerNotificationBody != "" && $campaignOwnerNotificationSbj != "")
						{
							$mailer->sendMail($fromEmail, $fromName, $ownerEmail, $campaignOwnerNotificationSbj, $campaignOwnerNotificationBody, 1);
						}
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
					}
				}
			}
		}
		//Send emails to notification emails to admin

		if ($config->notification_emails == '')
		{
			$notificationEmails = $fromEmail;
		}
		else
		{
			$notificationEmails = $config->notification_emails;
		}
		$notificationEmails = str_replace(' ', '', $notificationEmails);
		$emails             = explode(',', $notificationEmails);
		$subject            = $config->admin_email_subject;
		$body               = "<HTML>".$config->admin_email_body."</HTML>";
        $replaces           = self::buildReplaceTags($row, $config, true, true);
		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$body    = str_replace("[$key]", $value, $body);
			$subject = str_replace("[$key]", $value, $subject);
		}
		// Add attachment
		if($config->use_campaign && $rowCampaign->user_id > 0 && ($campaignOwnerNotificationSbj == '' || $campaignOwnerNotificationBody == ''))
		{
			$agency = Factory::getUser($rowCampaign->user_id);
			$emails[] = $agency->email;
		}
		for ($i = 0, $n = count($emails); $i < $n; $i++)
		{
			$email = $emails[$i];
			$mailer->ClearAllRecipients();
			if($config->send_receipt_to_admin && $config->activate_donation_receipt_feature && $row->published == 1)
			{
				if(count($attachments) == 0)
				{
					self::generateInvoicePDF($row);
					if (!$row->invoice_number)
					{
						$row->invoice_number = self::getInvoiceNumber($row);
					}
					$invoiceNumber = self::formatInvoiceNumber($row->invoice_number, $config, $row);

					$attachments[] = JPATH_ROOT . '/media/com_jdonation/receipts/' . $invoiceNumber .'.pdf';
				}
				try
				{
					if($body != "" && $subject != "")
					{
						$mailer->sendMail($fromEmail, $fromName, $email, $subject, $body, 1,  null, null, $attachments);
					}
				}
				catch (Exception $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
				}
			}
			else
			{
				try
				{
					if($body != "" && $subject != "")
					{
						$mailer->sendMail($fromEmail, $fromName, $email, $subject, $body, 1);
					}
				}
				catch (Exception $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
				}
			}
		}
		//log Emails
		if($body != "" && $subject != "")
		{
			static::logEmails($emails, $subject, $body, 2, 'admin_email');
		}
		//send email to honoree
        $replaces           = self::buildReplaceTags($row, $config, true, false);
        if($row->show_dedicate == 1 && $row->dedicate_email != "" && $config->send_email_to_honoree){
            $subject            = $config->honoree_email_subject;
            $body               = $config->honoree_email_body;
            foreach ($replaces as $key => $value)
            {
                $key     = strtoupper($key);
                $body    = str_replace("[$key]", $value, $body);
                $subject = str_replace("[$key]", $value, $subject);
            }
            $mailer->ClearAllRecipients();
			try
			{
				if($body != "" && $subject != "")
				{
					$mailer->sendMail($fromEmail, $fromName, $row->dedicate_email, $subject, $body, 1);
				}
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			}
			//log Emails
			if($body != "" && $subject != "")
			{
				static::logEmails(array( $row->dedicate_email), $subject, $body, 1, 'honoree_email');
			}
        }
	}

	static function sendRequestPaymentEmail($row, $config)
	{
		if (!MailHelper::isEmailAddress($row->email))
		{
			return;
		}

		
		$mailer = Factory::getMailer();

		if ($row->campaign_id && $rowCampaign->from_name != '')
        {
            $fromName = $rowCampaign->from_name;
        }
        elseif ($config->from_name)
        {
            $fromName = $config->from_name;
        }
        else
        {
            $fromName = Factory::getConfig()->get('fromname');
        }

        if ($row->campaign_id && $rowCampaign->from_email != '')
        {
            $fromEmail = $rowCampaign->from_email;
        }
        elseif ($config->from_email)
        {
            $fromEmail = $config->from_email;
        }
        else
        {
            $fromEmail = Factory::getConfig()->get('mailfrom');
        }

		$subject	 = $config->payment_request_sbj;
		$body		 = $config->payment_request_body;

		$replaces = self::buildReplaceTags($row, $config, true);

		$replaces['payment_link'] = Uri::root() . 'index.php?option=com_jdonation&task=donor.processPayment&id=' . $row->id;

		if (empty($subject))
		{
			throw new Exception('Please configure request payment email subject');
		}

		if (empty($body))
		{
			throw new Exception('Please configure request payment email body');
		}

		foreach ($replaces as $key => $value)
		{
			$value   = (string) $value;
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		try
		{
			if($body != "" && $subject != "")
			{
				$mailer->sendMail($fromEmail, $fromName, $row->email, $subject, $body, 1);
			}
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}
		//log Emails
		if($body != "" && $subject != "")
		{
			static::logEmails(array( $row->email), $subject, $body, 1, 'payment_request');
		}
	}

	static function getDedicateType($type)
	{
	    switch ($type){
            case "1":
                return Text::_('JD_IN_HONOR_OF');
                break;
            case "2":
                return Text::_('JD_IN_MEMORY_OF');
                break;
            case "3":
                return Text::_('JD_IN_DEDICATE_TO');
                break;
            case "4":
                return Text::_('JD_IN_REMEMBRANCE_OF');
                break;
        }
    }

	/**
	 * Get email content for sending to administrator
	 *
	 * @param  object $config : containing configuration data
	 *                        $param Object $row : containing donation record detail
	 */
	public static function getEmailContent($config, $row, $loadCss = false, $toAdmin= false)
	{
		$last4Digits                = '';
        if($toAdmin && $row->payment_method == 'os_jd_offline_creditcard'){
            $cardNumber = Factory::getApplication()->input->getString('x_card_num', '');
            if ($cardNumber)
            {
                $last4Digits         = substr($cardNumber, strlen($cardNumber) - 4);
            }
        }
		$db          = Factory::getContainer()->get('db');
		$fieldSuffix = DonationHelper::getFieldSuffix($row->language);
		$sql         = 'SELECT `title' . $fieldSuffix . '` FROM #__jd_campaigns WHERE id=' . (int) $row->campaign_id;
		$db->setQuery($sql);
		$campaignTitle         = $db->loadResult();
		$data                  = [];
		$data['config']        = $config;
		$data['last4Digits']   = $last4Digits;
		$data['row']           = $row;
		$data['campaignTitle'] = $campaignTitle;
		if ($loadCss)
		{
			$layout      = 'common/email_donation_detail.php';
			$data['css'] = file_get_contents(JPATH_ROOT . '/media/com_jdonation/assets/css/style.css');
		}
		else
		{
			$layout = 'common/donation_detail.php';
		}
		$rowFields = self::getFormFields($row->language, true);
		$formData  = self::getDonationData($row, $rowFields, true, false);
		$form      = new OSFForm($rowFields);
		$form->bind($formData)->prepareFormField($row->campaign_id);
		$data['form'] = $form;
		return DonationHelperHtml::loadCommonLayout($layout, $data);
	}

	/**
	 * Send recurring email
	 *
	 * @param object $row
	 * @param object $config
	 */
	public static function sendRecurringEmail($row, $config)
	{
		$db          = Factory::getContainer()->get('db');
		$query       = $db->getQuery(true);
		$mailer      = Factory::getMailer();
		$fieldSuffix = DonationHelper::getFieldSuffix($row->language);
		if ($fieldSuffix)
		{
			$configFields = array(
				'recurring_email_subject',
				'recurring_email_body'
			);
			DonationHelper::getMultilingualConfigData($config, $fieldSuffix, $configFields);
		}
		if ($config->from_name)
		{
			$fromName = $config->from_name;
		}
		else
		{
			$fromName = Factory::getConfig()->get('fromname');
		}
		if ($config->from_email)
		{
			$fromEmail = $config->from_email;
		}
		else
		{
			$fromEmail = Factory::getConfig()->get('mailfrom');
		}
		$siteName = Factory::getConfig()->get('sitename');
		$replaces = self::buildReplaceTags($row, $config, true);
		$amount   = Factory::getApplication()->input->getFloat('receive_amount', '');
		if ($amount == '')
		{
			$amount = $row->amount;
		}
		if ($row->campaign_id)
		{
			$query->select('*')
				->from('#__jd_campaigns')
				->where('id = ' . (int) $row->campaign_id);
			$db->setQuery($query);
			if ($fieldSuffix)
			{
				if((int)$config->simple_language == 0)
				{
					$campaignFields = array(
						'recurring_email_subject',
						'recurring_email_body'
					);
					DonationHelper::getMultilingualFields($query, $campaignFields, $fieldSuffix);
				}
			}
			$rowCampaign = $db->loadObject();
			if ($rowCampaign->recurring_email_subject)
			{
				$config->recurring_email_subject = $rowCampaign->recurring_email_subject;
			}
			if ($rowCampaign->recurring_email_body)
			{
				$config->recurring_email_body = $rowCampaign->recurring_email_body;
			}
			if ($rowCampaign->notification_emails)
			{
				$config->notification_emails = $rowCampaign->notification_emails;
			}
		}
		//Notification email send to administrators
		$subject = $config->recurring_email_subject;
		$body    = $config->recurring_email_body;
		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$body    = str_replace("[$key]", $value, $body);
			$subject = str_replace("[$key]", $value, $subject);
		}
		//Replace some information in the body
		$body       = str_replace('[DONATION_AMOUNT]', number_format($amount, 2), $body);
		$detailLink = Uri::base() . '/administrator/index.php?option=com_jdonation&task=edit&cid[]=' . $row->id;
		$body       = str_replace('[DETAIL_LINK]', $detailLink, $body);
		if ($config->notification_emails == '')
		{
			$notificationEmails = $fromEmail;
		}
		else
		{
			$notificationEmails = $config->notification_emails;
		}
		$notificationEmails = str_replace(' ', '', $notificationEmails);
		$emails             = explode(',', $notificationEmails);
		for ($i = 0, $n = count($emails); $i < $n; $i++)
		{
			$email = $emails[$i];
			$mailer->ClearAllRecipients();
			try
			{
				if($body != "" && $subject != "")
				{
					$mailer->sendMail($fromEmail, $fromName, $email, $subject, $body, 1);
				}
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			}
		}
		if($body != "" && $subject != "")
		{
			static::logEmails($emails, $subject, $body, 2, 'recurring_email');
		}
		if ($row->receive_user_id)
		{
			$query->clear();
			$query->select('*')
				->from('#__users')
				->where('id=' . $row->receive_user_id);
			$db->setQuery($query);
			$rowUser = $db->loadObject();
			if ($rowUser)
			{
				$name         = $rowUser->name;
				$receiveEmail = $rowUser->email;
				$subject      = $config->member_recurring_email_subject;
				$body         = $config->member_recurring_email_body;
				$body         = str_replace('[AMOUNT]', number_format($amount, 2), $body);
				$body         = str_replace('[SITE_NAME]', $siteName, $body);
				$body         = str_replace('[NAME]', $name, $body);
				foreach ($replaces as $key => $value)
				{
					$key     = strtoupper($key);
					$body    = str_replace("[$key]", $value, $body);
					$subject = str_replace("[$key]", $value, $subject);
				}
				$mailer->ClearAllRecipients();
				try
				{
					if($body != "" && $subject != "")
					{
						$mailer->sendMail($fromEmail, $fromName, $receiveEmail, $subject, $body, 1);
					}
				}
				catch (Exception $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
				}
				if($body != "" && $subject != "")
				{
					static::logEmails(array( $receiveEmail), $subject, $body, 1, 'member_recurring_email');
				}
			}
		}
	}

	public static function sendRecurringDonationCancelEmail($row, $config)
    {
        $db          = Factory::getContainer()->get('db');
        $query       = $db->getQuery(true);
        $mailer      = Factory::getMailer();
        $fieldSuffix = DonationHelper::getFieldSuffix($row->language);
        if ($fieldSuffix)
        {
            $configFields = array(
                'cancel_recurring_admin_email_subject',
                'cancel_recurring_admin_email_body'
            );
            DonationHelper::getMultilingualConfigData($config, $fieldSuffix, $configFields);
        }
        if ($config->from_name)
        {
            $fromName = $config->from_name;
        }
        else
        {
            $fromName = Factory::getConfig()->get('fromname');
        }
        if ($config->from_email)
        {
            $fromEmail = $config->from_email;
        }
        else
        {
            $fromEmail = Factory::getConfig()->get('mailfrom');
        }
        $siteName = Factory::getConfig()->get('sitename');
        $replaces = self::buildReplaceTags($row, $config, true);
        $amount   = Factory::getApplication()->input->getFloat('receive_amount', '');
        if ($amount == '')
        {
            $amount = $row->amount;
        }
        if ($row->campaign_id)
        {
            $query->select('*')
                ->from('#__jd_campaigns')
                ->where('id = ' . (int) $row->campaign_id);
            $db->setQuery($query);
            $rowCampaign = $db->loadObject();
            if ($rowCampaign->notification_emails)
            {
                $config->notification_emails = $rowCampaign->notification_emails;
            }
        }
        //Notification email send to administrators
        $subject = $config->cancel_recurring_admin_email_subject;
        $body    = $config->cancel_recurring_admin_email_body;
        foreach ($replaces as $key => $value)
        {
            $key     = strtoupper($key);
            $body    = str_replace("[$key]", $value, $body);
            $subject = str_replace("[$key]", $value, $subject);
        }
        //Replace some information in the body
        $body       = str_replace('[DONATION_AMOUNT]', number_format($amount, 2), $body);
        if ($config->notification_emails == '')
        {
            $notificationEmails = $fromEmail;
        }
        else
        {
            $notificationEmails = $config->notification_emails;
        }
        $notificationEmails = str_replace(' ', '', $notificationEmails);
        $emails             = explode(',', $notificationEmails);
        for ($i = 0, $n = count($emails); $i < $n; $i++)
        {
            $email = $emails[$i];
            $mailer->ClearAllRecipients();
			try
			{
				$mailer->sendMail($fromEmail, $fromName, $email, $subject, $body, 1);
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			}
        }
		static::logEmails($emails, $subject, $body, 2, 'cancel_recurring_admin_email');
        if ($row->receive_user_id)
        {
            $query->clear();
            $query->select('*')
                ->from('#__users')
                ->where('id=' . $row->receive_user_id);
            $db->setQuery($query);
            $rowUser = $db->loadObject();
            if ($rowUser)
            {
                $name         = $rowUser->name;
                $receiveEmail = $rowUser->email;
                $subject      = $config->cancel_recurring_email_subject;
                $body         = $config->cancel_recurring_email_body;
                $body         = str_replace('[AMOUNT]', number_format($amount, 2), $body);
                $body         = str_replace('[SITE_NAME]', $siteName, $body);
                $body         = str_replace('[NAME]', $name, $body);
                foreach ($replaces as $key => $value)
                {
                    $key     = strtoupper($key);
                    $body    = str_replace("[$key]", $value, $body);
                    $subject = str_replace("[$key]", $value, $subject);
                }
                $mailer->ClearAllRecipients();
				try
				{
					$mailer->sendMail($fromEmail, $fromName, $receiveEmail, $subject, $body, 1);
				}
				catch (Exception $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
				}
				static::logEmails(array( $receiveEmail), $subject, $body, 1, 'cancel_recurring_email');
            }
        }
    }

	/**
	 * Convert src of img tags to use absolute links instead of ralative link
	 *
	 * @param $html_content
	 *
	 * @return mixed
	 */
	public static function convertImgTags($html_content)
	{
		$patterns     = [];
		$replacements = [];
		$i            = 0;
		$src_exp      = "/src=\"(.*?)\"/";
		$link_exp     = "[^http:\/\/www\.|^www\.|^https:\/\/|^http:\/\/]";
		$siteURL      = Uri::root();
		preg_match_all($src_exp, $html_content, $out, PREG_SET_ORDER);
		foreach ($out as $val)
		{
			$links = preg_match($link_exp, $val[1], $match, PREG_OFFSET_CAPTURE);
			if ($links == '0')
			{
				$patterns[$i]     = $val[1];
				$patterns[$i]     = "\"$val[1]";
				$replacements[$i] = $siteURL . $val[1];
				$replacements[$i] = "\"$replacements[$i]";
			}
			$i++;
		}
		$mod_html_content = str_replace($patterns, $replacements, $html_content);

		return $mod_html_content;
	}

	/**
	 * Process download a file
	 *
	 * @param string $file : Full path to the file which will be downloaded
	 */
	public static function processDownload($filePath, $filename)
	{
		jimport('joomla.filesystem.file');
		$fsize    = @filesize($filePath);
		$mod_date = date('r', filemtime($filePath));
		$cont_dis = 'attachment';
		$ext      = File::getExt($filename);
		$mime     = self::getMimeType($ext);
		// required for IE, otherwise Content-disposition is ignored
		if (ini_get('zlib.output_compression'))
		{
			ini_set('zlib.output_compression', 'Off');
		}
		header("Pragma: public");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Expires: 0");
		header("Content-Transfer-Encoding: binary");
		header(
			'Content-Disposition:' . $cont_dis . ';' . ' filename="' . $filename . '";' . ' modification-date="' . $mod_date . '";' . ' size=' . $fsize .
			';'); //RFC2183
		header("Content-Type: " . $mime); // MIME type
		header("Content-Length: " . $fsize);

		if (!ini_get('safe_mode'))
		{ // set_time_limit doesn't work in safe mode
			@set_time_limit(0);
		}

		self::readfile_chunked($filePath);
	}

	/**
	 * Get mimetype of a file
	 *
	 * @return string
	 */
	public static function getMimeType($ext)
	{
		require_once JPATH_ROOT . "/components/com_jdonation/helper/mime.mapping.php";
		foreach ($mime_extension_map as $key => $value)
		{
			if ($key == $ext)
			{
				return $value;
			}
		}

		return "";
	}

	/**
	 * Read file
	 *
	 * @param string $filename
	 * @param        $retbytes
	 *
	 * @return unknown
	 */
	public static function readfile_chunked($filename, $retbytes = true)
	{
		$chunksize = 1 * (1024 * 1024); // how many bytes per chunk
		$cnt       = 0;
		$handle    = fopen($filename, 'rb');
		if ($handle === false)
		{
			return false;
		}
		while (!feof($handle))
		{
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			@ob_flush();
			flush();
			if ($retbytes)
			{
				$cnt += strlen($buffer);
			}
		}
		$status = fclose($handle);
		if ($retbytes && $status)
		{
			return $cnt; // return num. bytes delivered like readfile() does.
		}

		return $status;
	}

	/**
	 * Generate invoice PDF (receipt) for a donation record
	 *
	 * @param $row
	 */
	public static function generateInvoicePDF($row)
	{
		self::loadLanguage();
		$app      = Factory::getApplication();
		$db       = Factory::getContainer()->get('db');
		$config   = self::getConfig();
		$siteName = $app->get("sitename");
		require_once JPATH_ROOT . "/components/com_jdonation/tcpdf/tcpdf.php";
		if(file_exists(JPATH_ROOT . "/components/com_jdonation/tcpdf/config/lang/eng.php"))
		{
			require_once JPATH_ROOT . "/components/com_jdonation/tcpdf/config/lang/eng.php";
		}
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		if($config->invoice_readonly)
		{
			$pdf->SetProtection(array('print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'), '', null, 0, null);
		}
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($siteName);
		$pdf->SetTitle('Invoice');
		$pdf->SetSubject('Invoice');
		$pdf->SetKeywords('Invoice');
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		//set auto page breaks
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		//$pdf->SetFont('times', '', 8);
		$font = empty($config->pdf_font) ? 'times' : $config->pdf_font;

		// True type font
		if (substr($font, -4) == '.ttf')
		{
			$font = TCPDF_FONTS::addTTFfont(JPATH_ROOT . '/components/com_jdonation/tcpdf/fonts/' . $font, 'TrueTypeUnicode', '', 96);
		}

		$pdf->SetFont($font, '', 8);
		$pdf->AddPage('P','A4');

		//multiple languages
		if(Multilanguage::isEnabled())
        {
            $language = $row->language;
            if($language != "" && $language != "*")
            {
				$invoiceOutput = $config->{'donation_receipt_layout_'.strtolower($language)};
				if($invoiceOutput == "")
				{
					$language = explode("-", $language);
					$language = $language[0];
					$invoiceOutput = $config->{'donation_receipt_layout_'.$language};
				}
                if($invoiceOutput == "")
                {
                    $invoiceOutput = $config->donation_receipt_layout;
                }
            }
            else
            {
                $invoiceOutput = $config->donation_receipt_layout;
            }
        }
        else
        {
            $invoiceOutput = $config->donation_receipt_layout;
        }

		$replaces                  = self::buildReplaceTags($row, $config);
		$replaces['name']          = $row->first_name . ' ' . $row->last_name;
		$replaces['ITEM_QUANTITY'] = 1;
		//$replaces['AMOUNT']        = $replaces['ITEM_AMOUNT'] = $replaces['ITEM_SUB_TOTAL'] = $replaces['TOTAL_AMOUNT'] = number_format($row->amount, 2);
		$replaces['AMOUNT']        = $replaces['ITEM_AMOUNT'] = $replaces['ITEM_SUB_TOTAL'] = $replaces['TOTAL_AMOUNT'] = 
		DonationHelperHtml::formatAmount($config, $row->amount, $row->currency_code);

		
        $replaces['invoice_number'] = self::formatInvoiceNumber($row->invoice_number, $config, $row);
		if ($row->campaign_id)
		{
			$sql = 'SELECT title FROM #__jd_campaigns WHERE id=' . $row->campaign_id;
			$db->setQuery($sql);
			$campaignTitle = $db->loadResult();
		}
		else
		{
			$campaignTitle = '';
		}
		$itemName              = Text::_('JD_ONLINE_DONATION');
		$itemName              = str_replace('[CAMPAIGN_TITLE]', $campaignTitle, $itemName);
		$data['item_name']     = $itemName;
		$replaces['ITEM_NAME'] = $itemName;
		foreach ($replaces as $key => $value)
		{
			$key           = strtoupper($key);
			$invoiceOutput = str_replace("[$key]", $value, $invoiceOutput);
		}
		//echo $invoiceOutput;die();
		$pdf->writeHTML($invoiceOutput, true, false, false, false, '');
		//Filename
		$filePath = JPATH_ROOT . '/media/com_jdonation/receipts/' . $replaces['invoice_number'] . '.pdf';
		$pdf->Output($filePath, 'F');
	}

    public static function formatInvoiceNumber($invoiceNumber, $config, $row = null)
    {
        if (!empty($row->invoice_year))
        {
            $year = $row->invoice_year;
        }
        elseif (!empty($row->created_date))
        {
            $date = Factory::getDate($row->created_date);
            $year = $date->format('Y');
        }
        else
        {
            $year = 0;
        }

        $invoicePrefix = str_replace('[YEAR]', $year, $config->invoice_prefix);

        return $invoicePrefix . str_pad($invoiceNumber, $config->invoice_number_length ?: 4, '0', STR_PAD_LEFT);
    }

	/**
	 * Generate and download the invoice for a donation record
	 *
	 * @param $id
	 */
	static function downloadInvoice($id)
	{
        $config = self::getConfig();
		$row = Table::getInstance('Donor', 'DonationTable');
		$row->load($id);
		$invoiceStorePath = JPATH_ROOT . '/media/com_jdonation/receipts/';
		if ($row)
		{
            if (!$row->invoice_number)
            {
                $row->invoice_number = self::getInvoiceNumber($row);
                $row->store();
            }

            $invoiceNumber = self::formatInvoiceNumber($row->invoice_number, $config, $row);

            $invoicePath = $invoiceStorePath . $invoiceNumber . '.pdf';
			if(is_file(Path::clean($invoicePath)))
			{
				File::delete($invoicePath);
			}
			self::generateInvoicePDF($row);
			$fileName    = $invoiceNumber . '.pdf';
			while (@ob_end_clean()) ;
			self::processDownload($invoicePath, $fileName);
		}
	}

    public static function getInvoiceNumber($row = null)
    {
        $config = self::getConfig();
        $db     = Factory::getContainer()->get('db');
        $query  = $db->getQuery(true);
        $query->select('MAX(invoice_number)')
            ->from('#__jd_donors');

        if ($config->reset_invoice_number)
        {
            $currentYear = date('Y');
            $query->where('invoice_year = ' . $currentYear);
            $row->invoice_year = $currentYear;
        }

        $db->setQuery($query);
        $invoiceNumber = (int) $db->loadResult();

        if (!$invoiceNumber)
        {
            $invoiceNumber = (int) $config->invoice_start_number;
        }
		elseif($invoiceNumber < (int) $config->invoice_start_number )
		{
			$invoiceNumber += (int) $config->invoice_start_number;
		}
        else
        {
            $invoiceNumber++;
        }

        return $invoiceNumber;
    }

	/**
	 * Add sub-menus, use for Joomla 3
	 *
	 * @param $viewName
	 */
	public static function addSubMenus($viewName)
	{
        /*
		if (version_compare(JVERSION, '3.0', 'lt') || $viewName != 'donors')
		{
			OSFHelper::addSubMenus('com_jdonation', $viewName);
		}
        */
	}

	/**
	 * Get list of language uses on the site
	 *
	 * @return array
	 */
	public static function getLanguages()
	{
		if (DonationHelper::isMethodOverridden('DonationHelperOverrideHelper', 'getLanguages'))
		{
			return DonationHelperOverrideHelper::getLanguages();
		}

		$languages = LanguageHelper::getLanguages('lang_code');

		unset($languages[self::getDefaultLanguage()]);

		return array_values($languages);
	}

	/**
	 * Get front-end default language
	 *
	 * @return string
	 */
	public static function getDefaultLanguage()
	{
		$params = ComponentHelper::getParams('com_languages');

		return $params->get('site', 'en-GB');
	}

	/**
	 * Get field suffix used in sql query
	 *
	 * @param null $activeLanguage
	 *
	 * @return string
	 */
	/**
	 * Get field suffix used in sql query
	 *
	 * @return string
	 */
	public static function getFieldSuffix($activeLanguage = null)
	{
		if (DonationHelper::isMethodOverridden('DonationHelperOverrideHelper', 'getFieldSuffix'))
		{
			return DonationHelperOverrideHelper::getFieldSuffix($activeLanguage);
		}
		$prefix = '';
		if (Multilanguage::isEnabled())
		{
			if (!$activeLanguage)
			{
				$activeLanguage = Factory::getApplication()->getLanguage()->getTag();
			}
			if ($activeLanguage != self::getDefaultLanguage())
			{
				$db    = Factory::getContainer()->get('db');
				$query = $db->getQuery(true);
				$query->select('`sef`')
					->from('#__languages')
					->where('lang_code = ' . $db->quote($activeLanguage));
				$db->setQuery($query);
				$sef = $db->loadResult();
				if ($sef)
				{
					$prefix = '_' . $sef;
				}
			}
		}

		return $prefix;
	}

	/**
	 * Helper method to get fields from database table in case the site is multilingual
	 *
	 * @param JDatabaseQuery $query
	 * @param array          $fields
	 * @param                $fieldSuffix
	 */
	public static function getMultilingualFields(DatabaseQuery $query, $fields = [], $fieldSuffix = '')
	{
		foreach ($fields as $field)
		{
			$alias  = $field;
			$dotPos = strpos($field, '.');
			if ($dotPos !== false)
			{
				$alias = substr($field, $dotPos + 1);
			}
			$query->select($query->quoteName($field . $fieldSuffix, $alias));
		}
	}

	/**
	 * Get multilingual data for config
	 *
	 * @param       $config
	 * @param array $fields
	 * @param       $fieldSuffix
	 */
	public static function getMultilingualConfigData($config, $fieldSuffix, $fields = [])
	{
		if (!count($fields))
		{
			$fields = array(
				'amounts_explanation',
				'paypal_redirect_message',
				'admin_email_subject',
				'admin_email_body',
				'user_email_subject',
				'user_email_body',
				'user_email_body_offline',
				'user_email_body_offline_received',
				'recurring_email_subject',
				'recurring_email_body',
				'donation_form_msg',
				'thanks_message_offline',
				'cancel_message',
                'honoree_email_subject',
                'honoree_email_body'
			);
		}

		foreach ($fields as $field)
		{
			if (strlen(trim(strip_tags($config->{$field . $fieldSuffix}))))
			{
				$config->{$field} = $config->{$field . $fieldSuffix};
			}
		}
	}

	/**
	 * This function is used to check to see whether we need to update the database to support multilingual or not
	 *
	 * @return boolean
	 */
	public static function isSynchronized()
	{
		$db             = Factory::getContainer()->get('db');
		$fields         = array_keys($db->getTableColumns('#__jd_campaigns'));
		$extraLanguages = self::getLanguages();
		if (count($extraLanguages))
		{
			foreach ($extraLanguages as $extraLanguage)
			{
				$prefix = $extraLanguage->sef;
				if (!in_array('tilte_' . $prefix, $fields))
				{
					return false;
				}
				if (!in_array('paypal_redirection_message_' . $prefix, $fields))
				{
					return false;
				}
				if (!in_array('user_email_body_offline_received_' . $prefix, $fields))
				{
					return false;
				}
			}
		}

		$fields         = array_keys($db->getTableColumns('#__jd_fields'));
		$extraLanguages = self::getLanguages();
		if (count($extraLanguages))
		{
			foreach ($extraLanguages as $extraLanguage)
			{
				$prefix = $extraLanguage->sef;
				if (!in_array('place_holder_' . $prefix, $fields))
				{
					return false;
				}
			}
		}

		$fields         = array_keys($db->getTableColumns('#__jd_payment_plugins'));
		$extraLanguages = self::getLanguages();
		if (count($extraLanguages))
		{
			foreach ($extraLanguages as $extraLanguage)
			{
				$prefix = $extraLanguage->sef;
				if (!in_array('payment_description_' . $prefix, $fields))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Synchronize Joom Donation database to support multilingual
	 */
	public static function setupMultilingual()
	{
		$db        = Factory::getContainer()->get('db');
		$config	   = self::getConfig();
		$languages = self::getLanguages();
		if (count($languages))
		{
			$campaignTableFields = array_keys($db->getTableColumns('#__jd_campaigns'));
			$fieldTableFields    = array_keys($db->getTableColumns('#__jd_fields'));
			$pluginTableFields   = array_keys($db->getTableColumns('#__jd_payment_plugins'));
			foreach ($languages as $language)
			{
				$prefix = $language->sef;
				$fieldName = 'title_' . $prefix;
				if (!in_array($fieldName, $campaignTableFields))
				{
					$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` VARCHAR( 255 );";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'alias_' . $prefix;
				if (!in_array($fieldName, $campaignTableFields))
				{
					$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` VARCHAR( 255 );";
					$db->setQuery($sql);
					$db->execute();
				}

                $fieldName = 'short_description_' . $prefix;
                if (!in_array($fieldName, $campaignTableFields))
                {
                    $sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` TEXT NULL;";
                    $db->setQuery($sql);
                    $db->execute();
                }

				$fieldName = 'description_' . $prefix;
				if (!in_array($fieldName, $campaignTableFields))
				{
					$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}


				if((int)$config->simple_language == 0)
				{

					$fieldName = 'amounts_explanation_' . $prefix;
					if (!in_array($fieldName, $campaignTableFields))
					{
						$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` TEXT NULL;";
						$db->setQuery($sql);
						$db->execute();
					}

					$fieldName = 'user_email_subject_' . $prefix;
					if (!in_array($fieldName, $campaignTableFields))
					{
						$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` VARCHAR( 255 );";
						$db->setQuery($sql);
						$db->execute();
					}

					$fieldName = 'user_email_body_' . $prefix;
					if (!in_array($fieldName, $campaignTableFields))
					{
						$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` TEXT NULL;";
						$db->setQuery($sql);
						$db->execute();
					}

					$fieldName = 'user_email_body_offline_' . $prefix;
					if (!in_array($fieldName, $campaignTableFields))
					{
						$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` TEXT NULL;";
						$db->setQuery($sql);
						$db->execute();
					}

					$fieldName = 'recurring_email_subject_' . $prefix;
					if (!in_array($fieldName, $campaignTableFields))
					{
						$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` VARCHAR( 255 );";
						$db->setQuery($sql);
						$db->execute();
					}


					$fieldName = 'recurring_email_body_' . $prefix;
					if (!in_array($fieldName, $campaignTableFields))
					{
						$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` TEXT NULL;";
						$db->setQuery($sql);
						$db->execute();
					}

					$fieldName = 'donation_form_msg_' . $prefix;
					if (!in_array($fieldName, $campaignTableFields))
					{
						$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` VARCHAR( 255 );";
						$db->setQuery($sql);
						$db->execute();
					}

					$fieldName = 'thanks_message_' . $prefix;
					if (!in_array($fieldName, $campaignTableFields))
					{
						$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` TEXT NULL;";
						$db->setQuery($sql);
						$db->execute();
					}

					$fieldName = 'paypal_redirection_message_' . $prefix;
					if (!in_array($fieldName, $campaignTableFields))
					{
						$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` TEXT NULL;";
						$db->setQuery($sql);
						$db->execute();
					}


					$fieldName = 'user_email_body_offline_received_' . $prefix;
					if (!in_array($fieldName, $campaignTableFields))
					{
						$sql       = "ALTER TABLE  `#__jd_campaigns` ADD  `$fieldName` TEXT NULL;";
						$db->setQuery($sql);
						$db->execute();
					}
				}


				$fieldName = 'title_' . $prefix;
				if (!in_array($fieldName, $fieldTableFields))
				{
					$sql       = "ALTER TABLE  `#__jd_fields` ADD  `$fieldName` VARCHAR( 255 );";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'description_' . $prefix;
				if (!in_array($fieldName, $fieldTableFields))
				{
					$sql       = "ALTER TABLE  `#__jd_fields` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'values_' . $prefix;
				if (!in_array($fieldName, $fieldTableFields))
				{
					$sql       = "ALTER TABLE  `#__jd_fields` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'default_values_' . $prefix;
				if (!in_array($fieldName, $fieldTableFields))
				{
					$sql       = "ALTER TABLE  `#__jd_fields` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'place_holder_' . $prefix;
				if (!in_array($fieldName, $fieldTableFields))
				{
					$sql       = "ALTER TABLE  `#__jd_fields` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'payment_description_' . $prefix;
				if (!in_array($fieldName, $pluginTableFields))
				{
					$sql       = "ALTER TABLE  `#__jd_payment_plugins` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}
			}
		}
	}

	static function get_conversion($cur_from,$cur_to){
        $http				= HttpFactory::getHttp();
        $url				= 'https://free.currencyconverterapi.com/api/v6/convert?q='.$cur_from.'_'.$cur_to.'&compact=ultra&apiKey=98bea5b83490f12e5be0';
        $response			= $http->get($url);
		$converted			= 1;
		if ($response->code == 200)
        {
            $data = $response->body;
            $returnArr = json_decode($data);
            $converted = $returnArr->{$cur_from.'_'.$cur_to};
        }
        return $converted;
    }



	/**
     * Builds an exchange rate from the response content.
     *
     * @param string $content
     *
     * @return float
     *
     * @throws \Exception
     */
    protected static function buildExchangeRate($content)
    {
        $document = new \DOMDocument();

        if (false === @$document->loadHTML('<?xml encoding="utf-8" ?>' . $content))
        {
            throw new Exception('The page content is not loadable');
        }

        $xpath = new \DOMXPath($document);
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

        if (false !== strpos($bid, ' '))
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

	public static function isMultipleCurrencies()
	{
		$config = self::getConfig();
		$active_currencies = $config->active_currencies;
		$active_currencies_array = explode(",",$active_currencies);
		if(count($active_currencies_array) > 1){
			return true;
		}else{
			return false;
		}
	}

	/**
     * Get IP address of customers
     *
     * @return unknown
     */
    public static function get_ip_address()
    {
        foreach (array(
                     'HTTP_CLIENT_IP',
                     'HTTP_X_FORWARDED_FOR',
                     'HTTP_X_FORWARDED',
                     'HTTP_X_CLUSTER_CLIENT_IP',
                     'HTTP_FORWARDED_FOR',
                     'HTTP_FORWARDED',
                     'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }
    }

	static function isDonorProcessed($transactionId){
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('published')
			->from('#__jd_donors')
			->where('transaction_id = ' . $db->quote($transactionId));
		$db->setQuery($query);
		$status = (int) $db->loadResult();

		if ($status > 0)
		{
			return true;
		}
		return false;
	}

    /**
     * Generate article selection box
     *
     * @param int    $fieldValue
     * @param string $fieldName
     *
     * @return string
     */
    public static function getArticleInput($fieldValue, $fieldName = 'article_id')
    {
        HTMLHelper::_('jquery.framework');
        FormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_content/models/fields');
		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			FormHelper::addFieldPrefix('Joomla\Component\Content\Administrator\Field');

		}
        $field = FormHelper::loadFieldType('Modal_Article');
		if (version_compare(JVERSION, '4.2.0-dev', 'ge'))
		{
			$field->setDatabase(Factory::getContainer()->get('db'));
		}
        $element = new SimpleXMLElement('<field />');
        $element->addAttribute('name', $fieldName);
        $element->addAttribute('select', 'true');
        $element->addAttribute('clear', 'true');

        $field->setup($element, $fieldValue);

        return $field->input;
    }

    /**
     * This function is used to check to see whether if the Newsletter plugin is enabled or not
     */
    static function isNewsletterPluginEnabled(){
        $db         = Factory::getContainer()->get('db');
        $query      = $db->getQuery(true);
        $query->select('count(extension_id)')->from('#__extensions')->where('`type`= "plugin"')->where('`element`= "acymailing" or `element`= "mailchimp"')->where('`folder`= "jdonation"')->where('enabled = "1"');
        $db->setQuery($query);
        $count      = $db->loadResult();
        if($count > 0){
            return true;
        }else{
            return false;
        }
    }


    /**
     * This function is used to check to see if the Mailchimp plugin in enabled and the API key is entered
     * @return bool
     */
    static function isMailchimpPluginEnabled(){
        $db         = Factory::getContainer()->get('db');
        $query      = $db->getQuery(true);
        $query->select('count(extension_id)')->from('#__extensions')->where('`type`= "plugin"')->where('`element`= "mailchimp"')->where('`folder`= "jdonation"')->where('enabled = "1"');
        $db->setQuery($query);
        $count      = $db->loadResult();
        if($count > 0){
            $query->clear();
            $query->select('*')->from('#__extensions')->where('`type`= "plugin"')->where('`element`= "mailchimp"')->where('`folder`= "jdonation"')->where('enabled = "1"');
            $db->setQuery($query);
            $plugin = $db->loadObject();
            $params = new Registry();
            $params->loadString($plugin->params);
            if($params->get('api_key','') == ''){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }

    /**
     * This function is used to calculate total donated amount
     * @param $campaign_id
     */
    static function getTotalDonatedAmount($campaign_id)
    {
        $db = Factory::getContainer()->get('db');
        $db->setQuery("Select sum(amount) from #__jd_donors where campaign_id ='$campaign_id' and published = '1'");
        return $db->loadResult();
    }

    /**
     * This function is used to return number donors of specific campaign
     * @param $campaign_id
     * @return mixed
     */
    static function getTotalDonor($campaign_id)
    {
        $db = Factory::getContainer()->get('db');
        $db->setQuery("Select count(id) from #__jd_donors where campaign_id ='$campaign_id' and published = '1'");
        return $db->loadResult();
    }


    /**
     * This function is used to return days left of campaign
     * @param $campaign_id
     * @return mixed
     */
    static function getLeftDates($campaign_id)
    {
        $db = Factory::getContainer()->get('db');
        $db->setQuery("Select DATEDIFF(end_date, CURDATE()) AS days_left from #__jd_campaigns where id ='$campaign_id' and published = '1'");
        return $db->loadResult();
    }

    public static function getCampaignOwner($campaign_id)
    {
        $db = Factory::getContainer()->get('db');
        $db->setQuery("Select user_id from #__jd_campaigns where id = '$campaign_id'");
        return $db->loadResult();
    }

    static function getUserCampaigns()
    {
        $db = Factory::getContainer()->get('db');
        $user = Factory::getApplication()->getIdentity();
        $db->setQuery("Select id from #__jd_campaigns where user_id = '$user->id'");
        $campaigns = $db->loadColumn(0);
        return $campaigns;
    }

    static function canAccessDonation($id)
    {
        $db = Factory::getContainer()->get('db');
        $user = Factory::getApplication()->getIdentity();
        if((int)$user->id == 0)
        {
            return false;
        }
        $db->setQuery("Select campaign_id from #__jd_donors where id = '$id'");
        $campaign_id = (int)$db->loadResult();

        if($campaign_id == 0)
        {
            return false;
        }

        if($user->id != self::getCampaignOwner($campaign_id))
        {
            return false;
        }

        return true;
    }

    static function isPaypalEnable()
    {
        $db = Factory::getContainer()->get('db');
        $query = $db->getQuery(true);
        $query->select('count(id)')->from('#__jd_payment_plugins')->where('name="os_paypal" and published=1');
        $db->setQuery($query);
        $count = $db->loadResult();
        if($count > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    static function isAuthorizeEnable()
    {
        $db = Factory::getContainer()->get('db');
        $query = $db->getQuery(true);
        $query->select('count(id)')->from('#__jd_payment_plugins')->where('name="os_authnet" and published=1');
        $db->setQuery($query);
        $count = $db->loadResult();
        if($count > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

	static function getCurrencyName($code)
	{
		if($code != "" && $code != "0")
		{
			$db = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->select("currency_name")->from("#__jd_currencies")->where("currency_code = '".$code."'");
			$db->setQuery($query);
			$currency_name = $db->loadResult();
			if($currency_name != "" && $currency_name != "0")
			{
				return $currency_name;
			}
			else
			{
				return $code;
			}
		}
		else
		{
			return "";
		}
	}

    public static function cancelRecurringDonation($id)
    {
        $db    = Factory::getContainer()->get('db');
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__jd_donors')
            ->where('id = ' . (int) $id);
        $db->setQuery($query);
        $row = $db->loadObject();

        if ($row)
        {
            // The recurring subscription already cancelled before, no need to process it further
            if ($row->recurring_donation_cancelled)
            {
                return;
            }

            $query->clear()
                ->update('#__jd_donors')
                ->set('recurring_donation_cancelled = 1')
                ->where('id = ' . $row->id);
            $db->setQuery($query);
            $db->execute();

            $config = self::getConfig();
            self::sendRecurringDonationCancelEmail($row, $config);
        }
    }

    public static function canCancelRecurringDonation($row)
    {
        $user   = Factory::getApplication()->getIdentity();
        $userId = $user->id;

        if ($row
            && (($row->user_id == $userId && $userId) || $user->authorise('core.admin', 'com_jdonation'))
            && !$row->recurring_donation_cancelled)
        {
            return true;
        }

        return false;
    }

    /**
     * Load payment method object
     *
     * @param string $name
     *
     * @return MPFPayment
     * @throws Exception
     */
    public static function loadPaymentMethod($name)
    {
        $db    = Factory::getContainer()->get('db');
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__jd_payment_plugins')
            ->where('published = 1')
            ->where('name = ' . $db->quote($name));
        $db->setQuery($query);
        $row = $db->loadObject();

        if ($row && file_exists(JPATH_ROOT . '/components/com_jdonation/payments/' . $row->name . '.php'))
        {
            require_once JPATH_ROOT . '/components/com_jdonation/payments/' . $name . '.php';

            $params = new Registry($row->params);

            /* @var MPFPayment $method */
            $method = new $name($params);
            $method->setTitle($row->title);

            return $method;
        }

        throw new Exception(sprintf('Payment method %s not found', $name));
    }

	public static function logEmails($emails, $subject, $body, $sentTo = 0, $emailType = '')
	{
		$config = self::getConfig();
		if(static::loggingEnabled($config))
		{
			$email     = $emails[0];
			$bccEmails = [];

			if (count($emails) > 1)
			{
				unset($emails[0]);
				$bccEmails = $emails;
			}
			require_once JPATH_ADMINISTRATOR . '/components/com_jdonation/table/email.php';

			$row             = Table::getInstance('Email', 'DonationTable');
			$row->sent_at    = Factory::getDate()->toSql();
			$row->email      = $email;
			$row->subject    = $subject;
			$row->body       = $body;
			$row->sent_to    = $sentTo;
			$row->email_type = $emailType;
			$row->store();

			if (count($bccEmails))
			{
				foreach ($bccEmails as $email)
				{
					$row->id    = 0;
					$row->email = $email;
					$row->store();
				}
			}
		}
	}

	public static function loggingEnabled($config)
	{
		if ($config->log_emails)
		{
			return true;
		}
		return false;
	}

	public static function isTransactionProcessed($transactionId)
	{
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__jd_donors')
			->where('transaction_id = ' . $db->quote($transactionId))
			->where('published = 1');
		$db->setQuery($query);
		$total = (int) $db->loadResult();

		//return $total > 0;
		if($total > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

    /**
     * Method to accept privacy consent for a donation record
     *
     * @param   Donor  $row
     */
    public static function acceptPrivacyConsent($row)
    {
        if (!$row->user_id)
        {
            return;
        }

        $db    = Factory::getContainer()->get('db');
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__privacy_consents')
            ->where('user_id = ' . (int) $row->user_id)
            ->where('subject = ' . $db->quote('PLG_SYSTEM_PRIVACYCONSENT_SUBJECT'))
            ->where('state = 1');
        $db->setQuery($query);

        // User consented, do not process it further
        if ($db->loadResult())
        {
            return;
        }

        Factory::getApplication()->getLanguage()->load('plg_system_privacyconsent', JPATH_ADMINISTRATOR, $row->language);

        $params = new Registry($row->params);

        // Create the user note
        $privacyConsent = (object) [
            'user_id' => $row->user_id,
            'subject' => 'PLG_SYSTEM_PRIVACYCONSENT_SUBJECT',
            'body'    => Text::sprintf('PLG_SYSTEM_PRIVACYCONSENT_BODY', $params->get('user_ip'), $params->get('user_agent')),
            'created' => Factory::getDate()->toSql(),
        ];

        try
        {
            $db->insertObject('#__privacy_consents', $privacyConsent);
        }
        catch (Exception $e)
        {

        }
    }

	public static function isAvailablePayments()
	{
		$db = Factory::getContainer()->get('db');
		$db->setQuery("Select count(id) from #__jd_payment_plugins where published = '1'");
		$count = $db->loadResult();
		if($count > 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function loadMedia()
	{
		static $loaded;
		if ($loaded == true)
		{
			return;
		}
		
		$document = Factory::getApplication()->getDocument();
		DonationHelper::addStylesheet(Uri::root() . '/media/com_jdonation/assets/css/style.css');
		
		$loaded = true;
	}

	public static function loadComponentCssForModules()
	{
		static $mloaded = false;

		if ($mloaded == true)
		{
			return;
		}

		if (Factory::getApplication()->input->getCmd('option') === 'com_jdonation')
		{
			return;
		}
		$document = Factory::getApplication()->getDocument();
		DonationHelper::addStylesheet(Uri::root() . '/media/com_jdonation/assets/css/style.css');
		if (file_exists(JPATH_ROOT . '/media/com_jdonation/assets/css/custom.css') && filesize(JPATH_ROOT . '/media/com_jdonation/assets/css/custom.css') > 0)
		{
			DonationHelper::addStylesheet(Uri::root() . '/media/com_jdonation/assets/css/custom.css', 'text/css', null, null);
		}
		
		$mloaded = true;
	}

	/**
	 * Get hased field name to store the time which form started to be rendered
	 *
	 * @return string
	 */
	public static function getHashedFieldName()
	{
		$config = Factory::getConfig();

		$siteName = $config->get('sitename');
		$secret   = $config->get('secret');

		return md5($siteName . $secret);
	}


	public static function retrieveNumberDonations($email, $campaign_id)
	{
		$db = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('count(id)')->from('#__jd_donors')->where('email = '.$db->quote($email))->where('published = 1');
		if($campaign_id > 0)
		{
			$query->where('campaign_id='.$campaign_id);
		}
		$db->setQuery($query);
		return (int) $db->loadResult();
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
		return DonationHelperCryptor::encrypt($string);
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
		return DonationHelperCryptor::decrypt($string);
	}

	//chosen Joomla4
	public static function getChoicesJsSelect($html, $hint = '')
	{
		static $isJoomla4;

		if ($isJoomla4 === null)
		{
			$isJoomla4 = self::isJoomla4();
		}

		if ($isJoomla4)
		{
			Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');
			Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');

			Factory::getApplication()->getDocument()->getWebAssetManager()
				->usePreset('choicesjs')
				->useScript('webcomponent.field-fancy-select');

			$attributes = [];

			$hint = $hint ?: Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS');

			$attributes[] = 'placeholder="' . $hint . '""';
			$attributes[] = 'search-placeholder="' . $hint . '""';


			return '<joomla-field-fancy-select ' . implode(' ', $attributes) . '>' . $html . '</joomla-field-fancy-select>';
		}

		return $html;
	}

	public static function getCategoryDonatedAmount($catId)
	{
		$db = Factory::getContainer()->get('db');
		$db->setQuery("Select sum(amount) from #__jd_donors where campaign_id in (Select id from #__jd_campaigns where category_id = '$catId') and published = '1'");
		$total = $db->loadResult();

		$db->setQuery("Select count(id) from #__jd_campaigns where category_id = '$catId'");
		$count = $db->loadResult();

		return sprintf(Text::_('JD_TOTAL_DONATED_OF_CATEGORY'), number_format($total, 2), $count);
	}

	public static function generateQrCode($campaign_id)
	{
		require_once JPATH_ROOT . '/components/com_jdonation/helper/phpqrcode/qrlib.php';
		if(!is_dir(Path::clean(JPATH_ROOT . '/media/com_jdonation')))
		{
            Folder::create(JPATH_ROOT . '/media/com_jdonation');
            Folder::create(JPATH_ROOT . '/media/com_jdonation/qrcodes');
        }
        if(!is_dir(Path::clean(JPATH_ROOT . '/media/com_jdonation/qrcodes')))
		{
            Folder::create(JPATH_ROOT . '/media/com_jdonation/qrcodes');
        }
		$filename = $campaign_id . '.png';
		if (!file_exists(JPATH_ROOT . '/media/com_jdonation/qrcodes/' . $filename))
        {
			$itemId		= DonationHelperRoute::getDonationFormRoute($campaign_id);
			$url		= Route::_(DonationHelperRoute::getDonationFormRoute($campaign_id, $itemId), true, 0, true);
            QRcode::png($url, JPATH_ROOT . '/media/com_jdonation/qrcodes/' . $filename);
        }
	}

	public static function noindex()
	{
		$document = Factory::getApplication()->getDocument();
		$document->addCustomTag('<meta name="robots" content="nofollow" />');
        $document->setMetaData( "robots", "noindex" );
	}

	/**
	 * Helper method to write data to a log file, for debuging purpose
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

	public static function addScript($path, $unique_name = '')
	{
		// Kiểm tra unique_name có phải là text/javascript hoặc rỗng không
		if ($unique_name == '' || strtolower($unique_name) == 'text/javascript') {
			$file_name = pathinfo($path, PATHINFO_FILENAME);
			$unique_name = "com_jdonation." . $file_name;
		}
		// Loại bỏ ký tự không hợp lệ
		$unique_name = preg_replace('/[^a-zA-Z0-9._-]/', '', $unique_name);

		$wa = \Joomla\CMS\Factory::getApplication()->getDocument()->getWebAssetManager();
		$wa->registerAndUseScript($unique_name, $path);
	} 

	public static function addStyleSheet($path, $unique_name = '')
	{
		// Nếu unique_name bị đặt là 'text/css' hoặc rỗng, tự động sinh tên hợp lệ
		if ($unique_name == '' || strtolower($unique_name) == 'text/css') {
			$file_name = pathinfo($path, PATHINFO_FILENAME);
			$unique_name = "com_jdonation." . $file_name;
		}

		// Loại bỏ các ký tự không hợp lệ trong unique_name nếu cần
		$unique_name = preg_replace('/[^a-zA-Z0-9._-]/', '', $unique_name);

		$wa  = \Joomla\CMS\Factory::getApplication()->getDocument()->getWebAssetManager();
		$wa->registerAndUseStyle($unique_name, $path);
	}


	public static function addScriptDeclaration($script)
	{
		Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript($script);
	}


	/**
	 * Helper method to determine if we are in Joomla 4
	 *
	 * @return bool
	 */
	public static function isJoomla5()
	{
		return version_compare(JVERSION, '4.4.99', '>');
	}

	/**
	 * Helper method to determine if we are in Joomla 4
	 *
	 * @return bool
	 */
	public static function isJoomla4()
	{
		return version_compare(JVERSION, '4.0.0-dev', 'ge');
	}
}
