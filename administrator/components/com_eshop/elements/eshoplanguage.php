<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2013 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\Filesystem\Folder;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldEshoplanguage extends FormField
{

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $_name = 'eshoplanguage';

	public function getInput()
	{
		$path      = JPATH_ROOT . '/language';
		$folders   = Folder::folders($path);
		$languages = [];

		foreach ($folders as $folder)
		{
			if ($folder != 'pdf_fonts' && $folder != 'overrides')
			{
				$languages[] = $folder;
			}
		}

		$options = [];

		foreach ($languages as $item)
		{
			$options[] = HTMLHelper::_('select.option', $item, $item);
		}

		$language = HTMLHelper::_('select.genericlist', $options, $this->name, 'class="input-xlarge form-select"', 'value', 'text', $this->value);

		return $language;
	}
}