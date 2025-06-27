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

/**
 * Layout variables
 *
 * @var array $fields
 */
?>
<table class="table table-striped table-bordered table-condensed">
    <thead>
    <tr>
        <th class="text_left">
			<?php echo Text::_('OSM_FIELD'); ?>
        </th>
        <th class="text_left">
			<?php echo Text::_('OSM_OLD_VALUE'); ?>
        </th>
        <th class="text_left">
			<?php echo Text::_('OSM_NEW_VALUE'); ?>
        </th>
    </tr>
    </thead>
    <tbody>
        <?php
			foreach ($fields as $field)
			{
				if (is_string($field->old_value) && is_array(json_decode($field->old_value)))
				{
					$field->old_value = implode('<br />', json_decode($field->old_value));
				}

				if (is_string($field->new_value) && is_array(json_decode($field->new_value)))
				{
					$field->new_value = implode('<br />', json_decode($field->new_value));
				}
			?>
                <tr>
                    <td><?php echo $field->title; ?></td>
                    <td><?php echo $field->old_value; ?></td>
                    <td><?php echo $field->new_value; ?></td>
                </tr>
            <?php
			}
		?>
    </tbody>
</table>
