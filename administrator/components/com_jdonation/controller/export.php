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
use Joomla\CMS\Response\JsonResponse;

/**
 * Export controller for Joom Donation
 */
class DonationControllerExport extends DonationController
{
    /**
     * Export donation data to SQL file
     */
    public function exportData()
    {
        try {
            $model = $this->getModel();
            $sqlContent = $model->generateExportSQL();
            
            if ($sqlContent) {
                $filename = 'joomdonation_export_' . date('Y-m-d_H-i-s') . '.sql';
                
                // Set headers for file download
                header('Content-Type: application/sql');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($sqlContent));
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                
                echo $sqlContent;
                exit();
            } else {
                throw new Exception('Failed to generate SQL export');
            }
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            $this->setRedirect('index.php?option=com_jdonation');
        }
    }
}
