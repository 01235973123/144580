<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
?>
<table class="table table-striped jdonation_dasboard">
	<thead>
		<tr>
			<th class="title" width="18%"><?php echo Text::_('JD_FIRST_NAME')?></th>
			<th class="title" width="18%"><?php echo Text::_('JD_LAST_NAME')?></th>
			<th class="title center" width="25%"><?php echo Text::_('JD_DONATION_DATE')?></th>
            <th class="title center" width="15%"><?php echo Text::_('JD_TYPE')?></th>
			<th class="title" width="20%"><?php echo Text::_('JD_AMOUNT')?></th>
		</tr>
		<?php
			foreach ($this->latestDonors as $row)
			{ 
				$link = Route::_('index.php?option=com_jdonation&task=donor.edit&id='.(int)$row->id);
		?>
		<tr>
			<td><a href="<?php echo $link; ?>"><?php echo $row->first_name; ?></a></td>
			<td><?php echo $row->last_name; ?></td>			
			<td class="center" style="font-size:11px;"><?php echo  HTMLHelper::_('date', $row->created_date, $this->config->date_format.' H:i:s'); ?></td>
            <td class="center">
                <?php
                if ($row->donation_type == 'R')
                {
                    echo Text::_('JD_RECURRING') ;
                }
                else
                {
                    echo Text::_('JD_ONETIME') ;
                }
                ?>
            </td>
			<td style="text-align:right;">
				<?php
				$decimals      = isset($this->config->decimals) ? $this->config->decimals : 2;
				$dec_point     = isset($this->config->dec_point) ? $this->config->dec_point : '.';
				$thousands_sep = isset($this->config->thousands_sep) ? $this->config->thousands_sep : ',';
				echo number_format($row->amount, $decimals, $dec_point, $thousands_sep);
				echo "&nbsp;";
				echo "<strong style='font-size:10px;'>";
				if($row->currency_code != ""){
					echo $row->currency_code;
				}else{
					echo $this->config->currency;
				}
				echo "</strong>";
				?>
			</td>
		</tr>
		<?php
			} 
		?>
	</thead>
</table>
