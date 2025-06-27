<?php
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
/**
 * @version        4.2
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2016 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
?>
<form name="thermometer_form" id="thermometer_form" method="post" action="<?php echo Route::_('index.php?option=com_jdonation&task=donation_form&Itemid='.$itemId, false, (int) $config->use_https); ?>">
<?php
	$k = 0 ;
	for ($i = 0 , $n = count($rows) ; $i < $n ; $i++) {
		$row = $rows[$i] ;
		$goal = $row->goal ;
		$donatedAmount = $row->total_donated;
		if (!$donatedAmount) $donatedAmount = 0;
	?>	
		<div id="centered" class="wrapper-thermometer">
			<?php if ($params->get('show_title',0)): ?>
			<h3><?php echo $row->title; ?></h3>
			<?php endif;?>
   			<div id="goal-thermometer-<?php echo $row->id?>" ></div>
			<style>
				#goal-thermometer-<?php echo $row->id; ?>{
				    position:relative;
				    padding:0;
				    font-family:Arial, Helvetica, sans-serif;
				    color:#fff;
				    font-weight: bold;
				    opacity:0;
				}
				#therm-number-<?php echo $row->id; ?>{
				    position:absolute;
				    text-align:right;
				    font-size:13px;
				}
				#therm-graphics-<?php echo $row->id; ?>{
				    float:left;
				    position:relative;
				    width:46px;
				}
				#therm-bottom-<?php echo $row->id; ?>{
				    position:absolute;
				    left:0;
				    width:46px;
				    height:51px;
				} 
				#therm-body-fore-<?php echo $row->id; ?>{
				     position:absolute;
				     width:24px;
				     top:13px;
				     left:11px;
				     background-repeat:repeat-y;
				}
				#therm-body-mercury-<?php echo $row->id;?>{
				     position:absolute;
				     bottom:51px;
				     left:14px;
				     width: 18px;
				     height:2px;
				}
				#therm-body-bg-<?php echo $row->id; ?>{
				     position:absolute;
				     top:13px;
				     left:7px;
				     width:32px;
				}
				#therm-top-<?php echo $row->id; ?>{
				    position:absolute;
				    top:0;
				    left:7px;
				    width:32px;
				    height:13px;
				}
				#therm-tooltip-<?php echo $row->id; ?>{
				    position:absolute;
				    left:38px;
				    width:200px;
				}
				#therm-tooltip-<?php echo $row->id; ?> .tip-left{
				    float:left;
				    width:19px;
				    height:32px;
				}
				#therm-tooltip-<?php echo $row->id; ?> .tip-middle-<?php echo $row->id; ?>{
				    float:left;
				    height:32px;
				    font-size:15px;
				}
				#therm-tooltip-<?php echo $row->id; ?> .tip-middle-<?php echo $row->id; ?> p{
				    position:relative;
				    margin:0;
				    padding-right:4px;
				    padding-left:3px;
				    top:6px;
				    height:32px;
				    opacity:.7;
				    background-size:64px 64px;
				    -moz-background-size: 100%;
				}
				#therm-tooltip-<?php echo $row->id; ?> .tip-right{
				    float:left;
				    width:9px;
				    height:32px;
				}
			</style>
			<?php
				if ($showButton)
				{
				?>
					<input type="button" class="btn btn-primary" onclick="donationForm(<?php echo $row->id ; ?>);" value="<?php echo Text::_('JD_DONATE'); ?>" />
				<?php
				}
			?>
		</div>
	<?php	
	}
?>	
	<input type="hidden" name="option" value="com_jdonation" />
	<input type="hidden" name="task" value="" />	
	<input type="hidden" name="campaign_id" value="0" />
	<input type="hidden" name="Itemid" value="<?php echo $itemId ; ?>" />
	<script type="text/javascript">
		jQuery(function($){
			donationForm = (function(campaignId){
				$('[name^=campaign_id]').val(campaignId);
				$('[name^=task]').val('donation_form');
				$('#thermometer_form').submit();
			})
			var animationTime = 3000;//in milliseconds
			var numberPrefix = "<?php echo $config->currency_symbol;?>";//what comes before the number (set to "" if no prefix)
			var numberSuffix = "";//what goes after the number
			var tickMarkSegementCount = "<?php echo @$config->number_segments ? (int)$config->number_segments : 5; ?>";//each segement adds 40px to the height
			var widthOfNumbers = 70;//the width in px of the numbers on the left
			//standard resolution images
			var glassTopImg = "<?php echo PATH_IMAGES ;?>/glassTop.png";
			var glassBodyImg = "<?php echo PATH_IMAGES ;?>/glassBody.png";
			var redVerticalImg = "<?php echo PATH_IMAGES ;?>/redVertical.png";
			var tooltipFGImg = "<?php echo PATH_IMAGES ;?>/tickShine.png";
			var glassBottomImg = "<?php echo PATH_IMAGES ;?>/glassBottom.png";
			var tootipPointImg = "<?php echo PATH_IMAGES ;?>/tooltipPoint.png";
			var tooltipMiddleImg = "<?php echo PATH_IMAGES ;?>/tooltipMiddle.png";
			var tooltipButtImg = "<?php echo PATH_IMAGES ;?>/tooltipButt.png";
			//high res images
			var glassTopImg2x = "<?php echo PATH_IMAGES ;?>/glassTop2x.png";
			var glassBodyImg2x = "<?php echo PATH_IMAGES ;?>/glassBody2x.png";
			var redVerticalImg2x = "<?php echo PATH_IMAGES ;?>/redVertical2x.png";
			var tooltipFGImg2x = "<?php echo PATH_IMAGES ;?>/tickShine2x.png";
			var glassBottomImg2x = "<?php echo PATH_IMAGES ;?>/glassBottom2x.png";
			var tootipPointImg2x = "<?php echo PATH_IMAGES ;?>/tooltipPoint2x.png";
			var tooltipMiddleImg2x = "<?php echo PATH_IMAGES ;?>/tooltipMiddle2x.png";
			var tooltipButtImg2x = "<?php echo PATH_IMAGES ;?>/tooltipButt2x.png";
			/////////////////////////////////////////
			// ------ don't edit below here ------ //
			/////////////////////////////////////////
			var arrayOfImages;
			var imgsLoaded = 0;
			var tickHeight = 40;
			var mercuryHeightEmpty = 0;
			var numberStartY = 6;
			var thermTopHeight = 13;
			var thermBottomHeight = 51;
			var tooltipOffset = 15; 
			var heightOfBody;
			var mercuryId;
			var tooltipId;
			var resolution2x = false;
			//start once the page is loaded
			jQuery( document ).ready(function() {
				resolution2x = window.devicePixelRatio == 2;//check if resolution2x
				if(resolution2x){	
					//switch the regular for 2x res graphics
					glassTopImg = glassTopImg2x;
					glassBodyImg = glassBodyImg2x;
					redVerticalImg = redVerticalImg2x;
					glassBottomImg = glassBottomImg2x;
					tootipPointImg = tootipPointImg2x;
					tooltipButtImg = tooltipButtImg2x;	
				}
				<?php
				for ($i = 0 , $n = count($rows) ; $i < $n ; $i++) 
				{ 
					$row = $rows[$i];
					$goal = $row->goal ;
					$donatedAmount = $row->total_donated;
					if (!$donatedAmount) $donatedAmount = 0;
				?>
					createGraphics(<?php echo $row->id;?>, <?php echo $donatedAmount; ?>, <?php echo $goal; ?>);
				<?php
				 } 
				?>
			});
			//visually create the thermometer
			createGraphics = (function(campaignId, currentAmount, goalAmount){
				//add the html
				jQuery("#goal-thermometer-"+ campaignId).html(
					"<div class='therm-numbers' id='therm-numbers-"+ campaignId +"'>" + 
					"</div>" + 
					"<div id='therm-graphics-"+campaignId+"' class='therm-graphics'>" + 
						"<img id='therm-top-"+campaignId+"' src='"+glassTopImg+"'></img>" + 
						"<img id='therm-body-bg-"+campaignId+"' src='"+glassBodyImg+"' ></img>" + 
						"<img id='therm-body-mercury-"+campaignId+"' src='"+redVerticalImg+"'></img>" + 
						"<div id='therm-body-fore-"+campaignId+"'></div>" + 
						"<img id='therm-bottom-"+campaignId+"' src='"+glassBottomImg+"'></img>" + 
						"<div id='therm-tooltip-"+campaignId+"'>" + 
							"<img class='tip-left' src='"+tootipPointImg+"'></img>" + 
							"<div class='tip-middle-"+campaignId+"'><p><?php echo $params->get('currency','$');?>0</p></div>" + 
							"<img class='tip-right' src='"+tooltipButtImg+"'></img>" + 
						"</div>" + 
					"</div>"
				);
				//preload and add the background images
				jQuery('<img/>').attr('src', tooltipFGImg).on('load',function(){
					jQuery(this).remove();
					jQuery("#therm-body-fore-"+campaignId).css("background-image", "url('"+tooltipFGImg+"')");
					checkIfAllImagesLoaded(campaignId, currentAmount, goalAmount);
				});
				$('<img/>').attr('src', tooltipMiddleImg).on('load',function(){
					$(this).remove();
					$("#therm-tooltip-"+campaignId+" .tip-middle-"+campaignId+"").css("background-image", "url('" + tooltipMiddleImg + "')");
					checkIfAllImagesLoaded(campaignId, currentAmount, goalAmount);
				});
				//adjust the css
				heightOfBody = tickMarkSegementCount * tickHeight;
				$("#therm-graphics-"+ campaignId).css("left", widthOfNumbers)
				$("#therm-body-bg-"+campaignId).css("height", heightOfBody);
				$("#goal-thermometer-" + campaignId).css("height",  heightOfBody + thermTopHeight + thermBottomHeight);
				$("#therm-body-fore-"+campaignId).css("height", heightOfBody);
				$("#therm-bottom-" + campaignId).css("top", heightOfBody + thermTopHeight);
				mercuryId = $("#therm-body-mercury-"+campaignId);
				mercuryId.css("top", heightOfBody + thermTopHeight);
				tooltipId = $("#therm-tooltip-"+campaignId);
				tooltipId.css("top", heightOfBody + thermTopHeight - tooltipOffset);

				//add the numbers to the left
				var numbersDiv = $("#therm-numbers-" + campaignId);
				var countPerTick = goalAmount/tickMarkSegementCount;
				var commaSepCountPerTick = commaSeparateNumber(countPerTick);
				
				//add the number
				for ( var i = 0; i < tickMarkSegementCount; i++ ) {
					
					var yPos = tickHeight * i + numberStartY;
					var style = $("<style>.pos" + i + " { top: " + yPos + "px; width:"+widthOfNumbers+"px }</style>");
					$("html > head").append(style);
					var dollarText = commaSeparateNumber(goalAmount - countPerTick * i);
					$( numbersDiv ).append( "<div class='therm-number pos" + i + "'>" +dollarText+ "</div>" );
					
				}
				
				//check that the images are loaded before anything
				arrayOfImages = new Array( "#therm-top-"+campaignId, "#therm-body-bg-"+campaignId, "#therm-body-mercury-"+campaignId, "#therm-bottom-" + campaignId, ".tip-left", ".tip-right");
				for(i=0;i<arrayOfImages.length;i++){
					$(arrayOfImages[i]).on('load',function() { 
						checkIfAllImagesLoaded(campaignId, currentAmount, goalAmount);  
					});
				}
				
			});

			//check that all the images are preloaded
			checkIfAllImagesLoaded = (function(campaignId, currentAmount, goalAmount){
				imgsLoaded++;
				if(imgsLoaded == arrayOfImages.length+2){
					<?php
						for ($i = 0 , $n = count($rows) ; $i < $n ; $i++) 
						{ 
							$row = $rows[$i];
							$goal = $row->goal ;
							$donatedAmount = $row->total_donated;
							if (!$donatedAmount) $donatedAmount = 0;
					?>
					$("#goal-thermometer-<?php echo $row->id; ?>").fadeTo(1000, 1, function(){
						<?php
							if ($donatedAmount > $goal)
							{
						?>
							var percentageComplete = "1";
						<?php
						 	} 
						 	else 
						 	{
						?>
							var percentageComplete = "<?php echo ($donatedAmount/$goal); ?>";
						<?php
						 	} 
						?>
						var mercuryHeight = Math.round(heightOfBody * percentageComplete); 
						var newMercuryTop = heightOfBody + thermTopHeight - mercuryHeight;
						
						$("#therm-body-mercury-<?php echo $row->id; ?>").animate({height:mercuryHeight +1, top:newMercuryTop }, animationTime);
						$("#therm-tooltip-<?php echo $row->id;?>").animate({top:newMercuryTop - tooltipOffset}, {duration:animationTime});
						
						var tooltipTxt = $("#therm-tooltip-<?php echo $row->id; ?> .tip-middle-<?php echo $row->id; ?> p");
						//change the tooltip number as it moves
						$({tipAmount: 0}).animate({tipAmount: <?php echo $donatedAmount; ?>}, {
							duration:animationTime,
							step:function(){
								tooltipTxt.html(commaSeparateNumber(this.tipAmount));
							}
						});
					});
					<?php
					 } 
					?>
				}
			});
			//format the numbers with $ and commas
			commaSeparateNumber = (function(val){
				val = Math.round(val);
			    while (/(\d+)(\d{3})/.test(val.toString())){
			      val = val.toString().replace(/(\d+)(\d{3})/, '$1'+'<?php echo $thousands_sep;?>'+'$2');
			    }
			    return numberPrefix + val + numberSuffix;
			});
		})
	</script>
</form>
