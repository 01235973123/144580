<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$rowFluid        = $bootstrapHelper->getClassMapping('row-fluid');
$span7           = $bootstrapHelper->getClassMapping('span7');
$span5           = $bootstrapHelper->getClassMapping('span5');
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
<div class="<?php echo $rowFluid; ?>">
	<div class="<?php echo $span7; ?>">
		<fieldset class="form-horizontal options-form">
			<legend><?php echo Text::_('OSM_PLUGIN_DETAIL'); ?></legend>
			<div class="control-group">
				<div class="control-label">
					<?php echo  Text::_('OSM_NAME'); ?>
				</div>
				<div class="controls">
					<?php echo $this->item->name ; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo  Text::_('OSM_TITLE'); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="title" id="title" size="40" maxlength="250" value="<?php echo $this->item->title;?>" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_AUTHOR'); ?>
				</div>
				<div class="controls">
					<input class="form-control" type="text" name="author" id="author" size="40" maxlength="250" value="<?php echo $this->item->author;?>" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_CREATION_DATE'); ?>
				</div>
				<div class="controls">
					<?php echo $this->item->creation_date; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_COPYRIGHT') ; ?>
				</div>
				<div class="controls">
					<?php echo $this->item->copyright; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_LICENSE'); ?>
				</div>
				<div class="controls">
					<?php echo $this->item->license; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_AUTHOR_EMAIL'); ?>
				</div>
				<div class="controls">
					<?php echo $this->item->author_email; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_AUTHOR_URL'); ?>
				</div>
				<div class="controls">
					<?php echo $this->item->author_url; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_VERSION'); ?>
				</div>
				<div class="controls">
					<?php echo $this->item->version; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_DESCRIPTION'); ?>
				</div>
				<div class="controls">
					<?php echo $this->item->description; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('OSM_ACCESS'); ?>
				</div>
				<div class="controls">
					<?php
						echo $this->lists['access'];
					?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo Text::_('Published'); ?>
				</div>
				<div class="controls">
					<?php
						echo $this->lists['published'];
					?>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="<?php echo $span5; ?>">
		<fieldset class="form-horizontal options-form">
			<legend><?php echo Text::_('OSM_PLUGIN_PARAMETERS'); ?></legend>
			<?php
				$fieldSets = $this->form->getFieldsets();

				if (count($fieldSets) >= 2)
				{
					echo HTMLHelper::_( 'uitab.startTabSet', 'payment-plugin-params', ['active' => 'basic', 'recall' => true]);

					foreach ($fieldSets as $fieldSet)
					{
						echo HTMLHelper::_( 'uitab.addTab', 'payment-plugin-params', $fieldSet->name, $fieldSet->label);

						foreach ($this->form->getFieldset($fieldSet->name) as $field)
						{
							echo $field->renderField();
						}

						echo HTMLHelper::_( 'uitab.endTab');
					}

					echo HTMLHelper::_( 'uitab.endTabSet');
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
	<?php echo HTMLHelper::_('form.token'); ?>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value="" />
</form>