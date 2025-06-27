<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
?>
<div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
	<?php
	$span4Class = $bootstrapHelper->getClassMapping('span4');

	for ($i = 0 , $n = count($this->options) ; $i < $n ; $i++)
	{
		$value = $this->options[$i] ;
		?>
        <div class="<?php echo $span4Class; ?>">
            <input value="<?php echo $this->escape($value); ?>" type="checkbox" class="form-check-input" name="depend_on_options[]"><?php echo $value;?>
        </div>
		<?php
	}
	?>
</div>
