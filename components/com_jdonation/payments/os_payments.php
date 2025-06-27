<?php

/**
 * @version		3.8
 * @package		Joomla
 * @subpackage	Joom Donation
 * @author  	Tuan Pham Ngoc
 * @copyright	Copyright (C) 2009 - 2025 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die ;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Component\ComponentHelper;

class os_jdpayments {	
	/**
	 * Get list of payment methods
	 *
	 * @return array
	 */
	public static function getPaymentMethods($campaignId = 0)
    {
		static $methods ;			
		if (!$methods)
		{
			define('JPAYMENT_METHODS_PATH', JPATH_ROOT.'/components/com_jdonation/payments/') ;
			$db = Factory::getContainer()->get('db');
			$extraSql = '';
			if($campaignId > 0)
            {
                $db->setQuery("Select payment_plugins from #__jd_campaigns where id = '$campaignId'");
                $payment_plugins    = trim($db->loadResult());


				if(substr($payment_plugins,0,1) == ",")
				{
					$payment_plugins = substr($payment_plugins,1);
				}
                if($payment_plugins != '')
                {
                    $extraSql = ' and id in ('.trim($payment_plugins).')';
                }
            }

			$sql = 'SELECT * FROM `#__jd_payment_plugins` WHERE published=1 '.$extraSql.' and `access` IN (' . implode(',', Factory::getApplication()->getIdentity()->getAuthorisedViewLevels()) . ') ORDER BY ordering' ;
			$db->setQuery($sql) ;
			$rows = $db->loadObjectList();
			foreach ($rows as $row)
			{
				$paymentPluginPath = JPAYMENT_METHODS_PATH.$row->name.'.php' ;
				if(file_exists($paymentPluginPath))
				{
					require_once JPAYMENT_METHODS_PATH.$row->name.'.php';
					$method = new $row->name(new Registry($row->params));
					$method->setTitle($row->title);
					$params = new Registry($row->params);
					$icon = $params->get('payment_icon','');
					$method->icon = $icon;
					$methods[] = $method ;					
				}					
			}
		}
		return $methods ;
	}

	public static function returnPaymentMethodMessage($methodName)
	{
		$db = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('`payment_description`')->from('`#__jd_payment_plugins`')->where('`name` = '.$db->quote($methodName));
		$db->setQuery($query);
		$payment_description = $db->loadResult();
		if (Multilanguage::isEnabled())
		{
			$defaultLang = ComponentHelper::getParams('com_languages')->get('site');
			$language = Factory::getApplication()->getLanguage();
			$languageTag = $language->getTag();
			if($languageTag != $defaultLang)
			{
				$suffix = '_' . strtolower(substr($languageTag, 0, 2));
			}
			$query->clear();
			$query->select('`payment_description' . $suffix . '`')->from('`#__jd_payment_plugins`')->where('`name` = ' . $db->quote($methodName));
			$db->setQuery($query);
			$payment_descriptionsuffix = $db->loadResult();
			if($payment_descriptionsuffix != "")
			{
				$payment_description = $payment_descriptionsuffix;
			}
		}
		return $payment_description;
	}

	public static function returnPaymentMethodTitle($methodName)
	{
		$db = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('`title`')->from('`#__jd_payment_plugins`')->where('`name` = '.$db->quote($methodName));
		$db->setQuery($query);
		return $db->loadResult();
	}
	/**
	 * Write the javascript objects to show the page
	 *
	 * @return string
	 */		
	public static function writeJavascriptObjects()
    {
		$methods =  os_jdpayments::getPaymentMethods();
		$jsString = " methods = new PaymentMethods();\n" ;			
		if (count($methods))
		{
			foreach ($methods as $method)
			{
				$jsString .= " method = new PaymentMethod('".$method->getName()."',".$method->getCreditCard().",".$method->getCardType().",".$method->getCardCvv().",".$method->getCardHolderName().", ".$method->getEnableRecurring().");\n" ;
				$jsString .= " methods.Add(method);\n";								
			}
		}
		echo $jsString ;
	}
	/**
	 * Load information about the payment method
	 *
	 * @param string $name Name of the payment method
	 */
	public static  function loadPaymentMethod($name)
    {
		$db = Factory::getContainer()->get('db');
		$sql = 'SELECT * FROM #__jd_payment_plugins WHERE name="'.$name.'"';
		$db->setQuery($sql) ;
		return $db->loadObject();
	}
	/**
	 * Get default payment gateway
	 *
	 * @return string
	 */
	public static function getDefautPaymentMethod($campaignId = 0)
    {
		$db = Factory::getContainer()->get('db');
        if($campaignId > 0)
        {
            $extraSql           = '';
            $db->setQuery("Select payment_plugins from #__jd_campaigns where id = '$campaignId'");
            $payment_plugins    = trim($db->loadResult());


			if(substr($payment_plugins,0,1) == ",")
			{
				$payment_plugins = substr($payment_plugins,1);
			}
            if($payment_plugins != '')
            {
                $extraSql       = ' and id in ('.trim($payment_plugins).')';
            }
        }
		$sql                    = 'SELECT name FROM #__jd_payment_plugins WHERE published=1 '.$extraSql.' and `access` IN (' . implode(',', Factory::getApplication()->getIdentity()->getAuthorisedViewLevels()) . ') ORDER BY ordering LIMIT 1';
		$db->setQuery($sql) ;
		return $db->loadResult();	
	}
	/**
	 * Get the payment method object based on it's name
	 *
	 * @param string $name
	 * @return object
	 */		
	public static function getPaymentMethod($name, $campaignId = 0)
    {
		$methods = os_jdpayments::getPaymentMethods($campaignId) ;
		foreach ($methods as $method)
		{
			if ($method->getName() == $name)
			{
				return $method ;		
			}
		}
		return null ;
	}

	/**
	 * Check to see whether the ideal payment plugin installed and activated
	 * @return boolean
	 */
	static public function sisowEnabled()
    {
		$db = Factory::getContainer()->get('db');
		$sql = 'SELECT COUNT(id) FROM #__jd_payment_plugins WHERE name="os_sisow" AND published=1';
		$db->setQuery($sql) ;
		$total = $db->loadResult() ;
		if ($total) {
			require_once JPATH_ROOT.'/components/com_jdonation/payments/sisow/sisow.cls5.php';
			return true ;
		} else {
			return false ;
		}
	}
	/**
	 * Get list of banks for ideal payment plugin
	 * @return array
	 */
	public static function getBankLists()
    {
		$sisowPlugin = self::loadPaymentMethod('os_sisow');		
		$params = new Registry($sisowPlugin->params) ;		
		$paymentType = $params->get('payment_type');
		$mode = $params->get('ideal_mode',0);
		if (!$paymentType)
		{
			?>
			<div class="control-group" id="tr_bank_lists" style="display:none;">
				<label class="control-label" for="card_holder_name">
					<?php echo Text::_('JD_SELECT_BANK'); ?><span class="required">*</span>
				</label>
				<div class="controls">
					<select name="issuer_id" id="issuer_id" class="input-large">
						<?php
						if ($mode)
						{
							?>
							<script type="text/javascript" src="https://www.sisow.nl/Sisow/iDeal/issuers2.js"></script>
						<?php
						}
						else
						{
							?>
							<option value="99">Sisow Bank (test)</option>
						<?php
						}
						?>
					</select>
				</div>
			</div>
			<?php
		}
	}
}
?>
