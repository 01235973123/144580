<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 *
 * @var stdClass                     $row
 * @var string                       $name
 * @var array                        $options
 * @var string                       $value
 * @var string                       $attributes
 * @var ?OSMembershipHelperBootstrap $bootstrapHelper
 */

$size          = (int) $row->size ?: 1;
$span          = intval(12 / $size);
$i             = 0;
$numberOptions = count($options);

$rowFluid        = $bootstrapHelper ? $bootstrapHelper->getClassMapping('row-fluid') : 'row-fluid';
$spanClass       = $bootstrapHelper ? $bootstrapHelper->getClassMapping('span' . $span) : 'span' . $span;
$clearFixClass   = $bootstrapHelper ? $bootstrapHelper->getClassMapping('clearfix') : 'clearfix';
$ukFieldsetClass = $bootstrapHelper ? $bootstrapHelper->getFrameworkClass('uk-fieldset', 2) : '';
?>
<fieldset id="<?php echo $name; ?>" class="<?php echo $ukFieldsetClass . $clearFixClass; ?> oms-radio-container">
	<div class="<?php echo $rowFluid . ' ' . $clearFixClass; ?>">
		<?php
        $value = trim($value);

        foreach ($options as $option)
		{
            $i++;
            $checked = (trim($option) == $value) ? 'checked' : '';
		?>
            <div class="<?php echo $spanClass ?>">
                <label class="radio">
                    <input type="radio" id="<?php echo $name . $i; ?>"
                           name="<?php echo $name; ?>"
                           value="<?php echo htmlspecialchars((string) $option, ENT_COMPAT, 'UTF-8') ?>"
                        <?php echo $checked . $attributes; ?>
                    /><?php echo $option; ?>
                </label>
            </div>
		<?php
            if ($i % $size == 0 && $i < $numberOptions)
            {
            ?>
                </div>
                <div class="<?php echo $rowFluid . ' ' . $clearFixClass; ?>">
            <?php
            }
		}
		?>
	</div>
</fieldset>
