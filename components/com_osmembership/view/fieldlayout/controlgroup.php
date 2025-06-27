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
 * @var OSMembershipHelperBootstrap $bootstrapHelper
 * @var string                      $class
 * @var string                      $controlGroupAttributes
 * @var string                      $label
 * @var string                      $description
 * @var string                      $input
 */

$config = OSMembershipHelper::getConfig();

$controlGroupClass = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-group') : 'control-group';
$controlLabelClass = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-label') : 'control-label';
$controlsClass     = $bootstrapHelper ? $bootstrapHelper->getClassMapping('controls') : 'controls';
?>
<div class="<?php echo $controlGroupClass . $class ?>" <?php echo $controlGroupAttributes ?>>
	<div class="<?php echo $controlLabelClass ?>">
		<?php
            echo $label;

            if ($config->get('display_field_description', 'use_tooltip') == 'under_field_label' && strlen($description) > 0)
            {
            ?>
                <p class="osm-field-description"><?php echo $description; ?></p>
            <?php
            }
        ?>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<?php
            echo $input;

            if ($config->get('display_field_description') == 'under_field_input' && strlen($description) > 0)
            {
            ?>
                <p class="osm-field-description"><?php echo $description; ?></p>
            <?php
            }

            if ($config->get('display_field_description') == 'next_to_field_input' && strlen($description) > 0)
            {
            ?>
                <span class="osm-field-description"><?php echo $description; ?></span>
            <?php
            }
        ?>
	</div>
</div>

