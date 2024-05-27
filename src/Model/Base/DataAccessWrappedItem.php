<?php
class DataAccessWrappedItem

{
    public $wrappedItem;
    public $dataAccess;

    public function __construct($dataAccess, $wrappedItem)
    {
        $this->wrappedItem = $wrappedItem;
        $this->dataAccess = $dataAccess;
    }

    public function __call($methodName, $arguments)
    {
        if (method_exists($this->dataAccess, $methodName)) 
        {
            // If the method exists on dataAccess, call it with wrappedItem as an argument
            return $this->dataAccess->$methodName($this->wrappedItem, ...$arguments);
        } 
        else if (property_exists($this->dataAccess, $methodName)) 
        {
            // Accessing the property directly
            $property = $this->dataAccess->$methodName;
            // If the property is callable (a closure), call it with wrappedItem
            if (is_callable($property)) 
            {
                return $property($this->wrappedItem, ...$arguments);
            }
            else
            {
                return $property;
            }
        } 
        else
        { 
            // Fallback to calling valueForKey method
            return $this->dataAccess->valueForKey($methodName, $this->wrappedItem, ...$arguments);
        }
    }
}
