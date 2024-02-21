<?php
class TablePlusPage extends GTKHTMLPage
{


    function columnAndFilterSelectionAreaForDataSource($dataSource, $columns = null, $filters = null)
    {
        if ($columns == null)
        {
            $columns = $dataSource->getSearchableColumns();
        }

        $toReturn = "";

        $toReturn .= '<form method="POST"';
        $toReturn .= ' hx-post="/tablePlus/submit.php"';
        $toReturn .= ' hx-include="#filter-selection-area"';
        $toReturn .= ' hx-target="#ResultsArea"';
        $toReturn .= ' hx-swap="outerHTML">';

        $toReturn .= '<input type="hidden" name="data_source" value="'.get_class($dataSource).'">';

        $toReturn .= '<h3>Seleccionar Columnas</h3>';
        $toReturn .= '<ul id="column-selection-area">';
        $toReturn .= $this->createCheckboxForColumns($columns);
        $toReturn .= '</ul>';

        $toReturn .= '<h3>Filtros</h3>';
        $toReturn .= '<div id="filter-selection-area">';
        $toReturn .= $this->createSingleFilterLineForDataSource($dataSource);
        $toReturn .= '</div>';

        $toReturn .= '<br/>';
        $toReturn .= '<br/>';

        $toReturn .= '<button'; 
        $toReturn .= ' type="submit"';
        $toReturn .= ' id="tablePlusFormSubmit"';
        $toReturn .= ' hx-post="/tablePlus/submit.php"';
        $toReturn .= ' hx-include="#filter-selection-area"';
        $toReturn .= ' hx-target="#ResultsArea" ';
        $toReturn .= ' hx-swap="innerHTML">';
        $toReturn .= 'Submit Query';
        $toReturn .= '</button>';

        $toReturn .= '</form>';

        $toReturn .= '<button';
        $toReturn .= ' type="button"'; // Needed so that HTMX does not interpret this as a submit button 
        $toReturn .= ' id="add-filter"';
        $toReturn .= ' hx-get="/tablePlus/addFilter.php"';
        $toReturn .= ' hx-target="#filter-selection-area"';
        $toReturn .= ' hx-trigger="click"';
        $toReturn .= ' hx-swap="beforeend">';
        $toReturn .= 'Agregar Filtro';
        $toReturn .= '</button>';

        return $toReturn;
    }

    function createColumnCheckbox($columnMapping) {
        $toReturn = "";

        $toReturn .= '<li>';
        $toReturn .= '<input type="checkbox"'; 
        $toReturn .=         ' id="column_' . $columnMapping->phpKey;
        $toReturn .=         ' name="columns[]" ';
        $toReturn .=         ' value="'.$columnMapping->phpKey.'">';
        $toReturn .= '<label for="column_'.$columnMapping->phpKey. '">';
        $toReturn .= htmlspecialchars($columnMapping->getFormLabel(null));
        $toReturn .= '</label>';
        $toReturn .= '</li>';

        return $toReturn;
    }
    function createCheckboxForColumnsUser($columns, $user)
    {
        $toReturn = "";

        foreach ($columns as $columnMapping) {
            if ($columnMapping->hideOnSearchForUser($user) || $columnMapping->hideOnListsForUser($user)) {
                continue;
            }

            $toReturn .= $this->createColumnCheckbox($columnMapping);
        }

        return $toReturn;
    }

    // Function to determine the number of textboxes needed for each operator
    function getTextBoxCount($operator) 
    {

        switch ($operator) {
            case 'NULL':
            case 'NOT NULL':
                return 0;
            case 'BETWEEN':
            case 'NOT BETWEEN':
                return 2;
            default:
                return 1;
        }
    }

    function createSingleFilterLineForDataSource($dataSource)
    {
        $defaultOperator = "CONTAINS";

        $textBoxCount = $this->getTextBoxCount($defaultOperator);
        $filterOperators = [
            'IS NULL'  => 'Is Null',
            'NOT NULL' => 'Not Null',
            '='        => 'Equals',
            '<>'       => 'Not Equals',
            '<'        => 'Less Than',
            '>'        => 'Greater Than',
            '<='       => 'Less Than or Equal To',
            '>='       => 'Greater Than or Equal To',
            'IN'       => 'In',
            'NOT IN'   => 'Not In',
            'CONTAINS' => 'Contains',
        ];

        $toReturn = "";

        $toReturn .= '<div class="single-filter-line">';
        // $toReturn .=     '<input type="hidden" name="filters[active][]" value="0">'; // Hidden input with default value
        // $toReturn .=     '<input type="checkbox" name="filters[active][]" checked> Active';
        $toReturn .=     '<select name="filters[active][]">';
        $toReturn .=         '<option value="1">Active</option>';
        $toReturn .=         '<option value="0">Inactive</option>';
        $toReturn .=     '</select>';
        $toReturn .=     '<select name="filters[logical][]">';
        $toReturn .=         '<option value="AND">AND</option>';
        $toReturn .=         '<option value="OR">OR</option>';
        $toReturn .=     '</select>';

        $searchableColumns = $dataSource->getSearchableColumns();

        $toReturn .= '<select name="filters[column][]">';
        foreach ($searchableColumns as $columnMapping) 
        {
            if ($columnMapping->hideOnSearchForUser($user) || $columnMapping->hideOnListsForUser($user))
            {
                continue;
            }
            else
            {
                $toReturn .= '<option';
                $toReturn .= ' value="' . $columnMapping->phpKey . '"';
                $toReturn .= '>';
                $toReturn .= $columnMapping->getFormLabel();
                $toReturn .= '</option>';
            }
        }
        $toReturn .= '</select>';


        $toReturn .= '<select name="filters[type][]">';

        foreach ($filterOperators as $key => $value)
        {
            $selected = $key == $defaultOperator ? ' selected' : '';
            $toReturn .= '<option value="'.$key.'" '.$selected.'>';
            $toReturn .=  $value;
            $toReturn .= '</option>';
        }

        $toReturn .= '</select>';
        $toReturn .= '<span class="filter-input-fields">';

        if ($textBoxCount > 0)
        {        
            $toReturn .= '<input type="text" name="filters[value1][]">';
        }

        if ($textBoxCount > 1)
        {
            $toReturn .= '<input type="text" name="filters[value2][]">'; 
        }

        $toReturn .= '</span>';
        $toReturn .= '</div>';

        return $toReturn;
    }

}
