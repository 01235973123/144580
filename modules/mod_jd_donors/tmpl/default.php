<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
/**
 * @version        5.4.10
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2019 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
$rowFluidClass   = $bootstrapHelper->getClassMapping('row-fluid');
$span2Class      = $bootstrapHelper->getClassMapping('span2');
$span6Class      = $bootstrapHelper->getClassMapping('span6');
$span10Class     = $bootstrapHelper->getClassMapping('span10');
$span12Class     = $bootstrapHelper->getClassMapping('span12');
?>
<div class="<?php echo $rowFluidClass?> mod-jd-donors">
    <div class="<?php echo $span12Class?>">
        <?php
            if (count($rows))
            {
            ?>
            <?php
                $k = 0;
                for ($i = 0 , $n = count($rows); $i < $n; $i++)
                {
                    $row = $rows[$i];
                    if ($row->hide_me == 1)
                    {
                        $row->first_name = Text::_('JD_ANONYMOUS');
                        $row->last_name = '' ;
                    }
                    ?>
                    <div class="<?php echo $rowFluidClass?> donor-info">
                        <div class="<?php echo $span12Class?>">
                            <strong>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
								  <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
								</svg>
                                <?php
                                $name = $row->first_name.' '.$row->last_name ;
                                if ($row->user_id > 0)
                                {
                                    if ($displayUsername && $row->hide_me == 0)
                                    {
                                        $name = $row->username ;
                                    }
                                    if ($integration == 1)
                                    {
                                        $link = "index.php?option=com_comprofiler&task=userProfile&user=$row->user_id&Itemid=$itemId";
                                        ?>
                                        <a href="<?php echo $link; ?>"><?php echo $name; ?></a>
                                        <?php
                                    }
                                    elseif ($integration == 2)
                                    {
                                        $link = "index.php?option=com_community&view=profile&userid=$row->user_id&Itemid=$itemId";
                                        ?>
                                        <a href="<?php echo $link; ?>"><?php echo $name; ?></a>
                                        <?php
                                    }
                                    else
                                    {
                                        echo $name ;
                                    }
                                }
                                else
                                {
                                    echo $name ;
                                }
                                ?>
                            </strong>
							<?php
							$addressArr = array();
							if($show_donor_address == 1 && $row->address != "")
							{
								$addressArr[] = $row->address;
							}
							if($show_donor_city == 1 && $row->city != "")
							{
								$addressArr[] = $row->city;
							}
							if($show_donor_state == 1 && $row->state != "")
							{
								$addressArr[] = $row->state;
							}
							if($show_donor_country == 1 && $row->country != "")
							{
								$addressArr[] = $row->country;
							}
							if(count($addressArr) > 0)
							{
								?>
								<span class="donorAddress">
									<?php
									echo Text::_('JD_FROM');
									echo " ";
									echo implode(", ", $addressArr);
									?>
								</span>
								<?php
							}
							?>
							<br />
                            <small>
							<?php
							if($row->show_dedicate == 1 && $show_honoreename == 1 && $config->activate_tributes)
							{
								echo DonationHelper::getDedicateType($row->dedicate_type). " ".$row->dedicate_name;
								echo "<BR />";
							}
							?>
                            </small>
							<?php
							if($show_campaign == 1  || $show_donation_amount == 1) 
							{
							?>
                            <div class="<?php echo $rowFluidClass?>">
                                <div class="<?php echo $span12Class?>">
                                    <div class="campaign-info">
										<?php
										if($show_campaign == 1)
										{
											$link = DonationHelperRoute::getDonationFormRoute($row->campaign_id,  Factory::getApplication()->input->getInt('Itemid'));
										?>
                                        <div class="campaign-title">
                                            <a href="<?php echo Route::_($link);?>">
                                                <?php
                                                echo $row->title;
                                                ?>
                                            </a>
                                        </div>
										<?php
										}			
										if($show_donation_amount == 1)
										{
										?>
                                        <div class="donor-amount">
                                            <span class="amount"><?php echo DonationHelperHtml::formatAmount($config, $row->amount,null,$display_currency);?></span>
                                            <span class="date"><?php echo DonationHelperHtml::ago(Factory::getDate($row->created_date)->toUnix()); ?></small></span>
                                        </div>
										<?php
										}			
										?>
                                    </div>
                                </div>
                            </div>
							<?php
							}				
							?>
							<?php
							if($show_comment == 1 && $row->comment != "") 
							{
							?>
                            <div class="<?php echo $rowFluidClass?>">
								<div class="<?php echo $span12Class?> donor-comment-container">
									<div class="donor-comment-box">
										<div class="donor-comment-content">
											<?php echo $row->comment; ?>
										</div>
										<?php if (!empty($row->donor_name)): ?>
											<div class="donor-name">- <?php echo $row->donor_name; ?></div>
										<?php endif; ?>
									</div>
								</div>
							</div>


							<?php
							}				
							?>
                        </div>
                    </div>
                    <?php
                    $k =  1- $k;
                }
            }
            else
            {
            ?>
                <div class="empty"><?php echo Text::_('JD_EMPTY_DONORS'); ?></div>
            <?php
            }
        ?>
    </div>
</div>
