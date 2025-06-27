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

if ($this->item->state)
{
	$state = OSMembershipHelper::getStateName($this->item->country, $this->item->state);
}
else
{
	$state = '';
}

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');

$currencySymbol = $this->item->currency_symbol ?: $this->item->currency;
?>
<div id="osm-subscription-detail-page" class="osm-container">
<h1 class="osm-page-title"><?php echo Text::_('OSM_SUBSCRIPTION_DETAIL'); ?></h1>
<form method="post" name="osm_form" id="osm_form" class="<?php echo $bootstrapHelper->getClassMapping('form form-horizontal'); ?>">
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('OSM_PLAN'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo $this->item->plan_title ; ?>
		</div>
	</div>
	<?php
	if ($this->item->membership_id)
	{
	?>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('OSM_MEMBERSHIP_ID'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo OSMembershipHelper::formatMembershipId($this->item, $this->config); ?>
			</div>
		</div>
	<?php
	}

	$fields = $this->form->getFields();

	foreach ($fields as $field)
	{
		if (!$field->visible)
		{
			continue;
		}

		switch (strtolower($field->type))
		{
			case 'heading' :
				?>
				<h3 class="osm-heading"><?php echo Text::_($field->title) ; ?></h3>
				<?php
				break ;
			case 'message' :
				?>
					<div class="control-group osm-message">
						<?php echo $field->description ; ?>
					</div>
					<?php
				break ;
			default:
				?>
				<div class="<?php echo $controlGroupClass; ?>">
					<label class="<?php echo $controlLabelClass; ?>">
						<?php echo Text::_($field->title); ?>
					</label>
					<div class="<?php echo $controlsClass; ?>">
						<?php
							if ($field->name == 'state')
							{
								$fieldValue = $state;
							}
							elseif ($field->type == 'Date' && $field->value)
							{
								try
								{
									$fieldValue = HTMLHelper::_('date', $field->value, $this->config->date_format, null);
								}
								catch (Exception $e)
								{
									$fieldValue = $field->value;
									// Do-nothing
								}
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
					</div>
				</div>
				<?php
				break;
		}
	}
	?>
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('OSM_CREATED_DATE'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo HTMLHelper::_('date', $this->item->created_date, $this->config->date_format) ; ?>
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('OSM_SUBSCRIPTION_START_DATE'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo HTMLHelper::_('date', $this->item->from_date, $this->config->date_format) ; ?>
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('OSM_SUBSCRIPTION_END_DATE'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php
				if ($this->item->lifetime_membership || $this->item->to_date == '2099-12-31 23:59:59')
				{
					echo Text::_('OSM_LIFETIME');
				}
				else
				{
					echo HTMLHelper::_('date', $this->item->to_date, $this->config->date_format);
				}
			?>
		</div>
	</div>
	<?php
		if ($this->item->setup_fee > 0)
		{
		?>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo  Text::_('OSM_SETUP_FEE'); ?>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<?php echo OSMembershipHelper::formatCurrency($this->item->setup_fee, $this->config, $currencySymbol); ?>
				</div>
			</div>
		<?php
		}
	?>
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('OSM_NET_AMOUNT'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo OSMembershipHelper::formatCurrency($this->item->amount, $this->config, $currencySymbol); ?>
		</div>
	</div>
	<?php
	if ($this->item->discount_amount > 0)
	{
	?>
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('OSM_DISCOUNT_AMOUNT'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo OSMembershipHelper::formatCurrency($this->item->discount_amount, $this->config, $currencySymbol); ?>
		</div>
	</div>
	<?php
	}
	if ($this->item->tax_amount > 0)
	{
	?>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo  Text::_('OSM_TAX_AMOUNT'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo OSMembershipHelper::formatCurrency($this->item->tax_amount, $this->config, $currencySymbol); ?>
			</div>
		</div>
	<?php
	}
	?>

	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('OSM_GROSS_AMOUNT'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo OSMembershipHelper::formatCurrency($this->item->gross_amount, $this->config, $currencySymbol); ?>
		</div>
	</div>
	<?php
		if ($this->item->gross_amount > 0)
		{
		?>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo  Text::_('OSM_PAYMENT_METHOD'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php
					$method = OSMembershipHelperPayments::loadPaymentMethod($this->item->payment_method) ;

					if ($method)
					{
						echo Text::_($method->title);
					}
				?>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('OSM_TRANSACTION_ID'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->item->transaction_id ; ?>
			</div>
		</div>
		<?php
		}
	?>
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('OSM_SUBSCRIPTION_STATUS'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php
			switch ($this->item->published)
			{
				case 0 :
					echo Text::_('OSM_PENDING');
					break ;
				case 1 :
					echo Text::_('OSM_ACTIVE');
					break ;
				case 2 :
					echo Text::_('OSM_EXPIRED');
					break ;
				case 3 :
					echo Text::_('OSM_CANCELLED_PENDING');
					break ;
				case 4 :
					echo Text::_('OSM_CANCELLED_REFUNDED');
					break ;
			}
			?>
		</div>
	</div>
	<div class="form-actions">
		<input type="button" class="btn btn-primary" onclick="window.history.back();" value="<?php echo Text::_('OSM_BACK'); ?>" />
	</div>
</form>
</div>