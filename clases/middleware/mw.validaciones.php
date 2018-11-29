<?php
require_once 'clases/entidades/class.autentificador_jwt.php';
require_once 'clases/entidades/class.empleado.php';

class MW_Validaciones{

    /** ValidarEmpleado
     * Middleware para validación de empleado
     */
    public static function ValidarEmpleado($request, $response, $next){
        $empleado = (object)$request->getParsedBody();
        $mensaje_error = '';
        if(Empleado::ValidarEmpleado($empleado, $mensaje_error) === false){
            return $response->withJson($mensaje_error, 401, JSON_UNESCAPED_UNICODE);
        }
        return $next($request,$response);
    }
}
?>