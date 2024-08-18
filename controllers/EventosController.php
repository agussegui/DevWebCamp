<?php

namespace Controllers;

use Classes\Paginacion;
use Model\Categoria;
use Model\Dia;
use Model\Evento;
use Model\Hora;
use Model\Ponente;
use MVC\Router;

class EventosController {

    public static function index(Router $router) {

        //protejo que estan iniciado sesion
        if(!is_admin()){
            header('Location: /login');
        }

        //leer desde la URL
        $pagina_actual = $_GET['page'];
        //Validar si es un entero
        $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);

        if(!$pagina_actual || $pagina_actual < 1){
            header('Location: /admin/eventos?page=1');
        }

        $por_pagina = 10;
        $total = Evento::total();
        $paginacion = new Paginacion($pagina_actual, $por_pagina, $total);

        $eventos = Evento::paginar($por_pagina, $paginacion->offset());

        foreach($eventos as $evento){
            //crea una nueva llave de categoria pero se trae informacion de la tabla de categoria asociada con el del evento
            $evento->categoria = Categoria::find($evento->categoria_id);
            $evento->dia = Dia::find($evento->dia_id);
            $evento->hora = Hora::find($evento->hora_id);
            $evento->ponente = Ponente::find($evento->ponente_id);
        }

        $router->render('admin/eventos/index',[
            'titulo' => 'Conferencias y Workshops',
            'eventos' => $eventos,
            'paginacion' => $paginacion->paginacion()
        ]);
    }

    public static function crear(Router $router) {

        //protejo que estan iniciado sesion
        if(!is_admin()){
            header('Location: /login');
        }

        $alertas = [];

        $categorias = Categoria::all('ASC');
        //Toma el valor de ($orden) que viene de ActiveRecord
        $dias = Dia::all('ASC');
        $horas = Hora::all('ASC');

        $evento = new Evento;

        if($_SERVER ['REQUEST_METHOD'] === 'POST'){

            //protejo que estan iniciado sesion
            if(!is_admin()){
                header('Location: /login');
            }

            $evento->sincronizar($_POST); 
            $alertas = $evento->validar();
            
            //si no estan vacias las alertas pasamos la
            if(empty($alertas)){
                //guardo el la base de datos
                $resultado = $evento->guardar();

                //si el resultado es correcto lo redirecciono a eventos
                if($resultado ){
                    header('Location: /admin/eventos');
                }
            }
        }


        $router->render('admin/eventos/crear', [
            'titulo' => 'Registrar Evento',
            'alertas' => $alertas,
            'categorias' => $categorias,
            'dias' => $dias,
            'horas' => $horas,
            'evento' => $evento
        ]);
    } 


    public static function editar(Router $router) {

        //protejo que estan iniciado sesion
        if(!is_admin()){
            header('Location: /login');
        }

        $alertas = [];

        $id = $_GET['id'];
        $id = filter_var($id, FILTER_VALIDATE_INT);

        //si al presionar el boton editar no ecuentra el id correcto re direcciona a la pagina eventos 
        if(!$id) {
            header('Location: /admin/eventos');
        }    


        $categorias = Categoria::all('ASC');
        //Toma el valor de ($orden) que viene de ActiveRecord
        $dias = Dia::all('ASC');
        $horas = Hora::all('ASC');

        $evento = Evento::find($id);

        if(!$evento){
            header('Location: /admin/eventos');
        }

        if($_SERVER ['REQUEST_METHOD'] === 'POST'){

            //protejo que estan iniciado sesion
            if(!is_admin()){
                header('Location: /login');
            }

            $evento->sincronizar($_POST); 
            $alertas = $evento->validar();
            
            //si no estan vacias las alertas pasamos la
            if(empty($alertas)){
                //guardo el la base de datos
                $resultado = $evento->guardar();

                //si el resultado es correcto lo redirecciono a eventos
                if($resultado ){
                    header('Location: /admin/eventos');
                }
            }
        }


        $router->render('admin/eventos/editar', [
            'titulo' => 'Editar Evento',
            'alertas' => $alertas,
            'categorias' => $categorias,
            'dias' => $dias,
            'horas' => $horas,
            'evento' => $evento
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
            $evento = Evento::find($id);

            //verifico si existe
            if(!isset($evento)){
                header('Location: /admin/eventos');
            }

            //elimino el evento
            $resultado = $evento->eliminar();
            if($resultado){
                header('Location: /admin/eventos');
            }
        }
    }
}