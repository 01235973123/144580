<?php
/**
 * @package            Joomla
 * @subpackage         Membership Pro
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2012 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$inputSmallClass   = $bootstrapHelper->getClassMapping('input-small');
?>
<h3 class="osm-heading"><?php echo $this->escape(Text::_('OSM_PAYMENT_INFORMATION')); ?></h3>
<div class="<?php echo $controlGroupClass ?>">
    <div class="<?php echo $controlLabelClass; ?>">
        <label><?php echo Text::_('OSM_GROSS_AMOUNT'); ?></label>
    </div>
    <div class="<?php echo $controlsClass; ?>">
		<?php
		$input = '<input id="gross_amount" type="text" readonly="readonly" class="' . $inputSmallClass . '" value="' . OSMembershipHelper::formatAmount($this->row->gross_amount, $this->config) . '" />';

		if ($this->config->currency_position == 0)
		{
			echo $bootstrapHelper->getPrependAddon($input, $this->currencySymbol);
		}
		else
		{
			echo $bootstrapHelper->getAppendAddon($input, $this->currencySymbol);
		}
		?>
    </div>
</div>