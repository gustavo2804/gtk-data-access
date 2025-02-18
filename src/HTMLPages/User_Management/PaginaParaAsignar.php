<?php

use GTKHTMLPage;
use DataAccessManager;

class PaginaParaAsignar extends GTKHTMLPage
{
    public $NombreDataAccessParaUno;
    public $NombreDataAccessParaMuchos;

    public function __construct()
    {
        parent::__construct();
        $this->authenticationRequired = true;
    }

    public function processPost()
    {
        $uno = $_POST['uno'];
        $mucho = $_POST['mucho'];
        $action = $_POST['action'];

        $unoDataAccess = DataAccessManager::get($this->NombreDataAccessParaUno);
        $muchoDataAccess = DataAccessManager::get($this->NombreDataAccessParaMuchos);

        if ($action == 'assign') {
            $result = $unoDataAccess->assignPermissionsToRole($uno, [$mucho]);
        } else if ($action == 'remove') {
            $result = $unoDataAccess->removeFrom($uno, [$mucho]);
        }

        if ($result) {
            $this->messages[] = json_encode(['success' => true, 'message' => 'Operación realizada exitosamente.']);
        } else {
            $this->messages[] = json_encode(['success' => false, 'message' => 'Error al realizar la operación.']);
        }
    }

    public function renderMessages()
    {
        $toReturn = "";

        if (count($this->messages) > 0)
        {
            $toReturn .= "<h1>Mensajes</h1>";
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

    public function hasRelation($uno, $mucho)
    {
        $unoDataAccess = DataAccessManager::get($this->NombreDataAccessParaUno);
        $relations = $unoDataAccess->getPermissionsForRole($uno);
        return in_array($mucho, array_column($relations, 'id'));
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

        ob_start(); ?>
    
        <div class="container mx-auto mt-4">
            <h1 class="text-2xl font-bold mb-4">Asignar <?php echo ucfirst($this->NombreDataAccessParaMuchos); ?> a <?php echo ucfirst($this->NombreDataAccessParaUno); ?></h1>

            <?php echo $this->renderMessages(); ?>

            <form action="<?php echo $_SERVER['REQUEST_URI'] ?? ''; ?>" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <div class="mb-4">
                    <label for="uno" class="block text-gray-700 text-sm font-bold mb-2"><?php echo ucfirst($this->NombreDataAccessParaUno); ?>:</label>
                    <select id="uno" name="uno" required class="mt-1 block w-full border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <?php foreach ($unos as $uno): ?>
                            <option value="<?php echo htmlspecialchars($uno['id']); ?>"><?php echo htmlspecialchars($uno['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="mucho" class="block text-gray-700 text-sm font-bold mb-2"><?php echo ucfirst($this->NombreDataAccessParaMuchos); ?>:</label>
                    <select id="mucho" name="mucho" required class="mt-1 block w-full border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <?php foreach ($muchos as $mucho): ?>
                            <option value="<?php echo htmlspecialchars($mucho['id']); ?>"><?php echo htmlspecialchars($mucho['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <input type="submit" name="action" value="assign" class="px-8 w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 rounded">
                    <input type="submit" name="action" value="remove" class="px-8 w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 rounded mt-2">
                </div>
            </form>

            <h2 class="text-xl font-bold mb-4"><?php echo ucfirst($this->NombreDataAccessParaMuchos); ?> Asignados</h2>
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2"><?php echo ucfirst($this->NombreDataAccessParaMuchos); ?></th>
                        <th class="py-2">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($muchos as $mucho): ?>
                        <tr>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($mucho['name']); ?></td>
                            <td class="border px-4 py-2">
                                <?php if ($this->hasRelation($uno, $mucho['id'])): ?>
                                    <form action="<?php echo $_SERVER['REQUEST_URI'] ?? ''; ?>" method="POST">
                                        <input type="hidden" name="uno" value="<?php echo htmlspecialchars($uno['id']); ?>">
                                        <input type="hidden" name="mucho" value="<?php echo htmlspecialchars($mucho['id']); ?>">
                                        <input type="submit" name="action" value="remove" class="px-4 bg-red-500 hover:bg-red-700 text-white font-bold py-2 rounded">
                                    </form>
                                <?php else: ?>
                                    <form action="<?php echo $_SERVER['REQUEST_URI'] ?? ''; ?>" method="POST">
                                        <input type="hidden" name="uno" value="<?php echo htmlspecialchars($uno['id']); ?>">
                                        <input type="hidden" name="mucho" value="<?php echo htmlspecialchars($mucho['id']); ?>">
                                        <input type="submit" name="action" value="assign" class="px-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 rounded">
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    
        <?php return ob_get_clean();
    }
}