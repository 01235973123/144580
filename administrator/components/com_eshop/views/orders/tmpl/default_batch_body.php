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

use Joomla\CMS\Language\Text;

?>
<script type="text/javascript" language="javascript">
	function check(cb, tb)
	{
		if(document.getElementById(cb).checked)
		{
			document.getElementById(tb).disabled = false;
		}
		else document.getElementById(tb).disabled = true;
	}
	function check_boolean(cb, tb)
	{
		if(document.getElementById(cb).checked)
		{
			document.getElementById(tb+'0').disabled = false;
			document.getElementById(tb+'1').disabled = false;
		}
		else
		{
			document.getElementById(tb+'0').disabled = true;
			document.getElementById(tb+'1').disabled = true;
		}
	}
</script>
<div class="control-group">
	<div class="control-label">
		<?php echo Text::_('ESHOP_BATCH_ORDERS_STATUS'); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['order_status_ids']; ?>
	</div>
</div>