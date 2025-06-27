<?php

/**
 * @version        5.4.8
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Mail\MailHelper;
use DrewM\MailChimp\MailChimp;

class plgJDonationMailchimp extends CMSPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	function onAfterStoreDonor($row) {
        if (!MailHelper::isEmailAddress($row->email))
        {
            return;
        }
        require_once dirname(__FILE__) . '/api/MailChimp.php';
        $mailchimp = new MailChimp($this->params->get('api_key'));
		$config = DonationHelper::getConfig();
        $show_newsletter_subscription = DonationHelper::getConfigValue('show_newsletter_subscription');
        if($show_newsletter_subscription == 1 && $row->newsletter_subscription == 1) 
		{
            $subscriber = 1;
        }
		elseif($show_newsletter_subscription == 0)
		{
            $subscriber = 1;
        }
		else
		{
            $subscriber = 0;
        }

		if($subscriber == 1 && $config->mailchimp_list_ids != ''){
            $mailchimp_list_ids = $config->mailchimp_list_ids;
            $mailchimp_list_ids = explode(",", $mailchimp_list_ids);
            foreach ($mailchimp_list_ids as $listId)
            {
                if ($listId)
                {
					$data = [
						'skip_merge_validation' => true,
						'id'                    => $listId,
						'email_address'         => $row->email,
						'merge_fields'          => [],
						'status'                => 'subscribed',
						'update_existing'       => true,
					];

					if ($row->first_name)
					{
						$data['merge_fields']['FNAME'] = $row->first_name;
					}

					if ($row->last_name)
					{
						$data['merge_fields']['LNAME'] = $row->last_name;
					}

					if ($row->address && $row->city && $row->state && $row->zip)
					{
						$data['merge_fields']['ADDRESS'] = [
							'addr1'   => $row->address,
							'city'    => $row->city,
							'state'   => $row->state,
							'zip'     => $row->zip,
							'country' => $row->country,
						];
					}

					if ($row->phone)
					{
						$data['merge_fields']['PHONE'] = $row->phone;
					}

					$result = $mailchimp->post("lists/$listId/members", $data);

					/*
                    $mailchimp->call('lists/subscribe', array(
                        'id'                => $listId,
                        'email'             => array('email' => $row->email),
                        'merge_vars'        => array('FNAME' => $row->first_name, 'LNAME' => $row->last_name),
                        'double_optin'      => true,
                        'update_existing'   => true,
                        'replace_interests' => false,
                        'send_welcome'      => false,
                    ));
					*/
                }
            }
		}
	}
}	
