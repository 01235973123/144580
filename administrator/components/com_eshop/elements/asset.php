<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2010 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;

class JFormFieldAsset extends FormField
{
	protected $type = 'Asset';

	protected function getInput()
	{
		$rootUri = Uri::root(true);
		Factory::getApplication()->getDocument()
			->addScript($rootUri . '/' . $this->element['path'] . 'script.js')
			->addStyleSheet($rootUri . '/' . $this->element['path'] . 'style.css');

		return null;
	}
}