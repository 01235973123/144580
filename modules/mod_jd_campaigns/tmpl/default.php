<?php

/**
 * @version		    5.4.10
 * @package		    Joomla
 * @subpackage	    Joom Donation
 * @author          Tuan Pham Ngoc
 * @copyright	    Copyright (C) 2009 - 2023 Ossolution Team
 * @license		    GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
?>
<form name="campaign_form" id="campaign_form" method="post" action="<?php echo Route::_('index.php?option=com_jdonation&task=donation_form&Itemid='.$itemId, false, (int) $config->use_https); ?>">
<div class="campains-list">
<?php
	if($number_columns > 1)
	{
		$elementSpanNumber = 12/$number_columns;
		$elementSpan	   = $bootstrapHelper->getClassMapping('span'.$elementSpanNumber);
	}

	$k = 0 ;
	if($number_columns > 1)
	{
		?>
		<div class="<?php echo $rowFluidClass;?>">
		<?php
	}
	$j = 0;
	for ($i = 0 , $n = count($rows) ; $i < $n ; $i++)
    {
		$j++;
		$row = $rows[$i];
		if($row->goal > 0)
		{
			$donatedPercent = ceil($row->total_donated/ $row->goal *100);
		}
		else
		{
			$donatedPercent = 0;
		}
	?>
	<?php
	if($number_columns > 1)
	{
		?>
		<div class="<?php echo $elementSpan;?>">
		<?php
	}	
	?>
		<div class="campain-list">
			<div class="campaign-description">
				<?php
				if($row->campaign_photo != "")
				{
					if(file_exists(JPATH_ROOT.'/images/jdonation/'.$row->campaign_photo))
					{
						?>
						<div class="jd-description-photo">
							<img src="<?php echo Uri::root()?>images/jdonation/<?php echo $row->campaign_photo?>" class="img img-polaroid" />
						</div>
						<?php
					}
					elseif(file_exists(JPATH_ROOT.'/'.$row->campaign_photo))
					{
						?>
						<div class="jd-description-photo">
							<img src="<?php echo Uri::root()?>/<?php echo $row->campaign_photo?>" class="img img-polaroid" />
						</div>
						<?php
					}
				}
				?>
				<?php
				if ($showTitle)
				{
				?>
					<h3 class="jd_title"><?php echo $row->title; ?></h3>
				<?php
				}
				?>
				<?php
				$description = $row->short_description ;
				if($description == "")
				{
					$description = $row->description;
				}
				$description = strip_tags($description);
				if($description != "")
				{
					$descArr = explode(" ",$description);
					if(count($descArr) > 20)
					{
						for($j=0;$j<20;$j++)
						{
							echo $descArr[$j]." ";
						}
						echo "..";
					}
					else
					{
						echo $description;
					}
					?>
					<?php
				}
				?>
			</div>
			<div class="campaign-details">
				<div class="campaign-donate-info">
					<?php
					if($row->start_date != '' && $row->start_date != '0000-00-00 00:00:00' && $showCampaignDate == 1)
					{
						?>
						<span class="start-date"><span class="fa fa-calendar-o icon-calendar"></span>&nbsp;
							<strong><?php echo Text::_('JD_START_DATE')?>:</strong>&nbsp;<?php echo date("M, j Y",strtotime($row->start_date));?>
						</span>
						<?php
					}
					if($row->end_date != '' && $row->end_date != '0000-00-00 00:00:00' && $showCampaignDate == 1)
					{
						?>
						<span class="end-date"><span class="fa fa-calendar-o icon-calendar"></span>&nbsp;
							<strong><?php echo Text::_('JD_END_DATE')?>:</strong>&nbsp;<?php echo date("M, j Y",strtotime($row->end_date));?>
						</span>
						<?php
					}
					if ($showNumberDonors)
					{
						?>
						<span class="donors">
						<span class="fa fa-users icon-users">&nbsp;
						</span><strong><?php echo Text::_('JD_DONORS') ?>
								:</strong>&nbsp;<?php echo (int)$row->number_donors; ?>
					</span>
						<?php
					}
					?>
				</div>
				<?php
				if ($showDonatedAmount || $showGoal)
				{
					
				?>
					<div class="campaign-raised-goal">
						<?php
						if($showDonatedAmount) {
							?>
							<div class="raised">
								<span class="raised-val"><?php echo DonationHelperHtml::formatAmount($config, $row->total_donated, $row->currency_symbol); ?></span>
								<span class="value"><?php echo Text::_('JD_RAISED'); ?></span>
							</div>
							<?php
						}
						?>
						<?php
						if($showGoal){
						?>
						<div class="goal">
							<span class="goal-val"><?php echo DonationHelperHtml::formatAmount($config, $row->goal, $row->currency_symbol); ?></span>
							<span class="value"><?php echo Text::_('JD_GOAL'); ?></span>
						</div>
						<?php } ?>

						<div class="process-bar">
							<span class="process" style="width: <?php echo $donatedPercent; ?>%"></span>
						</div>
					</div>
				<?php
				} ?>

				<?php
				if ($showButton)
				{
				?>
					<div class="mod-jd-campaign-donate-now">
						<a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_jdonation&view=donation&campaign_id='.$row->id.'&Itemid='.$Itemid);?>"><?php echo Text::_('JD_DONATE_NOW');?></a>
					</div>
				<?php
				}
				?>
			</div>
		</div>
		<?php
		if($number_columns > 1)
		{
			?>
			</div>
			<?php
			if($j == $number_columns)
			{
				?>
				</div>
				<div class="<?php echo $elementSpan;?>">
				<?php
				$open = 1;
			}
			else
			{
				$open = 0;
			}

			$j = 0;
		}

		if($open == 1)
		{
			echo "</div>";
		}
		?>
	<?php	
	}
?>
<input type="hidden" name="campaign_id" value="0" />
</div>	
	<script type="text/javascript">
		function donationForm(campaignId, form)
        {
			form.campaign_id.value = campaignId ;
			form.submit();
		}		
	</script>
</form>
