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

if($this->config->qr_code)
{
	
	if(!file_exists(JPATH_ROOT . '/media/com_jdonation/qrcodes/'.$this->campaignId.'.png'))
	{
		DonationHelper::generateQrCode($this->campaignId);
	}
}

$hasPicture = 0;
$img = '';

$campaignPhoto = trim($this->campaign->campaign_photo);

if ($campaignPhoto !== '') {
    $imgPaths = [
        Path::clean(JPATH_ROOT . '/images/jdonation/' . $campaignPhoto) => Uri::root(true) . '/images/jdonation/' . $campaignPhoto,
        Path::clean(JPATH_ROOT . '/' . $campaignPhoto) => Uri::root(true) . '/' . $campaignPhoto
    ];

    foreach ($imgPaths as $filePath => $imgUrl) {
        if (is_file($filePath)) {
            $hasPicture = 1;
            $img = $imgUrl;
            break;
        }
    }

    if (!$hasPicture && $this->config->show_campaign_picture) {
        $hasPicture = 1;
        $img = Uri::root(true) . '/media/com_jdonation/assets/images/noimage.jpg';
    }
} else {
    if ($this->config->show_campaign_picture) {
        $hasPicture = 1;
        $img = Uri::root(true) . '/media/com_jdonation/assets/images/noimage.jpg';
    }
}
?>
<!-- Campaign Header -->
<div class="default-layout-campaign-header">
    <?php
    if($hasPicture == 1)
	{
    ?>
        <img class="default-layout-campaign-image" src="<?php echo $img?>" alt="<?php echo $this->campaign->title;?>">
    <?php
    }
    ?>
    <div class="default-layout-campaign-info">
        <div class="default-layout-campaign-title"><?php echo $this->campaign->title;?></div>
        <?php
        if($this->campaign->short_description != ""){
        ?>
            <div style="font-size:1.07rem; color:#666; margin-bottom:4px;">
                <?php echo HTMLHelper::_('content.prepare', $this->campaign->short_description); ?>
            </div>
        <?php } ?>
        <?php
        if($this->config->show_campaign_progress && $this->campaign->goal > 0){
            $totalDonatedAmount = DonationHelper::getTotalDonatedAmount($this->campaign->id);
            $percentDonatedAmount = round(($totalDonatedAmount/$this->campaign->goal)*100,0);
            ?>
            <div class="default-layout-campaign-progress-bar">
                <div class="default-layout-campaign-progress-fill" style="width:<?php echo $percentDonatedAmount; ?>%;"></div>
                <div class="default-layout-campaign-progress-text"><?php echo $percentDonatedAmount; ?>% <?php echo Text::_('JD_FUNDED'); ?></div>
            </div>
        <?php } ?>
        <div class="default-layout-campaign-meta">
            <?php
            if($totalDonatedAmount > 0){
            ?>
                <span><?php echo DonationHelperHtml::formatAmount($this->config, $totalDonatedAmount, $this->config->currency_code);?> <?php echo Text::_('JD_RAISED'); ?></span> &nbsp;|&nbsp; 
            <?php } 
            if($this->campaign->goal > 0){
            ?>
            <span><?php echo Text::_('JD_CAMPAIGN_GOAL'); ?>: <?php echo DonationHelperHtml::formatAmount($this->config, $this->campaign->goal, $this->config->currency_code);?></span> &nbsp;|&nbsp;
            <?php } ?>
            <span><?php echo DonationHelper::getTotalDonor($this->campaign->id);?> <?php echo Text::_('JD_DONORS'); ?></span>
        </div>
    </div>
</div>

<!-- Campaign Description (with collapse if long) -->
<div id="aboutCampaign" class="default-layout-about-campaign collapsed">
    <strong><?php echo Text::_('JD_ABOUT_THE_CAMPAIGN'); ?>:</strong>
    <?php echo $this->campaign->description; ?>
</div>
<button class="default-layout-show-more" id="showMoreBtn" onclick="toggleDesc()"><?php echo Text::_('JD_SHOW_MORE'); ?></button>


<!--
<div class="<?php echo $rowFluidClass;?> campaigndetailsdonationpage" style="background-color:#<?php echo ($campaign_color != '') ? $campaign_color : '7bb4e0'; ?>;">
	<?php
	if($this->config->qr_code && file_exists(JPATH_ROOT . '/media/com_jdonation/qrcodes/'.$this->campaignId.'.png'))
	{
	?>
	<div class="qr_code_bar">
		<img src="<?php echo Uri::root()?>media/com_jdonation/qrcodes/<?php echo $this->campaignId?>.png" />
	</div>
	<?php
	}
	?>
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
            if($this->campaign->campaign_photo != '')
			{
				if(is_file(Path::clean(JPATH_ROOT.'/images/jdonation/'.$this->campaign->campaign_photo)))
				{
					$hasPicture = 1;
					$img = Uri::root(true).'/images/jdonation/'.$this->campaign->campaign_photo;
				}
				elseif(is_file(Path::clean(JPATH_ROOT.'/'.$this->campaign->campaign_photo)))
				{
					$hasPicture = 1;
					$img = Uri::root(true).'/'.$this->campaign->campaign_photo;
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
                    if(DonationHelper::getLeftDates($this->campaign->id) > 0){
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
                }else{
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
                if($this->config->social_sharing == 1){
                    ?>
                    <div class="<?php echo $rowFluidClass?>">
                        <div class="<?php echo $span12Class?> center sharingtop">
                            <script type="text/javascript" src="<?php echo DonationHelper::getSiteUrl().'media/com_jdonation/assets/js/fblike.js'?>"></script>
                            <?php
                            if($this->config->social_sharing_type == 0)
                            {
                                $add_this_share='
                                <!-- AddThis Button BEGIN -->
                                <div class="addthis_toolbox addthis_default_style">
                                <a class="addthis_button_facebook_like" fb:like:layout="button_count" class="addthis_button" addthis:url="'.$campaign_link.'"></a>
                                <a class="addthis_button_tweet" class="addthis_button" addthis:url="'.$campaign_link.'"></a>
                                <a class="addthis_button_pinterest_pinit" class="addthis_button" addthis:url="'.$campaign_link.'"></a>
                                <a class="addthis_counter addthis_pill_style" class="addthis_button" addthis:url="'.$campaign_link.'"></a>
                                </div>
                                <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid='.$this->config->addthis_publisher.'"></script>
                                <!-- AddThis Button END -->' ;
                                $add_this_js='https://s7.addthis.com/js/300/addthis_widget.js';
                                $JdonationIntegrationsHelper=new JdontionIntegrationsHelper();
                                $JdonationIntegrationsHelper->loadScriptOnce($add_this_js);
                                //output all social sharing buttons
                                echo' <div id="rr" style="width:300px;">
                                    <div class="social_share_container">
                                    <div class="social_share_container_inner">'.
                                        $add_this_share.
                                    '</div>
                                </div>
                                </div>
                                ';
                            }
                            else
                            {
                                ?>
                                <ul class="campaign-sharing share horizontal">
                                    <li>
                                        <h6>
                                            <?php echo Text::_('JD_SHARE');?>
                                        </h6>
                                    </li>
                                    <li>
                                        <a href="https://twitter.com/intent/tweet?text=<?php echo $this->campaign->title?>&url=<?php echo $campaign_link;?>" title="X" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#FFF" class="bi bi-twitter-x" viewBox="0 0 16 16">
											  <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865l8.875 11.633Z"/>
											</svg>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.facebook.com/share.php?u=<?php echo $campaign_link;?>" title="Facebook" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
  <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
</svg>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo $campaign_link;?>" title="Linkedin" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-linkedin" viewBox="0 0 16 16">
  <path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z"/>
</svg>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="http://pinterest.com/pin/create/button/?url=<?php echo $campaign_link;?>" title="Pinterest" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-pinterest" viewBox="0 0 16 16">
												  <path d="M8 0a8 8 0 0 0-2.915 15.452c-.07-.633-.134-1.606.027-2.297.146-.625.938-3.977.938-3.977s-.239-.479-.239-1.187c0-1.113.645-1.943 1.448-1.943.682 0 1.012.512 1.012 1.127 0 .686-.437 1.712-.663 2.663-.188.796.4 1.446 1.185 1.446 1.422 0 2.515-1.5 2.515-3.664 0-1.915-1.377-3.254-3.342-3.254-2.276 0-3.612 1.707-3.612 3.471 0 .688.265 1.425.595 1.826a.24.24 0 0 1 .056.23c-.061.252-.196.796-.222.907-.035.146-.116.177-.268.107-1-.465-1.624-1.926-1.624-3.1 0-2.523 1.834-4.84 5.286-4.84 2.775 0 4.932 1.977 4.932 4.62 0 2.757-1.739 4.976-4.151 4.976-.811 0-1.573-.421-1.834-.919l-.498 1.902c-.181.695-.669 1.566-.995 2.097A8 8 0 1 0 8 0z"/>
											</svg>
                                        </a>
                                    </li>
									<li>
										<a href="#" id="sharecampaign" class="sharecampaign" title="<?php echo Text::_('JD_SHARE_THIS_CAMPAIGN_TO_YOUR_FRIEND');?>">
											<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-envelope-at" viewBox="0 0 16 16">
  <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/>
  <path d="M14.247 14.269c1.01 0 1.587-.857 1.587-2.025v-.21C15.834 10.43 14.64 9 12.52 9h-.035C10.42 9 9 10.36 9 12.432v.214C9 14.82 10.438 16 12.358 16h.044c.594 0 1.018-.074 1.237-.175v-.73c-.245.11-.673.18-1.18.18h-.044c-1.334 0-2.571-.788-2.571-2.655v-.157c0-1.657 1.058-2.724 2.64-2.724h.04c1.535 0 2.484 1.05 2.484 2.326v.118c0 .975-.324 1.39-.639 1.39-.232 0-.41-.148-.41-.42v-2.19h-.906v.569h-.03c-.084-.298-.368-.63-.954-.63-.778 0-1.259.555-1.259 1.4v.528c0 .892.49 1.434 1.26 1.434.471 0 .896-.227 1.014-.643h.043c.118.42.617.648 1.12.648m-2.453-1.588v-.227c0-.546.227-.791.573-.791.297 0 .572.192.572.708v.367c0 .573-.253.744-.564.744-.354 0-.581-.215-.581-.8Z"/>
</svg>
										</a>
									</li>
                                </ul>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div class="<?php echo $rowFluidClass?> ">
                    <div class="<?php echo $span12Class?> center">
                        <?php
                        if((($this->campaign->end_date != "") || ($this->campaign->end_date != "0000-00-00 00:00:00")) && (strtotime($this->campaign->end_date) > time())){
                            ?>
                            <a class="btn btn-primary donationbutton" href="javascript:void(0);">
                                <?php echo Text::_('JD_DONATE_NOW'); ?>
                            </a>
                        <?php } elseif(($this->campaign->end_date == "") || ($this->campaign->end_date == "0000-00-00 00:00:00")){
                            ?>
                            <a class="btn btn-primary donationbutton" href="javascript:void(0);">
                                <?php echo Text::_('JD_DONATE_NOW'); ?>
                            </a>
                            <?php
                        }?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                    -->
<?php
$user = Factory::getApplication()->getIdentity();

?>
<div id="shareCampaignModal" class="campaign-modal">
    <div class="share-modal-content">
        <span class="close-btn">&times;</span>
		<div class="alert alert-info" role="alert" id="share_alert_bar">
			<?php echo Text::_('JD_FILL_THE_SHARING_FORM');?>
		</div>
		<div id="responseMessage"></div>
		<form id="campaignSharingForm" class="container mt-5">
			<?php
			if((int)$user->id == 0)
			{
				?>
				<div class="<?php echo $rowFluidClass; ?>">
					<div class="form-group <?php echo $span6Class?>">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text"><i class="fas fa-user"></i></span>
							</div>
							<input type="text" class="form-control" id="myname" name="myname" placeholder="<?php echo Text::_('JD_ENTER_YOUR_NAME')?>" required>
						</div>
					</div>
					<div class="form-group <?php echo $span6Class?>">
						<div class="input-group">
							<div class="input-group-prepend">
								<span class="input-group-text"><i class="fas fa-user"></i></span>
							</div>
							<input type="text" class="form-control" id="myemail" id="myemail" placeholder="<?php echo Text::_('JD_ENTER_YOUR_EMAIL')?>" required>
						</div>
					</div>
				</div>
				<?php
			}
			?>
			<!-- Name field -->
			<div class="<?php echo $rowFluidClass; ?>">
				<div class="form-group <?php echo $span6Class?>">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-user"></i></span>
						</div>
						<input type="text" class="form-control" id="friendname" name="friendname" placeholder="<?php echo Text::_('JD_ENTER_YOUR_FRIEND_NAME');?>" required>
					</div>
				</div>

				<!-- Email field with icon -->
				<div class="form-group <?php echo $span6Class?>">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text"><i class="fas fa-envelope"></i></span>
						</div>
						<input type="email" class="form-control" id="friendemail" name="friendemail" placeholder="<?php echo Text::_('JD_ENTER_YOUR_FRIEND_EMAIL');?>" required>
					</div>
				</div>
			</div>

			<!-- Submit button -->
			<input type="hidden" name="campaign_url" value="<?php echo base64_encode(Uri::getInstance()->toString());?>" />
			<button type="submit" class="btn btn-primary"><?php echo Text::_('JD_SHARE'); ?></button>
		</form>
    </div>
</div>
<script type="text/javascript">
jQuery('.donationbutton').click(function() {
    jQuery('html, body').animate({
        scrollTop: jQuery("#donation_form").offset().top
    }, 1000);
});
var modalLogin			= document.querySelector("#shareCampaignModal");
const openPopupBtn		= document.querySelector("#sharecampaign");
var modalLoginIframe	= document.querySelector("#modalShareCampaignIframe");
var closeBtn			= document.querySelector(".close-btn");

if (openPopupBtn) {
	openPopupBtn.addEventListener('click', () => {
		modalLogin.style.display = 'flex';
	});
}

closeBtn.onclick = function() {
    modalLogin.style.display = "none";
};

window.onclick = function(event) {
    if (event.target == modalLogin) {
        modalLogin.style.display = "none";
    }
};

const shareform = document.querySelector('#campaignSharingForm');
const responseMessage = document.querySelector('#responseMessage');

// Add an event listener for form submission
if(shareform)
{
	shareform.addEventListener('submit', function (e) {
		e.preventDefault();  // Prevent the form from submitting normally

		// Create a new FormData object to capture the form data
		const formData = new FormData(shareform);

		// Create a new XMLHttpRequest object
		const xhr = new XMLHttpRequest();

		// Configure the request (POST method and URL)
		//alert('<?php echo Uri::root(); ?>index.php?option=com_jdonation&task=share_campaign&format=raw&id=<?php echo $this->campaignId; ?>&no_html=1&tmpl=component');
		xhr.open('POST', '<?php echo Uri::root(); ?>index.php?option=com_jdonation&task=share_campaign&format=raw&id=<?php echo $this->campaignId; ?>&no_html=1&tmpl=component', true);

		// Set up the request event listener
		xhr.onreadystatechange = function () {
			if (xhr.readyState === 4) {  // When the request is complete
				if (xhr.status === 200) {  // If the response is successful
					// Show success message
					
					shareform.style.display = "none";
					document.getElementById('share_alert_bar').style.display = 'none';
					responseMessage.innerHTML = xhr.responseText;
					
				} else {  // If there is an error with the request
					// Show failure message
					responseMessage.innerHTML = '<div class="alert alert-danger">Something went wrong. Please try again later.</div>';
				}
			}
		};

		// Send the form data
		xhr.send(formData);
	});
}

function toggleDesc() {
    var about = document.getElementById('aboutCampaign');
    var btn = document.getElementById('showMoreBtn');
    if (about.classList.contains('collapsed')) {
    about.classList.remove('collapsed');
    btn.textContent = '<?php echo Text::_('JD_SHOW_LESS');?>';
    } else {
    about.classList.add('collapsed');
    btn.textContent = '<?php echo Text::_('JD_SHOW_MORE');?>';
    }
}
</script>
