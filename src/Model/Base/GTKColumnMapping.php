<?php

class GTKColumnMapping extends GTKColumnBase
{
    public $isUpdateKey;
    public $sqlServerKey;
    public $type;
    public $databaseType;
    public $nonPrimaryLookup;

    public $columnType;
    public $columnSize;
    public $allowNulls;
    public $defaultValue;
    public $isAutoIncrement;

    public function listDisplay($dataSource, $item, $itemIdentifier, $options = null)
    {
        $debug = false; 

        if ($debug)
        {
            error_log("GTKColumnMapping->listDispaly --- ".$this->phpKey);
        }

        $toReturn     = "";
        $value        = $this->valueFromDatabase($item);
        
        $wrapStart    = "<td ";
        $wrapStart   .= ' id="cell-'.$dataSource->dataAccessorName.'-'.$itemIdentifier.'-'.$this->phpKey.'"';
        $wrapStart   .= " class='text-center align-middle'";
        $wrapStart   .=  ">";
        $wrapEnd      = "</td>";

        if ($this->isPrimaryKey())
        {
            return '<td>'.$dataSource->editLinkForItem($item, [
                'label' => $value,
            ]).'</td>';
        }      
        
        $htmlForValue = "";

        $href = null;

        if ($this->linkTo)
        {

            $linkToModel = null;
            $lookupOnKey = null;

            if (is_string($this->linkTo))
            {
                $linkToModel = $this->linkTo;
                $lookupOnKey = "id";
            }
            else
            {
                $linkToModel = $this->linkTo["model"];
                $lookupOnKey = $this->linkTo["lookupOnKey"];
            }

            $baseURL = $linkToModel."/edit";

            $queryParameters = [
                "data_source" => $linkToModel,
                $lookupOnKey  => $value,
            ];

            $href = "/".$baseURL."?".http_build_query($queryParameters);


            if ($debug)
            {
                error_log("linkTo - ".$href);
            }
        }

        if ($href)
        {
            $htmlForValue .= '<a ';
            $htmlForValue .= ' href="'.$href.'" ';
            $htmlForValue .= ">";
        }

        $transformValueOnLists = $this->transformValueOnLists;

        if ($transformValueOnLists)
        {
            $value = $transformValueOnLists($item, $value);
        }

        $htmlForValue .= $value;
      
        if ($href)
        {
            $htmlForValue .= '</a>';
        }
        
        $toReturn .= $wrapStart;
        $toReturn .= $htmlForValue;
        $toReturn .= $wrapEnd;

        if ($debug)
        {
            error_log("GTKColumnMapping->listDisplay: --- \n  ".$toReturn);
        }


        return $toReturn;
    }

    public function doesItemContainOurKey($item)
    {
        if (parent::doesItemContainOurKey($item))
        {
            return true;
        }
        
        return isset($item[$this->sqlServerKey]);
    }

    public static function stdStyle($isUpdateKey, $phpKey, $sqlServerKey, $formLabel, $options = [])
    {
        $options["dbKey"]     = $sqlServerKey;
        $options["formLabel"] = $formLabel;

        $toReturn = new GTKColumnMapping($this, $phpKey, $options);
        
        return $toReturn;
    }


    public function __construct(
        $dataSource, 
        $phpKey, 
        $options = null
    ){
        parent::__construct($dataSource, $phpKey, $options);
        
        if ($options)
        {
            $this->sqlServerKey        = arrayValueIfExists('dbKey',            $options);
            $this->type                = arrayValueIfExists('type',             $options);
            $this->formInputType       = arrayValueIfExists('formInputType',    $options);
            $this->linkTo              = arrayValueIfExists('linkTo',           $options);
            $this->nonPrimaryLookup    = arrayValueIfExists('nonPrimaryLookup', $options);
            
            $this->columnType          = arrayValueIfExists('columnType',       $options);   
            $this->columnSize          = arrayValueIfExists('columnSize',       $options);   
            $this->allowNulls          = arrayValueIfExists('allowNulls',       $options);   
            $this->defaultValue        = arrayValueIfExists('defaultValue',     $options); 
            $this->isAutoIncrement     = arrayValueIfExists('isAutoIncrement',  $options); 
        }
    }

    ////////////////////////////////////////////////////////////////////
    // DB-Particulars
    // --------
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function dbColumnName()
    {
        return $this->getSqlColumnName();
    }
    
    public function getSqlColumnName()
    {
        $sqlKey = null;

        if ($this->sqlServerKey)
        {
            $sqlKey = $this->sqlServerKey;
        }
        else
        {
            $sqlKey = $this->phpKey;
        }

        return $sqlKey;
    }

    public function isUpdateKey()
    {
        return $this->isUpdateKey;
    }


    ////////////////////////////////////////////////////////////////////
    // GetValue
    // --------
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function getValueFromArray($array, $tableName = null)
    {
        $toReturn = null;

        if ($this->sqlServerKey)
        {
            if (array_key_exists($this->sqlServerKey, $array))
            {
                $toReturn = $this->valueFromSqlServerData($array);
            }
        }
        
        if ($toReturn)
        {
            return $toReturn;
        }
        
        if (array_key_exists($this->phpKey, $array))
        {
            $toReturn = $this->valueFromPHPData($array);
        }
    
        if ($toReturn)
        {
            return $toReturn;
        }

        if (!$tableName)
        {
            $tableName = $this->dataAccessor->tableName();
        }

        if ($tableName)
        {
            $key = $tableName."_".$this->phpKey;
            $keyExists = array_key_exists($key, $array);
            if ($keyExists)
            {
                $toReturn = $array[$key];
            }
        }

        if ($toReturn)
        {
            return $toReturn;
        }

        return null;
    }

    public function valueForItem($item)
    {
        return $this->getValueFromArray($item);
    }


    public function valueFromDatabase($data) 
    {
        return $this->getValueFromArray($data);
    }

    public function valueFromSqlServerData($data) 
    {
        $debug = false;

        $value = null;
        $key   = $this->sqlServerKey;
        
        if (array_key_exists($key, $data))
        {
            $value = $data[$key];
        }
        else
        {
            if ($debug)
            {
                error_log("Key not found: " . $key);
                error_log("Getting value from SQL Server data --(**".$key."**)--: " . serialize($data));
            }
        }

        return $value;
    }

    public function valueFromPHPData($data) 
    {
        if ($this->phpKey)
        {
            
        }
        return $data[$this->phpKey];
    }

    ////////////////////////////////////////////////////////////////////
    // Form Value
    // --------
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function callCustomInputFunctionForUserItemOptions($user, $item, $options = null)
    {
        $debug = true;
        $value = ''; // $this->defaultValue;

        if ($item)
        {
            $value = $this->valueFromDatabase($item);
        }

        $currentValue = $value;
        
        if ($debug)
        {
            error_log("Got `customInputFunction`"); // ".$this->customInputFunction);
        }

        $customInputFunction        = $this->customInputFunction;
        $customInputFunctionObject  = $this->customInputFunctionObject;
        $customInputFunctionClass   = $this->customInputFunctionClass;
        $customInputFunctionOptions = $this->customInputFunctionOptions; // $this->customInputFunctionOptions;

        if ($customInputFunctionObject)
        {
            if ($debug)
            {
                error_log("Got `customInputFunctionObject`");
            }

            if (!method_exists($customInputFunctionObject, $customInputFunction))
            {
                if ($debug)
                {
                    error_log("Does NOT respond. Will call: `customInputFunctionObject`");
                }
                throw new Exception('$customInputFunctionObject - does not respond to $customInputFunction');
            }

            if ($debug)
            {
                error_log("Does respond. Will call: `customInputFunctionObject` of class: ".get_class($customInputFunctionObject));
            }

            $reflectionData = new ReflectionMethod($customInputFunctionObject, $customInputFunction);

            switch ($reflectionData->getNumberOfParameters())
            {
                case 5:
                    return $customInputFunctionObject->$customInputFunction(
                        $this,
                        $user,
                        $item,
                        $currentValue, // currentValue
                        $customInputFunctionOptions);
                case 6:
                    return $customInputFunctionObject->$customInputFunction(
                        $user,
                        $this->dataAccessor,
                        $options["identifier"],
                        $this->phpKey,
                        $currentValue,
                        $customInputFunctionOptions
                    );
                    break;
                case 7:
                    return $customInputFunctionObject->$customInputFunction(
                        $this, // $columnMapping,
                        $user,
                        $this->dataAccessor,
                        $objectID,
                        $foreignColumnName,
                        $foreignColumnValue,
                        $options);
                default:
                    throw new Exception("Invalid function for `customInputFunction` protocol");
            }

            

        }
        else if ($customInputFunctionClass)
        {
            if ($debug)
            {
                error_log("Got `customInputFunctionClass`");
            }

            $customInputFunctionScope = $this->customInputFunctionScope;

            switch ($customInputFunctionScope)
            {
                case "class":
                    if ($debug)
                    {
                        error_log("Will call static function on class...`");
                    }

                    return $customInputFunctionClass::$customInputFunction(
                        $user,
                        $this->dataAccessor,
                        $options["identifier"],
                        $this->phpKey,
                        $value,
                        $customInputFunctionOptions
                    );
                case "object":
                case "instance":
                case null:
                default:
                    if ($debug)
                    {
                        error_log("Will make new object from `customInputFunctionClass`");
                    }

                    $object = new $customInputFunctionClass();

                    if (!method_exists($object, $customInputFunction))
                    {
                        throw new Exception('$object - does not respond to $customInputFunction');
                    }

                    return $object->$customInputFunction(
                        $user,
                        $this->dataAccessor,
                        $options["identifier"],
                        $this->phpKey,
                        $value,
                        $customInputFunctionOptions
                    );
            }

        }
        else if (is_callable($customInputFunction))
        {
            if ($debug)
            {
                error_log("Plain `customInputFunctionClass`");
            }

            $argCount = getFunctionArgumentCount($customInputFunction);

            if ($debug)
            {

            }

            switch ($argCount)
            {
                case 5:
                    $toReturn = $customInputFunction(
                        $this,
                        $user,
                        $item,
                        $value,
                        $options);
                        break;
                case 6:
                    $toReturn = $customInputFunction(
                        $user,
                        $this->dataAccessor,
                        $options["identifier"],
                        $this->phpKey,
                        $value,
                        $customInputFunctionOptions);
                        break;
                case 7:
                    $toReturn = $customInputFunction(
                        $this,
                        $user,
                        $this->dataAccessor,
                        $options["identifier"],
                        $this->phpKey,
                        $value,
                        $customInputFunctionOptions);
                    break;
                default:
                    throw new Exception("INVALID customInputFunction on GTKColumnMapping - ".get_class($this->dataSource)." - ".$this->phpKey." - Arg Count: ".$argCount);
            }

            return $toReturn;
        }
        else
        {
            if ($debug)
            {
                error_log("Custom input function is not callable.");
            }
            throw new Exception("Custom input function is not callable.");
        }
    }

    public function htmlInputForUserItem($user, $item, array $options = null)
    {  
        $debug = false;
        $value = ''; // $this->defaultValue;

        if ($item)
        {
            if ($debug)
            {
                error_log("Got item...will getSQLServerData - ".$this->phpKey);
            }
            $value = $this->valueFromDatabase($item);
        }

        $inputType = $this->formInputType;

        if ($options)
        {
            if (array_key_exists('type', $options))
            {
                $inputType = $options['type'];
            }
        }

        $inputType = $inputType ?: "text";
        $phpKey    = $this->phpKey;

        $inputIdentifier = "";

        if ((isset($options["identifier"])) && isset($options["dataSourceName"]))
        {
            $inputIdentifier = $options["dataSourceName"].'-'.$options["identifier"].'-'.$this->phpKey;
        }

        if ($this->customInputFunction)
        {
            return $this->callCustomInputFunctionForUserItemOptions($user, $item, $options);
        }
        else
        {
            $formInputType = $this->formInputType();

            return $this->generateFormElementForUser(
                $user,
                $formInputType,
                $inputIdentifier,
                $phpKey,
                $value);
        }


    }

    public function generateFormElementForUser(
        $user,
        $inputType, 
        $inputIdentifier, 
        $phpKey, 
        $value
    ){
        $debug = false;

        if ($debug)
        {
            error_log("Generate Form Element");
        }
        // Escape user-generated values for use in HTML attributes
        $inputIdentifier = htmlspecialchars($inputIdentifier);
        $phpKey = htmlspecialchars($phpKey);
        $value = htmlspecialchars($value);
    
        $valueAttribute = ' value="' . $value . '" ';
        $idAttribute    = ' id="' . $inputIdentifier . '" ';
        $nameAttribute  = ' name="' . $phpKey . '" ';
    
        switch ($inputType) 
        {
            case "select":
                $element = "<select{$idAttribute}{$nameAttribute}>";
    
                if ($debug)
                 {
                    error_log("SELECT VALUE: " . $value);
                }
    
                foreach ($this->possibleValuesForUser($user) as $optionName => $option) {
                    $element .= '<option value="' . htmlspecialchars($optionName) . '"';
    
                    if ($value && $value === $optionName) {
                        $element .= " selected";
                    }
    
                    $label = $option["label"] ?? $optionName;
    
                    $element .= '>' . htmlspecialchars($label) . '</option>';
                }
    
                $element .= "</select>";
                return $element;
    
            case "textarea":
                $sizeAttribute = ' rows="6" cols="35" ';
                return "<textarea{$idAttribute}{$nameAttribute}{$sizeAttribute}>$value</textarea>";
    
            case "hidden":
                return "<input type=\"hidden\"{$valueAttribute}{$idAttribute}{$nameAttribute}>";
    
            case "submit":
            case "reset":
                return "<input type=\"$inputType\"{$valueAttribute}{$idAttribute}{$nameAttribute}>";
            
            case "button":
                return "<button type=\"button\"{$idAttribute}{$nameAttribute}>$value</button>";
            case "text":
            case "password":
            case "email":
            case "number":
            case "checkbox":
            case "radio":
            case "file":
            case "date":
            case "time":
            case "datetime-local":
            case "color":
            case "range":
            default:
                return "<input type=\"$inputType\"{$valueAttribute}{$idAttribute}{$nameAttribute}>";
        }
    }
    

    // Mark: - Check if exits

    public function doesColumnExist($db, $tableName)
    {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        switch ($driver)
        {
            case "sqlite":
                return $this->doesColumnExistSQLite($db, $tableName);
            case "sqlsrv":
            default:
                return $this->doesColumnExistSqlServer($db, $tableName);
                
        }
    }

    public function doesColumnExistSqlServer($db, $tableName)
    {
        $tableName = $tableName;

        $columnName = $this->getSqlColumnName();

        $sql = "SELECT * FROM information_schema.columns 
                WHERE 
                table_name = '{$tableName}' 
                AND 
                column_name = '{$columnName}'";

        $stmt = $db->prepare($sql);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return count($result) > 0;
    }

    function doesColumnExistSQLite($db, $tableName)
    {
        $debug = false;


        $sql = "PRAGMA table_info(".$tableName.")";

        if ($debug)
        {
            error_log("SQL: {$sql}");
        }   

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $columnName = $this->getSqlColumnName();

        if ($debug)
        {
            error_log("`doesColumnExistSQLite` — Looking for... $columnName");
        }
        

        foreach ($columns as $column) 
        {
            $isMatch = ($column['name'] == $columnName);
            
            if ($debug)
            {
                error_log("`doesColumnExistSQLite` ($isMatch) checking on ".$column["name"]);
            }

            if ($isMatch)
            {
                return true;
            }
        }
        return false;
    }


    // Mark: - Create Columns

    
    public function getColumnTypeForPDO($pdoObject)
    {
        $debug = false;

        $driver = $pdoObject->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($debug)
        {
            error_log("Column type for ".$this->phpKey);
            error_log("Got driver: ".$driver);
        }

        if ($this->isAutoIncrement())
        {
            if ($debug)
            {
                error_log("Returning `autoincrement` type.");
            }

            switch ($driver)
            {
                case "sqlite":
                    return "integer";
                case "sqlsrv":
                default:
                    return "int";
            }
        }

        if (!$this->columnType)
        {
            switch ($driver)
            {
                case "sqlite":
                    return '';
                default:
                    return "VARCHAR";
            }
        }

        $sqlColumnTypes = [
            // Integer Types
            'TINYINT',    // A very small integer
            'SMALLINT',   // A small integer
            'MEDIUMINT',  // A medium-sized integer
            'INT',        // A standard integer
            'BIGINT',     // A large integer
        
            // Decimal Types
            'DECIMAL',    // A fixed-point number
            'NUMERIC',    // An exact numeric value with a fixed precision and scale
            'FLOAT',      // A floating-point number
            'DOUBLE',     // A double precision floating-point number
            'REAL',       // A synonym for DOUBLE (except in SQL Server where it's a synonym for FLOAT)
        
            // Boolean Type
            'BOOLEAN',    // A boolean value (true or false)
        
            // Date and Time Types
            'DATE',       // A date value
            'DATETIME',   // A date and time combination
            'TIMESTAMP',  // A timestamp value
            'TIME',       // A time value
            'YEAR',       // A year value
        
            // String Types
            'CHAR',       // A fixed-length non-binary string (up to 255 characters)
            'VARCHAR',    // A variable-length non-binary string
            'TINYTEXT',   // A very small non-binary string
            'TEXT',       // A standard non-binary string
            'MEDIUMTEXT', // A medium-length non-binary string
            'LONGTEXT',   // A large non-binary string
        
            // Binary String Types
            'BINARY',     // A fixed-length binary string
            'VARBINARY',  // A variable-length binary string
            'TINYBLOB',   // A very small binary string
            'BLOB',       // A binary string
            'MEDIUMBLOB', // A medium-length binary string
            'LONGBLOB',   // A large binary string
        
            // Enumerated Types
            'ENUM',       // An enumeration
            'SET',        // A set of enumeration values
        
            // JSON Type
            'JSON',       // A JSON-formatted string
        
            // UUID Type
            'UUID'        // A universally unique identifier
        ];

        if (in_array(strtoupper($this->columnType), $sqlColumnTypes))
        {
            return $this->columnType;
        }

        throw new Exception("INVALID COLUMN TYPE for $this->phpKey");
    }

    public function isAutoIncrement()
    {
        $isAutoIncrement = parent::isAutoIncrement();
        
        if ($isAutoIncrement)
        {
            return true;
        }

        if ($this->isPrimaryKey())
        {
            switch (strtoupper($this->columnType))
            {
                case 'INT':
                case 'INTEGER':
                    return true;
                case 'null': // Code needs to specify the type for primary key to be auto-increment
                default:
                    return false;
            }
        }

        return $isAutoIncrement;
    }

    public function autoIncrementSyntaxForPDO($pdo)
    {
        if (!$this->isPrimaryKey() && !$this->isAutoIncrement)
        {
            return '';
        }

        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $columnDef = null;

        switch ($driver)
        {
            case 'mysql':
                $columnDef .= " AUTO_INCREMENT ";
                break;
            case 'pgsql':
                throw new Exception("MUST ADAPT TO `SERIAL` TYPE FOR PostgresSQL");
                break;
            case 'sqlsrv':
                $columnDef .= " IDENTITY(1,1) ";
                break;
            case 'sqlite':
                $columnDef .= " AUTOINCREMENT ";
                break;
        }

        if (!$columnDef)
        {
            throw new Exception("No AUTO-INCREMENT syntax for PDO - $driver");
        }

        return $columnDef;

        
    }

    public function getPrimaryKeySyntaxForPDO($pdo)
    {
        if ($this->isPrimaryKey())
        {
            return " PRIMARY KEY ";
        }
        else
        {
            return ' ';
        }
    }



    public function getColumnDef($pdoObject)
    {
        $columnDef = "";

        $columnName = $this->getSqlColumnName();
        $columnType = $this->columnType   ?? 'nvarchar';
        $columnSize = $this->columnSize   ?? 255;
        $columnDef  = "";

        $driver = $pdoObject->getAttribute(PDO::ATTR_DRIVER_NAME);

        switch ($driver) 
        {
            case 'mysql':
                $columnDef .= "`$columnName` $columnType";
                if ($columnSize) {
                    $columnDef .= "($columnSize)";
                }
                break;
            case 'pgsql':
            case 'sqlite':
                $columnDef .= "$columnName $columnType";
                if ($columnSize) {
                    $columnDef .= "($columnSize)";
                }
                break;
            case 'sqlsrv':
                $columnDef .= "[$columnName] $columnType";
                if ($columnSize) {
                    $columnDef .= "($columnSize)";
                }
                break;
            default:
                throw new Exception("Driver not supported");
        }
    
        if ($this->isPrimaryKey())
        {
            $columnDef .= " PRIMARY KEY";
        } 

        if ($this->isAutoIncrement)
        {
            switch ($driver)
            {
                case 'mysql':
                    $columnDef .= " AUTO_INCREMENT";
                    break;
                case 'pgsql':
                    $columnDef = str_replace("$columnType", "SERIAL", $columnDef);
                    break;
                case 'sqlsrv':
                    $columnDef .= " IDENTITY(1,1)";
                    break;
                case 'sqlite':
                    $columnDef .= " AUTOINCREMENT";
                    break;
            }
        }
    
        return $columnDef;
    }


    public function getColumnSizeStatementForPDO($pdo)
    {
        $columnType = $this->getColumnTypeForPDO($pdo);

        $shouldUseColumnSize = in_array(strtoupper($columnType), ['NVARCHAR', 'VARCHAR', 'CHAR']);
        
        if ($shouldUseColumnSize || $this->columnSize) 
        {
            $columnSize = $this->columnSize ?? "255";
            return "($columnSize)";
        }
        else
        {
            return '';
        }
    }

    public function getCreateSQLForPDO($pdo = null)
    {
        return $this->getSqlColumnName()." ".$this->getColumnTypeForPDO($pdo).$this->getColumnSizeStatementForPDO($pdo)." ".$this->getPrimaryKeySyntaxForPDO($pdo)." ".$this->autoIncrementSyntaxForPDO($pdo);
    }

    public function getCreateSQL($driverName = null)
    {
        $columnName   = $this->getSqlColumnName();
        $allowNulls   = $this->allowNulls   ?? true;
        $defaultValue = $this->defaultValue ?? null;
        $constraint   = null;



        // Check if the column should allow nulls
        $sqlAllowNulls = ($allowNulls) ? "NULL" : "NOT NULL";
        $sqlColumnType = '';

        switch ($driverName)
        {
            case 'sqlite':
                // Can ignore—SQL does not require types
                break;
            case 'sqlsrv':
            default:
                if ($this->columnType)
                {
                    $sqlColumnType = $this->columnType;
                }
                else
                {
                    $sqlColumnType = "nvarchar";
                }
                // Construct the main ALTER TABLE statement
                break;
        }
        
        $sql = "{$columnName} {$sqlColumnType} {$sqlAllowNulls}";

        if ($constraint)
        {
            // 
        }

        // If there's a default value, append it to the SQL statement
        if ($defaultValue) {
            $sql .= " DEFAULT '{$defaultValue}'";
        }

        return $sql;
    }


    // Mark: - Add

    public function addColumnIfNotExists($db, $tableName)
    {
        $driverName = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        $columnName = $this->getSqlColumnName();

        switch ($driverName)
        {
            case 'sqlite':
                $this->addColumnIfNotExistsSQLite($db, $tableName, $columnName);
                break;
            
            case 'sqlsrv':
                $this->addColumnIfNotExistsSQLServer($db, $tableName);
                break;
            
            case 'mysql':
            case 'pgsql':
            case 'oci': // Oracle
            default:
                error_log("`addColumnIfNotExists` - Connected to a database with unsupported driver: " . $driverName);
                die();
        }


    }

    function addColumnIfNotExistsSQLServer($db, $tableName)
    {
        $columnName = $this->getSqlColumnName();

        if (!$this->doesColumnExistSqlServer($db, $tableName))
        {
            $columnType   = $this->columnType   ?? 'nvarchar';
            $columnSize   = $this->columnSize   ?? 255;
            $allowNulls   = $this->allowNulls   ?? true;
            $defaultValue = $this->defaultValue ?? null;
            $constraint   = null;
    
            // Set data type with size if specified
            $sqlColumnType = ($columnSize) ? "{$columnType}({$columnSize})" : $columnType;
    
            // Check if the: column should allow nulls
            $sqlAllowNulls = ($allowNulls) ? "NULL" : "NOT NULL";
    
            // Construct the main ALTER TABLE statement
            $sql = "ALTER TABLE {$tableName} ADD {$columnName} {$sqlColumnType} {$sqlAllowNulls}";

            if ($constraint)
            {
                // 
            }
    
            // If there's a default value, append it to the SQL statement
            if ($defaultValue) {
                $sql .= " DEFAULT '{$defaultValue}'";
            }
    
            $stmt = $db->prepare($sql);
            $stmt->execute();
        }
    }


    function addColumnIfNotExistsSQLite($db, $tableName)
    {
        $columnExists = $this->doesColumnExistSQLite($db, $tableName);

        if (!$columnExists)
        {
            $columnName = $this->getSqlColumnName();
            $sql = "ALTER TABLE ".$tableName." ADD COLUMN $columnName"; //  $columnType";
            $db->exec($sql);
        }
    }

    public function getMaxLength($options = null)
    {
        $sqlColumn = $this->getSqlColumnName();

        $sqlV1 = "";
        $sqlV1 .= "SELECT CHARACTER_MAXIMUM_LENGTH";
        $sqlV1 .= " FROM INFORMATION_SCHEMA.COLUMNS";
        $sqlV1 .= " WHERE TABLE_NAME = '".$this->dataSource->tableName()."'";
        $sqlV1 .= " AND COLUMN_NAME = '".$sqlColumn."'";

        /*
        SELECT max_length
        FROM sys.columns
        WHERE object_id = OBJECT_ID('yourTable')
        AND name = 'yourColumn';
        */

        try
        {
            $stmt = $this->dataSource->getPDO()->prepare($sqlV1);
            $stmt->execute(); 
        }
        catch (Exception $e)
        {
            $exCode    = $e->getCode();
            $exMessage = $e->getMessage();

            $errorMessage = "`getMaxLengthForColumnMapping` Got exception with code (".$exCode.") - ".$exMessage;
            $errorMessage .= "\n\nSQL: $sqlV1";

            gtk_log($errorMessage);

            throw $e;
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $columnLength = $result['CHARACTER_MAXIMUM_LENGTH'];

        return $columnLength;
    }

    public function bindValueToStatementForItem($statement, $item)
    {
        $debug = false;

        if ($this->isAutoIncrement())
        {
            if ($debug)
            {
                gtk_log("`assignValueToStatementForColumnMapping`:".$this->phpKey." isAutoIncrement — skipping.");
            }
            return;
        }
        
        if ($this->isPrimaryKey())
        {
            if ($debug)
            {
                gtk_log("`assignValueToStatementForColumnMapping`:".$this->phpKey." isPrimaryKey — skipping.");
            }
            return;
        }

        if ($debug)
        {
            gtk_log("`assignValueToStatementForColumnMapping`:".$this->phpKey." binding value: ".$this->valueFromDatabase($item));
        }

        $statement->bindValue(":".$this->phpKey, $this->valueFromDatabase($item));
    }


}
