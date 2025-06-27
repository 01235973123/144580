<?php
/**
 * @package     Joom Donation
 * @subpackage  Administrator
 * @copyright   Copyright (C) 2025 Ossolution Team
 * @license     GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

/**
 * Import controller for Joom Donation
 */
class DonationControllerImportdb extends DonationController
{
    /**
     * Import donation data from SQL file
     */
    public function importData()
    {
        // Check for request forgeries
        //Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        
        $app = Factory::getApplication();
        
        try {
            $input = $app->input;
            $files = $input->files->get('jform', array(), 'array');
            
            if (empty($files['sql_file']['tmp_name'])) {
                throw new Exception(Text::_('JD_IMPORT_NO_FILE_SELECTED'));
            }
            
            $file = $files['sql_file'];
            
            // Validate file
            $this->validateUploadedFile($file);
            
            // Get model and process import
            $model = $this->getModel();
            $result = $model->importFromSQLFile($file['tmp_name']);
            
            if ($result['success']) {
                $message = Text::sprintf('JD_IMPORT_SUCCESS', $result['queries_executed'], $result['tables_processed']);
                $app->enqueueMessage($message, 'success');
                
                if (!empty($result['warnings'])) {
                    foreach ($result['warnings'] as $warning) {
                        $app->enqueueMessage($warning, 'warning');
                    }
                }
            } else {
                throw new Exception($result['error']);
            }
            
        } catch (Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
        }
        
        $this->setRedirect('index.php?option=com_jdonation&view=dashboard');
    }
    
    /**
     * Validate uploaded file
     */
    protected function validateUploadedFile($file)
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception(Text::_('JD_IMPORT_UPLOAD_ERROR'));
        }
        
        // Check file size (max 50MB)
        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($file['size'] > $maxSize) {
            throw new Exception(Text::_('JD_IMPORT_FILE_TOO_LARGE'));
        }
        
        // Check file extension
        $allowedExtensions = ['sql'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception(Text::_('JD_IMPORT_INVALID_FILE_TYPE'));
        }
        
        // Check if file is readable
        if (!is_readable($file['tmp_name'])) {
            throw new Exception(Text::_('JD_IMPORT_FILE_NOT_READABLE'));
        }
    }
}
