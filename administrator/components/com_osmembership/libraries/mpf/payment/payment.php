<?php
/**
 * Part of the Ossolution Payment Package
 *
 * @copyright  Copyright (C) 2016 - 2016 Ossolution Team. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * Abstract Payment Class
 *
 * @since  1.0
 */
abstract class MPFPayment
{
	use MPFPaymentCommon;

	/**
	 * The name of payment method
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $name;

	/**
	 * The title of payment method
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	public $title;

	/**
	 * Payment method type
	 *
	 * @var int 0: off-site (redirect), 1: on-site (credit card)
	 */
	protected $type = 0;

	/***
	 * Payment mode
	 *
	 * @var bool
	 *
	 * @since 1.0
	 */
	protected $mode;

	/***
	 * Payment gateway URL
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Payment plugin parameters
	 *
	 * @var Registry
	 */
	protected $params;

	/**
	 * The parameters which will be passed to payment gateway for processing payment
	 *
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * Notification data send from payment gateway back to the payment plugin.
	 *
	 * @var array
	 */
	protected $notificationData = [];

	/**
	 * Payment success URL
	 *
	 * @var string
	 */
	protected $paymentSuccessUrl = null;

	/**
	 * Instantiate the payment object
	 *
	 * @param   Registry  $params
	 * @param   array     $config
	 */
	public function __construct($params, $config = [])
	{
		$this->name = get_class($this);

		$this->mode = $params->get('mode', 0);

		if (isset($config['type']))
		{
			$this->type = (int) $config['type'];
		}

		$this->params = $params;
	}

	/**
	 * Method to return payment method parameters
	 *
	 * @return Registry
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * This method need to be implemented by the payment plugin class. It needs to set url which users will be
	 * redirected to after a successful payment. The url is stored in paymentSuccessUrl property
	 *
	 * @param   int    $id
	 * @param   array  $data
	 *
	 * @return void
	 */
	protected function setPaymentSuccessUrl($id, $data = [])
	{
		$app    = Factory::getApplication();
		$task   = $app->getInput()->getCmd('task');
		$Itemid = $app->getInput()->getInt('Itemid', OSMembershipHelper::getItemid());

		if ($task == 'process')
		{
			$Itemid = OSMembershipHelperRoute::findView('payment', $Itemid);

			$this->paymentSuccessUrl = Route::_('index.php?option=com_osmembership&view=payment&layout=complete&Itemid=' . $Itemid, false);
		}
		else
		{
			$this->paymentSuccessUrl = Route::_(OSMembershipHelperRoute::getViewRoute('complete', $Itemid), false);
		}
	}

	/**
	 * Set data for a parameter
	 *
	 * @param   string  $name
	 * @param   string  $value
	 */
	protected function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	/**
	 * Get data for a parameter
	 *
	 * @param   string  $name
	 * @param   mixed   $default
	 *
	 * @return null
	 */
	protected function getParameter($name, $default = null)
	{
		return $this->parameters[$name] ?? $default;
	}

	/**
	 * This is the main method of the payment gateway. It get the data which users input and the calculated payment
	 * amount, pass to payment gateway for processing payment
	 *
	 * @param $row
	 * @param $data
	 */

	abstract public function processPayment($row, $data);

	/**
	 * Get name of the payment method
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get title of the payment method
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set title of the payment method
	 *
	 * @param $title String
	 */

	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * Method to check if this payment method is a CreditCard based payment method
	 *
	 * @return int
	 */
	public function getCreditCard()
	{
		return $this->type;
	}

	/***
	 * Render form which will redirect users to payment gateway for processing payment
	 *
	 * @param   string  $url  The payment gateway URL which users will be redirected to
	 * @param   array   $data
	 * @param   bool    $newWindow
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	protected function renderRedirectForm($url = null, $data = [], $newWindow = false)
	{
		// Load component css here
		$app = Factory::getApplication();
		$wa  = $app->getDocument()
			->getWebAssetManager();

		$config = OSMembershipHelper::getConfig();

		if ($config->load_twitter_bootstrap_in_frontend !== '0')
		{
			$wa->useStyle('bootstrap.css');
		}

		$customCssFile = JPATH_ROOT . '/media/com_osmembership/assets/css/custom.css';

		if (file_exists($customCssFile) && filesize($customCssFile) > 0)
		{
			$wa->registerAndUseStyle(
				'com_osmembership.custom',
				'media/com_osmembership/assets/css/custom.css',
				['version' => filemtime($customCssFile)]
			);
		}

		if (empty($url))
		{
			$url = $this->url;
		}

		if (empty($data))
		{
			$data = $this->parameters;
		}

		//Get redirect heading
		$language    = $app->getLanguage();
		$languageKey = 'OSM_WAIT_' . strtoupper(substr($this->name, 3));

		if ($language->hasKey($languageKey))
		{
			$redirectHeading = Text::_($languageKey);
		}
		else
		{
			$redirectHeading = Text::sprintf('OSM_REDIRECT_HEADING', $this->getTitle());
		}

		$layoutData = [
			'redirectHeading' => $redirectHeading,
			'url'             => $url,
			'newWindow'       => $newWindow,
			'data'            => $data,
		];

		echo OSMembershipHelperHtml::loadCommonLayout('common/tmpl/paymentredirect.php', $layoutData);
	}

	/***
	 * Log the notification data
	 *
	 * @param   string  $extraData  a string contain the extra data which you want to log
	 *
	 * @return void
	 */
	protected function logGatewayData($extraData = null)
	{
		if (!$this->params->get('ipn_log'))
		{
			return;
		}

		$text = '[' . gmdate('m/d/Y g:i A') . '] - ';
		$text .= 'Notification Data From : ' . $this->title . " \n";
		foreach ($this->notificationData as $key => $value)
		{
			$text .= "$key=$value, ";
		}

		$text .= $extraData;

		$ipnLogFile = JPATH_COMPONENT . '/ipn_' . $this->getName() . '.txt';
		$fp         = fopen($ipnLogFile, 'a');
		fwrite($fp, $text . "\n\n");
		fclose($fp);
	}
}
