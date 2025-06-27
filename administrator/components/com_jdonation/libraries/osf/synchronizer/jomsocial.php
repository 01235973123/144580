<?php

use Joomla\CMS\Factory;
class OSFSynchronizerJomsocial
{

	public function getData($userId, $mappings)
	{
		$data = array();
		$db = Factory::getContainer()->get('db');
		$sql = 'SELECT cf.fieldcode , fv.value FROM #__community_fields AS cf ' . ' INNER JOIN #__community_fields_values AS fv ' .
			 ' ON cf.id = fv.field_id ' . ' WHERE fv.user_id = ' . $userId;
		$db->setQuery($sql);
		$rows = $db->loadObjectList('fieldcode');
		foreach ($mappings as $fieldName => $mappingFieldName)
		{
			if ($mappingFieldName && isset($rows[$mappingFieldName]))
			{
				if (stristr($rows[$mappingFieldName]->value, ","))
				{
					$rows[$mappingFieldName]->value = explode(',', $rows[$mappingFieldName]->value);
				}
				$data[$fieldName] = $rows[$mappingFieldName]->value;
			}
		}		
		return $data;
	}
}
