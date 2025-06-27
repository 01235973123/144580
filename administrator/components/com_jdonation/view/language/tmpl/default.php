<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('Translation management'), 'generic.png');
ToolbarHelper::save('save');
ToolbarHelper::cancel('cancel');
?>
<form action="index.php?option=com_jdonation&view=language&lang=<?php echo $this->lang; ?>&site=<?php echo $this->site; ?>" method="post" name="adminForm" id="adminForm">
    <div class="row-fluid">
        <div id="filter-bar" class="btn-toolbar">
            <div class="filter-search btn-group pull-left">
				<div class="input-group input-append">
					<input type="text" name="search" id="search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->search); ?>" class="hasTooltip form-control input-medium" />
					<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
					<button type="button" class="btn btn-secondary hasTooltip" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('search').value='';this.form.submit();"><span class="icon-remove"></span></button>
				</div>
            </div>

            <div class="btn-group pull-right hidden-phone" style="gap:10px;">
                <?php echo $this->lists['site']; ?>
                <?php echo $this->lists['filter_language']; ?>
                <?php echo $this->lists['filter_item']; ?>
            </div>
        </div>
    </div>
	<table class="adminlist table table-bordered" style="width:100%">
        <thead>
        <tr>
            <th class="key" style="width:5%; text-align: center;background-color:#B53526;color:white;""><?php echo Text::_('#'); ?></th>
            <th class="key" style="width:20%; text-align: left;background-color:#B53526;color:white;">Key</th>
            <th class="key" style="width:40%; text-align: left;background-color:#B53526;color:white;">Original</th>
            <th class="key" style="width:40%; text-align: left;background-color:#B53526;color:white;">Translation</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="4" style="text-align:center;">
                <?php
                echo $this->pagNa->getListFooter();
                ?>
            </td>
        </tr>
        </tfoot>
		<?php
             $j = 0;
			$original = $this->trans['en-GB'][$this->item] ;
			$trans = $this->trans[$this->lang][$this->item] ;
			foreach ($original as  $key=>$value) {
                $j++;
                if($j % 2 == 0){
                    $bgcolor = "#efefef";
                }else{
                    $bgcolor = "#ffffff";
                }
			?>
				<tr>
                    <td class="key" style="text-align:center;background-color:<?php echo $bgcolor;?>">
                        <?php echo $j + $this->pagNa->limitstart;?>.
                    </td>
                    <td class="key" style="text-align: left;background-color:<?php echo $bgcolor;?>"><?php echo $key; ?></td>
                    <td style="text-align: left;background-color:<?php echo $bgcolor;?>"><?php echo $value; ?></td>
					<td>
						<?php
							if (isset($trans[$key])) {
								$translatedValue = $trans[$key];
								$missing = false ; 	
							} else {
								$translatedValue = $value;
								$missing = true ;
							}							
						?>
						<input type="hidden" name="keys[]" value="<?php echo $key; ?>" />
                        <input type="hidden" name="items[]" value="<?php echo $j-1;?>" />
						<input type="text" id="item_<?php echo $j-1; ?>" name="item_<?php echo $j-1; ?>" class="ilarge input-xxlarge form-control" size="100" value="<?php echo $translatedValue; ; ?>" />
						<?php
							if ($missing) {
							?>
								<span style="color:red;">*</span>
							<?php	
							}							
						?>
					</td>					
				</tr>
			<?php	
			}
		?>
	</table>
	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
