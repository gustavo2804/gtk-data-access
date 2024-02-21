<?php

class DataAccessAddColumns
{
      // Column Exists

      public function doesColumnExist($columnName)
      {
          $driverName = $this->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
  
          switch ($driverName)
          {
              case 'sqlite':
                  return $this->doesColumnExistSQLite($columnName);
                  break;
              
              case 'sqlsrv':
                  return $this->doesColumnExistSqlServer($columnName);
                  break;
              
              case 'mysql':
              case 'pgsql':
              case 'oci': // Oracle
              default:
                  gtk_log("`addColumnIfNotExists` - Connected to a database with unsupported driver: " . $driverName);
                  die();
          }
      }
  
      public function doesColumnExistSqlServer($columnName)
      {
          $tableName = $this->tableName();
  
          $sql = "SELECT * FROM information_schema.columns 
                  WHERE 
                  table_name = '{$tableName}' 
                  AND 
                  column_name = '{$columnName}'";
  
          $stmt = $this->getDB()->prepare($sql);
  
          $stmt->execute();
  
          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
          return count($result) > 0;
      }
  
      function doesColumnExistSQLite($columnName)
      {
          $debug = false;
  
          $sql = "PRAGMA table_info(".$this->tableName().")";
  
          if ($debug)
          {
              gtk_log("SQL: {$sql}");
          }   
  
          $stmt = $this->getDB()->prepare($sql);
          $stmt->execute();
          $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
          foreach ($columns as $column) 
          {
              if ($column['name'] === $columnName) {
                  return true;
              }
          }
          return false;
      }
  
      // ADD Columns
  
      public function addColumnIfNotExists($columnName, $options = null)
      {
          $debug = false;
  
  
          $driverName = $this->getDB()->getAttribute(PDO::ATTR_DRIVER_NAME);
  
          switch ($driverName)
          {
              case 'sqlite':
                  // $sql = "ALTER TABLE ".$this->tableName()." ADD COLUMN IF NOT EXISTS ".$columnName;
                  // if ($debug) { gtk_log("SQL: {$sql}"); }
                  // $this->getDB()->exec($sql);
                  $this->addColumnIfNotExistsSQLite($columnName, $options);
                  break;
              
              case 'sqlsrv':
  
          
                  $this->addColumnIfNotExistsSQLServer($columnName, $options);
                  break;
              
              case 'mysql':
              case 'pgsql':
              case 'oci': // Oracle
              default:
                  gtk_log("`addColumnIfNotExists` - Connected to a database with unsupported driver: " . $driverName);
                  die();
          }
  
  
      }
  
      function addColumnIfNotExistsSQLServer($columnName, $options)
      {
          if (!$this->doesColumnExistSqlServer($columnName))
          {
              $columnType   = $options['columnType']   ?? 'nvarchar';
              $columnSize   = $options['columnSize']   ?? 255;
              $allowNulls   = $options['allowNulls']   ?? true;
              $defaultValue = $options['defaultValue'] ?? null;
              $constraint   = null;
      
              // Set data type with size if specified
              $sqlColumnType = ($columnSize) ? "{$columnType}({$columnSize})" : $columnType;
      
              // Check if the column should allow nulls
              $sqlAllowNulls = ($allowNulls) ? "NULL" : "NOT NULL";
      
              // Construct the main ALTER TABLE statement
              $sql = "ALTER TABLE {$this->tableName()} ADD {$columnName} {$sqlColumnType} {$sqlAllowNulls}";
  
              if ($constraint)
              {
                  // 
              }
      
              // If there's a default value, append it to the SQL statement
              if ($defaultValue) {
                  $sql .= " DEFAULT '{$defaultValue}'";
              }
      
              $stmt = $this->getDB()->prepare($sql);
              $stmt->execute();
  
              // $columnType   = null;
              // $columnSize   = null;
              // $allowNulls   = null;
              // $defaultValue = null;
  // 
              // if ($options)
              // {
              //     $columnType   = arrayValueIfExists("columnType", $options);
              //     $columnSize   = arrayValueIfExists("columnSize", $options);
              //     $allowNulls   = arrayValueIfExists("allowNulls", $options);
              //     $defaultValue = arrayValueIfExists("defaultValue", $options);
              // }
  // 
              // if (!$columnType)
              // {
              //     $columnType = "nvarchar";
              // }
  // 
              // if (!$columnSize)
              // {
              //     $columnSize = 255;
              // }
  // 
              // if (!$allowNulls)
              // {
              //     $allowNulls = true;
              // }
  // 
              // $sql = "ALTER TABLE {$this->tableName()} ADD COLUMN {$columnName} {$columnType}";
  // 
              // if ($defaultValue)
              // {
              //     // Append to SQL
              // }
  // 
              // $stmt = $this->getDB()->prepare($sql);
  // 
              // $stmt->execute();
              
          }
      }
  
  
  
      function addColumnIfNotExistsSQLite($columnName, $options = null)
      {
          if (!$this->doesColumnExistSQLite($columnName)) {
              $sql = "ALTER TABLE ".$this->tableName()." ADD COLUMN $columnName"; //  $columnType";
              $this->getDB()->exec($sql);
          }
      }
  
  
  
}
