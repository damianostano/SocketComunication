
<?php


function my_autoloader($class) {

    includeOnce('src/', $class);
    includeOnce('src/model/', $class);
    includeOnce('src/logic/', $class);
}

function includeOnce($path, $class){
    $path_class = $path.$class.'.php';
    if (file_exists($path_class))
        include_once $path_class;
}

spl_autoload_register('my_autoloader');

?>
