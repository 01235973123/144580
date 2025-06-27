<?php

/**
 * @version        5.6.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$percent            =  100 - DonationHelper::getConfigValue('percent_commission') ;
$bootstrapHelper    = $this->bootstrapHelper;
$rowFluidClass   	= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class   	    = $bootstrapHelper->getClassMapping('span12');
$span8Class   	    = $bootstrapHelper->getClassMapping('span8');
$span6Class   	    = $bootstrapHelper->getClassMapping('span6');
$span4Class   	    = $bootstrapHelper->getClassMapping('span4');
$extralayoutCss     = ((int)$this->config->layout_type == 1 ? "dark_layout" : "");
?>

<div class="<?php echo $rowFluidClass;?>">
	<?php
	for ($i = 0 , $n = count($this->items) ; $i < $n ; $i++)
	{
		$row = $this->items[$i] ;
		
		if ($row->hide_me == 1)
		{
			$row->first_name = Text::_('JD_ANONYMOUS');
			$row->last_name = '' ;
		}
		?>
		<div class="<?php echo $span6Class;?>">
			<div class="donor-grid-box">
				<div class="donor-grid-box-container">
					<div class="donor-grid-box-container-variation" style="flex-direction: row;align-items:  flex-end;">
						<h4 class="donor-grid-box-container-variation__name">
							<?php echo $row->first_name; ?>&nbsp;<?php echo $row->last_name ; ?>
						</h4>
						<p class="donor-grid-box-container-variation__timestamp">
							<?php echo  HTMLHelper::_('date', $row->created_date, $this->config->date_format, null); ?>
						</p>
					</div>
					<div class="donor-grid-box-donor-details">
						<div class="donor-grid-box-donor-details__wrapper">
							<span>
								<?php echo Text::_('JD_DONATION_AMOUNT');?>
							</span>
						</div>
						<span class="donor-grid-box-donor-details__total">
							<?php
							if(DonationHelper::isMultipleCurrencies())
							{
								if(($row->amount_converted > 0) && ($row->currency_converted != $row->currency_code) && $row->currency_converted !='' )
								{
									$show_amount = 1;
									
									echo number_format($row->amount_converted , 2);
									echo "&nbsp;";
									echo $row->currency_converted;
									?>
									<div class="clearfix"></div>
									<?php
									
									if($this->config->convert_currency_before_donation)
									{
										$show_amount = 1;
										
										echo number_format($row->amount , 2);
										echo "&nbsp;";
										if($row->currency_code != "")
										{
											echo $row->currency_code;
										}
										else
										{
											echo $this->config->currency;
										}
											
									}
								}
								else
								{
									$show_amount = 1;
									
									echo number_format($row->amount , 2);
									echo "&nbsp;";
									if($row->currency_code != "")
									{
										echo $row->currency_code;
									}
									else
									{
										echo $this->config->currency;
									}
										
								}
							}

							if($show_amount == 0)
							{
								
								echo number_format($row->amount , 2);
								echo "&nbsp;";
								if($row->currency_code != "")
								{
									echo $row->currency_code;
								}
								else
								{
									echo $this->config->currency;
								}
									
							}	
							?>
						</span>
					</div>
				</div>
			</div>
		</div>
		<?php
		if($i % 2 == 1)
		{
			?>
			</div>
			<div class="<?php echo $rowFluidClass?>" >
			<?php
		}
	}
	?>
</div>
