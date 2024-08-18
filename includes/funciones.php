<?php

function debuguear($variable) : string {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}
function s($html) : string {
    $s = htmlspecialchars($html);
    return $s;
}

function pagina_actual($path) : bool {
    return str_contains($_SERVER['PATH_INFO'] ?? '/', $path) ? true : false;
} 

function mensajeAlerta($variable, $texto){
    switch($variable){
        case '1':
            $mensaje = 'El' . $texto . 'password ha sido actualizado correctamente';
            break;
        case '2':
            $mensaje = 'El' . $texto . 'ponente fue registrado correctamente';
            break;
        case '3':
            $mensaje = 'El' . $texto . 'Eliminado Correctamente';
            break;
        default:
            '# code...';
            break;        
    }

    return $mensaje;
}

function setTimeout($fn, $timeout){
    // sleep for $timeout milliseconds.
    sleep(($timeout/3000));
    $fn();
}

function is_auth() : bool {
    if(!isset($_SESSION)){
        session_start();
    }
    return isset($_SESSION['nombre']) && !empty($_SESSION);
}

function is_admin() : bool {
    if(!isset($_SESSION)){
        session_start();
    }
    return isset($_SESSION['admin']) && !empty($_SESSION['admin']);  
}

function aos_animacion() : void {
    $efectos = ['fade-up', 'fade-down', 'fade-right', 'fade-left', 'flip-left', 'flip-right', 'zoom-in', 'zoom-in-up', 'zoom-in-down', 'zoom-out'];

    //retorna una posicion aleatoria de un arreglo(array) 
    $efecto = array_rand($efectos , 1);
    echo ' data-aos="' . $efectos[$efecto] . '" ';
} 

