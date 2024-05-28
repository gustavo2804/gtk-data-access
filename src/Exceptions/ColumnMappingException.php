<?php

class ColumnMappingException extends Exception
{
    private $key;
    private $className;
    private $methodName;

    public function __construct($key, $className, $methodName, $message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Error in {$className}::{$methodName} for key '{$key}': " . $message;
        parent::__construct($message, $code, $previous);
        $this->key = $key;
        $this->className = $className;
        $this->methodName = $methodName;
    }

    public function getColumnName()
    {
        return $this->key;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getMethodName()
    {
        return $this->methodName;
    }
}


class AccumulatedColumnMappingException extends Exception
{
    private $errors;

    public function __construct($errors = [], $code = 0, Throwable $previous = null)
    {
        $this->errors = $errors;
        $message = $this->generateMessage();
        parent::__construct($message, $code, $previous);
    }

    private function generateMessage()
    {
        $keys = array_map(function ($exception) {
            return $exception->getColumnName();
        }, $this->errors);

        return "Column mapping errors detected for keys: " . implode(', ', $keys);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getErrorMessages()
    {
        return implode("\n", array_map(function ($exception) {
            return $exception->getMessage();
        }, $this->errors));
    }
}
