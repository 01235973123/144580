<?php

use Joomla\CMS\Factory;
class OSFSynchronizerEasysocial
{

	public function getData($userId, $mappings)
	{
		$data = array();
		$db = Factory::getContainer()->get('db');
		$sql = 'SELECT cf.id as fieldcode, fv.raw FROM #__social_fields AS cf ' . ' INNER JOIN #__social_fields_data AS fv ' .
			 ' ON cf.id = fv.field_id ' . ' WHERE fv.uid = ' . $userId;
		$db->setQuery($sql);
		$rows = $db->loadObjectList('fieldcode');
		foreach ($mappings as $fieldName => $mappingFieldName)
		{
            $data[$fieldName] = $rows[$mappingFieldName]->raw;
		}
		return $data;
	}
}
