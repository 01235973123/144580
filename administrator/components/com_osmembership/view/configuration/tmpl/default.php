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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('Configuration'), 'generic.png');
ToolbarHelper::apply();
ToolbarHelper::save('save');
ToolbarHelper::cancel('cancel');

if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_osmembership'))
{
	ToolbarHelper::preferences('com_osmembership');
}

HTMLHelper::_('bootstrap.tooltip', '.hasTooltip', ['html' => true, 'sanitize' => false]);

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('keepalive')
	->useScript('showon')
	->addInlineStyle('.hasTip{display:block !important}');

$config = $this->config;
$editor = OSMembershipHelper::getEditor();

$translatable    = Multilanguage::isEnabled() && count($this->languages);
$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
?>
<form action="<?php echo Route::_('index.php?option=com_osmembership&view=configuration'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal osm-configuration" enctype="multipart/form-data">
    <?php echo HTMLHelper::_( 'uitab.startTabSet', 'configuration', ['active' => 'general-page', 'recall' => true]); ?>
        <?php echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'general-page', Text::_('OSM_GENERAL')); ?>
        <div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
                <div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
                    <?php echo $this->loadTemplate('subscriptions', ['config' => $config]); ?>
                    <?php echo $this->loadTemplate('mail', ['config' => $config]); ?>
                </div>
                <div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
	                <?php echo $this->loadTemplate('user_registration', ['config' => $config]); ?>
                    <?php echo $this->loadTemplate('themes', ['config' => $config]); ?>
                    <?php echo $this->loadTemplate('gdpr', ['config' => $config]); ?>
                    <?php echo $this->loadTemplate('other', ['config' => $config]); ?>
                </div>
        </div>
        <?php echo HTMLHelper::_( 'uitab.endTab'); ?>
        <?php
		echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'pdf-settings-page', Text::_('OSM_PDF_SETTINGS'));
		echo $this->loadTemplate('pdf_settings', ['config' => $config, 'editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');

		echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'invoice-page', Text::_('OSM_INVOICE_SETTINGS'));
		echo $this->loadTemplate('invoice', ['config' => $config, 'editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');

		echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'card-page', Text::_('OSM_MEMBER_CARD_SETTINGS'));
		echo $this->loadTemplate('card', ['config' => $config, 'editor' => $editor]);
		echo HTMLHelper::_( 'uitab.endTab');

        echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'eu-vat', Text::_('OSM_EU_VAT_SETTINGS'));
        echo $this->loadTemplate('eu_vat', ['config' => $config]);
        echo HTMLHelper::_( 'uitab.endTab');

		echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'export-settings-page', Text::_('OSM_EXPORT_SETTINGS'));
		echo $this->loadTemplate('export_fields', ['config' => $config]);
		echo HTMLHelper::_( 'uitab.endTab');

		echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'api-page', Text::_('OSM_API_SETTINGS'));
		echo $this->loadTemplate('api', ['config' => $config]);
		echo HTMLHelper::_( 'uitab.endTab');

		if ($translatable)
		{
			echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'invoice-translation', Text::_('OSM_INVOICE_TRANSLATION'));
			echo $this->loadTemplate('translation', ['config' => $config, 'editor' => $editor]);
			echo HTMLHelper::_( 'uitab.endTab');
		}

        echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'css-classes-map-page', Text::_('OSM_CSS_CLASSES_MAP'));
        echo $this->loadTemplate('css_classes_map', ['config' => $config]);
        echo HTMLHelper::_( 'uitab.endTab');

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

		if (file_exists(JPATH_ROOT . '/media/com_osmembership/assets/css/custom.css'))
		{
			$customCss = file_get_contents(JPATH_ROOT . '/media/com_osmembership/assets/css/custom.css');
		}
		else
		{
			$customCss = '';
		}

		echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'custom-css', Text::_('OSM_CUSTOM_CSS'));

		if ($editorPlugin)
		{
			echo Editor::getInstance($editorPlugin)->display('custom_css', $customCss, '100%', '550', '75', '8', false, null, null, null, ['syntax' => 'css']);
		}
		else
		{
		?>
            <textarea name="custom_css" rows="20" class="form-control" style="width: 100%;"><?php echo $customCss; ?></textarea>
        <?php
		}

		echo HTMLHelper::_( 'uitab.endTab');

		if (file_exists(JPATH_ROOT . '/components/com_osmembership/fields.xml'))
		{
			echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'custom-fields', Text::_('OSM_CUSTOM_FIELDS'));
			echo $this->loadTemplate('custom_fields', ['editorPlugin' => $editorPlugin]);
			echo HTMLHelper::_( 'uitab.endTab');
		}

		echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'common-tags-page', Text::_('OSM_COMMON_TAGS'));
		echo $this->loadTemplate('common_tags', ['config' => $config]);
		echo HTMLHelper::_( 'uitab.endTab');

		echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'anti-spam-page', Text::_('OSM_ANTI_SPAM_SETTINGS'));
		echo $this->loadTemplate('anti_spam', ['config' => $config]);
		echo HTMLHelper::_( 'uitab.endTab');

		// Add support for custom settings layout
		if (file_exists(__DIR__ . '/default_custom_settings.php'))
		{
			echo HTMLHelper::_( 'uitab.addTab', 'configuration', 'custom-settings-page', Text::_('OSM_CUSTOM_SETTINGS'));
			echo $this->loadTemplate('custom_settings', ['config' => $config, 'editor' => $editor]);
			echo HTMLHelper::_( 'uitab.endTab');
		}

		echo HTMLHelper::_( 'uitab.endTabSet');
	?>
    <input type="hidden" name="task" value="" />
    <div class="clearfix"></div>
</form>
