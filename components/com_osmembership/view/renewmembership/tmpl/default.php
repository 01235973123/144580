<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

OSMembershipHelperJquery::validateForm();
?>
<div id="osm-renew-options-page" class="osm-container">
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
	    <<?php echo $hTag; ?> class="osm-page-title"><?php echo Text::_('OSM_RENREW_MEMBERSHIP'); ?></<?php echo $hTag; ?>>
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
	?>
	<form action="<?php echo Route::_('index.php?option=com_osmembership&task=register.process_renew_membership&Itemid=' . $this->Itemid, false, $this->config->use_https ? 1 : 0); ?>" method="post" name="osm_form_renew" id="osm_form_renew" autocomplete="off" class="form form-horizontal">
		<p class="osm-description"><?php echo Text::_('OSM_RENREW_MEMBERSHIP_DESCRIPTION'); ?></p>
		<?php echo $this->loadCommonLayout('common/tmpl/renew_options.php'); ?>
	</form>
</div>