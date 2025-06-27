<?php
/**
 * @version        4.3
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
$jinput					= Factory::getApplication()->input;
$config					= Factory::getApplication()->getConfig();
$bootstrapHelper		= $this->bootstrapHelper;
$rowFluidClass			= $bootstrapHelper->getClassMapping('row-fluid');
$span12Class			= $bootstrapHelper->getClassMapping('span12');
$span7Class				= $bootstrapHelper->getClassMapping('span7');
$span5Class				= $bootstrapHelper->getClassMapping('span5');
?>
<div class="<?php echo $rowFluidClass; ?>" id="donationReport" style="margin-top:15px;">
    <div class="<?php echo $span12Class; ?>">
        <div class="card mb-4">
            <div class="card-header">
                <strong><?php echo Text::_('JD_EXPORT_PDF_OF_DONATIONS_AND_REVENUE'); ?></strong>
            </div>
            <div class="card-body d-flex flex-row align-items-center justify-content-between">
                <div>
                    <span class="text-muted">
                        <?php echo Text::_('JD_EXPORT_PDF_OF_DONATIONS_AND_REVENUE_EXPLAIN'); ?>
                    </span>
                </div>
                <div>
                    <a href="index.php?option=com_jdonation&task=donor.exportpdfrevenue"
                       class="btn btn-primary"
                       title="<?php echo Text::_('JD_EXPORT_PDF');?>"><?php echo Text::_('JD_EXPORT_PDF');?>
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <strong><?php echo Text::_('JD_EXPORT_REVENUE_AND_DONATION_STAS'); ?></strong>
            </div>
            <div class="card-body">
                <form name="csvExport" id="csvExport" action="index.php?option=com_jdonation&view=report" method="post" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <?php echo $this->lists['year1']; ?>
                    </div>
                    <div class="col-auto">
                        <?php echo $this->lists['month1']; ?>
                    </div>
                    <div class="col-auto">
                        <span>-</span>
                    </div>
                    <div class="col-auto">
                        <?php echo $this->lists['year2']; ?>
                    </div>
                    <div class="col-auto">
                        <?php echo $this->lists['month2']; ?>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary" title="<?php echo Text::_('JD_GENERATE_CSV');?>">
                            <?php echo Text::_('JD_GENERATE_CSV');?>
                        </button>
                    </div>
                    <input type="hidden" name="task" value="donor.exportrevenue" />
                    <?php echo HTMLHelper::_('form.token'); ?>
                </form>
                <div class="mt-2 text-muted small">
                    <?php echo Text::_('JD_EXPORT_REVENUE_AND_DONATION_STAS_EXPLAIN'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function submitForm(form_id)
{
	document.getElementById(form_id).submit();
}
</script>