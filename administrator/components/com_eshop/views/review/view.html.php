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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewReview extends EShopViewForm
{

	public function __construct($config)
	{
		$config['name'] = 'review';

		$document = Factory::getApplication()->getDocument();
		$document->addStyleSheet(Uri::root(true) . '/components/com_eshop/assets/rating/dist/star-rating.css');

		parent::__construct($config);
	}

	public function _buildListArray(&$lists, $item)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		//Build products list
		$query->select('a.id AS value, b.product_name AS text')
			->from('#__eshop_products AS a')
			->innerJoin('#__eshop_productdetails AS b ON (a.id = b.product_id)')
			->where('a.published = 1')
			->where('b.language = "' . ComponentHelper::getParams('com_languages')->get('site', 'en-GB') . '"')
			->order('a.ordering');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if (count($rows))
		{
			$lists['products'] = HTMLHelper::_(
				'select.genericlist',
				$rows,
				'product_id',
				[
					'option.text.toHtml' => false,
					'option.text'        => 'text',
					'option.value'       => 'value',
					'list.attr'          => ' class="input-xlarge form-select chosen" ',
					'list.select'        => $item->product_id,
				]
			);
		}
		else
		{
			$lists['products'] = '';
		}

		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('ESHOP_RATING_SELECT'));
		$options[] = HTMLHelper::_('select.option', '5', Text::_('ESHOP_RATING_EXCELLENT'));
		$options[] = HTMLHelper::_('select.option', '4', Text::_('ESHOP_RATING_VERY_GOOD'));
		$options[] = HTMLHelper::_('select.option', '3', Text::_('ESHOP_RATING_AVERAGE'));
		$options[] = HTMLHelper::_('select.option', '2', Text::_('ESHOP_RATING_POOR'));
		$options[] = HTMLHelper::_('select.option', '1', Text::_('ESHOP_RATING_TERRIBLE'));

		$selectRating = HTMLHelper::_('select.genericlist', $options, 'rating', 'class="star-rating"', 'value', 'text', $item->rating);

		$lists['rating'] = $selectRating;
	}
}