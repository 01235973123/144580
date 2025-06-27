<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Table\Table;

defined('_JEXEC') or die;

class OSMembershipModelExporttmpl extends MPFModelAdmin
{
	/**
	 * Pre-process data before export template is being saved to database
	 *
	 * @param   Table     $row
	 * @param   MPFInput  $input
	 * @param   bool      $isNew
	 */
	protected function beforeStore($row, $input, $isNew)
	{
		$fields               = [];
		$exportTemplateFields = $input->get('export_tmpl_fields', [], 'array');

		foreach ($exportTemplateFields as $exportTemplateField)
		{
			if (!empty($exportTemplateField['field']))
			{
				$fields[] = $exportTemplateField['field'];
			}
		}

		$input->set('fields', json_encode($fields));
	}
}
