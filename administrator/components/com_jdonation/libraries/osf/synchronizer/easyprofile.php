<?php

use Joomla\CMS\Factory;
class OSFSynchronizerEasyprofile
{

	public function getData($userId, $mappings)
	{
		$data = array();
		$db = Factory::getContainer()->get('db');
		$sql = 'SELECT * from #__jsn_users where id = "'.$userId.'"';
		$db->setQuery($sql);
		$row = $db->loadObject();
		foreach ($mappings as $fieldName => $mappingFieldName)
		{
            $data[$fieldName] = $row->$mappingFieldName;
		}
		return $data;
	}
}
