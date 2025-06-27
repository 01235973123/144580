<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Editor\Editor;

/**
 * Layout variables
 *
 * @var string $editorPlugin
 */

$customFields = file_get_contents(JPATH_ROOT . '/components/com_osmembership/fields.xml');

if ($editorPlugin)
{
	echo Editor::getInstance($editorPlugin)->display('custom_fields', $customFields, '100%', '550', '75', '8', false, null, null, null, ['syntax' => 'xml']);
}
else
{
?>
		<textarea name="custom_fields" rows="20" class="form-control" style="width: 100%;"><?php echo $customFields; ?></textarea>
<?php
}

