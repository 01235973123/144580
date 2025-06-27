<?php
/**
 * @package            Joomla
 * @subpackage         Membership Pro
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2012 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

trait OSMembershipViewRegister
{
	/**
	 * The flag to determine whether captcha is shown on the view
	 *
	 * @var bool
	 */
	protected $showCaptcha = false;

	/**
	 * Name of captcha plugin used
	 *
	 * @var string
	 */
	protected $captchaPlugin;

	/**
	 * The string contain HTML code to render captcha
	 *
	 * @var string
	 */
	protected $captcha = null;

	/**
	 *  Load captcha for subscription form
	 *
	 * @param $config
	 * @param $user
	 */
	protected function loadCaptcha($config, $user)
	{
		$showCaptcha = 0;

		if ($config->enable_captcha == 1)
		{
			$captchaPlugin = Factory::getApplication()->get('captcha') ?: 'recaptcha';

			$this->captchaPlugin = $captchaPlugin;

			$plugin = PluginHelper::getPlugin('captcha', $captchaPlugin);

			if ($plugin)
			{
				$showCaptcha   = 1;
				$this->captcha = Captcha::getInstance($captchaPlugin)->display('recaptcha', 'recaptcha', 'required');
			}
			else
			{
				Factory::getApplication()->enqueueMessage(Text::_('OSM_CAPTCHA_NOT_ACTIVATED_IN_YOUR_SITE'), 'error');
			}
		}

		$this->showCaptcha = $showCaptcha;
	}

	/**
	 * Load assets (JS/CSS) needed for payment processing
	 *
	 * @return void
	 */
	protected function loadAssets()
	{
		// Add necessary javascript files
		OSMembershipHelper::addLangLinkForAjax();
		OSMembershipHelperJquery::loadjQuery();
		OSMembershipHelperHtml::addOverridableScript('media/com_osmembership/assets/js/paymentmethods.min.js');

		$customJSFile = JPATH_ROOT . '/media/com_osmembership/assets/js/custom.js';

		if (file_exists($customJSFile) && filesize($customJSFile) > 0)
		{
			Factory::getApplication()
				->getDocument()
				->getWebAssetManager()
				->registerAndUseScript('com_osmembership.custom', 'media/com_osmembership/assets/js/custom.js');
		}
	}
}
