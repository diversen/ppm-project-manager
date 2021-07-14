<?php declare(strict_types=1);

namespace Pebble;

use Exception;

use PDO;

/**
 * Simple database class that can do anything you need to do with a database
 */
class DB
{

    /**
     * Var holding current stmt
     */
    private $stmt = null;

    /**
     * var holding DB handle
     */
    private $dbh = null;

    /**
     * Set database handle direct
     */
    public function setDbh(PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    /**
     * Return the objects database handle.
     */
    public function getDbh () {
        return $this->dbh;
    }

    /**
     * Create a database handle in the constructor
     */
    public function __construct(string $url, string $username = '', string $password = '', array $options = [])
    {

            $this->dbh = new PDO(
                $url,
                $username,
                $password,
                $options
            );
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         
    }

    /**
     * Prepare and execute an arbitrary string of SQL
     * `$db->prepareExecute('DELETE FROM auth WHERE email = ?', ['test@mail.com']); `
     */
    public function prepareExecute(string $sql, array $values = [], array $options = []): bool
    {   
        $this->stmt = $this->dbh->prepare($sql);
        return $this->stmt->execute($values);
    }

    /**
     * Prepare and fetch all rows, e.g
     * `$db->prepareFetchAll("SELECT * FROM invites WHERE status = ? ", [$status]);`
     */
    public function prepareFetchAll(string $sql, array $params = [], array $options = []) : array
    {

        $stmt = $this->getStmt($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Prepare execute, and fetch a single row or an empty array
     * `$db->prepareFetchAll("SELECT * FROM invites WHERE auth_id = ? ", [$auth_id]);`
     */
    public function prepareFetch(string $sql, array $params = [], array $options = []): array
    {

        $stmt = $this->getStmt($sql, $params);

        // Fetch returns false when 0 rows. FetchAll always returns an array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($row)) {
            return $row;
        }
        return [];
    }

    /**
     * Get PDOStatement where you can run e.g. `$stmt->fetch(PDO::FETCH_ASSOC)`;
     */
    public function getStmt(string $sql, array $params = [] ) {
        $this->stmt = $this->dbh->prepare($sql);
        $this->stmt->execute($params);
        return $this->stmt;
    }

    /**
     * Return number of affected rows
     * Use this with 'Delete', 'Update', 'Insert' if you need the row count.
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Returns last insert ID
     */
    public function lastInsertId(string $name = null): string
    {
        return $this->dbh->lastInsertId($name);
    }
    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->dbh->beginTransaction();
    }
    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->dbh->rollBack();
    }
    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->dbh->commit();
    }


    /**
     * Generate array with keys as named params
     * $post['title'] = $title will be transformed into
     * $post[':title'] = $title
     */
    private function generateNamedParams (array $values = []) {
        $ret_val = [];
        foreach($values as $key => $val) {
            $ret_val[':' . $key] = $val;
        }
        return $ret_val;
    }

    /**
     * Insert into $table a new row generated from $values:
     * `$db->insert('users_table', ['user_email' => 'test@test.com', 'user_name' => 'John Doe']);`
     */
    public function insert(string $table, array $values): bool
    {

        $field_names = array_keys($values);

        $sql = "INSERT INTO $table";

        // Escape field names
        $field_names_escaped = [];
        foreach($field_names as $key => $val) {
            $field_names_escaped[] = "`$val`";
        }

        // Insert values 
        $fields = '( ' . implode(', ', $field_names_escaped) . ' )';
        
        // Variable bindings
        $bound = '(:' . implode(', :', $field_names) . ' )';
        
        // SQL statement
        $sql .= $fields . ' VALUES ' . $bound;

        // Named params
        $values = $this->generateNamedParams($values);

        // Prepare and execute
        return $this->prepareExecute($sql, $values);
    }

    /**
     * UPDATE table row(s) 
     * `$db->update('user_table', ['user_email' => 'new_email@domain', 'user_name' => 'new name'], ['id' => 42]);`
     */
    public function update(string $table, array $values, array $where): bool
    {

        $sql = "UPDATE $table SET ";

        $final_values = [];

        // Generate named update parameters from insert value keys
        foreach ($values as $field => $value) {
            $update_ary[] = " `$field`=" . ":$field ";
            $final_values[$field] = $value;
        }

        $sql .= implode(',', $update_ary);
        $sql .= " WHERE ";

        // Generate named WHERE parameters from where array
        $i = 0;
        foreach ($where as $field => $value) {

            // Update values may be the same as where values
            // Ensure that all named params are unique
            $field_key = $field;
            if (isset($final_values[$field])) {
                $field_key = $field . '_' . $i;
                $i += 1;

            }

            $where_ary[] = " `$field`=" . ":$field_key ";
            $final_values[$field_key] = $value;
        }

        $sql .= implode(' AND ', $where_ary);
        $final_values = $this->generateNamedParams($final_values);

        return $this->prepareExecute($sql, $final_values);
    }

    /**
     * Generates simple where part of SQL, e.g. ['email' => 'some@email.dk', 'user' => 'some user'] 
     * returns WHERE username = :username AND user = :user
     * which then can be used in `prepareFetchAll` and `prepareFetch`
    */
    public function getWhereSql(array $where): string {

        if (empty($where)){
            return ' ';
        } 

        foreach($where as $field => $value) {
            $where_ary[] = " `$field`=" . ":$field ";   
        }

        $sql  = " WHERE ";
        $sql .= implode(' AND ', $where_ary) . ' ';
        return $sql;
    }

    /**
     * Delete rows from a table
     * `$db->delete('project', ['id' => $id]);`
     */
    public function delete(string $table, array $where): bool
    {

        $sql = "DELETE FROM $table ";
        $sql.= $this->getWhereSql($where);

        $where = $this->generateNamedParams($where);
        return $this->prepareExecute($sql, $where);

    }
    
    /**
     * Shortcut to get one row, e.g:
     * `$db->getOne('auth', ['verified' => 1, 'email' => $email]);`
     */
    public function getOne(string $table, array $where) {

        $sql = "SELECT * FROM `$table` ";  
        $sql.= $this->getWhereSql($where);
        
        return $this->prepareFetch($sql, $where);

    }

    /**
     * Shortcut to get all rows, e.g:
     * `$db->getAll('invites', ['invite_email' => $email]);`
     */
    public function getAll(string $table, array $where) {

        $sql = "SELECT * FROM `$table` ";
        $sql.= $this->getWhereSql($where);

        return $this->prepareFetchAll($sql, $where);
    }
}
