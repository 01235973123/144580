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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerCustomer extends EShopAdminController
{
	public function country()
	{
		$json      = [];
		$input     = Factory::getApplication()->input;
		$countryId = $input->getInt('country_id');
		$zones     = EShopHelper::getCountryZones($countryId);
		if (count($zones))
		{
			$json['zones'] = $zones;
		}
		else
		{
			$json['zones'] = '';
		}
		echo json_encode($json);
		exit();
	}

	/**
	 *
	 * Function to save new address for customer
	 */
	public function saveNewAddress()
	{
		$input = new EshopRADInput();
		$post  = $input->post->getData(ESHOP_RAD_INPUT_ALLOWRAW);
		$model = $this->getModel('customer');
		$ret   = $model->saveNewAddress($post);

		if ($ret)
		{
			$msg = Text::_('ESHOP_SAVE_NEW_ADDRESS_SUCCESSFULLY');
		}
		else
		{
			$msg = Text::_('ESHOP_SAVE_NEW_ADDRESS_ERROR');
		}

		$this->setRedirect('index.php?option=com_eshop&task=customer.edit&cid[]=' . $post['cid'][0], $msg);
	}
}