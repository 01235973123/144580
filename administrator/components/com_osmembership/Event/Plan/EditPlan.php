<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

namespace OSSolution\MembershipPro\Admin\Event\Plan;

class EditPlan extends \MPFEventBase
{
	protected $requiredArguments = ['row'];

	public function __construct(array $arguments = [])
	{
		parent::__construct('onEditSubscriptionPlan', $arguments);
	}
}