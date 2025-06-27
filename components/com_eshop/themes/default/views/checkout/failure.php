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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

?>
<div class="page-header">
	<h1 class="page-title eshop-title"><?php echo Text::_('ESHOP_PAYMENT_FAILURE_TITLE'); ?></h1>
</div>	
<p>
	<?php
	$session = Factory::getApplication()->getSession();
	echo $session->get('omnipay_payment_error_reason');
	?>
</p>