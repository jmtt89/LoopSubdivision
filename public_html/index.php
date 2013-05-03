<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
Design by Free CSS Templates
http://www.freecsstemplates.org
Released for free under a Creative Commons Attribution 2.5 License

Name       : Open Tools 
Description: A two-column, fixed-width design with dark color scheme.
Version    : 1.0
Released   : 20130803

-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700|Archivo+Narrow:400,700" rel="stylesheet" type="text/css"/>
<link href="./Assets/Styles/default.css" rel="stylesheet" type="text/css" media="all" />
</head>
<body>
<div id="header" class="container">
	<div id="logo">
		<h1><a href="#">LoopSubdivision</a></h1>
	</div>
	<div id="menu">
		<ul>
			<li class="active"><a href="./index.php" accesskey="1" title="">Principal</a></li>
			<li><a href="./Assets/Archivos/InformeProyectoWebGL.pdf" accesskey="2" title="">Informe</a></li>
			<li><a href="./Credits.html" accesskey="3" title="">Creditos</a></li>
		</ul>
	</div>
</div>
<div id="page" class="container">
	<div id="content">
		<div id="onecolumn">
			<h2>Modelos Precargados</h2>
            <form id="Previos" action="Visualizador.php" method="post">
                <select name="Modelo">
                <?php
                    //ruta a la carpeta, '.' es carpeta actual
                    $path="./Assets/Models";
                    $no_mostrar=Array("","php",".","..");
                    $dir_handle = @opendir($path) or die("No se pudo abrir $path");
                    while ($file = readdir($dir_handle)) {
                        $pos=strrpos($file,".");
                        $extension=substr($file,$pos);
                        if (!in_array($extension, $no_mostrar)) {
                            echo '<option value="'.$path."/".$file.'">'.$file.'</option>'; 
                        }
                    }
                    closedir($dir_handle);
                ?>
                </select>
                <input type="submit" value="Cargar">
            </form>
		</div>
		<div id="two-column">
			<div class="box-content">
				<h2 class="title title01">Carga Tu propio Modelo</h2>
				<form id="Nuevos" action="Upload.php" method="post" enctype="multipart/form-data">
                <input name="userfile" type="file">
                <input type="submit" value="Cargar">
            </form>
			</div>
		</div>
	</div>
	<div id="sidebar">
		<div id="sbox1">
			<h2>Descripcion</h2>
			<ul class="list-style1">
				<li class="first">
					<p>Proyecto para Computacion Grafica 2 que busca mostrar la aplicacion de la tecnica de sudivision Loop para Modelos Triangulizados implementada en WebGL</p>
				</li>
				<li>
					<p>Solo Admite Formatos Obj y Json</p>
				</li>
				<li>
					<p>Si el modelo Cargado no esta Triangulizado, la Aplicacion se encarga de Triangulizarlo al Cargarlo</p>
				</li>				
			</ul>
		</div>
	</div>
</div>
<div id="footer" class="container">

</div>
<div id="copyright" class="container">
</div>
</body>
</html>
