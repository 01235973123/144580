<?php

/**
 * @version        5.4
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.core');
HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

$translatable			= Multilanguage::isEnabled() && count($this->languages);
$bootstrapHelper		= $this->bootstrapHelper;
$rowFluidClass			= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class			= $bootstrapHelper->getClassMapping('span12');
$span7Class				= $bootstrapHelper->getClassMapping('span7');
$span6Class				= $bootstrapHelper->getClassMapping('span6');
$span5Class				= $bootstrapHelper->getClassMapping('span5');
$controlGroupClass		= $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass		= $bootstrapHelper->getClassMapping('control-label');
$controlsClass			= $bootstrapHelper->getClassMapping('controls');
?>
<script type="text/javascript">
    Joomla.submitbutton = function(pressbutton)
    {
		if (pressbutton == 'cancel')
        {
            Joomla.submitform( pressbutton );
			return;
		}
        else
        {
            var form = document.adminForm;
			//Should validate the information here
			if (form.name.value == "")
            {
				alert("<?php echo Text::_("JD_ENTER_CUSTOM_FIELD_NAME"); ?>");
                form.name.focus();
                return false;
			}
			if (form.title.value == "")
            {
				alert("<?php echo Text::_("JD_ENTER_CUSTOM_FIELD_TITLE"); ?>");
				form.title.focus();
				return false;
			}
			Joomla.submitform( pressbutton );
		}
	}

    function sanitizeFieldName()
    {
        var form = document.adminForm ;
        var name = form.name.value ;
        var oldValue = name ;
        name = name.replace('jd_','');
        while(name.indexOf('  ') >=0)
        {
            name = name.replace('  ', ' ');
        }
        while(name.indexOf(' ') >=0)
        {
            name = name.replace(' ', '_');
        }
        form.name.value=  name;
    }
    (function($)
    {
        $(document).ready(function(){
            var validateEngine = <?php  echo DonationHelper::validateEngine(); ?>;
            $("input[name='required']").bind( "click", function() {
                var change = 1;
                validateRules(change);
            });
            $( "#datatype_validation" ).bind( "change", function() {
                var change = 1;
                validateRules(change);
            });

            $( "#fieldtype" ).bind( "change", function() {
                changeFiledType($(this).val());
            });

            changeFiledType('<?php echo $this->item->fieldtype;  ?>');
            function validateRules(change)
            {
                var validationString;
                if ($("input[name='name']").val() == 'email')
                {
                    //Hardcode the validation rule for email
                    validationString = 'validate[required,custom[email],ajax[ajaxEmailCall]]';
                }
                else
                {
                    var validateType = parseInt($('#datatype_validation').val());
                    validationString = validateEngine[validateType];
                    var required = $("input[name='required']:checked").val();
                    if (required == 1)
                    {
                        if (validationString == '')
                        {
                            validationString = 'validate[required]';
                        }
                        else
                        {
                            if (validationString.indexOf('required') == -1)
                            {
                                validationString = [validationString.slice(0, 9), 'required,', validationString.slice(9)].join('');
                            }
                        }
                    }
                    else
                    {
                        if (validationString == 'validate[required]')
                        {
                            validationString = '';
                        }
                        else
                        {
                            validationString = validationString.replace('validate[required', 'validate[');
                        }
                    }
                }
                if(change == 1)
                {
                    $("input[name='validation_rules']").val(validationString);
                }
            }
            validateRules();
            function changeFiledType(fieldType)
            {
                if (fieldType == '')
                {
                    $('div.jd-field').hide();
                }
                else
                {
                    var cssClass = '.jd-' + fieldType.toLowerCase();
                    $('div.jd-field').show();
                    $('div.jd-field').not(cssClass).hide();
                }
            }
        });
    })(jQuery);

</script>
<form action="index.php?option=com_jdonation&view=field" method="post" name="adminForm" id="adminForm" class="form-horizontal">

<?php
if (DonationHelper::isJoomla4())
{
	$tabApiPrefix = 'uitab.';

	Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('showon');
}
else
{
	$tabApiPrefix = 'bootstrap.';

	HTMLHelper::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
}

if ($translatable)
{
?>
	<?php echo HTMLHelper::_($tabApiPrefix.'startTabSet', 'field', array('active' => 'general-page')); ?>
	<?php echo HTMLHelper::_($tabApiPrefix.'addTab', 'field', 'general-page', Text::_('JD_GENERAL', true)); ?>
<?php
}
?>
	<div class="<?php echo $rowFluidClass; ?>">
		<div class="<?php echo $span6Class; ?>">
			<fieldset class="form-horizontal options-form">
                <legend><?php echo Text::_('JD_GENERAL'); ?></legend>
				<?php
					if ($this->fieldCampaign)
					{
					?>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_CAMPAIGN'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php echo DonationHelper::getChoicesJsSelect($this->lists['campaign_id']) ; ?>
						</div>
					</div>
					<?php	
					}
				?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo DonationHelperHtml::getFieldLabel('name', Text::_('JD_NAME'), Text::_('JD_FIELD_NAME_REQUIREMENT')); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<input class="input-large form-control ilarge" type="text" name="name" id="name" size="50" maxlength="250" value="<?php echo $this->item->name;?>" onchange="sanitizeFieldName();" <?php if ($this->item->is_core) echo 'readonly="readonly"' ; ?>/>
					</div>
				</div>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo  Text::_('JD_TITLE'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<input class="input-large form-control ilarge" type="text" name="title" id="title" size="50" maxlength="250" value="<?php echo $this->item->title;?>" />
					</div>
				</div>
				
				<div class="<?php echo $controlGroupClass; ?> jd-field jd-list">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_MULTIPLE'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo DonationHelperHtml::showCheckboxfield('multiple',(int)$this->item->multiple); ?>
					</div>
				</div>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_('JD_REQUIRE'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php echo DonationHelperHtml::showCheckboxfield('required',(int)$this->item->required); ?>
					</div>
				</div>
				
				
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo  Text::_('JD_DESCRIPTION'); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<textarea rows="5" cols="50" name="description" class="form-control"><?php echo $this->item->description;?></textarea>
					</div>
				</div>
				
				<?php
					if (isset($this->lists['field_mapping']))
					{
					?>
						<div class="<?php echo $controlGroupClass; ?>">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo  Text::_('JD_FIELD_MAPPING'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<?php echo $this->lists['field_mapping']; ?>
							</div>
						</div>
					<?php
					}
				?>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_PUBLISHED'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php echo DonationHelperHtml::showCheckboxfield('published',(int)$this->item->published); ?>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<div class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_ACCESS'); ?>
						</div>
						<div class="<?php echo $controlsClass; ?>">
							<?php
							echo $this->lists['access'];
							?>
						</div>
					</div>
				</fieldset>

				<fieldset class="form-horizontal options-form">
					<legend><?php echo Text::_('JD_DISPLAY_SETTINGS'); ?></legend>
				
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo  Text::_('JD_CSS_CLASS'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input class="text_area input-large form-control ilarge" type="text" name="css_class" id="css_class" size="10" maxlength="250" value="<?php echo $this->item->css_class;?>" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo DonationHelperHtml::getFieldLabel('container_size', Text::_('JD_FIELD_CONTAINER_SIZE'),  Text::_('JD_FIELD_CONTAINER_SIZE_EXPLAIN')); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php
							echo $this->lists['container_size'];
							?>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo  Text::_('JD_CONTAINER_CLASS'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input class="text_area ilarge input-large form-control" type="text" name="container_class" id="container_class" value="<?php echo $this->item->container_class;?>" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?> jd-field jd-text jd-textarea">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo  Text::_('JD_PLACE_HOLDER'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input class="text_area form-control input-large ilarge" type="text" name="place_holder" id="place_holder" size="50" maxlength="250" value="<?php echo $this->item->place_holder;?>" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?> jd-field jd-text jd-checkboxes jd-radio jd-list">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo  Text::_('JD_SIZE'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input class="text_area input-mini form-control" type="text" name="size" id="size" size="10" maxlength="250" value="<?php echo (int)$this->item->size;?>" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?> jd-field jd-text jd-textarea">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo  Text::_('JD_MAX_LENGTH'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input class="text_area input-mini form-control" type="text" name="max_length" id="max_lenth" size="50" maxlength="250" value="<?php echo (int)$this->item->max_length;?>" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?> jd-field jd-textarea">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo  Text::_('JD_ROWS'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input class="text_area input-mini form-control" type="text" name="rows" id="rows" size="10" maxlength="250" value="<?php echo (int)$this->item->rows;?>" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?> jd-field jd-textarea">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo  Text::_('JD_COLS'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input class="text_area input-mini form-control" type="text" name="cols" id="cols" size="10" maxlength="250" value="<?php echo (int)$this->item->cols;?>" />
						</div>
					</div>
					
				</fieldset>
			</div>
			<div class="<?php echo $span6Class; ?>">
				<fieldset class="form-horizontal options-form">
					<legend><?php echo Text::_('JD_FIELD_SETTINGS'); ?></legend>
				
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_FIELD_TYPE'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php echo $this->lists['fieldtype']; ?>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_DEFAULT_VALUES'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<textarea rows="5" cols="50" name="default_values" class="form-control"><?php echo $this->item->default_values; ?></textarea>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_DATATYPE_VALIDATION') ; ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php echo $this->lists['datatype_validation']; ?>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?> validation-rules">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo DonationHelperHtml::getFieldLabel('validation_rules', Text::_('JD_VALIDATION_RULES'), Text::_('JD_VALIDATION_RULES_EXPLAIN')); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input type="text" class="input-xlarge form-control ilarge" size="50" name="validation_rules" value="<?php echo $this->item->validation_rules ; ?>" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?> jd-field jd-list jd-checkboxes jd-radio jd-sql">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_VALUES'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<textarea rows="5" cols="50" name="values" class="form-control"><?php echo $this->item->values; ?></textarea>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?> validation-rules">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo DonationHelperHtml::getFieldLabel('input_mask', Text::_('JD_INPUT_MASK'), Text::_('JD_INPUT_MASK_EXPLAIN')); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input type="text" class="input-xlarge form-control ilarge" size="50" name="input_mask" value="<?php echo $this->item->input_mask ; ?>" />
						</div>
					</div>
				</fieldset>
			</div>
		</div>
	<?php
	if ($translatable)
	{
        echo HTMLHelper::_($tabApiPrefix.'endTab');
        echo HTMLHelper::_($tabApiPrefix.'addTab', 'field', 'translation-page', Text::_('JD_TRANSLATION', true));
	?>
		<div class="tab-pane" id="translation-page">
			<div class="tab-content">
                <?php echo HTMLHelper::_($tabApiPrefix.'startTabSet', 'field-language', array('active' => 'translation-page-0')); ?>
				<?php
				$i = 0;
				foreach ($this->languages as $language)
				{
					$sef = $language->sef;
                    echo HTMLHelper::_($tabApiPrefix.'addTab', 'field-language', 'translation-page-'.$i, $language->title . ' <img src="' . Uri::root() . 'media/com_jdonation/flags/' . $sef . '.png" />');
					?>

                        <div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo  Text::_('JD_TITLE'); ?>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <input class="input-xlarge form-control ilarge" type="text" name="title_<?php echo $sef; ?>" id="title_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'title_'.$sef}; ?>" />
                            </div>
                        </div>
                        <div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_DESCRIPTION'); ?>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <textarea rows="5" cols="50" name="description_<?php echo $sef; ?>" class="form-control"><?php echo $this->item->{'description_'.$sef};?></textarea>
                            </div>
                        </div>
                        <div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_VALUES'); ?>
                                <BR />
                                <small><?php echo Text::_('JD_EACH_ITEM_LINE'); ?></small>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <textarea rows="5" cols="50" name="values_<?php echo $sef; ?>" class="form-control"><?php echo $this->item->{'values_'.$sef}; ?></textarea>
                            </div>
                        </div>
                        <div class="<?php echo $controlGroupClass; ?>">
                            <label class="<?php echo $controlLabelClass; ?>">
                                <?php echo Text::_('JD_DEFAULT_VALUES'); ?>
                                <BR />
                                <small><?php echo Text::_('JD_EACH_ITEM_LINE'); ?></small>
                            </label>
                            <div class="<?php echo $controlsClass; ?>">
                                <textarea rows="5" cols="50" name="default_values_<?php echo $sef; ?>" class="form-control"><?php echo $this->item->{'default_values_'.$sef}; ?></textarea>
                                <?php echo Text::_('JD_EACH_ITEM_LINE'); ?>
                            </div>
                        </div>
						<div class="<?php echo $controlGroupClass; ?> jd-field jd-text jd-textarea">
							<label class="<?php echo $controlLabelClass; ?>">
								<?php echo  Text::_('JD_PLACE_HOLDER'); ?>
							</label>
							<div class="<?php echo $controlsClass; ?>">
								<input class="text_area input-large form-control ilarge" type="text" name="place_holder_<?php echo $sef; ?>" id="place_holder" size="50" maxlength="250" value="<?php echo $this->item->{'place_holder_'.$sef}; ?>" />
							</div>
						</div>
					<?php
					$i++;
                    echo HTMLHelper::_($tabApiPrefix.'endTab');
				}
                echo HTMLHelper::_($tabApiPrefix.'endTabSet');
				?>
			</div>
		</div>
	<?php
        echo HTMLHelper::_($tabApiPrefix.'endTab');
        echo HTMLHelper::_($tabApiPrefix.'endTabSet');
	}
	?>

	<input type="hidden" name="id" value="<?php echo (int)$this->item->id; ?>" />
	<input type="hidden" name="task" value="" />	
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
