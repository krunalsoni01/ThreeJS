<!DOCTYPE html>
<html lang="en">
	<head>
		<title>three.js webgl</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
		<style>
			body {
				color: #fff;
				font-family: Monospace;
				font-size: 13px;
				text-align: center;
				font-weight: bold;

				background-color: #000;
				margin: 0px;
				overflow: hidden;
			}

			#info {
				position: absolute;
				padding: 10px;
				width: 100%;
				text-align: center;
				color: #fff;
			}

			a { color: blue; }

		</style>
	</head>
	<body>
		<div id="info">
			<a href="http://threejs.org" target="_blank">three.js</a><br />
			from <a href="https://www.udacity.com/course/interactive-3d-graphics--cs291">Udacity Interactive 3D Graphics</a>
		</div>

		
	<script src="js/three.js"></script>
		<script src="js/OrbitControls.js"></script>
		<script src="js/DDSLoader.js"></script>
		<script src="js/MTLLoader.js"></script>
		<script src="js/OBJLoader.js"></script>

		<script src="js/FlyControls.js"></script>
		<script src="js/Detector.js"></script>
		<script src="js/stats.min.js"></script>

		<script src="js/PointerLockControls.js"></script>

		<script src='js/dat.gui.min.js'></script>

		<script src='js/TeapotBufferGeometry.js'></script>

		<script src="js/tween.min.js"></script>

		<script>

			////////////////////////////////////////////////////////////////////////////////
			////////////////////////////////////////////////////////////////////////////////
			/*global THREE, Detector, container, dat, window */

			if ( ! Detector.webgl ) Detector.addGetWebGLMessage();

			var camera, scene, renderer;
			var cameraControls;
			var effectController;
			
			var ambientLight, light;
			var skybox;

			var tess = -1;	// force initialization
			var bBottom ;
			var bLid;

			var bBody;
			var bFitLid;
			var bNonBlinn;
			var shading;
			var wireMaterial, flatMaterial, gouraudMaterial, phongMaterial, texturedMaterial, reflectiveMaterial;

			var  textureCube;

			//House
			var house,bhouse,houseObject;

			//Cube1
			var cube1,bcube1,cubeObject1,geometry,material;

			//Cube2
			var cube2,bcube2,cubeObject2;

			//Location
			var locationCube,blocationCube,locationCubeObject;


			// allocate these just once
			var diffuseColor = new THREE.Color();
			var specularColor = new THREE.Color();

			init();
			//render();
			animate();

			function animate() {
			  requestAnimationFrame( animate );
			  render();
			 // control.update();
			}

			function init() {

				container = document.createElement( 'div' );
				document.body.appendChild( container );

				var canvasWidth = window.innerWidth;
				var canvasHeight = window.innerHeight;

				// CAMERA
				camera = new THREE.PerspectiveCamera( 45, window.innerWidth / window.innerHeight, 1, 80000 );
				//camera.position.set( -600, 550, 1300 );
				camera.position.set(30,30,30);	//For house

				// LIGHTS
				ambientLight = new THREE.AmbientLight( 0x333333 );	// 0.2

				light = new THREE.DirectionalLight( 0xFFFFFF, 1.0 );
				// direction is set in GUI

				// RENDERER
				renderer = new THREE.WebGLRenderer( { antialias: true } );
				renderer.setClearColor( 0xAAAAAA );
				renderer.setPixelRatio( window.devicePixelRatio );
				renderer.setSize( canvasWidth, canvasHeight );
				renderer.gammaInput = true;
				renderer.gammaOutput = true;
				container.appendChild( renderer.domElement );

				// EVENTS
				window.addEventListener( 'resize', onWindowResize, false );

				// CONTROLS
				cameraControls = new THREE.OrbitControls( camera, renderer.domElement );
				cameraControls.target.set( 0, 0, 0 );
				cameraControls.addEventListener( 'change', render );

				// TEXTURE MAP
				var textureMap = new THREE.TextureLoader().load( 'textures/UV_Grid_Sm.jpg' );
				textureMap.wrapS = textureMap.wrapT = THREE.RepeatWrapping;
				textureMap.anisotropy = 16;

				// REFLECTION MAP
				var path = "textures/cube/skybox/";
				var urls = [
					path + "px.jpg", path + "nx.jpg",
					path + "py.jpg", path + "ny.jpg",
					path + "pz.jpg", path + "nz.jpg"
				];

				textureCube = new THREE.CubeTextureLoader().load( urls );

				// MATERIALS
				var materialColor = new THREE.Color();
				materialColor.setRGB( 1.0, 1.0, 1.0 );

				wireMaterial = new THREE.MeshBasicMaterial( { color: 0xFFFFFF, wireframe: true } ) ;

				flatMaterial = new THREE.MeshPhongMaterial( { color: materialColor, specular: 0x0, shading: THREE.FlatShading, side: THREE.DoubleSide } );

				gouraudMaterial = new THREE.MeshLambertMaterial( { color: materialColor, side: THREE.DoubleSide } );

				phongMaterial = new THREE.MeshPhongMaterial( { color: materialColor, shading: THREE.SmoothShading, side: THREE.DoubleSide } );

				texturedMaterial = new THREE.MeshPhongMaterial( { color: materialColor, map: textureMap, shading: THREE.SmoothShading, side: THREE.DoubleSide } );

				reflectiveMaterial = new THREE.MeshPhongMaterial( { color: materialColor, envMap: textureCube, shading: THREE.SmoothShading, side: THREE.DoubleSide } );

				// scene itself
				scene = new THREE.Scene();

				scene.add( ambientLight );
				scene.add( light );

				// GUI
				setupGui();

			}

			// EVENT HANDLERS

			function onWindowResize() {

				var canvasWidth = window.innerWidth;
				var canvasHeight = window.innerHeight;

				renderer.setSize( canvasWidth, canvasHeight );

				camera.aspect = canvasWidth / canvasHeight;
				camera.updateProjectionMatrix();

				render();

			}

			function setupGui() {

				effectController = {

					shininess: 40.0,
					ka: 0.17,
					kd: 0.51,
					ks: 0.2,
					metallic: true,

					hue:		0.121,
					saturation: 0.73,
					lightness:  0.66,

					lhue:		 0.04,
					lsaturation: 0.01,	// non-zero so that fractions will be shown
					llightness:  1.0,



					//house
					house: true,

					//cube1
					cube1: true,
					
					//cube2
					cube2: true,

					//Location
					locationCube: true,
					

					// bizarrely, if you initialize these with negative numbers, the sliders
					// will not show any decimal places.
					lx: 0.32,
					ly: 0.39,
					lz: 0.7,
					newTess: 15,
					bottom: true,
					lid: true,
					body: true,
					fitLid: false,
					nonblinn: false,
					newShading: "glossy"
				};

				var h;

				var gui = new dat.GUI();

				// material (attributes)

			/*	h = gui.addFolder( "Material control" );

				h.add( effectController, "shininess", 1.0, 400.0, 1.0 ).name( "shininess" ).onChange( render );
				h.add( effectController, "kd", 0.0, 1.0, 0.025 ).name( "diffuse strength" ).onChange( render );
				h.add( effectController, "ks", 0.0, 1.0, 0.025 ).name( "specular strength" ).onChange( render );
				h.add( effectController, "metallic" ).onChange( render );

				// material (color)

				h = gui.addFolder( "Material color" );

				h.add( effectController, "hue", 0.0, 1.0, 0.025 ).name( "hue" ).onChange( render );
				h.add( effectController, "saturation", 0.0, 1.0, 0.025 ).name( "saturation" ).onChange( render );
				h.add( effectController, "lightness", 0.0, 1.0, 0.025 ).name( "lightness" ).onChange( render );

				// light (point)

				h = gui.addFolder( "Lighting" );

				h.add( effectController, "lhue", 0.0, 1.0, 0.025 ).name( "hue" ).onChange( render );
				h.add( effectController, "lsaturation", 0.0, 1.0, 0.025 ).name( "saturation" ).onChange( render );
				h.add( effectController, "llightness", 0.0, 1.0, 0.025 ).name( "lightness" ).onChange( render );
				h.add( effectController, "ka", 0.0, 1.0, 0.025 ).name( "ambient" ).onChange( render );

				// light (directional)

				h = gui.addFolder( "Light direction" );

				h.add( effectController, "lx", -1.0, 1.0, 0.025 ).name( "x" ).onChange( render );
				h.add( effectController, "ly", -1.0, 1.0, 0.025 ).name( "y" ).onChange( render );
				h.add( effectController, "lz", -1.0, 1.0, 0.025 ).name( "z" ).onChange( render );

				h = gui.addFolder( "Tessellation control" );
				h.add( effectController, "newTess", [ 2, 3, 4, 5, 6, 8, 10, 15, 20, 30, 40, 50 ] ).name( "Tessellation Level" ).onChange( render );
				h.add( effectController, "lid" ).name( "display lid" ).onChange( render );
				h.add( effectController, "body" ).name( "display body" ).onChange( render );
				h.add( effectController, "bottom" ).name( "display bottom" ).onChange( render );
				h.add( effectController, "fitLid" ).name( "snug lid" ).onChange( render );
				h.add( effectController, "nonblinn" ).name( "original scale" ).onChange( render );
*/
				//House
				h = gui.addFolder( "House control" );
			
				h.add( effectController, "house" ).name( "display house" ).onChange( render );
			
				//cube1
				
				h = gui.addFolder( "Cube control" );
			
				h.add( effectController, "cube1" ).name( "display cube1" ).onChange( render );
				h.add( effectController, "cube2" ).name( "display cube2" ).onChange( render );

				//Location
				//cube1
				
				h = gui.addFolder( "Location Cube control" );
			
				h.add( effectController, "locationCube" ).name( "display locationCube" ).onChange( render );
				
				
				
			}


			//

			function render() {
				

				if (effectController.house !== bhouse ||			//house
					effectController.cube1 !== bcube1 ||				//cube1
					effectController.cube2 !== bcube2 ||				//cube2
					effectController.locationCube !== blocationCube     //Location
					)
				{
					bhouse = effectController.house;				//house
					bcube1 = effectController.cube1;					//cube1
					bcube2 = effectController.cube2;					//cube2
					blocationCube = effectController.locationCube;    //Location
					
					if(effectController.house){
						createNewhouse();
					}
					else{
						reset(houseObject);
					}

					if(effectController.cube1){
						createNewCube1();
					}
					else{
						reset(cubeObject1);
					}

					if(effectController.cube2){
						createNewCube2();
					}
					else{
						reset(cubeObject2);
					}
					if(effectController.locationCube){
						createLocationCube(locationCubeObject);
					}
					else{
						reset(locationCubeObject);
					}
				}
				
				// We're a bit lazy here. We could check to see if any material attributes changed and update
				// only if they have. But, these calls are cheap enough and this is just a demo.
				phongMaterial.shininess = effectController.shininess; //krunal
				texturedMaterial.shininess = effectController.shininess;

				diffuseColor.setHSL( effectController.hue, effectController.saturation, effectController.lightness );
				if ( effectController.metallic )
				{

					// make colors match to give a more metallic look
					specularColor.copy( diffuseColor );

				}
				else
				{

					// more of a plastic look
					specularColor.setRGB( 1, 1, 1 );

				}

				diffuseColor.multiplyScalar( effectController.kd );
				flatMaterial.color.copy( diffuseColor );
				gouraudMaterial.color.copy( diffuseColor );
				phongMaterial.color.copy( diffuseColor );
				texturedMaterial.color.copy( diffuseColor );

				specularColor.multiplyScalar( effectController.ks );
				phongMaterial.specular.copy( specularColor );
				texturedMaterial.specular.copy( specularColor );

				// Ambient's actually controlled by the light for this demo
				ambientLight.color.setHSL( effectController.hue, effectController.saturation, effectController.lightness * effectController.ka );

				light.position.set( effectController.lx, effectController.ly, effectController.lz );
				light.color.setHSL( effectController.lhue, effectController.lsaturation, effectController.llightness );
				
				// skybox is rendered separately, so that it is always behind the house.
				if ( shading === "reflective" ) {

					scene.background = textureCube;

				} else {

					scene.background = null;

				}

				renderer.render( scene, camera );

			}

			// Whenever the house changes, the scene is rebuilt from scratch (not much to it).
			

			function createNewhouse(){
				
				if(!effectController.house){
					houseObject.visible = false;
					scene.remove(houseObject);
				}
				var onProgress = function ( xhr ) {
					if ( xhr.lengthComputable ) {
						var percentComplete = xhr.loaded / xhr.total * 100;
						console.log( Math.round(percentComplete, 2) + '% downloaded' );
					}
				};

				var onError = function ( xhr ) { };

				THREE.Loader.Handlers.add( /\.dds$/i, new THREE.DDSLoader() );

				var mtlLoader = new THREE.MTLLoader();
				mtlLoader.setPath( 'obj/' );
				mtlLoader.load( 'Bambo_House.mtl', function( materials ) {

				materials.preload();

				var objLoader = new THREE.OBJLoader();
				objLoader.setMaterials( materials );
				objLoader.setPath( 'obj/' );
				objLoader.load( 'Bambo_House.obj', function ( object ) {
				
				houseObject = object;
				scene.add(houseObject);
				}, onProgress, onError );	// For objLoader.load( 'Bambo_House.obj') function
				});				//For mtlLoader.load( 'Bambo_House.mtl') function 

			}

			function createNewCube1(){
				
				if(cubeObject1 != undefined){
					cubeObject1.visible = false;
					scene.remove(cubeObject1);
				}

				material = new THREE.MeshBasicMaterial( { vertexColors: THREE.FaceColors, overdraw: 0.5 } );
										
				geometry = new THREE.BoxGeometry( 1, 1, 0.5 );
				cube1 = new THREE.Mesh( geometry, material);
				cube1.position.set(5,1,0);
				cubeObject1 = cube1;
				cubeObject1.name = "cube1";
				scene.add( cubeObject1 );
			}
			
			function createNewCube2(){

				if(cubeObject2 != undefined){
					cubeObject2.visible = false;
					scene.remove(cubeObject2);
				}

				material = new THREE.MeshBasicMaterial( { vertexColors: THREE.FaceColors, overdraw: 0.5 } );
										
				geometry = new THREE.BoxGeometry( 0.5, 1, 1 );
				cube2 = new THREE.Mesh( geometry, material);
				cube2.position.set(11.3,3.5,-3.5);
				cubeObject2 = cube2;
				cubeObject2.name = "cube2";
				scene.add( cubeObject2 );
			}

			function createLocationCube(){
				
				if(locationCubeObject != undefined){
					locationCubeObject.visible = false;
					scene.remove(locationCubeObject);
				}
				console.log("Inside location");
				var longitude = 1;
				var latitude = 2;
				var locationCoordinates = locationGetIntoXYZ(longitude, latitude);
				
				material = new THREE.MeshBasicMaterial( { vertexColors: THREE.FaceColors, overdraw: 0.5 } );
										
				geometry = new THREE.BoxGeometry( 1,1,1);
				locationCube = new THREE.Mesh( geometry, material);
				locationCube.position.set(locationCoordinates.x, locationCoordinates.y, locationCoordinates.z);
				locationCubeObject = locationCube;
				locationCubeObject.name = "locationCube";
				scene.add( locationCubeObject );

			}
			
			function locationGetIntoXYZ(lon, lat){
				//var radius = 3958.75;					//Earth's Radius
				var radius = 100;
				var phi   = (90-lat)*(Math.PI/180);
				var theta = (lon+180)*(Math.PI/180);
				var myObject = new Object();
				myObject.x = -((radius) * Math.sin(phi)*Math.cos(theta));
				myObject.z = ((radius) * Math.sin(phi)*Math.sin(theta));
				myObject.y = ((radius) * Math.cos(phi));
				
				return myObject;
				
			}

			function reset(myObject){
			
				scene.remove(myObject);
			}

		</script>

	</body>
</html>
