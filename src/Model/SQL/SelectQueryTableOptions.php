<?php

class SelectQueryTableOptions
{
    public string $urlBase = '';
    public string $queryParameterName = 'page';
    public int    $itemsPerPage = 20;
    public ?array $columnsToDisplay = null;
    public string $tableClass = 'table';
    public string $theadClass = '';
    public string $tbodyClass = '';
    public string $trClass = '';
    public string $tdClass = '';
    public string $paginationClass = 'pagination';
    public string $paginationLinkClass = 'page-link';
    public string $paginationActiveLinkClass = 'active';
    public string $noItemsMessage = 'No items to display.';
    public /* callable */ $rowStyleCallback;

    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
