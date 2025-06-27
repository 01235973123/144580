<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

$params = new Registry($this->item->params);
?>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('OSM_RECURRING_PAYMENT_AMOUNTS'); ?></legend>
    <div class="control-group">
        <div class="control-label">
            <?php echo Text::_('OSM_REGULAR_AMOUNT'); ?>
        </div>
        <div class="controls">
            <input type="text" class="form-control d-inline-block w-auto" name="regular_amount" value="<?php echo $params->get('regular_amount') ?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_REGULAR_DISCOUNT_AMOUNT'); ?>
        </div>
        <div class="controls">
            <input type="text" class="form-control d-inline-block w-auto" name="regular_discount_amount" value="<?php echo $params->get('regular_discount_amount') ?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_REGULAR_TAX_AMOUNT'); ?>
        </div>
        <div class="controls">
            <input type="text" class="form-control d-inline-block w-auto" name="regular_tax_amount" value="<?php echo $params->get('regular_tax_amount') ?>" />
        </div>
    </div>
    <?php
		if ($params->get('payment_processing_fee'))
		{
		?>
            <div class="control-group">
                <div class="control-label">
			        <?php echo Text::_('OSM_REGULAR_PAYMENT_PROCESSING_FEE'); ?>
                </div>
                <div class="controls">
                    <input type="text" class="form-control d-inline-block w-auto" name="regular_payment_processing_fee" value="<?php echo $params->get('payment_processing_fee') ?>" />
                </div>
            </div>
        <?php
		}
	?>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_REGULAR_GROSS_AMOUNT'); ?>
        </div>
        <div class="controls">
            <input type="text" class="form-control d-inline-block w-auto" name="regular_gross_amount" value="<?php echo $params->get('regular_gross_amount') ?>" />
        </div>
    </div>
</fieldset>
