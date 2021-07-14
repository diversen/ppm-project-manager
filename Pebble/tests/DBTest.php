<?php declare (strict_types = 1);

use PHPUnit\Framework\TestCase;
use Pebble\Config;
use Pebble\DB;

$config_dir = dirname(__FILE__) . '/../../config';
Config::readConfig($config_dir);



use \Pebble\DBInstance;

final class DBTest extends TestCase
{

    /**
     * @return \Pebble\DB
     */
    private function getDB()
    {
        $db_config = Config::getSection('DB');
        DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);
        return DBInstance::get();
    }

    private function cleanup()
    {
        $db = $this->getDB();
        $db->prepareExecute('DROP TABLE IF EXISTS account_test');
    }

    public function createTestTable()
    {

        $sql = <<<EOF
CREATE TABLE `account_test` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `password` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;
EOF;

        $db = $this->getDB();

        $res = $db->prepareExecute($sql);
        return $res;
    }

    public function test_prepareExecuteBadSQL()
    {

        $this->expectException(PDOException::class);
        $db = $this->getDB();

        // Bad SQL
        $bad_sql = "CREATE xxTABLE bogus sql";

        $db->prepareExecute($bad_sql);
    }



    public function test_prepareExecute_GoodSQL()
    {
        $this->cleanup();
        $res = $this->createTestTable();
        $this->assertEquals(
            $res,
            true
        );

    }

    /**
     * Execute a prepared statement with an array of named values
     */
    public function test_prepareExecute_WithBoundVariables()
    {

        $this->cleanup();
        $this->createTestTable();

        $values = array(':email' => 'test@test.dk', ':password' => 'secret');
        $sql = "INSERT INTO account_test (`email`, `password`) VALUES (:email, :password)";

        $db = $this->getDB();
        $res = $db->prepareExecute($sql, $values);
        $this->assertEquals(
            $res,
            true
        );
    }

    /**
     *  Execute a prepared statement with an array of positional values
     */
    public function test_prepareExecute_WithPositionalValues()
    {

        $this->cleanup();
        $this->createTestTable();

        $values = array('test@test.dk', 'secret');
        $sql = "INSERT INTO account_test (`email`, `password`) VALUES (?, ?)";

        $db = $this->getDB();
        $res = $db->prepareExecute($sql, $values);
        $this->assertEquals(
            $res,
            true
        );
    }

    /**
     *  Execute a prepared statement with an array of positional values
     */
    public function test_lastInsertId_string()
    {

        $this->cleanup();
        $this->createTestTable();

        $values = array('test@test.dk', 'secret');
        $sql = "INSERT INTO account_test (`email`, `password`) VALUES (?, ?)";

        $db = $this->getDB();
        $db->prepareExecute($sql, $values);
        $last_insert_id = $db->lastInsertId();

        $this->assertIsString($last_insert_id);
        $this->assertGreaterThan(0, (int)$last_insert_id);
        
    }

    /**
     *  Execute a prepared statement with an array of positional values
     *  Note: This method may not return a meaningful or consistent result across different PDO drivers
     *  MySQL returns "0"
     */
    public function test_lastInsertId_false()
    {

        $this->cleanup();
        $this->createTestTable();

        $db_config = Config::getSection('DB');
        $db = new DB($db_config['url'], $db_config['username'], $db_config['password']);
        
        $last_insert_id = $db->lastInsertId();

        $this->assertIsString($last_insert_id);
        $this->assertEquals( (int)$last_insert_id, 0);

    }

    /**
     * Utils method that just adds three rows
     */
    private function addRows() {


        $values = [
            ['test@test.dk', 'secret'],
            ['test2@test.dk', 'secret2'],
            ['test3@test.dk', 'secret3']
        ];

        $db = $this->getDB();
        foreach($values as $value) {
            $sql = "INSERT INTO account_test (`email`, `password`) VALUES (?, ?)";
            $db->prepareExecute($sql, $value);
        }
    }

    public function test_prepareFetchAll()
    {

        $this->cleanup();
        $this->createTestTable();
        $this->addRows();

        $db = $this->getDB();
        
        $rows = $db->prepareFetchAll("SELECT * FROM account_test LIMIT 0, 2");
        $this->assertIsArray($rows);
        $this->assertEquals( count($rows), 2);

    }

    public function test_prepareFetch()
    {

        $this->cleanup();
        $this->createTestTable();
        $this->addRows();

        $db = $this->getDB();
        
        $row = $db->prepareFetch("SELECT * FROM account_test");

        $rows[] = $row;
        
        $this->assertIsArray($row);
        $this->assertEquals( count($rows), 1);

    }

    public function test_getStmt()
    {

        $this->cleanup();
        $this->createTestTable();
        $this->addRows();

        $db = $this->getDB();
        
        $stmt = $db->getStmt("SELECT * FROM account_test");
        $this->assertEquals(get_class($stmt), 'PDOStatement');


    }

    public function test_rowCount()
    {

        $this->cleanup();
        $this->createTestTable();
        $this->addRows();

        $db = $this->getDB();
        $db->prepareExecute("UPDATE account_test SET `email` = 'some_test_email@test.dk'");
        
        $rows_affected = $db->rowCount();
        $this->assertEquals($rows_affected, 3);


    }

    public function test_rollback()
    {

        $this->cleanup();
        $this->createTestTable();

        DBInstance::close();

        $db = $this->getDB();
        $res = $db->beginTransaction();
        $this->assertEquals(true, $res);
        
        $this->addRows();
        $res = $db->rollback();
        $this->assertEquals(true, $res);
        
        $rows = $db->prepareFetchAll("SELECT * FROM account_test");
        $num_rows = count($rows);
        $this->assertEquals($num_rows, 0);

    }

    public function test_commit()
    {

        $this->cleanup();
        $this->createTestTable();

        $db = $this->getDB();
        $res = $db->beginTransaction();
        $this->assertEquals(true, $res);
        
        $this->addRows();
        $res = $db->commit();
        $this->assertEquals(true, $res);
        
        $rows = $db->prepareFetchAll("SELECT * FROM account_test");
        $num_rows = count($rows);
        $this->assertEquals($num_rows, 3);

    }

    public function test_insert()
    {

        $this->cleanup();
        $this->createTestTable();

        $db = $this->getDB();
        $res = $db->insert('account_test',  ['email' => 'test4@test.dk', 'password' => 'secret4']);
        $this->assertEquals(true, $res);

        $row = $db->getOne('account_test', ['email' => 'test4@test.dk']);
        $this->assertEquals($row['email'], 'test4@test.dk');
        
    }

    public function test_update()
    {

        $this->cleanup();
        $this->createTestTable();
        $this->addRows();

        $db = $this->getDB();
        $res = $db->update(
            'account_test',  
            ['email' => 'test_update_zxc@test.dk', 'password' => 'update_very_secret'], 
            ['email' => 'test@test.dk']
        );

        $this->assertEquals(true, $res);


        $row = $db->getOne('account_test', ['email' => 'test_update_zxc@test.dk']);
        $this->assertEquals($row['email'], 'test_update_zxc@test.dk');
        $this->assertEquals($row['password'], 'update_very_secret');
        
    }

    public function test_getOne()
    {

        $this->cleanup();
        $this->createTestTable();

        $db = $this->getDB();
        $res = $db->insert('account_test',  ['email' => 'test4@test.dk', 'password' => 'secret4']);
        $this->assertEquals(true, $res);

        $row = $db->getOne('account_test', ['email' => 'test4@test.dk']);
        $this->assertEquals($row['email'], 'test4@test.dk');        

    }

    public function test_getAll()
    {

        $this->cleanup();
        $this->createTestTable();
        $this->addRows();

        $db = $this->getDB();

        $rows = $db->getAll('account_test', ['email' => 'test@test.dk']);
        $this->assertIsArray($rows);
        $row = $rows[0];
        
        $this->assertEquals($row['email'], 'test@test.dk');        

    }

    public function test_getWhereSql() {

        $db = $this->getDB();
        $where = $db->getWhereSql(['id' => 100, 'test' => 'this is a test']);
        $this->assertEquals($where, " WHERE  `id`=:id  AND  `test`=:test  ");
        
    }
}