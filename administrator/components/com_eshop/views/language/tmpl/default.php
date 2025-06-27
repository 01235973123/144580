<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_( 'ESHOP_TRANSLATION'), 'generic.png');
ToolbarHelper::addNew('new_item', 'ESHOP_NEW_ITEM');
ToolbarHelper::apply('language.save');
ToolbarHelper::cancel('language.cancel');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'new_item') {
			Joomla.newLanguageItem();
			return;				
		} else {
			Joomla.submitform(pressbutton, form);
		}
	}
	Joomla.newLanguageItem = function() {
		table = document.getElementById('lang_table');
		row = table.insertRow(1);
		cell0  = row.insertCell(0);
		cell0.innerHTML = '<input type="text" name="extra_keys[]" class="input-xlarge form-control" size="50" />';
		cell1 = row.insertCell(1);
		cell2 = row.insertCell(2);
		cell2.innerHTML = '<input type="text" name="extra_values[]" class="input-xxlarge form-control" size="100" />';
	}
</script>
<form action="index.php?option=com_eshop&view=language" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar<?php if (EShopHelper::isJoomla4()) echo ' js-stools-container-filters-visible'; ?>">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="search" class="input-xlarge form-control" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->lists['search'];?>" class="hasTooltip form-control" onchange="document.adminForm.submit();" />	
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn<?php if (EShopHelper::isJoomla4()) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn<?php if (EShopHelper::isJoomla4()) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<?php
                echo $this->lists['lang'];
                echo $this->lists['item'];
				?>
			</div>
		</div>
		<div class="clearfix"></div>
    	<table class="adminlist table table-striped" id="lang_table">
    		<thead>
    			<tr>
    				<th class="text_left" width="20%">
    					<?php echo Text::_( 'ESHOP_KEY' ); ?>
    				</th>
    				<th class="text_left" width="35%">
    					<?php echo Text::_( 'ESHOP_ORIGINAL' ); ?>
    				</th>
    				<th class="text_left" width="35%">
    					<?php echo Text::_( 'ESHOP_TRANSLATION' ); ?>
    				</th>
    			</tr>
    		</thead>
    		<tfoot>
    			<tr>
    				<td colspan="4"><?php echo $this->pagination->getListFooter(); ?></td>
    			</tr>
    		</tfoot>
    		<tbody>
    			<?php
    			$item = $this->item;
    			if (strpos($item, 'admin.') !== false)
    				$item = substr($item, 6);
    			$original = $this->trans['en-GB'][$item];
    			$trans = $this->trans[$this->lang][$item];
    			$search = $this->lists['search'];
    			foreach ($trans as $key => $value)
    			{
    				$show = true;
    				if (isset($trans[$key]))
    				{
    					$translatedValue = $trans[$key];
    					$missing = false;
    				}
    				else
    				{
    					$translatedValue = $value;
    					$missing = true;
    				}								
    				?>
    				<tr>
    					<td><?php echo $key; ?></td>
    					<td><?php echo $value; ?></td>
    					<td>
    						<input type="hidden" name="keys[]" value="<?php echo $key; ?>" />
    						<input type="text" name="<?php echo $key; ?>" class="input-xxlarge form-control" value="<?php echo $translatedValue != '' ? htmlspecialchars($translatedValue) : ''; ?>" />
    						<?php
    							if ($missing)
    							{
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
    		</tbody>
    	</table>
	</div>
	<input type="hidden" name="option" value="com_eshop" />	
	<input type="hidden" name="task" value="" />			
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>