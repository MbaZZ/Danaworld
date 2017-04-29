/*
* Widgets
*/
function GetScreenCordinates(obj) {
        var p = {};
        p.x = obj.offsetLeft;
        p.y = obj.offsetTop;
        while (obj.offsetParent) {
            p.x = p.x + obj.offsetParent.offsetLeft;
            p.y = p.y + obj.offsetParent.offsetTop;
            if (obj == document.getElementsByTagName("body")[0]) {				
                break;
            }
            else {
                obj = obj.offsetParent;
            }
        }
        return p;
 } 

Utils.getModel3DUrl=function(psModel3D){
	return Utils.getAbsUrl('files/model3D/'+psModel3D);
};
function webglUtils(){};
webglUtils.setNormal=function(geometry,normal){
	for(var iface in geometry.faces){
		geometry.faces[iface].normal = normal;
	}
};
webglUtils.getRandomColor=function() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
};
//function enterFullscreen(element) {
//        if(element.requestFullScreen) {
//                //fonction officielle du w3c
//                element.requestFullScreen();
//        } else if(element.webkitRequestFullScreen) {
//                //fonction pour Google Chrome (on lui passe un argument pour autoriser le plein écran lors d'une pression sur le clavier)
//                element.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
//        } else if(element.mozRequestFullScreen){
//                //fonction pour Firefox
//                element.mozRequestFullScreen();
//        } else {
//                alert('Votre navigateur ne supporte pas le mode plein écran, il est temps de passer à un plus récent ;)');
//        }
//}
//function exitFullscreen() {
//	if(document.cancelFullScreen) {
//			//fonction officielle du w3c
//			document.cancelFullScreen();
//	} else if(document.webkitCancelFullScreen) {
//			//fonction pour Google Chrome
//			document.webkitCancelFullScreen();
//	} else if(document.mozCancelFullScreen){
//			//fonction pour Firefox
//			document.mozCancelFullScreen();
//	}
//}
//FIN MBZ
// stats.js r8 - http://github.com/mrdoob/stats.js
function Stats(){var h,a,n=0,o=0,i=Date.now(),u=i,p=i,l=0,q=1E3,r=0,e,j,f,b=[[16,16,48],[0,255,255]],m=0,s=1E3,t=0,d,k,g,c=[[16,48,16],[0,255,0]];h=document.createElement("div");h.style.cursor="pointer";h.style.width="80px";h.style.opacity="0.9";h.style.zIndex="10001";h.addEventListener("mousedown",function(a){a.preventDefault();n=(n+1)%2;n==0?(e.style.display="block",d.style.display="none"):(e.style.display="none",d.style.display="block")},!1);e=document.createElement("div");e.style.textAlign=
"left";e.style.lineHeight="1.2em";e.style.backgroundColor="rgb("+Math.floor(b[0][0]/2)+","+Math.floor(b[0][1]/2)+","+Math.floor(b[0][2]/2)+")";e.style.padding="0 0 3px 3px";h.appendChild(e);j=document.createElement("div");j.style.fontFamily="Helvetica, Arial, sans-serif";j.style.fontSize="9px";j.style.color="rgb("+b[1][0]+","+b[1][1]+","+b[1][2]+")";j.style.fontWeight="bold";j.innerHTML="FPS";e.appendChild(j);f=document.createElement("div");f.style.position="relative";f.style.width="74px";f.style.height=
"30px";f.style.backgroundColor="rgb("+b[1][0]+","+b[1][1]+","+b[1][2]+")";for(e.appendChild(f);f.children.length<74;)a=document.createElement("span"),a.style.width="1px",a.style.height="30px",a.style.cssFloat="left",a.style.backgroundColor="rgb("+b[0][0]+","+b[0][1]+","+b[0][2]+")",f.appendChild(a);d=document.createElement("div");d.style.textAlign="left";d.style.lineHeight="1.2em";d.style.backgroundColor="rgb("+Math.floor(c[0][0]/2)+","+Math.floor(c[0][1]/2)+","+Math.floor(c[0][2]/2)+")";d.style.padding=
"0 0 3px 3px";d.style.display="none";h.appendChild(d);k=document.createElement("div");k.style.fontFamily="Helvetica, Arial, sans-serif";k.style.fontSize="9px";k.style.color="rgb("+c[1][0]+","+c[1][1]+","+c[1][2]+")";k.style.fontWeight="bold";k.innerHTML="MS";d.appendChild(k);g=document.createElement("div");g.style.position="relative";g.style.width="74px";g.style.height="30px";g.style.backgroundColor="rgb("+c[1][0]+","+c[1][1]+","+c[1][2]+")";for(d.appendChild(g);g.children.length<74;)a=document.createElement("span"),
a.style.width="1px",a.style.height=Math.random()*30+"px",a.style.cssFloat="left",a.style.backgroundColor="rgb("+c[0][0]+","+c[0][1]+","+c[0][2]+")",g.appendChild(a);return{domElement:h,update:function(){i=Date.now();m=i-u;s=Math.min(s,m);t=Math.max(t,m);k.textContent=m+" MS ("+s+"-"+t+")";var a=Math.min(30,30-m/200*30);g.appendChild(g.firstChild).style.height=a+"px";u=i;o++;if(i>p+1E3)l=Math.round(o*1E3/(i-p)),q=Math.min(q,l),r=Math.max(r,l),j.textContent=l+" FPS ("+q+"-"+r+")",a=Math.min(30,30-l/
100*30),f.appendChild(f.firstChild).style.height=a+"px",p=i,o=0}}}

////////////////////////////////////////////////////////////////////////////////// Utils /////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//------------------------------------
//Model 3D
//------------------------------------
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
var model3D = new webglModel3D();
function webglModel3D(){
this.controls = null;
this.renderer = null;
this.scene = null;
this.camera = null;
this.stats = null;
}
webglModel3D.getInstance=function(){
	return model3D;
};
webglModel3D.prototype.setSize=function(width, height){
	if(this.renderer!=null){
		this.renderer.setSize( width, height);
		//model3D.camera.aspect = width/height;
	}	 
};
webglModel3D.prototype.setNewRendu=function(parentElem, lfFormat){	
	try{// on initialise le moteur de rendu
		this.renderer = new THREE.WebGLRenderer({antialias:true});	
	}catch(e){
		var mess=new MessageBox();
		mess.setContent('Votre client WebGL ne fonctionne pas correctement, est-il activé sur votre navigateur ?', 'Impossible de charger le jeu !');
		mess.show();
	}	
	
	if(!lfFormat)lfFormat=1.95;
	var width=$(parentElem).width();
	var height=width/lfFormat;
	this.camera = new THREE.PerspectiveCamera(50, width / height, 1, 10000 );
	this.camera.position.set(100, 100, 500);	
	parentElem.appendChild(this.renderer.domElement);
	
	var loThis=this;	
	var fResize=function(){
		var width=$(parentElem).width();
		var height=width/lfFormat;
		loThis.renderer.setSize(width, height);
	};
	window.addEventListener("resize", fResize);
	fResize();
};
webglModel3D.prototype.setStatElem=function(parentElem){
	this.stats = new Stats();//statistiques du rendu
	this.stats.domElement.style.position = 'absolute';	
	this.stats.domElement.style.right= '20px';
	this.stats.domElement.style.top = '0px';	
	this.stats.domElement.style.zIndex = 1000;
	parentElem.appendChild(this.stats.domElement);
};
webglModel3D.prototype.initializeScene=function(width, height){
	this.scene = new THREE.Scene();
	
};

var THREEx	= THREEx	|| {}


THREEx.Text	= function(text, options){
	options	= options || {
		font		: "droid serif",
		weight		: "bold",
		size		: 1,
		height		: 0.4,
	}

	// create the tGeometry
	var geometry	= new THREE.TextGeometry(text, options)

	// center the geometry
	// - THREE.TextGeometry isnt centered for unknown reasons. all other geometries are centered
	geometry.computeBoundingBox();
	var center	= new THREE.Vector3();
	center.x	= (geometry.boundingBox.max.x - geometry.boundingBox.min.x) / 2
	center.y	= (geometry.boundingBox.max.y - geometry.boundingBox.min.y) / 2
	center.z	= (geometry.boundingBox.max.z - geometry.boundingBox.min.z) / 2
	geometry.vertices.forEach(function(vertex){
		vertex.sub(center)
	})
	
	// create a mesh with it
	var material	= new THREE.MeshNormalMaterial()
	var mesh	= new THREE.Mesh(geometry, material)
	// return mesh
	return mesh
}