<?php
/**
 * @version        5.4.5
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2018 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class plgJDonationAcyMailing extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	function onAfterStoreDonor($row) {			
		$db = & JFactory::getDBO() ;
        $show_newsletter_subscription = DonationHelper::getConfigValue('show_newsletter_subscription');
        if($show_newsletter_subscription == 1 && $row->newsletter_subscription == 1) {
            $subscriber = 1;
        }elseif($show_newsletter_subscription == 0){
            $subscriber = 1;
        }else{
            $subscriber = 0;
        }
		if (version_compare(JVERSION, '1.6.0', 'ge')) {
			$params = $this->params ;
		} else {
			$sql  = 'SELECT params FROM #__plugins WHERE folder = "jdonation" AND `element` = "acymailing"' ;
			$db->setQuery($sql) ;
			$params = $db->loadResult() ;
			$params = new JParameter($params) ;			
		}
		if($subscriber == 1){
            $sql = "SELECT COUNT(*) FROM #__acymailing_subscriber WHERE email='$row->email'";
            $db->setQuery($sql) ;
            $total = $db->loadResult();
            $time = time() ;
            if (!$total) {
                $name = $row->first_name . ' ' . $row->last_name;
                $ip = @$_SERVER['REMOTE_ADDR'];
                $user = &JFactory::getUser();
                $userId = $user->get('id');
                $sql = "INSERT INTO #__acymailing_subscriber(email, userid, name, created, 	confirmed, ip)
                VALUES('$row->email', '$userId', '$name', $time, 1, '$ip')
                ";
                $db->setQuery($sql);
                $db->execute();
                $subId = $db->insertId();
            }else{
                $db->setQuery("Select subid from #__acymailing_subscriber WHERE email='$row->email'");
                $subId = $db->loadResult();
            }
            if ($subId) {
                //Insert subscriber into list
                $listIds = trim($params->get('list_ids', ''));
                if ($listIds == '') {
                    $sql = 'SELECT listid FROM #__acymailing_list WHERE published=1';
                } else {
                    $sql = 'SELECT listid FROM #__acymailing_list WHERE listid IN (' . $listIds . ') AND published = "1"';
                }
                $db->setQuery($sql);
                $rows = $db->loadObjectList();
                if (count($rows)) {
                    foreach ($rows as $row) {
                        $listId = $row->listid;
                        //Check to see if users has subscribed for this list
                        $sql = 'SELECT COUNT(*) FROM #__acymailing_listsub WHERE listid=' . $listId . ' AND subid=' . $subId;
                        $db->setQuery($sql);
                        $total = $db->loadResult();
                        if (!$total) {
                            $sql = "INSERT INTO #__acymailing_listsub(listid, subid, subdate, unsubdate, `status`) VALUES($listId, $subId, $time, NULL, 1)";
                            $db->setQuery($sql);
                            $db->execute();
                        }
                    }
                }
            }
		}
	}
	
}	