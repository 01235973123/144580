<?php

/**
 * @version        5.5.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Dang Thuc Dam
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.core');
HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);
$editor					= Editor::getInstance(Factory::getConfig()->get('editor'));
$translatable			= Multilanguage::isEnabled() && count($this->languages);
$bootstrapHelper		= $this->bootstrapHelper;
$rowFluidClass			= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class			= $bootstrapHelper->getClassMapping('span12');
$span7Class				= $bootstrapHelper->getClassMapping('span7');
$span5Class				= $bootstrapHelper->getClassMapping('span5');
$controlGroupClass		= $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass		= $bootstrapHelper->getClassMapping('control-label');
$controlsClass			= $bootstrapHelper->getClassMapping('controls');
if (version_compare(JVERSION, '4.0.0-dev', 'lt'))
{
	HTMLHelper::_('behavior.tabstate');
}
?>
<script type="text/javascript">
	function changeValue(itemid){
		var temp = document.getElementById(itemid);
		if(temp.value == 0){
			temp.value = 1;
		}else{
			temp.value = 0;
		}
	}
	Joomla.submitbutton = function(pressbutton) 
	{
		var form = document.adminForm;
		if (pressbutton == 'cancel')
	    {
			Joomla.submitform( pressbutton );
			return;				
		}
	    else
	    {		    
	        <?php
	            $fields = array('description', 'user_email_subject', 'user_email_body', 'recurring_email_body', 'donation_form_msg', 'confirmation_message', 'thanks_message', 'cancel_message');
	            foreach($fields as $field)
	            {
	                //echo $editor->save($field);
	            }
	        ?>
	        Joomla.submitform(pressbutton);
		}
	}
</script>
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
?>
<form class="form-horizontal" action="index.php?option=com_jdonation&view=category" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
		<?php
		echo HTMLHelper::_($tabApiPrefix.'startTabSet', 'category', array('active' => 'general-page'));
			echo HTMLHelper::_($tabApiPrefix.'addTab', 'category', 'general-page', Text::_('JD_GENERAL', true));
			?>
			<div class="<?php echo $rowFluidClass;?>">
				<div class="<?php echo $span7Class; ?>">
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo  Text::_('JD_TITLE'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input class="text_area form-control" type="text" name="title" id="title" size="50" maxlength="250" value="<?php echo $this->item->title;?>" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo  Text::_('JD_ALIAS'); ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<input class="text_area form-control" type="text" name="alias" id="alias" maxlength="250" value="<?php echo $this->item->alias;?>" size="50" />
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_DESCRIPTION') ; ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php echo $editor->display( 'description',  $this->item->description , '100%', '300', '75', '8' ) ;?>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_ACCESS') ; ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php
							echo $this->lists['access'];
							?>
						</div>
					</div>
					<div class="<?php echo $controlGroupClass; ?>">
						<label class="<?php echo $controlLabelClass; ?>">
							<?php echo Text::_('JD_PUBLISHED') ; ?>
						</label>
						<div class="<?php echo $controlsClass; ?>">
							<?php
							echo DonationHelperHtml::showCheckboxfield('published',(int)$this->item->published);
							?>
						</div>
					</div>
				</div>
			</div>
			<?php
			echo HTMLHelper::_($tabApiPrefix.'endTab');
			if ($translatable) 
			{
				echo HTMLHelper::_($tabApiPrefix.'addTab', 'category', 'translation-page', Text::_('JD_TRANSLATION'));
				?>
				<div class="tab-pane" id="translation-page">
					<div class="tab-content">
						<?php
						$i = 0;
						echo HTMLHelper::_($tabApiPrefix.'startTabSet', 'campaign-translation', array('active' => 'translation-page-0'));
						foreach ($this->languages as $language) 
						{
							$sef = $language->sef;
							echo HTMLHelper::_($tabApiPrefix.'addTab', 'campaign-translation', 'translation-page-'.$i, $language->title . ' <img src="' . Uri::root() . 'media/com_jdonation/flags/' . $sef . '.png" />');
							?>
							<div class="tab-pane<?php echo $i == 0 ? ' active' : ''; ?>">
								<table class="admintable" style="width: 100%;">
									<div class="<?php echo $controlGroupClass; ?>">
										<label class="<?php echo $controlLabelClass; ?>">
											<?php echo Text::_('JD_TITLE'); ?>
										</label>
										<div class="<?php echo $controlsClass; ?>">
											<input class="form-control input-large ilarge" type="text" name="title_<?php echo $sef; ?>"
												   id="title_<?php echo $sef; ?>" size="" maxlength="250"
												   value="<?php echo $this->item->{'title_' . $sef}; ?>"/>
										</div>
									</div>
									<div class="<?php echo $controlGroupClass; ?>">
										<label class="<?php echo $controlLabelClass; ?>">
											<?php echo Text::_('JD_ALIAS'); ?>
										</label>
										<div class="<?php echo $controlsClass; ?>">
											<input class="form-control input-large ilarge" type="text" name="alias_<?php echo $sef; ?>"
												   id="alias_<?php echo $sef; ?>" size="" maxlength="250"
												   value="<?php echo $this->item->{'alias_' . $sef}; ?>"/>
										</div>
									</div>
									<div class="<?php echo $controlGroupClass; ?>">
										<label class="<?php echo $controlLabelClass; ?>">
											<?php echo Text::_('JD_DESCRIPTION'); ?>
										</label>
										<div class="<?php echo $controlsClass; ?>">
											<?php echo $editor->display('description_' . $sef, $this->item->{'description_' . $sef}, '100%', '250', '75', '10'); ?>
										</div>
									</div>
								</table>
							</div>
							<?php
							echo HTMLHelper::_($tabApiPrefix.'endTab');
							$i++;
						}
						echo HTMLHelper::_($tabApiPrefix.'endTabSet');
						?>
					</div>
				</div>
				<?php
				echo HTMLHelper::_($tabApiPrefix.'endTab');
			}
			echo HTMLHelper::_($tabApiPrefix.'endTabSet');
			?>
	<input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="MAX_UPLOAD_FILESIZE" value="90000000" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
	function changeValue(item){
		var itemElement = document.getElementById(item);
		if(itemElement.value == 0){
			itemElement.value = 1;
		}else{
			itemElement.value = 0;
		}
	}
</script>
