<?php
require_once 'clases/db/class.MySQL.php';
require_once 'clases/entidades/class.log.php';

/** Clase instanciable usada para el manejo de empleados
 * Métodos utilizables: 
 *  - GetEmpleadoByID
 *  - GetEmpleadoByNombre
 *  - VerificarClave
 */
class Empleado{
    public $id_empleado;
    public $nombre;
    public $apellido;
    public $mail;
    public $domicilio;
    public $login;
    private $clave;
    public $ruta_foto;
    public $perfil;
    public $activo;

    /** Empleado
     * Constructor de empleado según id, nombre y clave
     */
    public function __construct($id ="", $nombre ="", $clave =""){
        if($id != "" && $nombre == "" && $clave == ""){
            self::GetEmpleadoByID($id);
        }else{
            $this->id_empleado = $id;
            $this->nombre = $nombre;
            $this->clave = $clave;
        }
    }

    /** ValidarEmpleado
     * Valida los datos de un empleado pasado por parámetro
     * Devuelve false en caso de error y setea la variable mensaje con el mensaje correspondiente
     */
    public static function ValidarEmpleado($empleado, &$mensaje){
        $mensaje = '';

        //Valido el id de empleado
        if(isset($empleado->id_empleado) && !is_numeric($empleado->id_empleado)){
            $mensaje = 'Id de empleado inválido.';
            return false;
        }

        //Valido el nombre
        if(!isset($empleado->nombre) || !self::StringCargado($empleado->nombre)){
            $mensaje = 'Falta el nombre del empleado.';
            return false;
        }
        //Valido el apellido
        if(!isset($empleado->apellido) || !self::StringCargado($empleado->apellido)){
            $mensaje = 'Falta el apellido del empleado.';
            return false;
        }
        //Valido el mail
        if(!isset($empleado->mail) || !self::ValidarMail($empleado->mail)){
            $mensaje = 'Debe enviar un mail válido para el empleado.';
            return false;
        }
        //Valido el domicilio
        if(!isset($empleado->domicilio) || !self::StringCargado($empleado->domicilio)){
            $mensaje = 'Falta el domicilio del empleado.';
            return false;
        }
        //Valido el login (cargado)
        if(!isset($empleado->login) || !self::StringCargado($empleado->login)){
            $mensaje = 'Falta el login del empleado.';
            return false;
        }
        //Valido el login (usado)
        if(isset($empleado->id_empleado)){
            if(!self::ValidarLoginExistente($empleado->login, $empleado->id_empleado)){
                $mensaje = 'El login seleccionado no está disponible.';
                return false;
            }
        }else{
            if(!self::ValidarLoginExistente($empleado->login)){
                $mensaje = 'El login seleccionado no está disponible.';
                return false;
            }
        }
        //Valido la clave
        if(!isset($empleado->clave) || !self::StringCargado($empleado->clave)){
            $mensaje = 'Falta la clave del empleado.';
            return false;
        }
        //Valido la ruta_foto
        if(!isset($empleado->ruta_foto) || !self::StringCargado($empleado->ruta_foto)){
            $mensaje = 'Falta la ruta de la foto del empleado.';
            return false;
        }
        //Valido el perfil
        if(!isset($empleado->perfil) || !self::StringCargado($empleado->perfil)){
            $mensaje = 'Falta el perfil del empleado.';
            return false;
        }
        //Valido si es activo
        if(!isset($empleado->activo) || ($empleado->activo != 'S' && $empleado->activo != 'N') ){
            $mensaje = 'Falta flag de empleado activo (S o N).';
            return false;
        }
        return true;
    }

    /** StringCargado
     * Verifica que el parámetro string sea un string y tenga un largo mayor a cero
     */
    private static function StringCargado($string){
        return (is_string($string) && (strlen($string) > 0));
    }

    /** ValidarMail
     * Valida el mail pasado por parámetro y devuelve true en caso que sea correcto
     */
    private static function ValidarMail($mail){
        return (false !== filter_var($mail, FILTER_VALIDATE_EMAIL));
    }

    /** ValidarLoginExistente
     * Verifica si un login ya existe en la base de datos
     * El parámetro id_empleado se usa para especificar el id del empleado cuando este exite
     * de manera de omitirlo si ya está cargado en la base de datos
     */
    public static function ValidarLoginExistente($login,$id_empleado = null){
        try{
            $sql = "SELECT id_empleado FROM empleados WHERE login = '$login'";
            if($id_empleado != null){
                $sql = $sql." AND id_empleado != '$id_empleado'";
            }
            $coincidencias = DB::GetResultados($sql);
            if(count($coincidencias)>0){
                return false;
            }
            return true;
        }catch(Exception $e){
            return false;
        }
    }

    /** AltaEmpleado
     * Da de alta un empleado pasado por parámetro en la tabla empleados
     * Devuelve false en caso de error o el id de empleado
     */
    public static function AltaEmpleado($empleado){
        $sql = "INSERT INTO Empleados (nombre, apellido, mail, domicilio, login, clave, ruta_foto, perfil, activo) 
                VALUES ('$empleado->nombre','$empleado->apellido','$empleado->mail', '$empleado->domicilio',
                '$empleado->login','$empleado->clave', '$empleado->ruta_foto', '$empleado->perfil', '$empleado->activo')";
        try{
            if( DB::ExecNonQuery($sql) > 0){
                return DB::RetornarUltimoIdInsertado();
            }
            Log::Escribir(["AltaEmpleado - No se pudo guardar", $sql, json_encode($empleado)]);
        }catch(Exception $e){
            Log::Escribir(["AltaEmpleado - Error al guardar", $sql, $e->getMessage()]);
        }
        return false;
    }

    /** BajaEmpleado
     * Da de baja un empleado en la base de datos eliminandolo de la tabla empleados según su id
     */
    public static function BajaEmepleado($id_empleado){
        $sql = "DELETE FROM Empleados WHERE id_empleado = $id_empleado";
        try{
            if( DB::ExecNonQuery($sql) > 0){
                return true;
            }
            Log::Escribir(["BajaEmpleado - No se eliminó ningún empleado", $sql]);
        }catch(Exception $e){
            Log::Escribir(["BajaEmpleado - Error aleliminar empleado", $sql, $e->getMessage()]);
        }
        return false;
    }

    /** ModificarEmpleado
     * Modifica un empleado pasado por parámetro según su id de usuario
     * Devuelve 1 cuando se modificó el empleado, 0 cuando no se modificó nada, -1 cuando se produjo un error al modificar
     */
    public static function ModificarEmpleado($empleado){
        $sql = "UPDATE Empleados SET nombre = '$empleado->nombre', apellido = '$empleado->apellido', 
                mail = '$empleado->mail', domicilio = '$empleado->domicilio', login = '$empleado->login', 
                clave = '$empleado->clave', ruta_foto = '$empleado->ruta_foto', perfil = '$empleado->perfil',
                activo = '$empleado->activo' WHERE id_empleado = $empleado->id_empleado";
        try{
            if( DB::ExecNonQuery($sql) > 0){
                return 1;
            }
            Log::Escribir(["ModificarEmpleado - No se modificó ningún empleado", $sql, json_encode($empleado)]);
            return 0;
        }catch(Exception $e){
            Log::Escribir(["ModificarEmpleado - Error al modificar empleado", $sql, $e->getMessage()]);
            return -1;
        }
    }

    /** GetEmpleadoByID
     * Obtiene los datos de un empleado según su ID
     * Devuelve true en caso de éxito, false en caso de no encontrar coincidencias
     */
    public function GetEmpleadoByID($id){
        return $this->GetEmpleadoBy("id_empleado",$id);
    }

    /** GetEmpleadoByNombre
     * Obtiene los datos de un empleado según su Nombre
     * Devuelve true en caso de éxito, false en caso de no encontrar coincidencias
     */
    public function GetEmpleadoByNombre($nombre){
        return $this->GetEmpleadoBy("nombre",$nombre);
    }

    /** GetEmpleadoByLogin
     * Obtiene los datos de un empleado según su Nombre
     * Devuelve true en caso de éxito, false en caso de no encontrar coincidencias
     */
    public function GetEmpleadoByLogin($login){
        return $this->GetEmpleadoBy("login_empleado",$login);
    }

    /** GetempleadoBy
     * Obtiene los datos de un Empleado según la clave y el valor pasados por parámetro
     * Devuelve true en caso de éxito, false en caso de no encontrar coincidencias
     */
    private function GetEmpleadoBy($clave, $valor){
        $resultado = DB::GetResultados("SELECT 
                                        id_empleado,
                                        nombre,
                                        apellido,
                                        mail,
                                        domicilio,
                                        login,
                                        clave,
                                        ruta_foto,
                                        perfil,
                                        activo
                                        from Empleados
                                        where ".$clave." = '".$valor."'");
        if(count($resultado) > 0){
            $this->id_empleado = $resultado[0]->id_empleado;
            $this->nombre = $resultado[0]->nombre;
            $this->apellido = $resultado[0]->apellido;
            $this->mail = $resultado[0]->mail;
            $this->domicilio = $resultado[0]->domicilio;
            $this->clave = $resultado[0]->clave;
            $this->login = $resultado[0]->login;
            $this->ruta_foto = $resultado[0]->ruta_foto;
            $this->perfil = $resultado[0]->perfil;
            $this->activo = $resultado[0]->activo;
            return true;
        }else{
            return false;
        }
    }

    /** VerificarClave
     * Compara una clave pasada por parámetro con la guardada en la clave del empleado
     * retorna true en caso de que sea correcta o false de no serlo
     */
    public function VerificarClave($clave){
        if($this->clave != $clave){
            return false;
        }
        return true;
    }

    /** CodificarClave
     * Devuelve una clave codificada correspondiente a la clave pasada por parámetro
     */
    private function CodificarClave($clave){
        $codigo = [];
        for ($i = 0; $i<strlen($clave);$i++){
            $caracter = chr(ord($clave[$i]) ^ ( (($i+1) + $this->id) % 32 ));
            array_push($codigo, $caracter);
        }
        return implode($codigo);
    }

    /** ModificarClaveEmpleado
     * Modifica la clave del empleado actual en la base de datos
     */
    public function ModificarClaveEmpleado($clave_nueva){
        if($this->id_empleado != "" and isset($clave_nueva)){
            if(ConecxionPDO::ExecNonQuery("update Empleados set clave = '".$clave_nueva."' where id_empleado = ".$this->id)){
                return true;
            }
        }
        return false;
    }
}