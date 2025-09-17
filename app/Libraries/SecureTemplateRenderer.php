<?php
namespace App\Libraries;

use CodeIgniter\View\View;

class SecureTemplateRenderer extends View
{
    protected $uploadBasePath;
    
    public function __construct($config = null, $viewPath = null)
    {
        if ($config === null) {
            $config = config('View');
        } elseif (is_array($config)) {
            $config = (object)$config;
        }

        if ($viewPath === null) {
            $viewPath = APPPATH . 'Views';
        }

        parent::__construct($config, $viewPath);
        $this->uploadBasePath = USER_DATA_PATH;
    }
    
    public function renderUserTemplate(string $templatePath, array $data = [])
    {        
        if (!file_exists($templatePath)) {
            throw new \RuntimeException('Template not found: ' . $templatePath);
        }
        
        // Read template content
        $content = file_get_contents($templatePath);
        
        try {
            // Extract PHP code from the template
            ob_start();
            extract($data);
            eval('?>' . $content);
            $output = ob_get_clean();
            
            return $output;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw new \RuntimeException('Failed to render template: ' . $e->getMessage());
        }
    }

    public function renderString(string $view, ?array $options = null, ?bool $saveData = null): string
    {
        $options = $options ?? [];
        $saveData = $saveData ?? true;

        if (array_key_exists('data', $options) && is_array($options['data'])) {
            extract($options['data']);
        }

        ob_start();
        try {
            eval('?>' . $view);
        } catch (\Throwable $e) {
            ob_end_clean();
            log_message('error', '[SecureTemplateRenderer] Failed to render string: ' . $e->getMessage());
            return '';
        }
        $output = ob_get_clean();

        if ($saveData) {
            $this->tempData = $this->data;
        }

        return $output;
    }
}
