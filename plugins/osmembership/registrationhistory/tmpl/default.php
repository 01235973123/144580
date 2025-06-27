<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Layout variables
 *
 * @var RADConfig $config
 * @var bool      $showLastName
 * @var bool      $showDownloadCertificate
 * @var bool      $showDownloadTicket
 * @var bool      $showDueAmountColumn
 * @var array     $onlinePaymentPlugins
 */

$bootstrapHelper  = OSMembershipHelperBootstrap::getInstance();
$hiddenPhoneClass = $bootstrapHelper->getClassMapping('hidden-phone');
$centerClass      = $bootstrapHelper->getClassMapping('center');
$btnDanger        = $bootstrapHelper->getClassMapping('btn btn-danger');
?>
<table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?> osm-responsive-table"
       id="adminForm">
    <thead>
    <tr>
	    <?php
        if ($config->get('history_show_first_name', 1))
        {
		?>
	        <th>
		        <?php echo Text::_('EB_FIRST_NAME'); ?>
	        </th>
        <?php
        }

		if ($config->get('history_show_last_name', 1) && $showLastName)
		{
		?>
            <th>
				<?php echo Text::_('EB_LAST_NAME'); ?>
            </th>
		<?php
		}
		?>
        <th class="list_event">
			<?php echo Text::_('EB_EVENT'); ?>
        </th>
		<?php
		if ($config->show_event_date)
		{
		?>
            <th class="list_event_date">
				<?php echo Text::_('EB_EVENT_DATE'); ?>
            </th>
		<?php
		}
		?>
        <th class="list_event_date">
			<?php echo Text::_('EB_REGISTRATION_DATE'); ?>
        </th>
		<?php
		if ($config->get('history_show_number_registrants', 1))
		{
		?>
            <th class="list_registrant_number <?php echo $hiddenPhoneClass; ?>">
				<?php echo Text::_('EB_REGISTRANTS'); ?>
            </th>
		<?php
		}

		if ($config->get('history_show_amount', 1))
		{
		?>
            <th class="list_amount <?php echo $hiddenPhoneClass; ?>">
				<?php echo Text::_('EB_AMOUNT'); ?>
            </th>
		<?php
		}

		if ($config->activate_deposit_feature && $showDueAmountColumn)
		{
		?>
            <th style="text-align: right;">
				<?php echo Text::_('EB_DUE_AMOUNT'); ?>
            </th>
		<?php
		}
		?>
        <th class="list_id">
			<?php echo Text::_('EB_REGISTRATION_STATUS'); ?>
        </th>
		<?php
		if ($config->activate_invoice_feature)
		{
		?>
            <th class="<?php echo $centerClass; ?>">
				<?php echo Text::_('EB_INVOICE_NUMBER'); ?>
            </th>
		<?php
		}

		if ($showDownloadTicket)
		{
		?>
            <th class="<?php echo $centerClass; ?>">
				<?php echo Text::_('EB_TICKET'); ?>
            </th>
		<?php
		}

		if ($showDownloadCertificate)
		{
		?>
            <th class="<?php echo $centerClass; ?>">
				<?php echo Text::_('EB_CERTIFICATE'); ?>
            </th>
		<?php
		}
		?>
    </tr>
    </thead>
    <tbody>
	<?php
	$Itemid           = EventbookingHelper::getItemid();
	$registrantItemId = EventbookingHelperRoute::findView('history', $Itemid);

	for ($i = 0, $n = count($items); $i < $n; $i++)
	{
		$row       = $items[$i];
		$link      = Route::_('index.php?option=com_eventbooking&view=registrant&id=' . $row->id . '&Itemid=' . $registrantItemId . '&return=' . $return);
		$eventLink = Route::_(EventbookingHelperRoute::getEventRoute($row->event_id, $row->main_category_id, $Itemid));
		?>
        <tr>
	        <?php
	        if ($config->get('history_show_first_name', 1))
	        {
			?>
		        <td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_FIRST_NAME'); ?>">
			        <a href="<?php echo $link; ?>"><?php echo $row->first_name; ?></a>
		        </td>
	        <?php
	        }

			if ($config->get('history_show_last_name', 1) && $showLastName)
			{
			?>
                <td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_LAST_NAME'); ?>">
					<?php echo $row->last_name; ?>
                </td>
			<?php
			}
			?>
            <td class="tdno<?php echo $i; ?>" data-content="<?php echo Text::_('EB_EVENT'); ?>">
                <a href="<?php echo $eventLink; ?>" target="_blank"><?php echo $row->title; ?></a>
            </td>
			<?php
			if ($config->show_event_date)
			{
			?>
                <td class="tdno<?php echo $i . ' ' . $centerClass; ?>"
                    data-content="<?php echo Text::_('EB_EVENT_DATE'); ?>">
					<?php
					if ($row->event_date == EB_TBC_DATE)
					{
						echo Text::_('EB_TBC');
					}
					else
					{
						echo HTMLHelper::_('date', $row->event_date, $config->event_date_format, null);
					}
					?>
                </td>
			<?php
			}
			?>
            <td class="tdno<?php echo $i . ' ' . $centerClass; ?>"
                data-content="<?php echo Text::_('EB_REGISTRATION_DATE'); ?>">
				<?php echo HTMLHelper::_('date', $row->register_date, $config->date_format); ?>
            </td>
			<?php
			if ($config->get('history_show_number_registrants', 1))
			{
			?>
                <td class="<?php echo $centerClass . ' ' . $hiddenPhoneClass; ?>" style="font-weight: bold;">
					<?php echo $row->number_registrants; ?>
                </td>
			<?php
			}

			if ($config->get('history_show_amount', 1))
			{
			?>
                <td align="right" class="<?php echo $hiddenPhoneClass; ?>">
					<?php echo EventbookingHelper::formatCurrency($row->amount, $config, $row->currency_symbol); ?>
                </td>
			<?php
			}

			if ($config->activate_deposit_feature && $showDueAmountColumn)
			{
			?>
                <td style="text-align: right;" class="tdno<?php echo $i; ?>"
                    data-content="<?php echo Text::_('EB_DUE_AMOUNT'); ?>">
					<?php
					if ($row->payment_status != 1 && $row->published != 2)
					{
						// Check to see if there is an online payment method available for this event
						if ($row->payment_methods)
						{
							$hasOnlinePaymentMethods = count(array_intersect($onlinePaymentPlugins, explode(',', $row->payment_methods)));
						}
						else
						{
							$hasOnlinePaymentMethods = count($onlinePaymentPlugins);
						}

						echo EventbookingHelper::formatCurrency($row->amount - $row->deposit_amount, $config);

						if ($hasOnlinePaymentMethods && $row->group_id == 0)
						{
						?>
                            <a class="btn-primary"
                               href="<?php echo Route::_('index.php?option=com_eventbooking&view=payment&registration_code=' . $row->registration_code . '&Itemid=' . $registrantItemId); ?>"><?php echo Text::_('EB_MAKE_PAYMENT'); ?></a>
						<?php
						}
					}
					?>
                </td>
			<?php
			}
			?>
            <td class="tdno<?php echo $i . ' ' . $centerClass; ?>"
                data-content="<?php echo Text::_('EB_REGISTRATION_STATUS'); ?>">
				<?php
				switch ($row->published)
				{
					case 0 :
						echo Text::_('EB_PENDING');
						break;
					case 1 :
						echo Text::_('EB_PAID');
						break;
					case 2 :
						echo Text::_('EB_CANCELLED');
						break;
					case 3:
						echo Text::_('EB_WAITING_LIST');

						// If there is space, we will display payment link here to allow users to make payment to become registrants
						if ($config->enable_waiting_list_payment && $row->group_id == 0)
						{
							$event = EventbookingHelperDatabase::getEvent($row->event_id);

							if ($event->event_capacity == 0 || ($event->event_capacity - $event->total_registrants >= $row->number_registrants))
							{
								// Check to see if there is an online payment method available for this event
								if ($row->payment_methods)
								{
									$hasOnlinePaymentMethods = count(array_intersect($onlinePaymentPlugins, explode(',', $row->payment_methods)));
								}
								else
								{
									$hasOnlinePaymentMethods = count($onlinePaymentPlugins);
								}

								if ($hasOnlinePaymentMethods)
								{
								?>
                                    <a class="btn-primary"
                                       href="<?php echo Route::_('index.php?option=com_eventbooking&view=payment&layout=registration&order_number=' . $row->registration_code . '&Itemid=' . $registrantItemId); ?>"><?php echo Text::_('EB_MAKE_PAYMENT'); ?></a>
								<?php
								}
							}
						}
						break;
				}

                if (!$row->group_id && !empty($row->enable_cancel_registration) && in_array($row->published, [0, 1, 3]) && EventbookingHelperRegistration::canCancelRegistrationNow($row))
                {
                    if ($row->published == 3)
                    {
                        $buttonText = 'EB_CANCEL_WAITING_LIST';
                    }
                    else
                    {
                        $buttonText = 'EB_CANCEL_REGISTRATION';
                    }
                    ?>
					<a class="<?php echo $btnDanger; ?>" href="<?php echo Route::_('index.php?option=com_eventbooking&task=cancel_registration_confirm&cancel_code=' . $row->registration_code . '&Itemid=' . $this->Itemid); ?>"><?php echo Text::_($buttonText); ?></a>
                    <?php
                }
				?>
            </td>
			<?php
			if ($config->activate_invoice_feature)
			{
			?>
                <td class="tdno<?php echo $i . ' ' . $centerClass; ?>"
                    data-content="<?php echo Text::_('EB_INVOICE_NUMBER'); ?>">
					<?php
					if ($row->invoice_number)
					{
					?>
                        <a href="<?php echo Route::_('index.php?option=com_eventbooking&task=registrant.download_invoice&id=' . ($row->cart_id ?: ($row->group_id ?: $row->id)) . '&Itemid=' . $registrantItemId); ?>"
                           title="<?php echo Text::_('EB_DOWNLOAD'); ?>"><?php echo EventbookingHelper::formatInvoiceNumber($row->invoice_number, $config, $row); ?></a>
					<?php
					}
					?>
                </td>
			<?php
			}

			if ($showDownloadTicket)
			{
			?>
                <td class="tdno<?php echo $i . ' ' . $centerClass; ?>"
                    data-content="<?php echo Text::_('EB_TICKET'); ?>">
					<?php
					if ($row->ticket_code && $row->published == 1 && $row->payment_status == 1)
					{
					?>
                        <a href="<?php echo Route::_('index.php?option=com_eventbooking&task=registrant.download_ticket&id=' . $row->id . '&Itemid=' . $registrantItemId); ?>"
                           title="<?php echo Text::_('EB_DOWNLOAD'); ?>"><?php echo $row->ticket_number ? EventbookingHelperTicket::formatTicketNumber($row->ticket_prefix, $row->ticket_number, $config) : Text::_('EB_DOWNLOAD_TICKETS'); ?></a>
					<?php
					}
					?>
                </td>
			<?php
			}

			if ($showDownloadCertificate)
			{
			?>
                <td class="tdno<?php echo $i . ' ' . $centerClass; ?>"
                    data-content="<?php echo Text::_('EB_CERTIFICATE'); ?>">
					<?php
					if ($row->show_download_certificate)
					{
					?>
                        <a href="<?php echo Route::_('index.php?option=com_eventbooking&task=registrant.download_certificate&id=' . $row->id); ?>"
                           title="<?php echo Text::_('EB_DOWNLOAD'); ?>"><?php echo EventbookingHelper::formatCertificateNumber($row->id, $config); ?></a>
					<?php
					}
					?>
                </td>
			<?php
			}
			?>
        </tr>
		<?php
	}
	?>
    </tbody>
</table>
