<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Factory;

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_osmembership/loader.php';

/**
 * Routing class from com_osmembership
 *
 * @since  2.5.1
 */
class OSMembershipRouter extends RouterBase
{
	/**
	 * Build the route for the com_osmembership component
	 *
	 * @param   array &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   2.8.1
	 */
	public function build(&$query)
	{
		$segments = [];

		//We need a menu item.  Either the one specified in the query, or the current active one if none specified
		$menu = Factory::getApplication()->getMenu();

		if (empty($query['Itemid']))
		{
			$menuItem = $menu->getActive();
		}
		else
		{
			$menuItem = $menu->getItem($query['Itemid']);
		}

		// Initialize default value for menu item query
		if ($menuItem && empty($menuItem->query['view']))
		{
			$menuItem->query['view'] = '';
		}

		if ($menuItem && $menuItem->component === 'com_osmembership' && empty($menuItem->query['layout']))
		{
			$menuItem->query['layout'] = 'default';
		}

		// In case the active menu item link directly to the view
		if ($menuItem && isset($query['view']) && $menuItem->query['view'] === $query['view'])
		{
			if (isset($query['id'], $menuItem->query['id']) && $query['id'] == $menuItem->query['id'])
			{
				unset($query['id'], $query['catid']);
			}

			if (isset($query['layout']) && $query['layout'] == $menuItem->query['layout'])
			{
				unset($query['layout']);
			}

			unset($query['view']);
		}

		$mView = empty($menuItem->query['view']) ? null : $menuItem->query['view'];
		$mId   = empty($menuItem->query['id']) ? null : (int) $menuItem->query['id'];

		$view = $query['view'] ?? '';
		$id   = isset($query['id']) ? (int) $query['id'] : 0;

		// Link to register view and we have a menu item to link to plan view of the plan
		if ($menuItem && $view == 'register' && $mView == 'plan' && $mId == $id)
		{
			unset($query['catid'], $query['id']);
			$id = '';
		}

		if ($mView == 'plans' && $mId)
		{
			$parentId = (int) $menuItem->query['id'];

			if (isset($query['catid']) && $mId == intval($query['catid']))
			{
				unset($query['catid']);
			}
		}
		else
		{
			$parentId = 0;
		}

		$queryArr = $query;

		$task  = $query['task'] ?? '';
		$catId = isset($query['catid']) ? (int) $query['catid'] : 0;

		switch ($view)
		{
			case 'plans':
				if ($id)
				{
					$segments = array_merge($segments, OSMembershipHelperRoute::getCategoryRoutePath($id, $parentId));
				}

				unset($query['view'], $query['id']);
				break;
			case 'plan':
				if ($catId)
				{
					$segments = array_merge($segments, OSMembershipHelperRoute::getCategoryRoutePath($catId, $parentId));
				}

				if ($id)
				{
					$segments[] = OSMembershipHelperRoute::getPlanTitle($id);
				}

				unset($query['view'], $query['catid'], $query['id']);
				break;
			case 'register':
				if ($catId)
				{
					$segments = array_merge($segments, OSMembershipHelperRoute::getCategoryRoutePath($catId, $parentId));
				}

				if ($id)
				{
					$segments[] = OSMembershipHelperRoute::getPlanTitle($id);
				}

				$segments[] = 'Sign up';
				unset($query['view'], $query['catid'], $query['id']);
				break;
			case 'failure':
				$segments[] = 'Subscription Failure';
				unset($query['view']);
				break;
			case 'cancel':
				$segments[] = 'Subscription cancel';
				unset($query['view']);
				break;
			case 'complete':
				$segments[] = 'Subscription Complete';
				unset($query['view']);
				break;
			case 'subscription':
				$segments[] = 'Subscription Detail';
				unset($query['view']);
				break;
			case 'card':
				$segments[] = 'update card';
				unset($query['view']);
				break;
		}

		if ($task == 'renew_membership')
		{
			$segments[] = 'Renew Membership';
			unset($query['task']);
		}

		if ($task == 'download_document')
		{
			$segments[] = 'Download Document';
			unset($query['task']);
		}

		if (count($segments))
		{
			$unProcessedVariables = [
				'subscription_code',
				'group_id',
				'option',
				'Itemid',
				'search',
				'start',
				'limitstart',
				'limit',
			];

			foreach ($unProcessedVariables as $variable)
			{
				if (isset($queryArr[$variable]))
				{
					unset($queryArr[$variable]);
				}
			}

			$queryString = http_build_query($queryArr);

			/* @var \Joomla\Database\DatabaseDriver $db */
			$db       = Factory::getContainer()->get('db');
			$segments = array_map(ApplicationHelper::class . '::stringURLSafe', $segments);
			$key      = md5(implode('/', $segments));
			$q        = $db->getQuery(true);
			$q->select('COUNT(*)')
				->from('#__osmembership_sefurls')
				->where('md5_key="' . $key . '"');
			$db->setQuery($q);
			$total = $db->loadResult();

			if (!$total)
			{
				$q->clear()
					->insert('#__osmembership_sefurls')
					->columns('md5_key, `query`')
					->values("'$key', '$queryString'");
				$db->setQuery($q)
					->execute();
			}
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   2.5.1
	 */
	public function parse(&$segments)
	{
		$vars = [];

		if (count($segments))
		{
			/* @var \Joomla\Database\DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$key   = md5(str_replace(':', '-', implode('/', $segments)));
			$query = $db->getQuery(true)
				->select('`query`')
				->from('#__osmembership_sefurls')
				->where('md5_key="' . $key . '"');
			$db->setQuery($query);
			$queryString = $db->loadResult();

			if ($queryString)
			{
				parse_str(html_entity_decode($queryString), $vars);
			}
			else
			{
				$method = strtoupper(Factory::getApplication()->getInput()->getMethod());

				if ($method == 'GET')
				{
					throw new Exception('Page not found', 404);
				}
			}

			$segments = [];
		}

		$item = Factory::getApplication()->getMenu()->getActive();

		if ($item && $item->component == 'com_osmembership')
		{
			$mView = empty($item->query['view']) ? null : $item->query['view'];

			if (!empty($vars['view']) && ($vars['view'] == $mView || ($vars['view'] == 'register' && $mView == 'plan')))
			{
				foreach ($item->query as $key => $value)
				{
					if ($key != 'option' && $key != 'Itemid' && !isset($vars[$key]))
					{
						$vars[$key] = $value;
					}
				}
			}
		}

		return $vars;
	}
}

/**
 * Membership Pro router functions
 *
 * These functions are proxies for the new router interface
 * for old SEF extensions.
 *
 * @param   array &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 */

function OSMembershipBuildRoute(&$query)
{
	$router = new OSMembershipRouter();

	return $router->build($query);
}

function OSMembershipParseRoute($segments)
{
	$router = new OSMembershipRouter();

	return $router->parse($segments);
}
