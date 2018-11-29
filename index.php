<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

header('Content-Type: application/json; charset=utf-8');

require 'vendor/autoload.php';

require 'clases/entidades_api/api.empleado.php';

require_once 'clases/middleware/mw.autentificador.php';
require_once 'clases/middleware/mw.validaciones.php';

$config['displayErrorDetails'] = true; //para obtener información sobre los errores
$config['addContentLengthHeader'] = false;  //permite al servidor web establecer el encabezado Content-Length, lo que hace que Slim se comporte de manera más predecible

$app = new \Slim\App(["settings" => $config]);

//Login
$app->group('/login', function(){
    // Revive una sesión
    $this->get('/checkLogin',function($request, $response, $args){
        return $response->withJson('Responder si está o no creado el usuario de adm',200, JSON_UNESCAPED_UNICODE);
    })->add(\MW_Autentificador::class . ':VerificarAccesoGeneral');
    //Realiza el login
    $this->post('/doLogin', function($request, $response, $args){
        return $response->withJson('Responder si se creó con exito el usuario de administrador por primera vez',200, JSON_UNESCAPED_UNICODE);
    })->add(\MW_Autentificador::class . ':VerificarAccesoLogin');
});

//ABM Empleados
$app->group('/empleado', function(){
    //Alta de empleados
    $this->post('/nuevoEmpleado', \API_Empleado::class.':AltaEmpleado')->add(\MW_Validaciones::class.':ValidarEmpleado');
    $this->post('/bajaEmpleado', \API_Empleado::class.':BajaEmpleado');
    $this->post('/modificarEmpleado', \API_Empleado::class.':ModificarEmpleado')->add(\MW_Validaciones::class.':ValidarEmpleado');
});

$app->group('/prueba', function(){
    //Pruebas generales
    $this->get('/',function($req, $res){ echo json_encode(is_string('1') && (strlen('1') > 0)); } );
});

$app->run();

?>