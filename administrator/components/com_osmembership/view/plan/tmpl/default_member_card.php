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

?>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('activate_member_card_feature', Text::_('OSM_ACTIVATE_MEMBER_CARD_FEATURE'), Text::_('OSM_ACTIVATE_MEMBER_CARD_FEATURE_EXPLAIN')); ?>
    </div>
    <div class="controls">
		<?php echo OSMembershipHelperHtml::getBooleanInput('activate_member_card_feature', $this->item->activate_member_card_feature); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo Text::_('OSM_CARD_BG_IMAGE'); ?>
    </div>
    <div class="controls">
        <?php echo OSMembershipHelperHtml::getMediaInput($this->item->card_bg_image, 'card_bg_image'); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo Text::_('OSM_CARD_LAYOUT'); ?>
    </div>
    <div class="controls">
        <?php echo $editor->display('card_layout', $this->item->card_layout, '100%', '550', '75', '8') ;?>
    </div>
</div>
