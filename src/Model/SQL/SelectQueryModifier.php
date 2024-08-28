<?php

class GTKSelectQueryModifier
{
    public $extraClauses;
    public $desiredPageNumber;
    public $numberOfItemsPerPage;

    public function applyToQuery($query)
    {
        
        if ($this->desiredPageNumber)
        {
            $query->desiredPageNumber = $this->desiredPageNumber;
        }

        if ($this->numberOfItemsPerPage)
        {
            $query->limit = $this->numberOfItemsPerPage;
        }

        if (is_array($this->extraClauses))
        {
            foreach ($this->extraClauses as $extraClause)
            {
                $query->addClause($extraClause);
            }
        }
        else if ( $this->extraClauses instanceof WhereClause)
        {
            $query->addClause($this->extraClauses);
        }
        
        
    }

    public function serializeToQueryParameters(&$queryParameters)
    {
        if ($this->desiredPageNumber)
        {
            $queryParameters['page'] = $this->desiredPageNumber;
        }

        if ($this->numberOfItemsPerPage)
        {
            $queryParameters['itemsPerPage'] = $this->numberOfItemsPerPage;
        }

         if (is_array($this->extraClauses))
         {
             foreach ($this->extraClauses as $extraClause)
             {
                 $extraClause->serializeToQueryParameters($queryParameters);
             }
         }
         else if($this->extraClauses instanceof WhereClause)
         {
            $this->extraClauses->serializeToQueryParameters($queryParameters);
         }
    }
}
