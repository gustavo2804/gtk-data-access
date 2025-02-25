<?php
use GTKHTMLPage;
use DataAccessManager;

class AssignPermissionsToRolesHTMLPage extends GTKHTMLPage
{
    public function processPost()
    {
        $role = $_POST['role'];
        $permissions = $_POST['permissions'];

        if (is_string($permissions)) {
            $permissions = explode(',', $permissions);
        }

        $rolePermissionDataAccess = DataAccessManager::get('role_permission_relationships');

        $result = $rolePermissionDataAccess->assignPermissionsToRole($role, $permissions);

        if ($result) {
            $this->messages[] = json_encode(['success' => true, 'message' => 'Permisos asignados exitosamente.']);
        } else {
            $this->messages[] = json_encode(['success' => false, 'message' => 'Error al asignar los permisos.']);
        }
    }

    public function renderMessages()
    {
        $toReturn = "";

        if (count($this->messages) > 0)
        {
            $toReturn .= "<h1 class='font-bold'>Mensajes</h1>";
            $toReturn .= "<div>";
            foreach ($this->messages as $message)
            {
                $toReturn .= "<div>";
                if (is_string($message))
                {
                    $toReturn .= htmlspecialchars($message);
                }
                else
                {
                    $toReturn .= print_r($message, true);
                }
                $toReturn .= "</div>";
            }
            $toReturn .= "</div>";
        }

        return $toReturn;
    }

    public function renderBody()
    {
        $roleDataAccess = DataAccessManager::get('roles');
        $permissionDataAccess = DataAccessManager::get('permissions');

        $roles = $roleDataAccess->selectAll();
        $permissions = $permissionDataAccess->selectAll();

        ob_start(); ?>
    
        <h1>Asignar Permisos a Rol</h1>

        <?php
        echo $this->renderMessages();
        ?>

        <form action="<?php echo $_SERVER['REQUEST_URI'] ?? ''; ?>" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <label for="role">Rol:</label>
            <select id="role" name="role" required class="mt-1 block w-full border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo htmlspecialchars($role['id']); ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="permissions">Permisos:</label>
            <select id="permissions" name="permissions[]" multiple required class="mt-1 block w-full border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <?php foreach ($permissions as $permission): ?>
                    <option value="<?php echo htmlspecialchars($permission['id']); ?>"><?php echo htmlspecialchars($permission['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <br>
            <input type="submit" value="Asignar Permisos" class="px-8 w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 rounded">
        </form>
    
        <?php return ob_get_clean();
    }
}