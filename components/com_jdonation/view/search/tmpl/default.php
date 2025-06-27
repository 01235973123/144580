<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
/**
 * @version        5.7.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
if ($this->config->use_https) {
	$ssl 			= 1;
} else {
	$ssl 			= 0;
}
$bootstrapHelper 	= $this->bootstrapHelper;
$rowFluidClass   	= $bootstrapHelper->getClassMapping('row-fluid');
$span2Class      	= $bootstrapHelper->getClassMapping('span2');
$span5Class      	= $bootstrapHelper->getClassMapping('span5');
$span10Class      	= $bootstrapHelper->getClassMapping('span10');
$span12Class      	= $bootstrapHelper->getClassMapping('span12');
?>
<div id="donation-campaigns" class="<?php echo $rowFluidClass;?> jd-container">
	<!-- Campaigns List -->
	<?php if(count($this->items))		 
	{
	?>	    
	<h1 class="page-title"><?php echo Text::_('JD_SEARCH_RESULT'); ?></h1>
	<?php	        
		for ($i = 0 , $n = count($this->items) ;  $i < $n ; $i++) 
		{	        	
			$item = $this->items[$i];
			$donatedPercent = ceil($item->total_donated/ $item->goal *100);
			$url = Route::_(DonationHelperRoute::getDonationFormRoute($item->id, $this->Itemid), false, $ssl);
		?>
			<div class="jd-row clearfix">
				<div class="jd-description">
					<div class="<?php echo $rowFluidClass;?>">
						<div class="<?php echo $span12Class;?>">
                            <?php
                            if(($item->campaign_photo != "") && (JPATH_ROOT.'/images/jdonation/'.$item->campaign_photo)){
                                ?>
                                <div class="jd-description-photo">
                                    <img src="<?php echo Uri::root()?>images/jdonation/<?php echo $item->campaign_photo?>" class="img img-polaroid" />
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="<?php echo $rowFluidClass;?>">
                        <div class="<?php echo $span12Class;?>">
                            <h3 class="jd_title">
                                <a href="<?php echo $url; ?>" title="<?php echo $item->title; ?>">
                                    <?php echo $item->title; ?>
                                </a>
                            </h3>
							<?php
                                if($item->short_description != "")
                                {
                                    $item->description = HTMLHelper::_('content.prepare', $item->short_description);
                                }
                                else
                                {
                                    $item->description = HTMLHelper::_('content.prepare', $item->description);
                                }
								echo $item->description ;
							?>
						</div>
					</div>
				</div>
				<!-- DONATE BUTTON -->
					<?php
					if ($this->config->show_campaign_progress !== '0' && $item->goal > 0)
					{
					?>
						<div class="donate-details clearfix <?php echo $rowFluidClass;?>">
							<div class="<?php echo $span10Class;?> noleftpadding">
								<div class="<?php echo $rowFluidClass;?>">
									<div class="<?php echo $span5Class;?>">
                                        <div class="campaign-raised-goal">
                                            <div class="rased">
                                                <span class="raised-val number">
                                                    <?php echo DonationHelperHtml::formatAmount($this->config, $item->total_donated); ?>
                                                </span>
                                                <span class="value">
                                                    <?php echo Text::_('JD_RAISED'); ?>
                                                </span>
                                            </div>
                                            <div class="goal">
                                                <span class="goal-val number">
                                                    <?php echo DonationHelperHtml::formatAmount($this->config, $item->goal) ; ?>
                                                </span>
                                                <span class="value">
                                                    <?php echo Text::_('JD_GOAL'); ?>
                                                </span>
                                            </div>
                                        </div>
									</div>
									<div class="<?php echo $span2Class; ?> campaign-donated">
                                        <span class="donated-val number"><?php echo $donatedPercent; ?>% </span>
                                        <span class="value"><?php echo Text::_('JD_DONATED'); ?></span>
									</div>
									<div class="<?php echo $span2Class; ?> campaign-donors">
                                        <span class="donors-val number"><?php echo (int)$item->number_donors; ?> </span>
                                        <span class="value"><?php echo Text::_('JD_DONORS'); ?></span>
									</div>
                                    <?php
                                    if ($item->days_left > 0)
                                    {
                                        ?>
                                        <div class="<?php echo $span2Class; ?> campaign-expiration">
                                            <span class="days-val number"><?php echo $item->days_left; ?></span>
                                            <span class="value"><?php echo Text::_('JD_DAYS_LEFT'); ?></span>
                                        </div>
                                    <?php
                                    }
                                    ?>
								</div>
								<div class="progress">
									<div class="bar" style="width: <?php echo $donatedPercent; ?>%"></div>
								</div>
							</div>
							<?php
							if((($item->end_date != "") || ($item->end_date != "0000-00-00 00:00:00")) && (strtotime($item->end_date) > time()))
							{
							?>
                                <div class="jd-taskbar <?php echo $span2Class; ?>">
                                    <a class="btn btn-primary" href="<?php echo $url; ?>">
                                        <?php echo Text::_('JD_DONATE_NOW'); ?>
                                    </a>
                                </div>
							<?php
							}
							elseif(($item->end_date == "") || ($item->end_date == "0000-00-00 00:00:00"))
                            {
								?>
								<div class="jd-taskbar <?php echo $span2Class; ?>">
									<a class="btn btn-primary" href="<?php echo $url; ?>">
										<?php echo Text::_('JD_DONATE_NOW'); ?>
									</a>
								</div>
								<?php
							}
							?>
					</div>
					<?php
					}
					else
					{
						if((($item->end_date != "") || ($item->end_date != "0000-00-00 00:00:00")) && (strtotime($item->end_date) > time()))
						{
							?>
							<div class="donate-details clearfix">
								<div class="<?php echo $rowFluidClass;?>">
									<div class="jd-taskbar" style="float:right;">
										<a class="btn btn-primary" href="<?php echo $url; ?>">
											<?php echo Text::_('JD_DONATE_NOW'); ?>
										</a>
									</div>
								</div>
							</div>
							<?php
                        }
                        elseif(($item->end_date == "") || ($item->end_date == "0000-00-00 00:00:00"))
                        {
							?>
							<div class="donate-details clearfix">
								<div class="<?php echo $rowFluidClass;?>">
									<div class="jd-taskbar" style="float:right;">
										<a class="btn btn-primary" href="<?php echo $url; ?>">
											<?php echo Text::_('JD_DONATE_NOW'); ?>
										</a>
									</div>
								</div>
							</div>
							<?php
						}
					}
					?>
				<!-- END DONATE BUTTON -->
			</div>
		<?php	
		}
		if ($this->pagination->total > $this->pagination->limit) 
		{
		?>
			<div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		<?php	
		}    	 
	}
	else
    {
        ?>
        <div class="<?php echo $span12Class?>">
            <?php
            echo Text::_('JD_NO_CAMPAIGNS_FOUND');
            ?>
        </div>
        <?php
    }
?>		
</div>
