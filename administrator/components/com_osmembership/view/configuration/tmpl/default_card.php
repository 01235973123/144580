<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 *
 * @var MPFConfig                 $config
 * @var \Joomla\CMS\Editor\Editor $editor
 */

$tags = OSMembershipHelperHtml::getSupportedTags('card_layout');
?>
<fieldset class="form-horizontal options-form osm-mitem-form">
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('activate_member_card_feature', Text::_('OSM_ACTIVATE_MEMBER_CARD_FEATURE'), Text::_('OSM_ACTIVATE_MEMBER_CARD_FEATURE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo OSMembershipHelperHtml::getBooleanInput('activate_member_card_feature', $config->activate_member_card_feature); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('send_member_card_via_email', Text::_('OSM_SEND_MEMBER_CARD_VIA_EMAIL'), Text::_('OSM_SEND_MEMBER_CARD_VIA_EMAIL_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo OSMembershipHelperHtml::getBooleanInput('send_member_card_via_email', $config->send_member_card_via_email); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('card_page_orientation', Text::_('OSM_PAGE_ORIENTATION')); ?>
        </div>
        <div class="controls">
            <?php echo $this->lists['card_page_orientation']; ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('card_page_format', Text::_('OSM_PAGE_FORMAT')); ?>
        </div>
        <div class="controls">
            <?php echo $this->lists['card_page_format']; ?>
        </div>
    </div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['card_page_format' => 'custom']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('custom_page_format_width', Text::_('OSM_CUSTOM_PAGE_FORMAT_WIDTH')); ?>
		</div>
		<div class="controls">
			<input type="number" name="custom_page_format_width" value="<?php echo $this->config->custom_page_format_width; ?>" class="form-control">
		</div>
	</div>
	<div class="control-group" data-showon='<?php echo OSMembershipHelperHtml::renderShowon(['card_page_format' => 'custom']); ?>'>
		<div class="control-label">
			<?php echo OSMembershipHelperHtml::getFieldLabel('custom_page_format_height', Text::_('OSM_CUSTOM_PAGE_FORMAT_HEIGHT')); ?>
		</div>
		<div class="controls">
			<input type="number" name="custom_page_format_height" value="<?php echo $this->config->custom_page_format_height; ?>" class="form-control">
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('card_bg_image', Text::_('OSM_CARD_BG_IMAGE'), Text::_('OSM_CARD_BG_IMAGE_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo OSMembershipHelperHtml::getMediaInput($config->get('card_bg_image'), 'card_bg_image'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('card_bg_left', Text::_('OSM_CARD_BG_POSSITION')); ?>
        </div>
        <div class="controls">
            <?php echo Text::_('OSM_LEFT') . '    ';?><input type="number" name="card_bg_left" class="input-small form-control d-inline" value="<?php echo (int) $config->card_bg_left; ?>" />
            <?php echo Text::_('OSM_TOP') . '    ';?><input type="number" name="card_bg_top" class="input-small form-control d-inline" value="<?php echo (int) $config->card_bg_top; ?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('card_bg_width', Text::_('OSM_BG_SIZE')); ?>
        </div>
        <div class="controls">
            <?php echo Text::_('OSM_WIDTH') . '    ';?><input type="number" name="card_bg_width" class="input-small form-control d-inline" value="<?php echo (int) $config->get('card_bg_width'); ?>" />
            <?php echo Text::_('OSM_HEIGHT') . '    ';?><input type="number" name="card_bg_height" class="input-small form-control d-inline" value="<?php echo (int) $config->get('card_bg_height'); ?>" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('show_download_member_card', Text::_('OSM_SHOW_DOWNLOAD_MEMBER_CARD'), Text::_('OSM_SHOW_DOWNLOAD_MEMBER_CARD_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php echo OSMembershipHelperHtml::getBooleanInput('show_download_member_card', $config->show_download_member_card); ?>
        </div>
    </div>
    <?php
        echo $this->text('qrcode_image_width', 'QRCODE_IMAGE_WIDTH', 'QRCODE_IMAGE_WIDTH_EXPLAIN');
        echo $this->text('qrcode_image_height', 'QRCODE_IMAGE_HEIGHT', 'QRCODE_IMAGE_HEIGHT_EXPLAIN');
    ?>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('card_layout', Text::_('OSM_CARD_LAYOUT')); ?>
            <p class="osm-available-tags">
                <?php echo Text::_('OSM_AVAILABLE_TAGS'); ?>:<br /> <strong><?php echo '[' . implode(']<br /> [', $tags) . ']'; ?></strong>
            </p>
        </div>
        <div class="controls">
            <?php echo $editor->display('card_layout', $config->card_layout, '100%', '550', '75', '8') ;?>
        </div>
    </div>
</fieldset>