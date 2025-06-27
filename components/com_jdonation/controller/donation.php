<?php
/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class DonationControllerDonation extends DonationController
{

	public function process()
	{
		$this->csrfProtection();
		$data = $this->input->getData();

		$data['campaign_id'] = (int) $data['campaign_id'];
		$data['x_card_num']  = trim(str_replace(" ", "", $data['x_card_num']));
		$expiry_date		 = $data['expiry_date'];
		$expiry_date_array	 = explode("/", $expiry_date);
		$data['exp_month']	 = $expiry_date_array[0];
		$data['exp_year']	 = '20'.$expiry_date_array[1];

		Factory::getApplication()->setUserState('com_jdonation.formdata', serialize($data));
		// Check captcha if captcha is enabled
		$config = DonationHelper::getConfig();
		$user = Factory::getApplication()->getIdentity();

		$this->antiSpam();

		$check_captcha = 0;

        if($config->enable_captcha)
        {
            if((int)$user->id == 0 && $config->enable_captcha_with_public_user == 1)
			{
                $check_captcha = 1;
			}
			elseif((int)$user->id > 0  && $config->enable_captcha_with_public_user == 1)
			{
				$check_captcha = 0;
            }
			elseif($config->enable_captcha_with_public_user == 0)
			{
                $check_captcha = 1;
            }
        }
		
		if ($check_captcha == 1)
		{
			//$captchaPlugin = Factory::getConfig()->get('captcha');
			if($config->use_jd_captcha == 1)
			{
				$captchaPlugin = 'jdcaptcha';
			}
			else
			{
				$captchaPlugin = Factory::getConfig()->get('captcha');
			}
			$plugin		   = PluginHelper::getPlugin('captcha', $captchaPlugin);
			if ($plugin)
			{
				try
				{
					$res   = Captcha::getInstance($captchaPlugin)->checkAnswer($this->input->post->get('recaptcha_response_field', '', 'string'));
					if (!$res)
					{
						$this->app->enqueueMessage(Text::_('JD_INVALID_CAPTCHA_ENTERED'), 'error');
						$donationPageUrl = $this->input->get('donation_page_url', '', 'none');
						if ($donationPageUrl)
						{
							//Redirect back to the article
							$this->app->redirect(base64_decode($donationPageUrl));
						}
						else
						{
							$this->input->set('view', 'donation');
							$this->display();
						}

						return false;
					}
				}
				catch (Exception $e)
				{
					//do the same with case !$res

					$this->app->enqueueMessage(Text::_('JD_INVALID_CAPTCHA_ENTERED'), 'error');
					$donationPageUrl = $this->input->get('donation_page_url', '', 'none');
					if ($donationPageUrl)
					{
						//Redirect back to the article
						$this->app->redirect(base64_decode($donationPageUrl));
					}
					else
					{
						$this->input->set('view', 'donation');
						$this->display();
					}
					return false;
				}
			}
		}
		$model  = $this->getModel();

		$amount = (float) $data['amount'];
		if ($amount <= 0)
		{
			$data['amount'] = $data['rd_amount'];
		}
		$model->processDonation($data);
	}

	public function processPayment()
	{
		
		$id		= $this->input->getInt('id');
		$itemId = $this->input->getInt('itemId');
		if($id > 0)
		{
			$model  = $this->getModel();
			$model->processPayment($id);
		}
		else
		{
			$needs = [];
			$needs[] = 'history';
			$itemId = DonationRoute::findItem($needs, $itemId);
			Factory::getApplication()->enqueueMessage(Text::_('JD_DONATION_RECORD_IS_NOT_EXISTS'));
			Factory::getApplication()->redirect(Route::_('index.php?option=com_jdonation&view=history&Itemid='.$itemId));
		}
	}

	/**
	 * Method to add some checks to prevent spams
	 *
	 */
	protected function antiSpam()
	{
		$config = DonationHelper::getConfig();

		if (trim($this->input->getString('jd_my_own_website_name')) != "")
		{
			throw new \Exception('The system detect that you are spammer. If you are, please contact administrator', 403);
		}

		if ((int) $config->min_form_time > 0)
		{
			$startTime = $this->input->getInt(DonationHelper::getHashedFieldName(), 0);

			if ((time() - $startTime) < (int) $config->min_form_time)
			{
				throw new \Exception('You submit data too fast and we think that youa are Spammer. If you are a real user, please process the form slower', 403);
			}
		}

		if ((int) $config->max_form_submission)
		{
			$session = Factory::getApplication()->getSession();

			$numberSubmissions = (int) $session->get('jd_number_submissions', 0) + 1;

			if ($numberSubmissions > (int) $config->max_form_submission)
			{
				throw new \Exception('You exceeded the number form submissions limit', 403);
			}
			else
			{
				$session->set('jd_number_submissions', $numberSubmissions);
			}
		}
	}

	/**
	 * Verify onetime donation
	 *
	 */
	public function payment_confirm()
	{
		$paymentMethod = $this->input->get('payment_method', '', 'none');
		$method        = os_jdpayments::getPaymentMethod($paymentMethod);
		if ($method)
		{
			$method->verifyPayment();
		}
	}

	/**
	 * Verify a recurring donation
	 */
	public function recurring_donation_confirm()
	{
		$paymentMethod = $this->input->get('payment_method', '', 'none');
		$method        = os_jdpayments::getPaymentMethod($paymentMethod);
		if ($method)
		{
			$method->verifyRecurringPayment();
		}
	}

    /**
     * Cancel recurring subscription
     *
     * @throws Exception
     */
    public function cancelrecurringdonation()
    {
        //$this->csrfProtection();
        $id             = $this->input->getInt('id', 0);
        $Itemid         = $this->input->getInt('Itemid', 0);

        $db             = Factory::getContainer()->get('db');
        $query          = $db->getQuery(true);
        $query->select('*')
            ->from('#__jd_donors')
            ->where('id = ' . $db->quote($id));
        $db->setQuery($query);
        $row            = $db->loadObject();

        if ($row && DonationHelper::canCancelRecurringDonation($row))
        {
            /**@var OSMembershipModelRegister $model * */
            $model = $this->getModel('Donation');
            $ret   = $model->cancelRecurringDonation($row);

            if ($ret)
            {
                Factory::getApplication()->getSession()->set('donor_id', $row->id);
                $this->app->redirect('index.php?option=com_jdonation&view=cancel&Itemid=' . $Itemid);
            }
            else
            {
                // Redirect back to profile page, the payment plugin should enque the reason of failed cancellation so that it could be displayed to end user
                $this->app->redirect('index.php?option=com_jdonation&view=history&Itemid=' . $Itemid);
            }
        }
        else
        {
            // Redirect back to user profile page
            $this->app->enqueueMessage(Text::_('JD_INVALID_DONATION_RECORD'));
            $this->app->redirect('index.php?option=com_jdonation&view=history&Itemid=' . $Itemid, 404);
        }
    }

	public function save()
    {
        $db         = Factory::getContainer()->get('db');
        $id         = Factory::getApplication()->input->getInt('id');
        $published  = Factory::getApplication()->input->getInt('published');
        $db->setQuery("Update #__jd_donors set published = '$published' where id = '$id'");
        $db->execute();
        Factory::getApplication()->enqueueMessage(Text::_('JD_DONOR_SAVED'));
        Factory::getApplication()->redirect(Route::_('index.php?option=com_jdonation&view=donationdetails&id='.$id.'&Itemid='.Factory::getApplication()->input->getInt('Itemid')));
    }

	public function summary()
	{
		$config				= DonationHelper::getConfig();
		$amount				= $this->input->getFloat('amount', 0);
		$paymentMethod		= $this->input->getString('payment', '');
		$payment_fee_pay	= $this->input->getInt('payment_fee_pay', 0);
		$curreny_code		= $this->input->getString('currency_code', '');

		if($curreny_code    == "" || $curreny_code == $config->currency)
		{
			$currency_code	= $config->currency_symbol;
		}

		$db					= Factory::getContainer()->get('db');
		$query				= $db->getQuery(true);
		$query->clear();
		$query->select('params')
				->from('#__jd_payment_plugins')
				->where('name=' . $db->quote($paymentMethod))
				->where('published = 1');
		$db->setQuery($query);
		$plugin = $db->loadObject();

		$params = new Registry($plugin->params);

		$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
		$paymentFeePercent = (float) $params->get('payment_fee_percent');

		if($config->pay_payment_gateway_fee)
		{
            $pay_payment_gateway_fee = $payment_fee_pay;
        }
		else
		{
            $pay_payment_gateway_fee = 1;
        }

		if (($paymentFeeAmount != 0 || $paymentFeePercent != 0) && $pay_payment_gateway_fee == 1)
		{
			$payment_plugin_fee		= round($paymentFeeAmount + $amount * $paymentFeePercent / 100, 2);
			$amount					= round($amount + $payment_plugin_fee, 2);
		}
		//no payment fee on Offline payment
        elseif ($config->convenience_fee && $pay_payment_gateway_fee == 1 && $paymentMethod != "os_offline")
        {
            $amount					= round($amount * (1 + $config->convenience_fee / 100), 2);
        }

		//showing donated amount include payment fee
        //echo $amount;

		?>
		<div class="donated-amount">
			<div class="donated-amount-label">
				<?php echo Text::_('JD_INCOME'); ?>
			</div>
			<div class="donated-amount-value">
				<?php
				echo DonationHelperHtml::formatAmount($config, $amount, $currency_code);
				?>
			</div>
		</div>
		<input type="hidden" name="gross_amount" id="gross_amount" value="<?php echo $amount; ?>" />
		<?php

		$this->app->close();
	}
}
