<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class plgEshopAlsobought extends CMSPlugin
{
	/**
  * Application object.
  *
  * @var \Joomla\CMS\Application\CMSApplication
  */
 protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param   object    $subject
	 * @param   Registry  $config
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 *
	 *
	 * @param   EshopTableProduct  $row
	 *
	 * @return array|void
	 */
	public function onProductDisplay($row)
	{
		$alsoBoughtProducts = EShopHelper::getAlsoBoughtProducts($row->id);

		$relatedImageSizeFunction = EShopHelper::getConfigValue('related_image_size_function', 'resizeImage');

		if (count($alsoBoughtProducts))
		{
			// Related products images resize
			for ($i = 0; $n = count($alsoBoughtProducts), $i < $n; $i++)
			{
				$alsoBoughtProduct = $alsoBoughtProducts[$i];

				if ($alsoBoughtProduct->product_image && is_file(JPATH_ROOT . '/media/com_eshop/products/' . $alsoBoughtProduct->product_image))
				{
					if (EShopHelper::getConfigValue('product_use_image_watermarks'))
					{
						$watermarkImage = EShopHelper::generateWatermarkImage(
							JPATH_ROOT . '/media/com_eshop/products/' . $alsoBoughtProduct->product_image
						);
						$productImage   = $watermarkImage;
					}
					else
					{
						$productImage = $alsoBoughtProduct->product_image;
					}

					$thumbImage = call_user_func_array(['EShopHelper', $relatedImageSizeFunction],
						[
							$productImage,
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_related_width'),
							EShopHelper::getConfigValue('image_related_height'),
						]);
				}
				else
				{
					$thumbImage = call_user_func_array(['EShopHelper', $relatedImageSizeFunction],
						[
							'no-image.png',
							JPATH_ROOT . '/media/com_eshop/products/',
							EShopHelper::getConfigValue('image_related_width'),
							EShopHelper::getConfigValue('image_related_height'),
						]);
				}

				if ($relatedImageSizeFunction == 'notResizeImage')
				{
					$alsoBoughtProduct->thumb_image = Uri::base(true) . '/media/com_eshop/products/' . $thumbImage;
				}
				else
				{
					$alsoBoughtProduct->thumb_image = Uri::base(true) . '/media/com_eshop/products/resized/' . $thumbImage;
				}
			}
		}

		$bootstrapHelper = new EShopHelperBootstrap(EShopHelper::getConfigValue('twitter_bootstrap_version'));
		$currency        = EShopCurrency::getInstance();
		$tax             = new EShopTax();

		return [
			'name'  => 'also-bought-products',
			'title' => Text::_('ESHOP_ALSO_BOUGHT'),
			'form'  => EShopHtmlHelper::loadCommonLayout(
				'plugins/products.php',
				['products' => $alsoBoughtProducts, 'bootstrapHelper' => $bootstrapHelper, 'currency' => $currency, 'tax' => $tax]
			),
		];
	}
}
