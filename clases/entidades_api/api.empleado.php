<?php
    require_once 'clases/entidades/class.empleado.php';
    require_once 'clases/entidades/class.log.php';

    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    /** API_Empleado
     * Clase para el manejo de empleados desde el servidor WEB
     */
    class API_Empleado{
        
        /** Login
         * Código Necesario para realizar el login de un empleado 
         * Retornará los datos del empleado si la clave y el nombre recibidos son correctos
         * Retornará un error 401 de autenticación en caso de que la clave y el empleado sean incorrectos o el empleado no exista
         * Retornará un error 400 en caso de que no se reciba login de usuario o clave
         * Retornará un error 500 en caso de que se produzca algún error (Conexión a base de datos o de otro tipo)
         */
        public static function Login($request, $response){
            $respuesta = new stdclass();
            try{
                $datos_recibidos = (object)$request->getParsedBody();
                /*$file = fopen("c:/log.txt","a");
                fwrite($file,"\r\nDENTRO DE LA FUNC LOGIN: ".json_encode($datos_recibidos) ."\r\n");
                fclose($file);*/
                if(isset($datos_recibidos->clave) and isset($datos_recibidos->nombre)){
                    //Datos recibidos por request
                    $nombre_empleado = $datos_recibidos->nombre;
                    $clave_empleado = $datos_recibidos->clave;
                    
                    //Verifico que el empleado exista en la base de datos con GetEmpleadoByNombre
                    $empleado = new Empleado();
                    if($empleado->GetEmpleadoByLogin($nombre_empleado)){
                        //El empleado existe
                        
                        if($empleado->VerificarClave($clave_empleado)){
                            //La clave es correcta
                            return $response->withJson($empleado, 200, JSON_UNESCAPED_UNICODE);
                        }else{
                            //La clave no es correcta
                            $respuesta->error = "Usuario o clave incorrectos";
                            return $response->withJson($respuesta, 401, JSON_UNESCAPED_UNICODE);
                        }
                    }else{
                        //El empleado no existe
                        $respuesta->error = "Usuario inexistente";
                        return $response->withJson($respuesta, 401, JSON_UNESCAPED_UNICODE);
                    }
                }else{
                    //Los parámetros no son correctos
                    $respuesta->error = "Parametros recibidos incorrectos";
                    return $response->withJson($respuesta, 400, JSON_UNESCAPED_UNICODE);
                }
            }catch(Exception $e){
                $respuesta->error = "Error interno de base de datos: ".$e->getMessage();
                return $response->withJson($respuesta, 500, JSON_UNESCAPED_UNICODE);
            }
        }

        /** AltaEmpleado
         * Realiza el alta de un empleado en la base de datos
         * Recibe los datos de un empleadoy devuelve los mismos sumando el id de empelado generado
         * Devuelve un mensaje de error en caso de falla
         */
        public static function AltaEmpleado(Request $request, Response $response){
            $empleado = (object)$request->getParsedBody();
            $id_empleado = Empleado::AltaEmpleado($empleado);
            if($id_empleado === false){
                return $response->withJson('Error al guardar empleado', 500, JSON_UNESCAPED_UNICODE);
            }
            $empleado->id_empleado = $id_empleado;
            return $response->withJson($empleado, 200, JSON_UNESCAPED_UNICODE);
        }
        
        /** BajaEmpleado
         * [POST]
         * Da de baja un empleado según el id pasado por parámetro
         */
        public static function BajaEmpleado(Request $request, Response $response){
            $empleado = (object)$request->getParsedBody();
            if(!Empleado::BajaEmepleado($empleado->id_empleado)){
                return $response->withJson('Error al eliminar empleado', 500, JSON_UNESCAPED_UNICODE);
            }
            return $response->withJson('Empleado eliminado',200, JSON_UNESCAPED_UNICODE);
        }

        /** ModificarEmpleado
         * [POST]
         * Modifica un empleado según los datos recibidos
         */
        public static function ModificarEmpleado(Request $request, Response $response){
            $empleado = (object)$request->getParsedBody();
            $resultado = Empleado::ModificarEmpleado($empleado);
            if($resultado < 0){
                return $response->withJson('Error al modificar empleado', 500, JSON_UNESCAPED_UNICODE);
            }else{
                return $response->withJson($empleado,200, JSON_UNESCAPED_UNICODE);
            }
        }
    }
?>