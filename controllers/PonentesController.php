<?php

namespace Controllers;

use Classes\Paginacion;
use Model\Ponente;
use MVC\Router;
use Intervention\Image\ImageManagerStatic as Image;

class PonentesController {
    public static function index(Router $router) {

        $pagina_actual = $_GET['page'];
        $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);
        
        if(!$pagina_actual || $pagina_actual < 1){
            header('Location: /admin/ponentes?page=1');
        }

        $registros_por_pagina = 5; 
        $total = Ponente::total();
        $paginacion = new Paginacion($pagina_actual,$registros_por_pagina,$total);

        //si yo tengo 5 paginas pero esa cantidad es menor a la pagina actual (ponele que 100) redirecciono a la pagina 1
        if($paginacion->total_paginas() < $pagina_actual ){
            header('Location: /admin/ponentes?page=1');
        }
        $ponentes = Ponente::paginar($registros_por_pagina, $paginacion->offset());

        //protejo que estan iniciado sesion
        if(!is_admin()){
            header('Location: /login');
        }

        $router->render('admin/ponentes/index',[
            'titulo' => 'Ponentes | Conferencistas',
            'ponentes' => $ponentes,
            'paginacion' => $paginacion->paginacion()
        ]);
    }


    public static function crear(Router $router) {
        //protejo que estan iniciado sesion
        if(!is_admin()){
            header('Location: /login');
        }

        $alertas = [];
        $ponente =  new Ponente;

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            //protejo que estan iniciado sesion
            if(!is_admin()){
                header('Location: /login');
            }

            //Sincronizar imagen
            if(!empty($_FILES['imagen']['tmp_name'])){
                $carpeta_imagenes = '../public/img/speakers';
                
                //Crear la carpeta si no existe
                if(!is_dir($carpeta_imagenes)){
                    mkdir($carpeta_imagenes, 0755, true);
                }

                $imagen_png = Image::make($_FILES['imagen']['tmp_name'])->fit(800,800)->encode('png', 80);
                $imagen_webp = Image::make($_FILES['imagen']['tmp_name'])->fit(800,800)->encode('webp', 80);

                //con esto genero que no se dupliquen los nombres de las imagenes y genero un nombre aleatorio
                $nombre_imagen = md5( uniqid( rand(), true) );

                $_POST['imagen'] = $nombre_imagen;
            }

            $_POST['redes'] = json_encode($_POST['redes'], JSON_UNESCAPED_SLASHES);

            //sincronizo con el modelo POST
            $ponente->sincronizar($_POST);

            //validar
            $alertas = $ponente->validar();

            //guardar el registro
            if(empty($alertas)){
                
                //guardar imagenes
                $imagen_png->save($carpeta_imagenes . '/' . $nombre_imagen . ".png");
                $imagen_webp->save($carpeta_imagenes . '/' . $nombre_imagen . ".webp");
                
                //guardar en la base de datos
                $resultado = $ponente->guardar();

                if($resultado){
                    header('Location: /admin/ponentes');
                }
            }
        }

        $router->render('admin/ponentes/crear',[
            'titulo' => 'Registrar Ponente',
            'alertas' => $alertas,
            'ponente' => $ponente,
            'redes' => json_decode($ponente->redes) 

        ]);
    }


    public static function editar(Router $router){

        if(!is_admin()){
            header('Location: /login');
        }

        $alertas = [];
        //Validar el ID
        $id = $_GET['id'];
        $id =filter_var($id, FILTER_VALIDATE_INT);
        
        if(!$id){
            header('Location: /admin/ponentes');
        }

        //Obtener ponente a Editar
        $ponente = Ponente::find($id);
        //si no existe el ponente vuelve al pagina principal de ponentes
        if(!$ponente){
            header('Location: /admin/ponentes');
        }

        $ponente->imagen_actual = $ponente->imagen;

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            //protejo que estan iniciado sesion
            if(!is_admin()){
                header('Location: /login');
            }

            //Sincronizar imagen
            if(!empty($_FILES['imagen']['tmp_name'])){
                $carpeta_imagenes = '../public/img/speakers';
                
                //Crear la carpeta si no existe
                if(!is_dir($carpeta_imagenes)){
                    mkdir($carpeta_imagenes, 0755, true);
                }

                $imagen_png = Image::make($_FILES['imagen']['tmp_name'])->fit(800,800)->encode('png', 80);
                $imagen_webp = Image::make($_FILES['imagen']['tmp_name'])->fit(800,800)->encode('webp', 80);

                //con esto genero que no se dupliquen los nombres de las imagenes y genero un nombre aleatorio
                $nombre_imagen = md5( uniqid( rand(), true) );

                $_POST['imagen'] = $nombre_imagen;
            } else {
                //si no hay una imagen dejo la actual
                $_POST['imagen'] = $ponente->imagen_actual;
            }    
            $_POST['redes'] = json_encode($_POST['redes'], JSON_UNESCAPED_SLASHES);
            $ponente->sincronizar($_POST);

            $alertas = $ponente->validar();

            if(empty($alertas)){
                if(isset($nombre_imagen)){
                    $imagen_png->save($carpeta_imagenes . '/' . $nombre_imagen . ".png");
                    $imagen_webp->save($carpeta_imagenes . '/' . $nombre_imagen . ".webp");
                }

                $resultado = $ponente->guardar();
                if($resultado){
                    header('Location: /admin/ponentes');
                }
            }
        }

        $router->render('admin/ponentes/editar',[
            'titulo' => 'Actualizar Ponente',
            'alertas' => $alertas,
            'ponente' => $ponente,
            //conviete un string en un objeto
            'redes' => json_decode($ponente->redes) 
        ]);
    }

    public static function eliminar(){

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            //protejo que estan iniciado sesion
            if(!is_admin()){
                header('Location: /login');
            }

            //tomo el ID
            $id = $_POST['id'];
            //se trae todo el objeto
            $ponente = Ponente::find($id);

            //verifico si existe
            if(!isset($ponente)){
                header('Location: /admin/ponentes');
            }

            //elimino el ponente
            $resultado = $ponente->eliminar();
            if($resultado){
                header('Location: /admin/ponentes');
            }
        }
    }
}