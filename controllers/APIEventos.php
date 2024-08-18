<?php

namespace Controllers;

use Model\EventoHorario;

class APIEventos {
    public static function index(){
        // si no esta presente dia_id entonces dia_id toma un string vacio
        $dia_id = $_GET['dia_id'] ?? ''; 
        $categoria_id = $_GET['categoria_id'] ?? '';
        
        //los tengo que validar en numeros
        $dia_id = filter_var($dia_id,FILTER_VALIDATE_INT);
        $categoria_id = filter_var($categoria_id,FILTER_VALIDATE_INT);

        if(!$dia_id || !$categoria_id){
            echo json_encode([]);
            return;
        }

        //consultar la Base de Datos. le paso las llaves 'dia_id' y 'categoria_id' para filtrar sobre eventos en la BD
        $eventos = EventoHorario::whereArray(['dia_id' => $dia_id, 'categoria_id' => $categoria_id]); 

        echo json_encode($eventos);
    }
}