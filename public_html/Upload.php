<?php
$nombre_archivo = $_FILES['userfile']['name'];

$aux2 = explode(".", $nombre_archivo);
$ext = $aux2[1];

if(!($ext != "Obj" || $ext != "OBJ" || $ext != "obj" || $ext != "JSON" ||$ext != "Json" ||$ext != "json"))
{
    echo "Formato Invalido, Debe ser Un Obj o un Json";
    echo '<a href="./index.php">Regresar</a>';
}

$directorio_definitivo = "./Assets/Models/";
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $directorio_definitivo.$nombre_archivo)){
       echo "El archivo ha sido cargado correctamente.";
       $_POST['Modelo'] = $directorio_definitivo.$nombre_archivo;
       header('Location: Visualizador.php');
    }else{
       echo "Ocurrió algún error al subir el fichero. No pudo guardarse.";
       echo '<a href="./index.php">Regresar</a>';
    }
    
?> 