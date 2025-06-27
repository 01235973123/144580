<?php
/**
 * @package     Joom Donation
 * @subpackage  Administrator
 * @copyright   Copyright (C) 2025 Ossolution Team
 * @license     GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Factory;

/**
 * Export model for Joom Donation
 */
class DonationModelExport extends OSFModel
{
    /**
     * Database object
     */
    protected $db;
    
    /**
     * Database prefix
     */
    protected $prefix;
    
    /**
     * Tables to export
     */
    protected $tables = [
        'jd_campaigns',
        'jd_categories', 
        'jd_configs',
        'jd_donors',
        'jd_fields',
        'jd_field_value'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->db = Factory::getDbo();
        $this->prefix = $this->db->getPrefix();
    }
    
    /**
     * Generate complete SQL export
     */
    public function generateExportSQL()
    {
        $sql = $this->getSQLHeader();
        
        foreach ($this->tables as $table) {
            $sql .= $this->exportTable($table);
        }
        
        $sql .= $this->getSQLFooter();
        
        return $sql;
    }
    
    /**
     * Get SQL file header
     */
    protected function getSQLHeader()
    {
        $date = date('M d, Y \a\t h:i A');
        $version = JVERSION;
        
        return "-- Joom Donation Export
-- Generated on: {$date}
-- Joomla Version: {$version}
-- 
-- Host: localhost
-- Server version: " . $this->db->getVersion() . "

SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
START TRANSACTION;
SET time_zone = \"+00:00\";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: Joom Donation Export
--

-- --------------------------------------------------------

";
    }
    
    /**
     * Get SQL file footer
     */
    protected function getSQLFooter()
    {
        return "
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
";
    }
    
    /**
     * Export single table
     */
    protected function exportTable($tableName)
    {
        $fullTableName = $this->prefix . $tableName;
        $exportTableName = '#__' . $tableName;
        
        // Check if table exists
        if (!$this->tableExists($fullTableName)) {
            return "-- Table {$fullTableName} does not exist\n\n";
        }
        
        $sql = "--\n";
        $sql .= "-- Table structure for table `{$exportTableName}`\n";
        $sql .= "--\n\n";
        
        // Drop table statement
        $sql .= "DROP TABLE IF EXISTS `{$exportTableName}`;\n";
        
        // Create table statement
        $sql .= $this->getCreateTableSQL($fullTableName, $exportTableName);
        
        // Insert data
        $sql .= $this->getInsertDataSQL($fullTableName, $exportTableName);
        
        $sql .= "-- --------------------------------------------------------\n\n";
        
        return $sql;
    }
    
    /**
     * Check if table exists
     */
    protected function tableExists($tableName)
    {
        $query = "SHOW TABLES LIKE " . $this->db->quote($tableName);
        $this->db->setQuery($query);
        $result = $this->db->loadResult();
        
        return !empty($result);
    }
    
    /**
     * Get CREATE TABLE SQL
     */
    protected function getCreateTableSQL($fullTableName, $exportTableName)
    {
        $query = "SHOW CREATE TABLE " . $this->db->quoteName($fullTableName);
        $this->db->setQuery($query);
        $result = $this->db->loadRow();
        
        if (!$result) {
            return "-- Error: Could not get CREATE TABLE statement for {$fullTableName}\n\n";
        }
        
        $createSQL = $result[1];
        
        // Replace table name with export name
        $createSQL = str_replace("`{$fullTableName}`", "`{$exportTableName}`", $createSQL);
        
        // Add IF NOT EXISTS
        $createSQL = str_replace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $createSQL);
        
        return $createSQL . ";\n\n";
    }
    
    /**
     * Get INSERT data SQL
     */
    protected function getInsertDataSQL($fullTableName, $exportTableName)
    {
        // Get all data from table
        $query = "SELECT * FROM " . $this->db->quoteName($fullTableName);
        $this->db->setQuery($query);
        $rows = $this->db->loadAssocList();
        
        if (empty($rows)) {
            return "-- No data found in table {$exportTableName}\n\n";
        }
        
        $sql = "--\n";
        $sql .= "-- Dumping data for table `{$exportTableName}`\n";
        $sql .= "--\n\n";
        
        // Get column names
        $columns = array_keys($rows[0]);
        $columnList = '`' . implode('`, `', $columns) . '`';
        
        $sql .= "INSERT INTO `{$exportTableName}` ({$columnList}) VALUES\n";
        
        $values = [];
        foreach ($rows as $row) {
            $rowValues = [];
            foreach ($row as $value) {
                if ($value === null) {
                    $rowValues[] = 'NULL';
                } else {
                    $rowValues[] = $this->db->quote($value);
                }
            }
            $values[] = '(' . implode(', ', $rowValues) . ')';
        }
        
        $sql .= implode(",\n", $values) . ";\n\n";
        
        return $sql;
    }
}
