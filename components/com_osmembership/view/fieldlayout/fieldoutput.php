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
 * @var string $controlGroupAttributes
 * @var string $title
 * @var string $fieldValue
 */

$controlGroupClass = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-group') : 'control-group';
$controlLabelClass = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-label') : 'control-label';
$controlsClass     = $bootstrapHelper ? $bootstrapHelper->getClassMapping('controls') : 'controls';
?>
<div <?php echo $controlGroupAttributes; ?> class="<?php echo $controlGroupClass; ?> osm-field-value">
    <div class="<?php echo $controlLabelClass; ?>">
		<?php echo $title; ?>
    </div>
	<div class="<?php echo $controlsClass; ?>"><?php echo $fieldValue; ?></div>
</div>

