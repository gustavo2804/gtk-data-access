<?php

class GestionPermisosRolesPage extends GTKHTMLPage
{
    public $permisos;
    public $roles;
    public $mensaje;
    public $tipoMensaje;

    public function __construct()
    {
        parent::__construct();
        
        // Manejar las acciones POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostAction();
        }

        $this->prepareData();
    }

    private function handlePostAction()
    {
        try {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'crear_permiso':
                    $this->handleCrearPermiso();
                    break;
                case 'editar_permiso':
                    $this->handleEditarPermiso();
                    break;
                case 'eliminar_permiso':
                    $this->handleEliminarPermiso();
                    break;
                case 'crear_rol':
                    $this->handleCrearRol();
                    break;
                case 'editar_rol':
                    $this->handleEditarRol();
                    break;
                case 'eliminar_rol':
                    $this->handleEliminarRol();
                    break;
                default:
                    throw new Exception('Acción no válida');
            }

        } catch (Exception $e) {
            error_log("Error en GestionPermisosRolesPage::handlePostAction: " . $e->getMessage());
            $this->mensaje = "Error: " . $e->getMessage();
            $this->tipoMensaje = "error";
        }
    }

    private function handleCrearPermiso()
    {
        $permisosDA = DataAccessManager::get("permissions");
        
        $datosPermiso = [
            'name' => $_POST['nombre'] ?? '',
            'comments' => $_POST['comentarios'] ?? '',
            'is_active' => $_POST['estado'] ?? true,
            'date_created' => date('Y-m-d H:i:s')
        ];

        $resultado = $permisosDA->insert($datosPermiso);
        
        if ($resultado) {
            $this->mensaje = "Permiso creado exitosamente.";
            $this->tipoMensaje = "success";
            $this->prepareData();
        } else {
            throw new Exception('Error al crear el permiso');
        }
    }

    private function handleEditarPermiso()
    {
        $permisosDA = DataAccessManager::get("permissions");
        
        $datosPermiso = [
            'id' => $_POST['id'] ?? '',
            'name' => $_POST['nombre'] ?? '',
            'comments' => $_POST['comentarios'] ?? '',
            'is_active' => $_POST['estado'] ?? true,
            'date_modified' => date('Y-m-d H:i:s')
        ];

        $resultado = $permisosDA->update($datosPermiso);
        
        if ($resultado) {
            $this->mensaje = "Permiso actualizado exitosamente.";
            $this->tipoMensaje = "success";
            $this->prepareData();
        } else {
            throw new Exception('Error al actualizar el permiso');
        }
    }

    private function handleEliminarPermiso()
    {
        $id = $_POST['id'] ?? '';
        
        if (!$id) {
            throw new Exception('ID de permiso no proporcionado');
        }

        $permisosDA = DataAccessManager::get("permissions");
        
        // Cambiar estado a inactivo en lugar de eliminar físicamente
        $datosActualizar = [
            'id' => $id,
            'is_active' => false,
            'date_modified' => date('Y-m-d H:i:s')
        ];
        
        $resultado = $permisosDA->update($datosActualizar);
        
        if ($resultado) {
            $this->mensaje = "Permiso desactivado exitosamente.";
            $this->tipoMensaje = "success";
            $this->prepareData();
        } else {
            throw new Exception('Error al desactivar el permiso');
        }
    }

    private function handleCrearRol()
    {
        $rolesDA = DataAccessManager::get("roles");
        
        $datosRol = [
            'name' => $_POST['nombre'] ?? '',
            'purpose' => $_POST['proposito'] ?? '',
            'is_active' => $_POST['estado'] ?? true,
            'needs_qualifier' => $_POST['needs_qualifier'] ?? false,
            'qualifier_data_source' => $_POST['qualifier_data_source'] ?? null,
            'qualifier_data_source_column' => $_POST['qualifier_data_source_column'] ?? null,
            'qualifier_data_label_column' => $_POST['qualifier_data_label_column'] ?? null,
            'is_root_role' => $_POST['is_root_role'] ?? false,
            'date_created' => date('Y-m-d H:i:s')
        ];

        $resultado = $rolesDA->insert($datosRol);
        
        if ($resultado) {
            $this->mensaje = "Rol creado exitosamente.";
            $this->tipoMensaje = "success";
            $this->prepareData();
        } else {
            throw new Exception('Error al crear el rol');
        }
    }

    private function handleEditarRol()
    {
        $rolesDA = DataAccessManager::get("roles");
        
        $datosRol = [
            'id' => $_POST['id'] ?? '',
            'name' => $_POST['nombre'] ?? '',
            'purpose' => $_POST['proposito'] ?? '',
            'is_active' => $_POST['estado'] ?? true,
            'needs_qualifier' => $_POST['needs_qualifier'] ?? false,
            'qualifier_data_source' => $_POST['qualifier_data_source'] ?? null,
            'qualifier_data_source_column' => $_POST['qualifier_data_source_column'] ?? null,
            'qualifier_data_label_column' => $_POST['qualifier_data_label_column'] ?? null,
            'is_root_role' => $_POST['is_root_role'] ?? false,
            'date_modified' => date('Y-m-d H:i:s')
        ];

        $resultado = $rolesDA->update($datosRol);
        
        if ($resultado) {
            $this->mensaje = "Rol actualizado exitosamente.";
            $this->tipoMensaje = "success";
            $this->prepareData();
        } else {
            throw new Exception('Error al actualizar el rol');
        }
    }

    private function handleEliminarRol()
    {
        $id = $_POST['id'] ?? '';
        
        if (!$id) {
            throw new Exception('ID de rol no proporcionado');
        }

        $rolesDA = DataAccessManager::get("roles");
        
        // Cambiar estado a inactivo en lugar de eliminar físicamente
        $datosActualizar = [
            'id' => $id,
            'is_active' => false,
            'date_modified' => date('Y-m-d H:i:s')
        ];
        
        $resultado = $rolesDA->update($datosActualizar);
        
        if ($resultado) {
            $this->mensaje = "Rol desactivado exitosamente.";
            $this->tipoMensaje = "success";
            $this->prepareData();
        } else {
            throw new Exception('Error al desactivar el rol');
        }
    }

    public function prepareData()
    {
        try {
            // Obtener todos los permisos
            $permisosDA = DataAccessManager::get("permissions");
            $this->permisos = $permisosDA->getAll();
            
            // Obtener todos los roles
            $rolesDA = DataAccessManager::get("roles");
            $this->roles = $rolesDA->getAll();
            
            error_log("INFO - Permisos cargados: " . count($this->permisos));
            error_log("INFO - Roles cargados: " . count($this->roles));
            
        } catch (Exception $e) {
            error_log("Error en GestionPermisosRolesPage::prepareData: " . $e->getMessage());
            $this->permisos = [];
            $this->roles = [];
        }
    }

    public function estiloPagina()
    {
        ob_start();
        ?>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                background-color: #f5f5f5;
                margin: 0;
                padding: 20px;
            }

            .container {
                max-width: 1400px;
                margin: 0 auto;
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                overflow: hidden;
            }

            .header {
                background: linear-gradient(135deg, #2c3e50, #34495e);
                color: white;
                padding: 30px;
                text-align: center;
            }

            .header h1 {
                margin: 0;
                font-size: 2.2em;
                font-weight: 600;
            }

            .header p {
                margin: 10px 0 0 0;
                opacity: 0.9;
                font-size: 1.1em;
            }

            .content {
                padding: 30px;
            }

            /* Mensajes */
            .mensaje {
                padding: 15px 20px;
                border-radius: 8px;
                margin-bottom: 25px;
                font-weight: 500;
            }

            .mensaje.success {
                background-color: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }

            .mensaje.error {
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }

            /* Tablas */
            .tables-container {
                display: flex;
                flex-direction: column;
                gap: 30px;
                margin-top: 30px;
            }

            .table-section {
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                overflow: hidden;
            }

            .table-header {
                background: linear-gradient(135deg, #3498db, #2980b9);
                color: white;
                padding: 20px;
                font-weight: 600;
                font-size: 1.1em;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .table-content {
                max-height: 400px;
                overflow-y: auto;
            }

            .data-table {
                width: 100%;
                border-collapse: collapse;
                margin: 0;
            }

            .data-table th {
                background-color: #f8f9fa;
                color: #2c3e50;
                padding: 12px;
                text-align: left;
                font-weight: 600;
                font-size: 0.9em;
                border-bottom: 2px solid #e9ecef;
                position: sticky;
                top: 0;
                z-index: 10;
            }

            .data-table td {
                padding: 12px;
                border-bottom: 1px solid #e9ecef;
                vertical-align: top;
            }

            .data-table tbody tr:hover {
                background-color: #f8f9fa;
            }

            .status-badge {
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 0.8em;
                font-weight: 600;
            }

            .status-activo {
                background-color: #d4edda;
                color: #155724;
            }

            .status-inactivo {
                background-color: #f8d7da;
                color: #721c24;
            }

            /* Botones de acción */
            .btn-crear {
                background: linear-gradient(135deg, #3498db, #2980b9);
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 0.9em;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .btn-crear:hover {
                background: linear-gradient(135deg, #2980b9, #3498db);
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
            }

            .btn-editar {
                background: linear-gradient(135deg, #f39c12, #e67e22);
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                font-size: 0.8em;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-right: 5px;
            }

            .btn-editar:hover {
                background: linear-gradient(135deg, #e67e22, #f39c12);
                transform: translateY(-1px);
            }

            .btn-eliminar {
                background: linear-gradient(135deg, #e74c3c, #c0392b);
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                font-size: 0.8em;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .btn-eliminar:hover {
                background: linear-gradient(135deg, #c0392b, #e74c3c);
                transform: translateY(-1px);
            }

            /* Modales */
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
            }

            .modal-content {
                background-color: white;
                margin: 5% auto;
                padding: 0;
                border-radius: 12px;
                width: 90%;
                max-width: 600px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                animation: modalSlideIn 0.3s ease-out;
            }

            @keyframes modalSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-50px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .modal-header {
                background: linear-gradient(135deg, #3498db, #2980b9);
                color: white;
                padding: 20px;
                border-radius: 12px 12px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .modal-header h3 {
                margin: 0;
                font-size: 1.3em;
            }

            .close {
                color: white;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
                transition: opacity 0.3s ease;
            }

            .close:hover {
                opacity: 0.7;
            }

            .modal-body {
                padding: 25px;
            }

            .modal-form {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }

            .modal-form .form-group.full-width {
                grid-column: 1 / -1;
            }

            .modal-form input,
            .modal-form select,
            .modal-form textarea {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e1e8ed;
                border-radius: 8px;
                font-size: 1em;
                background: white;
                transition: border-color 0.3s ease;
            }

            .modal-form input:focus,
            .modal-form select:focus,
            .modal-form textarea:focus {
                outline: none;
                border-color: #3498db;
                box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            }

            .modal-footer {
                padding: 20px 25px;
                background: #f8f9fa;
                border-radius: 0 0 12px 12px;
                display: flex;
                justify-content: flex-end;
                gap: 15px;
            }

            .btn-cancelar {
                background: #6c757d;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .btn-cancelar:hover {
                background: #5a6268;
            }

            .btn-guardar {
                background: linear-gradient(135deg, #27ae60, #2ecc71);
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .btn-guardar:hover {
                background: linear-gradient(135deg, #2ecc71, #27ae60);
            }

            /* Responsive */
            @media (max-width: 768px) {
                .modal-form {
                    grid-template-columns: 1fr;
                }

                .table-content {
                    max-height: 300px;
                }

                .data-table {
                    font-size: 0.85em;
                }

                .data-table th,
                .data-table td {
                    padding: 8px;
                }

                .table-header {
                    flex-direction: column;
                    gap: 10px;
                    align-items: flex-start;
                }
            }
        </style>
        <?php
        return ob_get_clean();
    }

    public function renderTablas()
    {
        ob_start();
        ?>
        <div class="tables-container">
            <!-- Tabla de Permisos -->
            <div class="table-section">
                <div class="table-header">
                    <div>
                        🔐 Lista de Permisos
                    </div>
                    <button class="btn-crear" onclick="abrirModalCrearPermiso()">➕ Crear Permiso</button>
                </div>
                <div class="table-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Comentarios</th>
                                <th>Estado</th>
                                <th>Fecha Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->permisos as $permiso): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($permiso['id']) ?></strong></td>
                                <td><?php echo htmlspecialchars($permiso['name']) ?></td>
                                <td><?php echo htmlspecialchars($permiso['comments'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="status-badge <?php echo ($permiso['is_active'] == 1) ? 'status-activo' : 'status-inactivo' ?>">
                                        <?php echo ($permiso['is_active'] == 1) ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($permiso['date_created'] ?? 'N/A') ?></td>
                                <td>
                                    <button class="btn-editar" onclick="abrirModalEditarPermiso('<?php echo htmlspecialchars($permiso['id']) ?>', '<?php echo htmlspecialchars($permiso['name']) ?>', '<?php echo htmlspecialchars($permiso['comments'] ?? '') ?>', '<?php echo htmlspecialchars($permiso['is_active'] ?? '1') ?>')">
                                        ✏️ Editar
                                    </button>
                                    <button class="btn-eliminar" onclick="confirmarEliminarPermiso('<?php echo htmlspecialchars($permiso['id']) ?>', '<?php echo htmlspecialchars($permiso['name']) ?>')">
                                        🗑️ Desactivar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabla de Roles -->
            <div class="table-section">
                <div class="table-header">
                    <div>
                        👥 Lista de Roles
                    </div>
                    <button class="btn-crear" onclick="abrirModalCrearRol()">➕ Crear Rol</button>
                </div>
                <div class="table-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Propósito</th>
                                <th>Estado</th>
                                <th>Necesita Calificador</th>
                                <th>Fecha Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->roles as $rol): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($rol['id']) ?></strong></td>
                                <td><?php echo htmlspecialchars($rol['name']) ?></td>
                                <td><?php echo htmlspecialchars($rol['purpose'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="status-badge <?php echo ($rol['is_active'] == 1) ? 'status-activo' : 'status-inactivo' ?>">
                                        <?php echo ($rol['is_active'] == 1) ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo ($rol['needs_qualifier'] == 1) ? 'status-activo' : 'status-inactivo' ?>">
                                        <?php echo ($rol['needs_qualifier'] == 1) ? 'Sí' : 'No' ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($rol['date_created'] ?? 'N/A') ?></td>
                                <td>
                                    <button class="btn-editar" onclick="abrirModalEditarRol('<?php echo htmlspecialchars($rol['id']) ?>', '<?php echo htmlspecialchars($rol['name']) ?>', '<?php echo htmlspecialchars($rol['purpose'] ?? '') ?>', '<?php echo htmlspecialchars($rol['is_active'] ?? '1') ?>', '<?php echo htmlspecialchars($rol['needs_qualifier'] ?? '0') ?>', '<?php echo htmlspecialchars($rol['qualifier_data_source'] ?? '') ?>', '<?php echo htmlspecialchars($rol['qualifier_data_source_column'] ?? '') ?>', '<?php echo htmlspecialchars($rol['qualifier_data_label_column'] ?? '') ?>', '<?php echo htmlspecialchars($rol['is_root_role'] ?? '0') ?>')">
                                        ✏️ Editar
                                    </button>
                                    <button class="btn-eliminar" onclick="confirmarEliminarRol('<?php echo htmlspecialchars($rol['id']) ?>', '<?php echo htmlspecialchars($rol['name']) ?>')">
                                        🗑️ Desactivar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function renderModales()
    {
        ob_start();
        ?>
        <!-- Modal Crear Permiso -->
        <div id="modalCrearPermiso" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>🔐 Crear Nuevo Permiso</h3>
                    <span class="close" onclick="cerrarModal('modalCrearPermiso')">&times;</span>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="crear_permiso">
                    <div class="modal-body">
                        <div class="modal-form">
                            <div class="form-group full-width">
                                <label for="nombre_permiso">Nombre del Permiso:</label>
                                <input type="text" id="nombre_permiso" name="nombre" required placeholder="Ej: persona.create">
                            </div>
                            <div class="form-group full-width">
                                <label for="comentarios_permiso">Comentarios:</label>
                                <textarea id="comentarios_permiso" name="comentarios" rows="3" placeholder="Descripción del permiso"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="estado_permiso">Estado:</label>
                                <select id="estado_permiso" name="estado" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModal('modalCrearPermiso')">Cancelar</button>
                        <button type="submit" class="btn-guardar">Crear Permiso</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Editar Permiso -->
        <div id="modalEditarPermiso" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>✏️ Editar Permiso</h3>
                    <span class="close" onclick="cerrarModal('modalEditarPermiso')">&times;</span>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="editar_permiso">
                    <div class="modal-body">
                        <div class="modal-form">
                            <div class="form-group">
                                <label for="id_permiso_edit">ID:</label>
                                <input type="text" id="id_permiso_edit" name="id" required readonly>
                            </div>
                            <div class="form-group">
                                <label for="estado_permiso_edit">Estado:</label>
                                <select id="estado_permiso_edit" name="estado" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <label for="nombre_permiso_edit">Nombre del Permiso:</label>
                                <input type="text" id="nombre_permiso_edit" name="nombre" required>
                            </div>
                            <div class="form-group full-width">
                                <label for="comentarios_permiso_edit">Comentarios:</label>
                                <textarea id="comentarios_permiso_edit" name="comentarios" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModal('modalEditarPermiso')">Cancelar</button>
                        <button type="submit" class="btn-guardar">Actualizar Permiso</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Crear Rol -->
        <div id="modalCrearRol" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>👥 Crear Nuevo Rol</h3>
                    <span class="close" onclick="cerrarModal('modalCrearRol')">&times;</span>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="crear_rol">
                    <div class="modal-body">
                        <div class="modal-form">
                            <div class="form-group">
                                <label for="nombre_rol">Nombre del Rol:</label>
                                <input type="text" id="nombre_rol" name="nombre" required placeholder="Ej: ADMIN_USER">
                            </div>
                            <div class="form-group">
                                <label for="estado_rol">Estado:</label>
                                <select id="estado_rol" name="estado" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <label for="proposito_rol">Propósito:</label>
                                <textarea id="proposito_rol" name="proposito" rows="3" placeholder="Descripción del propósito del rol"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="needs_qualifier_rol">Necesita Calificador:</label>
                                <select id="needs_qualifier_rol" name="needs_qualifier" required>
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="is_root_role_rol">Es Rol Raíz:</label>
                                <select id="is_root_role_rol" name="is_root_role" required>
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="qualifier_data_source_rol">Fuente de Datos del Calificador:</label>
                                <input type="text" id="qualifier_data_source_rol" name="qualifier_data_source" placeholder="Ej: agencia">
                            </div>
                            <div class="form-group">
                                <label for="qualifier_data_source_column_rol">Columna de la Fuente:</label>
                                <input type="text" id="qualifier_data_source_column_rol" name="qualifier_data_source_column" placeholder="Ej: id">
                            </div>
                            <div class="form-group">
                                <label for="qualifier_data_label_column_rol">Columna de Etiqueta:</label>
                                <input type="text" id="qualifier_data_label_column_rol" name="qualifier_data_label_column" placeholder="Ej: nombre">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModal('modalCrearRol')">Cancelar</button>
                        <button type="submit" class="btn-guardar">Crear Rol</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Editar Rol -->
        <div id="modalEditarRol" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>✏️ Editar Rol</h3>
                    <span class="close" onclick="cerrarModal('modalEditarRol')">&times;</span>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="editar_rol">
                    <div class="modal-body">
                        <div class="modal-form">
                            <div class="form-group">
                                <label for="id_rol_edit">ID:</label>
                                <input type="text" id="id_rol_edit" name="id" required readonly>
                            </div>
                            <div class="form-group">
                                <label for="estado_rol_edit">Estado:</label>
                                <select id="estado_rol_edit" name="estado" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nombre_rol_edit">Nombre del Rol:</label>
                                <input type="text" id="nombre_rol_edit" name="nombre" required>
                            </div>
                            <div class="form-group">
                                <label for="needs_qualifier_rol_edit">Necesita Calificador:</label>
                                <select id="needs_qualifier_rol_edit" name="needs_qualifier" required>
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <label for="proposito_rol_edit">Propósito:</label>
                                <textarea id="proposito_rol_edit" name="proposito" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="is_root_role_rol_edit">Es Rol Raíz:</label>
                                <select id="is_root_role_edit" name="is_root_role" required>
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="qualifier_data_source_rol_edit">Fuente de Datos del Calificador:</label>
                                <input type="text" id="qualifier_data_source_rol_edit" name="qualifier_data_source">
                            </div>
                            <div class="form-group">
                                <label for="qualifier_data_source_column_rol_edit">Columna de la Fuente:</label>
                                <input type="text" id="qualifier_data_source_column_rol_edit" name="qualifier_data_source_column">
                            </div>
                            <div class="form-group">
                                <label for="qualifier_data_label_column_rol_edit">Columna de Etiqueta:</label>
                                <input type="text" id="qualifier_data_label_column_rol_edit" name="qualifier_data_label_column">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModal('modalEditarRol')">Cancelar</button>
                        <button type="submit" class="btn-guardar">Actualizar Rol</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Formularios ocultos para eliminar -->
        <form id="formEliminarPermiso" method="POST" action="" style="display: none;">
            <input type="hidden" name="action" value="eliminar_permiso">
            <input type="hidden" name="id" id="id_eliminar_permiso">
        </form>

        <form id="formEliminarRol" method="POST" action="" style="display: none;">
            <input type="hidden" name="action" value="eliminar_rol">
            <input type="hidden" name="id" id="id_eliminar_rol">
        </form>
        <?php
        return ob_get_clean();
    }

    public function renderBody()
    {
        $toReturn = $this->estiloPagina();
        
        $toReturn .= '<div class="container">';
        $toReturn .= '<div class="header">';
        $toReturn .= '<h1>Gestión de Permisos y Roles</h1>';
        $toReturn .= '<p>Administre los permisos y roles del sistema</p>';
        $toReturn .= '</div>';
        
        $toReturn .= '<div class="content">';
        
        // Mostrar mensaje si existe
        if ($this->mensaje) {
            $toReturn .= '<div class="mensaje ' . $this->tipoMensaje . '">' . htmlspecialchars($this->mensaje) . '</div>';
        }
        
        $toReturn .= $this->renderTablas();
        $toReturn .= $this->renderModales();
        
        $toReturn .= '</div>';
        $toReturn .= '</div>';
        
        // Scripts para funcionalidad de modales
        $toReturn .= '
        <script>
            // Funciones para modales de permisos
            function abrirModalCrearPermiso() {
                document.getElementById("modalCrearPermiso").style.display = "block";
            }

            function abrirModalEditarPermiso(id, nombre, comentarios, estado) {
                document.getElementById("id_permiso_edit").value = id;
                document.getElementById("nombre_permiso_edit").value = nombre;
                document.getElementById("comentarios_permiso_edit").value = comentarios;
                document.getElementById("estado_permiso_edit").value = estado;
                document.getElementById("modalEditarPermiso").style.display = "block";
            }

            function confirmarEliminarPermiso(id, nombre) {
                if (confirm("¿Está seguro de que desea desactivar el permiso \"" + nombre + "\" (ID: " + id + ")?\\n\\nEsta acción cambiará el estado del permiso a inactivo.")) {
                    document.getElementById("id_eliminar_permiso").value = id;
                    document.getElementById("formEliminarPermiso").submit();
                }
            }

            // Funciones para modales de roles
            function abrirModalCrearRol() {
                document.getElementById("modalCrearRol").style.display = "block";
            }

            function abrirModalEditarRol(id, nombre, proposito, estado, needs_qualifier, qualifier_data_source, qualifier_data_source_column, qualifier_data_label_column, is_root_role) {
                document.getElementById("id_rol_edit").value = id;
                document.getElementById("nombre_rol_edit").value = nombre;
                document.getElementById("proposito_rol_edit").value = proposito;
                document.getElementById("estado_rol_edit").value = estado;
                document.getElementById("needs_qualifier_rol_edit").value = needs_qualifier;
                document.getElementById("qualifier_data_source_rol_edit").value = qualifier_data_source;
                document.getElementById("qualifier_data_source_column_rol_edit").value = qualifier_data_source_column;
                document.getElementById("qualifier_data_label_column_rol_edit").value = qualifier_data_label_column;
                document.getElementById("is_root_role_edit").value = is_root_role;
                document.getElementById("modalEditarRol").style.display = "block";
            }

            function confirmarEliminarRol(id, nombre) {
                if (confirm("¿Está seguro de que desea desactivar el rol \"" + nombre + "\" (ID: " + id + ")?\\n\\nEsta acción cambiará el estado del rol a inactivo.")) {
                    document.getElementById("id_eliminar_rol").value = id;
                    document.getElementById("formEliminarRol").submit();
                }
            }

            function cerrarModal(modalId) {
                document.getElementById(modalId).style.display = "none";
            }

            // Cerrar modal al hacer clic fuera de él
            window.onclick = function(event) {
                var modals = document.getElementsByClassName("modal");
                for (var i = 0; i < modals.length; i++) {
                    if (event.target == modals[i]) {
                        modals[i].style.display = "none";
                    }
                }
            }

            // Cerrar modal con ESC
            document.addEventListener("keydown", function(event) {
                if (event.key === "Escape") {
                    var modals = document.getElementsByClassName("modal");
                    for (var i = 0; i < modals.length; i++) {
                        if (modals[i].style.display === "block") {
                            modals[i].style.display = "none";
                        }
                    }
                }
            });
        </script>';
        
        return $toReturn;
    }
}
?> 