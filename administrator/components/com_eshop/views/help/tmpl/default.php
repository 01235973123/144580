<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('ESHOP_HELP'), 'generic.png');
?>
<div class="helps-area">
    <div class="eshop-released">
    	<?php echo Text::_('ESHOP_HELP_RELEASED'); ?>
    </div>
    <div class="eshop-demo">
    	<?php echo Text::_('ESHOP_HELP_DEMO'); ?>
    </div>
    <div class="eshop-doc">
    	<?php echo Text::_('ESHOP_HELP_DOCUMENTATION'); ?>
    </div>
    <div class="eshop-support">
		<?php echo Text::_('ESHOP_HELP_SUPPORT'); ?>
		<ol>
			<li><?php echo Text::_('ESHOP_HELP_SUPPORT_1'); ?></li>
			<li><?php echo Text::_('ESHOP_HELP_SUPPORT_2'); ?></li>
			<li><?php echo Text::_('ESHOP_HELP_SUPPORT_3'); ?></li>
		</ol>
    </div>
</div>