<?php

class TestDataAccess extends \PHPUnit\Framework\TestCase
{
    private DataAccess $db;

    protected function setUp(): void
    {
        $this->pdo = getTestableSqliteConnection();
        $this->db = new DataAccess();
    }

    public function testConnection(): void
    {
        $this->assertTrue($this->db->connect());
    }

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
