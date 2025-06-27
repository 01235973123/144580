<?php
/**
 * @package     Joom Donation
 * @subpackage  Administrator
 * @copyright   Copyright (C) 2023 Ossolution Team
 * @license     GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

/**
 * Import view for Joom Donation
 */
class DonationViewImportdbHtml extends OSFViewHtml
{
    /**
     * Import statistics
     */
    protected $stats;
    
    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        // Get import statistics
        $model = $this->getModel();
        $this->stats = $model->getImportStatistics();
        
        // Set page title
        ToolbarHelper::title(Text::_('JD_IMPORT_DATA'), 'upload');
        
        // Add toolbar buttons
        ToolbarHelper::cancel('dashboard', Text::_('JTOOLBAR_CLOSE'));
        DonationHelperHtml::renderSubmenu('importdb');
        parent::display($tpl);
    }
}
