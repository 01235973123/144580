<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Layout variables
 *
 * @var MPFConfig                         $config
 * @var OSMembershipViewConfigurationHtml $this
 */
?>
<div class="control-group">
	<div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('pdf_font', Text::_('OSM_PDF_FONT'), Text::_('OSM_PDF_FONT_EXPLAIN')); ?>
		<p class="text-warning">
			<?php echo Text::_('OSM_PDF_FONT_WARNING'); ?>
		</p>
	</div>
	<div class="controls">
		<?php echo $this->lists['pdf_font']; ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('pdf_margin_left', Text::_('OSM_MARGIN_LEFT')); ?>
    </div>
    <div class="controls">
		<input type="number" class="form-control" name="pdf_margin_left" step="1" value="<?php echo $this->config->get('pdf_margin_left', 15); ?>">
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('pdf_margin_right', Text::_('OSM_MARGIN_RIGHT')); ?>
    </div>
    <div class="controls">
        <input type="number" class="form-control" name="pdf_margin_right" step="1" value="<?php echo $this->config->get('pdf_margin_right', 15); ?>">
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('pdf_margin_top', Text::_('OSM_MARGIN_TOP')); ?>
    </div>
    <div class="controls">
        <input type="number" class="form-control" name="pdf_margin_top" step="1" value="<?php echo $this->config->get('pdf_margin_top', 0); ?>">
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo OSMembershipHelperHtml::getFieldLabel('pdf_margin_bottom', Text::_('OSM_MARGIN_BOTTOM')); ?>
    </div>
    <div class="controls">
        <input type="number" class="form-control" name="pdf_margin_bottom" step="1" value="<?php echo $this->config->get('pdf_margin_bottom', 25); ?>">
    </div>
</div>
<?php
if (PluginHelper::isEnabled('editors', 'codemirror'))
{
	$editorPlugin = 'codemirror';
}
elseif (PluginHelper::isEnabled('editor', 'none'))
{
	$editorPlugin = 'none';
}
else
{
	$editorPlugin = null;
}

if (PluginHelper::isEnabled('osmembership', 'mpdf'))
{
?>
    <div class="control-group">
        <div class="control-label">
            <?php echo OSMembershipHelperHtml::getFieldLabel('pdf_css', Text::_('OSM_PDF_CSS'), Text::_('OSM_PDF_CSS_EXPLAIN')); ?>
        </div>
        <div class="controls">
            <?php
			if ($editorPlugin)
			{
				echo Editor::getInstance('codemirror')->display('pdf_css', $this->config->pdf_css, '100%', '550', '75', '8', false);
			}
			else
			{
			?>
                <textarea name="pdf_css" class="form-control" rows="10"><?php echo $this->config->pdf_css; ?></textarea>
            <?php
			}
			?>
        </div>
    </div>
<?php
}
