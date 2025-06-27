<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

echo $this->newAddressForm->render();
?>
<input type="hidden" name="customer_id" value="<?php echo intval($this->item->customer_id); ?>" />