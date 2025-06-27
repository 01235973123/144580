<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

OSMembershipHelperJquery::validateForm();

Factory::getApplication()
	->getDocument()
	->addScriptOptions('paymentMethod', $this->subscription->payment_method)
	->getWebAssetManager()
	->useScript('core')
	->registerAndUseScript('com_osmembership.site-card-default', 'media/com_osmembership/js/site-card-default.min.js');

/* @var OSMembershipHelperBootstrap $bootstrapHelper */
$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
?>
<div id="osm-update-credit-card" class="osm-container">
    <h1 class="osm-page-title"><?php echo Text::_('OSM_UPDATE_CARD'); ?></h1>
    <form method="post" name="os_form" id="os_form" action="<?php echo Route::_('index.php?option=com_osmembership&task=profile.update_card&Itemid=' . $this->Itemid, false, $this->config->use_https ? 1 : 0); ?>" autocomplete="off" class="<?php echo $bootstrapHelper->getClassMapping('form form-horizontal'); ?>">
        <?php
            if (str_contains($this->subscription->payment_method, 'os_stripe'))
            {
               echo $this->loadTemplate('card_stripe');
            }
            else
            {
                echo $this->loadTemplate('card_classic');
            }
        ?>
        <div class="form-actions">
            <input type="submit" class="<?php echo $this->bootstrapHelper->getClassMapping('btn btn-primary'); ?>" name="btnSubmit" id="btn-submit" value="<?php echo  Text::_('OSM_UPDATE') ;?>" />
        </div>
        <input type="hidden" name="subscription_id" value="<?php echo $this->subscription->subscription_id; ?>" />
        <input type="hidden" name="payment_method" value="<?php echo $this->method->getName(); ?>" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>