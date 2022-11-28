<?php

declare(strict_types=1);

namespace App\Admin;

use App\AppUtils;
use Pebble\DB\Utils as DBUtils;

class TableUtils extends AppUtils {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Method that gets the column types of the table
     */
    public function getColumnTypes(string $table) {
        $column_types = [];
        $sql = "SHOW COLUMNS FROM $table";
        $rows = $this->db->getAllQuery($sql);
        foreach ($rows as $row) {
            $column_types[$row['Field']] = $row['Type'];
        }

        // Simplify column types, e.g. varchar(255) to varchar and int(11) to int and tinyint(1) to tinyint
        foreach ($column_types as $key => $type) {
            $column_types[$key] = preg_replace('/\(\d+\)/', '', $type);
        }
        return $column_types;
    }

    /**
     * Get database name from config
     */
    public function getDBName(): string {
        $ary = DBUtils::parsePDOString($this->config->get('DB.url'));
        return $ary['dbname'];
    }
}
