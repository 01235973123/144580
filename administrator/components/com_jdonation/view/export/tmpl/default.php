<?php
/**
 * @package     Joom Donation
 * @subpackage  Administrator
 * @copyright   Copyright (C) 2025 Ossolution Team
 * @license     GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.keepalive');
?>

<form action="<?php echo Route::_('index.php?option=com_jdonation&task=export.exportData'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-download me-2"></i>
                            <?php echo Text::_('JD_EXPORT_DATA'); ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="lead"><?php echo Text::_('JD_EXPORT_DESCRIPTION'); ?></p>
                        
                        <div class="alert alert-info" role="alert">
                            <h5 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>
                                <?php echo Text::_('JD_EXPORT_TABLES_INCLUDED'); ?>
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="fas fa-table text-primary me-2"></i><?php echo Text::_('JD_TABLE_CAMPAIGNS'); ?></li>
                                        <li><i class="fas fa-table text-primary me-2"></i><?php echo Text::_('JD_TABLE_CATEGORIES'); ?></li>
                                        <li><i class="fas fa-table text-primary me-2"></i><?php echo Text::_('JD_TABLE_CONFIGS'); ?></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="fas fa-table text-primary me-2"></i><?php echo Text::_('JD_TABLE_DONORS'); ?></li>
                                        <li><i class="fas fa-table text-primary me-2"></i><?php echo Text::_('JD_TABLE_FIELDS'); ?></li>
                                        <li><i class="fas fa-table text-primary me-2"></i><?php echo Text::_('JD_TABLE_FIELD_VALUES'); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning" role="alert">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo Text::_('JD_EXPORT_WARNING'); ?>
                            </h6>
                            <p class="mb-0"><?php echo Text::_('JD_EXPORT_WARNING_DESC'); ?></p>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            <button type="submit" class="btn btn-primary btn-lg me-md-2" onclick="this.form.submit();">
                                <i class="fas fa-download me-2"></i>
                                <?php echo Text::_('JD_EXPORT_SQL'); ?>
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg" onclick="window.history.back();">
                                <i class="fas fa-arrow-left me-2"></i>
                                <?php echo Text::_('JCANCEL'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-footer text-muted">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            <?php echo Text::_('JD_EXPORT_FOOTER_NOTE'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php echo HTMLHelper::_('form.token'); ?>
    <input type="hidden" name="option" value="com_jdonation" />
    <input type="hidden" name="task" value="export.exportData" />
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add loading state to export button
    const exportBtn = document.querySelector('button[type="submit"]');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?php echo Text::_('JD_EXPORTING'); ?>';
            
            // Re-enable button after 10 seconds (in case of error)
            setTimeout(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-download me-2"></i><?php echo Text::_('JD_EXPORT_SQL'); ?>';
            }, 10000);
        });
    }
});
</script>
