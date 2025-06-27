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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;

class os_offline extends os_payment
{
	/**
	 * Constructor functions, init some parameter
	 *
	 * @param   object  $params
	 */
	public function __construct($params)
	{
		$config = [
			'type'                  => 0,
			'show_card_type'        => false,
			'show_card_holder_name' => false,
		];

		parent::__construct($params, $config);
	}

	/**
	 * Process payment
	 *
	 */
	public function processPayment($data)
	{
		$app = Factory::getApplication();
		
		$row = Table::getInstance('Eshop', 'Order');
		$id  = $data['order_id'];
		$row->load($id);

		// Change order status based on the Order Status setting
		$row->order_status_id = $this->params->get('order_status', EShopHelper::getConfigValue('order_status_id'));

		$row->store();

		EShopHelper::completeOrder($row);
		PluginHelper::importPlugin('eshop');
		$app->triggerEvent('onAfterCompleteOrder', [$row]);
		
		$langLink	= EShopHelper::getLangLink();
		$Itemid = $app->input->getInt('Itemid', 0);
		
		if (!$Itemid)
		{
			$Itemid = EShopRoute::getDefaultItemId();
		}
		
		//Send confirmation email here
		if (EShopHelper::getConfigValue('order_alert_mail'))
		{
			EShopHelper::sendEmails($row);
		}
		
		$app->redirect(Route::_(EShopRoute::getViewRoute('checkout') . '&layout=complete' . $langLink . '&Itemid=' . $Itemid));
	}
}