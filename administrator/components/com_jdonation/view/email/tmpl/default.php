<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$config = DonationHelper::getConfig();
?>
<form action="index.php?option=com_jdonation&view=email" method="post" name="adminForm" id="adminForm" class="form form-horizontal emaildetailsform">
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('JD_SUBJECT'); ?>
		</div>
		<div class="controls">
			<?php echo $this->item->subject; ?>
		</div>
	</div>	
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('JD_EMAIL'); ?>
		</div>
		<div class="controls">
			<?php echo $this->item->email; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('JD_SENT_TO'); ?>
		</div>
		<div class="controls">
			<?php
				if ($this->item->sent_to == 1)
				{
					echo Text::_('JD_ADMIN');
				}
				else
				{
					echo Text::_('JD_DONORS');
				}
			?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('JD_SENT_AT'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::_('date', $this->item->sent_at, $config->date_format.' H:i'); ?>
		</div>
	</div>				
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('JD_MESSAGE'); ?>
		</div>
		<div class="controls">
			<?php echo $this->item->body; ?>
		</div>
	</div>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value="" />
</form>
