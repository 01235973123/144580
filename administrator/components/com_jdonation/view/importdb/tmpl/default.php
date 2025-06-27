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

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.modal');
?>

<form action="<?php echo Route::_('index.php?option=com_jdonation'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-upload me-2"></i>
                            <?php echo Text::_('JD_IMPORT_DATA'); ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="lead"><?php echo Text::_('JD_IMPORT_DESCRIPTION'); ?></p>
                        
                        <!-- Import Form -->
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="mb-4">
                                    <label for="sql_file" class="form-label">
                                        <strong><?php echo Text::_('JD_IMPORT_SELECT_FILE'); ?></strong>
                                    </label>
                                    <input type="file" class="form-control" id="sql_file" name="jform[sql_file]" accept=".sql" required>
                                    <div class="form-text"><?php echo Text::_('JD_IMPORT_FILE_HELP'); ?></div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                    <button type="submit" class="btn btn-primary btn-lg me-md-2" id="importBtn">
                                        <i class="fas fa-upload me-2"></i>
                                        <?php echo Text::_('JD_IMPORT_SQL'); ?>
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-lg" onclick="window.history.back();">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        <?php echo Text::_('JCANCEL'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Current Data Statistics -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fas fa-chart-bar me-2"></i>
                            <?php echo Text::_('JD_IMPORT_CURRENT_DATA'); ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($this->stats as $table => $stat): ?>
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card border-<?php echo $stat['exists'] ? 'success' : 'secondary'; ?>">
                                    <div class="card-body text-center">
                                        <h5 class="card-title"><?php echo Text::_('JD_TABLE_' . strtoupper($table)); ?></h5>
                                        <?php if ($stat['exists']): ?>
                                            <p class="card-text">
                                                <span class="badge bg-success fs-6"><?php echo number_format($stat['count']); ?> records</span>
                                            </p>
                                        <?php else: ?>
                                            <p class="card-text">
                                                <span class="badge bg-secondary fs-6">Not exists</span>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Warnings -->
                <div class="card mt-4">
                    <div class="card-header bg-warning">
                        <h4 class="card-title text-dark mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo Text::_('JD_IMPORT_WARNINGS'); ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning" role="alert">
                            <h6 class="alert-heading"><?php echo Text::_('JD_IMPORT_WARNING_TITLE'); ?></h6>
                            <ul class="mb-0">
                                <li><?php echo Text::_('JD_IMPORT_WARNING_BACKUP'); ?></li>
                                <li><?php echo Text::_('JD_IMPORT_WARNING_OVERWRITE'); ?></li>
                                <li><?php echo Text::_('JD_IMPORT_WARNING_COMPATIBILITY'); ?></li>
                                <li><?php echo Text::_('JD_IMPORT_WARNING_SIZE'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmImport" required>
                            <label class="form-check-label" for="confirmImport">
                                <strong><?php echo Text::_('JD_IMPORT_CONFIRM'); ?></strong>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php echo HTMLHelper::_('form.token'); ?>
    <input type="hidden" name="task" value="import.importData" />
    <input type="hidden" name="option" value="com_jdonation" />
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('adminForm');
    const importBtn = document.getElementById('importBtn');
    const fileInput = document.getElementById('sql_file');
    const confirmCheck = document.getElementById('confirmImport');

    // File validation
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Check file size (50MB max)
            const maxSize = 50 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('<?php echo Text::_('JD_IMPORT_FILE_TOO_LARGE'); ?>');
                this.value = '';
                return;
            }

            // Check file extension
            const allowedExtensions = ['sql'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(fileExtension)) {
                alert('<?php echo Text::_('JD_IMPORT_INVALID_FILE_TYPE'); ?>');
                this.value = '';
                return;
            }
        }
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        // Lấy giá trị task
        let task = '';
        const taskInput = form.querySelector('input[name="task"]');
        if (taskInput) {
            task = taskInput.value;
        }

        if (task === 'dashboard') {
            // Nếu là cancel thì bỏ qua kiểm tra file và confirm
            return;
        }

        if (!fileInput.files.length) {
            e.preventDefault();
            alert('<?php echo Text::_('JD_IMPORT_NO_FILE_SELECTED'); ?>');
            return;
        }

        if (!confirmCheck.checked) {
            e.preventDefault();
            alert('<?php echo Text::_('JD_IMPORT_MUST_CONFIRM'); ?>');
            return;
        }

        // Show loading state
        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?php echo Text::_('JD_IMPORTING'); ?>';

        // Show progress message
        const progressDiv = document.createElement('div');
        progressDiv.className = 'alert alert-info mt-3';
        progressDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?php echo Text::_('JD_IMPORT_IN_PROGRESS'); ?>';
        form.appendChild(progressDiv);
    });
});

</script>
