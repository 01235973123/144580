<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use OSSolution\MembershipPro\Admin\Event\Subscription\AfterCancelRecurringSubscription;
use OSSolution\MembershipPro\Admin\Event\Subscription\MembershipExpire;

class OSMembershipHelperSubscription
{
	/**
	 * Calculate recurring subscription fees based on input parameter
	 *
	 * @param   OSMembershipTablePlan  $rowPlan  the object which contains information about the plan
	 * @param   MPFForm                $form     The form object which is used to calculate extra fee
	 * @param   array                  $data     The post data
	 * @param   MPFConfig              $config
	 * @param   string                 $paymentMethod
	 *
	 * @return array
	 */
	public static function calculateRecurringSubscriptionFee($rowPlan, $form, $data, $config, $paymentMethod = null)
	{
		$numberDecimals = (int) $config->get('decimals') ?: 2;

		$replaces = ['PLAN_PRICE' => $rowPlan->price, 'SETUP_FEE' => $rowPlan->setup_fee];

		// Note: Do not remove the line below. Although $feeAmount is not used, the method is still need to be called
		$feeAmount                 = $form->calculateFormFee($replaces);
		$noneTaxableFee            = max($replaces['none_taxable_fee'], 0);
		$trialFeeFromFeeFields     = $replaces['trial_fee'] ?? 0;
		$recurringFeeFromFeeFields = $replaces['recurring_fee'] ?? 0;

		$fees    = [];
		$coupon  = OSMembershipHelper::callOverridableHelperMethod(
			'Subscription',
			'getSubscriptionCoupon',
			[$rowPlan, $data, &$fees]
		);
		$taxRate = OSMembershipHelper::callOverridableHelperMethod(
			'Subscription',
			'calculateSubscriptionTaxRate',
			[$rowPlan, $data, &$fees]
		);

		$action = $data['act'];

		if ($action != 'renew')
		{
			$setupFee = $rowPlan->setup_fee;
		}
		else
		{
			$setupFee = 0;
		}

		$fees['setup_fee'] = $setupFee;

		if ($action == 'upgrade')
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true)
				->select('*')
				->from('#__osmembership_upgraderules')
				->where('id = ' . (int) $data['upgrade_option_id']);
			$db->setQuery($query);
			$upgradeOption = $db->loadObject();
			$regularAmount = $upgradeOption->price;

			if ($upgradeOption->upgrade_prorated == 2)
			{
				$regularAmount -= OSmembershipHelper::callOverridableHelperMethod(
					'Subscription',
					'calculateProratedUpgradePrice',
					[$upgradeOption, (int) Factory::getApplication()->getIdentity()->id]
				);
			}
		}
		else
		{
			$regularAmount = $rowPlan->price;
		}

		if ($regularAmount < 0)
		{
			$regularAmount = 0;
		}

		$regularDiscountAmount = 0;
		$regularTaxAmount      = 0;
		$trialDiscountAmount   = 0;
		$trialTaxAmount        = 0;
		$trialAmount           = 0;
		$trialDuration         = 0;
		$trialDurationUnit     = '';

		// Simple support for fixed payment day every month. In case fixed payment day is set for the plan, we will ignore any trial duration setting
		if ($rowPlan->payment_day > 0 && $rowPlan->subscription_length == 1 && $rowPlan->subscription_length_unit == 'M')
		{
			$todayDate         = Factory::getDate('now', Factory::getApplication()->get('offset'));
			$todayDay          = (int) $todayDate->format('d', true);
			$numberDaysInMonth = (int) $todayDate->format('t', true);
			$priceEachDay      = round($rowPlan->price / 30, 2);

			if ($todayDay == $rowPlan->payment_day)
			{
				$trialDuration     = 0;
				$trialAmount       = 0;
				$trialDurationUnit = 'D';
			}
			elseif ($todayDay > $rowPlan->payment_day)
			{
				$trialDuration     = $numberDaysInMonth - $todayDay + $rowPlan->payment_day;
				$trialAmount       = $trialDuration * $priceEachDay;
				$trialDurationUnit = 'D';
			}
			else
			{
				$trialDuration     = $rowPlan->payment_day - $todayDay;
				$trialAmount       = $trialDuration * $priceEachDay;
				$trialDurationUnit = 'D';
			}
		}
		elseif ($rowPlan->trial_duration || $setupFee > 0 || (!empty($coupon) && $coupon->apply_for == 1))
		{
			// There will be trial duration
			if ($rowPlan->trial_duration)
			{
				$trialAmount = $rowPlan->trial_amount + $setupFee + $trialFeeFromFeeFields;

				if ($trialAmount < 0)
				{
					$trialAmount = 0;
				}

				$trialDuration     = $rowPlan->trial_duration;
				$trialDurationUnit = $rowPlan->trial_duration_unit;
			}
			elseif ($setupFee > 0)
			{
				$trialAmount = $regularAmount + $setupFee + $trialFeeFromFeeFields;

				if ($trialAmount < 0)
				{
					$trialAmount = 0;
				}

				$trialDuration     = $rowPlan->subscription_length;
				$trialDurationUnit = $rowPlan->subscription_length_unit;
			}
			else
			{
				$trialAmount = $regularAmount + $recurringFeeFromFeeFields;

				if ($trialAmount < 0)
				{
					$trialAmount = 0;
				}

				$trialDuration     = $rowPlan->subscription_length;
				$trialDurationUnit = $rowPlan->subscription_length_unit;
			}
		}

		$regularAmount += $recurringFeeFromFeeFields;

		if ($coupon)
		{
			if ($coupon->coupon_type == 0)
			{
				$trialDiscountAmount = $trialAmount * $coupon->discount / 100;

				if ($coupon->apply_for == 0)
				{
					$regularDiscountAmount = $regularAmount * $coupon->discount / 100;
				}
			}
			else
			{
				$trialDiscountAmount = min($coupon->discount, $trialAmount);

				if ($coupon->apply_for == 0)
				{
					$regularDiscountAmount = min($coupon->discount, $regularAmount);
				}
			}
		}

		if ($taxRate > 0)
		{
			if ($rowPlan->trial_duration)
			{
				$trialTaxableAmount = $trialAmount - $trialDiscountAmount - $noneTaxableFee;
			}
			else
			{
				$trialTaxableAmount = $trialAmount - $trialDiscountAmount;
			}

			$trialTaxAmount = static::calculateTaxAmount($trialTaxableAmount, $taxRate);

			$regularTaxableAmount = $regularAmount - $regularDiscountAmount - $noneTaxableFee;

			$regularTaxAmount = static::calculateTaxAmount($regularTaxableAmount, $taxRate);
		}

		$trialGrossAmount   = $trialAmount - $trialDiscountAmount;
		$regularGrossAmount = $regularAmount - $regularDiscountAmount;

		if (!$config->setup_price_including_tax)
		{
			$trialGrossAmount   += $trialTaxAmount;
			$regularGrossAmount += $regularTaxAmount;
		}

		$fees['trial_payment_processing_fee']   = 0;
		$fees['regular_payment_processing_fee'] = 0;

		if ($trialGrossAmount > 0)
		{
			$trialPaymentProcessingFee            = OSMembershipHelper::callOverridableHelperMethod(
				'Subscription',
				'calculatePaymentProcessingFee',
				[$paymentMethod, $trialGrossAmount]
			);
			$fees['trial_payment_processing_fee'] = round($trialPaymentProcessingFee, $numberDecimals);
			$trialGrossAmount                     += $fees['trial_payment_processing_fee'];
		}

		if ($regularGrossAmount > 0)
		{
			$regularPaymentProcessingFee            = OSMembershipHelper::callOverridableHelperMethod(
				'Subscription',
				'calculatePaymentProcessingFee',
				[$paymentMethod, $regularGrossAmount]
			);
			$fees['regular_payment_processing_fee'] = round($regularPaymentProcessingFee, $numberDecimals);
			$regularGrossAmount                     += $fees['regular_payment_processing_fee'];
		}

		$fees['trial_amount']            = $trialAmount;
		$fees['trial_discount_amount']   = $trialDiscountAmount;
		$fees['trial_tax_amount']        = $trialTaxAmount;
		$fees['trial_gross_amount']      = $trialGrossAmount;
		$fees['regular_amount']          = $regularAmount;
		$fees['regular_discount_amount'] = $regularDiscountAmount;
		$fees['regular_tax_amount']      = $regularTaxAmount;
		$fees['regular_gross_amount']    = $regularGrossAmount;
		$fees['trial_duration']          = $trialDuration;
		$fees['trial_duration_unit']     = $trialDurationUnit;

		if ($fees['regular_gross_amount'] > 0)
		{
			$fees['show_payment_information'] = 1;
		}
		else
		{
			$fees['show_payment_information'] = 0;
		}

		$replaces = [];

		$replaces['REGULAR_AMOUNT']   = OSMembershipHelper::formatCurrency(
			$fees['regular_gross_amount'],
			$config,
			$rowPlan->currency_symbol
		);
		$replaces['SETUP_FEE']        = OSMembershipHelper::formatCurrency(
			$rowPlan->setup_fee,
			$config,
			$rowPlan->currency_symbol
		);
		$replaces['REGULAR_DURATION'] = OSMembershipHelper::callOverridableHelperMethod(
			'Subscription',
			'getDurationText',
			[$rowPlan->subscription_length, $rowPlan->subscription_length_unit, false]
		);

		$replaces['NUMBER_PAYMENTS'] = $rowPlan->number_payments;

		$replaces['TOTAL_PAYMENT_AMOUNT'] = OSMembershipHelper::formatCurrency(
			$rowPlan->number_payments * $fees['regular_gross_amount'],
			$config,
			$rowPlan->currency_symbol
		);

		if ($trialDuration > 0)
		{
			$replaces['TRIAL_DURATION'] = OSMembershipHelper::callOverridableHelperMethod(
				'Subscription',
				'getDurationText',
				[$trialDuration, $trialDurationUnit, false]
			);

			if ($fees['trial_gross_amount'] > 0)
			{
				$replaces['TRIAL_AMOUNT'] = OSMembershipHelper::formatCurrency(
					$fees['trial_gross_amount'],
					$config,
					$rowPlan->currency_symbol
				);

				if ($rowPlan->number_payments > 0)
				{
					$paymentTermsLanguageKey = 'OSM_TERMS_TRIAL_AMOUNT_NUMBER_PAYMENTS';
				}
				else
				{
					$paymentTermsLanguageKey = 'OSM_TERMS_TRIAL_AMOUNT';
				}
			}
			elseif ($rowPlan->number_payments > 0)
			{
				$paymentTermsLanguageKey = 'OSM_TERMS_FREE_TRIAL_NUMBER_PAYMENTS';
			}
			else
			{
				$paymentTermsLanguageKey = 'OSM_TERMS_FREE_TRIAL';
			}
		}
		elseif ($rowPlan->number_payments > 0)
		{
			$paymentTermsLanguageKey = 'OSM_TERMS_EACH_DURATION_NUMBER_PAYMENTS';
		}
		else
		{
			$paymentTermsLanguageKey = 'OSM_TERMS_EACH_DURATION';
		}

		if (Factory::getApplication()->getLanguage()->hasKey($paymentTermsLanguageKey . '_' . $rowPlan->id))
		{
			$paymentTermsLanguageKey = $paymentTermsLanguageKey . '_' . $rowPlan->id;
		}

		$paymentTerms = Text::_($paymentTermsLanguageKey);

		$paymentTerms = OSMembershipHelper::replaceCaseInsensitiveTags($paymentTerms, $replaces);

		$fees['payment_terms'] = $paymentTerms;

		$fees['fields_fee_values'] = $replaces['fields_fee_values'] ?? [];

		return $fees;
	}

	/**
	 * Calculate onetime fees based on input parameter
	 *
	 * @param   OSMembershipTablePlan  $rowPlan  the object which contains information about the plan
	 * @param   MPFForm                $form     The form object which is used to calculate extra fee
	 * @param   array                  $data     The post data
	 * @param   MPFConfig              $config
	 * @param   string                 $paymentMethod
	 *
	 * @return array
	 */
	public static function calculateOnetimeSubscriptionFee($rowPlan, $form, $data, $config, $paymentMethod = null)
	{
		$user = Factory::getApplication()->getIdentity();

		/* @var DatabaseDriver $db */
		$db             = Factory::getContainer()->get('db');
		$numberDecimals = (int) $config->get('decimals') ?: 2;

		$replaces       = ['PLAN_PRICE' => $rowPlan->price, 'SETUP_FEE' => $rowPlan->setup_fee];
		$feeAmount      = $form->calculateFormFee($replaces);
		$noneTaxableFee = max($replaces['none_taxable_fee'], 0);

		$fees    = [];
		$coupon  = OSMembershipHelper::callOverridableHelperMethod(
			'Subscription',
			'getSubscriptionCoupon',
			[$rowPlan, $data, &$fees]
		);
		$taxRate = OSMembershipHelper::callOverridableHelperMethod(
			'Subscription',
			'calculateSubscriptionTaxRate',
			[$rowPlan, $data, &$fees]
		);

		$action = $data['act'] ?? '';

		if ($action != 'renew')
		{
			$setupFee = $rowPlan->setup_fee;
		}
		else
		{
			$setupFee = 0;
		}

		$fees['payment_processing_fee'] = 0;
		$fees['setup_fee']              = $setupFee;

		$discountAmount = 0;
		$taxAmount      = 0;

		if ($action == 'renew')
		{
			$renewOptionId = (int) $data['renew_option_id'];

			if ($renewOptionId == OSM_DEFAULT_RENEW_OPTION_ID)
			{
				$amount = $rowPlan->price;
			}
			else
			{
				$query = $db->getQuery(true)
					->select('price')
					->from('#__osmembership_renewrates')
					->where('id = ' . $renewOptionId);
				$db->setQuery($query);
				$amount = $db->loadResult();
			}

			// Get renewal discount
			$renewalDiscount = static::getRenewalDiscount((int) $user->id, $rowPlan->id);

			if ($renewalDiscount)
			{
				if ($renewalDiscount->discount_type == 0)
				{
					$amount = round($amount * (1 - $renewalDiscount->discount_amount / 100), $numberDecimals);
				}
				else
				{
					$amount = $amount - $renewalDiscount->discount_amount;
				}
			}
		}
		elseif ($action == 'upgrade')
		{
			$query = $db->getQuery(true)
				->select('*')
				->from('#__osmembership_upgraderules')
				->where('id = ' . (int) $data['upgrade_option_id']);
			$db->setQuery($query);
			$upgradeOption = $db->loadObject();
			$amount        = $upgradeOption->price;

			if ($upgradeOption->upgrade_prorated == 2)
			{
				$amount -= OSmembershipHelper::callOverridableHelperMethod(
					'Subscription',
					'calculateProratedUpgradePrice',
					[$upgradeOption, (int) Factory::getApplication()->getIdentity()->id]
				);
			}
			elseif (in_array($upgradeOption->upgrade_prorated, [4, 5]))
			{
				$amount = OSmembershipHelper::callOverridableHelperMethod(
					'Subscription',
					'calculateProratedUpgradePrice',
					[$upgradeOption, (int) Factory::getApplication()->getIdentity()->id]
				);
			}
		}
		else
		{
			$amount = $rowPlan->price;

			if ((int) $rowPlan->expired_date && $rowPlan->prorated_signup_cost)
			{
				$expiredDate = Factory::getDate($rowPlan->expired_date, Factory::getApplication()->get('offset'));
				$date        = Factory::getDate('now', Factory::getApplication()->get('offset'));
				$expiredDate->setTime(23, 59, 59);
				$date->setTime(23, 59, 59);

				if ($rowPlan->subscription_length_unit == 'Y')
				{
					$subscriptionLengthYears = $rowPlan->subscription_length;
				}
				else
				{
					$subscriptionLengthYears = 1;
				}

				$expiredDate->setDate($date->year, $expiredDate->month, $expiredDate->day);

				if ($date > $expiredDate)
				{
					$expiredDate->modify("+ $subscriptionLengthYears years");
				}
				else
				{
					$expiredDate->modify('+ ' . ($subscriptionLengthYears - 1) . ' years');
				}

				$diff = $expiredDate->diff($date, true);

				if ($rowPlan->prorated_signup_cost == 1)
				{
					$numberDays = $subscriptionLengthYears * 365;
					$amount     = $amount * ($diff->days + 1) / $numberDays;
				}
				elseif ($rowPlan->prorated_signup_cost == 2)
				{
					$numberMonths = $subscriptionLengthYears * 12;
					$amount       = $amount * ($diff->y * 12 + $diff->m + 1) / $numberMonths;
				}
			}
		}

		$amount += $feeAmount;

		if ($amount <= 0)
		{
			$amount = 0;
		}

		if ($coupon)
		{
			if ($coupon->coupon_type == 0)
			{
				$discountAmount = ($amount + $setupFee) * $coupon->discount / 100;
			}
			else
			{
				$discountAmount = min($coupon->discount, $amount + $setupFee);
			}
		}

		if ($taxRate > 0)
		{
			$taxableAmount = $amount + $setupFee - $discountAmount - $noneTaxableFee;

			$taxAmount = static::calculateTaxAmount($taxableAmount, $taxRate);
		}

		$grossAmount = $setupFee + $amount - $discountAmount;

		// In case setup price without tax, we must add tax amount to gross amount
		if (!$config->setup_price_including_tax)
		{
			$grossAmount += $taxAmount;
		}

		if ($grossAmount > 0)
		{
			$paymentProcessingFee           = OSMembershipHelper::callOverridableHelperMethod(
				'Subscription',
				'calculatePaymentProcessingFee',
				[$paymentMethod, $grossAmount]
			);
			$fees['payment_processing_fee'] = round($paymentProcessingFee, $numberDecimals);
			$grossAmount                    += $fees['payment_processing_fee'];
		}

		$fees['amount']          = $amount;
		$fees['discount_amount'] = $discountAmount;
		$fees['tax_amount']      = $taxAmount;
		$fees['gross_amount']    = $grossAmount;

		if ($fees['gross_amount'] > 0)
		{
			$fees['show_payment_information'] = 1;
		}
		else
		{
			$fees['show_payment_information'] = 0;
		}

		$fees['payment_terms'] = '';

		$fees['fields_fee_values'] = $replaces['fields_fee_values'] ?? [];

		return $fees;
	}

	/**
	 * Method to get the coupon code use for the subscription
	 *
	 * @param   OSMembershipTablePlan  $rowPlan
	 * @param   array                  $data
	 * @param   array                  $fees
	 *
	 * @return mixed|null
	 */
	public static function getSubscriptionCoupon($rowPlan, $data, &$fees)
	{
		$user = Factory::getApplication()->getIdentity();

		/* @var DatabaseDriver $db */
		$db          = Factory::getContainer()->get('db');
		$nullDate    = $db->getNullDate();
		$query       = $db->getQuery(true);
		$couponValid = 1;
		$coupon      = null;
		$action      = $data['act'] ?? '';

		$couponCode = $data['coupon_code'] ?? '';

		if ($couponCode)
		{
			$currentDate = $db->quote(Factory::getDate('now', Factory::getApplication()->get('offset'))->toSql(true));
			$negPlanId   = -1 * $rowPlan->id;

			$query->clear()
				->select('*')
				->from('#__osmembership_coupons')
				->where('published = 1')
				->whereIn('access', $user->getAuthorisedViewLevels())
				->where('code = ' . $db->quote($couponCode))
				->where('(valid_from = ' . $db->quote($nullDate) . ' OR valid_from <= ' . $currentDate . ')')
				->where('(valid_to = ' . $db->quote($nullDate) . ' OR valid_to >= ' . $currentDate . ')')
				->where('(times = 0 OR times > used)')
				->where('(user_id = 0 OR user_id =' . $user->id . ')')
				->where(
					'(plan_id = 0 OR id IN (SELECT coupon_id FROM #__osmembership_coupon_plans WHERE plan_id = ' . $rowPlan->id . ' OR plan_id < 0))'
				)
				->where(
					'id NOT IN (SELECT coupon_id FROM #__osmembership_coupon_plans WHERE plan_id = ' . $negPlanId . ')'
				);

			if ($action)
			{
				$query->where('(subscription_type = "" OR subscription_type = ' . $db->quote($action) . ')');
			}

			$db->setQuery($query);
			$coupon = $db->loadObject();

			if (!$coupon)
			{
				$couponValid = 0;
			}
			elseif ($coupon && $coupon->max_usage_per_user > 0 && $user->id > 0)
			{
				// Check to see how many times this coupon was used by current user
				$query->clear()
					->select('COUNT(*)')
					->from('#__osmembership_subscribers')
					->where('user_id = ' . $user->id)
					->where('coupon_id = ' . $coupon->id)
					->where('(published IN (1,2) OR (published = 0 AND payment_method LIKE "%os_offline"))');
				$db->setQuery($query);
				$total = $db->loadResult();

				if ($total >= $coupon->max_usage_per_user)
				{
					$couponValid = 0;
					$coupon      = null;
				}
			}
			else
			{
				$fees['coupon_id'] = $coupon->id;
			}
		}

		$fees['coupon_valid'] = $couponValid;

		return $coupon;
	}

	/**
	 * Calculate tax rate for the subscription
	 *
	 * @param   OSMembershipTablePlan  $rowPlan
	 * @param   array                  $data
	 * @param   array                  $fees
	 *
	 * @return float
	 */
	public static function calculateSubscriptionTaxRate($rowPlan, $data, &$fees)
	{
		$config = OSMembershipHelper::getConfig();

		$country     = $data['country'] ?? $config->default_country;
		$state       = $data['state'] ?? '';
		$countryCode = OSMembershipHelper::getCountryCode($country);

		if ($countryCode == 'GR')
		{
			$countryCode = 'EL';
		}

		$vatNumberValid = 1;
		$vatNumber      = '';
		$viesRegistered = 0;

		// Calculate tax
		if (!empty($config->eu_vat_number_field) && isset($data[$config->eu_vat_number_field]))
		{
			$vatNumber = $data[$config->eu_vat_number_field];

			if ($vatNumber)
			{
				// If users doesn't enter the country code into the VAT Number, append the code
				$firstTwoCharacters = substr($vatNumber, 0, 2);

				if (strtoupper($firstTwoCharacters) != $countryCode)
				{
					$vatNumber = $countryCode . $vatNumber;
				}
			}
		}

		if ($vatNumber)
		{
			$valid = OSMembershipHelperEuvat::validateEUVATNumber($vatNumber);

			if ($valid)
			{
				$taxRate        = OSMembershipHelper::callOverridableHelperMethod(
					'Helper',
					'calculateTaxRate',
					[$rowPlan->id, $country, $state, 1]
				);
				$viesRegistered = 1;
			}
			else
			{
				$vatNumberValid = 0;
				$taxRate        = OSMembershipHelper::callOverridableHelperMethod(
					'Helper',
					'calculateTaxRate',
					[$rowPlan->id, $country, $state, 0]
				);
			}
		}
		else
		{
			$taxRate = OSMembershipHelper::callOverridableHelperMethod(
				'Helper',
				'calculateTaxRate',
				[$rowPlan->id, $country, $state, 0]
			);
		}

		$fees['tax_rate']        = $taxRate;
		$fees['country_code']    = $countryCode;
		$fees['vatnumber_valid'] = $vatNumberValid;
		$fees['vies_registered'] = $viesRegistered;

		if (!OSMembershipHelperEuvat::isEUCountry($countryCode)
			|| ($config->hide_vat_field_for_home_country && $country == $config->default_country))
		{
			$fees['show_vat_number_field'] = 0;
		}
		else
		{
			$fees['show_vat_number_field'] = 1;
		}

		return $taxRate;
	}

	/**
	 * Get payment processing fee for a payment method
	 *
	 * @param   string  $paymentMethod
	 *
	 * @return array
	 */
	public static function getPaymentProcessingFee($paymentMethod)
	{
		$paymentFeeAmount  = 0;
		$paymentFeePercent = 0;

		if ($paymentMethod)
		{
			$method            = OSMembershipHelperPayments::loadPaymentMethod($paymentMethod);
			$params            = new Registry($method->params);
			$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
			$paymentFeePercent = (float) $params->get('payment_fee_percent');
		}

		return [$paymentFeeAmount, $paymentFeePercent];
	}

	/**
	 * Calculate payment processing for a payment method base on the given amount
	 *
	 * @param   string  $paymentMethod
	 * @param   float   $amount
	 */
	public static function calculatePaymentProcessingFee($paymentMethod, $amount)
	{
		// Special case for PayPal payment processing fee calculation
		if (str_contains($paymentMethod, 'os_paypal'))
		{
			return static::calculatePayPalPaymentProcessingFee($paymentMethod, $amount);
		}

		if ($paymentMethod)
		{
			$method            = OSMembershipHelperPayments::loadPaymentMethod($paymentMethod);
			$params            = new Registry($method->params);
			$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
			$paymentFeePercent = (float) $params->get('payment_fee_percent');

			if ($paymentFeeAmount != 0 || $paymentFeePercent != 0)
			{
				return round($paymentFeeAmount + $amount * $paymentFeePercent / 100, 2);
			}
		}

		return 0;
	}

	/**
	 * PayPal payment method payment processing fee
	 *
	 * @param   string  $paymentMethod
	 * @param   float   $amount
	 *
	 * @return  float
	 */
	public static function calculatePayPalPaymentProcessingFee($paymentMethod, $amount)
	{
		$method            = OSMembershipHelperPayments::loadPaymentMethod($paymentMethod);
		$params            = new Registry($method->params);
		$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
		$paymentFeePercent = (float) $params->get('payment_fee_percent');

		if ($paymentFeeAmount != 0 || $paymentFeePercent != 0)
		{
			$paymentProcessingFee = ($amount + $paymentFeeAmount) / (1 - $paymentFeePercent / 100) - $amount;

			return round($paymentProcessingFee, 2);
		}

		return 0;
	}

	/**
	 * Check to see if a payment method has payment processing fee enabled
	 *
	 * @param   string  $paymentMethod
	 *
	 * @return bool
	 */
	public static function hasPaymentProcessingFee($paymentMethod)
	{
		$method = OSMembershipHelperPayments::loadPaymentMethod($paymentMethod);

		if ($method)
		{
			$params            = new Registry($method->params);
			$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
			$paymentFeePercent = (float) $params->get('payment_fee_percent');

			if ($paymentFeeAmount != 0 || $paymentFeePercent != 0)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get date interval string
	 *
	 * @param   int     $length
	 * @param   string  $unit
	 *
	 * @return string
	 */
	public static function getDateIntervalString($length, $unit)
	{
		if (in_array($unit, ['H', 'm']))
		{
			return 'PT' . $length . strtoupper($unit);
		}

		return 'P' . $length . $unit;
	}

	/**
	 * Method to get duration text from duration length and unit
	 *
	 * @param   string  $unit
	 * @param   int     $length
	 * @param   bool    $showLengthForOne
	 *
	 * @return string
	 */
	public static function getDurationText($length, $unit, $showLengthForOne = true)
	{
		$durations = [];

		if ($length > 1 || $showLengthForOne)
		{
			$durations[] = $length;
		}

		switch ($unit)
		{
			case 'm':
				$durations[] = ($length > 1 ? Text::_('OSM_MINUTES') : Text::_('OSM_MINUTE'));
				break;
			case 'H':
				$durations[] = ($length > 1 ? Text::_('OSM_HOURS') : Text::_('OSM_HOUR'));
				break;
			case 'W':
				$durations[] = ($length > 1 ? Text::_('OSM_WEEKS') : Text::_('OSM_WEEK'));
				break;
			case 'M':
				$durations[] = ($length > 1 ? Text::_('OSM_MONTHS') : Text::_('OSM_MONTH'));
				break;
			case 'Y':
				$durations[] = ($length > 1 ? Text::_('OSM_YEARS') : Text::_('OSM_YEAR'));
				break;
			default:
				$durations[] = ($length > 1 ? Text::_('OSM_DAYS') : Text::_('OSM_DAY'));
				break;
		}

		return implode(' ', $durations);
	}

	/**
	 * Method to check if there is coupon code available for plan
	 *
	 * @param   int     $planId
	 * @param   string  $action
	 *
	 * @return bool
	 */
	public static function isCouponAvailableForPlan($planId, $action = '')
	{
		$user = Factory::getApplication()->getIdentity();

		/* @var DatabaseDriver $db */
		$db          = Factory::getContainer()->get('db');
		$negPlanId   = -1 * $planId;
		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(Factory::getDate('now', Factory::getApplication()->get('offset'))->toSql(true));

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__osmembership_coupons')
			->where('published = 1')
			->whereIn('access', $user->getAuthorisedViewLevels())
			->where('(valid_from = ' . $nullDate . ' OR valid_from <= ' . $currentDate . ')')
			->where('(valid_to = ' . $nullDate . ' OR valid_to >= ' . $currentDate . ')')
			->where('(times = 0 OR times > used)')
			->where('(user_id = 0 OR user_id =' . (int) $user->id . ')')
			->where(
				'(plan_id = 0 OR id IN (SELECT coupon_id FROM #__osmembership_coupon_plans WHERE plan_id = ' . (int) $planId . ' OR plan_id < 0))'
			)
			->where(
				'id NOT IN (SELECT coupon_id FROM #__osmembership_coupon_plans WHERE plan_id = ' . $negPlanId . ')'
			);

		if ($action)
		{
			$query->where('(subscription_type = "" OR subscription_type = ' . $db->quote($action) . ')');
		}

		$db->setQuery($query);
		$total = (int) $db->loadResult();

		return $total > 0;
	}

	/**
	 * Get membership profile record of the given user
	 *
	 * @param   int  $userId
	 *
	 * @return object
	 */
	public static function getMembershipProfile($userId)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('a.*, b.username')
			->from('#__osmembership_subscribers AS a ')
			->leftJoin('#__users AS b ON a.user_id = b.id')
			->where('is_profile = 1')
			->where('user_id = ' . (int) $userId)
			->order('a.id DESC');
		$db->setQuery($query);

		return $db->loadObject();
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

		$userId = (int) $userId;
		$query->select('id')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $userId)
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
	 * Get active subscription plan ids of the given user
	 *
	 * @param   int    $userId
	 * @param   array  $excludes
	 *
	 * @return array
	 */
	public static function getActiveMembershipPlans($userId = 0, $excludes = [])
	{
		return array_merge([0], static::getActivePlanIdsForUser($userId, $excludes));
	}

	/**
	 * Get active plan ids of the given user. It does not include
	 *
	 * @param   int    $userId
	 * @param   array  $excludes
	 *
	 * @return array
	 */
	public static function getActivePlanIdsForUser($userId = 0, $excludes = [])
	{
		if (!$userId)
		{
			$userId = (int) Factory::getApplication()->getIdentity()->id;
		}

		if ($userId == 0)
		{
			return [];
		}

		$config      = OSMembershipHelper::getConfig();
		$gracePeriod = (int) $config->get('grace_period');

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('a.id')
			->from('#__osmembership_plans AS a')
			->innerJoin('#__osmembership_subscribers AS b ON a.id = b.plan_id')
			->where('b.user_id = ' . $userId)
			->where('b.published = 1');

		if ($gracePeriod > 0)
		{
			$gracePeriodUnit = $config->get('grace_period_unit', 'd');

			switch ($gracePeriodUnit)
			{
				case 'm':
					$query->where(
						'(a.lifetime_membership = 1 OR (from_date <= UTC_TIMESTAMP() AND DATE_ADD(b.to_date, INTERVAL ' . $gracePeriod . ' MINUTE) >= UTC_TIMESTAMP()))'
					);
					break;
				case 'h':
					$query->where(
						'(a.lifetime_membership = 1 OR (from_date <= UTC_TIMESTAMP() AND DATE_ADD(b.to_date, INTERVAL ' . $gracePeriod . ' HOUR) >= UTC_TIMESTAMP()))'
					);
					break;
				default:
					$query->where(
						'(a.lifetime_membership = 1 OR (from_date <= UTC_TIMESTAMP() AND DATE_ADD(b.to_date, INTERVAL ' . $gracePeriod . ' DAY) >= UTC_TIMESTAMP()))'
					);
					break;
			}
		}
		else
		{
			$query->where(
				'(a.lifetime_membership = 1 OR (from_date <= UTC_TIMESTAMP() AND to_date >= UTC_TIMESTAMP()))'
			);
		}

		if (count($excludes))
		{
			$query->whereNotIn('b.id', $excludes);
		}

		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Get active subscriptions for the given user
	 *
	 * @param   int  $userId
	 *
	 * @return array
	 */
	public static function getUserActiveSubscriptions($userId)
	{
		$config = OSMembershipHelper::getConfig();

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('b.*')
			->from('#__osmembership_plans AS a')
			->innerJoin('#__osmembership_subscribers AS b ON a.id = b.plan_id')
			->where('b.user_id = ' . (int) $userId)
			->where('b.published = 1')
			->order('b.to_date');

		$gracePeriod = (int) $config->get('grace_period');

		if ($gracePeriod > 0)
		{
			$gracePeriodUnit = $config->get('grace_period_unit', 'd');

			switch ($gracePeriodUnit)
			{
				case 'm':
					$query->where(
						'(a.lifetime_membership = 1 OR (b.from_date <= UTC_TIMESTAMP() AND DATE_ADD(b.to_date, INTERVAL ' . $gracePeriod . ' MINUTE) >= UTC_TIMESTAMP()))'
					);
					break;
				case 'h':
					$query->where(
						'(a.lifetime_membership = 1 OR (b.from_date <= UTC_TIMESTAMP() AND DATE_ADD(b.to_date, INTERVAL ' . $gracePeriod . ' HOUR) >= UTC_TIMESTAMP()))'
					);
					break;
				default:
					$query->where(
						'(a.lifetime_membership = 1 OR (b.from_date <= UTC_TIMESTAMP() AND DATE_ADD(b.to_date, INTERVAL ' . $gracePeriod . ' DAY) >= UTC_TIMESTAMP()))'
					);
					break;
			}
		}
		else
		{
			$query->where(
				'(a.lifetime_membership = 1 OR (b.from_date <= UTC_TIMESTAMP() AND b.to_date >= UTC_TIMESTAMP()))'
			);
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get information about subscription plans of a user
	 *
	 * @param   int  $userId
	 *
	 * @return array
	 */
	public static function getSubscriptionsForUser($userId)
	{
		$config = OSMembershipHelper::getConfig();

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $userId)
			->order('to_date');

		if (!$config->get('show_incomplete_payment_subscriptions', 1))
		{
			$query->where('(published != 0 OR gross_amount = 0 OR payment_method LIKE "os_offline%")');
		}

		$db->setQuery($query);
		$rows             = $db->loadObjectList();
		$rowSubscriptions = [];

		foreach ($rows as $row)
		{
			$rowSubscriptions[$row->plan_id][] = $row;
		}

		$planIds = array_keys($rowSubscriptions);

		if (count($planIds) == 0)
		{
			$planIds = [0];
		}

		$query->clear()
			->select('*')
			->from('#__osmembership_plans')
			->whereIn('id', $planIds)
			->where('published = 1');

		// Translate plan title
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		if ($fieldSuffix)
		{
			OSMembershipHelperDatabase::getMultilingualFields($query, ['title', 'description'], $fieldSuffix);
		}

		$db->setQuery($query);
		$rowPlans = $db->loadObjectList();

		foreach ($rowPlans as $rowPlan)
		{
			$isActive                = false;
			$isPending               = false;
			$isExpired               = false;
			$subscriptions           = $rowSubscriptions[$rowPlan->id];
			$lastActiveDate          = null;
			$subscriptionId          = null;
			$recurringCancelled      = 0;
			$cancelledSubscriptionId = '';
			$lastSubscriptionStatus  = 0;

			foreach ($subscriptions as $subscription)
			{
				if ($subscription->published == 1)
				{
					$isActive       = true;
					$lastActiveDate = $subscription->to_date;
				}
				elseif ($subscription->published == 0)
				{
					$isPending = true;
				}
				elseif ($subscription->published == 2)
				{
					$isExpired = true;
				}
				else
				{
					$lastSubscriptionStatus = $subscription->published;
				}

				if ($subscription->recurring_subscription_cancelled)
				{
					$recurringCancelled      = 1;
					$cancelledSubscriptionId = $subscription->subscription_id;
				}

				if ($subscription->subscription_id && $subscription->subscription_id != $cancelledSubscriptionId && !$subscription->recurring_subscription_cancelled && $subscription->payment_method)
				{
					$method = OSMembershipHelperPayments::getPaymentMethod($subscription->payment_method);

					if ($method && method_exists(
							$method,
							'supportCancelRecurringSubscription'
						) && $method->supportCancelRecurringSubscription())
					{
						$subscriptionId = $subscription->subscription_id;
					}

					$recurringCancelled = 0;
				}
			}

			$rowPlan->subscriptions          = $subscriptions;
			$rowPlan->subscription_id        = $subscriptionId;
			$rowPlan->subscription_from_date = $subscriptions[0]->from_date;
			$rowPlan->subscription_to_date   = $subscriptions[count($subscriptions) - 1]->to_date;
			$rowPlan->recurring_cancelled    = $recurringCancelled;

			if ($isActive)
			{
				$rowPlan->subscription_status  = 1;
				$rowPlan->subscription_to_date = $lastActiveDate;
			}
			elseif ($isPending)
			{
				$rowPlan->subscription_status = 0;
			}
			elseif ($isExpired)
			{
				$rowPlan->subscription_status = 2;
			}
			elseif ($lastSubscriptionStatus > 2)
			{
				// In case last subscription is cancelled, set it to cancelled status
				$rowPlan->subscription_status = $lastSubscriptionStatus;
			}
			else
			{
				$rowPlan->subscription_status = 3;
			}
		}

		return $rowPlans;
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
		$config = OSMembershipHelper::getConfig();

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('profile_id = ' . (int) $profileId)
			->order('to_date');

		if (!$config->get('show_incomplete_payment_subscriptions', 1))
		{
			$query->where('(published != 0 OR gross_amount = 0 OR payment_method LIKE "os_offline%")');
		}

		$db->setQuery($query);
		$rows             = $db->loadObjectList();
		$rowSubscriptions = [];

		foreach ($rows as $row)
		{
			$rowSubscriptions[$row->plan_id][] = $row;
		}

		$planIds = array_keys($rowSubscriptions);

		if (count($planIds) == 0)
		{
			$planIds = [0];
		}

		$query->clear()
			->select('*')
			->from('#__osmembership_plans')
			->whereIn('id', $planIds)
			->where('published = 1');

		// Translate plan title
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		if ($fieldSuffix)
		{
			OSMembershipHelperDatabase::getMultilingualFields($query, ['title', 'description'], $fieldSuffix);
		}

		$db->setQuery($query);
		$rowPlans = $db->loadObjectList();

		foreach ($rowPlans as $rowPlan)
		{
			$isActive                = false;
			$isPending               = false;
			$isExpired               = false;
			$subscriptions           = $rowSubscriptions[$rowPlan->id];
			$lastActiveDate          = null;
			$subscriptionId          = null;
			$recurringCancelled      = 0;
			$cancelledSubscriptionId = '';
			$lastSubscriptionStatus  = 0;

			foreach ($subscriptions as $subscription)
			{
				if ($subscription->published == 1)
				{
					$isActive       = true;
					$lastActiveDate = $subscription->to_date;
				}
				elseif ($subscription->published == 0)
				{
					$isPending = true;
				}
				elseif ($subscription->published == 2)
				{
					$isExpired = true;
				}
				else
				{
					$lastSubscriptionStatus = $subscription->published;
				}

				if ($subscription->recurring_subscription_cancelled)
				{
					$recurringCancelled      = 1;
					$cancelledSubscriptionId = $subscription->subscription_id;
				}

				if ($subscription->subscription_id && $subscription->subscription_id != $cancelledSubscriptionId && !$subscription->recurring_subscription_cancelled && $subscription->payment_method)
				{
					$method = OSMembershipHelperPayments::getPaymentMethod($subscription->payment_method);

					if ($method && method_exists(
							$method,
							'supportCancelRecurringSubscription'
						) && $method->supportCancelRecurringSubscription())
					{
						$subscriptionId = $subscription->subscription_id;
					}

					$recurringCancelled = 0;
				}
			}

			$rowPlan->subscriptions          = $subscriptions;
			$rowPlan->subscription_id        = $subscriptionId;
			$rowPlan->subscription_from_date = $subscriptions[0]->from_date;
			$rowPlan->subscription_to_date   = $subscriptions[count($subscriptions) - 1]->to_date;
			$rowPlan->recurring_cancelled    = $recurringCancelled;

			if ($isActive)
			{
				$rowPlan->subscription_status  = 1;
				$rowPlan->subscription_to_date = $lastActiveDate;
			}
			elseif ($isPending)
			{
				$rowPlan->subscription_status = 0;
			}
			elseif ($isExpired)
			{
				$rowPlan->subscription_status = 2;
			}
			elseif ($lastSubscriptionStatus > 2)
			{
				// In case last subscription is cancelled, set it to cancelled status
				$rowPlan->subscription_status = $lastSubscriptionStatus;
			}
			else
			{
				$rowPlan->subscription_status = 3;
			}
		}

		return $rowPlans;
	}

	/**
	 * Get information about subscription plans of a user. This is a copy of the above method
	 * as a workaround to prevent fatal error. See ticket #133098
	 *
	 * @param   int  $profileId
	 *
	 * @return array
	 */
	public static function getSubscriptionsForMembershipStatusModule($profileId)
	{
		$config = OSMembershipHelper::getConfig();

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('profile_id = ' . (int) $profileId)
			->order('to_date');

		if (!$config->get('show_incomplete_payment_subscriptions', 1))
		{
			$query->where('(published != 0 OR gross_amount = 0 OR payment_method LIKE "os_offline%")');
		}

		$db->setQuery($query);
		$rows             = $db->loadObjectList();
		$rowSubscriptions = [];

		foreach ($rows as $row)
		{
			$rowSubscriptions[$row->plan_id][] = $row;
		}

		$planIds = array_keys($rowSubscriptions);

		if (count($planIds) == 0)
		{
			$planIds = [0];
		}

		$query->clear()
			->select('*')
			->from('#__osmembership_plans')
			->whereIn('id', $planIds)
			->where('published = 1');

		// Translate plan title
		$fieldSuffix = OSMembershipHelper::getFieldSuffix();

		if ($fieldSuffix)
		{
			OSMembershipHelperDatabase::getMultilingualFields($query, ['title', 'description'], $fieldSuffix);
		}

		$db->setQuery($query);
		$rowPlans = $db->loadObjectList();

		foreach ($rowPlans as $rowPlan)
		{
			$isActive                = false;
			$isPending               = false;
			$isExpired               = false;
			$subscriptions           = $rowSubscriptions[$rowPlan->id];
			$lastActiveDate          = null;
			$subscriptionId          = null;
			$recurringCancelled      = 0;
			$cancelledSubscriptionId = '';
			$lastSubscriptionStatus  = 0;

			foreach ($subscriptions as $subscription)
			{
				if ($subscription->published == 1)
				{
					$isActive       = true;
					$lastActiveDate = $subscription->to_date;
				}
				elseif ($subscription->published == 0)
				{
					$isPending = true;
				}
				elseif ($subscription->published == 2)
				{
					$isExpired = true;
				}
				else
				{
					$lastSubscriptionStatus = $subscription->published;
				}

				if ($subscription->recurring_subscription_cancelled)
				{
					$recurringCancelled      = 1;
					$cancelledSubscriptionId = $subscription->subscription_id;
				}
			}

			$rowPlan->subscriptions          = $subscriptions;
			$rowPlan->subscription_id        = $subscriptionId;
			$rowPlan->subscription_from_date = $subscriptions[0]->from_date;
			$rowPlan->subscription_to_date   = $subscriptions[count($subscriptions) - 1]->to_date;
			$rowPlan->recurring_cancelled    = $recurringCancelled;

			if ($isActive)
			{
				$rowPlan->subscription_status  = 1;
				$rowPlan->subscription_to_date = $lastActiveDate;
			}
			elseif ($isPending)
			{
				$rowPlan->subscription_status = 0;
			}
			elseif ($isExpired)
			{
				$rowPlan->subscription_status = 2;
			}
			elseif ($lastSubscriptionStatus > 2)
			{
				// In case last subscription is cancelled, set it to cancelled status
				$rowPlan->subscription_status = $lastSubscriptionStatus;
			}
			else
			{
				$rowPlan->subscription_status = 3;
			}
		}

		return $rowPlans;
	}

	/**
	 * Method to check if the user is a group admin of the plan
	 *
	 * @param   int  $userId
	 * @param   int  $planId
	 *
	 * @return bool
	 */
	public static function isGroupAdmin(int $userId, int $planId)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__osmembership_subscribers')
			->where('plan_id = ' . $planId)
			->where('user_id = ' . $userId)
			->where('group_admin_id > 0');
		$db->setQuery($query);

		return $db->loadResult() <= 0;
	}

	/**
	 * Get upgrade rules available for the current user
	 *
	 * @param   int  $userId
	 * @param   int  $fromPlanId
	 *
	 * @return array
	 */
	public static function getUpgradeRules($userId = 0, $fromPlanId = 0)
	{
		$user = Factory::getApplication()->getIdentity();

		if (empty($userId))
		{
			$userId = (int) $user->id;
		}

		$config = OSMembershipHelper::getConfig();

		// Get list of plans which users can upgrade from

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('DISTINCT plan_id')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $userId);

		if ($config->get('allow_upgrade_from_expired_subscriptions'))
		{
			$query->where('published IN (1, 2)');
		}
		else
		{
			$query->where('published = 1');
		}

		if ($fromPlanId > 0)
		{
			$query->where('plan_id = ' . $fromPlanId);
		}

		$db->setQuery($query);
		$planIds = $db->loadColumn();

		if (!$planIds)
		{
			return [];
		}

		$activePlanIds = static::getActiveMembershipPlans($userId);

		$query->clear()
			->select('a.*')
			->from('#__osmembership_upgraderules AS a')
			->innerJoin('#__osmembership_plans AS b On a.from_plan_id = b.id')
			->whereIn('b.id', $planIds)
			->where('a.published = 1')
			->where('b.published = 1')
			->whereIn('b.access', $user->getAuthorisedViewLevels())
			->order('b.ordering')
			->order('a.ordering');

		if (count($activePlanIds) > 1)
		{
			$query->whereNotIn('to_plan_id', $activePlanIds);
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			// Adjust the upgrade price if price is pro-rated
			if (in_array($row->upgrade_prorated, [2, 4, 5]))
			{
				if ($row->upgrade_prorated == 2)
				{
					$row->price -= OSmembershipHelper::callOverridableHelperMethod(
						'Subscription',
						'calculateProratedUpgradePrice',
						[$row, $userId]
					);

					if ($row->price < 0)
					{
						$row->price = 0;
					}
				}
				else
				{
					$row->price = OSmembershipHelper::callOverridableHelperMethod(
						'Subscription',
						'calculateProratedUpgradePrice',
						[$row, $userId]
					);
				}
			}
		}

		return $rows;
	}

	/**
	 * Get Ids of the plans which is renewable
	 *
	 * @param   int  $userId
	 * @param   int  $forPlanId
	 *
	 * @return array
	 */
	public static function getRenewOptions($userId, $forPlanId = 0)
	{
		$config = OSMembershipHelper::getConfig();

		$activePlanIds    = static::getActiveMembershipPlans($userId);
		$exclusivePlanIds = static::getExclusivePlanIds($userId);

		// Get list of plans which the user has upgraded from

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('from_plan_id')
			->from('#__osmembership_subscribers AS a')
			->where('a.user_id = ' . $userId)
			->where('a.published IN (1, 2)')
			->where('from_plan_id > 0');
		$db->setQuery($query);
		$upgradedFromPlanIds = $db->loadColumn();

		$query->clear()
			->select('DISTINCT plan_id')
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $userId)
			->where('published IN (1, 2)');

		if ($forPlanId > 0)
		{
			$query->where('plan_id = ' . $forPlanId);
		}
		else
		{
			$query->where('plan_id IN (SELECT id FROM #__osmembership_plans WHERE published = 1)');
		}

		if (count($upgradedFromPlanIds))
		{
			$query->whereNotIn('plan_id', $upgradedFromPlanIds);
		}

		$db->setQuery($query);
		$planIds = $db->loadColumn();

		$todayDate = Factory::getDate();

		for ($i = 0, $n = count($planIds); $i < $n; $i++)
		{
			$planId = $planIds[$i];

			$query->clear()
				->select('*')
				->from('#__osmembership_plans')
				->where('id = ' . $planId);
			$db->setQuery($query);
			$row = $db->loadObject();

			if (!$row->enable_renewal || $row->published == 0)
			{
				unset($planIds[$i]);

				continue;
			}

			if (in_array($row->id, $exclusivePlanIds) && !in_array($row->id, $activePlanIds))
			{
				unset($planIds[$i]);

				continue;
			}

			// If this is a recurring plan and users still have active subscription, they can't renew
			if ($row->recurring_subscription && in_array($row->id, $activePlanIds))
			{
				// Check payment method, if it's not offline payment and have a valid subscription_id, then disable renewal
				$query->clear()
					->select('*')
					->from('#__osmembership_subscribers')
					->where('plan_id = ' . $row->id)
					->where('user_id = ' . $userId)
					->where('published = 1')
					->order('id DESC');
				$db->setQuery($query);
				$rowSubscription = $db->loadObject();

				if ($rowSubscription && $rowSubscription->subscription_id && !str_contains(
						$rowSubscription->payment_method,
						'os_offline'
					))
				{
					unset($planIds[$i]);

					continue;
				}
			}

			if ($config->number_days_before_renewal > 0)
			{
				//Get max date
				$query->clear()
					->select('MAX(to_date)')
					->from('#__osmembership_subscribers')
					->where(
						'user_id=' . (int) $userId . ' AND plan_id=' . $row->id . ' AND (published=1 OR (published = 0 AND payment_method LIKE "os_offline%"))'
					);
				$db->setQuery($query);
				$maxDate = $db->loadResult();

				if ($maxDate)
				{
					$expiredDate = Factory::getDate($maxDate);
					$diff        = $expiredDate->diff($todayDate);

					if (($expiredDate > $todayDate) && ($diff->days > $config->number_days_before_renewal))
					{
						unset($planIds[$i]);
					}
				}
			}
		}

		if (count($planIds))
		{
			$query->clear()
				->select('a.*')
				->from('#__osmembership_renewrates AS a')
				->innerJoin('#__osmembership_plans AS b ON a.plan_id = b.id')
				->whereIn('a.plan_id', $planIds)
				->order('b.ordering')
				->order('a.ordering');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$renewOptions = [];

			foreach ($rows as $row)
			{
				$renewalDiscountRule = static::getRenewalDiscount($userId, $row->plan_id);

				if ($renewalDiscountRule)
				{
					if ($renewalDiscountRule->discount_type == 0)
					{
						$row->price = round($row->price * (1 - $renewalDiscountRule->discount_amount / 100), 2);
					}
					else
					{
						$row->price = $row->price - $renewalDiscountRule->discount_amount;
					}

					if ($row->price < 0)
					{
						$row->price = 0;
					}
				}

				$renewOptions[$row->plan_id][] = $row;
			}

			return [
				$planIds,
				$renewOptions,
			];
		}

		return [
			[],
			[],
		];
	}

	/**
	 * Get max renewal discount rule
	 *
	 * @param $userId
	 * @param $planId
	 *
	 * @return stdClass
	 */
	public static function getRenewalDiscount($userId, $planId)
	{
		static $renewalDiscounts = [];

		if (!isset($renewalDiscounts[$planId]))
		{
			// Initial value
			$renewalDiscounts[$planId] = '';

			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true)
				->select('*')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . $userId)
				->where('plan_id = ' . $planId)
				->where('published IN (1, 2)')
				->order('to_date DESC');
			$db->setQuery($query);
			$row = $db->loadObject();

			if ($row)
			{
				$todayDate   = Factory::getDate();
				$expiredDate = Factory::getDate($row->to_date);
				$diff        = $todayDate->diff($expiredDate);

				// The subscription is active, we should check for early renewal discount
				if ($row->published == 1 && $expiredDate >= $todayDate)
				{
					$query->clear()
						->select('*')
						->from('#__osmembership_renewaldiscounts')
						->where('plan_id IN (0, ' . $planId . ')')
						->where('number_days <= ' . $diff->days)
						->where('published = 1')
						->order('discount_amount DESC');
					$db->setQuery($query, 0, 1);
					$renewalDiscounts[$planId] = $db->loadObject();
				}
				elseif ($todayDate > $expiredDate)
				{
					// This is expired subscription, we can check for late renewal discount
					$diff       = $todayDate->diff($expiredDate);
					$numberDays = -1 * $diff->days;

					// Get the renewal discount object with max discount amount
					$query->clear()
						->select('*')
						->from('#__osmembership_renewaldiscounts')
						->where('plan_id IN (0, ' . $planId . ')')
						->where('number_days <= ' . $numberDays)
						->where('published = 1')
						->order('discount_amount DESC');
					$db->setQuery($query, 0, 1);
					$renewalDiscounts[$planId] = $db->loadObject();
				}
			}
		}

		return $renewalDiscounts[$planId];
	}

	/**
	 * Get subscriptions information of the current user
	 *
	 * @return array
	 */
	public static function getUserSubscriptionsInfo()
	{
		static $subscriptions;

		if ($subscriptions === null)
		{
			$user = Factory::getApplication()->getIdentity();

			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);

			$now    = Factory::getDate();
			$nowSql = $db->quote($now->toSql());

			$query->select(
				'plan_id, MIN(from_date) AS active_from_date, MAX(DATEDIFF(' . $nowSql . ', from_date)) AS active_in_number_days'
			)
				->from('#__osmembership_subscribers AS a')
				->where('user_id = ' . (int) $user->id)
				->where('DATEDIFF(' . $nowSql . ', from_date) >= 0')
				->where('published IN (1, 2)')
				->group('plan_id');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$subscriptions = [];

			foreach ($rows as $row)
			{
				$subscriptions[$row->plan_id] = $row;
			}
		}

		return $subscriptions;
	}

	/**
	 * Get subscription status for a plan of the given user
	 *
	 * @param   int  $profileId
	 * @param   int  $planId
	 *
	 * @return int
	 */
	public static function getPlanSubscriptionStatusForUser($profileId, $planId)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('published')
			->from('#__osmembership_subscribers')
			->where('profile_id = ' . $profileId)
			->where('plan_id = ' . $planId)
			->order('to_date');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$isActive  = false;
		$isPending = false;
		$isExpired = false;

		foreach ($rows as $subscription)
		{
			if ($subscription->published == 1)
			{
				$isActive = true;
			}
			elseif ($subscription->published == 0)
			{
				$isPending = true;
			}
			elseif ($subscription->published == 2)
			{
				$isExpired = true;
			}
		}

		if ($isActive)
		{
			return 1;
		}
		elseif ($isPending)
		{
			return 0;
		}
		elseif ($isExpired)
		{
			return 2;
		}

		return 3;
	}

	/**
	 * Upgrade a membership
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	public static function processUpgradeMembership($row)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);

		$rowSubscription = new OSMembershipTableSubscriber($db);

		$query->select('from_plan_id')
			->from('#__osmembership_upgraderules')
			->where('id = ' . $row->upgrade_option_id);
		$db->setQuery($query);
		$planId            = (int) $db->loadResult();
		$row->from_plan_id = $planId;
		$row->store();

		$query->clear()
			->select('id')
			->from('#__osmembership_subscribers')
			->where('plan_id = ' . $planId)
			->where('profile_id = ' . $row->profile_id)
			->where('published = 1');
		$db->setQuery($query);
		$subscriberIds = $db->loadColumn();

		$mainSubscription = null;

		// Extra reminders
		$extraReminderSentFields = [
			'fourth_reminder_sent',
			'fifth_reminder_sent',
			'sixth_reminder_sent',
		];

		foreach ($subscriberIds as $subscriberId)
		{
			$rowSubscription->load($subscriberId);
			$rowSubscription->to_date   = date('Y-m-d H:i:s');
			$rowSubscription->published = 2;

			// Mark email reminders as sent
			$rowSubscription->first_reminder_sent  = 1;
			$rowSubscription->second_reminder_sent = 1;
			$rowSubscription->third_reminder_sent  = 1;

			foreach ($extraReminderSentFields as $field)
			{
				if (property_exists($rowSubscription, $field))
				{
					$rowSubscription->{$field} = 1;
				}
			}

			// Mark SMS reminders as sent
			$rowSubscription->first_sms_reminder_sent  = 1;
			$rowSubscription->second_sms_reminder_sent = 1;
			$rowSubscription->third_sms_reminder_sent  = 1;

			$rowSubscription->store();

			if ($rowSubscription->subscription_id && $rowSubscription->payment_method &&
				!$rowSubscription->recurring_subscription_cancelled)
			{
				$mainSubscription = $rowSubscription;
			}

			//Trigger plugins
			PluginHelper::importPlugin('osmembership');

			$event = new MembershipExpire(['row' => $rowSubscription]);

			Factory::getApplication()->triggerEvent($event->getName(), $event);
		}

		// Move group members to new plan
		$rowPlan = OSMembershipHelperDatabase::getPlan($row->plan_id);

		if (($rowPlan->number_group_members > 0 || $rowPlan->number_members_field) && $row->user_id > 0)
		{
			// Move all group members from old plan to new upgraded plan
			$query->clear()
				->update('#__osmembership_subscribers')
				->set('plan_id = ' . $row->plan_id)
				->set('from_date = ' . $db->quote($row->from_date))
				->set('to_date = ' . $db->quote($row->to_date))
				->where('group_admin_id = ' . $row->user_id)
				->where('plan_id = ' . $row->from_plan_id);
			$db->setQuery($query)
				->execute();
		}

		if ($mainSubscription)
		{
			try
			{
				JLoader::register(
					'OSMembershipModelRegister',
					JPATH_ROOT . '/components/com_osmembership/model/register.php'
				);

				/**@var OSMembershipModelRegister $model * */
				$model = new OSMembershipModelRegister();
				$model->cancelSubscription($mainSubscription);
			}
			catch (Exception $e)
			{
				// Ignore for now
			}
		}
	}

	/**
	 * Modify subscription duration based on the option which subscriber choose on form
	 *
	 * @param   Date   $date
	 * @param   array  $rowFields
	 * @param   array  $data
	 */
	public static function modifySubscriptionDuration($date, $rowFields, $data)
	{
		// Check to see whether there are any fields which can modify subscription end date
		foreach ($rowFields as $rowField)
		{
			if (!empty($rowField->modify_subscription_duration) && !empty($data[$rowField->name]))
			{
				$durationValues = explode("\r\n", $rowField->modify_subscription_duration);
				$values         = explode("\r\n", $rowField->values);
				$values         = array_map('trim', $values);
				$fieldValue     = $data[$rowField->name];

				$fieldValueIndex = array_search($fieldValue, $values);

				if ($fieldValueIndex !== false && !empty($durationValues[$fieldValueIndex]))
				{
					$modifyDurationString = $durationValues[$fieldValueIndex];

					if (!$date->modify($modifyDurationString))
					{
						Factory::getApplication()->enqueueMessage(
							sprintf('Modify duration string %s is invalid', $modifyDurationString),
							'warning'
						);
					}
				}
			}
		}
	}

	/**
	 * Get plan which the given user has subscribed for
	 *
	 * @param   int  $userId
	 *
	 * @return array
	 */
	public static function getSubscribedPlans($userId = 0)
	{
		if ($userId == 0)
		{
			$userId = (int) Factory::getApplication()->getIdentity()->id;
		}

		if ($userId > 0)
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);

			$query->select('DISTINCT plan_id')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . $userId)
				->where('published IN (1, 2)');
			$db->setQuery($query);

			return $db->loadColumn();
		}

		return [];
	}

	/**
	 * Get subscription from ID
	 *
	 * @param   string  $subscriptionId
	 *
	 * @return OSMembershipTableSubscriber
	 */
	public static function getSubscription($subscriptionId)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_subscribers')
			->where('subscription_id = ' . $db->quote($subscriptionId))
			->order('id');
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Calculate prorated upgrade price for an upgrade rule
	 *
	 * @param $row
	 * @param $userId
	 *
	 * @return float|int
	 */
	public static function calculateProratedUpgradePrice($row, $userId)
	{
		/* @var DatabaseDriver $db */
		$db        = Factory::getContainer()->get('db');
		$query     = $db->getQuery(true);
		$todayDate = Factory::getDate('now');

		$query->select('MAX(to_date)')
			->from('#__osmembership_subscribers')
			->where('published = 1')
			->where('plan_id = ' . (int) $row->from_plan_id)
			->where('user_id = ' . (int) $userId);
		$db->setQuery($query);
		$fromPlanSubscriptionEndDate = $db->loadResult();

		if ($fromPlanSubscriptionEndDate)
		{
			$fromPlanSubscriptionEndDate = Factory::getDate($fromPlanSubscriptionEndDate);

			if ($fromPlanSubscriptionEndDate > $todayDate)
			{
				$diff = $todayDate->diff($fromPlanSubscriptionEndDate);

				// Get price of the original plan
				if ($row->upgrade_prorated == 2)
				{
					$query->clear()
						->select('*')
						->from('#__osmembership_plans')
						->where('id = ' . (int) $row->from_plan_id);
					$db->setQuery($query);
					$fromPlan      = $db->loadObject();
					$fromPlanPrice = $fromPlan->price;
				}
				elseif ($row->upgrade_prorated == 4)
				{
					$query->clear()
						->select('*')
						->from('#__osmembership_plans')
						->where('id = ' . (int) $row->from_plan_id);
					$db->setQuery($query);
					$fromPlan      = $db->loadObject();
					$fromPlanPrice = $fromPlan->price;
				}
				elseif ($row->upgrade_prorated == 5)
				{
					$query->clear()
						->select('*')
						->from('#__osmembership_plans')
						->where('id = ' . (int) $row->to_plan_id);
					$db->setQuery($query);
					$fromPlan      = $db->loadObject();
					$fromPlanPrice = $fromPlan->price;
				}
				else
				{
					return 0;
				}

				switch ($fromPlan->subscription_length_unit)
				{
					case 'W':
						$numberDays = $fromPlan->subscription_length * 7;
						break;
					case 'M':
						$numberDays = $fromPlan->subscription_length * 30;
						break;
					case 'Y':
						$numberDays = $fromPlan->subscription_length * 365;
						break;
					default:
						$numberDays = $fromPlan->subscription_length;
						break;
				}

				return $fromPlanPrice * ($diff->days + 1) / $numberDays;
			}
		}

		return 0;
	}

	/**
	 * Get Ids of the plans which current users is not allowed to subscribe because exclusive rule
	 *
	 * @param   int  $userId
	 *
	 * @return array
	 */
	public static function getExclusivePlanIds($userId = 0)
	{
		if (!$userId)
		{
			$userId = Factory::getApplication()->getIdentity()->id;
		}

		$activePlanIds = static::getActiveMembershipPlans($userId);

		if (count($activePlanIds) > 1)
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true);
			$query->select('a.id')
				->from('#__osmembership_categories AS a')
				->innerJoin('#__osmembership_plans AS b ON a.id = b.category_id')
				->where('a.published = 1')
				->where('a.exclusive_plans = 1')
				->whereIn('b.id', $activePlanIds);
			$db->setQuery($query);
			$categoryIds = $db->loadColumn();

			if (count($categoryIds))
			{
				$query->clear()
					->select('id')
					->from('#__osmembership_plans')
					->whereIn('category_id', $categoryIds)
					->where('published = 1');
				$db->setQuery($query);

				return $db->loadColumn();
			}
		}

		return [];
	}

	/**
	 * Cancel recurring subscription
	 *
	 * @param   int  $id
	 */
	public static function cancelRecurringSubscription($id)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_subscribers')
			->where('id = ' . (int) $id);
		$db->setQuery($query);
		$row = $db->loadObject();

		if ($row)
		{
			// The recurring subscription already cancelled before, no need to process it further
			if ($row->recurring_subscription_cancelled)
			{
				return;
			}

			$query->clear()
				->update('#__osmembership_subscribers')
				->set('recurring_subscription_cancelled = 1')
				->where('id = ' . $row->id);
			$db->setQuery($query);
			$db->execute();

			$config = OSMembershipHelper::getConfig();
			OSMembershipHelperMail::sendSubscriptionCancelEmail($row, $config);

			// Mark all reminder emails as sent so that the system won't re-send these emails
			if ($row->user_id > 0 && $row->plan_id > 0)
			{
				$query->clear()
					->update('#__osmembership_subscribers')
					->set('first_reminder_sent = 1')
					->set('second_reminder_sent = 1')
					->set('third_reminder_sent = 1')
					->set('offline_recurring_email_sent = 1')
					->where('plan_id = ' . (int) $row->plan_id)
					->where('user_id = ' . (int) $row->user_id);

				$extraReminderSentFields = [
					'fourth_reminder_sent',
					'fifth_reminder_sent',
					'sixth_reminder_sent',
				];

				$subscribeTableFields = array_keys($db->getTableColumns('#__osmembership_subscribers'));

				foreach ($extraReminderSentFields as $extraField)
				{
					if (in_array($extraField, $subscribeTableFields))
					{
						$query->set($extraField . ' = 1');
					}
				}
				
				$db->setQuery($query);
				$db->execute();
			}

			PluginHelper::importPlugin('osmembership');

			$event = new AfterCancelRecurringSubscription(['row' => $row]);

			Factory::getApplication()->triggerEvent($event->getName(), $event);
		}
	}

	/**
	 * Synchronize profile data for a subscriber
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 * @param   array                        $fields
	 */
	public static function synchronizeProfileData($row, $fields)
	{
		if ($row->profile_id == 0)
		{
			return;
		}

		/* @var DatabaseDriver $db */
		$db         = Factory::getContainer()->get('db');
		$query      = $db->getQuery(true);
		$data       = [];
		$fieldNames = [];

		foreach ($fields as $field)
		{
			if ($field->synchronize_data)
			{
				$fieldNames[] = $field->name;
			}
		}

		// If no fields were configured to synchronize data, return early
		if (count($fieldNames) == 0)
		{
			return;
		}

		$query->select('id')
			->from('#__osmembership_subscribers')
			->where('profile_id = ' . (int) $row->profile_id)
			->where('id !=' . (int) $row->id);

		if ($row->user_id > 0)
		{
			$query->where('user_id = ' . $row->user_id);
		}

		$db->setQuery($query);
		$subscriptionIds = $db->loadColumn();

		if (count($subscriptionIds) == 0)
		{
			return;
		}

		if ($row->user_id && OSMembershipHelper::isUniquePlan($row->user_id))
		{
			$planId = $row->plan_id;
		}
		else
		{
			$planId = 0;
		}

		$rowFields = OSMembershipHelper::getProfileFields($planId);

		for ($i = 0, $n = count($rowFields); $i < $n; $i++)
		{
			$rowField = $rowFields[$i];

			if (!in_array($rowField->name, $fieldNames))
			{
				unset($rowFields[$i]);
				continue;
			}

			if ($rowField->is_core)
			{
				$data[$rowField->name] = $row->{$rowField->name};
				unset($rowFields[$i]);
			}
		}

		// Store core fields data
		foreach ($subscriptionIds as $subscriptionId)
		{
			$rowSubscription = new OSMembershipTableSubscriber($db);
			$rowSubscription->load($subscriptionId);
			$rowSubscription->bind($data);
			$rowSubscription->store();
		}

		$rowFields = array_values($rowFields);

		if (count($rowFields) == 0)
		{
			return;
		}

		$fieldIds = [];

		foreach ($rowFields as $rowField)
		{
			$fieldIds[] = $rowField->id;
		}

		// Delete old data
		$query->clear()
			->delete('#__osmembership_field_value')
			->whereIn('subscriber_id', $subscriptionIds)
			->whereIn('field_id', $fieldIds);
		$db->setQuery($query)
			->execute();

		foreach ($subscriptionIds as $subscriptionId)
		{
			$sql = ' INSERT INTO #__osmembership_field_value(subscriber_id, field_id, field_value)'
				. " SELECT $subscriptionId, field_id, field_value FROM #__osmembership_field_value WHERE subscriber_id = $row->id AND field_id IN (" . implode(
					',',
					$fieldIds
				) . ')';
			$db->setQuery($sql)
				->execute();
		}
	}

	/**
	 * Method to check and disable free trial for recurring plan if needed
	 *
	 * @param   OSMembershipTablePlan  $plan
	 *
	 * @return void
	 */
	public static function disableFreeTrialForPlan($plan)
	{
		// If this is a free trial plan and the current user subscribed for it before, we will disable free trial
		$user = Factory::getApplication()->getIdentity();

		if ($user->id && $plan->recurring_subscription && $plan->trial_duration > 0)
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . $user->id)
				->where('plan_id = ' . $plan->id)
				->where('published IN (1,2)');
			$db->setQuery($query);

			if ($db->loadResult())
			{
				$plan->trial_duration = 0;
			}
		}
	}

	/**
	 * Generate member card for the given user
	 *
	 * @param   OSMembershipTableSubscriber  $item
	 * @param   MPFConfig                    $config
	 *
	 * @return string
	 */
	public static function generateMemberCard($item, $config)
	{
		$options = [
			'PDF_PAGE_ORIENTATION' => $config->get('card_page_orientation'),
			'PDF_PAGE_FORMAT'      => $config->get('card_page_format'),
			'bg_image'             => $config->card_bg_image,
			'bg_left'              => $config->get('card_bg_left', ''),
			'bg_top'               => $config->get('card_bg_top', ''),
			'bg_width'             => $config->get('card_bg_width', 0),
			'bg_height'            => $config->get('card_bg_height', 0),
			'type'                 => 'member_card',
			'title'                => 'Member Card',
		];

		$replaces = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$item, $config]);

		$subscriptions = static::getSubscriptions($item->profile_id);

		$replaces['subscriptions'] = OSMembershipHelperHtml::loadCommonLayout(
			'emailtemplates/tmpl/subscriptions.php',
			['subscriptions' => $subscriptions, 'config' => $config]
		);
		$replaces['register_date'] = $replaces['created_date'];
		$replaces['name']          = trim($item->first_name . ' ' . $item->last_name);

		// Get latest subscription
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('published IN (1,2)')
			->where('user_id = ' . (int) $item->user_id)
			->order('id DESC');
		$db->setQuery($query);
		$latestSubscription = $db->loadObject();

		if (!$latestSubscription)
		{
			$latestReplaces = $replaces;
		}
		else
		{
			$latestReplaces = OSMembershipHelper::callOverridableHelperMethod(
				'Helper',
				'buildTags',
				[$latestSubscription, $config]
			);
		}

		$output = $config->card_layout;

		$output = OSMembershipHelper::replaceCaseInsensitiveTags($output, $replaces);

		foreach ($latestReplaces as $key => $value)
		{
			$key    = strtoupper('latest_' . $key);
			$output = str_ireplace("[$key]", $value, $output);
		}

		$output = OSMembershipHelperHtml::processConditionalText($output);

		$filePath = JPATH_ROOT . '/media/com_osmembership/membercards/' . $item->username . '.pdf';

		OSMembershipHelperPdf::generatePDFFile($output, $filePath, $options);

		return $filePath;
	}

	/**
	 * Generate member card for the given user
	 *
	 * @param   OSMembershipTableSubscriber  $item
	 * @param   MPFConfig                    $config
	 *
	 * @return string
	 */
	public static function generatePlanMemberCard($item, $config)
	{
		$plan        = OSMembershipHelperDatabase::getPlan($item->plan_id);
		$fieldSuffix = OSMembershipHelper::getFieldSuffix($item->language);

		$options = [
			'PDF_PAGE_ORIENTATION' => $config->get('card_page_orientation'),
			'PDF_PAGE_FORMAT'      => $config->get('card_page_format'),
			'bg_image'             => $plan->card_bg_image ?: $config->card_bg_image,
			'bg_left'              => $config->get('card_bg_left', ''),
			'bg_top'               => $config->get('card_bg_top', ''),
			'bg_width'             => $config->get('card_bg_width', 0),
			'bg_height'            => $config->get('card_bg_height', 0),
			'type'                 => 'member_card',
			'title'                => 'Member Card',
		];

		if ($config->get('card_page_format') === 'custom'
			&& $config->get('custom_page_format_width') > 0
			&& $config->get('custom_page_format_width') > 0)
		{
			$options['PDF_PAGE_FORMAT_CUSTOM'] = [
				$config->get('custom_page_format_width'),
				$config->get('custom_page_format_width')
			];
		}

		$replaces = OSMembershipHelper::callOverridableHelperMethod('Helper', 'buildTags', [$item, $config]);

		$replaces['register_date'] = $replaces['created_date'];
		$replaces['name']          = trim($item->first_name . ' ' . $item->last_name);

		if ($fieldSuffix && OSMembershipHelper::isValidMessage($plan->{'card_layout' . $fieldSuffix}))
		{
			$output = $plan->{'card_layout' . $fieldSuffix};
		}
		elseif (OSMembershipHelper::isValidMessage($plan->card_layout))
		{
			$output = $plan->card_layout;
		}
		else
		{
			$output = $config->card_layout;
		}

		$replaces['plan_subscription_from_date'] = HTMLHelper::_(
			'date',
			$item->plan_subscription_from_date,
			$config->date_format
		);
		$replaces['plan_subscription_to_date']   = HTMLHelper::_(
			'date',
			$item->plan_subscription_to_date,
			$config->date_format
		);

		$subscriptions             = static::getSubscriptions($item->profile_id);
		$replaces['subscriptions'] = OSMembershipHelperHtml::loadCommonLayout(
			'emailtemplates/tmpl/subscriptions.php',
			['subscriptions' => $subscriptions, 'config' => $config]
		);

		$output = OSMembershipHelper::replaceCaseInsensitiveTags($output, $replaces);

		$attributes = [];

		if ($config->qrcode_image_width > 0)
		{
			$attributes['width'] = $config->qrcode_image_width;
		}

		if ($config->qrcode_image_height > 0)
		{
			$attributes['height'] = $config->qrcode_image_height;
		}

		if (count($attributes) > 0)
		{
			$attributes = ' ' . http_build_query($attributes, '', '=');
		}
		else
		{
			$attributes = '';
		}

		// Generate QRCODE and have it displayed
		if (str_contains($output, '[QRCODE]') && $item->subscription_code)
		{
			$filePath = JPATH_ROOT . '/media/com_osmembership/qrcodes/' . $item->subscription_code . '.jpg';

			if (!file_exists($filePath))
			{
				$version = (int) $config->get('qrcode_size', 4) ?: QRCODE::VERSION_AUTO;

				$qrOptions = new QROptions([
					'version'    => $version,
					'outputType' => QRCode::OUTPUT_IMAGE_JPG,
				]);

				(new QRCode($qrOptions))->render($item->subscription_code, $filePath);
			}

			$imgTag = '<img src="media/com_osmembership/qrcodes/' . $item->subscription_code . '.jpg"' . $attributes . ' border="0" alt="QRCODE" />';
			$output = str_ireplace('[QRCODE]', $imgTag, $output);
		}

		// Generate Member Data QRCODE
		if (str_contains($output, '[MEMBER_DATA_QRCODE]'))
		{
			$filePath = JPATH_ROOT . '/media/com_osmembership/qrcodes/subscription_' . $item->id . '.jpg';

			$memberData = Text::_('OSM_MEMBER_DATA_QRCODE');

			$memberData = OSMembershipHelper::replaceCaseInsensitiveTags($memberData, $replaces);

			$version = (int) $config->get('qrcode_size', 4) ?: QRCODE::VERSION_AUTO;

			$qrOptions = new QROptions([
				'version'    => $version,
				'outputType' => QRCode::OUTPUT_IMAGE_JPG,
			]);

			(new QRCode($qrOptions))->render($memberData, $filePath);

			$imgTag = '<img src="media/com_osmembership/qrcodes/subscription_' . $item->id . '.jpg"' . $attributes . ' border="0" alt="QRCODE Member Data" />';
			$output = str_ireplace('[MEMBER_DATA_QRCODE]', $imgTag, $output);
		}

		$output = OSMembershipHelperHtml::processConditionalText($output);

		if (empty($item->username))
		{
			$user     = Factory::getUser($item->user_id);
			$username = $user->username;
		}
		else
		{
			$username = $item->username;
		}

		$filePath = JPATH_ROOT . '/media/com_osmembership/membercards/' . $username . '_' . $item->plan_id . '.pdf';

		OSMembershipHelperPdf::generatePDFFile($output, $filePath, $options);

		return $filePath;
	}

	/**
	 * Method to get allowed actions for a subscription plan
	 *
	 * @param   OSMembershipTablePlan  $item
	 *
	 * @return array
	 */
	public static function getAllowedActions($item)
	{
		if (!OSMembershipHelper::callOverridableHelperMethod('helper', 'canSubscribe', [$item]))
		{
			return [];
		}

		static $activePlanIds, $exclusivePlanIds;

		if ($activePlanIds === null)
		{
			$activePlanIds = static::getActiveMembershipPlans();
		}

		if ($exclusivePlanIds === null)
		{
			$exclusivePlanIds = static::getExclusivePlanIds();
		}

		$user = Factory::getApplication()->getIdentity();

		// If user does not have subscribe access, he is not allowed to subscribe or upgrade to the plan
		if (property_exists($item, 'subscribe_access') && !in_array(
				$item->subscribe_access,
				$user->getAuthorisedViewLevels()
			))
		{
			return [];
		}

		$config  = OSMembershipHelper::getConfig();
		$actions = [];

		// Only show subscribe/renew button if the plan is not in exclusive or if it's in exclusive plans, it needs to be current active plan
		if ((!in_array($item->id, $exclusivePlanIds) || in_array($item->id, $activePlanIds))
			&& (empty($item->upgrade_rules) || !$config->get('hide_signup_button_if_upgrade_available')))
		{
			if ($item->recurring_subscription && in_array($item->id, $activePlanIds))
			{
				// This is a recurring plan, so we only allow renew if the active subscription is offline payment

				/* @var DatabaseDriver $db */
				$db    = Factory::getContainer()->get('db');
				$query = $db->getQuery(true)
					->select('payment_method')
					->from('#__osmembership_subscribers')
					->where('plan_id = ' . $item->id)
					->where('user_id = ' . $user->id)
					->order('id DESC');
				$db->setQuery($query);

				if (str_contains($db->loadResult(), 'os_offline'))
				{
					$actions[] = 'subscribe';
				}
			}
			else
			{
				$actions[] = 'subscribe';
			}
		}

		if (!empty($item->upgrade_rules))
		{
			$actions[] = 'upgrade';
		}

		return $actions;
	}

	/**
	 * Method to check if the current user can download member card
	 *
	 * @param   int  $planId
	 *
	 * @return bool
	 */
	public static function canDownloadMemberCard($planId)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('a.*, b.username')
			->from('#__osmembership_subscribers AS a')
			->innerJoin('#__users AS b ON a.user_id = b.id')
			->where('a.user_id = ' . Factory::getApplication()->getIdentity()->id)
			->where('a.plan_id = ' . $planId)
			->where('published IN (1, 2)')
			->order('id');
		$db->setQuery($query);
		$item = $db->loadObject();

		return (bool) $item;
	}

	/**
	 * Method to get user groups related to the field value
	 *
	 * @param   stdClass  $rowField
	 * @param   mixed     $fieldValue
	 *
	 * @return array
	 */
	public static function getUserGroupsFromFieldValue($rowField, $fieldValue)
	{
		$groups = [];

		$fieldValues = explode("\r\n", $rowField->values);
		$groupIds    = explode("\r\n", $rowField->joomla_group_ids);

		if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
		{
			$selectedValues = json_decode($fieldValue);
		}
		else
		{
			$selectedValues = [$fieldValue];
		}

		$selectedValues = array_filter($selectedValues);

		foreach ($selectedValues as $selectedValue)
		{
			$valueIndex = array_search($selectedValue, $fieldValues);

			if ($valueIndex !== false)
			{
				$groupId = (int) $groupIds[$valueIndex];

				if ($groupId)
				{
					$groups[] = $groupId;
				}
			}
		}

		return $groups;
	}

	/**
	 * Method to accept privacy consent for a subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 */
	public static function acceptPrivacyConsent($row)
	{
		if (!$row->user_id)
		{
			return;
		}

		/* @var DatabaseDriver $db */
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

		Factory::getApplication()->getLanguage()->load(
			'plg_system_privacyconsent',
			JPATH_ADMINISTRATOR,
			$row->language
		);

		$params = new Registry($row->params);

		// Create the user note
		$privacyConsent = (object) [
			'user_id' => $row->user_id,
			'subject' => 'PLG_SYSTEM_PRIVACYCONSENT_SUBJECT',
			'body'    => Text::sprintf(
				'PLG_SYSTEM_PRIVACYCONSENT_BODY',
				$params->get('user_ip'),
				$params->get('user_agent')
			),
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

	/**
	 * Get IDs of plans which belong to a category which has grouping plans enabled
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return array
	 */
	public static function getGroupingPlans($row)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('a.id, a.grouping_plans')
			->from('#__osmembership_categories AS a')
			->innerJoin('#__osmembership_plans AS b ON a.id = b.category_id')
			->where('b.id = ' . $row->plan_id);
		$db->setQuery($query);
		$category = $db->loadObject();

		if ($category && $category->grouping_plans)
		{
			// Get all plans in the group
			$query->clear()
				->select('id')
				->from('#__osmembership_plans')
				->where('category_id = ' . $category->id)
				->where('published = 1');

			$db->setQuery($query);

			return $db->loadColumn();
		}

		return [];
	}

	/**
	 * Method to expired date of a plan for given user
	 *
	 * @param   int  $planId
	 * @param   int  $userId
	 *
	 * @return string
	 */
	public static function getPlanExpiredDate($planId, $userId = 0)
	{
		static $cache = [];

		if (!array_key_exists($planId, $cache))
		{
			/* @var DatabaseDriver $db */
			$db = Factory::getContainer()->get('db');

			if (!$userId)
			{
				$userId = Factory::getApplication()->getIdentity()->id;
			}

			// Get latest expiring date if available
			$query = $db->getQuery(true)
				->select('MAX(to_date)')
				->from('#__osmembership_subscribers')
				->where('user_id = ' . (int) $userId)
				->where('plan_id = ' . (int) $planId)
				->where('published = 1');
			$db->setQuery($query);
			$maxDate = $db->loadResult();

			// No active subscriptions, get latest expired date
			if (!$maxDate)
			{
				$query = $db->getQuery(true)
					->select('MAX(to_date)')
					->from('#__osmembership_subscribers')
					->where('user_id = ' . (int) $userId)
					->where('plan_id = ' . (int) $planId)
					->where('published = 2');
				$db->setQuery($query);
				$maxDate = $db->loadResult();
			}

			$cache[$planId] = $maxDate;
		}

		return $cache[$planId];
	}

	/**
	 * Method to check to see if we need to trigger active event for the subscription
	 *
	 * @param   OSMembershipTableSubscriber  $row
	 *
	 * @return bool
	 */
	public static function needToTriggerActiveEvent($row)
	{
		if (!PluginHelper::isEnabled('system', 'mptriggeractiveevent'))
		{
			return true;
		}

		$plan   = OSMembershipHelperDatabase::getPlan($row->plan_id);
		$params = new Registry($plan->params);

		if ($params->get('subscription_start_date_option', '0') == '0')
		{
			return true;
		}

		// We need to check to see if subscription start date is greater than current date
		$startDate   = Factory::getDate($row->from_date);
		$currentDate = Factory::getDate();

		if ($currentDate >= $startDate)
		{
			return true;
		}

		return false;
	}

	/**
	 * Get field value for a custom field from his last subscription
	 *
	 * @param   int  $userId
	 * @param   int  $fieldId
	 * @param   int  $planId
	 *
	 * @return string
	 */
	public static function getLastFieldValue($userId, $fieldId, $planId = 0)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('field_value')
			->from('#__osmembership_field_value AS a')
			->innerJoin('#__osmembership_subscribers AS b ON a.subscriber_id = b.id')
			->where('a.field_id = ' . $fieldId)
			->where('b.user_id = ' . $userId)
			->where('(b.published >= 1 OR b.payment_method LIKE "os_offline%")')
			->order('a.id DESC');

		if ($planId > 0)
		{
			$query->where('b.plan_id = ' . $planId);
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get field value for a custom field from his first subscription
	 *
	 * @param   int  $userId
	 * @param   int  $fieldId
	 * @param   int  $planId
	 *
	 * @return string
	 */
	public static function getFirstFieldValue($userId, $fieldId, $planId = 0)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('field_value')
			->from('#__osmembership_field_value AS a')
			->innerJoin('#__osmembership_subscribers AS b ON a.subscriber_id = b.id')
			->where('a.field_id = ' . $fieldId)
			->where('b.user_id = ' . $userId)
			->where('(b.published >= 1 OR b.payment_method LIKE "os_offline%")')
			->order('a.id');

		if ($planId > 0)
		{
			$query->where('b.plan_id = ' . $planId);
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get field value for a custom field from his last subscription
	 *
	 * @param   int     $userId
	 * @param   string  $fieldName
	 * @param   int     $planId
	 *
	 * @return string
	 */
	public static function getLastCoreFieldValue($userId, $fieldName, $planId = 0)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select($db->quoteName($fieldName))
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $userId)
			->where('(published >= 1 OR payment_method LIKE "os_offline%")')
			->order('id DESC');

		if ($planId > 0)
		{
			$query->where('plan_id = ' . $planId);
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get field value for a custom field from his first subscription
	 *
	 * @param   int     $userId
	 * @param   string  $fieldId
	 * @param   int     $planId
	 *
	 * @return string
	 */
	public static function getFirstCoreFieldValue($userId, $fieldName, $planId = 0)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select($db->quoteName($fieldName))
			->from('#__osmembership_subscribers')
			->where('user_id = ' . $userId)
			->where('(published >= 1 OR payment_method LIKE "os_offline%")')
			->order('id DESC');

		if ($planId > 0)
		{
			$query->where('plan_id = ' . $planId);
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to get data related to the subscription
	 *
	 * @param   int  $id
	 *
	 * @return array  array containing subscription data on success, false otherwise
	 */
	public static function getSubscriptionData($id)
	{
		$data = [];
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('*')
			->from('#__osmembership_subscribers')
			->where('id = ' . $id);
		$db->setQuery($query);
		$row = $db->loadObject();

		if (!$row)
		{
			return $data;
		}

		$rowFields = OSMembershipHelper::getProfileFields(
			$row->plan_id,
			true,
			$row->language,
			$row->act,
			null,
			$row->user_id
		);

		$query->clear()
			->select('a.name, b.field_value')
			->from('#__osmembership_fields AS a')
			->innerJoin('#__osmembership_field_value AS b ON a.id = b.field_id')
			->where('b.subscriber_id = ' . $id);
		$db->setQuery($query);
		$fieldValues = $db->loadObjectList('name');

		foreach ($rowFields as $rowField)
		{
			if ($rowField->is_core)
			{
				$data[$rowField->name] = $row->{$rowField->name};
			}
			elseif (isset($fieldValues[$rowField->name]))
			{
				$data[$rowField->name] = $fieldValues[$rowField->name]->field_value;
			}
		}

		return $data;
	}

	/**
	 * Get custom field value for a subscription
	 *
	 * @param   int  $id
	 * @param   int  $fieldId
	 *
	 * @return void
	 */
	public static function getFieldValue(int $id, int $fieldId): ?string
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)
			->select('field_value')
			->from('#__osmembership_field_value')
			->where('subscriber_id = ' . $id)
			->where('field_id = ' . $fieldId);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Calculate tax amount
	 *
	 * @param   float  $taxableAmount
	 * @param   float  $taxRate
	 *
	 * @return float
	 */
	protected static function calculateTaxAmount($taxableAmount, $taxRate)
	{
		$config         = OSMembershipHelper::getConfig();
		$numberDecimals = (int) $config->get('decimals') ?: 2;

		if ($config->setup_price_including_tax)
		{
			return round($taxableAmount - ($taxableAmount / (1 + ($taxRate / 100))), $numberDecimals);
		}

		return round($taxableAmount * $taxRate / 100, $numberDecimals);
	}
}
