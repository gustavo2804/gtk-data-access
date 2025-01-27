<?php

class GTKTemplate {
    private static $instance = null;
    private $layouts = [];
    private $components = [];
    private $layoutRequirements = [];
    private $componentRequirements = [];
    private $isAjax;
    private $templateDir;

    private function __construct() {
        // Get template directory from globals
        $this->templateDir = $_GLOBALS['TEMPLATE_DIR'] ?? __DIR__ . '/templates';
        
        // Detect AJAX
        $this->isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        // Discover templates on instantiation
        $this->discoverTemplates();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function discoverTemplates() {
        $componentsDir = $this->templateDir . '/components';
        
        if (!is_dir($componentsDir)) {
            throw new Exception("Components directory not found: $componentsDir");
        }

        // Discover components
        foreach (glob("$componentsDir/*.php") as $file) {
            if (str_ends_with($file, 'Config.php')) {
                continue; // Skip config files
            }

            $name = basename($file, '.php');
            $this->registerComponent($name, $file);
            
            // Look for accompanying config file
            $configFile = dirname($file) . "/{$name}Config.php";
            if (file_exists($configFile)) {
                $config = include $configFile;
                if (!empty($config['required'])) {
                    $this->setComponentRequirements($name, $config['required']);
                }
            }
        }
    }

    private function registerComponent($name, $path, array $required = []) {
        $this->components[$name] = $path;
        $this->componentRequirements[$name] = $required;
    }

    private function setComponentRequirements($name, array $required) {
        if (!isset($this->components[$name])) {
            throw new Exception("Component '$name' not found");
        }
        $this->componentRequirements[$name] = $required;
    }

    public function component($name, $data = []) {
        if (!isset($this->components[$name])) {
            throw new Exception("Component '$name' not found");
        }

        // Validate component requirements
        $missing = [];
        foreach ($this->componentRequirements[$name] as $requirement) {
            if (!array_key_exists($requirement, $data)) {
                $missing[] = $requirement;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception(
                "Component '$name' is missing required data: " . 
                implode(', ', $missing)
            );
        }
        
        extract($data);
        ob_start();
        include $this->components[$name];
        return ob_get_clean();
    }



    public static function render($layout, $content = '', $options = []) 
    {
        // For AJAX requests, skip layout rendering if component-only request
        if (self::$isAjax && !empty($_GET['component'])) 
        {
            return self::component($_GET['component'], $options);
        }

        if (self::$isAjax)
        {
            return $content;
        }

        if (!isset(self::$layouts[$layout])) {
            throw new Exception("Layout '$layout' not found");
        }

        // Validate layout requirements
        $missing = [];
        foreach (self::$layoutRequirements[$layout] as $requirement) {
            if (!array_key_exists($requirement, $options)) {
                $missing[] = $requirement;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception(
                "Layout '$layout' is missing required options: " . 
                implode(', ', $missing)
            );
        }

        $defaults = [
            'title' => 'Kanboombo',
            'showSidebar' => true,
            'scripts' => [],
            'styles' => []
        ];
        
        $options = array_merge($defaults, $options);
        
        ob_start();
        include self::$layouts[$layout];
        return ob_get_clean();
    }

    // Static wrapper methods for convenience
    public static function renderComponent($name, $data = []) {
        return self::getInstance()->component($name, $data);
    }

    public static function renderPage($layout, $content = '', $options = []) {
        return self::getInstance()->render($layout, $content, $options);
    }
}