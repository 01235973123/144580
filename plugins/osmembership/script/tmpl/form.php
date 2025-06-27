<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Layout variables
 * -----------------
 * @var   OSMembershipTablePlan $row
 */

$params = new Registry($row->params);

if (PluginHelper::isEnabled('editors', 'codemirror'))
{
	$editorPlugin = 'codemirror';
}
elseif (PluginHelper::isEnabled('editor', 'none'))
{
	$editorPlugin = 'none';
}
else
{
	$editorPlugin = null;
}

if ($editorPlugin)
{
    $editor = Editor::getInstance($editorPlugin);
}
else
{
    $editor = null;
}
?>
<p class="text-warning">
    This feature is usually used by developers that know how to write PHP code. Please only use this
    feature if you know how to program in PHP and understand what you are doing.
</p>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('subscription_store_script', Text::_('OSM_SUBSCRIPTION_STORED_SCRIPT'), Text::_('OSM_SUBSCRIPTION_STORED_SCRIPT_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <?php
            if ($editor)
            {
                echo $editor->display('subscription_store_script', $params->get('subscription_store_script'), '100%', '250', '75', '10', false, null, null, null, ['syntax' => 'php']);
            }
            else
            {
            ?>
                <textarea rows="10" cols="70" class="form-control" name="subscription_store_script"><?php echo $params->get('subscription_store_script'); ?></textarea>
            <?php
            }
        ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('subscription_active_script', Text::_('OSM_SUBSCRIPTION_ACTIVE_SCRIPT'), Text::_('OSM_SUBSCRIPTION_ACTIVE_SCRIPT_EXPLAIN')); ?>
    </div>
    <div class="controls">
	    <?php
	    if ($editor)
	    {
		    echo $editor->display('subscription_active_script', $params->get('subscription_active_script'), '100%', '250', '75', '10', false, null, null, null, ['syntax' => 'php']);
	    }
	    else
	    {
		?>
            <textarea rows="10" cols="70" class="form-control" name="subscription_active_script"><?php echo $params->get('subscription_active_script'); ?></textarea>
		<?php
	    }
	    ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('subscription_expired_script', Text::_('OSM_SUBSCRIPTION_EXPIRED_SCRIPT'), Text::_('OSM_SUBSCRIPTION_EXPIRED_SCRIPT_EXPLAIN')); ?>
    </div>
    <div class="controls">
	    <?php
	    if ($editor)
	    {
		    echo $editor->display('subscription_expired_script', $params->get('subscription_expired_script'), '100%', '250', '75', '10', false, null, null, null, ['syntax' => 'php']);
	    }
	    else
	    {
		?>
            <textarea rows="10" cols="70" class="form-control" name="subscription_expired_script"><?php echo $params->get('subscription_expired_script'); ?></textarea>
		<?php
	    }
	    ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('subscription_update_script', Text::_('OSM_SUBSCRIPTION_UPDATE_SCRIPT'), Text::_('OSM_SUBSCRIPTION_UPDATE_SCRIPT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php
		if ($editor)
		{
			echo $editor->display('subscription_update_script', $params->get('subscription_update_script'), '100%', '250', '75', '10', false, null, null, null, ['syntax' => 'php']);
		}
		else
		{
		?>
            <textarea rows="10" cols="70" class="form-control" name="subscription_update_script"><?php echo $params->get('subscription_update_script'); ?></textarea>
		<?php
		}
		?>
	</div>
</div>