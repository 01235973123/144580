<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\Filesystem\File;

$customCss = '';

$theme    = EShopHelper::getConfigValue('theme');

if (is_file(JPATH_ROOT . '/components/com_eshop/themes/' . $theme . '/css/custom.css'))
{
	$customCss = file_get_contents(JPATH_ROOT . '/components/com_eshop/themes/' . $theme . '/css/custom.css');
}
elseif (is_file(JPATH_ROOT . '/components/com_eshop/themes/default/css/custom.css')) 
{
	$customCss = file_get_contents(JPATH_ROOT . '/components/com_eshop/themes/default/css/custom.css');
}
	
if (!empty($this->editor))
{
	echo $this->editor->display('custom_css', $customCss, '100%', '550', '75', '8', false, null, null, null, array('syntax' => 'css'));
}
else
{
	?>
		<textarea class="form-control" name="custom_css" rows="20"><?php echo $customCss; ?></textarea>
	<?php
}