<?php
/**
 * @package		   Joomla
 * @subpackage	   Membership Pro
 * @author		   Tuan Pham Ngoc
 * @copyright	   Copyright (C) 2012 - 2025 Ossolution Team
 * @license		   GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('OSM_SUBSCRIBER_EDIT'), 'generic.png');
ToolbarHelper::save('save');
ToolbarHelper::cancel('cancel');

if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_osmembership'))
{
	ToolbarHelper::preferences('com_osmembership');
}

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core');
?>
<form action="<?php echo $this->getFormAction(); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" enctype="multipart/form-data" class="form form-horizontal">
	<?php
		echo HTMLHelper::_( 'uitab.startTabSet', 'osm-profile', ['active' => 'profile-page', 'recall' => true]);
		echo HTMLHelper::_( 'uitab.addTab', 'osm-profile', 'profile-page', Text::_('OSM_PROFILE_INFORMATION'));
		echo $this->loadTemplate('profile');
		echo HTMLHelper::_( 'uitab.endTab');
		echo HTMLHelper::_( 'uitab.addTab', 'osm-profile', 'subscription-history-page', Text::_('OSM_SUBSCRIPTION_HISTORY'));
		echo $this->loadTemplate('subscriptions_history');
		echo HTMLHelper::_( 'uitab.endTab');

		if (count($this->plugins))
		{
			$count = 0 ;

			foreach ($this->plugins as $plugin)
			{
				$count++ ;

				if (empty($plugin['form']))
				{
					continue;
				}

				echo HTMLHelper::_( 'uitab.addTab', 'osm-profile', 'tab_' . $count, Text::_($plugin['title']));
				echo $plugin['form'];
				echo HTMLHelper::_( 'uitab.endTab');
			}
		}

		echo HTMLHelper::_( 'uitab.endTabSet');
	?>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>