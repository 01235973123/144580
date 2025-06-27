<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	ESshop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
$translatable = $this->isMultilingualTranslable;
$requireNameInMultipleLanguages = EShopHelper::getConfigValue('require_name_in_multiple_languages', 1);
$defaultSiteLanguage =  ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

if (EShopHelper::isJoomla4())
{
    $tabApiPrefix = 'uitab.';
}
else
{
    HTMLHelper::_('behavior.tabstate');

    $tabApiPrefix = 'bootstrap.';
}
?>
<script type="text/javascript">	
	function sanitizeFieldName() 
	{
		var form = document.adminForm ;
		var name = form.name.value ;								
		name = name.replace(/[^a-zA-Z0-9_]*/ig, '');
		form.name.value = name;							
	}
	function buildValidationString()
	{
		(function($) {
            var rules = $('#validation_rule').val();            
            var currentRules = $('#validation_rules_string').val();
            var required = $("input[name='required']:checked").val();					                                                
            var newRules = [];                         
            if (required == 1)
            {
            	newRules.push('required');
            } 
            if (currentRules == '')
            {                                
                if (rules)
                {
                	rules = rules.join('|');
                	newRules.push(rules);    
                }                                
            }          
            else 
            {
                if (rules)
                {
                	for (var i = 0; i < rules.length; i++)
                    {
                        rule = rules[i];    
                        commaIndex = rule.indexOf(',');           
                        if (commaIndex == -1)
                        {
                             //This rule has no param, just push it to new rule
                        	newRules.push(rule);       	    
                        }
                        else 
                        {         
                            //This rule has param, try to find param value from the entered rule                                            	           
                            ruleName = rule.substr(0, commaIndex);
                         	ruleIndex = currentRules.indexOf(ruleName);
                         	if (ruleIndex ==  -1)
                         	{
                             	//There is no rule in this current rule yet, so use default param
                         		newRules.push(rule);                     		    
                         	} 
                         	else 
                         	{
                             	//Get the current rule param
                             	var remainingRules = currentRules.substr(ruleIndex);
                             	if (remainingRules.indexOf('|') != -1)
                             	{                             	
                             		remainingRules = remainingRules.substr(0, remainingRules.indexOf('|'));
                             	}                     
                             	if(remainingRules.indexOf(',') != -1)
                             	{
                                 	commaIndex = remainingRules.indexOf(',');
                                 	param = remainingRules.substr(commaIndex + 1);
                                 	newRules.push(ruleName + ',' + param);                              	
                             	}
                             	else 
                             	{
                             		newRules.push(rule);
                             	}
                         	}                		                  	   
                        }
                	}        
                }            	           
            }            
            var validationRulesString = newRules.join('|');
            if (validationRulesString.length)
            {
            	if (validationRulesString.charAt(validationRulesString.length - 1) == '|')
            	{
            		validationRulesString = validationRulesString.substr(0, validationRulesString.length - 1);
            	}	    	
            }
            $('#validation_rules_string').val(validationRulesString);
        })(jQuery);
	}	
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'field.cancel') {
			Joomla.submitform(pressbutton, form);
			return;				
		} else {
			//Validate the entered data before submitting
			<?php
			if ($translatable)
			{
				foreach ($this->languages as $language)
				{
					$langId = $language->lang_id;
					
					if ($requireNameInMultipleLanguages)
					{
					    ?>
    					if (document.getElementById('title_<?php echo $langId; ?>').value == '') {
    						alert("<?php echo Text::_('ESHOP_ENTER_TITLE'); ?>");
    						document.getElementById('title_<?php echo $langId; ?>').focus();
    						return;
    					}
    					if (jQuery('.field-value').hasClass('required-value'))
    					{
    						if (document.getElementById('values_<?php echo $langId; ?>').value == '') {
    							alert("<?php echo Text::_('ESHOP_ENTER_VALUE'); ?>");
    							document.getElementById('values_<?php echo $langId; ?>').focus();
    							return;
    						}	
    					}
    					<?php
                    }
                    else 
                    {
                       if ($language->lang_code == $defaultSiteLanguage)
                       {
                            ?>
        					if (document.getElementById('title_<?php echo $langId; ?>').value == '') {
        						alert("<?php echo Text::_('ESHOP_ENTER_TITLE'); ?>");
        						document.getElementById('title_<?php echo $langId; ?>').focus();
        						return;
        					}
        					if (jQuery('.field-value').hasClass('required-value'))
        					{
        						if (document.getElementById('values_<?php echo $langId; ?>').value == '') {
        							alert("<?php echo Text::_('ESHOP_ENTER_VALUE'); ?>");
        							document.getElementById('values_<?php echo $langId; ?>').focus();
        							return;
        						}	
        					}
        					<?php
                       }
                    }
				}
			}
			else
			{
			?>
				if (form.title.value == '') 
				{
					alert("<?php echo Text::_('ESHOP_ENTER_TITLE'); ?>");
					form.title.focus();
					return;
				}
				if(jQuery('.field-value').hasClass('required-value'))
				{
					if (form.values.value == '') 
					{
						alert("<?php echo Text::_('ESHOP_ENTER_VALUES'); ?>");
						form.values.focus();
						return;
					}
				}
				<?php
			}
			?>
			if (form.name.value == '')
			{
				alert("<?php echo Text::_('ESHOP_ENTER_NAME'); ?>");
				form.name.focus();
				return;
			}
			Joomla.submitform(pressbutton, form);
		}
	}

	function changeField(value)
	{
		if(value == 'List' || value == 'Checkboxes' || value == 'Radio')
		{
			jQuery('.field-value').addClass('required-value');
			jQuery('#required-value').before('<span class="eshop-show-hide required">*</span>');
		}
		else
		{
			jQuery('.field-value').removeClass('required-value');
			jQuery('.eshop-show-hide').remove();
		}
	}
	<?php if ($this->item->id) :?>
	(function($){
		$(document).ready(function(){
			fieldValue = $('#fieldtype').val();
			changeField(fieldValue);
		})
	})(jQuery)
	<?php endif;?>
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form form-horizontal">
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'field', array('active' => 'general-page'));
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'field', 'general-page', Text::_('ESHOP_GENERAL', true));
	
	if ($translatable)
	{
	    $rootUri = Uri::root();
	    echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'field-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));

		foreach ($this->languages as $language)
		{
			$langId = $language->lang_id;
			$langCode = $language->lang_code;
			$sef = $language->sef;
			echo HTMLHelper::_($tabApiPrefix . 'addTab', 'field-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . 'media/com_eshop/flags/' . $sef . '.gif" />');
            ?>
			<div class="control-group">
				<div class="control-label">
					<span class="required">*</span>
					<?php echo  Text::_('ESHOP_TITLE'); ?>
				</div>
				<div class="controls">
					<input class="input-xlarge form-control" type="text" name="title_<?php echo $langCode; ?>" id="title_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'title_'.$langCode} ?? ''; ?>" />
				</div>								
			</div>																												
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('ESHOP_DESCRIPTION'); ?>
				</div>
				<div class="controls">
					<textarea class="form-control" rows="5" cols="30" name="description_<?php echo $langCode; ?>"><?php echo $this->item->{'description_'.$langCode}; ?></textarea>
				</div>								
			</div>
			<div class="control-group">
				<div class="control-label">
					<span id="required-value"><?php echo Text::_('ESHOP_VALUES'); ?></span>
				</div>
				<div class="controls">
					<textarea class="form-control" class="field-value" rows="5" cols="30" name="values_<?php echo $langCode; ?>" id="values_<?php echo $langId; ?>"><?php echo $this->item->{'values_'.$langCode}; ?></textarea>
				</div>
			</div>			
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('ESHOP_DEFAULT_VALUES'); ?>
				</div>
				<div class="controls">
					<textarea class="form-control" rows="5" cols="30" name="default_values_<?php echo $langCode; ?>"><?php echo $this->item->{'default_values_'.$langCode}; ?></textarea>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('ESHOP_VALIDATION_ERORR_MESSAGE'); ?>
				</div>
				<div class="controls">
					<input class="input-xlarge form-control" type="text" name="validation_error_message_<?php echo $langCode; ?>" id="validation_error_message_<?php echo $langId; ?>" size="" maxlength="250" value="<?php echo $this->item->{'validation_error_message_'.$langCode} ?? ''; ?>" />											
				</div>
			</div>
			<?php
			echo HTMLHelper::_($tabApiPrefix . 'endTab');
		}
		echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
	}
	else
	{
	?>
		<div class="control-group">
			<div class="control-label">
				<span class="required">*</span>
				<?php echo  Text::_('ESHOP_TITLE'); ?>
			</div>
			<div class="controls">
				<input class="input-xlarge form-control" type="text" name="title" id="title" size="" maxlength="250" value="<?php echo $this->item->title ?? ''; ?>" />
			</div>								
		</div>																												
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_DESCRIPTION'); ?>
			</div>
			<div class="controls">
				<textarea class="form-control" rows="5" cols="30" name="description"><?php echo $this->item->description; ?></textarea>
			</div>								
		</div>
		<div class="control-group">
			<div class="control-label">
				<span id="required-value"><?php echo Text::_('ESHOP_VALUES'); ?></span>
			</div>
			<div class="controls">
				<textarea class="form-control" class="field-value" rows="5" cols="30" name="values"><?php echo $this->item->values; ?></textarea>
				<small>
					<?php echo Text::_('ESHOP_VALUES_EXPLAIN'); ?>
				</small>
			</div>
		</div>			
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_DEFAULT_VALUES'); ?>
			</div>
			<div class="controls">
				<textarea class="form-control" rows="5" cols="30" name="default_values"><?php echo $this->item->default_values; ?></textarea>
				<small>
					<?php echo Text::_('ESHOP_DEFAULT_VALUES_EXPLAIN'); ?>	
				</small>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('ESHOP_VALIDATION_ERORR_MESSAGE'); ?>
			</div>
			<div class="controls">
				<input class="input-xlarge form-control" type="text" name="validation_error_message" id="validation_error_message" size="" maxlength="250" value="<?php echo $this->item->validation_error_message ?? ''; ?>" />								
			</div>
		</div>
		<?php
	}
	
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'field', 'data-page', Text::_('ESHOP_DATA', true));
	?>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo  Text::_('ESHOP_NAME'); ?>
		</div>
		<div class="controls">
			<input class="input-large form-control" type="text" name="name" id="name" size="" maxlength="250" onchange="sanitizeFieldName();" value="<?php echo $this->item->name; ?>" <?php if ($this->item->is_core) echo 'disabled'; ?> />
		</div>							
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_FIELD_TYPE'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['fieldtype']; ?>
		</div>							
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_ADDRESS_TYPE'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['address_type']; ?>
		</div>							
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_REQUIRED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['required']; ?>
		</div>							
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_VALIDATION_RULE'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['validation_rule']; ?>
		</div>							
	</div>		
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_VALIDATION_STRING'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="validation_rules_string" id="validation_rules_string" size="" maxlength="250" value="<?php echo $this->item->validation_rules_string; ?>" />
		</div>							
	</div>	
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_SIZE'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="size" id="size" size="10" maxlength="250" value="<?php echo $this->item->size;?>" />
		</div>							
	</div>				
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_MAX_LENGTH'); ?>				
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="max_length" id="max_lenth" size="50" maxlength="250" value="<?php echo $this->item->max_length;?>" />
		</div>							
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_ROWS'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="rows" id="rows" size="10" maxlength="250" value="<?php echo $this->item->rows;?>" />
		</div>							
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_COLS'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="cols" id="cols" size="10" maxlength="250" value="<?php echo $this->item->cols;?>" />
		</div>
	</div>		
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('ESHOP_EXTRA'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="extra_attributes" id="extra" size="40" maxlength="250" value="<?php echo $this->item->extra_attributes;?>" />
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
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
	echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
	?>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="cid[]" value="<?php echo intval($this->item->id); ?>" />
	<?php
	if ($translatable)
	{
		foreach ($this->languages as $language)
		{
			$langCode = $language->lang_code;
			?>
			<input type="hidden" name="details_id_<?php echo $langCode; ?>" value="<?php echo intval($this->item->{'details_id_' . $langCode} ?? ''); ?>" />
			<?php
		}
	}
	elseif ($this->translatable)
	{
	?>
		<input type="hidden" name="details_id" value="<?php echo $this->item->{'details_id'} ?? ''; ?>" />
		<?php
	}
	?>
	<input type="hidden" name="task" value="" />
</form>