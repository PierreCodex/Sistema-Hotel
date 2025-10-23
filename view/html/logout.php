<?php 
     session_start();
     require_once("../../config/conexion.php"); 
     $conectar = new Conectar();
     session_destroy(); 
     header("Location:".$conectar->ruta()); 
     exit(); 
 ?>