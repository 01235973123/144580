<?php
/**
 * @package     Joom Donation
 * @subpackage  Administrator
 * @copyright   Copyright (C) 2025 Ossolution Team
 * @license     GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;

/**
 * Export view for Joom Donation
 */
class DonationViewExportHtml extends OSFViewHtml
{
    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        // Set page title
        ToolbarHelper::title(Text::_('JD_EXPORT_DATA'), 'download');
        
        // Add toolbar buttons
        ToolbarHelper::custom('export.exportData', 'download', 'download', Text::_('JD_EXPORT_SQL'), false);
        ToolbarHelper::cancel('campaigns.display', Text::_('JTOOLBAR_CLOSE'));
        DonationHelperHtml::renderSubmenu('export');
        parent::display($tpl);
    }
}
