<?php
use Joomla\CMS\Language\Text;
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
?>
<div id="donation-failure-page" class="row-fluid jd-container">
	<h1 class="title"><?php echo Text::_('JD_FAILURE'); ?></h1>
	<form id="os_form" class="form form-horizontal">
		<div class="control-group">
        	<div class="jd-message"><?php echo  Text::_('JD_FAILURE_MESSAGE'); ?></div>
    	</div>
		<?php
		if($this->reason != "")
		{
		?>
			<div class="control-group">					
				<label class="control-label">
					<?php echo Text::_('JD_REASON'); ?>
				</label>
				<div class="controls">
					<?php echo $this->reason; ?>
				</div>
			</div>
		<?php
		}	
		?>
		<div class="form-actions">			
			<?php echo Text::_('JD_CLICK');?>&nbsp;<a href="<?php echo $this->link ?>"><?php echo Text::_('JD_HERE'); ?></a>&nbsp;<?php echo Text::_('JD_TRY_AGAIN'); ?>			
		</div>
	</form>
</div>
