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
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

class EShopModelQuote extends EShopModel
{
	/**
	 * Entity data
	 *
	 * @var array
	 */
	protected $quoteData = null;
	
	/**
	 *
	 * Total Data object array, each element is an price price in the quote
	 * @var object array
	 */
	protected $totalData = null;
	
	/**
	 *
	 * Final total price of the quote
	 * @var float
	 */
	protected $total = null;
	
	/**
	 *
	 * Taxes of all elements in the quote
	 * @var array
	 */
	protected $taxes = null;
	
	public function __construct($config = [])
	{
		parent::__construct();
	
		$this->quoteData	= null;
		$this->totalData	= null;
		$this->total		= null;
		$this->taxes		= null;
	}

	/**
	 *
	 * Function to get Quote Data
	 */
	public function getQuoteData()
	{
		if (!$this->quoteData)
		{
			$quote           = new EShopQuote();
			$this->quoteData = $quote->getQuoteData();
		}

		return $this->quoteData;
	}
	
	/**
	 *
	 * Function to get Costs
	 */
	public function getCosts()
	{
		$totalData = [];
		$total     = 0;
		$taxes     = [];
		$this->getSubTotalCosts($totalData, $total, $taxes);
		$this->getTaxesCosts($totalData, $total, $taxes);
		$this->getTotalCosts($totalData, $total, $taxes);
		$this->totalData = $totalData;
		$this->total     = $total;
		$this->taxes     = $taxes;
	}
	
	/**
	 *
	 * Function to get Sub Total Costs
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getSubTotalCosts(&$totalData, &$total, &$taxes)
	{
		$quote		 = new EShopQuote();
		$currency    = EShopCurrency::getInstance();
		$total       = $quote->getSubTotal();
		$totalData[] = [
			'name'  => 'sub_total',
			'title' => Text::_('ESHOP_SUB_TOTAL'),
			'text'  => $currency->format(max(0, $total)),
			'value' => max(0, $total),
		];
		$taxes       = $quote->getTaxes();
	}
	
	/**
	 *
	 * Function to get Taxes Costs
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getTaxesCosts(&$totalData, &$total, &$taxes)
	{
		$tax = new EShopTax(EShopHelper::getConfig());
		$tax->getCosts($totalData, $total, $taxes);
	}
	
	/**
	 *
	 * Function to get Total Costs
	 *
	 * @param   array  $totalData
	 * @param   float  $total
	 * @param   array  $taxes
	 */
	public function getTotalCosts(&$totalData, &$total, &$taxes)
	{
		$currency    = EShopCurrency::getInstance();
		$totalData[] = [
			'name'  => 'total',
			'title' => Text::_('ESHOP_TOTAL'),
			'text'  => $currency->format(max(0, $total)),
			'value' => max(0, $total),
		];
	}
	
	/**
	 *
	 * Function to get Total Data
	 */
	public function getTotalData()
	{
		return $this->totalData;
	}
	
	/**
	 *
	 * Function to get Total
	 */
	public function getTotal()
	{
		return $this->total;
	}
	
	/**
	 *
	 * Function to get Taxes
	 */
	public function getTaxes()
	{
		return $this->taxes;
	}

	/**
	 *
	 * Function to process quote
	 *
	 * @param   array  $data
	 *
	 * @return  array
	 */
	public function processQuote($data)
	{
		$quote                            = new EShopQuote();
		$user                             = Factory::getUser();
		$currency                         = EShopCurrency::getInstance();
		$data['currency_id']              = $currency->getCurrencyId();
		$data['currency_code']            = $currency->getCurrencyCode();
		$data['currency_exchanged_value'] = $currency->getExchangedValue();

		$json = [];

		// Validate products in the quote
		if (!$quote->hasProducts())
		{
			$json['return'] = Route::_(EShopRoute::getViewRoute('quote'));
		}

		if (!$json)
		{
			// Name validate
			if (EShopHelper::getConfigValue('quote_form_name_required', 1) && !strlen($data['name']))
			{
				$json['error']['name'] = Text::_('ESHOP_QUOTE_NAME_REQUIRED');
			}

			// Email validate
			if (EShopHelper::getConfigValue('quote_form_email_required', 1) && ((strlen($data['email']) > 96) || !preg_match(
						'/^[^\@]+@.*\.[a-z]{2,6}$/i',
						$data['email']
					)))
			{
				$json['error']['email'] = Text::_('ESHOP_QUOTE_EMAIL_REQUIRED');
			}

			// Company validate
			if (EShopHelper::getConfigValue('quote_form_company_required', 1) && !strlen($data['company']))
			{
				$json['error']['company'] = Text::_('ESHOP_QUOTE_COMPANY_REQUIRED');
			}

			// Telephone validate
			if (EShopHelper::getConfigValue('quote_form_telephone_required', 1) && !strlen($data['telephone']))
			{
				$json['error']['telephone'] = Text::_('ESHOP_QUOTE_TELEPHONE_REQUIRED');
			}

			// Address validate
			if (EShopHelper::getConfigValue('quote_form_address_required', 1) && !strlen($data['address']))
			{
				$json['error']['address'] = Text::_('ESHOP_QUOTE_ADDRESS_REQUIRED');
			}

			// City validate
			if (EShopHelper::getConfigValue('quote_form_city_required', 1) && !strlen($data['city']))
			{
				$json['error']['city'] = Text::_('ESHOP_QUOTE_CITY_REQUIRED');
			}

			// Postcode validate
			if (EShopHelper::getConfigValue('quote_form_postcode_required', 1) && !strlen($data['postcode']))
			{
				$json['error']['postcode'] = Text::_('ESHOP_QUOTE_POSTCODE_REQUIRED');
			}

			// Country validate
			if (EShopHelper::getConfigValue('quote_form_country_required', 1) && !$data['country_id'])
			{
				$json['error']['country_id'] = Text::_('ESHOP_QUOTE_COUNTRY_REQUIRED');
			}

			// State validate
			if (EShopHelper::getConfigValue('quote_form_state_required', 1) && !$data['zone_id'])
			{
				$json['error']['zone_id'] = Text::_('ESHOP_QUOTE_STATE_REQUIRED');
			}

			// Message validate
			if (EShopHelper::getConfigValue('quote_form_message_required', 1) && !strlen($data['message']))
			{
				$json['error']['message'] = Text::_('ESHOP_QUOTE_MESSAGE_REQUIRED');
			}

			if (EShopHelper::getConfigValue('enable_quote_captcha'))
			{
				$app           = Factory::getApplication();
				$input         = $app->input;
				$captchaPlugin = $app->get('captcha') ?: 'recaptcha';

				$plugin = PluginHelper::getPlugin('captcha', $captchaPlugin);

				if ($plugin)
				{
					try
					{
						Captcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('dynamic_recaptcha_1', '', 'string'));
					}
					catch (Exception $e)
					{
						$json['error']['captcha'] = Text::_('ESHOP_INVALID_CAPTCHA');
					}
				}
			}

			if (!$json)
			{
				$this->getCosts();
				
				// Store Quote
				$row = Table::getInstance('Eshop', 'Quote');
				$row->bind($data);

				$countryId = $data['country_id'] ?? 0;

				if ($countryId > 0)
				{
					$countryInfo = EShopHelper::getCountry($countryId);

					if (is_object($countryInfo))
					{
						$row->country_name = $countryInfo->country_name;
					}
					else
					{
						$row->country_name = '';
					}
				}

				$zoneId = $data['zone_id'] ?? 0;

				if ($zoneId > 0)
				{
					$zoneInfo = EShopHelper::getZone($zoneId);

					if (is_object($zoneInfo))
					{
						$row->zone_name = $zoneInfo->zone_name;
					}
					else
					{
						$row->zone_name = '';
					}
				}

				$row->total            = $this->total;
				$row->customer_id      = $user->get('id');
				$row->created_date     = gmdate('Y-m-d H:i:s');
				$row->modified_date    = gmdate('Y-m-d H:i:s');
				$row->modified_by      = 0;
				$row->checked_out      = 0;
				$row->checked_out_time = '0000-00-00 00:00:00';
				$row->store();
				$quoteRow = $row;
				$quoteId  = $row->id;
				$quote    = new EShopQuote();

				// Store Quote Products, Quote Options
				foreach ($quote->getQuoteData() as $product)
				{
					// Quote Products
					$row               = Table::getInstance('Eshop', 'Quoteproducts');
					$row->id           = '';
					$row->quote_id     = $quoteId;
					$row->product_id   = $product['product_id'];
					$row->product_name = $product['product_name'];
					$row->product_sku  = $product['product_sku'];
					$row->quantity     = $product['quantity'];
					$row->price        = $product['price'];
					$row->total_price  = $product['total_price'];
					$row->store();
					$quoteProductId = $row->id;

					// Quote Options
					foreach ($product['option_data'] as $option)
					{
						$row                          = Table::getInstance('Eshop', 'Quoteoptions');
						$row->id                      = '';
						$row->quote_id                = $quoteId;
						$row->quote_product_id        = $quoteProductId;
						$row->product_option_id       = $option['product_option_id'];
						$row->product_option_value_id = $option['product_option_value_id'];
						$row->option_name             = $option['option_name'];
						$row->option_value            = $option['option_value'];
						$row->option_type             = $option['option_type'];
						$row->sku                     = $option['sku'];
						$row->store();
					}
				}
				
				// Store Order Totals
				foreach ($this->totalData as $total)
				{
					$row           = Table::getInstance('Eshop', 'Quotetotals');
					$row->id       = '';
					$row->quote_id = $quoteId;
					$row->name     = $total['name'];
					$row->title    = $total['title'];
					$row->text     = $total['text'];
					$row->value    = $total['value'];
					$row->store();
				}

				//Send confirmation email here
				if (EShopHelper::getConfigValue('quote_alert_mail', 1))
				{
					EShopHelper::sendQuoteEmails($quoteRow);
				}

				//Newsetter integration
				if (isset($data['newsletter_interest']))
				{
					if (EShopHelper::getConfigValue('acymailing_integration') && is_file(
							JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php'
						))
					{
						$acyMailingIntegration = true;
					}
					else
					{
						$acyMailingIntegration = false;
					}

					$mailchimpIntegration = EShopHelper::getConfigValue('mailchimp_integration');

					foreach ($quote->getQuoteData() as $product)
					{
						//Store customer to AcyMailing
						if ($acyMailingIntegration)
						{
							$params  = new Registry($product['params']);
							$listIds = $params->get('acymailing_list_ids', '');
							if ($listIds != '')
							{
								require_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';
								$userClass = acymailing_get('class.subscriber');
								$subId     = $userClass->subid($row->email);
								if (!$subId)
								{
									$myUser         = new stdClass();
									$myUser->email  = $data['email'];
									$myUser->name   = $data['name'];
									$myUser->userid = '';
									$eventClass     = acymailing_get('class.subscriber');
									$subId          = $eventClass->save($myUser);
								}
								$listIds    = explode(',', $listIds);
								$newProduct = [];
								foreach ($listIds as $listId)
								{
									$newList             = [];
									$newList['status']   = 1;
									$newProduct[$listId] = $newList;
								}
								$userClass->saveSubscription($subId, $newProduct);
							}
						}

						//Store subscriber to MailChimp
						if ($mailchimpIntegration)
						{
							$params  = new Registry($product['params']);
							$listIds = $params->get('mailchimp_list_ids', '');

							if ($listIds != '')
							{
								$listIds = explode(',', $listIds);

								if (count($listIds))
								{
									require_once JPATH_SITE . '/components/com_eshop/helpers/MailChimp.php';

									$mailchimp = new MailChimp(EShopHelper::getConfigValue('api_key_mailchimp'));

									foreach ($listIds as $listId)
									{
										if ($listId)
										{
											$mailchimp->call('lists/subscribe', [
												'id'                => $listId,
												'email'             => ['email' => $data['email']],
												'merge_vars'        => ['NAME' => $data['name']],
												'double_optin'      => false,
												'update_existing'   => true,
												'replace_interests' => false,
												'send_welcome'      => false,
											]);
										}
									}
								}
							}
						}
					}
				}

				$json['success'] = Route::_(EShopRoute::getViewRoute('quote') . '&layout=complete');

				$quote->clear();
			}
		}

		return $json;
	}
}