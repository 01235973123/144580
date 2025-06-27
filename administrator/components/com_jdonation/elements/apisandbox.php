<?php
use Joomla\CMS\Form\FormField;
defined('_JEXEC') or die('Restricted access');
class JFormFieldApisandbox extends FormField
{
    var $type = 'api';
    function getInput() {
        return '<button class="btn" onclick="window.open(\'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true\', \'\', \'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=540\');">GET API Test Mode Access</button>';
    }
}
