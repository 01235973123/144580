<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class OSMembershipViewCategoriesHtml extends MPFViewList
{
	/**
	 * Prepare view data
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		parent::prepareView();

		$ordering = [];

		foreach ($this->items as &$item)
		{
			$ordering[$item->parent_id][] = $item->id;
		}

		foreach ($this->items as $row)
		{
			if ($row->level > 1)
			{
				$currentParentId = $row->parent_id;
				$parentsStr      = ' ' . $currentParentId;

				for ($i2 = 0; $i2 < $row->level; $i2++)
				{
					foreach ($ordering as $k => $v)
					{
						$v = implode('-', $v);
						$v = '-' . $v . '-';

						if (str_contains($v, '-' . $currentParentId . '-'))
						{
							$parentsStr      .= ' ' . $k;
							$currentParentId = $k;
							break;
						}
					}
				}
			}
			else
			{
				$parentsStr = '';
			}

			$row->parentsStr = $parentsStr;
		}
	}
}