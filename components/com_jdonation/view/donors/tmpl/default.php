<?php

/**
 * @version        5.6.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$percent            =  100 - DonationHelper::getConfigValue('percent_commission') ;
$bootstrapHelper    = $this->bootstrapHelper;
$rowFluidClass   	= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class   	    = $bootstrapHelper->getClassMapping('span12');
$span8Class   	    = $bootstrapHelper->getClassMapping('span8');
$span6Class   	    = $bootstrapHelper->getClassMapping('span6');
$span4Class   	    = $bootstrapHelper->getClassMapping('span4');
$extralayoutCss     = ((int)$this->config->layout_type == 1 ? "dark_layout" : "");
?>
<div class="<?php echo $rowFluidClass; ?>">
    <div class="<?php echo $span8Class;?>">
        <h1 class="jd-title"><?php echo $this->page_heading;?></h1>
    </div>
	<?php
	if(Factory::getApplication()->getIdentity()->authorise('core.manage','com_jdonation'))
	{
	?>
		<div class="<?php echo $span4Class; ?> donorstoolbar">
			<a href="index.php?option=com_jdonation&task=export" class="btn btn-primary">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
			  <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
			  <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
			</svg>
			&nbsp;<?php echo Text::_('JD_EXPORT_DONORS')?></a>
		</div>
	<?php
	}	
	?>
</div>
<form action="<?php echo Route::_('index.php?option=com_jdonation&view=donors&Itemid='.$this->Itemid); ?>" method="post" name="adminForm" id="adminForm">
	<div class="<?php echo $rowFluidClass." ".$extralayoutCss;?>">
		<div class="<?php echo $span12Class;?>" id="items_list">
			<?php
			echo $this->loadTemplate('items');
			?>
		</div>
	</div>
	<?php
	if($this->totalPages > 1)
	{
	?>
	<div class="<?php echo $rowFluidClass?>">
		<div class="<?php echo $span12Class;?>">
			<button class="btn btn-primary loadingbtn" id="load-more">
				<span id="buttonText"><?php echo Text::_('JD_LOAD_MORE')?></span>
				<div id="spinner" class="spinner"></div>
			</button>
		</div>
	</div>
	<?php
	}	
	?>
</form>

<script type="text/javascript"
>
const loadButton = document.getElementById('load-more');
const spinner = document.getElementById('spinner');
const buttonText = document.getElementById('buttonText');

jQuery(document).ready(function() {
    var page = 1; 
    jQuery('#load-more').click(function() {
		event.preventDefault();
        page++;
		if(page > <?php echo (int)$this->totalPages?>)
		{
			loadButton.disabled = true;
			return;
		}
		loadButton.classList.add('loading');
		spinner.style.display = 'block';
		loadButton.disabled = true;
        jQuery.ajax({
            url: '<?php echo Uri::root(); ?>index.php?option=com_jdonation&view=donors&ajax=1&format=raw&tmpl=component&Itemid=<?php echo $this->Itemid;?>', 
            type: 'GET', 
            data: { page: page }, 
            success: function(response) {
                if(response) {
                    jQuery('#items_list').append(response);
					loadButton.classList.remove('loading');spinner.style.display = 'none';loadButton.disabled = false;
                } else {
                    jQuery('#load-more').hide();
					loadButton.classList.remove('loading');spinner.style.display = 'none';loadButton.disabled = false;
                }
            },
            error: function() {
            }
        });
    });
});
</script>