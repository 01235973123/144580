<?php
/**
 * @package     Joom Donation
 * @subpackage  Module Joom Donation Search
 *
 * @copyright   Copyright (C) 2010 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php // no direct access
$output = '<input name="filter_search" class="'.$input_style.' form-control" id="search_jd_box" maxlength="50"  type="text"  value="'.$text.'"  onblur="if(this.value==\'\') this.value=\''.$defaultText.'\';" onfocus="if(this.value==\''.$defaultText.'\') this.value=\'\';" />';
?>
<div class="jdsearch<?php echo $moduleclass_sfx; ?>">
<form id="jd_search" name="jd_search" action="<?php echo JRoute::_('index.php?option=com_jdonation&task=search&Itemid='.$itemId);  ?>" method="post">
	<table width="100%" class="search_table">
		<tr>
			<td>
				<div class="btn-group">
					<div class="input-group input-append btn-wrapper">
						<?php echo $output; ?>
						<button class="btn btn-primary button search_button" title="<?php echo JText::_('JD_SEARCH'); ?>" onclick="JDSearchData();" />
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
							  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
							</svg>
						</button>
					<div>
				</div>
			</td>
		</tr>
	</table>

	<script language="javascript">
		function JDSearchData()
		{
			var form = document.jd_search ;
			if (form.filter_search.value == '<?php echo $defaultText; ?>')
			{
				form.filter_search.value = '' ;
			}
			form.submit();
		}
	</script>
</form>
</div>