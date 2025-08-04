<?php

class PaginaParaAsignar extends GTKHTMLPage
{
    public $NombreDataAccessParaUno;
    public $NombreDataAccessParaMuchos;
    public $NombreRelacion;
    private $relation;

    public function __construct($NombreDataAccessParaUno = null, $NombreDataAccessParaMuchos = null, $NombreRelacion = null, $unoColumn = null, $muchoColumn = null)
    {
        parent::__construct();
        $this->authenticationRequired = true;
        $this->NombreDataAccessParaUno = $NombreDataAccessParaUno;
        $this->NombreDataAccessParaMuchos = $NombreDataAccessParaMuchos;
        $this->NombreRelacion = $NombreRelacion;

        
        if ($unoColumn === null || $muchoColumn === null) {
            throw new InvalidArgumentException("Las columnas unoColumn y muchoColumn son obligatorias.");
        }

        $this->relation = new OneToManyRelation($NombreRelacion, $unoColumn, $muchoColumn);

     
        error_log("PaginaParaAsignar initialized with: NombreDataAccessParaUno = $NombreDataAccessParaUno, NombreDataAccessParaMuchos = $NombreDataAccessParaMuchos, NombreRelacion = $NombreRelacion, unoColumn = $unoColumn, muchoColumn = $muchoColumn");
    }

    public function processPost()
    {
        $uno = $_POST['uno'] ?? null;
        $mucho = $_POST['mucho'] ?? null;
        $action = $_POST['action'] ?? null;
        $page = $_POST['page'] ?? 1;
        $search = $_POST['search'] ?? '';

        // Log the POST data
        error_log("processPost: uno = " . print_r($uno, true));
        error_log("processPost: mucho = " . print_r($mucho, true));
        error_log("processPost: action = " . print_r($action, true));

        if ($uno && $mucho && $action) {
            if ($action == 'assign') {
                if ($this->relation->assignRelation($uno, $mucho)) {
                    $this->messages[] = "Relación asignada correctamente.";
                } else {
                    $this->messages[] = "Error al asignar la relación.";
                }
            } elseif ($action == 'remove') {
                if ($this->relation->removeRelation($uno, $mucho)) {
                    $this->messages[] = "Relación eliminada correctamente.";
                } else {
                    $this->messages[] = "Error al eliminar la relación.";
                }
            }
            
            // Redirigir de vuelta con los parámetros
            $redirectUrl = "?uno=" . urlencode($uno) . "&page=" . urlencode($page);
            if (!empty($search)) {
                $redirectUrl .= "&search=" . urlencode($search);
            }
            header("Location: " . $_SERVER['PHP_SELF'] . $redirectUrl);
            exit;
        }
    }

    public function renderMessages()
    {
        $toReturn = "";

        if (count($this->messages) > 0) {
            $toReturn .= "<h1>Mensajes</h1>";
            $toReturn .= "<div>";
            foreach ($this->messages as $message) {
                $toReturn .= "<div>";
                if (is_string($message)) {
                    $toReturn .= htmlspecialchars($message);
                } else {
                    $toReturn .= print_r($message, true);
                }
                $toReturn .= "</div>";
            }
            $toReturn .= "</div>";
        }

        return $toReturn;
    }

    public function hasRelation($unoId, $muchoId)
    {
        try {
            if (empty($this->NombreDataAccessParaUno) || empty($this->NombreDataAccessParaMuchos)) {
                error_log("Error: Nombres de DataAccess no configurados");
                return false;
            }

            if (empty($unoId) || empty($muchoId)) {
                error_log("Error: IDs inválidos");
                return false;
            }

            return $this->relation->hasRelation($unoId, $muchoId);
        } catch (Exception $e) {
            error_log("Error en hasRelation: " . $e->getMessage());
            return false;
        }
    }

    public function filterUnique($items)
    {
        $uniqueItems = [];
        $seen = [];

        foreach ($items as $item) {
            if (!in_array($item['name'], $seen)) {
                $uniqueItems[] = $item;
                $seen[] = $item['name'];
            }
        }

        return $uniqueItems;
    }

    public function renderBody()
    {
        $unoDataAccess = DataAccessManager::get($this->NombreDataAccessParaUno);
        $muchosDataAccess = DataAccessManager::get($this->NombreDataAccessParaMuchos);

        $unos = $unoDataAccess->selectAll();
        $muchos = $this->filterUnique($muchosDataAccess->selectAll());

        $selectedUno = $_POST['uno'] ?? $_GET['uno'] ?? null;
        $searchTerm = $_GET['search'] ?? '';

        // Log the selected uno
        error_log("renderBody: selectedUno = " . print_r($selectedUno, true));

        $assigned = [];
        $notAssigned = [];

        foreach ($muchos as $mucho) {
            if ($this->hasRelation($selectedUno, $mucho['id'])) {
                $assigned[] = $mucho;
            } else {
                $notAssigned[] = $mucho;
            }
        }

        // Aplicar filtro de búsqueda si hay término de búsqueda
        if (!empty($searchTerm)) {
            $searchTermLower = strtolower($searchTerm);
            $assigned = array_filter($assigned, function($item) use ($searchTermLower) {
                return strpos(strtolower($item['name']), $searchTermLower) !== false;
            });
            $notAssigned = array_filter($notAssigned, function($item) use ($searchTermLower) {
                return strpos(strtolower($item['name']), $searchTermLower) !== false;
            });
        }

        
        error_log("renderBody: assigned = " . print_r($assigned, true));
        error_log("renderBody: notAssigned = " . print_r($notAssigned, true));

        // Paginación
        $itemsPerPage = 25;
        $currentPage = $_GET['page'] ?? 1;
        $totalItems = count($assigned) + count($notAssigned);
        $totalPages = ceil($totalItems / $itemsPerPage);
        $startIndex = ($currentPage - 1) * $itemsPerPage;
        $pagedItems = array_slice(array_merge($assigned, $notAssigned), $startIndex, $itemsPerPage);

        ob_start(); ?>
    
        <div class="container mx-auto mt-4">
            <h1 class="text-2xl font-bold mb-4">Asignar <?php echo ucfirst($this->NombreDataAccessParaMuchos); ?> a <?php echo ucfirst($this->NombreDataAccessParaUno); ?></h1>

            <?php echo $this->renderMessages(); ?>

            <!-- Agregar CSS de Select2 -->
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <style>
                .select2-container {
                    width: 25% !important;
                }
                .select2-container .select2-selection--single {
                    height: 36px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                }
                .select2-container--default .select2-selection--single .select2-selection__rendered {
                    line-height: 36px;
                    padding-right: 20px;
                }
                .select2-container--default .select2-selection--single .select2-selection__arrow {
                    height: 34px;
                    width: 30px;
                }
                .select2-container--default .select2-results__option--highlighted[aria-selected] {
                    background-color: #007bff;
                }
                .select2-search__field {
                    padding: 6px !important;
                }
                /* Ocultar el botón de limpiar */
                .select2-container--default .select2-selection--single .select2-selection__clear {
                    display: none !important;
                }
                .select2-container--default .select2-selection--single:focus {
                    border-color: #007bff;
                    outline: none;
                    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
                }
                /* Estilos para el buscador */
                .search-container {
                    margin-bottom: 20px;
                }
                .search-form {
                    display: flex;
                    gap: 10px;
                    align-items: center;
                    width: 30%;
                    margin: 0;
                }
                .search-input {
                    width: 250px;
                    padding: 8px 12px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 14px;
                    transition: border-color 0.3s ease;
                }
                .search-input:focus {
                    outline: none;
                    border-color: #007bff;
                    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
                }
                .search-input::placeholder {
                    color: #999;
                }
                .search-btn {
                    background-color: #007bff;
                    color: white;
                    padding: 10px 20px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 14px;
                    transition: background-color 0.3s ease;
                }
                .search-btn:hover {
                    background-color: #0056b3;
                }
                .clear-search-btn {
                    background-color: #6c757d;
                    color: white;
                    padding: 10px 20px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 14px;
                    text-decoration: none;
                    transition: background-color 0.3s ease;
                }
                .clear-search-btn:hover {
                    background-color: #545b62;
                }
                .search-results-info {
                    margin-top: 10px;
                    padding: 8px 12px;
                    background-color: #f8f9fa;
                    border-radius: 4px;
                    font-size: 14px;
                    color: #666;
                }
                .search-results-info p {
                    margin: 0;
                }
            </style>

            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <div class="mb-4">
                    <label for="uno" class="block text-gray-700 text-sm font-bold mb-2"><?php echo ucfirst($this->NombreDataAccessParaUno); ?>:</label>
                    <select id="uno" name="uno" required class="select2-search">
                        <option value="">Seleccione un <?php echo ucfirst($this->NombreDataAccessParaUno); ?></option>
                        <?php 
                        // Ordenar el array por nombre/email
                        usort($unos, function($a, $b) {
                            $aValue = $this->NombreDataAccessParaUno == 'persona' ? $a['email'] : $a['name'];
                            $bValue = $this->NombreDataAccessParaUno == 'persona' ? $b['email'] : $b['name'];
                            return strcasecmp($aValue, $bValue);
                        });
                        
                        foreach ($unos as $uno): 
                            $displayValue = $this->NombreDataAccessParaUno == 'persona' ? $uno['email'] : $uno['name'];
                        ?>
                            <option value="<?php echo htmlspecialchars($uno['id']); ?>" 
                                    <?php echo $selectedUno == $uno['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($displayValue); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
            </form>

            <?php if ($selectedUno): ?>
            <div class="search-container">
                <label for="search" class="block text-gray-700 text-sm font-bold mb-2">Buscar <?php echo ucfirst($this->NombreDataAccessParaMuchos); ?>:</label>
                <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="search-form">
                    <input type="hidden" name="uno" value="<?php echo htmlspecialchars($selectedUno); ?>">
                    <input type="text" id="search" name="search" placeholder="Escriba para buscar..." class="search-input" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit" class="search-btn">Buscar</button>
                    <?php if (!empty($searchTerm)): ?>
                        <a href="?uno=<?php echo htmlspecialchars($selectedUno); ?>" class="clear-search-btn">Limpiar</a>
                    <?php endif; ?>
                </form>
                <?php if (!empty($searchTerm)): ?>
                    <div class="search-results-info">
                        <p>Mostrando <?php echo count($pagedItems); ?> de <?php echo $totalItems; ?> resultados para "<?php echo htmlspecialchars($searchTerm); ?>"</p>
                    </div>
                <?php else: ?>
                    <div class="search-results-info">
                        <p>Total: <?php echo $totalItems; ?> elementos</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Agregar jQuery y Select2 JS -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <script>
                $(document).ready(function() {
                    $('.select2-search').select2({
                        placeholder: 'Buscar...',
                        allowClear: false,
                        width: '100%',
                        language: {
                            noResults: function() {
                                return "No se encontraron resultados";
                            },
                            searching: function() {
                                return "Buscando...";
                            }
                        },
                        matcher: function(params, data) {
                            // Si no hay término de búsqueda, mostrar la opción
                            if ($.trim(params.term) === '') {
                                return data;
                            }

                            // No mostrar la opción si no hay texto
                            if (typeof data.text === 'undefined') {
                                return null;
                            }

                            // Búsqueda case-insensitive y con acentos
                            const normalizedTerm = params.term.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                            const normalizedText = data.text.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

                            // Si el texto contiene el término de búsqueda, mostrar la opción
                            if (normalizedText.indexOf(normalizedTerm) > -1) {
                                return data;
                            }

                            // Si no hay coincidencia, no mostrar la opción
                            return null;
                        }
                    }).on('select2:select', function(e) {
                        // Enviar el formulario cuando se seleccione una opción
                        $(this).closest('form').submit();
                    });
                });
            </script>

            <?php if ($selectedUno): ?>
            <h2 class="text-xl font-bold mb-4"><?php echo ucfirst($this->NombreDataAccessParaMuchos); ?> Asignados</h2>
            <style>
               .form-group {
                    margin-bottom: 15px;
                }
                .form-group label {
                    display: block;
                    font-weight: bold;
                    margin-bottom: 5px;
                }
                .btn-remove {
                    background-color: #dc3545;
                    color: white;
                    padding: 10px 15px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
                .btn-remove:hover {
                    background-color: #c82333;
                }
                .btn-assign {
                    background-color: #28a745;
                    color: white;
                    padding: 10px 15px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
                .btn-assign:hover {
                    background-color: #218838;
                }
                .table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .table th, .table td {
                    border: 1px solid #ddd;
                    padding: 8px;
                }
                .table th {
                    background-color: #f2f2f2;
                    text-align: left;
                }
                /* Estilos para el buscador */
                .search-container {
                    margin-bottom: 20px;
                }
                .search-input {
                    width: 100%;
                    padding: 10px 15px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 14px;
                    transition: border-color 0.3s ease;
                }
                .search-input:focus {
                    outline: none;
                    border-color: #007bff;
                    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
                }
                .search-input::placeholder {
                    color: #999;
                }
                /* Estilos para el mensaje de no resultados */
                .no-results-message td {
                    text-align: center;
                    padding: 20px;
                    color: #666;
                    font-style: italic;
                }
                /* Estilos para la paginación */
                .pagination {
                    margin-top: 20px;
                    text-align: center;
                }
                .pagination a {
                    display: inline-block;
                    padding: 8px 12px;
                    margin: 0 4px;
                    border: 1px solid #ddd;
                    text-decoration: none;
                    color: #007bff;
                    border-radius: 4px;
                }
                .pagination a:hover {
                    background-color: #f8f9fa;
                }
                .pagination a.active {
                    background-color: #007bff;
                    color: white;
                    border-color: #007bff;
                }
            </style>
            <table class="table">
                <thead>
                    <tr>
                        <th><?php echo ucfirst($this->NombreDataAccessParaMuchos); ?></th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pagedItems as $mucho): ?>
                        <tr class="<?php echo in_array($mucho, $assigned) ? 'bg-green-100' : ''; ?>">
                            <td><?php echo htmlspecialchars($mucho['name']); ?></td>
                            <td>
                                <?php if (in_array($mucho, $assigned)): ?>
                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                        <input type="hidden" name="uno" value="<?php echo htmlspecialchars($selectedUno); ?>">
                                        <input type="hidden" name="mucho" value="<?php echo htmlspecialchars($mucho['id']); ?>">
                                        <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                                        <input type="submit" name="action" value="remove" class="btn btn-remove">
                                    </form>
                                <?php else: ?>
                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                        <input type="hidden" name="uno" value="<?php echo htmlspecialchars($selectedUno); ?>">
                                        <input type="hidden" name="mucho" value="<?php echo htmlspecialchars($mucho['id']); ?>">
                                        <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                                        <input type="submit" name="action" value="assign" class="btn btn-assign">
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&uno=<?php echo htmlspecialchars($selectedUno); ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" class="<?php echo $i == $currentPage ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    
        <?php return ob_get_clean();
    }
}