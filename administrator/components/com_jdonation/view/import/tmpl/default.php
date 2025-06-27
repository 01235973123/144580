<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;


ToolbarHelper::title(Text::_('JD_IMPORT_DONORS_TITLE'));
ToolbarHelper::save('donor.import');
ToolbarHelper::cancel('donor.cancel');
if (version_compare(JVERSION, '3.0', 'ge')) {
    //DonationHelper::addSideBarmenus('import');
    //$sidebar = JHtmlSidebar::render();
}
?>
<form action="index.php?option=com_jdonation&view=import" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div id="j-main-container" style="margin-top:15px;">
    <div class="container-fluid">
        <fieldset class="general form-horizontal options-form">
            <legend class="mb-3"><?php echo Text::_('JD_IMPORT_DONORS_TITLE'); ?></legend>
            <div class="row align-items-center mb-3">
                <div class="col-md-2">
                    <label for="csv_donors" class="form-label fw-bold mb-0" style="font-size: 1rem;">
                        <?php echo Text::_('JD_CSV_FILE'); ?>
                    </label>
                </div>
                <div class="col-md-6">
                    <input type="file"
                           id="csv_donors"
                           name="csv_donors"
                           class="form-control"
                           accept=".csv,.txt" />
                </div>
                <div class="col-md-4">
                    <small class="form-text text-muted">
                        <?php echo Text::_('JD_CSV_FILE_EXPLAIN'); ?>
                        <a href="path/to/sample.csv" target="_blank">
                            <?php echo Text::_('JD_DOWNLOAD_SAMPLE_CSV_FILE'); ?>
                        </a>
                    </small>
                </div>
            </div>
        </fieldset>
    </div>
</div>


<input type="hidden" name="task" value="" />
<?php echo HTMLHelper::_( 'form.token' ); ?>			
</form>
