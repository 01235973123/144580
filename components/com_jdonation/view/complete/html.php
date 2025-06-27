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
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;


class DonationViewCompleteHtml extends OSFViewHtml
{
	public $hasModel = false;

	public function display()
	{
		$db		= Factory::getContainer()->get('db');
		$id		= (int) Factory::getApplication()->getSession()->get('id', 0);
		if($id == 0)
		{
			$id = (int) Factory::getApplication()->input->getInt('id',0);
		}

		$row = Table::getInstance('Donor', 'DonationTable');
		$row->load($id);
		if ($row->id)
		{
			if ($row->published == 0 && ($row->payment_method == 'os_ideal' || $row->payment_method == 'os_paygate'))
			{
				// Use online payment method and the payment is not success for some reason, we need to redirec to failure page
				$Itemid     = Factory::getApplication()->input->getInt('Itemid', 0);
				$failureUrl = Route::_('index.php?option=com_jdonation&view=failure&id=' . $row->id . '&Itemid=' . $Itemid, false, false);
				Factory::getApplication()->enqueueMessage(Text::_('JD_SOMETHINGWRONG_INPAYMENT'));
				Factory::getApplication()->redirect($failureUrl);
			}	
			
			$document = Factory::getApplication()->getDocument();
			$document->setTitle(Text::_('JD_COMPLETE'));

			$config      = DonationHelper::getConfig();

			//having Custom Donation complete page
			if($config->complete_url != "")
			{
				Factory::getApplication()->redirect($config->complete_url);
			}
		
			$fieldSuffix = DonationHelper::getFieldSuffix();
			if ($fieldSuffix)
			{
				DonationHelper::getMultilingualConfigData($config, $fieldSuffix, array('thanks_message_offline', 'thanks_message'));
			}
			if ($row->payment_method == 'os_offline')
			{
				//$message = $config->thanks_message_offline;
				$query = $db->getQuery(true);
				$query->select($db->quoteName('b.thanks_message_offline'))
					->from('#__jd_donors AS a')
					->innerJoin('#__jd_campaigns AS b ON a.campaign_id=b.id')
					->where('a.id=' . $id);
				$db->setQuery($query);
				$thanksMessage = $db->loadResult();
				if (strlen(trim(strip_tags($thanksMessage))))
				{
					$message = $thanksMessage;
				}
				else
				{
					$message = $config->thanks_message_offline;
				}
			}
			else
			{
				$query = $db->getQuery(true);
				$query->select($db->quoteName('b.thanks_message' . $fieldSuffix))
					->from('#__jd_donors AS a')
					->innerJoin('#__jd_campaigns AS b ON a.campaign_id=b.id')
					->where('a.id=' . $id);
				$db->setQuery($query);
				$thanksMessage = $db->loadResult();
				if (strlen(trim(strip_tags($thanksMessage))))
				{
					$message = $thanksMessage;
				}
				else
				{
					$message = $config->thanks_message;
				}
			}
			$replaces = DonationHelper::buildReplaceTags($row, $config);
			foreach ($replaces as $key => $value)
			{
				$key     = strtoupper($key);
				$message = str_replace("[$key]", $value, $message);
			}
			$this->row				= $row;
			$this->message			= $message;
			$bootstrapHelper		= new DonationHelperBootstrap($config->twitter_bootstrap_version);
			$this->bootstrapHelper  = $bootstrapHelper;
			$this->config			= $config;
			parent::display();
		}
		else
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('JD_INVALID_DONATION_RECORD'), 'warning');
			$app->redirect('index.php');
		}
	}
}
