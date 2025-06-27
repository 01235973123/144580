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

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
EShopHelper::chosen();
?>
<script type="text/javascript">	
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'geozone.cancel') {
			Joomla.submitform(pressbutton, form);
			return;				
		} else {
			//Validate the entered data before submitting
			if (form.geozone_name.value == '') {
				alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
				form.geozone_name.focus();
				return;
			}
			Joomla.submitform(pressbutton, form);
		}
	}
	var rowIndexGeozones = <?php echo count($this->zoneToGeozones); ?>;
	var defaultCountry = <?php echo $this->countryId; ?>;
	function addGeoZone() {
		var countryId = 'country' + rowIndexGeozones;
		var zoneId = 'zone' + rowIndexGeozones;	
		html = '<tr id="zone-to-geo-zone-row' + rowIndexGeozones + '">';
		html += '<td style="text-align: center;"><select class="input-xlarge form-select" name="country_id[]"  id="country' + rowIndexGeozones + '" onchange="Eshop.updateStateList(this.value,\''+ zoneId + '\')">';
		html += countriesOptions;
		html += '</select></td>';
		html += '<td style="text-align: center;"><select class="input-xlarge form-select" name="zone[]" style="width: 220px;" id="zone' + rowIndexGeozones + '"><option value="0"><?php echo Text::_('ESHOP_ALL_ZONES'); ?></option></select></td>';
		html += '<td style="text-align: center;"><input type="button" onclick="jQuery(\'#zone-to-geo-zone-row' + rowIndexGeozones + '\').remove();" class="btn btn-small btn-primary" value="<?php echo Text::_('ESHOP_BTN_REMOVE'); ?>"></td>';
		html += '</tr>';
		jQuery('#zone-to-geo-zone').append(html);
		jQuery('#country' + rowIndexGeozones).attr('value', defaultCountry);
		Eshop.updateStateList(defaultCountry, zoneId);
		rowIndexGeozones++;
	}
	var rowIndex = <?php echo count($this->geozonePostcodes); ?>;
	function addPostcode() {
		html = '<tr id="postcode-to-geo-zone-row' + rowIndex + '">';
		html += '<td style="text-align: center;"><input type="text" class="input-medium form-control" name="start_postcode[]" value="" /></td>';
		html += '<td style="text-align: center;"><input type="text" class="input-medium form-control" name="end_postcode[]" value="" /></td>';
		html += '<td style="text-align: center;"><input type="button" value="<?php echo Text::_('ESHOP_BTN_REMOVE'); ?>" class="btn btn-small btn-primary" onclick="jQuery(\'#postcode-to-geo-zone-row' + rowIndex + '\').remove();">';
		html += '</tr>';
		jQuery('#postcode-to-geo-zone').append(html);
		rowIndex++;
	}
	<?php
	if (count($this->zoneToGeozones)) {
		$index = 0;
 		?>
		jQuery(document).ready(function() {
 		  	<?php
			foreach ($this->zoneToGeozones as $zoneToGeozone) {
 		  		$zoneSelectTagId = 'zone'.$index;
				?>
				Eshop.updateStateList(<?php echo $zoneToGeozone->country_id; ?>, '<?php echo $zoneSelectTagId ?>');
				jQuery('#<?php echo $zoneSelectTagId; ?>').val(<?php echo $zoneToGeozone->zone_id; ?>);
 		  		<?php
 		  			$index++;
 		  		}
 		  	?>
 		});
	<?php
 	}
	?>
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form form-horizontal">
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('ESHOP_GEOZONE_DETAILS'); ?></legend>
		<div class="control-group">
			<div class="control-label">
				<span class="required">*</span>
				<?php echo  Text::_('ESHOP_NAME'); ?>
			</div>
			<div class="controls">
				<input  class="input-xlarge form-control" type="text" name="geozone_name" id="geozone_name" maxlength="250" value="<?php echo $this->item->geozone_name; ?>" />
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo  Text::_('ESHOP_DESCRIPTION'); ?>
			</div>
			<div class="controls">
				<textarea class="form-control" rows="5" cols="40" name="geozone_desc"><?php echo $this->item->geozone_desc; ?></textarea>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_PUBLISHED'); ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['published']; ?>
			</div>
		</div>
	</fieldset>
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('ESHOP_GEOZONE_ZONES'); ?></legend>
		<span class="help"><?php echo Text::_('ESHOP_GEOZONE_ZONES_HELP'); ?></span><br />
		<table class="adminlist table table-bordered" style="text-align: center;">
			<thead>
				<tr>
					<th class="title"  style="text-align: center;"><?php echo Text::_('ESHOP_COUNTRY')?></th>
					<th class="title"  style="text-align: center;"><?php echo Text::_('ESHOP_ZONE')?></th>
					<th class="title">&nbsp;</th>
				</tr>
			</thead>
			<tbody id="zone-to-geo-zone">
			<?php
			$rowIndex = 0;
			if(count($this->zoneToGeozones))
			{
				foreach ($this->zoneToGeozones as $zoneToGeozone)
				{
				?>					
				<tr id="zone-to-geo-zone-row<?php echo $rowIndex?>">
					<td style="text-align: center;">
						<?php echo HTMLHelper::_('select.genericlist', $this->countryOptions, 'country_id[]', ' class="input-xlarge form-select" onchange="Eshop.updateStateList(this.value, \'zone'.$rowIndex.'\')"', 'id', 'name', $zoneToGeozone->country_id, 'country'.$rowIndex); ?>
					</td>
					<td style="text-align: center;">
						<select name="zone[]" class="input-xlarge form-select" style="width: 220px;" id="zone<?php echo $rowIndex; ?>"></select>
					</td>
					<td style="text-align: center;">
						<input type="button" value="<?php echo Text::_('ESHOP_BTN_REMOVE'); ?>" class="btn btn-small btn-primary" onclick="jQuery('#zone-to-geo-zone-row<?php echo $rowIndex; ?>').remove();">
					</td>
				</tr>						
				<?php
					$rowIndex++;	
				}					
			}
			?>
			</tbody>
			<tfoot>
				<tr>
	              <td colspan="3" class="left"><input class="btn btn-small btn-primary" type="button" name="add" value="<?php echo Text::_('ESHOP_BTN_ADD')?>" onclick="addGeoZone();" ></td>
	            </tr>	
			</tfoot>									
		</table>
	</fieldset>
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('ESHOP_GEOZONE_COUNTRIES'); ?></legend>
		<span class="help"><?php echo Text::_('ESHOP_GEOZONE_COUNTRIES_HELP'); ?></span><br />
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_GEOZONE_COUNTRIES_LIST'); ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['countries_list']; ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_GEOZONE_INCLUDE_COUNTRIES'); ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['include_countries']; ?>
				<span class="help"><?php echo Text::_('ESHOP_GEOZONE_INCLUDE_COUNTRIES_HELP'); ?></span><br />
			</div>
		</div>
	</fieldset>
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('ESHOP_GEOZONE_POSTCODES'); ?></legend>
		<span class="help"><?php echo Text::_('ESHOP_GEOZONE_POSTCODES_HELP'); ?></span><br />
		<table class="adminlist table table-bordered" style="text-align: center;">
			<thead>
				<tr>
					<th class="title"  style="text-align: center;"><?php echo Text::_('ESHOP_POSTCODE_START')?></th>
					<th class="title"  style="text-align: center;"><?php echo Text::_('ESHOP_POSTCODE_END')?></th>
					<th class="title">&nbsp;</th>
				</tr>
			</thead>
			<tbody id="postcode-to-geo-zone">
			<?php
			$rowIndex = 0;
			if(count($this->geozonePostcodes))
			{
				foreach ($this->geozonePostcodes as $geozonePostcode)
				{
				?>					
				<tr id="postcode-to-geo-zone-row<?php echo $rowIndex?>">
					<td style="text-align: center;">
						<input type="text" class="input-medium form-control" name="start_postcode[]" value="<?php echo $geozonePostcode->start_postcode; ?>" />
					</td>
					<td style="text-align: center;">
						<input type="text" class="input-medium form-control" name="end_postcode[]" value="<?php echo $geozonePostcode->end_postcode; ?>" />
					</td>
					<td style="text-align: center;">
						<input type="button" value="<?php echo Text::_('ESHOP_BTN_REMOVE'); ?>" class="btn btn-small btn-primary" onclick="jQuery('#postcode-to-geo-zone-row<?php echo $rowIndex; ?>').remove();">
						<input type="hidden" name="geozonepostcode_id[]" value="<?php echo $geozonePostcode->id; ?>" />						
					</td>
				</tr>						
				<?php
					$rowIndex++;	
				}					
			}
			?>
			</tbody>
			<tfoot>
				<tr>
	              <td colspan="3" class="left"><input class="btn btn-small btn-primary" type="button" name="add" value="<?php echo Text::_('ESHOP_BTN_ADD')?>" onclick="addPostcode();" ></td>
	            </tr>	
			</tfoot>									
		</table>
	</fieldset>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="cid[]" value="<?php echo intval($this->item->id); ?>" />
	<input type="hidden" name="task" value="" />
</form>