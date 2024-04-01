<?php

class GTKColumnBase 
{
    public $dataSource;
    public $phpKey;
    public $formLabel;
    public $dataAccessor;

    public $customInputFunction; //  objectID, columnName columnValue (?data access type??)
    public $customInputFunctionObject;
    public $customInputFunctionClass;
    public $customInputFunctionScope;
    public $customInputFunctionOptions;

    public $formNewProcessFunction;
    public $formEditProcessFunction;

    // Options
    public $linkTo;
    public $type;
    public $formInputType;

    public $process;

    public $assignTo;
    public $required;
    public $display;

    public $_isAutoIncrement;
    public $_hideOnLists;
    public $_hideOnShow;
    public $_hideOnSearch;
    public $_hideOnForms;
    public $_removeOnForms;
    public $_hideOnInsert;
    public $_hideOnUpdate;
    public $_isNullable;
    public $_isPrimaryKey;
    public $_isUnique;
    public $_isInvalid;
    public $_groups;
    public $_processOnInsert;
    public $_processOnAll;
    public $isSearchable;
    public $_onlyDisplayOnForms;
    public $_possibleValues;
    public $_formInputType;
    public $transformValueOnLists;
    public $hideOnListsForUserFunction;

    public function isInsertable()
    {
        return false;
    }

    public function getLabel($dataAccess = null)
    {
        if ($this->formLabel)
        {
            return $this->getFormLabel($dataAccess);
        }

        return $this->phpKey;
    }

    public function doesItemContainOurKey($item)
    {
        return isset($item[$this->phpKey]);
    }

    public function __construct($dataSource, $phpKey, $options = null)
    {
        $this->dataSource = $dataSource;
        $this->phpKey     = trim($phpKey);
        
        if (isset($options['formLabel']))
        {
            $this->formLabel = $options['formLabel'];
        }
        else
        {
            $array = explode("_", $phpKey);
            
            $new = [];

            foreach ($array as $value)
            {
                if ($value == "id")
                {
                    $new[] = "ID";
                }
                else
                {
                    $new[] = ucfirst($value);
                }
                
            }

            $this->formLabel = implode(" ", $new);
        }
        
        if ($options)
        {
            $this->type                       = arrayValueIfExists("type",                         $options);
            $this->_hideOnLists               = arrayValueIfExists("hideOnLists",                  $options);
            $this->_hideOnShow                = arrayValueIfExists("hideOnShow",                   $options);
            $this->_hideOnSearch              = arrayValueIfExists("hideOnSearch",                 $options);
            $this->_hideOnForms               = arrayValueIfExists("hideOnForms",                  $options);
            $this->_hideOnInsert              = arrayValueIfExists("hideOnInsert",                 $options);
            $this->_hideOnUpdate              = arrayValueIfExists("hideOnUpdate",                 $options);
            $this->type                       = arrayValueIfExists("type",                         $options);
            $this->process                    = arrayValueIfExists("process",                      $options);
            $this->assignTo                   = arrayValueIfExists("assignTo",                     $options);
            $this->required                   = arrayValueIfExists("required",                     $options);
            $this->display                    = arrayValueIfExists("display",                      $options);
            $this->_isNullable                = arrayValueIfExists("isNullable",                   $options);
            $this->_isPrimaryKey              = arrayValueIfExists("isPrimaryKey",                 $options);
            $this->_isUnique                  = arrayValueIfExists("isUnique",                     $options);
            $this->_isAutoIncrement           = arrayValueIfExists("isAutoIncrement",              $options);
            $this->_isInvalid                 = arrayValueIfExists("isInvalid",                    $options);
            $this->customInputFunction        = arrayValueIfExists('customInputFunction',          $options);
            $this->customInputFunctionClass   = arrayValueIfExists('customInputFunctionClass',     $options);
            $this->customInputFunctionScope   = arrayValueIfExists('customInputFunctionScope',     $options);
            $this->customInputFunctionObject  = arrayValueIfExists('customInputFunctionObject',    $options);
            $this->customInputFunctionOptions = arrayValueIfExists('customInputFunctionOptions',   $options);
            $this->formNewProcessFunction     = arrayValueIfExists('formNewProcessFunction',       $options);
            $this->formEditProcessFunction    = arrayValueIfExists('formEditProcessFunction',      $options);
            $this->_processOnInsert           = arrayValueIfExists('processOnInsert',              $options);
            $this->_processOnAll              = arrayValueIfExists('processOnAll',                 $options);
            $this->_onlyDisplayOnForms        = arrayValueIfExists('onlyDisplayOnForms',           $options);
            $this->_removeOnForms             = arrayValueIfExists('removeOnForms',                $options);
            $this->_possibleValues            = arrayValueIfExists('possibleValues',               $options);
            $this->_formInputType             = arrayValueIfExists('formInputType',                $options);
            $this->transformValueOnLists      = arrayValueIfExists('transformValueOnLists',        $options);
            $this->hideOnListsForUserFunction = arrayValueIfExists('hideOnListsForUserFunction',   $options);
            $this->linkTo                     = arrayValueIfExists('linkTo',                       $options);


            $this->_groups = arrayValueIfExists("groups", $options);   
        }

        if ($options && isset($options["isSearchable"]))
        {
            $this->isSearchable = $options["isSearchable"];
        }
        else 
        {
            $this->isSearchable = true;
        }
    }

    public function isPartOfGroupAndUser($groupName, $user)
    {
        if ($groupName === "all")
        {
            return true;
        }

        if ($groupName === "searchable")
        {
            return $this->isSearchable;
        }   

        if ($groupName === "lists")
        {
            return $this->hideOnListsForUser($user);
        }

        if ($groupName === "forms")
        {
            return $this->hideOnFormsForUser($user);
        }

        return in_array($groupName, $this->_groups);
    }

    public function setDataAccessor($dataAccessor)
    {
        $this->dataAccessor = $dataAccessor;
    }


    public function hideOnListsForUser($user)
    {
        /*
        if ($this->phpKey === "password")
        {
            error_log("Hiding on lists: " . $this->_hideOnLists);
        }
        */
        
        return isset($this->_hideOnLists) ? $this->_hideOnLists : false;
    }

    public function isNullable()
    {
        return isset($this->_isNullable) ? $this->_isNullable : false;
    }

    public function isPrimaryKey()
    {
        return isset($this->_isPrimaryKey) ? $this->_isPrimaryKey : false;
    }

    public function isUnique()
    {
        if ($this->isPrimaryKey())
        {
            return true;
        }

        if ($this->isAutoIncrement())
        {
            return true;
        }
        
        return isset($this->_isUnique) ? $this->_isUnique : false;
    }

    public function isAutoIncrement()
    {
        $debug = false;

        if (isset($this->_isAutoIncrement))
        {
            if ($debug)
            {
                error_log("Auto Increment: `$this->_isAutoIncrement");
            }
            return $this->_isAutoIncrement;
        }
        else
        {
            return false;
        } 
    }


    public function isVirtual()
    {
        return false;
    }

    // MARK: Hiders
    
    public function hideOnShow()
    {
        return isset($this->_hideOnShow) ? $this->_hideOnShow : false;
    }

    public function hideOnSearch()
    {
        return isset($this->_hideOnSearch) ? $this->_hideOnSearch : false;
    }



    public function hideOnFormsForUser($user)
    {
        return isset($this->_hideOnForms) ? $this->_hideOnForms : false;
    }

    public function removeOnFormsForUser($user)
    {
        return isset($this->_removeOnForms) ? $this->_removeOnForms : false;
    }

    public function hideOnInsertForUser($user)
    {
        return isset($this->_hideOnInsert) ? $this->_hideOnInsert : false;
    }

    public function hideOnUpdateForUser($user)
    {
        return isset($this->_hideOnUpdate) ? $this->_hideOnUpdate : false;
    }

    public function isRequiredForUser($user)
    {
        return isset($this->required) ? $this->required : false;
    }

    
    public function processOnInsertForUser($user)
    {
        return isset($this->_processOnInsert) ? $this->_processOnInsert : false;
    }

    public function processOnAllForUser($user)
    {
        return isset($this->_processOnAll) ? $this->_processOnAll : false;
    }

    public function onlyDisplayOnFormsForUser($user)
    {
        return isset($this->_onlyDisplayOnForms) ? $this->_onlyDisplayOnForms : false;
    }

    public function possibleValuesForUser($user)
    {
        if (!isTruthy($this->_possibleValues))
        {
            return null;
        }
        
        return $this->_possibleValues;
    }

    public function getFormLabel($dataSource = null)
    {
        if (!$dataSource)
        {
            $dataSource = $this->dataSource;
        }

        $currentClass = get_class($dataSource);

        while ($currentClass) 
        {
            $translationKey = $currentClass . "/" . $this->phpKey;
            $translation = Glang::get($translationKey, ["allowReturnOfNull" => true]);
    
            if ($translation) 
            {
                return $translation;
            }
    
            $currentClass = get_parent_class($currentClass);
        }
    
        return snakeToSpaceCase($this->phpKey);
    }

    public function formInputType()
    {
        return isset($this->_formInputType) ? $this->_formInputType : false;
    }



}
