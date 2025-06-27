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

?>
<button class="btn" type="button" onclick="Joomla.submitbutton('products.cancel'); ">
	<?php echo Text::_('ESHOP_CANCEL'); ?>
</button>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('products.saveInventory');">
	<?php echo Text::_('ESHOP_PROCESS'); ?>
</button>