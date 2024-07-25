<?php

class GTKSelectQueryModifier
{
    public $extraClauses;
    public $desiredPageNumber;
    public $numberOfItemsPerPage;

    public function applyToQuery($query)
    {
        if ($this->extraClauses)
        {
            foreach ($this->extraClauses as $extraClause)
            {
                $query->addClause($extraClause);
            }
        }

        if ($this->desiredPageNumber)
        {
            $query->desiredPageNumber = $this->desiredPageNumber;
        }

        if ($this->numberOfItemsPerPage)
        {
            $query->limit = $this->numberOfItemsPerPage;
        }
    }
}
