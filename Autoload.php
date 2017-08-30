
<?php


function my_autoloader($class) {

    includeOnce(ROOT.'/src/', $class);
    includeOnce(ROOT.'/src/model/', $class);
    includeOnce(ROOT.'/src/logic/', $class);
    includeOnce(ROOT.'/src/dao/', $class);
    includeOnce(ROOT.'/exception/', $class);
}

function includeOnce($path, $class){
    $path_class = $path.$class.'.php';
    if (file_exists($path_class))
        include_once $path_class;
}

spl_autoload_register('my_autoloader');

?>
