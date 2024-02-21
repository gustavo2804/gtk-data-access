<?php

class ColumnView implements Serializable
{
    public $formLabel;
    public $_valueFromDatabase;
    public $phpKey;
    public $linkTo;
    public $options;

    public function isPrimaryKey()
    {
        return false;
    }

    public function serialize() 
    {
        return serialize([
          'formLabel'         => $this->getFormLabel(), 
          'valueFromDatabase' => 'Closure',
        ]);
    }
    
    public function unserialize($data) 
    {
        $data = unserialize($data);
        $this->getFormLabel = $data['string'];
    }


    public function __construct($formLabel, $valueFromDatabase, $options = null)
    {
        $this->formLabel        = $formLabel;
        $this->_valueFromDatabase = $valueFromDatabase;
        $this->options           = $options;    
        $this->phpKey = "column_view".toSnakeCase($formLabel);

        if (isset($options))
        {
            if (isset($options["linkTo"]))
            {
                $this->linkTo = $options["linkTo"];
            }
        }
    }

    public function hideOnListsForUser($user)
    {
        return false;
    }

    public function formLabel($item = null)
    {
        return $this->getFormLabel;
    }

    public function valueFromDatabase($item)
    {
        $method =  $this->_valueFromDatabase;

        return $method($item);
    }
}
