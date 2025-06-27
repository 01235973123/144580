<?php
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Filesystem\Path;

include_once JPATH_ROOT.'/components/com_jdonation/helper/integrations.php';
$campaign_link = Uri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')).Route::_(DonationHelperRoute::getDonationFormRoute($this->campaign->id,Factory::getApplication()->input->getInt('Itemid',0)));
$bootstrapHelper 	= $this->bootstrapHelper;
$rowFluidClass   	= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class		= $bootstrapHelper->getClassMapping('span12');
$span6Class		    = $bootstrapHelper->getClassMapping('span6');
$span4Class		    = $bootstrapHelper->getClassMapping('span4');
$span3Class		    = $bootstrapHelper->getClassMapping('span3');
$span5Class		    = $bootstrapHelper->getClassMapping('span5');
$span7Class		    = $bootstrapHelper->getClassMapping('span7');
$span2Class		    = $bootstrapHelper->getClassMapping('span2');
$controlGroupClass 	= $bootstrapHelper->getClassMapping('control-group');
$inputPrependClass 	= $bootstrapHelper->getClassMapping('input-group');
$addOnClass        	= $bootstrapHelper->getClassMapping('add-on');
$controlLabelClass 	= $bootstrapHelper->getClassMapping('control-label');
$controlsClass     	= $bootstrapHelper->getClassMapping('controls');
$btnClass          	= $bootstrapHelper->getClassMapping('btn');
$inputSmallClass	= $bootstrapHelper->getClassMapping('input-small');
$inputxxLargeClass  = $bootstrapHelper->getClassMapping('input-large');
$inputMediumClass   = $bootstrapHelper->getClassMapping('input-medium');
if($this->campaign->currency_symbol != "")
{
	$this->config->currency_symbol = $this->campaign->currency_symbol;
}

//show campaign process
$show_campaign_process		= (int)$this->config->show_campaign_progress;
if($this->campaign->show_campaign == -1)
{
	$show_campaign_process	= (int)$this->config->show_campaign_progress;
}
elseif($this->campaign->show_campaign == 0)
{
	$show_campaign_process	= 0;
}
else
{
	$show_campaign_process	= 1; 
}
$campaign_color		= '';
if($this->config->campaign_color != '')
{
	$campaign_color			= $this->config->campaign_color;
	if(substr($campaign_color, 0, 1) == "#")
	{
		$campaign_color		= substr($campaign_color, 1);
	}
}
?>
<div class="<?php echo $rowFluidClass;?> campaigndetailsdonationpage" style="background-color:#<?php echo ($campaign_color != '') ? $campaign_color : '7bb4e0'; ?>;">
    <div class="<?php echo $span12Class?>">
        <div class="<?php echo $rowFluidClass;?>">
            <div class="<?php echo $span12Class; ?> center">
                <h1 class="campaign_title">
                    <?php echo $this->campaign->title;?>
                </h1>
            </div>
        </div>
        <div class="<?php echo $rowFluidClass;?>">
            <?php
			$hasPicture = 0;
            if($this->campaign->campaign_photo != '' && is_file(Path::clean(JPATH_ROOT.'/images/jdonation/'.$this->campaign->campaign_photo)))
			{
				$hasPicture = 1;
                $img = Uri::root(true).'/images/jdonation/'.$this->campaign->campaign_photo;
            }
			else
			{
				if($this->config->show_campaign_picture)
				{
					$hasPicture = 1; 
					$img = Uri::root(true).'/media/com_jdonation/assets/images/noimage.jpg';
				}
				else
				{
					$hasPicture = 0; 
					$img = "";
				}
            }
			if($hasPicture == 1)
			{
				?>
				<div class="<?php echo $span6Class?>">
					<img src="<?php echo $img?>" class="campaign_photo">
				</div>
				<?php
				$nextSpan = $span6Class;
			}
			else
			{
				$nextSpan = $span12Class;
			}
			?>
            <div class="<?php echo $nextSpan?>">
                <?php
                if($this->config->show_campaign_progress)
				{
                    ?>
                    <div class="<?php echo $rowFluidClass?>">

                    <?php
                    if($this->campaign->goal > 0) {
                        $totalDonatedAmount = DonationHelper::getTotalDonatedAmount($this->campaign->id);
                        $percentDonatedAmount = round(($totalDonatedAmount/$this->campaign->goal)*100,0);
                        ?>
                        <script
                            src="<?php echo Uri::root(true) ?>/media/com_jdonation/assets/js/jquery.peity.min.js"></script>
                        <div class="<?php echo $span5Class?> circle_graph">
                            <p class="data-attributes">
                                <span data-peity='{ "fill": ["#aaa", "#fff"], "innerRadius": 30, "radius": 40 }'><?php echo $totalDonatedAmount; ?>/<?php echo $this->campaign->goal;?></span>
                            </p>
                            <p class="percentDonatedAmount">
                                <span class="percentDonatedAmount_value">
                                <?php
                                echo $percentDonatedAmount;
                                ?>
                                <sup>%</sup>
                                    </span><span class="percentDonatedAmount_label">
                                    <?php echo Text::_('JD_FUNDED')?></span>
                            </p>
                            <script type="text/javascript">
                                jQuery(".data-attributes span").peity("donut")
                            </script>
                        </div>
                        <div class="<?php echo $span7Class?> noleftmargin">
                            <div class="<?php echo $rowFluidClass?> ">
                                <div class="<?php echo $span12Class?> campaignstatistic percentDonatedAmount">
                                    <span class="percentDonatedAmount_value1">
                                    <?php
                                    echo DonationHelperHtml::formatAmount($this->config, $totalDonatedAmount, $this->config->currency_code);
                                    ?>
                                    </span>
                                    <div class="clearfix"></div>
                                    <span class="percentDonatedAmount_label">
                                        <?php
                                        echo Text::_('JD_DONATED');
                                        ?>
                                    </span>
                                </div>
                            </div>
							<?php
							if($this->campaign->show_goal == 1)
							{
							?>
                            <div class="<?php echo $rowFluidClass?> ">
                                <div class="<?php echo $span12Class?> campaignstatistic percentDonatedAmount">
                                    <span class="percentDonatedAmount_value1">
                                    <?php
                                    echo DonationHelperHtml::formatAmount($this->config, $this->campaign->goal, $this->config->currency_code);
                                    ?>
                                    </span>
                                    <div class="clearfix"></div>
                                    <span class="percentDonatedAmount_label">
                                        <?php
                                        echo Text::_('JD_GOAL');
                                        ?>
                                    </span>
                                </div>
                            </div>
							<?php
							}				
							?>
                        </div>
                    <?php
                    }
                    ?>
                    </div>
                    <div class="<?php echo $rowFluidClass?> ">
                        <div class="<?php echo $span12Class?> campaignstatistic percentDonatedAmount">
                            <span class="percentDonatedAmount_value1">
                            <?php
                            echo DonationHelper::getTotalDonor($this->campaign->id);
                            ?>
                            </span>
                            <div class="clearfix"></div>
                            <span class="percentDonatedAmount_label">
                                <?php
                                echo Text::_('JD_DONORS');
                                ?>
                            </span>
                        </div>
                    </div>
                    <?php
                    if(DonationHelper::getLeftDates($this->campaign->id) > 0)
					{
                        ?>
                        <div class="<?php echo $rowFluidClass?> ">
                            <div class="<?php echo $span12Class?> campaignstatistic percentDonatedAmount">
                            <span class="percentDonatedAmount_value1">
                            <?php
                            echo DonationHelper::getLeftDates($this->campaign->id);
                            ?>
                            </span>
                                <div class="clearfix"></div>
                                <span class="percentDonatedAmount_label">
                                <?php
                                echo Text::_('JD_DAYS_LEFT');
                                ?>
                            </span>
                            </div>
                        </div>
                        <?php
                    }

                    ?>
                    <?php
                }
				else
				{
                    ?>
                    <div class="<?php echo $rowFluidClass?>">
                        <div class="<?php echo $span12Class?>">
                            <span class="campaign_description_top">
                            <?php
                            echo HTMLHelper::_('content.prepare', $this->campaign->description);
                            ?>
                            </span>
                        </div>
                    </div>
                    <?php
                }
                
                ?>
            </div>
        </div>
    </div>
</div>
