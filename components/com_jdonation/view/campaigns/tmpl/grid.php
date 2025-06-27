<?php

/**
 * @version        5.4
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

if ($this->config->use_https) {
	$ssl 			= 1;
} else {
	$ssl 			= 0;
}
$bootstrapHelper 	= $this->bootstrapHelper;
$rowFluidClass   	= $bootstrapHelper->getClassMapping('row-fluid');
$span2Class      	= $bootstrapHelper->getClassMapping('span2');
$span6Class      	= $bootstrapHelper->getClassMapping('span6');
$span4Class      	= $bootstrapHelper->getClassMapping('span4');
$span10Class      	= $bootstrapHelper->getClassMapping('span10');
$span12Class      	= $bootstrapHelper->getClassMapping('span12');

$extralayoutCss     = ((int)$this->config->layout_type == 1 ? "dark_layout" : "");
?>
<div id="donation-campaigns" class="<?php echo $rowFluidClass;?> jd-container <?php echo $extralayoutCss; ?>">
	<?php
	if($this->show_category && $this->category_id > 0)
	{
		?>
		<div class="<?php echo $rowFluidClass;?>">
			<div class="<?php echo $span12Class;?>">
				<div class="category_details">
					<h1 class="category-title"><?php echo $this->category->title; ?></h1>
					<?php
					if($this->show_category_description)
					{
						?>
						<div class="clearfix"></div>
						<?php echo $this->category->description; ?>
					<?php
					}		
					?>
				</div>
			</div>
		</div>
		<?php
	}
	
	?>
	<!-- Campaigns List -->
	<?php if(count($this->items))
	{
	    if($this->ncolumns == 2)
        {
            $span = $span6Class;
        }
        else
        {
            $span = $span4Class;
        }
	?>
	    <h1 class="page-title"><?php echo Text::_('JD_CAMPAIGNS'); ?></h1>
        <div class="<?php echo $rowFluidClass;?>">
	    <?php
        $j = 0;
		for ($i = 0 , $n = count($this->items) ;  $i < $n ; $i++) 
		{
		    $j++;
			$item = $this->items[$i];
			$itemId = DonationHelperRoute::findItem($item->id);
			$show_campaign_process		= (int)$this->config->show_campaign_progress;
			if($item->show_campaign == -1)
			{
				$show_campaign_process	= (int)$this->config->show_campaign_progress;
			}
			elseif($item->show_campaign == 0)
			{
				$show_campaign_process	= 0;
			}
			else
			{
				$show_campaign_process	= 1; 
			}
			if((int) $item->goal == 0)
			{
				$item->goal = 100;
			}
			$donatedPercent = ceil($item->total_donated/ $item->goal *100);
			$url = Route::_(DonationHelperRoute::getDonationFormRoute($item->id, $itemId), false, $ssl);
		?>
			<style>
			<?php
			if($item->highlight_color != "")
			{
				?>
				#campaign_<?php echo $item->id;?> .campaign-raised-goal
				{
					border: 1px solid #<?php echo $item->highlight_color; ?> !important;
				}
				#campaign_<?php echo $item->id;?> .campaign-raised-goal .rased
				{
					background-color:#<?php echo $item->highlight_color; ?> !important;
				}
				#campaign_<?php echo $item->id;?> .donate-details .jd-taskbar-grid .btn, .donate-details .jd-taskbar .btn, .donate-details-mod .jd-taskbar .btn
				{
					background-color:#<?php echo $item->highlight_color; ?> !important;
					border:1px solid #<?php echo $item->border_highlight_color; ?> !important;
				}
				<?php
			}
			if($item->progress_color != "")
			{
				?>
				#campaign_<?php echo $item->id;?> .donate-details .progress .bar, .donate-details-mod .progress .bar
				{
					background-color: #<?php echo $item->progress_color; ?> !important;
					background-image: linear-gradient(to bottom, #<?php echo $item->gradient_progress_color; ?>, #<?php echo $item->gradient_progress_color1; ?>) !important;
				}
				<?php
			}
			?>
			</style>
            <div class="<?php echo $span?>" id="campaign_<?php echo $item->id; ?>">
                <div class="jd-row-grid clearfix">
                    <div class="jd-description">
                        <div class="<?php echo $rowFluidClass;?>">
                            <div class="osm-description-details <?php echo $span12Class;?>">
                                <?php
								if($item->campaign_photo != "")
								{
									if(file_exists(JPATH_ROOT.'/images/jdonation/'.$item->campaign_photo))
									{
										?>
										<div class="jd-description-photo">
											<img src="<?php echo Uri::root()?>images/jdonation/<?php echo $item->campaign_photo?>" class="img img-polaroid" />
										</div>
										<?php
									}
									elseif(file_exists(JPATH_ROOT.'/'.$item->campaign_photo))
									{
										?>
										<div class="jd-description-photo">
											<img src="<?php echo Uri::root()?>/<?php echo $item->campaign_photo?>" class="img img-polaroid" />
										</div>
										<?php
									}
								}
                                ?>
								<div class="jd-description1">
									<h3 class="jd_title">
										<a href="<?php echo $url; ?>" title="<?php echo $item->title; ?>">
											<?php echo $item->title; ?>
										</a>
									</h3>
									<?php
									if($this->show_category_description)
									{
										if($item->short_description != "")
										{
											$item->description = HTMLHelper::_('content.prepare', $item->short_description);
										}
										else
										{
											$item->description = HTMLHelper::_('content.prepare', $item->description);
										}
										echo $item->description ;
									}
									?>
								</div>
                            </div>
                        </div>
                    </div>
                    <!-- DONATE BUTTON -->
                    <?php
                    if ($show_campaign_process == 1 && $item->goal > 0)
                    {
                        ?>
                        <div class="donate-details clearfix">
                            <div class="<?php echo $span12Class;?> noleftpadding">
                                <div class="<?php echo $rowFluidClass;?>">
                                    <div class="<?php echo $span12Class;?>">
                                        <div class="campaign-raised-goal">
                                            <div class="rased">
                                                <span class="raised-val number">
                                                    <?php echo DonationHelperHtml::formatAmount($this->config, $item->total_donated,$item->currency_symbol); ?>
                                                </span>
                                                <span class="value">
                                                    <?php echo Text::_('JD_RAISED'); ?>
                                                </span>
                                            </div>
											<?php
											if($item->show_goal == 1)
											{
											?>
                                            <div class="goal">
                                                <span class="goal-val number">
                                                    <?php echo DonationHelperHtml::formatAmount($this->config, $item->goal,$item->currency_symbol) ; ?>
                                                </span>
                                                <span class="value">
                                                    <?php echo Text::_('JD_GOAL'); ?>
                                                </span>
                                            </div>
											<?php
											}	
											?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="progress">
                                    <div class="bar" style="width: <?php echo $donatedPercent; ?>%"></div>
                                </div>

								<div class="<?php echo $rowFluidClass;?>">
									<div class="<?php echo $span12Class;?>">
										<div class="jd-grid-donated">
											<span class="donated-val number"><?php echo $donatedPercent; ?>% </span>
											<span class="value"><?php echo Text::_('JD_DONATED'); ?></span>
										</div>
										<div class="jd-campaign-expiration-grid">
											<?php
											if ($item->days_left > 0)
											{
											?>
												<span class="days-val number"><?php echo $item->days_left; ?></span>
												<span class="value"><?php echo Text::_('JD_DAYS_LEFT'); ?></span>
												<?php
											}
											?>
										</div>	
										<div class="jd-campaign-donors-grid">
											<span class="donors-val number"><?php echo (int)$item->number_donors; ?> </span>
											<span class="value"><?php echo Text::_('JD_DONORS'); ?></span>
										</div>
									</div>
                                </div>
                            </div>
                            <?php
                            if(($item->end_date != "" || $item->end_date != "0000-00-00 00:00:00") && (strtotime($item->end_date) > time()))
                            {
                                ?>
                                <div class="jd-taskbar-grid <?php echo $span12Class; ?>">
                                    <a class="btn btn-primary" href="<?php echo $url; ?>">
                                        <?php echo Text::_('JD_DONATE_NOW'); ?>
                                    </a>
                                </div>
                                <?php
                            }
                            elseif($item->end_date == "" || $item->end_date == "0000-00-00 00:00:00")
                            {
                                ?>
                                <div class="jd-taskbar-grid <?php echo $span12Class; ?>">
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
                        if(($item->end_date != "" || $item->end_date != "0000-00-00 00:00:00") && (strtotime($item->end_date) > time()))
                        {
                            ?>
                            <div class="donate-details clearfix">
                                <div class="<?php echo $rowFluidClass;?>">
                                    <div class="jd-taskbar-grid <?php echo $span12Class; ?>">
                                        <a class="btn btn-primary" href="<?php echo $url; ?>">
                                            <?php echo Text::_('JD_DONATE_NOW'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        elseif($item->end_date == "" || $item->end_date == "0000-00-00 00:00:00")
                        {
                            ?>
                            <div class="donate-details clearfix">
                                <div class="<?php echo $rowFluidClass;?> <?php echo $span12Class; ?>">
                                    <div class="jd-taskbar-grid">
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
            </div>
		<?php
            if($j == $this->ncolumns)
            {
                $j = 0;
                echo "</div><div class='".$rowFluidClass."'>";
            }
		}
		?>
        </div>
        <?php
		if ($this->pagination->total > $this->pagination->limit) 
		{
		?>
			<div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		<?php	
		}    	 
	}
?>		
</div>
