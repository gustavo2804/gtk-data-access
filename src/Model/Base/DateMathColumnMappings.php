<?php


class VirtualColumn
{
    public $key;
    public $label;
    public $requiredColumns;

    public function __construct($key, $label, $requiredColumns)
    {
        $this->key             = $key;
        $this->label           = $label;
        $this->requiredColumns = $requiredColumns;
    }

    public function getKey()
    {
        return $this->key;
    }
    public function getLabel()
    {
        return $this->label;
    }
    public function getRequiredColumns()
    {
        return $this->requiredColumns;
    }
    public function valueForItem($item)
    {
        throw new Exception("NOT IMPLEMENTED : TODO");
    }
}

class ClosureMapping extends VirtualColumn
{
    public $closureForItem;

    public function __construct(
        $key, 
        $label, 
        $requiredColumns, 
        $closureForItem)
    {
        parent::__construct($key, $label, $requiredColumns);
        $this->closureForItem  = $closureForItem;
    }
    public function valueForItem($item)
    {
        $closure =  $this->closureForItem;
        return $closure($item);
    }
}



class NestableDateMath extends VirtualColumn
{
    public $startItem;
    public $endItem;
    
    public function __construct($key, $label, $startValue, $endValue)
    {
        $requiredColumns = [];

        foreach ([ $startValue, $endValue ] as $value)
        {
            if (is_string($value))
            {
                $requiredColumns[] = $value;
            }
            else if (method_exists($value, "getRequiredColumns"))
            {
                foreach ($value->getRequiredColumns() as $requiredColumn)
                {
                    $requiredColumns[] = $requiredColumn;
                }
            }
        }

        parent::__construct($key, $label, $requiredColumns);

        $this->startItem             = $startValue;
        $this->endItem               = $endValue;
    }


    public function valueForItem($item)
    {
        $startValue = null;

        if (method_exists($this->startItem, "valueForItem"))
        {
            $startValue = $this->startItem->valueForItem($item);
        }
        else
        {
            $startValue = $this->startItem;
        }

        $endValue = null;

        if (method_exists($this->endItem, "valueForItem"))
        {
            $endValue = $this->endItem->valueForItem($item);
        }
        else
        {
            $endValue = $this->endItem;
        }


        $startDateTime = $startValue instanceof DateTime ? $startValue : new DateTime($startValue);
        $endDateTime   = $endValue   instanceof DateTime ? $endValue   : new DateTime($endValue);
        
        $diffInSeconds = $endDateTime->getTimestamp() - $startDateTime->getTimestamp();
        
        $twentyFourHoursInSeconds = 24 * 60 * 60;

        $result = ceil($diffInSeconds / $twentyFourHoursInSeconds);
        
        return $result;

    }
}

class SeperatedDateMapping extends VirtualColumn
{
    public $dateColumn;
    public $hourColumn;
    public $dataAccessor;

    public function __construct($key, $label, $dateColumn, $hourColumn, $dataAccessor)
    {
        parent::__construct($key, $label, [
            $dateColumn,
            $hourColumn,
        ]);

        
        $this->dateColumn            = $dateColumn;
        $this->hourColumn            = $hourColumn;
        $this->dataAccessor          = $dataAccessor;
    }
    public function getLabel()
    {
        return $this->label;
    }
    public function valueForItem($item)
    {
        $rawDateValue = $this->dataAccessor->valueForKey($this->dateColumn, $item);
        $hourValue    = $this->dataAccessor->valueForKey($this->hourColumn, $item);
        
        if (is_string($rawDateValue))
        {
            $dateValue = explode(' ', $rawDateValue)[0];
        }
        else if ($rawDateValue instanceof DateTime)
        {
            $dateValue = $rawDateValue->format('Y-m-d');
        }
        else if (!$rawDateValue)
        {
            return null;
        }        
        
        $dateString   = "{$dateValue} {$hourValue}";

        $date   = new DateTime($dateString);
        
        return $date;
    }
}

class SeperatedDateMathMapping extends VirtualColumn
{
    public $startDateColumn;
    public $startHourColumn;
    public $endDateColumn;
    public $endHourColumn;
    public $startDateDataAccessor;
    public $endDateDataAccessor;

    public function __construct($key, $label, $startDateColumn, $startHourColumn, $endDateColumn, $endHourColumn, $startDateDataAccessor, $endDateDataAccessor = null)
    {

        parent::__construct($key, $label, [
            $startDateColumn,
            $startHourColumn,
            $endDateColumn,
            $endHourColumn,
        ]);

        
        $this->startDateColumn       = $startDateColumn;
        $this->startHourColumn       = $startHourColumn;
        $this->endDateColumn         = $endDateColumn;
        $this->endHourColumn         = $endHourColumn;
        $this->startDateDataAccessor = $startDateDataAccessor;
        $this->startDateDataAccessor = $endDateDataAccessor ?? $startDateDataAccessor;
    }

    public function valueForItem($item)
    {
        $startDateValue = $this->startDateDataAccessor->valueForKey($this->startDateColumn, $item);
        $startHourValue = $this->startDateDataAccessor->valueForKey($this->startHourColumn, $item);

        $endDateValue   = $this->endDateDataAccessor->valueForKey($this->endDateColumn, $item);
        $endHourValue   = $this->endDateDataAccessor->valueForKey($this->endHourColumn, $item);

        $startDateValue = explode(' ', $startDateValue)[0];
        $endDateValue   = explode(' ', $endDateValue)[0];

        $startDateTimeString = "{$startDateValue} {$startHourValue}";
        $endDateTimeString   = "{$endDateValue} {$endHourValue}";

        $startDate = new DateTime($startDateTimeString);
        $endDate   = new DateTime($endDateTimeString);
        
        $diffInSeconds = $endDate->getTimestamp() - $startDate->getTimestamp();
        
        $twentyFourHoursInSeconds = 24 * 60 * 60;

        $result = ceil($diffInSeconds / $twentyFourHoursInSeconds);
        
        return $result;
    }
}

class DateMathMapping extends VirtualColumn
{
    public $startDateColumn;
    public $endDateColumn;
    public $startDateDataAccessor;
    public $endDateDataAccessor;

    public function __construct($key, $label, $startDateColumn, $endDateColumn, $startDateDataAccessor, $endDateDataAccessor = null)
    {
        $this->key = $key;

        parent::__construct($key, $label, [
            $startDateColumn,
            $endDateColumn,
        ]);


        $this->startDateColumn       = $startDateColumn;
        $this->endDateColumn         = $endDateColumn;
        $this->startDateDataAccessor = $startDateDataAccessor;
        $this->startDateDataAccessor = $endDateDataAccessor ?? $startDateDataAccessor;
    }
    public function valueForItem($item)
    {
        $startDate = $this->startDateDataAccessor->valueForKey($this->startDateColumn, $item);
        $endDate   = $this->endDateDataAccessor->valueForKey($this->endDateColumn, $item);
        
        $startDate = $startDate instanceof DateTime ? $startDate : new DateTime($startDate);
        $endDate   = $endDate instanceof DateTime ? $endDate : new DateTime($endDate);
        
        $diffInSeconds = $endDate->getTimestamp() - $startDate->getTimestamp();
        
        $twentyFourHoursInSeconds = 24 * 60 * 60;
    
        $result = ceil($diffInSeconds / $twentyFourHoursInSeconds);
    
        return $result;
    }
    


}