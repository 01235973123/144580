<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core')
	->addInlineStyle('.hasTip{display:block !important}');

/* @var OSMembershipHelperBootstrap $bootstrapHelper */
$bootstrapHelper = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$clearFix          = $bootstrapHelper->getClassMapping('clearfix');

$editor = Editor::getInstance($this->config->get('editor') ?: Factory::getApplication()->get('editor'));
?>
<div id="osm-invite-group-members" class="osm-container">
    <h1 class="osm-page-title"><?php echo Text::_('OSM_INVITE_GROUP_MEMBERS'); ?></h1>
    <div class="btn-toolbar" id="btn-toolbar">
        <?php echo Toolbar::getInstance('toolbar')->render(); ?>
    </div>
    <form method="post" name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_osmembership&Itemid=' . $this->Itemid, false, 0); ?>" autocomplete="off" class="<?php echo $bootstrapHelper->getClassMapping('form form-horizontal'); ?>">
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlLabelClass; ?>">
                <?php echo  Text::_('OSM_PLAN') ?>
                <span class="required">*</span>
            </div>
            <div class="<?php echo $controlsClass; ?>">
                <?php echo $this->lists['plan_id']; ?>
            </div>
        </div>
	    <div class="<?php echo $controlGroupClass; ?>">
		    <div class="<?php echo $controlLabelClass; ?>">
			    <?php echo OSMembershipHelperHtml::getFieldLabel('emails', Text::_('OSM_INVITE_EMAILS'), Text::_('OSM_INVITE_EMAILS_EXPLAIN')); ?>
			    <span class="required">*</span>
		    </div>
		    <div class="<?php echo $controlsClass; ?>">
			    <textarea name="emails" class="form-control input-xxlarge" rows="10" cols="70"><?php echo $this->input->getString('emails'); ?></textarea>
		    </div>
	    </div>
	    <div class="<?php echo $controlGroupClass; ?>">
		    <div class="<?php echo $controlLabelClass; ?>">
			    <?php echo  Text::_('OSM_SUBJECT') ?>
			    <span class="required">*</span>
		    </div>
		    <div class="<?php echo $controlsClass; ?>">
			    <input type="text" name="subject" class="form-control input-xxlarge" value="<?php echo $this->input->getString('subject', $this->message->get('invite_group_members_email_subject')); ?>" />
		    </div>
	    </div>
	    <div class="<?php echo $controlGroupClass; ?>">
		    <div class="<?php echo $controlLabelClass; ?>">
			    <?php echo  Text::_('OSM_EMAIL_MESSAGE') ?>
			    <span class="required">*</span>
		    </div>
		    <div class="<?php echo $controlsClass; ?>">
			    <?php echo $editor->display('message', $this->input->get('message', $this->message->get('invite_group_members_message'), 'raw'), '100%', '250', '75', '10'); ?>
		    </div>
	    </div>
	    <input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
