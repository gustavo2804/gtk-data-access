<?php

use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__, 2)."/vendor/autoload.php");



function getTestableSqliteConnection()
{
    static $sqliteDBConnection = null;

    if (!$sqliteDBConnection)
    {
        $sqliteDBConnection = new PDO("C:\AppStonewood\Test\dbForTestRunner.sqlite", SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE);
    } 

    return $sqliteDBConnection;
}

function getTestableSqliteDataAccess()
{
    $sqliteDBConnection = getTestableSqliteConnection();

    static $testableDataAccess = null;

    if (!$testableDataAccess)
    {
        $testableDataAccess = new TestableDataAccess($sqliteDBConnection);
    }

    return $testableDataAccess;
}

final class TestDataAccess extends TestCase
{
    public function testInsertUpdate()
    {
        $testableDataAccess = getTestableSqliteDataAccess();

        // $obj["id"]             =           
        $obj["a"]              = "Test Update Object";        
        $obj["b"]              = "My first write";
        $obj["date_created"]   = date('Y-m-d H:i:s');
        $obj["date_modified"]  = null;

        $testableDataAccess->insert($obj);

        $this->assertTrue(
            isset($obj["id"]),
            "No se ha insertado un ID en este objeto."); 
    }
    public function testGetOneReturns()
    {
        $testableDataAccess = getTestableSqliteDataAccess();

    }
    public function testSaveImageSavesToClassPath()
    {
        $testableDataAccess = getTestableSqliteDataAccess();
    }
    public function testSaveImageSavesToOptionPath()
    {
        $testableDataAccess = getTestableSqliteDataAccess();
    }

    public function testUpdateStringWorksWhenPrimaryKeyIsFirstInMapping()
    {
        $testableDataAccess = getTestableSqliteDataAccess();
    }
}


class TestableDataAccess extends DataAccess
{
    public function register()
    {

        $columns = [
            GTKColumnMapping::stdStyle(1, "id",             null, "ID", [
                "isPrimaryKey"    => true,
                "isAutoIncrement" => true,
            ]),
            GTKColumnMapping::stdStyle(1, "a",              null, "A"),
            GTKColumnMapping::stdStyle(1, "b",              null, "B"),
            GTKColumnMapping::stdStyle(1, "date_created",   null, "Date Created"),
            GTKColumnMapping::stdStyle(1, "date_modified",  null, "Date Modified"),
        ]; 

        $this->tableName = 'TestableTable_'.$this->generateMicroTimeUUID();
    }
 

    private function generateMicroTimeUUID() 
    {
        $microTime = microtime(true);
        $microSeconds = sprintf("%06d", ($microTime - floor($microTime)) * 1e6);
        $time = new DateTime(date('Y-m-d H:i:s.' . $microSeconds, $microTime));
        $time = $time->format("YmdHisu"); // Format time to a string with microseconds
        return md5($time); // You can also use sha1 or any other algorithm
    }   

}
