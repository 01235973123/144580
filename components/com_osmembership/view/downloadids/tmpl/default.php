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
use Joomla\CMS\Uri\Uri;

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core');

$iconPublish   = $this->bootstrapHelper->getClassMapping('icon-publish');
$iconUnPublish = $this->bootstrapHelper->getClassMapping('icon-unpublish');
$centerClass   = $this->bootstrapHelper->getClassMapping('center');
?>
<div id="osm-download-ids" class="osm-container">
	<?php
	if ($this->params->get('show_page_heading', 1))
	{
		if ($this->input->getInt('hmvc_call'))
		{
			$hTag = 'h2';
		}
		else
		{
			$hTag = 'h1';
		}
	?>
		<<?php echo $hTag; ?> class="osm-page-title"><?php echo Text::_('OSM_MANAGE_DOWNLOAD_IDS') ?></<?php echo $hTag; ?>>
	<?php
	}

	echo $this->message->download_ids_manage_message;
	?>
	<div class="btn-toolbar" id="btn-toolbar">
		<?php echo Toolbar::getInstance('toolbar')->render(); ?>
	</div>
    <form method="post" name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_osmembership&view=downloadids&Itemid=' . $this->Itemid); ?>">
        <p class="<?php echo $this->bootstrapHelper->getClassMapping('pull-right'); ?>">
            <?php echo Text::_('OSM_GENERATE');?> <?php echo HTMLHelper::_('select.integerlist', 1, 5, 1, 'number_download_ids', 'class="input-small form-select d-inline-block w-auto"'); ?> <?php echo Text::_('OSM_NEW_DOWNLOAD_IDS'); ?>
            <button type="button" class="btn btn-small btn-primary" onclick="Joomla.submitform('generate_download_ids');"><i class="icon-new icon-white"></i><?php echo Text::_('OSM_PROCESS'); ?></button>
        </p>
        <table class="<?php echo $this->bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?>">
            <thead>
            <tr>
	            <th width="20">
		            <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
	            </th>
	            <th>
		            <?php echo Text::_('OSM_DOWNLOAD_ID'); ?>
	            </th>
	            <?php
	                if ($this->params->get('show_domain', 1))
	                {
					?>
		                <th>
			                <?php echo Text::_('OSM_DOMAIN'); ?>
		                </th>
	                <?php
	                }
	            ?>
                <th>
                    <?php echo Text::_('OSM_CREATED_DATE'); ?>
                </th>
                <th class="<?php echo $centerClass; ?>">
                    <?php echo Text::_('OSM_ENABLED'); ?>
                </th>
            </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="5"><div class="pagination"><?php echo $this->pagination->getListFooter(); ?></div></td>
                </tr>
            </tfoot>
            <tbody>
            <?php
			$rootUri = Uri::root(true);

			for ($i = 0 , $n = count($this->items) ; $i < $n; $i++)
			{
				$item    = $this->items[$i];
				$checked = HTMLHelper::_('grid.id', $i, $item->id);
				$alt     = $item->published ? Text::_('OSM_ENABLED') : Text::_('OSM_DISABLED');
				?>
                <tr>
                    <td>
                        <?php echo $checked; ?>
                    </td>
                    <td>
                        <?php echo $item->download_id; ?>
                    </td>
	                <?php
	                if ($this->params->get('show_domain', 1))
	                {
					?>
		                <td>
			                <?php echo $item->domain; ?>
		                </td>
	                <?php
	                }
	                ?>
                    <td>
                        <?php echo HTMLHelper::_('date', $item->created_date, $this->config->date_format); ?>
                    </td>
                    <td class="<?php echo $centerClass; ?>">
                        <i class="icon <?php echo $item->published ? $iconPublish : $iconUnPublish; ?>"></i>
                    </td>
                </tr>
                <?php
			}
			?>
            </tbody>
        </table>
        <input type="hidden" name="task" value="" />
	    <input type="hidden" name="boxchecked" value="0" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>