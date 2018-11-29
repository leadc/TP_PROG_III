<?php
require_once 'clases/entidades/class.autentificador_jwt.php';
require_once 'clases/entidades_api/api.empleado.php';

class MW_Autentificador{
    public static function VerificarAccesoLogin($request, $response, $next){
        $token = $request->getHeaders();
        $resp = new stdclass();
        if(isset($token['HTTP_ACCEPT'][0])){
            $nuevo_token = AutentificadorJWT::VerificarToken($token['HTTP_ACCEPT'][0]);
            if($nuevo_token != false){
                $resp->token = $nuevo_token;
                $resp->respuesta = "Bienvenido";
                return $response->withJson($resp, 200);
            }
        }
        $respuesta = $next($request,$response);
        
        if($respuesta->getStatusCode() == 200){
            $resp->token = AutentificadorJWT::CrearToken(json_decode($respuesta->getBody()->__toString()));
            $resp->respuesta = json_decode($respuesta->getBody()->__toString());
            $respuesta = $respuesta->withJson($resp);
        }
        return $respuesta;
    }
    public static function VerificarAccesoGeneral($request, $response, $next){
        $token = $request->getHeaders();
        $resp = new stdclass();
        if(isset($token['HTTP_ACCEPT'][0])){
            $nuevo_token = AutentificadorJWT::VerificarToken($token['HTTP_ACCEPT'][0]);
            if($nuevo_token != false){
                $resp->token = $nuevo_token;
                $respuesta = $next($request,$response);
                $resp->respuesta = json_decode($respuesta->getBody()->__toString());
                return $respuesta->withJson($resp);
            }
            $resp->error = "Su sesión expiró";
            return $response->withJson($resp, 401);
        }
        $resp->error = "No se encuentra logueado";
        return $response->withJson($resp, 401);
    }
}
?>