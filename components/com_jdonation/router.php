<?php
/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Factory;


require_once JPATH_ROOT . '/components/com_jdonation/helper/route.php';
class JDonationRouter extends RouterBase
{
	function build(&$query)
	{
		$segments = array();
		$db       = Factory::getContainer()->get('db');

		//Store the query string to use in the parseRouter method
		$queryArr = $query;

		$app  = Factory::getApplication();
		$menu = $app->getMenu();

		//We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem      = $menu->getActive();
			$menuItemGiven = false;
		}
		else
		{
			$menuItem      = $menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// If the given menu item doesn't belong to our component, unset the Itemid from query array
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_jdonation')
		{
			unset($query['Itemid']);
		}

		if ($menuItem && empty($menuItem->query['view']))
		{
			$menuItem->query['view'] = '';
		}

		// Dealing with link to donation form
		if ($menuItem && isset($query['view']) && $query['view'] == 'donation' && $menuItem->query['view'] == 'donation')
		{
			$campaignId = isset($query['campaign_id']) ? (int) $query['campaign_id'] : 0;
			if ($menuItem->query['campaign_id'] == $campaignId)
			{
				unset($query['view']);
				if (isset($query['campaign_id']))
				{
					unset($query['campaign_id']);
				}
				if (isset($query['layout']))
				{
					unset($query['layout']);
				}
			}
		}

		$view       = isset($query['view']) ? $query['view'] : '';
		$campaignId = isset($query['campaign_id']) ? (int) $query['campaign_id'] : 0;
		$task       = isset($query['task']) ? $query['task'] : '';
		switch ($view)
		{
			case 'campaigns':
				unset($query['view']);
			break;
			case 'donation':
				if ($campaignId)
				{
					$segments[]         = DonationHelperRoute::getCampaignTitle($campaignId);
				}
				else
				{
					$segments[]         = 'donation form';
				}
				unset($query['campaign_id']);
				unset($query['view']);
				break;
			case 'usercampaigns':
				$segments[]             = 'User campaigns';
				unset($query['view']);
				break;
			case 'userdonors':
				$segments[]             = 'Received donation';
				if(isset($query['id']) && $query['id'] > 0)
				{
					$campaignId         = $query['id'];
					$db->setQuery("Select title from #__jd_campaigns where id = '$campaignId'");
					$title              = $db->loadResult();
					$segments[]         = $title;
					unset($query['id']);
				}
				unset($query['view']);
				break;
			case 'donationdetails':
				$segments[]             = 'Donation';
				if(isset($query['campaign_id']) && $query['campaign_id'] > 0)
				{
					$campaignId         = $query['campaign_id'];
					$db->setQuery("Select title from #__jd_campaigns where id = '$campaignId'");
					$title              = $db->loadResult();
					$segments[]         = $title;
					unset($query['campaign_id']);
				}
				$segments[] = $query['id'];
				unset($query['view']);
				unset($query['id']);
			break;
			case 'complete':
				$segments[]             = 'Donation Complete';
				if (isset($query['id']))
				{
					unset($query['id']);
				}
				unset($query['view']);
				break;
			case 'failure':
				$segments[]             = 'Donation Failure';
				unset($query['view']);
				break;
			case 'cancel':
				$segments[]             = 'Donation Cancel';
				unset($query['view']);
				break;
		}

		switch ($task)
		{
			case 'cancel':

				$segments[] = 'cancel-donation';
				unset($query['task']);
				break;
			case 'campaign.edit':
				if($query['id'] > 0)
				{
					$segments[]			= 'edit-campaign';
					$campaignId         = $query['id'];
					$db->setQuery("Select title from #__jd_campaigns where id = '$campaignId'");
					$title              = $db->loadResult();
					$segments[]         = $title;
				}
				else
				{
					$segments[] = 'add-campaign';
				}
				unset($query['task']);
				break;
		}

		if (count($segments))
		{
			$unProcessedVariables = array(
				'option',
				'Itemid',
				'start',
				'limitstart',
				'limit'
			);

			foreach ($unProcessedVariables as $variable)
			{
				if (isset($queryArr[$variable]))
				{
					unset($queryArr[$variable]);
				}
			}
			$queryString = http_build_query($queryArr);
			$segments    = array_map('Joomla\CMS\Application\ApplicationHelper::stringURLSafe', $segments);
			$key         = md5(implode('/', $segments));
			$dbQuery     = $db->getQuery(true);
			$dbQuery->select('COUNT(*)')
				->from('#__jd_urls')
				->where('md5_key="' . $key . '"');
			$db->setQuery($dbQuery);
			$total = $db->loadResult();
			if (!$total)
			{
				$dbQuery->clear();
				$dbQuery->insert('#__jd_urls')
					->columns('md5_key, `query`')
					->values("'$key', '$queryString'");
				$db->setQuery($dbQuery);
				$db->execute();
			}
		}

		return $segments;
	}

	/**
	 *
	 * Parse the segments of a URL.
	 *
	 * @param    array    The segments of the URL to parse.
	 *
	 * @return    array    The URL attributes to be used by the application.
	 */
	function parse( & $segments)
	{
		$vars = array();
		if (count($segments))
		{
			$db    = Factory::getContainer()->get('db');
			$key   = md5(str_replace(':', '-', implode('/', $segments)));
			$query = $db->getQuery(true);
			$query->select('`query`')
				->from('#__jd_urls')
				->where('md5_key="' . $key . '"');
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

			if (version_compare(JVERSION, '4.0.0-dev', 'ge')) {
				$segments = [];
			}
		}

		$item = Factory::getApplication()->getMenu()->getActive();
		if ($item)
		{
			if (!empty($vars['view']) && !empty($item->query['view']) && $vars['view'] == $item->query['view'])
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
		/*
		$file = fopen(JPATH_ROOT."/test.txt","w");
		ob_start();
		print_r ($segments);
		$vars = ob_get_contents();
		ob_end_clean();
		fwrite($file,$vars);
		fclose($file);
		*/
		return $vars;
	}
}

function JDonationBuildRoute(&$query)
{
    $router = new JDonationRouter();
    return $router->build($query);
}

function JDonationParseRoute($segments)
{
    $router = new JDonationRouter();
    return $router->parse($segments);
}
