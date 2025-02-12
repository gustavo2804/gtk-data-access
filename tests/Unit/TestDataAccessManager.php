<?php 



class TestDataAccessManager extends \PHPUnit\Framework\TestCase
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

    public function testCreateSQLTableString()
    {
        $testableDataAccess = getTestableSqliteDataAccess();

        $sql = $testableDataAccess->createSQLTableString();

        $this->assertTrue(
            strpos($sql, "CREATE TABLE") !== false,
            "No se ha encontrado la cadena CREATE TABLE en la sentencia SQL.");
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
