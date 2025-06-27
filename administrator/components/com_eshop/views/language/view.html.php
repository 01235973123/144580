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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewLanguage extends HtmlView
{
	/**
	 *
	 * @var $trans
	 */
	protected $trans;

	/**
	 *
	 * @var $lists
	 */
	protected $lists;

	/**
	 *
	 * @var $lang
	 */
	protected $lang;

	/**
	 *
	 * @var $item
	 */
	protected $item;

	/**
	 *
	 * @var $pagination
	 */
	protected $pagination;

	public function display($tpl = null)
	{
		$input     = Factory::getApplication()->input;
		$mainframe = Factory::getApplication();
		$option    = 'com_eshop';
		$lang      = $mainframe->getUserStateFromRequest($option . 'lang', 'lang', 'en-GB', 'string');
		if (!$lang)
		{
			$lang = 'en-GB';
		}
		$search          = $mainframe->getUserStateFromRequest('com_eshop.language.search', 'search', '', 'string');
		$search          = strtolower($search);
		$lists['search'] = $search;
		$item            = $input->get('item', 'com_eshop');
		if (!$item)
		{
			$item = 'com_eshop';
		}
		$model      = $this->getModel('language');
		$trans      = $model->getTrans($lang, $item);
		$languages  = $model->getSiteLanguages();
		$pagination = $model->getPagination();
		$options    = [];
		foreach ($languages as $language)
		{
			$options[] = HTMLHelper::_('select.option', $language, $language);
		}
		$lists['lang'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'lang',
			' class="input-xlarge form-select"  onchange="submit();" ',
			'value',
			'text',
			$lang
		);

		$options       = [];
		$options[]     = HTMLHelper::_('select.option', 'com_eshop', Text::_('ESHOP_FRONT_END'));
		$options[]     = HTMLHelper::_('select.option', 'admin.com_eshop', Text::_('ESHOP_BACK_END'));
		$lists['item'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'item',
			' class="input-xlarge form-select"  onchange="submit();" ',
			'value',
			'text',
			$item
		);

		$this->trans      = $trans;
		$this->lists      = $lists;
		$this->lang       = $lang;
		$this->item       = $item;
		$this->pagination = $pagination;

		parent::display($tpl);
	}
}