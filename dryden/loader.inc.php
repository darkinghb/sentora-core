<?php

$mtime = explode(' ', microtime());
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;
$class_name = null;

function __autoload($class_name)
{
    $path = 'dryden/' . str_replace('_', '/', $class_name) . '.class.php';
    if (file_exists($path)) {
        require_once $path;
    }
}

spl_autoload_register('__autoload');

if (isset($_GET['module'])) {
    $CleanModuleName = fs_protector::SanitiseFolderName($_GET['module']);

    $ControlerPath = 'modules/' . $CleanModuleName . '/code/controller.ext.php';
    if (file_exists($ControlerPath)) {
        require_once $ControlerPath;
    }

    $ModulePath = 'modules/' . $CleanModuleName . '/code/' . $class_name . '.class.php';
    if (file_exists($ModulePath)) {
        require_once $ModulePath;
    }
}
