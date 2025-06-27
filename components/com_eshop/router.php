<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2013 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;

/**
 * Routing class from com_eshop
 *
 * @since  1.0.0
 */
class EshopRouter extends RouterBase
{
	/**
	 *
	 * Build the route for the com_eshop component
	 *
	 * @param   array    An array of URL arguments
	 *
	 * @return    array    The URL arguments to use to assemble the subsequent URL.
	 * @since    1.5
	 */
	public function build(&$query)
	{
		require_once JPATH_ROOT . '/components/com_eshop/helpers/helper.php';
		require_once JPATH_ROOT . '/components/com_eshop/helpers/' . ((version_compare(JVERSION, '3.0', 'ge') && Multilanguage::isEnabled() && count(
					EShopHelper::getLanguages()
				) > 1) ? 'routev3.php' : 'route.php');

		$db       = Factory::getDbo();
		$segments = [];
		$queryArr = $query;

		if (isset($queryArr['option']))
		{
			unset($queryArr['option']);
		}

		if (isset($queryArr['Itemid']))
		{
			unset($queryArr['Itemid']);
		}

		//Store the query string to use in the parseRouter method
		$queryString = http_build_query($queryArr);

		$app  = Factory::getApplication();
		$menu = $app->getMenu();

		//We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);

			// If the given menu item doesn't belong to our component, unset the Itemid from query array
			if ($menuItem && $menuItem->component != 'com_eshop')
			{
				unset($query['Itemid']);
			}
		}

		if ($menuItem && empty($menuItem->query['view']))
		{
			$menuItem->query['view'] = '';
		}

		//Are we dealing with an product or category that is attached to a menu item?
		if (is_object(
				$menuItem
			) && isset($query['view']) && isset($query['id']) && $menuItem->query['view'] == $query['view'] && isset($query['id']) && $menuItem->query['id'] == intval(
				$query['id']
			))
		{
			unset($query['view']);

			if (isset($query['catid']))
			{
				unset($query['catid']);
			}

			unset($query['id']);
		}

		if (is_object($menuItem) && $menuItem->query['view'] == 'category' && isset($query['catid']) && $menuItem->query['id'] == intval(
				$query['catid']
			))
		{
			if (isset($query['catid']))
			{
				unset($query['catid']);
			}
		}

		$parentId = 0;

		if (is_object($menuItem))
		{
			if (isset($menuItem->query['view']) && $menuItem->query['view'] == 'category')
			{
				$parentId = (int) $menuItem->query['id'];
			}
		}

		$view  = $query['view'] ?? '';
		$id    = isset($query['id']) ? (int) $query['id'] : 0;
		$catid = isset($query['catid']) ? (int) $query['catid'] : 0;

		if ($view == 'cart' || $view == 'quote' || $view == 'checkout' || $view == 'wishlist' || $view == 'compare' || $view == 'customer')
		{
			if (isset($query['Itemid']) && !EShopRoute::findView($view, $query['l'] ?? ''))
			{
				unset($query['Itemid']);
			}
		}

		switch ($view)
		{
			case 'search':
				if (!isset($query['Itemid']) || (isset($query['Itemid']) && $query['Itemid'] == EShopRoute::getDefaultItemId(
							$query['l'] ?? ''
						)))
				{
					$segments[] = 'search result';
				}

				break;
			case 'filter':
				if (!isset($query['Itemid']) || (isset($query['Itemid']) && $query['Itemid'] == EShopRoute::getDefaultItemId(
							$query['l'] ?? ''
						)))
				{
					$segments[] = 'filter';
				}

				break;
			case 'category' :
				if ($id)
				{
					$segments = array_merge($segments, EShopHelper::getCategoryPath($id, 'alias', $query['l'] ?? '', $parentId));
				}

				break;
			case 'product' :
				if ($id)
				{
					$segments[] = EShopHelper::getElementAlias($id, 'product', $query['l'] ?? '');
				}
				if ($catid)
				{
					$segments = array_merge(
						EShopHelper::getCategoryPath($catid, 'alias', $query['l'] ?? '', $parentId),
						$segments
					);
				}

				break;
			case 'manufacturer':
				if ($id)
				{
					$segments[] = EShopHelper::getElementAlias($id, 'manufacturer', $query['l'] ?? '');
				}

				break;
			case 'checkout':
				$layout = $query['layout'] ?? '';

				if (!isset($query['Itemid']) || (isset($query['Itemid']) && $query['Itemid'] == EShopRoute::getDefaultItemId(
							$query['l'] ?? ''
						)))
				{
					$segments[] = 'checkout';
				}

				switch ($layout)
				{
					case 'cancel':
						$segments[] = 'cancel';
						break;
					case 'complete':
						$segments[] = 'complete';
						break;
					case 'failure':
						$segments[] = 'failure';
						break;
					default:
						break;
				}

				break;
			case 'cart':
				if (!isset($query['Itemid']) || (isset($query['Itemid']) && $query['Itemid'] == EShopRoute::getDefaultItemId(
							$query['l'] ?? ''
						)))
				{
					$segments[] = 'shopping cart';
				}
				break;
			case 'quote':
				$layout = $query['layout'] ?? '';

				if (!isset($query['Itemid']) || (isset($query['Itemid']) && $query['Itemid'] == EShopRoute::getDefaultItemId(
							$query['l'] ?? ''
						)))
				{
					$segments[] = 'quote cart';
				}

				switch ($layout)
				{
					case 'complete':
						$segments[] = 'complete';
						break;
					default:
						break;
				}

				break;
			case 'wishlist':
				if (!isset($query['Itemid']) || (isset($query['Itemid']) && $query['Itemid'] == EShopRoute::getDefaultItemId(
							$query['l'] ?? ''
						)))
				{
					$segments[] = 'wishlist';
				}

				break;
			case 'compare':
				if (!isset($query['Itemid']) || (isset($query['Itemid']) && $query['Itemid'] == EShopRoute::getDefaultItemId(
							$query['l'] ?? ''
						)))
				{
					$segments[] = 'compare';
				}

				break;
			case 'customer':
				$layout = $query['layout'] ?? '';

				if (!isset($query['Itemid']) || (isset($query['Itemid']) && $query['Itemid'] == EShopRoute::getDefaultItemId(
							$query['l'] ?? ''
						)))
				{
					$segments[] = 'customer';
				}

				switch ($layout)
				{
					case 'account':
						$segments[] = 'account';
						break;
					case 'orders':
						$segments[] = 'order history';
						break;
					case 'quotes':
						$segments[] = 'quote history';
						break;
					case 'downloads':
						$segments[] = 'downloads';
						break;
					case 'addresses':
						$segments[] = 'addresses';
						break;
					case 'order':
						$segments[] = 'order details';
						break;
					case 'quote':
						$segments[] = 'quote details';
						break;
					case 'address':
						$segments[] = 'edit address';
						break;
					default:
						break;
				}

				break;
		}

		if (isset($query['task']) && $query['task'] == 'product.downloadPDF')
		{
			$segments[] = 'download pdf';
		}

		if (isset($query['task']) && $query['task'] == 'customer.downloadInvoice')
		{
			$segments[] = 'download invoice';
		}

		if (isset($query['task']) && $query['task'] == 'cart.reOrder')
		{
			$segments[] = 're order';
		}

		if (isset($query['task']) && $query['task'] == 'customer.downloadFile')
		{
			$segments[] = 'download';
		}

		if (isset($query['task']) && $query['task'] == 'search')
		{
			$segments[] = 'search';
		}


		if (isset($query['task']))
		{
			unset($query['task']);
		}

		if (isset($query['view']))
		{
			unset($query['view']);
		}

		if (isset($query['id']))
		{
			unset($query['id']);
		}

		if (isset($query['catid']))
		{
			unset($query['catid']);
		}

		if (isset($query['key']))
		{
			unset($query['key']);
		}

		if (isset($query['redirect']))
		{
			unset($query['redirect']);
		}

		if (isset($query['l']))
		{
			unset($query['l']);
		}

		if (isset($query['layout']))
		{
			unset($query['layout']);
		}

		if (count($segments))
		{
			$segments = array_map('Joomla\CMS\Application\ApplicationHelper::stringURLSafe', $segments);
			$key      = md5(implode('/', $segments));
			$q        = $db->getQuery(true);
			$q->select('id')
				->from('#__eshop_urls')
				->where('md5_key="' . $key . '"');
			$db->setQuery($q);
			$urlId = $db->loadResult();

			if (!$urlId)
			{
				$q->clear();
				$q->insert('#__eshop_urls')
					->columns('md5_key, `query`')
					->values("'$key', '$queryString'");
				$db->setQuery($q);
				$db->execute();
			}
			else
			{
				$q->clear();
				$q->update('#__eshop_urls')
					->set('query="' . $queryString . '"')
					->where('id = ' . $urlId);
				$db->setQuery($q);
				$db->execute();
			}
		}

		return $segments;
	}

	/**
	 *
	 * Parse the segments of a URL.
	 *
	 * @param   array    The segments of the URL to parse.
	 *
	 * @return    array    The URL attributes to be used by the application.
	 * @since    1.5
	 */
	public function parse(&$segments)
	{
		$vars = [];

		if (count($segments))
		{
			$db    = Factory::getDbo();
			$key   = md5(str_replace(':', '-', implode('/', $segments)));
			$query = $db->getQuery(true);
			$query->select('`query`')
				->from('#__eshop_urls')
				->where('md5_key = "' . $key . '"');
			$db->setQuery($query);
			$queryString = $db->loadResult();

			if ($queryString)
			{
				parse_str(html_entity_decode($queryString), $vars);
			}
			else
			{
				$method = strtoupper(Factory::getApplication()->input->getMethod());

				if ($method == 'GET')
				{
					throw new Exception('Page not found', 404);
				}
			}

			if (version_compare(JVERSION, '4.0.0', 'ge'))
			{
				$segments = [];
			}
		}

		$item = Factory::getApplication()->getMenu()->getActive();

		if ($item)
		{
			foreach ($item->query as $key => $value)
			{
				if ($key != 'option' && $key != 'Itemid' && !isset($vars[$key]))
				{
					$vars[$key] = $value;
				}
			}
		}

		return $vars;
	}
}

/**
 * EShop router functions
 *
 * These functions are proxies for the new router interface
 * for old SEF extensions.
 *
 * @param   array &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 */
function EshopBuildRoute(&$query)
{
	$router = new EshopRouter();

	return $router->build($query);
}

function EshopParseRoute($segments)
{
	$router = new EshopRouter();

	return $router->parse($segments);
}