<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

OSMembershipHelperJquery::validateForm();
?>
<div id="osm-upgrade-options-page" class="osm-container">
    <?php
	if ($this->params->get('show_page_heading', 1))
	{
		if ($this->input->getInt('hmvc_call'))
		{
			$hTag = 'h2';
		}
		else
		{
			$hTag = 'h1';
		}
	?>
	    <<?php echo $hTag; ?> class="osm-page-title"><?php echo Text::_('OSM_UPGRADE_MEMBERSHIP'); ?></<?php echo $hTag; ?>>
	<?php
	}

	if (OSMembershipHelper::isValidMessage($this->params->get('intro_text')))
	{
	?>
		<div class="osm-description osm-page-intro-text <?php echo $this->bootstrapHelper->getClassMapping('clearfix'); ?>">
			<?php echo HTMLHelper::_('content.prepare', $this->params->get('intro_text')); ?>
		</div>
	<?php
	}

	if (count($this->upgradeRules))
	{
	?>
        <p class="osm-description"><?php echo Text::_('OSM_UPGRADE_MEMBERSHIP_DESCRIPTION'); ?></p>
        <form action="<?php echo Route::_('index.php?option=com_osmembership&task=register.process_upgrade_membership&Itemid=' . $this->Itemid, false, $this->config->use_https ? 1 : 0); ?>" method="post" name="osm_form_update_membership" id="osm_form_update_membership" autocomplete="off" class="form form-horizontal">
            <?php echo $this->loadCommonLayout('common/tmpl/upgrade_options.php'); ?>
            <div class="form-actions">
                <input type="submit" class="<?php echo $this->bootstrapHelper->getClassMapping('btn btn-primary'); ?>" value="<?php echo Text::_('OSM_PROCESS_UPGRADE'); ?>"/>
            </div>
        </form>
    <?php
	}
	else
	{
	?>
        <p class="text-info"><?php echo Text::_('OSM_NO_UPGRADE_OPTIONS_AVAILABLE'); ?></p>
    <?php
	}
	?>
</div>