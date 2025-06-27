<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Layout variables
 * -----------------
 * @var   array     $rows
 * @var   MPFConfig $config
 */

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
$centerClass     = $bootstrapHelper->getClassMapping('center');

$discountTypes = [
    0 => '%',
    1 => $config->get('currency_symbol', '$')
];
?>
<table class="adminlist <?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?>" id="adminForm">
    <thead>
    <tr>
        <th class="title" style="text-align: left;">
            <?php echo Text::_('OSM_CODE'); ?>
        </th>
        <th class="center title">
            <?php echo Text::_('OSM_DISCOUNT'); ?>
        </th>
        <?php
        if ($this->params->get('show_times', 1))
        {
        ?>
            <th class="center title">
		        <?php echo Text::_('OSM_TIMES'); ?>
            </th>
        <?php
        }

        if ($this->params->get('show_used', 1))
        {
        ?>
            <th class="center title">
		        <?php echo Text::_('OSM_USED'); ?>
            </th>
        <?php
        }

        if ($this->params->get('show_valid_from', 1))
        {
        ?>
            <th class="center title">
		        <?php echo Text::_('OSM_VALID_FROM'); ?>
            </th>
        <?php
        }

        if ($this->params->get('show_valid_to', 1))
        {
        ?>
            <th class="center title">
		        <?php echo Text::_('OSM_VALID_TO'); ?>
            </th>
        <?php
        }
        ?>
    </tr>
    </thead>
    <tbody>
    <?php
    $k = 0;
    foreach ($rows as $row)
    {
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td>
                <?php echo $row->code; ?>
            </td>
            <td class="center">
                <?php echo OSMembershipHelper::formatAmount($row->discount, $config) . ($discountTypes[$row->coupon_type] ?? ''); ?>
            </td>
            <?php
            if ($this->params->get('show_times', 1))
            {
            ?>
                <td class="center">
		            <?php echo $row->times; ?>
                </td>
            <?php
            }

            if ($this->params->get('show_used', 1))
            {
            ?>
                <td class="center">
		            <?php echo $row->used; ?>
                </td>
            <?php
            }

            if ($this->params->get('show_valid_from', 1))
            {
            ?>
                <td class="center">
		            <?php
		            if ((int) $row->valid_from)
		            {
			            echo HTMLHelper::_('date', $row->valid_from, $config->date_format, null);
		            }
		            ?>
                </td>
            <?php
            }

            if ($this->params->get('show_valid_to', 1))
            {
            ?>
                <td class="center">
		            <?php
		            if ((int) $row->valid_to)
		            {
			            echo HTMLHelper::_('date', $row->valid_to, $config->date_format, null);
		            }
		            ?>
                </td>
            <?php
            }
            ?>
        </tr>
        <?php
        $k = 1 - $k;
    }
    ?>
    </tbody>
</table>