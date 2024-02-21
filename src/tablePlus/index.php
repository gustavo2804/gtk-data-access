<?php

require_once(dirname($_SERVER["DOCUMENT_ROOT"])."/vendor/autoload.php");
require_once "tablePlusHelpers.php";

class TablePlusIndex extends TablePlusPage
{
    public $dataSourceName;
    public $dataAccessorConstructions;
    public $dataSource;

    public function processGet($getObject)
    {
        if (!isset($_GET["data_source"]))
        {
            $this->dataSourceName = "PersonaDataAccess";
        }
        else
        {
            $this->dataSourceName = $_GET["data_source"];
        }

        $this->dataAccessorConstructions = DataAccessManager::get('DataAccessManager')->DataAccessManager::getConstructions();

        $this->dataSource = DataAccessManager::get($dataSourceName);

        usort($this->dataAccessorConstructions, function($a, $b) {
            return strcmp($a["class"], $b["class"]);
        });
    }

    public function render()
    {
        ob_start(); ?>
        <html>
        <head>
        <script src="https://unpkg.com/htmx.org"></script>

        </head>
        </html>

        <h3>Seleccionar Data Source</h3>


        <select name="data_source"
                hx-get="/tablePlus/dataSourceHandler.php" 
                hx-target="#dynamic-content" 
                hx-trigger="change">
            <?php
                foreach ($this->dataAccessorConstructions as $dataAccessorMap) 
                {
                    $key = $dataAccessorMap["class"];
                    $selected = ($key == $this->dataSourceName) ? ' selected="selected"' : '';
                    echo '<option value="'.$key.'" '.$selected.'>';
                    echo htmlspecialchars($dataAccessorMap["class"]);
                    echo '</option>';
                }
            ?>
        </select>
            
            
        <div id="dynamic-content">
            <!-- The server will return the filter selection area here -->
            <!-- This div will be populated dynamically based on user interactions -->
            <?php echo columnAndFilterSelectionAreaForDataSource($dataSource); ?>
        </div>
            
            
            
        <h1>Results</h1>
        <div id="ResultsArea">
            <p>Count: {count}</p>
            <h3>Pages</h3>
            <table>
                <!-- The server will return the query results here -->
            </table>
        </div>
            
            
            
        <script>
        
        function didSelectAddFilter() {
            htmx.trigger("#filter-selection-area", "htmx:load", {
                target: "tablePlusNewFilterForDataSource.php?dataSourceName=" + encodeURIComponent(dataSourceName)
            });
        }

        function didSelectFilterType(e)
        {
            if (e.target.name === 'filter_type') 
            {
                // Assuming each filter type dropdown has a unique ID
                var filterOperator = e.target.value;
                htmx.trigger(e.target, "htmx:load", {
                    target: "tablePlusFilterTextBox.php?filterOperator=" + encodeURIComponent(filterOperator) + "&dataSourceName=" + encodeURIComponent(dataSourceName)
                });
            }
        }

        function addParametersToRequestOnElement(event, elementURL, parametersToAdd) 
        {
            var newUrl = new URL(elementURL, window.location.origin);
        
            for (var key in parametersToAdd) 
            {
                if (parametersToAdd.hasOwnProperty(key)) {
                    newUrl.searchParams.set(key, parametersToAdd[key]);
                }
            }

            /*
            for (var [key, value] of Object.entries(parametersToAdd)) 
            {
                newUrl.searchParams.set(key, value);
            }
            */
        
            // Update the XHR URL
            event.detail.xhr.open(event.detail.xhr.method, newUrl.toString(), true);
        }

        document.addEventListener('htmx:beforeRequest', function(event) {
            console.log('HTMX beforeRequest: ' + event.target.id);
            console.log(event.detail.parameters);
            console.log(event.detail.headers);
            console.log(event.detail.elt);
            // console.log(event.detail.xhr);
            // console.log(event.detail.target);
            // console.log(event.detail.trigger);
            // console.log(event.detail.swaps);
            // console.log(event.detail.indicators);
            // console.log(event.detail.timeout);
            // console.log(event.detail.verb);
            // console.log(event.detail.path);
            // console.log(event.detail.params);
            // console.log(event.detail.body);
            // console.log(event.detail.headers);
            // console.log(event.detail.requestConfig);
            // console.log(event.detail.responseConfig);
            // console.log(event.detail.templateConfig);
            // console.log(event.detail.eventInfo);
            // console.log(event.detail.extensionEventInfo);
            // console.log(event.detail.extensionEventInfo);

            /*
            if (event.target.id === 'tablePlusFormSubmit') 
            {
                console.log('ResultsArea - beforeRequest');
            
                var dataSource = document.querySelector('select[name="data_source"]').value;
                console.log('Data source: ' + dataSource);
            
                var elementURL  = event.detail.elt.getAttribute('hx-post');
                console.log('Element URL: ' + elementURL);
            
                addParametersToRequestOnElement(event, elementURL, {
                    'data_source': dataSource
                });
            
                // Update the XHR URL
                // event.detail.xhr.open(event.detail.xhr.method, newUrl.toString(), true);
            
                return;
            }
            */

            if (event.target.id === 'add-filter') 
            {
                console.log('add-filter - beforeRequest');
            
                var dataSource = document.querySelector('select[name="data_source"]').value;
                console.log('Data source: ' + dataSource);
            
                var elementURL  = event.detail.elt.getAttribute('hx-get');
                console.log('Element URL: ' + elementURL);
            
                addParametersToRequestOnElement(event, elementURL, {
                    'data_source': dataSource
                });
            
                // Update the XHR URL
                // event.detail.xhr.open(event.detail.xhr.method, newUrl.toString(), true);
            
                return;
            }
        });



        // Example: Custom JavaScript for additional logic
        document.addEventListener('htmx:afterRequest', function(event) {
            /*
            document.getElementById('add-filter').addEventListener('click', function() {
                didSelectAddFilter();
            });
        
        
            document.getElementById('filter-selection-area').addEventListener('change', function(e) {
                didSelectFilterType(e);
            });
            */
        
        });

        // htmx events
        // htmx:afterRequest
        // htmx:beforeRequest
        // htmx:configRequest
        // htmx:configResponse
        // htmx:configTemplate
        // htmx:connected
        // htmx:connectionError
        // htmx:cssSwap
        // htmx:doneRequest
        // htmx:embed
        // htmx:endRequest
        // htmx:extension:afterOnLoad
        // htmx:extension:beforeOnLoad
        // htmx:extension:configElt
        // htmx:extension:configSSE
        // htmx:extension:configWS
        // htmx:extension:created
        // htmx:extension:destroyed
        // htmx:extension:prepRequest
        // htmx:extension:responseError
        // htmx:extension:responseReceived
        // htmx:extension:responseProcessed
        // htmx:extension:responseProcessing
        // htmx:extension:send
        // hx-trigger={load | click | mouseover | ...}
        // https://htmx.org/events/
        // hx-swap={afterend | afterbegin | beforebegin | beforeend | innerHTML | outerHTML }

        document.addEventListener('htmx:load', function(event) {
            // document.
        });

        </script>


        <script>
            function updateFilterInputFields(selectElement) {
                var operator = selectElement.value;
                var inputFieldsContainer = selectElement.closest('.single-filter-line').querySelector('.filter-input-fields');
            
                // Clear existing inputs
                inputFieldsContainer.innerHTML = '';
            
                // Define the number of textboxes needed for each operator
                var textBoxCount = {
                    '=': 1, '<>': 1, '<': 1, '>': 1, '<=': 1, '>=': 1, 'IN': 1, 'NOT IN': 1, 'CONTAINS': 1,
                    'BETWEEN': 2, 'NOT BETWEEN': 2, // Add other operators as needed
                };
            
                // Add new inputs based on the selected operator
                for (var i = 0; i < (textBoxCount[operator] || 1); i++) {
                    var input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'filters[value][]';
                    inputFieldsContainer.appendChild(input);
                }
            }
        
            document.addEventListener('htmx:load', function(event) {
                // document.
            });
        </script>
        <?php return ob_get_clean();
    }
}
