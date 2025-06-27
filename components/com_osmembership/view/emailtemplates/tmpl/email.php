<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright	Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Layout variables
 *
 * @var MPFForm   $form
 * @var stdClass  $row
 * @var MPFConfig $config
 * @var string    $planTitle
 * @var bool      $toAdmin
 * @var bool      $lifetimeMembership
 * @var string    $currencySymbol
 */

$fields = $form->getFields();
?>
<table class="os_table" width="100%">
	<tr>
		<td class="title_cell" width="35%">
			<?php echo  Text::_('OSM_PLAN') ?>
		</td>
		<td class="field_cell">
			<?php echo $planTitle;?>
		</td>
	</tr>
	<?php
		if ($row->coupon_id)
		{
			/* @var \Joomla\Database\DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true)
				->select($db->quoteName('code'))
				->from('#__osmembership_coupons')
				->where('id = ' . $row->coupon_id);
			$db->setQuery($query);
			$couponCode = $db->loadResult();
		?>
			<tr>
				<td class="title_cell" width="35%">
					<?php echo  Text::_('OSM_COUPON') ?>
				</td>
				<td class="field_cell">
					<?php echo $couponCode; ?>
				</td>
			</tr>
		<?php
		}

		if (isset($username))
		{
		?>
			<tr>
				<td class="title_cell" width="35%">
					<?php echo  empty($config->use_email_as_username) ? Text::_('OSM_USERNAME') : $fields['email']->title; ?>
				</td>
				<td class="field_cell">
					<?php echo $config->use_email_as_username ? $row->email : $username; ?>
				</td>
			</tr>
		<?php
		}

		if (isset($password) && !$toAdmin)
		{
		?>
			<tr>
				<td class="title_cell" width="35%">
					<?php echo  Text::_('OSM_PASSWORD') ?>
				</td>
				<td class="field_cell">
					<?php echo $password; ?>
				</td>
			</tr>
		<?php
		}
	?>
	<tr>
		<td class="title_cell">
			<?php echo Text::_('OSM_SUBSCRIPTION_START_DATE'); ?>
		</td>
		<td class="field_cell">
			<?php echo HTMLHelper::_('date', $row->from_date, $config->date_format); ?>
		</td>
	</tr>
	<tr>
		<td class="title_cell">
			<?php echo Text::_('OSM_SUBSCRIPTION_END_DATE'); ?>
		</td>
		<td class="field_cell">
			<?php
				if ($lifetimeMembership || $row->to_date == '2099-12-31 23:59:59')
				{
					echo Text::_('OSM_LIFETIME');
				}
				else
				{
					echo HTMLHelper::_('date', $row->to_date, $config->date_format);
				}
			?>
		</td>
	</tr>
	<?php

	if (!empty($config->use_email_as_username))
	{
		unset($fields['email']);
	}

	foreach ($fields as $field)
	{
		if (!$field->visible || $field->row->hide_on_email)
		{
			continue;
		}

		switch (strtolower($field->type))
		{
			case 'heading' :
				?>
					<tr>
						<td colspan="2"><h3 class="osm-heading"><?php echo Text::_($field->title) ; ?></h3></td>
					</tr>

					<?php
					break ;
				case 'message' :
					?>
						<tr>
							<td colspan="2">
								<p class="osm-message"><?php echo $field->description ; ?></p>
							</td>
						</tr>
						<?php
					break ;
				case 'date':
					?>
					<tr>
						<td class="title_cell">
							<?php echo Text::_($field->title); ?>
						</td>
						<td class="field_cell">
							<?php
							$fieldValue = $field->value;
							if ($fieldValue)
							{
								try
								{
									$formattedValue = HTMLHelper::_('date', $fieldValue, $config->date_format, null);
									echo $formattedValue;
								}
								catch (Exception $e)
								{
									echo $fieldValue;
								}
							}
							else
							{
								echo $fieldValue;
							}
							?>
						</td>
					</tr>
					<?php
					break;
				default:
					?>
					<tr>
						<td class="title_cell">
							<?php echo Text::_($field->title); ?>
						</td>
						<td class="field_cell">
							<?php
							if ($field->name == 'state')
							{
								if ($field->value)
								{
									$fieldValue = OSMembershipHelper::getStateName($row->country, $field->value);
								}
								else
								{
									$fieldValue = '';
								}
							}
							elseif ($field->name == 'country')
							{
								$fieldValue = OSMembershipHelper::getTranslatedCountryName($row);
							}
							else
							{
								$fieldValue = $field->value;

								if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
								{
									$fieldValue = implode(', ', json_decode($fieldValue));
								}
							}

							echo $fieldValue;
							?>
						</td>
					</tr>
					<?php
					break;
			}
		}
		if ($row->gross_amount > 0)
		{
			if ($row->setup_fee > 0)
			{
			?>
				<tr>
					<td class="title_cell">
						<?php echo Text::_('OSM_SETUP_FEE'); ?>
					</td>
					<td class="field_cell">
						<?php echo OSMembershipHelper::formatCurrency($row->setup_fee, $config, $currencySymbol); ?>
					</td>
				</tr>
			<?php
			}
		?>
			<tr>
				<td class="title_cell">
					<?php echo Text::_('OSM_PRICE'); ?>
				</td>
				<td class="field_cell">
					<?php echo OSMembershipHelper::formatCurrency($row->amount, $config, $currencySymbol); ?>
				</td>
			</tr>
			<?php
				if ($row->discount_amount > 0)
				{
				?>
					<tr>
						<td class="title_cell">
							<?php echo Text::_('OSM_DISCOUNT'); ?>
						</td>
						<td class="field_cell">
							<?php echo OSMembershipHelper::formatCurrency($row->discount_amount, $config, $currencySymbol); ?>
						</td>
					</tr>
				<?php
				}
				if ($row->tax_amount > 0)
				{
				?>
					<tr>
						<td class="title_cell">
							<?php echo Text::_('OSM_TAX_AMOUNT'); ?>
						</td>
						<td class="field_cell">
							<?php echo OSMembershipHelper::formatCurrency($row->tax_amount, $config, $currencySymbol); ?>
						</td>
					</tr>
				<?php
				}
				if ($row->payment_processing_fee > 0)
				{
				?>
					<tr>
						<td class="title_cell">
							<?php echo Text::_('OSM_PAYMENT_FEE'); ?>
						</td>
						<td class="field_cell">
							<?php echo OSMembershipHelper::formatCurrency($row->payment_processing_fee, $config, $currencySymbol); ?>
						</td>
					</tr>
				<?php
				}
				if ($row->setup_fee > 0 || $row->discount_amount > 0 || $row->tax_amount > 0 || $row->payment_processing_fee > 0)
				{
				?>
					<tr>
						<td class="title_cell">
							<?php echo Text::_('OSM_GROSS_AMOUNT'); ?>
						</td>
						<td class="field_cell">
							<?php echo OSMembershipHelper::formatCurrency($row->gross_amount, $config, $currencySymbol); ?>
						</td>
					</tr>
				<?php
				}
			?>
			<tr>
				<td class="title_cell">
					<?php echo Text::_('OSM_PAYMENT_OPTION'); ?>
				</td>
				<td class="field_cell">
					<?php
						$method = OSMembershipHelperPayments::loadPaymentMethod($row->payment_method) ;
						if ($method)
						{
							echo Text::_($method->title);
						}
					?>
				</td>
			</tr>

            <?php
				if ($row->subscription_id)
				{
				?>
                    <tr>
                        <td class="title_cell">
                            <?php echo Text::_('OSM_SUBSCRIPTION_ID'); ?>
                        </td>
                        <td class="field_cell">
                            <?php echo $row->subscription_id ; ?>
                        </td>
                    </tr>
                <?php
				}

				if ($row->transaction_id)
				{
				?>
                    <tr>
                        <td class="title_cell">
			                <?php echo Text::_('OSM_TRANSACTION_ID'); ?>
                        </td>
                        <td class="field_cell">
			                <?php echo $row->transaction_id ; ?>
                        </td>
                    </tr>
                <?php
				}
			?>
		<?php
			if ($toAdmin && ($row->payment_method == 'os_offline_creditcard'))
			{
			?>
			<tr>
				<td class="title_cell">
					<?php echo Text::_('OSM_LAST_4DIGITS'); ?>
				</td>
				<td class="field_cell">
					<?php echo $last4Digits; ?>
				</td>
			</tr>
			<?php
			}
		}
	?>
</table>