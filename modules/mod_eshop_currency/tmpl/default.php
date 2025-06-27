<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2011 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
?>
<form method="post" action="<?php echo Uri::base(); ?>index.php?option=com_eshop&task=currency.change">
	<div id="currency" class="eshop-currency<?php echo $params->get( 'moduleclass_sfx' ); ?>">
		<?php
		for ($i = 0; $n = count($currencies), $i < $n; $i++)
		{
			$currency = $currencies[$i];
			
			if ($currency->currency_code == $currencyCode)
			{
				?>
				<a title="<?php echo $currency->currency_name; ?>">
					<b><?php echo $currency->currency_code; ?></b>
				</a>
				<?php
			}
			else 
			{
				?>
				<a onclick="jQuery('input[name=\'currency_code\']').attr('value', '<?php echo $currency->currency_code; ?>'); jQuery(this).parent().parent().submit();" title="<?php echo $currency->currency_name; ?>">
					<?php echo $currency->currency_code; ?>
				</a>
				<?php
			}
		}
		?>
		<input type="hidden" value="" name="currency_code" />
		<input type="hidden" value="<?php echo base64_encode(Uri::getInstance()->toString()); ?>" name="return" />
	</div>
</form>