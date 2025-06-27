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

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;

class EShopModelProduct extends EShopModel
{
	/**
	 * Entity ID
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * Entity data
	 *
	 * @var array
	 */
	protected $data = null;

	/**
	 * Current active language
	 *
	 * @var string
	 */
	protected $language = null;

	/**
	 *
	 * Constructor
	 * @since 1.5
	 */
	public function __construct($config = [])
	{
		parent::__construct();
		$input          = Factory::getApplication()->input;
		$this->id       = $input->getInt('id');
		$this->data     = null;
		$this->language = Factory::getLanguage()->getTag();
	}

	/**
	 *
	 * Function to get product data
	 * @see EShopModel::getData()
	 */
	public function &getData()
	{
		if (empty($this->data))
		{
			$this->_loadData();
		}

		return $this->data;
	}

	/**
	 *
	 * Function to load product data
	 * @see EShopModel::_loadData()
	 */
	public function _loadData()
	{
		$db          = $this->getDbo();
		$query       = $db->getQuery(true);
		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(EShopHelper::getServerTimeFromGMTTime());

		$query->select(
			'a.*, b.product_name, b.product_alias, b.product_desc, b.product_short_desc, b.product_page_title, b.product_page_heading, b.product_alt_image, b.product_canoncial_link, b.product_price_text, b.meta_key, b.meta_desc, b.tab1_title, b.tab1_content, b.tab2_title, b.tab2_content, b.tab3_title, b.tab3_content, b.tab4_title, b.tab4_content, b.tab5_title, b.tab5_content'
		)
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('a.id = ' . intval($this->id))
			->where('a.published = 1')
			->where('b.language = "' . $this->language . '"');

		//Check viewable of customer groups
		$customerGroupId = (new EShopCustomer())->getCustomerGroupId();

		$query->where(
			'((a.product_customergroups = "") OR (a.product_customergroups IS NULL) OR (a.product_customergroups = "' . $customerGroupId . '") OR (a.product_customergroups LIKE "' . $customerGroupId . ',%") OR (a.product_customergroups LIKE "%,' . $customerGroupId . ',%") OR (a.product_customergroups LIKE "%,' . $customerGroupId . '"))'
		);

		$query->where(
			'(a.product_available_date = ' . $nullDate . ' OR a.product_available_date IS NULL OR a.product_available_date <= ' . $currentDate . ')'
		);
		$query->where('(a.product_end_date = ' . $nullDate . ' OR a.product_end_date IS NULL OR a.product_end_date >= ' . $currentDate . ')');

		$langCode = Factory::getLanguage()->getTag();
		$query->where(
			'((a.product_languages = "") OR (a.product_languages IS NULL) OR (a.product_languages = "' . $langCode . '") OR (a.product_languages LIKE "' . $langCode . ',%") OR (a.product_languages LIKE "%,' . $langCode . ',%") OR (a.product_languages LIKE "%,' . $langCode . '"))'
		);

		//Check out of stock
		if (EShopHelper::getConfigValue('hide_out_of_stock_products'))
		{
			$query->where('a.product_quantity > 0');
		}

		$db->setQuery($query);

		$this->data = $db->loadObject();
	}

	/**
	 *
	 * Function to write review
	 *
	 * @param   array  $data
	 *
	 * @return  array
	 */
	public function writeReview($data)
	{
		$user = Factory::getUser();
		$json = [];

		if ($data['author'] == '')
		{
			$json['error'] = Text::_('ESHOP_ERROR_YOUR_NAME');

			return $json;
		}

		if ($data['email'] == '')
		{
			$json['error'] = Text::_('ESHOP_ERROR_YOUR_EMAIL');

			return $json;
		}

		if ($data['review'] == '')
		{
			$json['error'] = Text::_('ESHOP_ERROR_YOUR_REVIEW');

			return $json;
		}

		if (!isset($data['rating']) || $data['rating'] == '')
		{
			$json['error'] = Text::_('ESHOP_ERROR_RATING') . $data['rating'];

			return $json;
		}

		if (EShopHelper::getConfigValue('enable_reviews_captcha'))
		{
			$app           = Factory::getApplication();
			$input         = $app->input;
			$captchaPlugin = $app->get('captcha') ?: 'recaptcha';
			$plugin		   = PluginHelper::getPlugin('captcha', $captchaPlugin);

			if ($plugin)
			{
				try
				{
					Captcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('dynamic_recaptcha_1', null, 'string'));
				}
				catch (Exception $e)
				{
					$json['error'] = Text::_('ESHOP_INVALID_CAPTCHA');

					return $json;
				}
			}
		}

		if (!$json)
		{
			$row = Table::getInstance('Eshop', 'Review');
			$row->bind($data);
			$row->id               = '';
			$row->product_id       = $data['product_id'];
			$row->customer_id      = $user->get('id') ?: 0;
			$row->published        = 0;
			$row->created_date     = gmdate('Y-m-d H:i:s');
			$row->created_by       = $user->get('id') ?: 0;
			$row->modified_date    = gmdate('Y-m-d H:i:s');
			$row->modified_by      = $user->get('id') ?: 0;
			$row->checked_out      = 0;
			$row->checked_out_time = '0000-00-00 00:00:00';

			if ($row->store())
			{
				$json['success'] = Text::_('ESHOP_REVIEW_SUBMITTED_SUCESSFULLY');
				//Send notification to admin
				if (EShopHelper::getConfigValue('product_alert_review', 1))
				{
					$sendFrom      = EShopHelper::getSendFrom();
					$fromName      = $sendFrom['from_name'];
					$fromEmail     = $sendFrom['from_email'];
					$reviewSubject = EShopHelper::getMessageValue('review_notification_email_subject');
					$reviewBody    = EShopHelper::getReviewNotificationEmailBody($data);
					$adminEmail    = EShopHelper::getConfigValue('email') ? trim(EShopHelper::getConfigValue('email')) : $fromEmail;
					$mailer        = Factory::getMailer();

					try
					{
						$mailer->sendMail($fromEmail, $fromName, $adminEmail, $reviewSubject, $reviewBody, 1);
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
					}

					//Send notification email to additional emails
					$alertEmails = EShopHelper::getConfigValue('product_alert_emails');
					$alertEmails = str_replace(' ', '', $alertEmails);
					$alertEmails = explode(',', $alertEmails);

					for ($i = 0; $n = count($alertEmails), $i < $n; $i++)
					{
						if ($alertEmails[$i] != '')
						{
							$mailer->clearAllRecipients();

							try
							{
								$mailer->sendMail($fromEmail, $fromName, $alertEmails[$i], $reviewSubject, $reviewBody, 1);
							}
							catch (Exception $e)
							{
								Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
							}
						}
					}
				}
			}
			else
			{
				$json['error'] = Text::_('ESHOP_REVIEW_SUBMITTED_FAILURED');
			}

			return $json;
		}
	}

	/**
	 *
	 * Function to process ask question
	 *
	 * @param   array  $data
	 */
	public function processAskQuestion($data)
	{
		//Store customer information
		$row = Table::getInstance('Eshop', 'Question');
		$row->bind($data);
		$row->id               = '';
		$row->product_id       = $data['product_id'];
		$row->created_date     = gmdate('Y-m-d H:i:s');
		$row->modified_date    = gmdate('Y-m-d H:i:s');
		
		$row->store();
		
		if (EShopHelper::getConfigValue('product_alert_ask_question', 1))
		{
			$sendFrom           = EShopHelper::getSendFrom();
			$fromName           = $sendFrom['from_name'];
			$fromEmail          = $sendFrom['from_email'];
			$product            = EShopHelper::getProduct($data['product_id']);
			$askQuestionSubject = EShopHelper::getMessageValue('ask_question_notification_email_subject');
			$askQuestionBody    = EShopHelper::getAskQuestionEmailBody($data, $product);
			$adminEmail         = EShopHelper::getConfigValue('email') ? trim(EShopHelper::getConfigValue('email')) : $fromEmail;
			$mailer             = Factory::getMailer();

			try
			{
				$mailer->sendMail($fromEmail, $fromName, $adminEmail, $askQuestionSubject, $askQuestionBody, 1, null, null, null, $data['email']);
			}
			catch (Exception $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			}

			//Send notification email to additional emails
			$alertEmails = EShopHelper::getConfigValue('product_alert_emails');
			$alertEmails = str_replace(' ', '', $alertEmails);
			$alertEmails = explode(',', $alertEmails);

			for ($i = 0; $n = count($alertEmails), $i < $n; $i++)
			{
				if ($alertEmails[$i] != '')
				{
					$mailer->clearAllRecipients();

					try
					{
						$mailer->sendMail($fromEmail, $fromName, $alertEmails[$i], $askQuestionSubject, $askQuestionBody, 1);
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
					}
				}
			}
		}
	}
	
	/**
	 *
	 * Function to process price match
	 *
	 * @param   array  $data
	 */
	public function processPriceMatch($data)
	{
		$sendFrom           = EShopHelper::getSendFrom();
		$fromName           = $sendFrom['from_name'];
		$fromEmail          = $sendFrom['from_email'];
		$product            = EShopHelper::getProduct($data['product_id']);
		$priceMatchSubject	= EShopHelper::getMessageValue('price_match_notification_email_subject');
		$priceMatchBody		= EShopHelper::getPriceMatchEmailBody($data);
		$adminEmail         = EShopHelper::getConfigValue('email') ? trim(EShopHelper::getConfigValue('email')) : $fromEmail;
		$mailer             = Factory::getMailer();
		
		try
		{
			$mailer->sendMail($fromEmail, $fromName, $adminEmail, $priceMatchSubject, $priceMatchBody, 1, null, null, null, $data['email']);
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}
		
		//Send notification email to additional emails
		$alertEmails = EShopHelper::getConfigValue('product_alert_emails');
		$alertEmails = str_replace(' ', '', $alertEmails);
		$alertEmails = explode(',', $alertEmails);
		
		for ($i = 0; $n = count($alertEmails), $i < $n; $i++)
		{
			if ($alertEmails[$i] != '')
			{
				$mailer->clearAllRecipients();
		
				try
				{
					$mailer->sendMail($fromEmail, $fromName, $alertEmails[$i], $priceMatchSubject, $priceMatchBody, 1);
				}
				catch (Exception $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
				}
			}
		}
	}

	/**
	 *
	 * Function to process email a friend
	 *
	 * @param   array  $data
	 */
	public function processEmailAFriend($data)
	{
		$sendFrom  = EShopHelper::getSendFrom();
		$fromName  = $sendFrom['from_name'];
		$fromEmail = $sendFrom['from_email'];

		$product                   = EShopHelper::getProduct($data['product_id']);
		$emailAFriendSubject       = EShopHelper::getMessageValue('email_a_friend_subject');
		$emailAFriendSubject       = str_replace('[STORE_NAME]', EShopHelper::getConfigValue('store_name'), $emailAFriendSubject);
		$emailAFriendSubject       = str_replace('[PRODUCT_NAME]', $product->product_name, $emailAFriendSubject);
		$emailAFriendBody          = EShopHelper::getMessageValue('email_a_friend');
		$replaces                  = [];
		$replaces['sender_name']   = $data['sender_name'];
		$replaces['sender_email']  = $data['sender_email'];
		$replaces['invitee_name']  = $data['invitee_name'];
		$replaces['invitee_email'] = $data['invitee_email'];
		$replaces['message']       = $data['message'];
		$replaces['product_link']  = Route::_(
			Uri::root() . EShopRoute::getProductRoute(
				$data['product_id'],
				EShopHelper::getProductCategory($data['product_id']),
				Factory::getLanguage()->getTag()
			)
		);

		foreach ($replaces as $key => $value)
		{
			$key              = strtoupper($key);
			$emailAFriendBody = str_replace("[$key]", $value, $emailAFriendBody);
		}

		$emailAFriendBody = EShopHelper::convertImgTags($emailAFriendBody);
		$mailer           = Factory::getMailer();

		try
		{
			$mailer->sendMail($fromEmail, $fromName, $data['invitee_email'], $emailAFriendSubject, $emailAFriendBody, 1);
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}
	}

	/**
	 *
	 * Function to process notify
	 *
	 * @param   array  $data
	 */
	public function processNotify($data)
	{
		if (!isset($data['product_id']) || !$data['product_id'])
		{
			echo Text::_('ESHOP_PRODUCT_NOTIFY_ERROR_MISS_PRODUCT');
		}
		elseif (!isset($data['notify_email']) && empty($data['notify_email']))
		{
			echo Text::_('ESHOP_PRODUCT_NOTIFY_ERROR_MISS_NOTIFY_EMAIL');
		}
		else
		{
			$row = Table::getInstance('Eshop', 'Notify');
			$row->load(['product_id' => $data['product_id'], 'notify_email' => $data['notify_email'], 'sent_email' => 0]);
			$this->id           = $data['product_id'];
			$this->data         = $this->getData();
			$data['sent_email'] = 0;
			$data['sent_date']  = gmdate('Y-m-d H:i:s');
			$data['language']   = Factory::getLanguage()->getTag();

			if ($row->id)
			{
				echo sprintf(Text::_('ESHOP_PRODUCT_NOTIFY_EXISTED'), $data['notify_email'], $this->data->product_name);
			}
			else
			{
				$row->bind($data);

				if ($row->store())
				{
					echo sprintf(Text::_('ESHOP_PRODUCT_NOTIFY_SUCCESSFULLY'), $data['notify_email'], $this->data->product_name);
				}
				else
				{
					echo Text::_('ESHOP_PRODUCT_NOTIFY_ERROR');
				}
			}
		}
	}

	/**
	 *
	 * Function to process download PDF
	 *
	 * @param   int  $productId
	 */
	public function downloadPDF($productId)
	{
		EShopHelper::generateProductPDF($productId);
		$product        = EShopHelper::getProduct($productId, Factory::getLanguage()->getTag());
		$filename       = 'product_' . $productId . '.pdf';
		$productPdfPath = JPATH_ROOT . '/media/com_eshop/pdf/' . $filename;
		while (@ob_end_clean())
		{
		}
		EShopHelper::processDownload($productPdfPath, $filename, true);
	}
}