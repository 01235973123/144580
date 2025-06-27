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
$controlGroupClass      = $bootstrapHelper->getClassMapping('control-group');
$controlsClass          = $bootstrapHelper->getClassMapping('controls');
$inputAppendClass       = $bootstrapHelper->getClassMapping('input-append');
$inputPrependClass      = $bootstrapHelper->getClassMapping('input-prepend');
$imgPolaroid            = $bootstrapHelper->getClassMapping('img-polaroid');
$btnClass				= $bootstrapHelper->getClassMapping('btn');

$rootUri = Uri::root(true);
?>
<script src="<?php echo $rootUri; ?>/media/com_eshop/assets/colorbox/jquery.colorbox.js" type="text/javascript"></script>
<script src="<?php echo $rootUri; ?>/media/com_eshop/assets/js/eshop.js" type="text/javascript"></script>
<h1><?php echo Text::_('ESHOP_QUOTE_CART'); ?></h1>
<?php
if (isset($this->success))
{
	?>
	<div class="success"><?php echo $this->success; ?></div>
	<?php
}
?>
<?php
if (!count($this->quoteData))
{
	?>
	<div class="no-content"><?php echo Text::_('ESHOP_QUOTE_EMPTY'); ?></div>
	<?php
}
else
{
	?>
	<div class="quote-info">
		<table class="table table-responsive table-bordered table-striped">
			<thead>
				<tr>
					<th style="text-align: center;"><?php echo Text::_('ESHOP_REMOVE'); ?></th>
					<th style="text-align: center;"><?php echo Text::_('ESHOP_IMAGE'); ?></th>
					<th><?php echo Text::_('ESHOP_PRODUCT_NAME'); ?></th>
					<th><?php echo Text::_('ESHOP_QUANTITY'); ?></th>
					<?php
					if (EShopHelper::showPrice())
					{
						?>
						<th nowrap="nowrap"><?php echo Text::_('ESHOP_UNIT_PRICE'); ?></th>
						<th><?php echo Text::_('ESHOP_TOTAL'); ?></th>
						<?php
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
				$countProducts = 0;

				foreach ($this->quoteData as $key => $product)
				{
				    $countProducts++;
					$optionData = $product['option_data'];
					$viewProductUrl = Route::_(EShopRoute::getProductRoute($product['product_id'], EShopHelper::getProductCategory($product['product_id'])));
					?>
					<tr>
						<td class="eshop-center-text" style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_REMOVE'); ?>">
							<a class="eshop-remove-item-quote" id="<?php echo $key; ?>" style="cursor: pointer;">
								<img alt="<?php echo Text::_('ESHOP_REMOVE'); ?>" title="<?php echo Text::_('ESHOP_REMOVE'); ?>" src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/remove.png" />
							</a>
						</td>
						<td class="muted eshop-center-text" style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_IMAGE'); ?>">
							<a href="<?php echo $viewProductUrl; ?>">
								<img class="<?php echo $imgPolaroid; ?>" src="<?php echo $product['image']; ?>" />
							</a>
						</td>
						<td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_PRODUCT_NAME'); ?>">
							<a href="<?php echo $viewProductUrl; ?>">
								<?php echo $product['product_name']; ?>
							</a>
							<br />	
							<?php
							for ($i = 0; $n = count($optionData), $i < $n; $i++)
							{
								echo '- ' . $optionData[$i]['option_name'] . ': ' . htmlentities($optionData[$i]['option_value']) . (isset($optionData[$i]['sku']) && $optionData[$i]['sku'] != '' ? ' (' . $optionData[$i]['sku'] . ')' : '') . '<br />';
							}
							?>
						</td>
						<td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_QUANTITY'); ?>">
							<div class="<?php echo $inputAppendClass; ?> <?php echo $inputPrependClass; ?>">
								<span class="eshop-quantity">
									<input type="hidden" name="key[]" value="<?php echo $key; ?>" />
									<a onclick="quantityUpdate('+', 'quantity_quote_<?php echo $countProducts; ?>', <?php echo EShopHelper::getConfigValue('quantity_step', '1'); ?>);<?php echo EShopHelper::getConfigValue('update_quote_function', 'update_button') == 'quantity_button' ? 'updateQuote();' : ''; ?>" class="<?php echo $btnClass; ?> button-plus" id="quote_<?php echo $countProducts; ?>">+</a>
										<input type="text" class="eshop-quantity-value" value="<?php echo EShopHelper::escape($product['quantity']); ?>" name="quantity[]" id="quantity_quote_<?php echo $countProducts; ?>" />
									<a onclick="quantityUpdate('-', 'quantity_quote_<?php echo $countProducts; ?>', <?php echo EShopHelper::getConfigValue('quantity_step', '1'); ?>);<?php echo EShopHelper::getConfigValue('update_quote_function', 'update_button') == 'quantity_button' ? 'updateQuote();' : ''; ?>" class="<?php echo $btnClass; ?> button-minus" id="quote_<?php echo $countProducts; ?>">-</a>
								</span>
							</div>
						</td>
						<?php
						if (EShopHelper::showPrice())
						{
							?>
							<td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_UNIT_PRICE'); ?>">
								<?php
								if (!$product['product_call_for_price'])
								{
									if (EShopHelper::getConfigValue('include_tax_anywhere', '0'))
									{
										echo $this->currency->format($this->tax->calculate($product['price'], $product['product_taxclass_id'], EShopHelper::getConfigValue('tax')));
									}
									else
									{
										echo $this->currency->format($product['price']);
									}
								}
								?>
							</td>
							<td style="vertical-align: middle;" data-content="<?php echo Text::_('ESHOP_TOTAL'); ?>">
								<?php
								if (!$product['product_call_for_price'])
								{
									if (EShopHelper::getConfigValue('include_tax_anywhere', '0'))
									{
										echo $this->currency->format($this->tax->calculate($product['total_price'], $product['product_taxclass_id'], EShopHelper::getConfigValue('tax')));
									}
									else
									{
										echo $this->currency->format($product['total_price']);
									}
								}	
								?>
							</td>
							<?php
						}
						?>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
		if (EShopHelper::showPrice())
		{
			?>
			<div class="totals text-center" style="text-align: center">
				<?php
				foreach ($this->totalData as $data)
				{
					?>
						<div>
							<?php echo $data['title']; ?>:
							<?php echo $data['text']; ?>
						</div>
					<?php	
				}
				?>
			</div>
			<?php
		}
		?>
	</div>
	<div class="<?php echo $controlGroupClass; ?>" style="text-align: center;">
		<div class="controls">
			<a class="<?php echo $btnClass; ?> btn-danger" href="<?php echo Route::_(EShopHelper::getContinueShopingUrl()); ?>"><?php echo Text::_('ESHOP_CONTINUE_SHOPPING'); ?></a>
			<?php
			if (EShopHelper::getConfigValue('update_quote_function', 'update_button') == 'update_button')
			{
			    ?>
			    <button type="button" class="<?php echo $btnClass; ?> btn-info" onclick="updateQuote();" id="update-quote"><?php echo Text::_('ESHOP_UPDATE_QUOTE'); ?></button>
			    <?php
			}
			?>
			<a class="<?php echo $btnClass; ?> btn-success" href="<?php echo Route::_(EShopRoute::getViewRoute('quote')); ?>"><?php echo Text::_('ESHOP_QUOTE_FORM'); ?></a>
		</div>
	</div>
	<script type="text/javascript">
		//Function to update quote
		function updateQuote(key)
		{
			Eshop.jQuery(function($){
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					type: 'POST',
					url: siteUrl + 'index.php?option=com_eshop&task=quote.updates<?php echo EShopHelper::getAttachedLangLink(); ?>',
					data: $('.quote-info input[type=\'text\'], .quote-info input[type=\'hidden\']'),
					beforeSend: function() {
						$('#update-quote').attr('disabled', true);
						$('#update-quote').after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
					},
					complete: function() {
						$('#update-quote').attr('disabled', false);
						$('.wait').remove();
					},
					success: function() {
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=quote&layout=popout&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>',
							dataType: 'html',
							success: function(html) {
								$.colorbox({
									overlayClose: true,
									opacity: 0.5,
									width: '90%',
									maxWidth: '800px',
									href: false,
									html: html
								});
								$.ajax({
									url: siteUrl + 'index.php?option=com_eshop&view=quote&layout=mini&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>',
									dataType: 'html',
									success: function(html) {
										jQuery('#eshop-quote').html(html);
										jQuery('.eshop-content').hide();
									},
									error: function(xhr, ajaxOptions, thrownError) {
										alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
									}
								});
							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
				  	}
				});
			})
		}

		Eshop.jQuery(function($) {
			//Ajax remove quote item
			$('.eshop-remove-item-quote').bind('click', function() {
				var aTag = $(this);
				var id = aTag.attr('id');
				var siteUrl = '<?php echo EShopHelper::getSiteUrl(); ?>';
				$.ajax({
					type :'POST',
					url: siteUrl + 'index.php?option=com_eshop&task=quote.remove&key=' +  id + '&redirect=1<?php echo EShopHelper::getAttachedLangLink(); ?>',
					beforeSend: function() {
						aTag.attr('disabled', true);
						aTag.after('<span class="wait">&nbsp;<img src="<?php echo $rootUri; ?>/media/com_eshop/assets/images/loading.gif" alt="" /></span>');
					},
					complete: function() {
						aTag.attr('disabled', false);
						$('.wait').remove();
					},
					success : function() {
						$.ajax({
							url: siteUrl + 'index.php?option=com_eshop&view=quote&layout=popout&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>',
							dataType: 'html',
							success: function(html) {
								$.colorbox({
									overlayClose: true,
									opacity: 0.5,
									width: '90%',
									maxWidth: '800px',
									href: false,
									html: html
								});
								$.ajax({
									url: siteUrl + 'index.php?option=com_eshop&view=quote&layout=mini&format=raw<?php echo EShopHelper::getAttachedLangLink(); ?>&pt=<?php echo time(); ?>',
									dataType: 'html',
									success: function(html) {
										jQuery('#eshop-quote').html(html);
										jQuery('.eshop-content').hide();
									},
									error: function(xhr, ajaxOptions, thrownError) {
										alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
									}
								});
							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			});
		});
	</script>
	<?php
}