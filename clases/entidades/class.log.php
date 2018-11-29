<?php

    class Log{
        public static $archivo = "/API_LOG.txt";

        public static function Escribir($lineas){
            $f = fopen(__DIR__ . self::$archivo, "a+");
            for($i=0;$i<count($lineas);$i++){
                fwrite($f, $lineas[$i].PHP_EOL);
            }
            fclose($f);
        }
    }
?>