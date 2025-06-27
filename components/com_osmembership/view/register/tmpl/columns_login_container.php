<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$actionUrl = Route::_('index.php?option=com_users&task=user.login');
$returnUrl = Uri::getInstance()->toString();
?>
<form method="post" action="<?php echo $actionUrl ; ?>" name="osm_login_form" id="osm_login_form" autocomplete="off">
    <input type="hidden" name="username" id="username" value="" />
    <input type="hidden" id="password" name="password" value="" />
    <input type="hidden" name="remember" value="1" />
    <input type="hidden" name="login_from_mp_subscription_form" value="1" />
    <input type="hidden" name="return" value="<?php echo base64_encode($returnUrl) ; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
