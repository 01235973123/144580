<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**@var OSMembershipHelperBootstrap $bootstrapHelper * */
$bootstrapHelper = $this->bootstrapHelper;

$controlGroupClass   = $bootstrapHelper->getClassMapping('control-group');

foreach ($this->extraLoginButtons as $button) :
	$dataAttributeKeys = array_filter(array_keys($button), function ($key) {
		return str_starts_with($key, 'data-');
	});
	?>
	<div class="osm-extra-login-buttons <?php echo $controlGroupClass; ?>">
		<div class="controls">
			<button type="button"
			        class="btn btn-secondary w-100 <?php echo $button['class'] ?? '' ?>"
			<?php foreach ($dataAttributeKeys as $key) : ?>
				<?php echo $key ?>="<?php echo $button[$key] ?>"
			<?php endforeach; ?>
			<?php if ($button['onclick']) : ?>
				onclick="<?php echo $button['onclick'] ?>"
			<?php endif; ?>
			title="<?php echo Text::_($button['label']) ?>"
			id="<?php echo $button['id'] ?>"
			>
			<?php if (!empty($button['icon'])) : ?>
				<span class="<?php echo $button['icon'] ?>"></span>
			<?php elseif (!empty($button['image'])) : ?>
				<?php echo HTMLHelper::_('image', $button['image'], Text::_($button['tooltip'] ?? ''), [
					'class' => 'icon',
				], true) ?>
			<?php elseif (!empty($button['svg'])) : ?>
				<?php echo $button['svg']; ?>
			<?php endif; ?>
			<?php echo Text::_($button['label']) ?>
			</button>
		</div>
	</div>
<?php endforeach; ?>

