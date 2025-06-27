<?php
/**
 * @package     MPF
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2016 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

class MPFFormFieldFile extends MPFFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'File';

	public function __construct($row, $value = null, $fieldSuffix = null)
	{
		parent::__construct($row, $value, $fieldSuffix);

		if ($row->size)
		{
			$this->attributes['size'] = $row->size;
		}
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @param   OSMembershipHelperBootstrap  $bootstrapHelper
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput($bootstrapHelper = null)
	{
		$data = [
			'name'       => $this->name,
			'value'      => $this->value,
			'attributes' => $this->buildAttributes(),
			'row'        => $this->row,
		];

		return OSMembershipHelperHtml::loadCommonLayout('fieldlayout/file.php', $data);
	}
}
