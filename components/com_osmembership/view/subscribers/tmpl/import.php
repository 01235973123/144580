<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$centerClass  = $bootstrapHelper->getClassMapping('center');

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core')
	->registerAndUseScript('com_osmembership.site-subscribers-import', 'media/com_osmembership/js/site-subscribers-import.min.js');

Text::script('OSM_SELECT_FILE_TO_IMPORT_SUBSCRIPTIONS', true);
?>
<div id="osm-container">
	<div class="page-header">
		<h1 class="osm-heading"><?php echo Text::_('OSM_IMPORT_SUBSCRIPTIONS'); ?></h1>
	</div>
	<form action="<?php echo Route::_('index.php?option=com_osmembership&view=subscribers&Itemid=&Itemid=' . $this->Itemid, false); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form form-horizontal">
		<div class="btn-toolbar" id="btn-toolbar">
			<?php echo Toolbar::getInstance('toolbar')->render(); ?>
		</div>
        <p class="<?php echo $bootstrapHelper->getClassMapping('text-info'); ?>"><?php echo Text::_('OSM_SUBSCRIBERS_FILE_EXPLAIN'); ?></p>
        <div class="<?php echo $bootstrapHelper->getClassMapping('control-group'); ?>">
              <div class="<?php echo $bootstrapHelper->getClassMapping('control-label'); ?>">
                    <?php echo Text::_('OSM_SUBSCRIBERS_FILE'); ?>
              </div>
              <div class="<?php echo $bootstrapHelper->getClassMapping('controls'); ?>">
                  <input type="file" name="input_file" size="50" />
              </div>
        </div>
		<input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
