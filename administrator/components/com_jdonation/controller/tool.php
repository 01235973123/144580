<?php
/**
 * @package            Joomla
 * @subpackage         Joom Donation
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2023 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class DonationControllerTool extends DonationController
{
	/**
	 * Reset the urls table
	 */
	public function reset_urls()
	{
		Factory::getDbo()->truncateTable('#__jd_urls');
		$this->setRedirect('index.php?option=com_jdonation&view=dashboard', Text::_('JD_URLS_HAVE_BEEN_RESET_SUCCESFULLY'));
	}

	/**
	 * Method to allow sharing language files for Events Booking
	 */
	public function share_translation()
	{
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('lang_code')
			->from('#__languages')
			->where('published = 1')
			->where('lang_code != "en-GB"')
			->order('ordering');
		$db->setQuery($query);
		$languages = $db->loadObjectList();

		if (count($languages))
		{
			$mailer   = Factory::getMailer();
			$jConfig  = Factory::getConfig();
			$mailFrom = $jConfig->get('mailfrom');
			$fromName = $jConfig->get('fromname');
			$mailer->setSender(array($mailFrom, $fromName));
			$mailer->addRecipient('thucdam84@gmail.com');
			$mailer->setSubject('Language Packages for Joom Donation shared by ' . Uri::root());
			$mailer->setBody('Dear JoomDonation \n. I am happy to share my language packages for Joom Donation.\n Enjoy!');
			foreach ($languages as $language)
			{
				$tag = $language->lang_code;
				if (file_exists(JPATH_ROOT . '/language/' . $tag . '/' . $tag . '.com_jdonation.ini'))
				{
					$mailer->addAttachment(JPATH_ROOT . '/language/' . $tag . '/' . $tag . '.com_jdonation.ini', $tag . '.com_jdonation.ini');
				}

				if (file_exists(JPATH_ADMINISTRATOR . '/language/' . $tag . '/' . $tag . '.com_jdonation.ini'))
				{
					echo JPATH_ADMINISTRATOR . '/language/' . $tag . '/' . $tag . '.com_jdonation.ini';
					$mailer->addAttachment(JPATH_ADMINISTRATOR . '/language/' . $tag . '/' . $tag . '.com_jdonation.ini', 'admin.' . $tag . '.com_jdonation.ini');
				}
			}
			$mailer->Send();
			$msg = 'Thanks so much for sharing your language files to Joom Donation Community';
		}
		else
		{
			$msg = 'Thanks so willing to share your language files to Joom Donation Community. However, you don"t have any none English langauge file to share';
		}

		$this->setRedirect('index.php?option=com_jdonation&view=dashboard', $msg);
	}

    /**
     * Remove Unpaid donation records
     */
    public function remove_unpaid()
    {
        $db = Factory::getContainer()->get('db');
        $query = $db->getQuery(true);
        $query->delete('#__jd_donors')->where('published = 0');
        $db->setQuery($query);
        $db->execute();
        Factory::getApplication()->enqueueMessage('Unpaid donation records are removed');
        Factory::getApplication()->redirect('index.php?option=com_jdonation');
    }
}
