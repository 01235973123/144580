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

$bootstrapHelper        = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
$rowFluidClass          = $bootstrapHelper->getClassMapping('row-fluid');
$span                   = intval(12 / $productsPerRow);
$spanClass              = $bootstrapHelper->getClassMapping('span' . $span);
?>
<div class="eshop-product<?php echo $params->get( 'moduleclass_sfx' ); ?>">
	<?php
	if (EshopHelper::isValidMessage($headerText))
	{
		?>
		<div class="eshop-header"><?php echo $headerText; ?></div>
		<?php
	}
	?>
	<div id="products-list" class="<?php echo $rowFluidClass; ?>">
		<?php
		foreach ($items as $key => $product)
		{
			$count = 0;
			?>
			<div class="eshop-product col-6 <?php echo $spanClass ; ?>">
				<?php
				echo EShopHtmlHelper::loadCommonLayout('common/product.php', array (
					'product'	=> $product,
					'params'		=> $params
				));
				?>
			</div>
			<?php
			if (($key + 1) % $productsPerRow == 0 && $key < (count($items) - 1))
			{
			    echo '</div><div class="' . $rowFluidClass . '">';
			}
		}
	?>
	</div>
	<?php
	if (EshopHelper::isValidMessage($footerText))
	{
		?>
		<div class="eshop-footer"><?php echo $footerText; ?></div>
		<?php
	}
	?>
</div>