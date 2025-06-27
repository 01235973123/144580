<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2025 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Layout variables
 *
 * @var array                     $rows
 * @var \Joomla\Registry\Registry $params
 */

if (!count($rows))
{
    return;
}

$config = EventbookingHelper::getConfig();
Factory::getApplication()->getLanguage()->load('com_eventbooking', JPATH_ADMINISTRATOR);
?>
<table class="adminlist table table-striped">
    <thead>
        <tr>
            <th class="title" nowrap="nowrap"><?php echo Text::_('EB_FIRST_NAME'); ?></th>
	        <?php
            if ($params->get('show_last_name', 1))
            {
			?>
	            <th class="title"><?php echo Text::_('EB_LAST_NAME'); ?></th>
            <?php
            }

			if ($params->get('show_event_title', 1))
			{
			?>
				<th class="title"><?php echo Text::_('EB_EVENT'); ?></th>
            <?php
			}

			if ($params->get('show_event_date', 1))
			{
			?>
				<th class="center"><?php echo Text::_('EB_EVENT_DATE'); ?></th>
            <?php
			}

			if ($params->get('show_email', 1))
			{
			?>
				<th class="title"><?php echo Text::_('EB_EMAIL'); ?></th>
            <?php
			}

			if ($params->get('show_number_registrants', 1))
			{
			?>
				<th class="center"><?php echo Text::_('EB_NUMBER_REGISTRANTS'); ?></th>
            <?php
			}

	        if ($params->get('show_registration_date', 1))
	        {
			?>
		        <th class="center"><?php echo Text::_('EB_REGISTRATION_DATE'); ?></th>
	        <?php
	        }
	        ?>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach ($rows AS $row)
    {
        $link = Route::_('index.php?option=com_eventbooking&view=registrant&id='.$row->id);
        $eventLink = Route::_('index.php?option=com_eventbooking&view=event&id='.$row->event_id);
    ?>
    <tr>
        <td><a href="<?php echo $link ?>" target="_blank"><?php echo $row->first_name; ?></a></td>
	    <?php
	    if ($params->get('show_last_name', 1))
	    {
		?>
		    <td><?php echo $row->last_name; ?></td>
	    <?php
	    }

		if ($params->get('show_event_title', 1))
	    {
		?>
		    <td><a href="<?php echo $eventLink ?>" target="_blank"><?php echo $row->title; ?></a></td>
	    <?php
	    }

		if ($params->get('show_event_date', 1))
	    {
		?>
		    <td class="center">
			    <?php
			    if ($row->event_date == EB_TBC_DATE)
			    {
				    echo Text::_('EB_TBC');
			    }
			    else
			    {
				    echo HTMLHelper::_('date', $row->event_date, $config->date_format . ' H:i', null);
			    }
			    ?>
		    </td>
	    <?php
	    }

		if ($params->get('show_email', 1))
	    {
		?>
		    <td><?php echo $row->email; ?></td>
	    <?php
	    }

		if ($params->get('show_number_registrants', 1))
	    {
		?>
		    <td class="center"><?php echo $row->number_registrants; ?></td>
	    <?php
	    }

		if ($params->get('show_registration_date', 1))
	    {
		?>
		    <td class="center"><?php echo HTMLHelper::_('date', $row->register_date, $config->date_format.' H:i:s'); ?></td>
	    <?php
	    }
	    ?>
    </tr>
    <?php
    }
    ?>
    </tbody>
</table>