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
use Joomla\CMS\Uri\Uri;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewGeozone extends EShopViewForm
{
	/**
	 *
	 * @var $countryId
	 */
	protected $countryId;

	/**
	 *
	 * @var $countryOptions
	 */
	protected $countryOptions;

	/**
	 *
	 * @var $zoneToGeozones
	 */
	protected $zoneToGeozones;

	/**
	 *
	 * @var $geozonePostcodes
	 */
	protected $geozonePostcodes;

	public function _buildListArray(&$lists, $item)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, country_name AS name')
			->from('#__eshop_countries')
			->where('published=1')
			->order('country_name');
		$db->setQuery($query);
		$countryOptions = $db->loadObjectList();
		$query->clear();
		$query->select('zone_id,country_id')
			->from('#__eshop_geozonezones')
			->where('geozone_id = ' . (int) $item->id)
			->where('zone_id != 0');
		$db->setQuery($query);
		$zoneToGeozones = $db->loadObjectList();
		$config         = EShopHelper::getConfig();
		Factory::getApplication()->getDocument()->addScript(Uri::root(true) . '/administrator/components/com_eshop/assets/js/eshop.js')
			->addScriptDeclaration(EShopHtmlHelper::getZonesArrayJs())
			->addScriptDeclaration(EShopHtmlHelper::getCountriesOptionsJs());
		$this->countryId      = $config->country_id;
		$this->countryOptions = $countryOptions;
		$this->zoneToGeozones = $zoneToGeozones;

		$lists['include_countries'] = EShopHtmlHelper::getBooleanInput('include_countries', 1);
		$query->clear();
		$query = $db->getQuery(true);
		$query->select('a.id AS value, a.country_name AS text')
			->from('#__eshop_countries AS a')
			->where('published = 1')
			->order('a.country_name');
		$db->setQuery($query);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '-1', '-- ' . Text::_('ESHOP_ALL_COUNTRIES') . ' --');
		$options   = array_merge($options, $db->loadObjectList());
		$query->clear();
		$query->select('DISTINCT a.country_id')
			->from('#__eshop_geozonezones AS a')
			->where('zone_id = 0 and geozone_id = ' . (int) $item->id);
		$db->setQuery($query);
		$selectedItems           = $db->loadColumn();
		$lists['countries_list'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'countries_list[]',
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="input-xlarge form-select chosen" multiple="multiple"',
				'list.select'        => $selectedItems,
			]
		);
		Factory::getApplication()->getDocument()->addScriptDeclaration(EShopHtmlHelper::getTaxrateOptionsJs())->addScriptDeclaration(
			EShopHtmlHelper::getBaseonOptionsJs()
		);

		//Geozone postcodes
		$query->clear()
			->select('*')
			->from('#__eshop_geozonepostcodes')
			->where('geozone_id = ' . intval($item->id));
		$db->setQuery($query);
		$this->geozonePostcodes = $db->loadObjectList();
	}
}