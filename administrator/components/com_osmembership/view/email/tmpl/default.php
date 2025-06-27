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

$config = OSMembershipHelper::getConfig();
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_SUBJECT'); ?>
		</div>
		<div class="controls">
			<?php echo $this->item->subject; ?>
		</div>
	</div>	
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_EMAIL'); ?>
		</div>
		<div class="controls">
			<?php echo $this->item->email; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_SENT_TO'); ?>
		</div>
		<div class="controls">
			<?php
				if ($this->item->sent_to == 1)
				{
					echo Text::_('OSM_ADMIN');
				}
				else
				{
					echo Text::_('OSM_SUBSCRIBER');
				}
			?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('OSM_SENT_AT_TIME'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('date', $this->item->sent_at, $config->date_format . ' H:i'); ?>
		</div>
	</div>				
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_MESSAGE'); ?>
		</div>
		<div class="controls">
			<?php echo $this->item->body; ?>
		</div>
	</div>
	<?php echo HTMLHelper::_('form.token'); ?>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value="" />
</form>