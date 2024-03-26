<?php

function extractColumnNameFromUniqueConstraintError($errorMessage) {
    $pattern = '/UNIQUE constraint failed: \w+\.(\w+)/';
    if (preg_match($pattern, $errorMessage, $matches)) {
        return $matches[1];
    }
    return null;
}

function extractTableNameFromUniqueConstraintError($errorMessage) {
    $pattern = '/UNIQUE constraint failed: (\w+)\.\w+/';
    if (preg_match($pattern, $errorMessage, $matches)) {
        return $matches[1];
    }
    return null;
}

class QueryExceptionManager
{
    public $dataSource;
    public $exception; 
    public $sql; 
    public $item; 
    public $outError;

    public static function manageQueryExceptionForDataSource(
        $dataSource,
        $exception, 
        $sql, 
        $item = null, 
        &$outError = ''
    ) {
        $queryException = new self();

        $queryException->dataSource = $dataSource;
        $queryException->exception  = $exception;
        $queryException->sql        = $sql;
        $queryException->item       = $item;
        $queryException->outError   = $outError;

        return $queryException->handle(
            $exception, 
            $sql, 
            $item, 
            $outError);
    }

    public function __construct()
    {
        
    }



    public function handle(
        $exception, 
        $sql, 
        $item, 
        $outError
    ){
        $debug = false;
        $currentUser = DataAccessManager::get("persona")->getCurrentUser();
        $isDeveloper = DataAccessManager::get("persona")->isInGroups($currentUser, [
            "DEV",
        ]) ?? $debug;

        if ($debug)
        {
            return $this->handleForDeveloper(
                $exception, 
                $sql, 
                $item, 
                $outError);
        }
        else
        {
            return $this->handleForMereMortals(
                $exception, 
                $sql, 
                $item, 
                $outError);
        }
    }

    public function handleForMereMortals(
        $exception, 
        $sql, 
        $item, 
        $outError = ''
    ){
        $exCode      = $exception->getCode();
        $exMessage   = $exception->getMessage();

        $toReturn = [];
        $errorMessage = null;
        if (($exCode == '42000') || (strpos($exMessage, 'Incorrect syntax') !== false))
        {
            $errorMessage = "Incorrect syntax: ".$sql;
        }
        else if ((strpos($exMessage, 'String or binary data would be truncated') !== false)) // 22001
        {
            $errorMessage = $this->errorMessageForTruncationOnItem($item, $sql);
        }
        else if ((strpos($exMessage, 'Invalid object name') !== false)) // ????
        {
            $errorMessage = "Error grave";
        }
        else if (static::isUniqueConstraintException($exception))        
        {
            $dataSourceTableName = $this->dataSource->tableName();
            $errorTable = extractTableNameFromUniqueConstraintError($exMessage);
            $errorColumn = extractColumnNameFromUniqueConstraintError($exMessage);
            $columnMapping = $this->dataSource->columnMappingForKey($errorColumn);
            $errorMessage = "Esta intentando duplicar un dato que no se puede duplicar: $columnMapping->formLabel()";
        }
        throw new Exception($errorMessage);
        return $errorMessage;
    }

    // ($exCode == "23000") || strpos($exMessage, "UNIQUE constraint failed"))
    public static function isUniqueConstraintException($exception) {
        $exCode = $exception->getCode();
        $exMessage = $exception->getMessage();
        return ($exCode == "23000") || strpos($exMessage, "UNIQUE constraint failed");
    }
    
    public function handleForDeveloper(
        $exception, 
        $sql, 
        $item, 
        $outError
    ){
        $exCode    = $exception->getCode();
        $exMessage = $exception->getMessage();

        $errorMessage = "Got exception with code (".$exCode.") - ".$exMessage;

        gtk_log($errorMessage);

        if ($outError)
        {
            $outError .= $errorMessage;
        }

        if (($exCode == '42000') || (strpos($exMessage, 'Incorrect syntax') !== false))
        {
            
            $errorMessage = "Incorrect syntax: ".$sql;
            gtk_log($errorMessage);
            if ($outError)
            {
                $outError .= $errorMessage;
            }
        }
        else if ((strpos($exMessage, 'String or binary data would be truncated') !== false)) // 22001
        {
            $errorMessage = $this->errorMessageForTruncationOnItem($item, $sql);
            gtk_log($errorMessage);
            if ($outError)
            {
                $outError .= $errorMessage;
            }
        }
        else if ((strpos($exMessage, 'Invalid object name') !== false)) // ????
        {
            /*
            3) CreatePermisoParaLiberarContenedorTest::testFailure
            PDOException: SQLSTATE[42S02]: [Microsoft][ODBC Driver 17 for SQL Server][SQL Server]Invalid object name 'PermisoParaLiberarContenedor'.
            */
            $errorMessage = $exMessage;
            $errorMessage .= "\nConsider running a create table statement...";
            $errorMessage .= "\n".$this->dataSource->createTableSQLString();

            gtk_log($errorMessage);
            if ($outError)
            {
                $outError .= $errorMessage;
            }

        }
        else
        {
            $errorMessage = $this->genericDebugabbleErrorMessage($item, $sql);
            gtk_log($errorMessage);
            if ($outError)
            {
                $outError .= $errorMessage;
            }
        }
    
        if ($this->outError)
        {
            return null;
        }
        {
            throw $this->exception;
        }
    }

    public function genericDebugabbleErrorMessage($item, $sql)
    {
        $accumulator = '';

        $toAccumulate = function ($key, $value, $columnMapping) {
            $toReturn = "";

              
            $sqlColumn = $columnMapping->sqlServerKey;
            $key = $key;

            $sqlColumnWidth = 44;
            $keyColumnWidth = 44;
            
            $formattedSqlColumn = str_pad($sqlColumn, $sqlColumnWidth);
            $formattedKey = str_pad($key, $keyColumnWidth);

            $toReturn .= "$formattedSqlColumn - $formattedKey => $value\n";

            return $toReturn;
        };

        $toReturn = "";

        $sqlForTesting = $this->genericDebugSQLForItem($item, $accumulator, $toAccumulate);

        $toReturn .= "Column Lengths\n\n\n";
        $toReturn .= $accumulator;
        $toReturn .= "\n\n\n";
        $toReturn .= "SQL FOR TESTING\n\n\n";
        $toReturn .= $sqlForTesting;
        $toReturn .= "\n\n\n";
        $toReturn .= $sql;

        return $toReturn;
    }

    public function genericDebugSQLForItem($item, &$accumulator, $closureOnKeyValueColumnMapping = null)
    {

        $sqlColumnsString = "INSERT INTO ".$this->dataSource->tableName()." (";
        $sqlValuesString  = ") VALUES  (";
        $isFirst = true;

        foreach ($item as $key => $value)
        {
            
            $columnMapping = $this->dataSource->dataMapping->columnMappingForPHPKey($key);

            if ($columnMapping)
            {
                if ($value)
                {
                    if ($isFirst)
                    {
                        $isFirst = false;
                    }
                    else
                    {
                        $sqlColumnsString .= ",\n ";
                        $sqlValuesString .= ",\n ";
                    }

                    $sqlColumnsString .= $columnMapping->getSqlColumnName();

                    if (is_string($value))
                    {
                        $sqlValuesString  .= "'".$value."'";
                    }
                    else if (is_numeric($value))
                    {
                        $sqlValuesString  .= $value;
                    }
                    
                    if ($closureOnKeyValueColumnMapping)
                    {
                        if (is_callable($closureOnKeyValueColumnMapping))
                        {
                            $accumulator .= $closureOnKeyValueColumnMapping($key, $value, $columnMapping);
                        }
                    }
                }
            }
        }

        return $sqlColumnsString.$sqlValuesString.")";
    }

    public function getMaxLengthForColumnMapping($columnMapping)
    {
        return $columnMapping->getMaxLength();
    }

    public function errorMessageForTruncationOnItem($item, $sql)
    {
        $accumulator = '';

        $toAccumulate = function ($key, $value, $columnMapping) {
            $valueSize = null;

            if (is_string($value))
            {
                $valueSize = strlen($value);
            }
            else if (is_numeric($value))
            {
                $number = $value;
                $valueSize = strlen((string) $number);
            }

            if (!$valueSize)
            {
                return null;
            }

            $maxColumnLength = $this->getMaxLengthForColumnMapping($columnMapping);

            if ($valueSize >= $maxColumnLength)
            {
                $toReturn = "";

                $sqlColumn = $columnMapping->getSqlColumnName();
                $key = $key;

                $sqlColumnWidth = 44;
                $keyColumnWidth = 44;

                $formattedLengthCheck = '';

                $fmtLengthCheckSize = 10;




                if ($valueSize)
                {
                    $formattedLengthCheck  = str_pad($valueSize.'/'.$maxColumnLength, $fmtLengthCheckSize, ' ', STR_PAD_LEFT);
                }
                else
                {
                    $formattedLengthCheck = str_pad('N/A', $fmtLengthCheckSize, ' ', STR_PAD_LEFT);
                }


                $formattedSqlColumn        = str_pad($sqlColumn, $sqlColumnWidth);
                $formattedKey              = str_pad($key, $keyColumnWidth);

                $toReturn .= "Length check: ".$formattedLengthCheck." - SQL Column Name: ".$formattedSqlColumn." - Key: ".$formattedKey." => Value -> ".$value."\n";

                return $toReturn;
            }
            else
            {
                return null;
            }
        };

        $toReturn = "";

        $sqlForTesting = $this->genericDebugSQLForItem($item, $accumulator, $toAccumulate);

        $toReturn .= "Column Lengths\n\n\n";
        $toReturn .= $accumulator;
        $toReturn .= "\n\n\n";
        $toReturn .= "SQL FOR TESTING\n\n\n";
        $toReturn .= $sqlForTesting;
        $toReturn .= "\n\n\n";
        $toReturn .= $sql;

        return $toReturn;
    }




}
