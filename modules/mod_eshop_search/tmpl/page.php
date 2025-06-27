<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2011 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
?>
<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="<?php echo Route::_('index.php?option=com_eshop&task=search'); ?>" method="post" name="eshop-search" id="eshop-search">
	<div class="eshop-search<?php echo $params->get( 'moduleclass_sfx' ) ?>">
        <div class="input-prepend">
            <button  type="submit" class="btn"><i class="fa fa-search"></i></button>
            <input class="inputbox product_search" type="text" name="keyword" id="prependedInput" value="" placeholder="<?php echo Text::_('ESHOP_FIND_A_PRODUCT'); ;?>">
        </div>
		<input type="hidden" name="live_site" id="live_site" value="<?php echo Uri::root(); ?>">
		<input type="hidden" name="image_width" id="image_width" value="<?php echo $params->get('image_width')?>">
		<input type="hidden" name="image_height" id="image_height" value="<?php echo $params->get('image_height')?>">
		<?php
		$categories = $params->get('category_ids');
		
		if (isset($categories))
		{
		    for ($i = 0; $n = count($categories), $i < $n; $i++)
		    {
		        $categoryId = $categories[$i];
		        ?>
    				<input type="hidden" name="category_ids[]" id="category_ids_<?php echo $categoryId; ?>" value="<?php echo $categoryId; ?>">
    			<?php
    		}
		}
		?>
		<input type="hidden" name="description_max_chars" id="description_max_chars" value="<?php echo $params->get('description_max_chars',50); ?>">
	</div>
</form>
