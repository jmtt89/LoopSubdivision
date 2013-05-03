<?php
/*
    Version Original:
        Three.js "tutorials by example"
        Author: Lee Stemkoski
        Date: March 2013 (three.js v56)
    
    Modificacion Realizada por:
        Jesus Torres
        Alfredo Gallardo
        para la materia Graficas II
        20/03/2013
        Proyecto
    
 */

    $Path = $_POST['Modelo'];
    $aux = explode("/",$Path);
    $name = $aux[count($aux)-1];
    $aux2 = explode(".", $name);
    $ext = $aux2[1];
    
?>
<!doctype html>
<html lang="es">
<head>
	<title>Visualizador</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
	<style>
            body {
                font-family: Monospace;
                font-weight: bold;
                background-color: #ccccff;
                margin: 0px;
                overflow: hidden;}
            .lbl { 
                color:#fff; 
                font-size:16px; 
                font-weight:bold; 
                position: absolute; 
                bottom:0px; 
                z-index:100; 
                text-shadow:#000 1px 1px 1px; 
                background-color:rgba(0,0,0,0.85); 
                padding:1em }
            
            #right { text-align:left; 
                     right:0px }
            
            #info {
                color:#fff;
                text-shadow:#000 1px 1px 1px; 
                background-color:rgba(0,0,0,0.85); 
                position: absolute;
                top: 0px; width: 300px;
                padding: 5px;
                z-index:100; }
	</style>
</head>
<body>

<div id="info">
    Modelo Cargado <?php echo $name; ?><br/>
    Para Proximo nivel de Subdivision presionar Flecha Arriba en Teclado<br/>
    Para Nivel Previo Flecha Abajo<br/>
</div>

<div id="right" class="lbl">
    Stats <br/>
    Nivel:    <span id="N">0</span><br/>
    Caras:    <span id="C">0</span><br/>
    Vertices: <span id="V">0</span><br/>
    <br/>
</div>

<script type='text/javascript' src="Assets/JavaScripts/Three.js"></script>
<script type='text/javascript' src="Assets/JavaScripts/Detector.js"></script>
<script type='text/javascript' src="Assets/JavaScripts/Stats.js"></script>
<script type='text/javascript' src="Assets/JavaScripts/TrackballControls.js"></script>
<script type='text/javascript' src="Assets/JavaScripts/THREEx.KeyboardState.js"></script>
<script type='text/javascript' src="Assets/JavaScripts/THREEx.FullScreen.js"></script>
<script type='text/javascript' src="Assets/JavaScripts/THREEx.WindowResize.js"></script>
<script type='text/javascript' src="Assets/JavaScripts/OBJLoader.js"></script>
<script type='text/javascript' src="Assets/JavaScripts/Loop.js"></script>
<script type='text/javascript' src="Assets/JavaScripts/OBJ.js"></script>
<script type='text/javascript' src="Assets/JavaScripts/TessellateModifier.js"></script>
<script type='text/javascript' src='Assets/JavaScripts/DAT.GUI.min.js'></script>


<script>

// MAIN
if ( ! Detector.webgl ) 
    Detector.addGetWebGLMessage();

// standard global variables
var container, scene, camera, renderer, controls, stats;
var keyboard = new THREEx.KeyboardState();
var clock = new THREE.Clock();

// custom global variables
var Model;
var PrevMod = [];
var projector, mouse = { x: 0, y: 0 }, INTERSECTED, FACE;
var sprite1;
var canvas1, context1, texture1;
var parameters, gui;
var tessellateModifier = new THREE.TessellateModifier( 4 );
var loopDivision;
var Triangle;

var Objeto3D;

init();
animate();

// FUNCTIONS 		
function init(){
    
    // SCENE
    scene = new THREE.Scene();
    
    // CAMERA
    var SCREEN_WIDTH = window.innerWidth, SCREEN_HEIGHT = window.innerHeight;
    var VIEW_ANGLE = 45, ASPECT = SCREEN_WIDTH / SCREEN_HEIGHT, NEAR = 0.1, FAR = 20000;
    camera = new THREE.PerspectiveCamera( VIEW_ANGLE, ASPECT, NEAR, FAR);
    scene.add(camera);
    camera.position.set(0,150,400);
    camera.lookAt(scene.position);
    
    // RENDERER
    renderer = new THREE.WebGLRenderer( {antialias:true} );
    renderer.setSize(SCREEN_WIDTH, SCREEN_HEIGHT);
    container = document.createElement( 'div' );
    document.body.appendChild( container );
    container.appendChild( renderer.domElement );
    
    // EVENTS
    THREEx.WindowResize(renderer, camera);
    THREEx.FullScreen.bindKey({ charCode : 'm'.charCodeAt(0) });
    document.addEventListener( 'mousemove', onDocumentMouseMove, false );
    
    // CONTROLS
    controls = new THREE.TrackballControls( camera, renderer.domElement );
    
    // STATS
    stats = new Stats();
    stats.domElement.style.position = 'absolute';
    stats.domElement.style.bottom = '0px';
    stats.domElement.style.zIndex = 100;
    container.appendChild( stats.domElement );
    
    // LIGHT
    var light = new THREE.PointLight(0xffffff);
    light.position.set(0,250,0);
    scene.add(light);

    //AXES
    function v(x,y,z){ 
        return new THREE.Vertex(new THREE.Vector3(x,y,z)); 
    }

    scene.add( new THREE.AxisHelper(500) );
    
    //Cargar Geometrias
    var ext = "<?php echo $ext; ?>";
    var path = "<?php echo $Path; ?>";
    var Loader = null;
    if(ext ==="obj" || ext ==="OBJ" || ext ==="Obj")
        Loader = new THREE.OBJLoader();
    else
        Loader = new THREE.JSONLoader();
    
    Loader.load(path,LoadOBJ);
    
    //PICKING
    addTriangle();
    
    //GUI

    gui = new dat.GUI();

    parameters = 
    {
        Load: function() { location.href="./index.php"; },
        Reset: function() { alert("Hello!"); },
        wire:true,
        back:true
    };

    var check = gui.add( parameters, 'wire' ).name('Wireframe?').listen();
    check.onChange(function(value){ ChangeWire(value);  });        

    var check2 = gui.add( parameters, 'back' ).name('Base? ').listen();
    check2.onChange(function(value){ ChangeBack(value);  });        

    gui.add( parameters, 'Load' ).name('Otro Modelo');
    //gui.add( parameters, 'Reset' ).name('Resetear Camara');
    gui.open();

    // Seleccion
    // initialize object to perform world/screen calculations
    projector = new THREE.Projector();
    
}

function LoadOBJ(geometry,materials){
    materials;
    tessellateModifier.divide(geometry);
    loopDivision = new THREE.LoopDivision(geometry);
    loopDivision.prepare(geometry);
    Model = geometry;
    addOBJToScene();
}


//Color Base del OBJ
function ChangeBack(value){      
    Objeto3D.children[0].material.visible = value;
}

//Wireframe del OBJ
function ChangeWire(value){       
    Objeto3D.children[1].material.wireframe = value;
    Objeto3D.children[1].material.visible = value;
}

function addTriangle(){
    var TriangleMaterialArray = [];
    TriangleMaterialArray.push( new THREE.MeshBasicMaterial( { color: 0xff00ff } ) );
	
    var TriangleMaterials = new THREE.MeshFaceMaterial( TriangleMaterialArray );
	
    var geometry = new THREE.Geometry();

    geometry.vertices[0] = new THREE.Vector3( 1, 0, 0 );
    geometry.vertices[1] = new THREE.Vector3( 0, 1, 0 );
    geometry.vertices[2] = new THREE.Vector3( 0, 0, 1 );
    geometry.faces[0] = new THREE.Face3(0,1,2,new THREE.Vector3( 0, 1, 0 ),new THREE.Color( 0x00ff00 ));
	
    Triangle = new THREE.Mesh( geometry, TriangleMaterials );
    Triangle.name = "pick";
    Triangle.type = "OBJ";
    Triangle.Valid = true;
    scene.add(Triangle);
}


function addOBJToScene(){
    
    //var Texture = new THREE.ImageUtils.loadTexture( 'images/txture.jpg' );
    //var Material = new THREE.MeshBasicMaterial( { map: Texture } );
    var Material = new THREE.MeshBasicMaterial( { color: 0x000088 } );
    var wireframeMaterial = new THREE.MeshBasicMaterial( { color: 0x00ff00, wireframe: true, transparent: true } ); 
    var multiMaterial = [ Material, wireframeMaterial ];
    
    var geometry = null;
    if(Model.ActLvl > Model.SubLvl)
        geometry = PrevMod[Model.SubLvl].clone();
    else{
        geometry = Model.clone();
        PrevMod.push(geometry);
    }
    geometry.SubLvl = Model.SubLvl;
    geometry.dynamic = Model.dynamic;            
    Objeto3D = THREE.SceneUtils.createMultiMaterialObject( geometry, multiMaterial );
    scene.add(Objeto3D);
}


function onDocumentMouseMove( event ){
	// update the mouse variable
	mouse.x = ( event.clientX / window.innerWidth ) * 2 - 1;
	mouse.y = - ( event.clientY / window.innerHeight ) * 2 + 1;
}

function animate(){
    requestAnimationFrame( animate );
    render();
    if(Objeto3D === undefined)
        updateLoading();
    else
        update();
}

var Lock;

function update(){
    document.getElementById('N').innerHTML = ""+Objeto3D.children[0].geometry.SubLvl;
    document.getElementById('C').innerHTML = ""+Objeto3D.children[0].geometry.faces.length;
    document.getElementById('V').innerHTML = ""+Objeto3D.children[0].geometry.vertices.length;
    Triangle.visible = false;
    
    if(!keyboard.pressed("up") && !keyboard.pressed("down"))
            Lock = false;

	if ( keyboard.pressed("up") && !Lock ) 
	{ 
            scene.remove(Objeto3D);
            loopDivision.next(Model);
            addOBJToScene();           
            Lock = true;
        }
        
	if ( keyboard.pressed("down") && !Lock ) 
	{ 
            scene.remove(Objeto3D);
            loopDivision.prev(Model);
            addOBJToScene();             
            Lock = true;
	}

        // Crea un Rayo con origen en el mouse y direccion de la camara
	var vector = new THREE.Vector3( mouse.x, mouse.y, 1 );
	projector.unprojectVector( vector, camera );
	var ray = new THREE.Raycaster( camera.position, vector.sub( camera.position ).normalize() );

	// Arreglo con todos los Objetos de la Scena que intersecta
	var intersects = ray.intersectObjects( scene.children );
	
        if ( intersects.length > 0 )
        {
            if ( intersects[ 0 ].object !== INTERSECTED ){                    
                INTERSECTED = intersects[ 0 ].object;
            }else if( intersects[ 0 ].face !== FACE ){
                Triangle.visible = false;
                FACE = intersects[ 0 ].face;
                
                Triangle.geometry.vertices[0] = INTERSECTED.geometry.vertices[intersects[ 0 ].face.a].clone();
                Triangle.geometry.vertices[1] = INTERSECTED.geometry.vertices[intersects[ 0 ].face.b].clone();
                Triangle.geometry.vertices[2] = INTERSECTED.geometry.vertices[intersects[ 0 ].face.c].clone();
                Triangle.geometry.faces[0] = new THREE.Face3(0,1,2,intersects[ 0 ].face.normal.clone(),intersects[ 0 ].face.color.clone());
                
                Triangle.geometry.faces[0].centroid = intersects[ 0 ].face.centroid.clone();
                Triangle.geometry.faces[0].vertexNormals[0] = intersects[ 0 ].face.vertexNormals[0];
                Triangle.geometry.faces[0].vertexNormals[1] = intersects[ 0 ].face.vertexNormals[1];
                Triangle.geometry.faces[0].vertexNormals[2] = intersects[ 0 ].face.vertexNormals[2];

                Triangle.geometry.computeCentroids();
                Triangle.geometry.computeFaceNormals();
                Triangle.geometry.computeVertexNormals();


                Triangle.geometry.verticesNeedUpdate= true;
                Triangle.geometry.normalsNeedUpdate= true;
                Triangle.geometry.elementsNeedUpdate=true;
            }else
                Triangle.visible = true;			
			
        }
	else{ // No hay interseccion
            Triangle.visible = false;
            INTERSECTED = null;
            FACE = null;
	}
    
	controls.update();
	stats.update();
}

function updateLoading(){
    controls.update();
    stats.update();
}

function render(){
    renderer.render( scene, camera );
}

</script>

</body>
</html>
