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
use Joomla\CMS\Language\Text;

/**
 * Import model for Joom Donation
 */
class DonationModelImportdb extends OSFModel
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
     * Expected tables
     */
    protected $expectedTables = [
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
     * Import data from SQL file
     */
    public function importFromSQLFile($filePath)
    {
        $result = [
            'success' => false,
            'error' => '',
            'queries_executed' => 0,
            'tables_processed' => 0,
            'warnings' => []
        ];
        
        try {
            // Read and parse SQL file
            $sqlContent = file_get_contents($filePath);
            
            if ($sqlContent === false) {
                throw new Exception(Text::_('JD_IMPORT_CANNOT_READ_FILE'));
            }
            
            // Parse SQL queries
            $queries = $this->parseSQLFile($sqlContent);
            
            if (empty($queries)) {
                throw new Exception(Text::_('JD_IMPORT_NO_VALID_QUERIES'));
            }
            
            // Begin transaction
            $this->db->transactionStart();
            
            $tablesProcessed = [];
            
            // Execute queries
            foreach ($queries as $query) {
                if (trim($query) === '') {
                    continue;
                }
                
                try {
                    // Replace generic prefix with actual prefix
                    $processedQuery = $this->processQuery($query);
                    
                    if ($processedQuery) {
                        $this->db->setQuery($processedQuery);
                        $this->db->execute();
                        $result['queries_executed']++;
                        
                        // Track processed tables
                        $tableName = $this->extractTableName($processedQuery);
                        if ($tableName && !in_array($tableName, $tablesProcessed)) {
                            $tablesProcessed[] = $tableName;
                        }
                    }
                    
                } catch (Exception $e) {
                    $result['warnings'][] = Text::sprintf('JD_IMPORT_QUERY_WARNING', $e->getMessage());
                    continue;
                }
            }
            
            $result['tables_processed'] = count($tablesProcessed);
            
            // Commit transaction
            $this->db->transactionCommit();
            
            $result['success'] = true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->transactionRollback();
            $result['error'] = $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Parse SQL file content into individual queries
     */
    protected function parseSQLFile($content)
    {
        // Remove comments and normalize line endings
        $content = $this->cleanSQLContent($content);
        
        // Split by semicolon, but be careful with quoted strings
        $queries = [];
        $currentQuery = '';
        $inString = false;
        $stringChar = '';
        $escaped = false;
        
        for ($i = 0; $i < strlen($content); $i++) {
            $char = $content[$i];
            
            if ($escaped) {
                $currentQuery .= $char;
                $escaped = false;
                continue;
            }
            
            if ($char === '\\') {
                $currentQuery .= $char;
                $escaped = true;
                continue;
            }
            
            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
                $currentQuery .= $char;
                continue;
            }
            
            if ($inString && $char === $stringChar) {
                $inString = false;
                $stringChar = '';
                $currentQuery .= $char;
                continue;
            }
            
            if (!$inString && $char === ';') {
                $queries[] = trim($currentQuery);
                $currentQuery = '';
                continue;
            }
            
            $currentQuery .= $char;
        }
        
        // Add last query if exists
        if (trim($currentQuery) !== '') {
            $queries[] = trim($currentQuery);
        }
        
        return array_filter($queries);
    }
    
    /**
     * Clean SQL content from comments and normalize
     */
    protected function cleanSQLContent($content)
    {
        // Remove SQL comments
        $lines = explode("\n", $content);
        $cleanLines = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and comments
            if ($line === '' || 
                strpos($line, '--') === 0 || 
                strpos($line, '/*') === 0 || 
                strpos($line, '*/') !== false ||
                strpos($line, '/*!') === 0) {
                continue;
            }
            
            $cleanLines[] = $line;
        }
        
        return implode(' ', $cleanLines);
    }
    
    /**
     * Process individual query
     */
    protected function processQuery($query)
    {
        // Replace generic prefix with actual prefix
        $processedQuery = str_replace('#__', $this->prefix, $query);
        
        // Skip certain SQL commands that might cause issues
        $skipCommands = [
            'SET SQL_MODE',
            'START TRANSACTION',
            'SET time_zone',
            'SET @OLD_CHARACTER_SET_CLIENT',
            'SET @OLD_CHARACTER_SET_RESULTS',
            'SET @OLD_COLLATION_CONNECTION',
            'SET NAMES',
            'COMMIT'
        ];
        
        foreach ($skipCommands as $command) {
            if (stripos($processedQuery, $command) === 0) {
                return null;
            }
        }
        
        return $processedQuery;
    }
    
    /**
     * Extract table name from query
     */
    protected function extractTableName($query)
    {
        // Match CREATE TABLE, DROP TABLE, INSERT INTO patterns
        $patterns = [
            '/CREATE TABLE(?:\s+IF NOT EXISTS)?\s+`?([^`\s]+)`?/i',
            '/DROP TABLE(?:\s+IF EXISTS)?\s+`?([^`\s]+)`?/i',
            '/INSERT INTO\s+`?([^`\s]+)`?/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $query, $matches)) {
                return str_replace($this->prefix, '', $matches[1]);
            }
        }
        
        return null;
    }
    
    /**
     * Backup existing data before import
     */
    public function backupExistingData()
    {
        $backupQueries = [];
        
        foreach ($this->expectedTables as $table) {
            $fullTableName = $this->prefix . $table;
            
            // Check if table exists
            $query = "SHOW TABLES LIKE " . $this->db->quote($fullTableName);
            $this->db->setQuery($query);
            
            if ($this->db->loadResult()) {
                // Create backup table
                $backupTableName = $fullTableName . '_backup_' . date('Y_m_d_H_i_s');
                $backupQueries[] = "CREATE TABLE `{$backupTableName}` AS SELECT * FROM `{$fullTableName}`";
            }
        }
        
        return $backupQueries;
    }
    
    /**
     * Get import statistics
     */
    public function getImportStatistics()
    {
        $stats = [];
        
        foreach ($this->expectedTables as $table) {
            $fullTableName = $this->prefix . $table;
            
            try {
                $query = "SELECT COUNT(*) FROM " . $this->db->quoteName($fullTableName);
                $this->db->setQuery($query);
                $count = $this->db->loadResult();
                
                $stats[$table] = [
                    'exists' => true,
                    'count' => $count
                ];
            } catch (Exception $e) {
                $stats[$table] = [
                    'exists' => false,
                    'count' => 0
                ];
            }
        }
        
        return $stats;
    }
}
