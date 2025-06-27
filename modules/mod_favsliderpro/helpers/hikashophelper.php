<?php

/**
*   FavSlider Pro
*
*   Responsive and customizable Joomla!3 module
*
*   @version        1.1
*   @link           http://extensions.favthemes.com/favsliderpro
*   @author         FavThemes - http://www.favthemes.com
*   @copyright      Copyright (C) 2012-2017 FavThemes.com. All Rights Reserved.
*   @license        Licensed under GNU/GPLv3, see http://www.gnu.org/licenses/gpl-3.0.html
*/

// no direct access

defined('_JEXEC') or die;

class HikaShopHelper {

    private $db;

    public function __construct($hkmenuid = 0) {

        $this->db = JFactory::getDBO();
       

    }

    public function get_cat_products_cnt($catid) {

        $cnt = 0;

        $query = "SELECT COUNT(product_id) FROM #__hikashop_product_category WHERE category_id =" .(int)$catid;
        $this->db->setQuery($query);
        $cnt = $this->db->loadResult();

        return $cnt;

    }

    public function get_category_products($catid, $category_number_of_products, $category_order_by, $category_description_limit) {

        $products = array();

        $query = "SELECT a.product_name, a.product_description, a.product_id, c.price_value, d.currency_symbol FROM #__hikashop_product as a LEFT JOIN #__hikashop_product_category as b ON a.product_id = b.product_id LEFT JOIN #__hikashop_price as c ON c.price_product_id = a.product_id LEFT JOIN #__hikashop_currency as d ON d.currency_id = c.price_currency_id WHERE a.product_type = 'main' AND a.product_published = 1 AND b.category_id =" .(int)$catid;

        $orderby = '';

        switch ($category_order_by) {

            case "product-name-asc":
                $orderby = ' ORDER BY a.product_name ASC';
            break;

            case "product-name-desc":
                $orderby = ' ORDER BY a.product_name DESC';
            break;

            case "product-price-asc":
                $orderby = ' ORDER BY c.price_value ASC';
            break;

            case "product-price-desc":
                $orderby = ' ORDER BY c.price_value DESC';
            break;

        }

        $query .= $orderby;

        if ($category_number_of_products > 0) { $query .= ' LIMIT '.$category_number_of_products; }

        $this->db->setQuery($query);
        $results = $this->db->LoadObjectList();

        foreach ($results as $result) {

            $result->image = $this->get_image($result->product_id);
            $result->badge = $this->get_badge($result->product_id);
            $result->url = $this->get_url($result->product_id);
            $result->description_str = $this->get_description($result->product_description, $category_description_limit);
            $result->price_str = round($result->price_value,1).' '.$result->currency_symbol;

            $products[] = (array)$result;

        }

        return $products;

    }

    public function get_product_details($pid,$category_description_limit) {

        $product = array();

        $query = "SELECT a.product_name, a.product_description, a.product_id, b.price_value, c.currency_symbol FROM #__hikashop_product as a LEFT JOIN #__hikashop_price as b ON b.price_product_id = a.product_id LEFT JOIN #__hikashop_currency as c ON c.currency_id = b.price_currency_id WHERE a.product_id =" .(int)$pid;
        $this->db->setQuery($query);
        $results = $this->db->LoadObjectList();

        foreach ($results as $result) {

            $result->image = $this->get_image($result->product_id);
            $result->badge = $this->get_badge($result->product_id);
            $result->url = $this->get_url($result->product_id);
            $result->description_str = $this->get_description($result->product_description, $category_description_limit);
            $result->price_str = round($result->price_value,1).' '.$result->currency_symbol;

            $product = (array)$result;

        }

        return $product;

    }

    public function get_description($product_description,$limit) {

        if (strlen(strip_tags($product_description)) > $limit) {

            $description_str = preg_replace("/^(.{1,".$limit."})(\s.*|$)/s", '\\1...',   strip_tags($product_description));

        } else {

            $description_str = strip_tags($product_description);

        }

        return $description_str;

    }

    private function get_url($pid) {

        $query = "SELECT product_alias FROM #__hikashop_product WHERE product_id =" .(int)$pid;
        $this->db->setQuery($query);
        $alias = $this->db->loadResult();

        if (!empty($this->itemid) && is_numeric($this->itemid) && $this->itemid > 0) { $itemid_str = '&Itemid='.$this->itemid; } else { $itemid_str = ''; }

        $route = 'index.php?option=com_hikashop'.$itemid_str.'&ctrl=product&task=show&cid='.(int)$pid.'&name='.$alias;

        return JRoute::_($route);

    }

    private function get_badge($pid) {

        $badge = '';

        $categories = array();

        $query = "SELECT category_id FROM #__hikashop_product_category WHERE product_id =" .(int)$pid;
        $this->db->setQuery($query);
        $results = $this->db->LoadObjectList();

        foreach ($results as $result) {

            $categories[] = $result->category_id;

        }

        $query = "SELECT badge_name, badge_product_id, badge_category_id FROM #__hikashop_badge WHERE (badge_start = 0 OR badge_start <= ".time().") AND (badge_end = 0 OR badge_end >= ".time().") ORDER BY badge_id DESC";
        $this->db->setQuery($query);
        $results = $this->db->LoadObjectList();

        foreach ($results as $result) {

            $cp_array = explode(',',$result->badge_product_id);
            $cc_array = explode(',',$result->badge_category_id);

            if (in_array($pid,$cp_array)) { $badge = $result->badge_name; break; }

            foreach ($categories as $category) { if (in_array($category,$cc_array)) { $badge = $result->badge_name; break; } }

        }

        return $badge;

    }

    private function get_image($pid) {

        $image_url = '';

        $query = "SELECT config_value FROM #__hikashop_config WHERE config_namekey = 'uploadfolder'";
        $this->db->setQuery($query);
        $uploadfolder = $this->db->loadResult();

        $query = "SELECT file_path FROM #__hikashop_file WHERE file_type = 'product' AND file_ref_id = ".(int)$pid." ORDER BY file_id ASC LIMIT 1";
        $this->db->setQuery($query);
        $results = $this->db->LoadObjectList();

        foreach ($results as $result) {

            $image_url = $uploadfolder.$result->file_path;

        }

        if (empty($image_url)) {

            $query = "SELECT config_value FROM #__hikashop_config WHERE config_namekey = 'default_image'";
            $this->db->setQuery($query);
            $defaultimage = $this->db->loadResult();

            $image_url = $uploadfolder.$defaultimage;

        }

        return $image_url;

    }

}
