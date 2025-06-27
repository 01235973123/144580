<?php
/**
 * @version        1.0
 * @package        Joomla
 * @subpackage     OSFramework
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EShopViewForm extends HtmlView
{
	/**
	 *
	 * @var $languages
	 */
	protected $languages;

	/**
	 *
	 * @var $languageData
	 */
	protected $languageData;

	/**
	 *
	 * @var $translatable
	 */
	protected $translatable;

	/**
	 *
	 * @var $item
	 */
	protected $item;

	/**
	 * Hold all select lists use on the form
	 *
	 * @var array $lists
	 */
	protected $lists;

	/**
	 * Flag to determine if this form is multilingual and translatable
	 *
	 * @var bool
	 */
	protected $isMultilingualTranslable = false;

	/**
	 *
	 * @var $lang_prefix
	 */
	public $lang_prefix = ESHOP_LANG_PREFIX;

	/**
	 * Suffix use to append to name of the input
	 *
	 * @var string
	 */
	protected $nameSuffix = '';

	/**
	 * Suffix use to append to id of the input
	 *
	 * @var string
	 */
	protected $idSuffix = '';

	public function display($tpl = null)
	{
		// Check access first
		$mainframe   = Factory::getApplication();
		$accessViews = ['orders', 'reviews', 'payments', 'shippings', 'themes', 'customers', 'customergroups', 'coupons', 'taxclasses', 'taxrates'];
		foreach ($accessViews as $view)
		{
			if (EShopInflector::singularize($view) == $this->getName() && !Factory::getUser()->authorise('eshop.' . $view, 'com_eshop'))
			{
				$mainframe->enqueueMessage(Text::_('ESHOP_ACCESS_NOT_ALLOW'), 'error');
				$mainframe->redirect('index.php?option=com_eshop&view=dashboard');
			}
		}
		$db    = Factory::getDbo();
		$item  = $this->get('Item');
		$lists = [];

		if (property_exists($item, 'published'))
		{
			$lists['published'] = EShopHtmlHelper::getBooleanInput('published', $item->published ?? 1);
		}

		if (isset($item->access))
		{
			if (version_compare(JVERSION, '1.6.0', 'ge'))
			{
				$lists['access'] = HTMLHelper::_('access.level', 'access', $item->access, 'class="input-xlarge form-select"', false);
			}
			else
			{
				$sql = 'SELECT id AS value, name AS text FROM #__groups ORDER BY id';
				$db->setQuery($sql);
				$groups          = $db->loadObjectList();
				$lists['access'] = HTMLHelper::_(
					'select.genericlist',
					$groups,
					'access',
					'class="input-xlarge form-select"',
					'value',
					'text',
					$item->access
				);
			}
		}
		if ($this->get('translatable'))
		{
			if (Multilanguage::isEnabled())
			{
				$this->languages    = EShopHelper::getLanguages();
				$this->languageData = EShopHelper::getLanguageData();

				if (count($this->languages) > 1)
				{
					$this->isMultilingualTranslable = true;
				}
			}

			$this->translatable = true;
		}
		else
		{
			$this->translatable = false;
		}

		$this->_buildListArray($lists, $item);

		$this->item  = $item;
		$this->lists = $lists;

		$this->_buildToolbar();

		parent::display($tpl);
	}

	/**
	 * Build all the lists items used in the form and store it into the array
	 *
	 * @param  $lists
	 *
	 * @return boolean
	 */
	public function _buildListArray(&$lists, $item)
	{
		return true;
	}

	/**
	 * Build the toolbar for view list
	 */
	public function _buildToolbar()
	{
		$input    = Factory::getApplication()->input;
		$viewName = $this->getName();
		$canDo    = EShopHelper::getActions($viewName);
		$edit     = $input->get('edit');
		$text     = $edit ? Text::_($this->lang_prefix . '_EDIT') : Text::_($this->lang_prefix . '_NEW');
		ToolbarHelper::title(Text::_($this->lang_prefix . '_' . $viewName) . ': <small><small>[ ' . $text . ' ]</small></small>');
		ToolbarHelper::apply($viewName . '.apply');
		ToolbarHelper::save($viewName . '.save');
		ToolbarHelper::save2new($viewName . '.save2new');

		if ($edit)
		{
			ToolbarHelper::cancel($viewName . '.cancel', 'JTOOLBAR_CLOSE');
		}
		else
		{
			ToolbarHelper::cancel($viewName . '.cancel');
		}
	}

	/**
	 * Load form behavior
	 *
	 * @return void
	 */
	protected function loadFormValidator()
	{
		if (version_compare(JVERSION, '4.0.0', '>='))
		{
			Factory::getDocument()->getWebAssetManager()
				->useScript('form.validate');
		}
		else
		{
			HTMLHelper::_('behavior.formvalidator');
		}
	}

	/**
	 * Method to render hidden variables for management list view
	 *
	 * @return void
	 */
	protected function renderFormHiddenVariables()
	{
		include __DIR__ . '/tmpl/form_hidden_vars.php';
	}
}
