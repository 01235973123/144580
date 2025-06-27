<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
?>
<input type="text" name="<?php echo $this->config->get('honeypot_fieldname', 'osm_my_own_website_name'); ?>" value="" autocomplete="off" class="<?php echo $this->config->get('honeypot_field_css_class', 'osm-invisible-to-visitors'); ?>" />
<input type="hidden" name="<?php echo OSMembershipHelper::getHashedFieldName(); ?>" value="<?php echo time(); ?>" />

