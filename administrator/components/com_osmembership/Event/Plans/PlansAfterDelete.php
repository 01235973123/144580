<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

namespace OSSolution\MembershipPro\Admin\Event\Plans;

class PlansAfterDelete extends \MPFEventModelItemsdelete
{
	public function __construct(array $arguments = [])
	{
		parent::__construct('onPlansAfterDelete', $arguments);
	}
}
