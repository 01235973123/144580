<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

/**
 * Layout variables
 *
 * @var MPFConfig $config
 */

$form                    = Form::getInstance('common_tags', JPATH_ADMINISTRATOR . '/components/com_osmembership/view/configuration/forms/common_tags.xml');
$formData['common_tags'] = [];

if ($config->common_tags)
{
	$commonTags = json_decode($config->common_tags, true);
}
else
{
	$commonTags = [];
}

foreach ($commonTags as $commonTag)
{
	$formData['common_tags'][] = [
		'name'  => $commonTag['name'],
		'value' => $commonTag['value'],
	];
}

$form->bind($formData);

?>
<p class="text-info"><?php echo Text::_('OSM_COMMON_TAGS_EXPLAIN'); ?></p>
<?php
foreach ($form->getFieldset() as $field)
{
	echo $field->input;
}
