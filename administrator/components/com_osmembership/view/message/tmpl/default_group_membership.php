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
        <?php echo OSMembershipHelperHtml::getFieldLabel('new_group_member_email_subject', Text::_('OSM_NEW_GROUP_MEMBER_EMAIL_SUBJECT'), Text::_('OSM_NEW_GROUP_MEMBER_EMAIL_SUBJECT_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <input type="text" name="new_group_member_email_subject" class="form-control" value="<?php echo $this->item->new_group_member_email_subject; ?>" size="40" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo Text::_('OSM_NEW_GROUP_MEMBER_EMAIL_BODY'); ?>
        <p class="osm-available-tags">
            <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [GROUP_ADMIN_NAME], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT]</strong>
        </p>
    </div>
    <div class="controls">
        <?php echo $editor->display('new_group_member_email_body', $this->item->new_group_member_email_body, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo Text::_('OSM_JOIN_GROUP_FROM_MESSAGE'); ?>
        <p class="osm-available-tags">
            <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> [PLAN_TITLE], [GROUP_ADMIN_NAME]:</strong>
        </p>
    </div>
    <div class="controls">
        <?php echo $editor->display('join_group_form_message', $this->item->join_group_form_message, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo Text::_('OSM_JOIN_GROUP_COMPLETE_MESSAGE'); ?>
        <p class="osm-available-tags">
            <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [GROUP_ADMIN_NAME], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT]</strong>
        </p>
    </div>
    <div class="controls">
        <?php echo $editor->display('join_group_complete_message', $this->item->join_group_complete_message, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('join_group_user_email_subject', Text::_('OSM_JOIN_GROUP_USER_EMAIL_SUBJECT'), Text::_('OSM_JOIN_GROUP_USER_EMAIL_SUBJECT_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <input type="text" name="join_group_user_email_subject" class="form-control" value="<?php echo $this->item->join_group_user_email_subject; ?>" size="40" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo Text::_('OSM_JOIN_GROUP_USER_EMAIL_BODY'); ?>
        <p class="osm-available-tags">
            <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [GROUP_ADMIN_NAME], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT]</strong>
        </p>
    </div>
    <div class="controls">
        <?php echo $editor->display('join_group_user_email_body', $this->item->join_group_user_email_body, '100%', '250', '75', '8') ;?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo OSMembershipHelperHtml::getFieldLabel('join_group_group_admin_email_subject', Text::_('OSM_JOIN_GROUP_GROUP_ADMIN_EMAIL_SUBJECT'), Text::_('OSM_JOIN_GROUP_GROUP_ADMIN_EMAIL_SUBJECT_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <input type="text" name="join_group_group_admin_email_subject" class="form-control" value="<?php echo $this->item->join_group_group_admin_email_subject; ?>" size="40" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo Text::_('OSM_JOIN_GROUP_GROUP_ADMIN_EMAIL_BODY'); ?>
        <p class="osm-available-tags">
            <strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :[SUBSCRIPTION_DETAIL], [GROUP_ADMIN_NAME], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT]</strong>
        </p>
    </div>
    <div class="controls">
        <?php echo $editor->display('join_group_group_admin_email_body', $this->item->join_group_group_admin_email_body, '100%', '250', '75', '8') ;?>
    </div>
</div>