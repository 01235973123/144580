<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

class OSMembershipControllerDiscount extends OSMembershipController
{
	/**
	 * Batch coupon generation
	 */
	public function batch()
	{
		/* @var OSMembershipModelCoupon $model */
		$model = $this->getModel('Discount');
		$model->batch($this->input);

		$this->setRedirect(
			'index.php?option=com_osmembership&view=discounts',
			Text::_('OSM_DISCOUNTS_SUCCESSFULLY_GENERATED')
		);
	}
}
