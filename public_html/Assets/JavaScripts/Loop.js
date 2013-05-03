THREE.LoopDivision = function ( geometry ) {
    geometry.SubLvl = 0;
    geometry.ActLvl = 0;
    geometry.dynamic = true;
};

THREE.LoopDivision.prototype.prepare = function ( geometry ) {
    
    for(var i = 0 ;  i < geometry.faceVertexUvs.length; i++)
        for(var j=0 ; j < geometry.faces.length; j++)
            if(geometry.faceVertexUvs[i][j]===undefined){
                geometry.faceVertexUvs[i][j] = [];
                geometry.faceVertexUvs[i][j].push(new THREE.Vector2(0,0));
                geometry.faceVertexUvs[i][j].push(new THREE.Vector2(1,0));
                geometry.faceVertexUvs[i][j].push(new THREE.Vector2(0,1));
            }else{
                geometry.faceVertexUvs[i][j][0] = new THREE.Vector2(0,0);
                geometry.faceVertexUvs[i][j][1] = new THREE.Vector2(1,0);
                geometry.faceVertexUvs[i][j][2] = new THREE.Vector2(0,1);
            }
};

THREE.LoopDivision.prototype.prev = function ( geometry ) {
    if(geometry.SubLvl > 0)
    {
        geometry.SubLvl--;
    }
};

THREE.LoopDivision.prototype.next = function ( geometry ) {
    geometry.SubLvl++;
    
    if(geometry.ActLvl <= geometry.SubLvl){
       geometry.ActLvl++; 
       this.nextP(geometry);
    }
};

THREE.LoopDivision.prototype.nextP = function ( geometry ) {
    var a,b,c,va,vb,vc;
    var ab,bc,ac,vab,vac,vbc;
    var vnab,vnac,vnbc;
    var vcab,vcac,vcbc;
    var uvs,uvA,uvB,uvC,uvAB,uvAC,uvBC;
    var uvsTriA,uvsTriB,uvsTriC,uvsTriD;
    var i,il,j,jl;
    var triA,triB,triC,triD;
    var odd = [];
    var even= [];
    var faces = [];
    var faceVertexUvs = [];
    
    function Busqueda(Faces,a,b){
        var i, il,j,jl;
        var Face1,Face2;
        for ( i = 0, il = Faces.length; i < il; i++ ){
            Face1 = Faces[i];          
            if((Face1.a===a || Face1.b ===a || Face1.c===a)&&(Face1.a===b || Face1.b ===b || Face1.c===b)){
                for ( j = i+1, jl = Faces.length; j < jl; j++ ){
                    Face2 = Faces[j];
                    if((Face2.a===a || Face2.b ===a || Face2.c===a)&&(Face2.a===b || Face2.b ===b || Face2.c===b))
                        break;
                }
                break;
            }
        }
        
        var Res = [];
        Res.push(a,b);
        
        if(Face1.a!==a && Face1.a!==b)
            Res.push(Face1.a);

        if(Face1.b!==a && Face1.b!==b)
            Res.push(Face1.b);

        if(Face1.c!==a && Face1.c!==b)
            Res.push(Face1.c);
        
        if(Face2.a!==a && Face2.a!==b)
            Res.push(Face2.a);

        if(Face2.b!==a && Face2.b!==b)
            Res.push(Face1.b);

        if(Face2.c!==a && Face2.c!==b)
            Res.push(Face1.c);
        
        return Res;
        
    }
    
    function OddVertex(geometry,a,b){
        var Pos = Busqueda(geometry.faces,a,b);
        var x = 0,y = 0,z = 0;
        x += (geometry.vertices[ Pos[0] ].x) * 3 / 8;
        x += (geometry.vertices[ Pos[1] ].x) * 3 / 8;
        x += (geometry.vertices[ Pos[2] ].x) * 1 / 8;
        x += (geometry.vertices[ Pos[3] ].x) * 1 / 8;

        y += (geometry.vertices[ Pos[0] ].y) * 3 / 8;
        y += (geometry.vertices[ Pos[1] ].y) * 3 / 8;
        y += (geometry.vertices[ Pos[2] ].y) * 1 / 8;
        y += (geometry.vertices[ Pos[3] ].y) * 1 / 8;

        z += (geometry.vertices[ Pos[0] ].z) * 3 / 8;
        z += (geometry.vertices[ Pos[1] ].z) * 3 / 8;
        z += (geometry.vertices[ Pos[2] ].z) * 1 / 8;
        z += (geometry.vertices[ Pos[3] ].z) * 1 / 8;   
        
        return new THREE.Vector3( x, y, z );
    }
    
    function EvenVertex(Faces,even){
        var i, il;
        var Vertex = [];
        for ( i = 0, il = Faces.length; i < il; i++ ){
            var Face = Faces[i];
            if(Face.a===even){
                if(Vertex.indexOf(Face.b) === -1)
                    Vertex.push(Face.b);
                if(Vertex.indexOf(Face.c) === -1)
                    Vertex.push(Face.c);
            }
            if(Face.b===even){
                if(Vertex.indexOf(Face.a) === -1)
                    Vertex.push(Face.a);
                if(Vertex.indexOf(Face.c) === -1)
                    Vertex.push(Face.c);
            }
            if(Face.c===even){
                if(Vertex.indexOf(Face.a) === -1)
                    Vertex.push(Face.a);
                if(Vertex.indexOf(Face.b) === -1)
                    Vertex.push(Face.b);
            }
        }
        var n = Vertex.length;
        var B = 5/8 - ((Math.pow(3+2*Math.cos(2*Math.PI/n),2)) /64);
        
        var x = 0,y = 0,z = 0;
        for( i = 0, il = Vertex.length; i < il; i++){
            x += (geometry.vertices[ Vertex[i] ].x) * B / n;
            y += (geometry.vertices[ Vertex[i] ].y) * B / n;
            z += (geometry.vertices[ Vertex[i] ].z) * B / n; 
        }
        x += (geometry.vertices[ even ].x) * (1 - B);
        y += (geometry.vertices[ even ].y) * (1 - B);
        z += (geometry.vertices[ even ].z) * (1 - B);
        
        return new THREE.Vector3( x, y, z );
    }
    
    for ( i = 0, il = geometry.faceVertexUvs.length; i < il; i ++ )
            faceVertexUvs[ i ] = [];

    for ( i = 0, il = geometry.vertices.length; i < il; i ++ )
            even.push(geometry.vertices[i].clone());

    //Division de Maya
    for ( i = 0, il = geometry.faces.length; i < il; i ++ ) {
        var face = geometry.faces[ i ];
        if ( face instanceof THREE.Face3 ) {
            a = face.a;
            ab = geometry.vertices.length;
            b = face.b;
            ac = ab + 1;
            c = face.c;
            bc = ac + 1;
                        
            triA = face.clone();
            triA.a = ab; 
            triA.b = b;
            triA.c = bc;
                    
            triB = face.clone();
            triB.a = a;
            triB.b = ab;
            triB.c = ac;
            
            triC = face.clone();
            triC.a = ab;
            triC.b = bc;
            triC.c = ac;
            
            triD = face.clone();
            triD.a = ac;
            triD.b = bc;
            triD.c = c;
            
            
            va = geometry.vertices[ a ];
            vb = geometry.vertices[ b ];
            vc = geometry.vertices[ c ];            
            
            vab = va.clone();
            vab.lerp( vb, 0.5 );
            vab.Parents = [];
            vab.Parents.push(a);
            vab.Parents.push(b);
            
            vac = va.clone();
            vac.lerp( vc, 0.5 );
            vac.Parents = [];
            vac.Parents.push(a);
            vac.Parents.push(c);
            
            vbc = vb.clone();
            vbc.lerp( vc, 0.5 );
            vbc.Parents = [];
            vbc.Parents.push(b);
            vbc.Parents.push(c);
            
            
            if ( face.vertexNormals.length === 3 ) {

                vnab = face.vertexNormals[ 0 ].clone();
                vnab.lerp( face.vertexNormals[ 1 ], 0.5 );
                
                vnac = face.vertexNormals[ 0 ].clone();
                vnac.lerp( face.vertexNormals[ 2 ], 0.5 );
                
                vnbc = face.vertexNormals[ 1 ].clone();
                vnbc.lerp( face.vertexNormals[ 2 ], 0.5 );

                triA.vertexNormals[ 0 ].copy( vnab );
                triA.vertexNormals[ 2 ].copy( vnbc );

                triB.vertexNormals[ 1 ].copy( vnab );
                triB.vertexNormals[ 2 ].copy( vnac );
                
                triC.vertexNormals[ 0 ].copy( vnab );
                triC.vertexNormals[ 1 ].copy( vnbc );
                triC.vertexNormals[ 2 ].copy( vnac );
                
                triD.vertexNormals[ 0 ].copy( vnac );
                triD.vertexNormals[ 1 ].copy( vnbc );

            }

            if ( face.vertexColors.length === 3 ) {

                vcab = face.vertexColors[ 0 ].clone();
                vcab.lerp( face.vertexColors[ 1 ], 0.5 );
                
                vcac = face.vertexColors[ 0 ].clone();
                vcac.lerp( face.vertexColors[ 2 ], 0.5 );
                
                vcbc = face.vertexColors[ 1 ].clone();
                vcbc.lerp( face.vertexColors[ 2 ], 0.5 );

                triA.vertexColors[ 0 ].copy( vnab );
                triA.vertexColors[ 2 ].copy( vnbc );

                triB.vertexColors[ 1 ].copy( vnab );
                triB.vertexColors[ 2 ].copy( vnac );
                
                triC.vertexColors[ 0 ].copy( vnab );
                triC.vertexColors[ 1 ].copy( vnbc );
                triC.vertexColors[ 2 ].copy( vnac );
                
                triD.vertexColors[ 0 ].copy( vnac );
                triD.vertexColors[ 1 ].copy( vnbc );

            }
            
            faces.push( triA, triB, triC, triD );
            geometry.vertices.push( vab,vac,vbc );
            odd.push( vab,vac,vbc );

            for ( j = 0, jl = geometry.faceVertexUvs.length; j < jl; j ++ ) {
                if ( geometry.faceVertexUvs[ j ].length ) {
                    uvs = geometry.faceVertexUvs[ j ][ i ];
                    uvA = uvs[ 0 ];
                    uvB = uvs[ 1 ];
                    uvC = uvs[ 2 ];

                    uvAB = uvA.clone();
                    uvAB.lerp( uvB, 0.5 );

                    uvAC = uvA.clone();
                    uvAC.lerp( uvC, 0.5 );
                    
                    uvBC = uvB.clone();
                    uvBC.lerp( uvC, 0.5 );


                    uvsTriA = [ uvAB.clone(), uvB.clone(), uvBC.clone() ];
                    uvsTriB = [ uvA.clone(), uvAB.clone(), uvAC.clone() ];
                    uvsTriC = [ uvAB.clone(), uvBC.clone(), uvAC.clone() ];
                    uvsTriD = [ uvAC.clone(), uvBC.clone(), uvC.clone() ];

                    faceVertexUvs[ j ].push( uvsTriA, uvsTriB, uvsTriC, uvsTriD  );
                }
            }
        }
    }
    
    // Vertices Pares
     for ( i = 0, il = even.length; i < il; i ++ ){
         geometry.vertices[i] = EvenVertex(geometry.faces,i);
     }
    
    // Vertices Impares
    for ( i = 0, il = odd.length; i < il; i ++ ){
        geometry.vertices[i+even.length] = OddVertex(geometry,odd[i].Parents[0],odd[i].Parents[1]);
     }
     
    
    geometry.faces = faces;
    geometry.faceVertexUvs = faceVertexUvs;
    
    geometry.mergeVertices();
    geometry.computeCentroids();
    geometry.computeFaceNormals();
    geometry.computeVertexNormals();
    geometry.computeBoundingSphere();
    
    geometry.verticesNeedUpdate = true;
    geometry.elementsNeedUpdate = true;
    geometry.uvsNeedUpdate = true;
    geometry.normalsNeedUpdate = true;
    geometry.tangentsNeedUpdate = true;
    geometry.colorsNeedUpdate = true;
    geometry.lineDistancesNeedUpdate = true;
    geometry.buffersNeedUpdate = true;
    
};
