<?php

/**
 * @package     MPF
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2016 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

/**
 * Form Field class for the Joomla MPF.
 * Supports a hidden input.
 *
 * @package     Joomla.MPF
 * @subpackage  Form
 */
class MPFFormFieldHidden extends MPFFormField
{
	/**
	 * Field Type
	 *
	 * @var string
	 */
	protected $type = 'Hidden';

	/**
	 * Get the field input markup.
	 *
	 * @param   OSMembershipHelperBootstrap  $bootstrapHelper
	 *
	 * @return string
	 */
	public function getInput($bootstrapHelper = null)
	{
		return '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars(
				$this->value,
				ENT_COMPAT,
				'UTF-8'
			) . '" class="mp-hidden-field" />';
	}

	/**
	 * Get output of the field using for sending email and display on the registration complete page
	 *
	 * @param   bool                         $tableLess
	 *
	 * @param   OSMembershipHelperBootstrap  $bootstrapHelper
	 *
	 * @return string
	 */
	public function getOutput($tableLess = true, $bootstrapHelper = null)
	{
		return '';
	}
}
