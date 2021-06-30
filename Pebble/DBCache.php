<?php declare(strict_types=1);

namespace Pebble;

use Pebble\DBInstance;

class DBCache
{

    /**
     * Default database cache table name
     */
    private $table = 'cache_system';

    /**
     * constructor
     * @param   object $conn PDO connection
     * @param   string $table database table
     */
    public function __construct($table = null)
    {
        if ($table) {
            $this->table = $table;
        }
    }

    private function generateJsonKey($id) {

        $key = null;
        if (is_string($id)) {
            $key = $id;
        } else {
            $key = json_encode($id);
        }
        return $key;
    }

    private function generateHashKey($id)
    {

        $json_key = $this->generateJsonKey($id);
        return $this->hash($json_key);
    }

    /**
     * Hash a key using sha256
     */
    private function hash(string $key): string
    {
        return hash('sha256', $key);
    }

    /**
     * Get a cache result
     * @param string $id
     * @param int $max_life_time max life time in seconds
     * @return mixed $res NULL if no result of if result is outdated. Else return the result
     */
    public function get($id, int $max_life_time = 0)
    {

        $query = "SELECT * FROM {$this->table} WHERE id = ? ";
        $db = DBInstance::get();
        $row = $db->prepareFetch($query, [$this->generateHashKey($id)]);

        if (empty($row)) {
            return null;
        }

        if ($max_life_time) {
            $expire = $row['unix_ts'] + $max_life_time;
            if ($expire < time()) {
                $this->delete($this->generateHashKey($id));
                return null;
            } else {
                return json_decode($row['data'], true);
            }
        } else {
            return json_decode($row['data'], true);
        }
    }
    /**
     * Sets a string in cache
     */
    public function set($id, $data): bool
    {
        $db = DBInstance::get();
        $db->beginTransaction();

        $res = $this->delete($id);
        if (!$res) {
            $db->rollback();
            return false;
        }

        $query = "INSERT INTO {$this->table} (id, json_key, unix_ts, data) VALUES (?, ?, ?, ?)";
        $res = $db->prepareExecute($query, [$this->generateHashKey($id), $this->generateJsonKey($id), time(), json_encode($data)]);

        if (!$res) {
            $db->rollback();
            return false;
        }

        return $db->commit();
    }

    /**
     * Delete a string from cache
     * @param   int     $id
     * @return  boolean $res db result
     */
    public function delete($id): bool
    {

        $db = DBInstance::get();

        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $row = $db->prepareFetch($query, [$this->generateHashKey($id)]);

        if (!empty($row)) {
            $query = "DELETE FROM {$this->table} WHERE id = ?";
            return $db->prepareExecute($query, [$this->generateHashKey($id)]);
        }

        return true;
    }
}
