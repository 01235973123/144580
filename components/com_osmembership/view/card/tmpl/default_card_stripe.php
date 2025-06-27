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

/* @var OSMembershipHelperBootstrap $bootstrapHelper */
$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
?>
<div class="<?php echo $controlGroupClass; ?> payment_information" id="stripe-card-form">
	<div class="<?php echo $controlLabelClass; ?>" for="stripe-card-element">
		<?php echo Text::_('OSM_CREDIT_OR_DEBIT_CARD'); ?><span class="required">*</span>
	</div>
	<div class="<?php echo $controlsClass; ?>" id="stripe-card-element">

	</div>
</div>