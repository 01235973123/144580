<?php

/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
HTMLHelper::_('jquery.framework');
?>
<table>
	<tr>
        <td width="20%" valign="top">
            <img src="<?php echo Uri::base();?>components/com_jdonation/assets/icons/donation_jdonation_small.png" style="width:100%;"/>
            <BR />
			
            <table width="100%">
                <tr>
                    <td width="100%" style="padding:10px;color:#474445;background-color:#F46F20;color:white;">
                        Installed version: <?php echo DonationHelper::getInstalledVersion();?>
                        <BR />
                        Author: <a href="http://www.joomdonation.com" target="_blank" style="color:white;"><strong>Ossolution team</strong></a>
                        <BR /><BR />
                        <strong><?php echo Text::_('JD_USEFUL_LINKS'); ?></strong>
                        <BR />
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-globe" viewBox="0 0 16 16">
						  <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm7.5-6.923c-.67.204-1.335.82-1.887 1.855A7.97 7.97 0 0 0 5.145 4H7.5V1.077zM4.09 4a9.267 9.267 0 0 1 .64-1.539 6.7 6.7 0 0 1 .597-.933A7.025 7.025 0 0 0 2.255 4H4.09zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a6.958 6.958 0 0 0-.656 2.5h2.49zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5H4.847zM8.5 5v2.5h2.99a12.495 12.495 0 0 0-.337-2.5H8.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5H4.51zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5H8.5zM5.145 12c.138.386.295.744.468 1.068.552 1.035 1.218 1.65 1.887 1.855V12H5.145zm.182 2.472a6.696 6.696 0 0 1-.597-.933A9.268 9.268 0 0 1 4.09 12H2.255a7.024 7.024 0 0 0 3.072 2.472zM3.82 11a13.652 13.652 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5H3.82zm6.853 3.472A7.024 7.024 0 0 0 13.745 12H11.91a9.27 9.27 0 0 1-.64 1.539 6.688 6.688 0 0 1-.597.933zM8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855.173-.324.33-.682.468-1.068H8.5zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.65 13.65 0 0 1-.312 2.5zm2.802-3.5a6.959 6.959 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5h2.49zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7.024 7.024 0 0 0-3.072-2.472c.218.284.418.598.597.933zM10.855 4a7.966 7.966 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4h2.355z"/></svg>&nbsp;<a href="https://www.joomdonation.com/forum/joom-donation.html" target="_blank" style="color:white;">Forum</a>
                        <BR />
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-question-circle" viewBox="0 0 16 16">
						  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
						  <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"/>
						</svg>&nbsp;<a href="https://www.joomdonation.com/support-tickets.html" target="_blank" style="color:white;">Support ticket</a>
                        <BR />
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-download" viewBox="0 0 16 16">
						  <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
						  <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
						</svg>&nbsp;<a href="https://www.joomdonation.com/my-downloads.html" target="_blank" style="color:white;">Download latest</a>
                        <BR />
                    </td>
                </tr>
            </table>
        </td>
		
		<td valign="top" width="44%" style="padding-left:15px;">
			<div id="cpanel">
				<?php
				$this->quickiconButton('index.php?option=com_jdonation&view=campaigns', 'icon-48-jdonation-campaigns.png', Text::_('JD_CAMPAIGNS'));
                $this->quickiconButton('index.php?option=com_jdonation&task=campaigns.add', 'icon-48-jdonation-campaigns-add.png', Text::_('JD_ADD_CAMPAIGN'));
				$this->quickiconButton('index.php?option=com_jdonation&view=fields', 'icon-48-jdonation-fields.png', Text::_('JD_CUSTOM_FIELDS'));
                $this->quickiconButton('index.php?option=com_jdonation&view=field', 'icon-48-jdonation-fields-add.png', Text::_('JD_ADD_CUSTOM_FIELD'));
				$this->quickiconButton('index.php?option=com_jdonation&view=donors', 'icon-48-jdonation-donors.png', Text::_('JD_DONORS'));
                $this->quickiconButton('index.php?option=com_jdonation&task=donors.add', 'icon-48-jdonation-donors-add.png', Text::_('JD_ADD_DONOR'));
				$this->quickiconButton('index.php?option=com_jdonation&view=plugins', 'icon-48-jdonation-payments.png', Text::_('JD_PAYMENT_PLUGINS'));
				$this->quickiconButton('index.php?option=com_jdonation&view=language', 'icon-48-jdonation-language.png', Text::_('JD_TRANSLATION'));
				$this->quickiconButton('index.php?option=com_jdonation&task=donor.export', 'icon-48-jdonation-export.png', Text::_('JD_EXPORT_DONORS'));
                $this->quickiconButton('index.php?option=com_jdonation&view=import', 'icon-48-jdonation-import.png', Text::_('JD_IMPORT_DONORS'));
                $this->quickiconButton('index.php?option=com_jdonation&view=configuration', 'icon-48-jdonation-config.png', Text::_('JD_CONFIGURATION'));
				//$this->quickiconButton('index.php?option=com_jdonation', 'icon-48-download.png', Text::_('JD_UPDATE_CHECKING'), 'update-check');
				$link = 'index.php?option=com_jdonation';
				switch ($this->updateResult['status'])
				{
					case 0:
						$icon = 'icon-48-deny.png';
						$text = Text::_('JD_UPDATE_CHECKING_ERROR');
						break;
					case 1:
						$icon = 'icon-48-jupdate-uptodate.png';
						$text = $this->updateResult['message'];
						break;
					case 2:
						$icon = 'icon-48-jupdate-updatefound.png';
						$text = $this->updateResult['message'];
						$link = 'index.php?option=com_installer&view=update';
						break;
					default:
						$icon = 'icon-48-download.png';
						$text = Text::_('JD_UPDATE_CHECKING');
						break;
				}

				$this->quickiconButton($link, $icon, $text, 'update-check');
				?>
			</div>			
		</td>
		
		<?php
		if (!DonationHelper::isJoomla4())
		{
		?>
			<td valign="top" width="35%" style="padding: 0 0 0 5px">
				<?php
				if (DonationHelper::isJoomla4())
				{
					echo HTMLHelper::_('bootstrap.startAccordion', 'statistics_pane', array('active' => 'statistic'));
					echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('JD_RECENT_RECEIVED'), 'statistic');
					echo $this->loadTemplate('statistics');
					echo HTMLHelper::_('bootstrap.endSlide');
					echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('JD_LATEST_DONORS'), 'donors');
					echo $this->loadTemplate('donors');
					echo HTMLHelper::_('bootstrap.endSlide');
					echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('JD_USEFUL_LINKS'), 'links_panel');
					echo $this->loadTemplate('useful_links');
					echo HTMLHelper::_('bootstrap.endSlide');
					echo HTMLHelper::_('bootstrap.endAccordion');
				}else{
					echo HTMLHelper::_('sliders.start', 'statistics_pane');
					echo HTMLHelper::_('sliders.panel', Text::_('JD_RECENT_RECEIVED'), 'statistic');
					echo $this->loadTemplate('shortstatistics');
					echo HTMLHelper::_('sliders.panel', Text::_('JD_STATISTICS'), 'statistic');
					echo $this->loadTemplate('statistics');
					echo HTMLHelper::_('sliders.panel', Text::_('JD_LATEST_DONORS'), 'donors');
					echo $this->loadTemplate('donors');
					echo HTMLHelper::_('sliders.panel', Text::_('JD_USEFUL_LINKS'), 'links_panel');
					echo $this->loadTemplate('useful_links');
					echo HTMLHelper::_('sliders.end');
				}
				?>
			</td>
		<?php
		}			
		?>
	</tr>
	<tr>
		<td colspan="2">
			<?php
			if (DonationHelper::isJoomla4())
			{
					
				echo HTMLHelper::_('bootstrap.startAccordion', 'statistics_pane', array('active' => 'statistic'));
				echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('JD_RECENT_RECEIVED'), 'statistic');
				echo $this->loadTemplate('statistics');
				echo HTMLHelper::_('bootstrap.endSlide');
				echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('JD_LATEST_DONORS'), 'donors');
				echo $this->loadTemplate('donors');
				echo HTMLHelper::_('bootstrap.endSlide');
				echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('JD_USEFUL_LINKS'), 'links_panel');
				echo $this->loadTemplate('useful_links');
				echo HTMLHelper::_('bootstrap.endSlide');
				echo HTMLHelper::_('bootstrap.endAccordion');
			}
			?>
		</td>
	</tr>
</table>
<style>
	#statistics_pane{
		margin:0px !important
	}
</style>
<!--
<script type="text/javascript">
	var upToDateImg = '<?php echo Uri::base(true).'/components/com_jdonation/assets/icons/icon-48-jupdate-uptodate.png' ?>';
	var updateFoundImg = '<?php echo Uri::base(true).'/components/com_jdonation/assets/icons/icon-48-jupdate-updatefound.png';?>';
	var errorFoundImg = '<?php echo Uri::base(true).'/components/com_jdonation/assets/icons/icon-48-deny.png';?>';
	jQuery(document).ready(function() {
		jQuery.ajax({
			type: 'POST',
			url: 'index.php?option=com_jdonation&task=check_update',
			dataType: 'json',
			success: function(msg, textStatus, xhr)
			{
				if (msg.status == 1)
				{
					jQuery('#update-check').find('img').attr('src', upToDateImg).attr('title', msg.message);
					jQuery('#update-check').find('span').text(msg.message);
                    jQuery('#update-check').find('span').css('color','green');
				}
				else if (msg.status == 2)
				{
					jQuery('#update-check').find('img').attr('src', updateFoundImg).attr('title', msg.message);
					jQuery('#update-check').find('a').attr('href', 'http://joomdonation.com/my-downloads.html');
					jQuery('#update-check').find('span').text(msg.message);
                    jQuery('#update-check').find('span').css('color','red');
				}
				else
				{
					jQuery('#update-check').find('img').attr('src', errorFoundImg);
					jQuery('#update-check').find('span').text('<?php echo Text::_('JD_UPDATE_CHECKING_ERROR'); ?>');
				}
			},
			error: function(jqXHR, textStatus, errorThrown)
			{
				jQuery('#update-check').find('img').attr('src', errorFoundImg);
				jQuery('#update-check').find('span').text('<?php echo Text::_('JD_UPDATE_CHECKING_ERROR'); ?>');
			}
		});
	});
</script>
-->
