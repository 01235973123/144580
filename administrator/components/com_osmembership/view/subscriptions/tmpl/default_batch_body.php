<?php
/**
 * @package           Joomla
 * @subpackage        Membership Pro
 * @author            Tuan Pham Ngoc
 * @copyright         Copyright (C) 2012 - 2025 Ossolution Team
 * @license           GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$config  = OSMembershipHelper::getConfig();
$editor  = OSMembershipHelper::getEditor();
$message = OSMembershipHelper::getMessages();

$bootstrapHelper = OSMembershipHelperHtml::getAdminBootstrapHelper();

Factory::getApplication()
	->getDocument()
	->addScriptOptions('siteUrl', Uri::base(true));
?>
<div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?> form form-horizontal">
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_REPLY_TO_EMAIL'); ?>
        </div>
        <div class="controls">
            <input type="email" name="reply_to_email" value="" size="70" class="form-control" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_BCC_EMAIL'); ?>
        </div>
        <div class="controls">
            <input type="email" name="bcc_email" value="" size="70" class="form-control" />
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('OSM_FIRST_ATTACHMENT'); ?>
        </div>
        <div class="controls">
            <input type="file" name="attachment" value="" size="70" class="form-control" />
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_SECOND_ATTACHMENT'); ?>
		</div>
		<div class="controls">
			<input type="file" name="attachment2" value="" size="70" class="form-control" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_THIRD_ATTACHMENT'); ?>
		</div>
		<div class="controls">
			<input type="file" name="attachment3" value="" size="70" class="form-control" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_FOURTH_ATTACHMENT'); ?>
		</div>
		<div class="controls">
			<input type="file" name="attachment4" value="" size="70" class="form-control" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_EMAIL_SUBJECT'); ?>
		</div>
		<div class="controls">
			<input type="text" name="subject" value="" size="70" class="form-control" />
		</div>
	</div>
	<?php
		if (isset($this->lists['mm_template_id']))
		{
		?>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_MAIL_TEMPLATE'); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['mm_template_id']; ?>
				</div>
			</div>
		<?php
		}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_EMAIL_MESSAGE'); ?>
            <p><strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> : [PLAN_TITLE], [FIRST_NAME], [LAST_NAME], [ADDRESS] ...,[CREATED_DATE],[FROM_DATE], [TO_DATE]</strong></p>
		</div>
		<div class="controls">
			<?php echo $editor->display('message', $message->mass_mail_template, '100%', '250', '75', '10'); ?>
		</div>
	</div>
</div>

