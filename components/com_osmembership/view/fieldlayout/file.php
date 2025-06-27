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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Layout variables
 *
 * @var stdClass $row
 * @var string   $name
 * @var string   $value
 * @var string   $attributes
 */

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->registerAndUseScript('com_osmembership.ajaxupload', 'media/com_osmembership/assets/js/ajaxupload.min.js');
?>
<input type="button" value="<?php echo Text::_('OSM_SELECT_FILE'); ?>" id="button-file-<?php echo $name; ?>" class="btn btn-primary" />
<span class="osm-uploaded-file" id="uploaded-file-<?php echo $name; ?>">
<?php
    if ($value && file_exists(JPATH_ROOT . '/media/com_osmembership/upload/' . $value))
    {
    ?>
        <a href="<?php echo Route::_('index.php?option=com_osmembership&task=controller.download_file&file_name=' . $value); ?>"><i class="fa fa-donwload"></i><strong><?php echo $value; ?></strong></a>
    <?php
    }
?>
</span>
<input type="hidden" id="<?php echo $name; ?>" name="<?php echo $name; ?>"  value="<?php echo $value; ?>" />
<script type="text/javascript">
    new AjaxUpload('#button-file-<?php echo $name; ?>', {
        action: siteUrl + 'index.php?option=com_osmembership&task=upload_file&field_id=<?php echo $row->id; ?>',
        name: 'file',
        autoSubmit: true,
        responseType: 'json',
        onSubmit: function (file, extension) {
            jQuery('#button-file-<?php echo $name; ?>').after('<span class="wait">&nbsp;<img src="<?php echo Uri::root(true);?>/media/com_osmembership/ajax-loadding-animation.gif" alt="" /></span>');
            jQuery('#button-file-<?php echo $name; ?>').attr('disabled', true);
        },
        onComplete: function (file, json) {
            jQuery('#button-file-<?php echo $name; ?>').attr('disabled', false);
            jQuery('.error').remove();
            if (json['success']) {
                jQuery('#uploaded-file-<?php echo $name; ?>').html(file);
                jQuery('input[name="<?php echo $name; ?>"]').attr('value', json['file']);
            }
            if (json['error']) {
                jQuery('#button-file-<?php echo $name; ?>').after('<span class="error">' + json['error'] + '</span>');
            }

            jQuery('.wait').remove();
        }
    });
</script>