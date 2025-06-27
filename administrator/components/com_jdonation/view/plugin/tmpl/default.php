<?php
/**
 * @version        5.4.2
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
if (version_compare(JVERSION, '4.0.0-dev', 'lt'))
{
	HTMLHelper::_('behavior.tooltip');
}
$editor				= Editor::getInstance(Factory::getApplication()->get('editor'));
$bootstrapHelper	= DonationHelperHtml::getAdminBootstrapHelper();
$rowFluidClass		= $bootstrapHelper->getClassMapping('row-fluid');
$span7Class			= $bootstrapHelper->getClassMapping('span7');
$span5Class			= $bootstrapHelper->getClassMapping('span5');
$controlGroupClass	= $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass	= $bootstrapHelper->getClassMapping('control-label');
$controlsClass		= $bootstrapHelper->getClassMapping('controls');
$translatable		= Multilanguage::isEnabled() && count($this->languages);
$languages			= $this->languages;
$tabApiPrefix 		= 'uitab.';
?>

<script language="javascript" type="text/javascript">

</script>
<form action="index.php?option=com_jdonation&view=plugin" method="post" name="adminForm" id="adminForm" class="form-horizontal">
<div class="<?php echo $rowFluidClass;?>">
<div class="<?php echo $span7Class;?>">
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('JD_PLUGIN_DETAIL'); ?></legend>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo  Text::_('JD_NAME'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->item->name ; ?>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo  Text::_('JD_TITLE'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<input class="text_area form-control" type="text" name="title" id="title" size="40" maxlength="250" value="<?php echo $this->item->title;?>" />
			</div>
		</div>					
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('JD_AUTHOR'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<input class="text_area form-control" type="text" name="author" id="author" size="40" maxlength="250" value="<?php echo $this->item->author;?>" />
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('JD_CREATION_DATE'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->item->creation_date; ?>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('JD_COPYRIGHT') ; ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->item->copyright; ?>
			</div>
		</div>	
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('JD_LICENSE'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->item->license; ?>
			</div>
		</div>							
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('JD_AUTHOR_EMAIL'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->item->author_email; ?>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('JD_AUTHOR_URL'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->item->author_url; ?>
			</div>
		</div>				
		<div class="<?php echo $controlGroupClass; ?>">
			<div class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('JD_VERSION'); ?>
			</div>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->item->version; ?>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('JD_DESCRIPTION'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->item->description; ?>
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
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('JD_PUBLISHED'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo DonationHelperHtml::showCheckboxfield('published',(int)$this->item->published); ?>
			</div>
		</div>
	</fieldset>
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('JD_PLUGIN_MESSAGE'); ?></legend>
		<?php
		if ($translatable)
		{
			echo HTMLHelper::_($tabApiPrefix.'startTabSet', 'translation', array('active' => 'general-page'));
				echo HTMLHelper::_($tabApiPrefix.'addTab', 'translation', 'general-page', Text::_('JD_GENERAL', true));
		}
		?>
				<?php echo Text::_('JD_PLUGIN_MESSAGE_EXPLAIN'); ?>
				<?php
				echo $editor->display( 'payment_description',  $this->item->payment_description , '95%', '450', '75', '20' ,false);
				?>
		<?php
		if ($translatable)
		{
		?>
		<?php echo HTMLHelper::_($tabApiPrefix.'endTab'); ?>
			<?php echo HTMLHelper::_($tabApiPrefix.'addTab', 'translation', 'translation-page', Text::_('JD_TRANSLATION', true)); ?>		
							
					<?php	
						$i = 0;
						$activate_sef = $languages[0]->sef;
						echo HTMLHelper::_($tabApiPrefix.'startTabSet', 'languagetranslation', array('active' => 'translation-page-'.$activate_sef));
						foreach ($languages as $language)
						{												
							$sef = $language->sef;
							echo HTMLHelper::_($tabApiPrefix.'addTab', 'languagetranslation',  'translation-page-'.$sef, '<img src="'.Uri::root().'media/com_jdonation/flags/'.$sef.'.png" />');
						?>		
							<?php
							echo $editor->display( 'payment_description_'.$sef,  $this->item->{'payment_description_'.$sef} , '95%', '450', '75', '20' ,false);
							?>								
						<?php				
							echo HTMLHelper::_($tabApiPrefix.'endTab');
							$i++;		
						}
						echo HTMLHelper::_($tabApiPrefix.'endTabSet');
					?>
			<?php
			echo HTMLHelper::_($tabApiPrefix.'endTab');
			echo HTMLHelper::_($tabApiPrefix.'endTabSet');
		}
		
		?>
	</fieldset>		
</div>						
<div class="<?php echo $span5Class;?>">
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('JD_PLUGIN_PARAMETERS'); ?></legend>
		<?php
			$fieldSets = $this->form->getFieldsets();

			if (count($fieldSets) >= 2)
            {
				echo HTMLHelper::_('bootstrap.startTabSet', 'payment-plugin-params', array('active' => 'basic'));

				foreach ($fieldSets as $fieldSet)
				{
					echo HTMLHelper::_('bootstrap.addTab', 'payment-plugin-params', $fieldSet->name , $fieldSet->label);

					foreach ($this->form->getFieldset($fieldSet->name) as $field)
					{
						echo $field->renderField();
					}

					echo HTMLHelper::_('bootstrap.endTab');
				}

				echo HTMLHelper::_('bootstrap.endTabSet');
			}
			else
			{
				foreach ($this->form->getFieldset('basic') as $field)
				{
					echo $field->renderField();
				}
			}
		?>					
	</fieldset>				
</div>
</div>		
<div class="clearfix"></div>	
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="task" value="" />
</form>
