<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha.hashcash
 *
 * @copyright   (C) 2018 Micahel Richey. <https://www.richeyweb.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Captcha\HashCash\Extension;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Form\Field\CaptchaField;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Utilities\IpHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Invisible reCAPTCHA Plugin.
 *
 * @since  3.9.0
 */
final class HashCash extends CMSPlugin
{

    protected $app;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.9.0
     */
    protected $autoloadLanguage = true;
    
    /**
     * Initialise the captcha
     *
     * @param   string  $id  The id of the field.
     *
     * @return  Boolean	True on success, false otherwise
     *
     * @since  2.5
     */
    public function onInit($id = 'dynamic_hashcash_1') 
    {
        $this->app = Factory::getApplication();

        if (!$this->app instanceof CMSWebApplicationInterface) {
            return false;
        }

        $options = $this->app->input->getCmd('option', '', 'cmd');
        $view = $this->app->input->getCmd('view', '', 'cmd');

        $doc = $this->app->getDocument();
        
        // $this->app->getLanguage()->load('plg_captcha_hashcash', JPATH_ADMINISTRATOR);
        $session = $this->app->getSession();

        $debug = $this->app->get('debug', 0)?'':'.min';
        $doc->addScript(Uri::root(true) . '/media/plg_captcha_hashcash/js/hashcash' . $debug . '.js', ['version' => 'auto']);
        if(
            $this->params->get('knownforms', 1, 'INTEGER') &&
            strlen($options) && strlen($view) && 
            file_exists(JPATH_ROOT.'/media/plg_captcha_hashcash/js/'.$options.'.'.$view.$debug.'.js')
        ) {
            $doc->addScript(Uri::root(true) . '/media/plg_captcha_hashcash/js/'.$options.'.'.$view.$debug.'.js', ['version' => 'auto']);
            Text::script('PLG_CAPTCHA_HASHCASH_LOADING');
        }

        $vars = array();
        $vars['container'] = $id;
        $vars['remote_addr'] = IpHelper::getIp();
        $vars['level'] = (int) $this->params->get('difficulty', 3);
        $vars['request_time'] = $session->get('request_time', time(), 'plg_captcha_hashcash');
        $vars['delaystart'] = (bool)$this->params->get('delaystart', 1, 'INTEGER');
        if(!$vars['delaystart']) {
            $vars['punish'] = false;
            $vars['trigger'] = false;
            $vars['cdp'] = false;
            $vars['nonce'] = false;
        } else {
            $vars['punish'] = (bool)$this->params->get('punish', 0, 'INTEGER');
            $vars['trigger'] = (bool)$this->params->get('trigger', 0, 'INTEGER');
            $vars['cdp'] = (bool)$this->params->get('cdp', 0, 'INTEGER');
            $vars['nonce'] = (bool)$this->params->get('nonce', 0, 'INTEGER');
        }

        $session->set('remote_addr', $vars['remote_addr'], 'plg_captcha_hashcash');
        $session->set('level', $vars['level'], 'plg_captcha_hashcash');
        $session->set('request_time', $vars['request_time'], 'plg_captcha_hashcash');

        $doc->addScriptOptions('plg_captcha_hashcash', $vars);

        return true;
    }

    /**
     * Method to react on the setup of a captcha field. Gives the possibility
     * to change the field and/or the XML element for the field.
     *
     * @param   CaptchaField       $field    Captcha field instance
     * @param   \SimpleXMLElement  $element  XML form definition
     *
     * @return void
     *
     * @since 3.9.0
     */
    public function onSetupField(CaptchaField $field, \SimpleXMLElement $element)
    {
        $element['hiddenLabel'] = 'true';
    }

    /**
     * Gets the challenge HTML
     *
     * @param   string  $name   The name of the field.
     * @param   string  $id     The id of the field.
     * @param   string  $class  The class of the field. This should be passed as
     *                          e.g. 'class="required"'.
     *
     * @return  string  The HTML to be embedded in the form.
     *
     * @since  2.5
     */
    public function onDisplay($name, $id = 'dynamic_hashcash_1', $class = '') {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ele = $dom->createElement('input');
        $ele->setAttribute('type', 'hidden');
        $ele->setAttribute('id', $id);
        $ele->setAttribute('data-name', $name);
        $ele->setAttribute('name', 'hashcash_response_field');
        $ele->setAttribute('class', $class);
        $ele->setAttribute('disabled', true); // disable the field so it doesn't get submitted, js re-enables it
        $dom->appendChild($ele);
        return $dom->saveHTML($ele);
    }

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct
     *
     * @param   string  $code  Answer provided by user.
     *
     * @return  True if the answer is correct, false otherwise
     *
     * @since  2.5
     */
    public function onCheckAnswer($code) {
        $session = $this->app->getSession();
        $input = $this->app->input;

        $remote_addr = $session->get('remote_addr', IpHelper::getIp(), 'plg_captcha_hashcash');
        $level = (int) $this->params->get('difficulty', 3);
        $request_time = $session->get('request_time', time(), 'plg_captcha_hashcash');

        $count = $input->get('hashcash_response_field', '', 'string');

        // Discard spam submissions
        if ($count == null || strlen($count) == 0) {
            throw new \RuntimeException($this->app->getLanguage()->_('PLG_CAPTCHA_HASHCASH_ERROR_EMPTY_RESPONSE'));
            return false;
        }
        $string = $remote_addr . $request_time . $count;
        $sha256 = hash('sha256', $string, false);
        $valid = preg_match('/^0{' . $level . '}/', $sha256);

        foreach (array('remote_addr', 'request_time') as $name) {
            $session->clear($name, 'plg_captcha_hashcash');
        }

        if ($valid) {
            return true;
        } else {
            throw new \RuntimeException($this->app->getLanguage()->_('PLG_CAPTCHA_HASHCASH_ERROR_INVALID_RESPONSE'));
            return false;
        }
    }
}
