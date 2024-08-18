<?php

namespace Controllers;

use Model\Categoria;
use Model\Dia;
use Model\Evento;
use Model\EventosRegistros;
use Model\Hora;
use Model\Paquete;
use Model\Ponente;
use Model\Regalo;
use Model\Registro;
use Model\Usuario;
use MVC\Router;


class RegistroController {
    public static function crear(Router $router) {

        if(!is_auth()){
            header('Location: /');
            return;
        }

        //Verificar si el usuario ya esta registrado
        $registro = Registro::where('usuario_id', $_SESSION['id']);

        if(isset($registro) && ($registro->paquete_id === "3" || $registro->paquete_id === "2")){
            header('Location: /boleto?id=' . urlencode($registro->token));
            return;
        }

        if(isset($registro) && $registro->paquete_id === "1"){
            header('Location: /finalizar-registro/conferencias');
            return;
        }

        $router->render('registro/crear', [
            'titulo' => 'Finalizar Registro '
        ]);
    }

    public static function gratis(Router $router) {

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(!is_auth()){
                header('Location: /login');
                return;
            }

            //Verificar si el usuario ya esta registrado
            $registro = Registro::where('usuario_id', $_SESSION['id']);
            if(isset($registro) && $registro->paquete_id === "3"){
                header('Location: /boleto?id=' . urlencode($registro->token));
                return;
            }

            //Boleto virtual creado aleatoriamente 
            $token = substr(md5(uniqid(rand(), true)), 0, 8);
            
            //Crear Registro para el boleto
            $datos = [
                'paquete_id' => 3,
                'pago_id' => '',
                'token' => $token,
                'usuario_id' => $_SESSION['id']
            ];

            $registro = new Registro($datos);
            $resultado = $registro->guardar();
            
            if($resultado){
                header('Location: /boleto?id=' . urlencode($registro->token));
                return;
            }
        }
    }

    public static function boleto(Router $router) {

        //Validar la URL
        $id = $_GET['id'];

        if(!$id || !strlen($id) === 8){
            header('Location: /'); 
            return;
        }

        //buscar en la base de datos
        $registro = Registro::where('token', $id);
        if(!$registro){
            header('Location: /'); 
            return;
        }

        //Llenar las tablas de referancia
        $registro->usuario = Usuario::find($registro->usuario_id);
        $registro->paquete = Paquete::find($registro->paquete_id);


        $router->render('registro/boleto', [
            'titulo' => 'Asistencia a DevWebCamp',
            'registro' => $registro
        ]);
    }


    public static function pagar(Router $router) {

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(!is_auth()){
                header('Location: /login');
                return;
            }

            //Validar que Post no venga vacio
            if(empty($_POST)){
                echo json_encode([]);
                return;
            }

            //Crear el registro
            $datos = $_POST;
            $datos['token'] = substr(md5(uniqid(rand(), true)), 0, 8);
            //Esto se hace desde el servidor y no desde paypal porque es mas seguro
            $datos['usuario_id'] = $_SESSION['id'];
            
            //si se ejecuta el try
            //sino se ejecuta try y va al catch va a saltar un error que no se puedo realizar el pago 
            try {
                $registro = new Registro($datos);
                $resultado = $registro->guardar();
                echo json_encode($resultado);
            } catch(\Throwable $th){
                echo json_encode([
                    'resultado' => 'error',
                ]);
            }
            
        }
    }

    public static function conferencias(Router $router) {

        if(!is_auth()){
            header('Location: /login');
            return;
        }

        //validar que tenga el pack presencial
        $usuario_id = $_SESSION['id'];
        $registro = Registro::where('usuario_id', $usuario_id);

        if(isset($registro) && $registro->paquete_id === "2"){
            header('Location: /boleto?id=' . urlencode($registro->token)); 
            return;
        }  

        //si el registro es distinto a uno que es el paquete que tiene el id 1 vuelve al login 
        if($registro->paquete_id !== "1"){
            header('Location: /');
            return;
        }
        
        //Redireccionar a boleto virtual en caso de haber finalizado su registro   
        if(isset($registro->regalo_id) && $registro->paquete_id === "1"){
            header('Location: /boleto?id=' . urlencode($registro->token)); 
            return;
        }   

        $eventos = Evento::ordenar('hora_id', 'ASC');
        
        //el arreglo se va llenando con el Foreach
        $eventos_formateados = [];
        foreach($eventos as $evento){
            $evento->categoria = Categoria::find($evento->categoria_id);
            $evento->dia = Dia::find($evento->dia_id);
            $evento->hora = Hora::find($evento->hora_id);
            $evento->ponente = Ponente::find($evento->ponente_id);

            if($evento->dia_id === "1" && $evento->categoria_id === "1"){
                $eventos_formateados['conferencias_v'][] = $evento;     
            }

            if($evento->dia_id === "2" && $evento->categoria_id === "1"){
                $eventos_formateados['conferencias_s'][] = $evento;     
            }

            if($evento->dia_id === "1" && $evento->categoria_id === "2"){
                $eventos_formateados['workshops_v'][] = $evento;     
            }

            if($evento->dia_id === "2" && $evento->categoria_id === "2"){
                $eventos_formateados['workshops_s'][] = $evento;     
            }
        }

        $regalos= Regalo::all('ASC');

        //Manejando el registro mediante $_POST
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            
            //Revisar que el usuario este autenticado
            if(!is_auth()){
                header('Location: /login');
                return;
            }
            
            $eventos = explode(',', $_POST['eventos']);
            if(empty($eventos)){
                echo json_encode(['resultado' => false]);
                return;
            }

            //Obtener el registro de usuario
            $registro = Registro::where('usuario_id', $_SESSION['id']);
            if(!isset($registro)|| $registro->paquete_id !== "1"){
                echo json_encode(['resultado' => false]);
                return;
            }

            $eventos_array = [];

            //validar la disponibilidad de los eventos seleccionados
            foreach($eventos as $evento_id){
                $evento = Evento::find($evento_id);
                
                //Comprobar que el evento exista, si no existe retorno un error
                if(!isset($evento) || $evento->disponibles === "0"){
                    echo json_encode(['resultado' => false]);
                    return;
                } 
                $eventos_array[] = $evento; 
            }

            foreach($eventos_array as $evento){

                $evento->disponibles -= 1;
                $evento->guardar();

                //Almacenar el registro
                $datos =[
                    'evento_id' => (int) $evento->id,
                    'registro_id' => (int) $registro->id
                ];

                //lo guardo en la nueva la tabla de eventosRegistros
                $registro_usuario = new EventosRegistros($datos);
                $resultado = $registro_usuario->guardar(); 
            }

            //Almacenar el Regalo
            $registro->sincronizar(['regalo_id' => $_POST['regalo_id']]);
            $resultado = $registro->guardar();

            if($resultado){
                echo json_encode([
                    'resultado' => $resultado, 
                    'token' => $registro->token
                ]);
            } else {
                echo json_encode(['resultado' => false]);
            }

            return;
        }


        $router->render('registro/conferencias', [
            'titulo' => 'Elige Workshops y Conferencias',
            'eventos'=> $eventos_formateados,
            'regalos' => $regalos  
            
        ]);
    }

}    