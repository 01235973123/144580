<?php
/**
 * @package            Joomla
 * @subpackage         Joom Donation
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2009 - 2023 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class DonationModelChart extends OSFModel
{
	/**
	 * Constructor
	 *
	 * @param   array  $config
	 *
	 * @throws Exception
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);

		$this->state->insert('filter_campaign_id', 'int', 0);
	}

	/**
	 * Get sales data in last 12 months
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function getData()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$today = Factory::getDate('Now', Factory::getApplication()->get('offset'));
		$sales = [];

		for ($i = 0; $i < 12; $i++)
		{
			if ($i > 0)
			{
				$today->modify('-1 month');
			}

			$month = $today->format('n', true);
			$year  = $today->format('Y', true);

			$startMonth = clone $today;
			$endMonth   = clone $today;

			$startMonth->setTime(0, 0, 0);
			$startMonth->setDate($year, $month, 1);
			$endMonth->setTime(23, 59, 59);
			$endMonth->setDate($year, $month, $today->format('t', true));	

			$query->clear()
				->select('SUM(amount)')
				->from('#__jd_donors')
				->where('published = 1')
				->where('created_date >=' . $db->quote($startMonth->toSql()))
				->where('created_date <= ' . $db->quote($endMonth->toSql()));
			if ($this->state->filter_campaign_id)
			{
				$query->where('campaign_id =  ' . $this->state->filter_campaign_id . ')');
			}
			$db->setQuery($query);
			//echo $db->getQuery();
			//echo "<BR />";
			$sales[$today->format('M') . '/ ' . $year] = (int) $db->loadResult();
		}

		return (array)$sales;
	}
}