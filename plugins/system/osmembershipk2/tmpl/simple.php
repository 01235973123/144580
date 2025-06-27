<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * -----------------
 * @var   \Joomla\Registry\Registry $params
 * @var array                       $planArticles
 */
?>

<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('k2_item_categories', Text::_('OSM_K2_CATEGORIES'), Text::_('OSM_K2_CATEGORIES_SIMPLE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" class="form-control" name="k2_item_categories" value="<?php echo $params->get('k2_item_categories', ''); ?>"/>
	</div>
</div>

<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('k2_item_ids', Text::_('OSM_K2_ITEMS'), Text::_('OSM_K2_ITEMS_SIMPLE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" class="form-control" name="k2_item_ids" value="<?php echo implode(',', $planArticles); ?>"/>
	</div>
</div>