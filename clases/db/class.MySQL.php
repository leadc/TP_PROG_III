<?php
/*Clase no instanciable, genera una conexión a una base de datos Oracle para hacer consultas
 * Para Conectarse se debe configurar una conexión ODBC en el sistema con el nombre del $dsn
 *Se pueden setear sus atributos estáticos para seleccionar a qué base de datos conectarse
 USANDO PDO
 */
class DB
{
    public static $dsn = 'mysql:host=localhost;dbname=db_restoapp';
    public static $usuario = 'root';
    public static $clave = '';
    private static $ObjetoAccesoDatos;
    /**Ejecuta una consulta en la base de datos conectada y devuelve el resultado como un array asociativo
     * 
     */
    public static function GetResultados($sql, $abrirConexion = true)
    {
        if ($abrirConexion) {
            self::AbrirConexion();
        }
        $consulta = self::$ObjetoAccesoDatos->prepare($sql);
        $consulta->execute();
        return self::ResultToObjectArray($consulta->fetchall());
    }

    /**Devuelve un recurso para ejecutar una consulta en una base de datos
     * 
     */
    public static function RetornarConsulta($sql, $abrirConexion = true)
    {
        if ($abrirConexion) {
            self::AbrirConexion();
        }
        return self::$ObjetoAccesoDatos->prepare($sql);
    }
    /** Ejecuta una consulta en la base de datos y devuelve el nro de filas afectadas
     * 
     */
    public static function ExecNonQuery($sql, $abrirConexion = true)
    {
        if ($abrirConexion) {
            self::AbrirConexion();
        }
        return self::$ObjetoAccesoDatos->exec($sql);
    }
    /**Devuelve el último ID insertado
     * 
     */
    public static function RetornarUltimoIdInsertado()
    {
        return self::$ObjetoAccesoDatos->lastInsertId();
    }
    
    public static function MaxId($campo, $tabla){
        $sql_ultimo_insertado = "SELECT MAX($campo) AS id FROM $tabla";
        
        $id = ConecxionPDO::GetResultados($sql_ultimo_insertado);
        if(count($id)>0){
            return $id[0]->id;
        }
        return false;
    }
    /**Establece una conexión según los parámetros de dsn, usuario y clave
     * 
     */
    public static function AbrirConexion()
    {
        self::$ObjetoAccesoDatos = new PDO(self::$dsn, self::$usuario, self::$clave);
        self::$ObjetoAccesoDatos->exec("SET CHARACTER SET utf8");	
        self::$ObjetoAccesoDatos->exec("ALTER SESSION SET nls_date_format='yyyy-mm-dd'");
    }
    /**Evita que el objeto se pueda clonar
     * 
     */
    public function __clone()
    {
        trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR);
    }
    /** Crea un objeto asociativo a partir de un elemento en un array de consulta a la base de datos
     * 
     */
    public static function ArrayToObject($array){
        $object = new stdClass();
        foreach ($array as $clave => $valor){
            if(!is_numeric($clave)){
                $clave = strtolower($clave);
                $valor = $valor;
                $object->$clave = $valor;
            }
        }
        return $object;
    }
    /** ResultToObjectArray
     * 
     */
    public static function ResultToObjectArray($array){
        $obj_array = [];
        for($i = 0; $i < count($array);$i++){
            $object = new stdClass();
            foreach ($array[$i] as $clave => $valor){                
                if(!is_numeric($clave)){
                    $clave = utf8_encode(strtolower($clave));
                    $valor = utf8_encode($valor);
                    $object->$clave = $valor;
                }
            }
            array_push($obj_array, $object);
        }
        return $obj_array;
    }
}
?>