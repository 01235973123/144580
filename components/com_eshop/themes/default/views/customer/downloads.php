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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$bootstrapHelper        = $this->bootstrapHelper;
$rowFluidClass          = $bootstrapHelper->getClassMapping('row-fluid');
$span2Class             = $bootstrapHelper->getClassMapping('span2');
$pullLeftClass          = $bootstrapHelper->getClassMapping('pull-left');
$btnBtnPrimaryClass		= $bootstrapHelper->getClassMapping('btn btn-primary');

if (isset($this->warning))
{
	?>
	<div class="warning"><?php echo $this->warning; ?></div>
	<?php
}
?>
<div class="page-header">
	<h1 class="page-title eshop-title"><?php echo Text::_('ESHOP_DOWNLOADS'); ?></h1>
</div>	
<?php
if (!count($this->downloads))
{
	?>
	<div class="no-content"><?php echo Text::_('ESHOP_NO_DOWNLOADS'); ?></div>
	<?php
}
else
{
	?>
	<div class="<?php echo $rowFluidClass; ?>">
		<form id="adminForm" class="download-list">
			<?php
			foreach ($this->downloads as $download)
			{
				?>
				<div class="order-id"><b><?php echo Text::_('ESHOP_ORDER_ID'); ?>: </b> #<?php echo $download->order_id; ?></div>
				<div class="download-size"><b><?php echo Text::_('ESHOP_SIZE'); ?>: </b><?php echo $download->size;  ?></div>
				<div class="download-content">
					<div>
						<b><?php echo Text::_('ESHOP_NAME'); ?>: </b><?php echo $download->download_name; ?><br />
					</div>
					<div>
						<b><?php echo Text::_('ESHOP_REMAINING'); ?>: </b> <?php echo $download->remaining; ?>
					</div>
					<div class="download-info" align="right">
						<a href="<?php echo Route::_('index.php?option=com_eshop&task=customer.downloadFile&order_id='.intval($download->order_id).'&download_code='.$download->download_code); ?>" title="<?php echo Text::_('ESHOP_DOWNLOAD'); ?>">
							<img src="<?php echo Uri::root(true); ?>/components/com_eshop/themes/default/images/download.png" />
						</a>
					</div>
				</div>
				<?php
			}
			?>
		</form>
	</div>
	<?php
}
?>
<div class="<?php echo $rowFluidClass; ?>">
	<div class="<?php echo $span2Class; ?>">
		<input type="button" value="<?php echo Text::_('ESHOP_BACK'); ?>" id="button-back-download" class="<?php echo $btnBtnPrimaryClass; ?> <?php echo $pullLeftClass; ?>" />
	</div>
</div>
<script type="text/javascript">
	Eshop.jQuery(function($){
		$(document).ready(function(){
			$('#button-back-download').click(function() {
				var url = '<?php echo Route::_(EShopRoute::getViewRoute('customer')); ?>';
				$(location).attr('href', url);
			});
		})
	});
</script>