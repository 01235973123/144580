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

use Joomla\CMS\Table\Table;

/**
 * Config Table class
 */
class ConfigEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_configs', 'id', $db);
	}
}

/**
 * Address Table class
 */
class AddressEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_addresses', 'id', $db);
	}
}

/**
 * Customer Table class
 */
class CustomerEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_customers', 'id', $db);
	}
}

/**
 * Order Table class
 */
class OrderEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_orders', 'id', $db);
	}
}

/**
 * Order Products Table class
 */
class OrderproductsEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_orderproducts', 'id', $db);
	}
}

/**
 * Order Options Table class
 */
class OrderoptionsEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_orderoptions', 'id', $db);
	}
}

/**
 * Order Downloads Table class
 */
class OrderdownloadsEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_orderdownloads', 'id', $db);
	}
}

/**
 * Order Totals Table class
 */
class OrdertotalsEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_ordertotals', 'id', $db);
	}
}

/**
 * Quote Table class
 */
class QuoteEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_quotes', 'id', $db);
	}
}

/**
 * Question Table class
 */
class QuestionEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_questions', 'id', $db);
	}
}

/**
 * Quote Products Table class
 */
class QuoteproductsEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_quoteproducts', 'id', $db);
	}
}

/**
 * Quote Options Table class
 */
class QuoteoptionsEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_quoteoptions', 'id', $db);
	}
}

/**
 * Quote Totals Table class
 */
class QuotetotalsEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_quotetotals', 'id', $db);
	}
}

/**
 * Coupon History Table class
 */
class CouponhistoryEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_couponhistory', 'id', $db);
	}
}

/**
 * Voucher History Table class
 */
class VoucherhistoryEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_voucherhistory', 'id', $db);
	}
}

/**
 * Review Table class
 */
class ReviewEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_reviews', 'id', $db);
	}
}

/**
 * Custom field Table class
 */
class FieldEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_fields', 'id', $db);
	}
}

/**
 * Wishlist Table class
 */
class WishlistEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_wishlists', 'id', $db);
	}
}

/**
 * Notify Table class
 */
class NotifyEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_notify', 'id', $db);
	}
}

/**
 * Geo Zone Postcode Table class
 */
class GeozonepostcodesEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_geozonepostcodes', 'id', $db);
	}
}

/**
 * Cart Table class
 */
class CartEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_carts', 'id', $db);
	}
}

/**
 * Product details Table class
 */
class OptiondetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_optiondetails', 'id', $db);
	}
}

/**
 * Product details Table class
 */
class ProductdetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_productdetails', 'id', $db);
	}
}

/**
 * Category details Table class
 */
class CategorydetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_categorydetails', 'id', $db);
	}
}

/**
 * Attribute group details Table class
 */
class AttributegroupdetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_attributegroupdetails', 'id', $db);
	}
}

/**
 * Attribute details Table class
 */
class AttributedetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_attributedetails', 'id', $db);
	}
}

/**
 * Customer group details Table class
 */
class CustomergroupdetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_customergroupdetails', 'id', $db);
	}
}

/**
 * Download details Table class
 */
class DownloaddetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_downloaddetails', 'id', $db);
	}
}

/**
 * Field details Table class
 */
class FielddetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_fielddetails', 'id', $db);
	}
}

/**
 * Label details Table class
 */
class LabeldetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_labeldetails', 'id', $db);
	}
}

/**
 * Length details Table class
 */
class LengthdetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_lengthdetails', 'id', $db);
	}
}

/**
 * Manufacturer details Table class
 */
class ManufacturerdetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_manufacturerdetails', 'id', $db);
	}
}

/**
 * Message details Table class
 */
class MessagedetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_messagedetails', 'id', $db);
	}
}

/**
 * Order status details Table class
 */
class OrderstatusdetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_orderstatusdetails', 'id', $db);
	}
}

/**
 * Stock status details Table class
 */
class StockstatusdetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_stockstatusdetails', 'id', $db);
	}
}

/**
 * Weight details Table class
 */
class WeightdetailEshop extends Table
{

	/**
	 * Constructor
	 *
	 * @param
	 * object Database connector object
	 *
	 * @since 1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__eshop_weightdetails', 'id', $db);
	}
}
