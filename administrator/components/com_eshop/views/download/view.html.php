<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewDownload extends EShopViewForm
{

	public function _buildListArray(&$lists, $item)
	{
		$files = Folder::files(JPATH_ROOT . '/media/com_eshop/downloads');
		sort($files);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('ESHOP_NONE'));
		for ($i = 0, $n = count($files); $i < $n; $i++)
		{
			$file      = $files[$i];
			$options[] = HTMLHelper::_('select.option', $file, $file);
		}
		$lists['existed_file'] = HTMLHelper::_(
			'select.genericlist',
			$options,
			'existed_file',
			'class="input-large form-select"',
			'value',
			'text',
			$item->filename
		);
		parent::_buildListArray($lists, $item);
	}
}