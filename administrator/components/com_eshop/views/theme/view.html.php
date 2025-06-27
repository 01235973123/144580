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

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewTheme extends EShopViewForm
{
	/**
	 *
	 * @var $form
	 */
	protected $form;

	public function _buildListArray(&$lists, $item)
	{
		$registry     = new Registry($item->params ?? '{}');
		$data         = new stdClass();
		$data->params = $registry->toArray();
		$form         = Form::getInstance(
			'eshop',
			JPATH_ROOT . '/components/com_eshop/themes/' . $item->name . '/' . $item->name . '.xml',
			[],
			false,
			'//config'
		);
		$form->bind($data);
		$this->form = $form;

		return true;
	}

	/**
	 * Override Build Toolbar function, only need Save, Save & Close and Close
	 */
	public function _buildToolbar()
	{
		$viewName = $this->getName();
		$canDo    = EShopHelper::getActions($viewName);
		$text     = Text::_($this->lang_prefix . '_EDIT');
		ToolbarHelper::title(Text::_($this->lang_prefix . '_' . $viewName) . ': <small><small>[ ' . $text . ' ]</small></small>');
		ToolbarHelper::apply($viewName . '.apply');
		ToolbarHelper::save($viewName . '.save');
		ToolbarHelper::cancel($viewName . '.cancel', 'JTOOLBAR_CLOSE');
	}
}