<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
/*******************************************
**  CLASSE DE INCLUSAO DE PAGINAS
**  METODO - trocarURL($url)
**  VERSAO 1.2
********************************************/
class verURL {
    private $allowedViews = [];
    private $normalizedMap = [];

    public function __construct() {
        $this->allowedViews = $this->buildAllowedViews();
    }

    private function buildAllowedViews() {
        $viewDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'pages/paginas';
        $files = glob($viewDir . DIRECTORY_SEPARATOR . '*.php');
        if ($files === false) {
            return [];
        }

        $blocked = [
            'debug-session',
            'config',
        ];

        $allowed = [];
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if ($this->isAllowedViewName($name, $blocked)) {
                $allowed[] = $name;
                $normalized = $this->normalizeViewName($name);
                if (!isset($this->normalizedMap[$normalized])) {
                    $this->normalizedMap[$normalized] = $name;
                }
            }
        }
        return $allowed;
    }

    private function normalizeViewName($name) {
        $name = str_replace('-', '_', $name);
        return strtolower($name);
    }

    private function isAllowedViewName($name, $blocked) {
        if (!is_string($name) || $name === '') {
            return false;
        }
        if (strpos($name, '.') !== false) {
            return false;
        }
        if (preg_match('/\s/', $name)) {
            return false;
        }
        if (in_array($name, $blocked, true)) {
            return false;
        }
        return preg_match('/^[A-Za-z0-9_-]+$/', $name) === 1;
    }

    public function trocarURL($url) {
        $view = is_string($url) ? trim($url) : '';
        if ($view === '') {
            $view = 'home';
        }

        $target = $view;
        if (!in_array($view, $this->allowedViews, true)) {
            $normalized = $this->normalizeViewName($view);
            if (isset($this->normalizedMap[$normalized])) {
                $target = $this->normalizedMap[$normalized];
            } else {
                $this->showErrorPage(404);
                return;
            }
        }

        if (!in_array($target, $this->allowedViews, true)) {
            $this->showErrorPage(404);
            return;
        }

        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'pages/paginas' . DIRECTORY_SEPARATOR . $target . '.php';
        if (is_file($path)) {
            include_once($path);
            return;
        }

        $this->showErrorPage(404);
    }

    private function showErrorPage($errorCode) {
        $errorDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'pages/paginas' . DIRECTORY_SEPARATOR . 'error';
        $map = [
            404 => $errorDir . DIRECTORY_SEPARATOR . '404.php',
            500 => $errorDir . DIRECTORY_SEPARATOR . '500.php',
        ];

        $file = $map[$errorCode] ?? ($errorDir . DIRECTORY_SEPARATOR . 'default.php');
        if (is_file($file)) {
            include_once($file);
            return;
        }

        header('Content-Type: text/html; charset=utf-8');
        http_response_code((int)$errorCode);
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Erro</title></head><body>';
        echo '<h1>Erro</h1><p>Nao foi possivel carregar a pagina.</p>';
        echo '</body></html>';
    }
}
?>
