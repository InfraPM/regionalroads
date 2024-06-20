<?php
require 'headerpublic.php';
require_once '../support/environmentsettings.php';
$datatoken = 'public';
?>

<html>
    <style>
     #mapid { height: 100%;
	 clear: both;
     }
     <style>
     table {
	 font-family: arial, sans-serif;
	 border-collapse: collapse;
	 table-layout: fixed;
	 width: 100%;
     }

     td{
	 border: 1px solid #dddddd;
	 text-align: left;
	 padding: 2px;
	 word-wrap: break-word;
	 max-width: 150px;
	 font-size: 0.75em !important;
     }

     tr:nth-child(even) {
	 background-color: #dddddd;
     }
</style>
<head>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css"
         integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
	      crossorigin=""/>
         <link rel="stylesheet" type="text/css" href="leaflet-geoman.css">
         <script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"
         integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew=="
		     crossorigin=""></script>
         <script
	    src="https://code.jquery.com/jquery-3.4.1.min.js"
	    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
	    crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.css"/>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.js"></script>
         	<script src="L.TileLayer.BetterWMS.js"></script>
    <script src="leaflet-geoman.min.js"></script>
    <script
  src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"
  integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E="
  crossorigin="anonymous"></script>
    </head>
    <body>
	<div  class="clearfloat" style="padding-top:20px;padding-bottom:20px">
<!--	    <a href="index.php" class="headerlink btn btn-primary btn-block btn-large" style="clear:right">< Return to Data</a>-->
    </div>
    <div id="titleContainer"><h4><?php echo $data; ?></h4></div>
    <div class="toolbar" id="basemapSelector" style="display:none">
    <form id="basemapSelectorForm" action="nada">
    <h4>Basemaps</h4>
    <input type="radio" id="basemap-imagery" name="basemap-selector" value="basemap-imagery" checked><label for="basemap-imagery">Imagery</label><br>
    <input type="radio" id="basemap-map" name="basemap-selector" value="basemap-map"><label for="basemap-map">Map</label><br>
    
</form>
    </div>
    <div class="toolbar" id="editToolbar" style="display:none">
    <div id="addbuttoncontainer"><button type="button" id="addbutton" class="btn btn-primary btn-block btn-large">Add Feature</button><button type="button" id="cancelAddButton" style="display:none" class="btn btn-primary btn-block btn-large">Cancel</button></div>
    <div id="editbuttoncontainer"><button type="button" id="editbutton" class="btn btn-primary btn-block btn-large">Edit Feature</button><button type="button" id="addToFeatureButton" style="display:none" class="btn btn-primary btn-block btn-large">Add to Feature</button><button type="button" id="cancelEditButton" style="display:none" class="btn btn-primary btn-block btn-large">Cancel</button></div>
    <div id="deletebuttoncontainer"><button type="button" id="deleteButton" class="btn btn-primary btn-block btn-large">Delete Feature</button><button type="button" id="cancelDeleteButton" style="display:none" class="btn btn-primary btn-block btn-large">Cancel</button></div>
    </div>
    <div id="trnlegend"><img src="<?php echo $baseURL; ?>regionalroads.com/img/pcslegend.png" style="width:400px;"></div>
    
	<div id="mapid"></div>

    <script>
    /*
      Regional Roads Editable WFS-T
      Completed June 2020 by Alan Tabbernor, Senior GIS Specialist, Infrastructure Program Management, TransLink
      Limitations: OBJECTID and Shape (case sensitive) must be used as primary key and geometry field for datasets respectively
      Option exists to 'whitelist' primary key and geometry fields to allow for multiple options
      editable wfs services must be in dev workspace (this is hard coded at the time)
      Ensure appropriate permissions, etc. are set up in geoserver to allow wfs-t editing
      Also dependent on 'simplewfs' PHP API
      Points and Lines only at this time -> Polygons coming soon!
     */
         /*function describeFeatureType(data){
    //call wfs DescribeFeatureType to get all fields and types
    return new Promise((resolve, reject)=>{
	    $.ajax({
		type:"GET",
		url:"<?php echo $baseAPIURL ?>/simplewfs/?service=wfs&version=2.0.0&request=DescribeFeatureType&typeNames=" + data + "&spatialdata=" + data + "&token=" + "<?php echo $datatoken ?>",
		//token: "<?php echo $datatoken; ?>",
		//spatialdata: data,
		dataType: "xml",
		asynch: true,
		success: function(msg){
		    resolve(msg);
		    //console.log(msg);
		}
	    });
    });
}
*/
function getForeignKeys(data, token){
    //call wfs DescribeFeatureType to get all fields and types
    return new Promise((resolve, reject)=>{
	    $.ajax({
		type:"GET",
		url:"<?php echo $baseURL ?>regionalroads.com/fkList.php?table=" + data + "&token=" + token,
		//dataType: "json",
		asynch: true,
		success: function(fkJson){
		    resolve(fkJson);
		    //console.log(fkJson);
		}
	    });
    });
    }

function truncateSpatialData(spatialData){
    var truncateString = "_dev";
    var stringLength = spatialData.length;
    var startInt = stringLength - truncateString.length;
    if (spatialData.substring(startInt,stringLength)==truncateString){
	return spatialData.substring(0,startInt);
    }
}
    /*

function foreignKeyField(fkJson, fieldName){
    //var obj = json_decode(fkJson);
    for (var i=0; i< fkJson.length; i++){
	if (fkJson[i]['primaryColumnName']==fieldName){
	    return true;
	}
    }
    return false;
}

function getForeignKeyDropDown(fkJson, fieldName, defaultValue=null){
    //$obj = json_decode($fkJson);
    var dropDownHTML = '<select id="'+fieldName+'" name="'+fieldName+'">';
    for(var i=0; i<fkJson.length; i++){
	if (fkJson[i]['primaryColumnName']==fieldName){
	    for(var j=0; j<fkJson[i]['values'].length; j++){
		if (defaultValue===null){
		    dropDownHTML+='<option value="'+fkJson[i]['values'][j]['id']+'">'+fkJson[i]['values'][j]['value']+'</option>';
		}
		else{
		    if (defaultValue==fkJson[i]['values'][j]['id']){
			dropDownHTML+='<option value="'+fkJson[i]['values'][j]['id']+'" selected="selected">'+fkJson[i]['values'][j]['value']+'</option>';
		    }
		    else{
			dropDownHTML+='<option value="'+fkJson[i]['values'][j]['id']+'">'+fkJson[i]['values'][j]['value']+'</option>';
		    }
		}
	    }
	}
    }
    dropDownHTML+='</select>';
    return dropDownHTML;
}

function getPopupForm(msg, fkJson){
    //create html form for use in popup.  Dynamically generated using fields / types from wfs getfeaturerequest
    var htmlForm = '<h2>Add Feature</h2><form id="addAttributesForm" action="nada" onsubmit="return false">';
    var xmlDoc = msg;
    var s = new XMLSerializer();
    //var a, childNode = xmlDoc.getElementsByTagName("xsd:sequence").childNodes;
    var a = xmlDoc.getElementsByTagName("xsd:element");
    for (i=0; i < a.length; i++){
	//var curName = s.serializeToString(i.attributes.name);
	node = a[i];
	if (s.serializeToString(node.attributes.name) != "<?php echo $data ?>"){
	    var curName = s.serializeToString(node.attributes.name);
	    var curLabel = curName +"Label";
	    var curType = s.serializeToString(node.attributes.type);
	    var curNillable = s.serializeToString(node.attributes.nillable);
	    //curName = curName.substr(curName.indexOf(":"),curName.length);
	    curType = curType.substr(curType.indexOf(":")+1,curType.length);
	    //curNillable = curNillable.substr(curNillable.indexOf(":"),curNillable.length);
	    if (curName=="OBJECTID" || curName=="Shape"){
		htmlForm+='<label for="'+curName+'" style="display:none">'+curName+'</label>';		
	    }
	    else{
		htmlForm+='<label for="'+curName+'">'+curName+'</label><br>';
	    }	    
	    if(foreignKeyField(fkJson, curName)==false){
		var formType = getFormType(curType, curName);
		//console.log(formType);
		if (formType=="hidden"){
		    htmlForm+='<input type="'+formType+'" id="'+curName+'" name="'+curName+'">';
		}
		else if (formType=="textarea"){
		    htmlForm+='<textarea id="'+curName+'" name="'+curName+'" rows="5" cols="45"></textarea><br>'
		}
		else{
		    htmlForm+='<input type="'+formType+'" id="'+curName+'" name="'+curName+'"><br>';
		}
		//console.log(htmlForm);
	    }
	    else{
		htmlForm+=getForeignKeyDropDown(fkJson, curName);
	    }
	}

    }
    htmlForm+='<button id="addAttributesButton">Save</button>';
htmlForm+='</form>';
    return htmlForm;
}


function getFormType(type, name){
    //translate wfs type to form input type
    if (name=="OBJECTID"){
        return "hidden";
    }
    else if (name=="Shape"){
        return "hidden";
    }
    else if (name.toLowerCase().includes("description")){
	return "textarea";
    }
    else{
        if (type=="string"){
            return "text";
        }
        else if (type=="date"){
            return "date";
        }
        else if (type=="long"){
            return "number";
        }
        else if (type=="short"){
            return "number";
        }
    }
}

function buildGMLFeature(editLayer, formArray, requestFeatureType, projection, memberType, baseFeatureType, geomField, latLngIndex, mode){
    var gmlFeature = "";
    gmlFeature+=`<gml:` + requestFeatureType + ` srsName="http://www.opengis.net/gml/srs/epsg.xml#` + projection + `">`;
    var commaCount = 0
    var latlngString = "";
    //console.log(requestFeatureType);
    editLayer.eachLayer(function(layer){
	//console.log(layer);
	latlngString="";
	if (mode!="Update"){
	    latlngString = "";
	    gmlFeature+=`<gml:` + memberType + `>`;
	    gmlFeature+=`<gml:`+ baseFeatureType +` srsName="http://www.opengis.net/gml/srs/epsg.xml#`+ projection +`">
<gml:coordinates decimal="." cs="," ts=" ">`;
	}
	if (commaCount > 0){
	    latlngString+=" ";
	}
	if (requestFeatureType!="MultiPoint"){
	    var lineSpaceCount = 0;
	    if (mode=="Insert"){
		var loopFeature = layer[latLngIndex];
		var spaceCount = 0
		for (var j=0; j<loopFeature.length; j++){
		    if (spaceCount>0){
			latlngString+=" ";
		    }
		    latlngString+=loopFeature[j]['lng'] + "," + loopFeature[j]['lat'];
		    spaceCount+=1;
		}
	    }
	    else if (mode=="Update"){
		var loopFeature = layer[latLngIndex];
		var verticesCount = 0;
		for (var j=0; j<loopFeature.length; j++){		    
		    if (loopFeature[j].length>0 && loopFeature[j] instanceof Array){
			//console.log(loopFeature[j].length);
		    latlngString+=`<gml:` + memberType + `>`;
		    latlngString+=`<gml:`+ baseFeatureType +` srsName="http://www.opengis.net/gml/srs/epsg.xml#`+ projection +`">
<gml:coordinates decimal="." cs="," ts=" ">`;
			var spaceCount = 0;
			for (var k=0; k<loopFeature[j].length; k++){
			    if (spaceCount>0){
				latlngString+=" ";
			    }
			    latlngString+=loopFeature[j][k]['lng'] + "," + loopFeature[j][k]['lat'];
			    
			    spaceCount+=1;
			}
			latlngString+=`</gml:coordinates>`;
			latlngString+=`</gml:`+baseFeatureType+`>`;
			latlngString+=`</gml:` + memberType + `>`;
		    }
		    else{
			if (loopFeature[j]['lng']!=undefined && loopFeature[j]['lat']!=undefined){
			    if (verticesCount==0){
				latlngString+=`<gml:` + memberType + `>`;
				latlngString+=`<gml:`+ baseFeatureType +` srsName="http://www.opengis.net/gml/srs/epsg.xml#`+ projection +`">
<gml:coordinates decimal="." cs="," ts=" ">`;
			    }
			    if (verticesCount>0){
				latlngString+=" ";
			    }
			    latlngString+=loopFeature[j]['lng'] + "," + loopFeature[j]['lat'];
			    if (verticesCount==loopFeature.length-1){
				latlngString+=`</gml:coordinates>`;
				latlngString+=`</gml:`+baseFeatureType+`>`;
				latlngString+=`</gml:` + memberType + `>`;
			    }
			}
			verticesCount+=1;
		    }
		}		
	    }	   
	}
	else{
	    if (mode=="Insert"){
		latlngString+=layer[latLngIndex]['lng'] + "," + layer[latLngIndex]['lat'];
	    }
	    else if (mode=="Update"){
		var loopFeature = layer['_layers'];
		if (loopFeature!=undefined){		    
		    var spaceCount = 0;
		    for (var j in loopFeature){
			if (loopFeature[j]['pm']['_enabled']==false){
			latlngString+=`<gml:` + memberType + `>`;
			latlngString+=`<gml:`+ baseFeatureType +` srsName="http://www.opengis.net/gml/srs/epsg.xml#`+ projection +`">
<gml:coordinates decimal="." cs="," ts=" ">`;
			latlngString+=loopFeature[j][latLngIndex]['lng'] + "," + loopFeature[j][latLngIndex]['lat'];
			latlngString+=`</gml:coordinates>`;
			latlngString+=`</gml:`+baseFeatureType+`>`;
			latlngString+=`</gml:` + memberType + `>`;			
			}
		    }
		}
		else{		    
		    latlngString+=`<gml:` + memberType + `>`;
		    latlngString+=`<gml:`+ baseFeatureType +` srsName="http://www.opengis.net/gml/srs/epsg.xml#`+ projection +`">
<gml:coordinates decimal="." cs="," ts=" ">`;
		    latlngString+=layer[latLngIndex]['lng'] + "," + layer[latLngIndex]['lat'];
		    latlngString+=`</gml:coordinates>`;
		    latlngString+=`</gml:`+baseFeatureType+`>`;
		    latlngString+=`</gml:` + memberType + `>`;
		}
	    }
	}
	commaCount+=1;
	gmlFeature+=latlngString;
	if (mode!="Update"){
	    gmlFeature+=`</gml:coordinates>
</gml:`+ baseFeatureType +`>
</gml:`+ memberType +`>`;
	}
    });
    gmlFeature+=`</gml:` + requestFeatureType + `>`;
    //var attributeString = buildGMLAttributes(formArray, mode);
    //gmlFeature+=attributeString;
    return gmlFeature;

}

function buildGMLAttributes(formArray, mode){
    var attributeString="";
    for (i=0; i<formArray.length; i++){
	if (mode=="Insert"){
	    if (formArray[i]['name']!="OBJECTID" && formArray[i]['name']!= "Shape"){
		attributeString+="<" +formArray[i]['name'] +">" + formArray[i]['value'] + "</" + formArray[i]['name'] +">";
	    }
	}
	else if (mode=="Update"){
	    if (formArray[i]['name']!="OBJECTID" && formArray[i]['name']!= "Shape"){
		attributeString+="<wfs:Property><wfs:Name>" +formArray[i]['name'] +"</wfs:Name><wfs:Value>" + formArray[i]['value'] + "</wfs:Value></wfs:Property>";
	    }
	}
    }
    return attributeString;
}

function buildXMLRequest(data, method, featureType, projection, geomField, editLayer, formArray, featureId=null){
    //build XML body for WFS-T post request -> so far only insert works
    var xmlString = `<wfs:Transaction service="WFS" version="1.0.0"
  xmlns:wfs="http://www.opengis.net/wfs"
  xmlns:topp="http://www.openplans.org/topp"
  xmlns:gml="http://www.opengis.net/gml"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.opengis.net/wfs http://schemas.opengis.net/wfs/1.0.0/WFS-transaction.xsd http://www.openplans.org/topp">`;
    var memberType, baseFeatureType, latLngIndex, requestFeatureType;
    //console.log(featureType);
    if (featureType=="gml:MultiPointPropertyType"){
        memberType = "pointMember";
	baseFeatureType = "Point";
	latLngIndex = '_latlng';
	requestFeatureType = "MultiPoint";
    }
    else if (featureType=="gml:MultiCurvePropertyType"){
	memberType = "lineStringMember";
	baseFeatureType = "LineString"
	latLngIndex = '_latlngs';
	requestFeatureType = "MultiLineString";
    }
    else if (featureType=="gml:MultiSurfacePropertyType"){
	memberType = "polygonMember";
	baseFeatureType = "Polygon";
	latLngIndex = '_latlngs';
	requestFeatureType = "MultiPolygon";
    }
    else{
	//unsupported
    }
    if (method=="Insert"){
	xmlString+=`<wfs:Insert>
    <`+ data +`>`;
	xmlString+=`<` + geomField + `>`;
	var featureString = buildGMLFeature(editLayer, formArray, requestFeatureType, projection, memberType, baseFeatureType, geomField, latLngIndex, method);
	xmlString += featureString;
	// add attributes string (outside function)
	xmlString += `</` + geomField + `>`;
	var attributeString = buildGMLAttributes(formArray, "Insert");
	xmlString+= attributeString;
	xmlString+=`</`+data+`>
</wfs:Insert>
</wfs:Transaction>`;
	return xmlString;
    }
    else if (method=="Delete"){

    }
    else if (method=="Update"){
	
	xmlString+= `<wfs:Update typeName="` + data +`">
    <wfs:Property>`;
	if (requestFeatureType=="MultiPoint"){
	    var featureString = buildGMLFeature(editLayer, formArray, requestFeatureType, projection, memberType, baseFeatureType, geomField, latLngIndex, method);
	}
	else{
	    var featureString = buildGMLFeature(editLayer, formArray, requestFeatureType, projection, memberType, baseFeatureType, geomField, latLngIndex, method);
	}
	    xmlString+=`<wfs:Name>` + geomField + `</wfs:Name>
      <wfs:Value>`;
	xmlString += featureString;
	xmlString+=`</wfs:Value></wfs:Property>`;
	var attributeString = buildGMLAttributes(formArray, "Update");
	xmlString += attributeString;
	//add attribute string
	xmlString+= ` <Filter>
      <FeatureId fid="`+ data + `.` + featureId + `"/>
    </Filter>
  </wfs:Update>
</wfs:Transaction>`;
	return xmlString;
    }
    else{
	
    }
}
function addAttributes(){
    return false;
}

function addWFS(formId, editLayer, wms, featureType, method, data, mymap){
    //make ajax request to add wfs layer to database
    //var data = "<?php echo $data ?>";
    //var method = "Insert";
    //var featureType = "MultiPoint";
    var projection = "4326";
    var geomField = "Shape";
    var formArray = $(formId).serializeArray();
    var xmlString = buildXMLRequest(data, method, featureType, projection, geomField, editLayer, formArray);
    var url = "<?php echo $baseAPIURL ?>/simplewfs/?token=<?php echo $datatoken?>&spatialdata=<?php echo $data;?>" 
    $.ajax({
	type: "POST",
	//url: "<?php echo $baseAPIURL ?>/simplewfs/?",
	url: url,
	//token: "<?php echo $datatoken; ?>",
	//spatialdata: "<?php echo $data; ?>",
	data: xmlString,
	dataType: "xml",
	success: function(msg){
	    var stringMsg = new XMLSerializer().serializeToString(msg);
	    editLayer.closePopup();
	    editLayer.clearLayers();
	    wms.setParams({fake: Date.now()}, false);//cache buster
	    wms.redraw();//reload wms
	},
	error: function(msg){
	    console.log("Error adding features. " + msg);
	}
    });
}
function editWFS(xmlString, wms, editLayer, mymap){
    //make ajax request to add wfs layer to database
    //var data = "<?php echo $data ?>";
    //var method = "Insert";
    //var featureType = "MultiPoint";
    //var projection = "4326";
    //var geomField = "Shape";
    //var formArray = $(formId).serializeArray();
    //console.log(formArray);
    //var xmlString = buildXMLRequest(data, method, featureType, projection, geomField, editLayer, formArray);
    var url = "<?php echo $baseAPIURL ?>/simplewfs/?token=<?php echo $datatoken; ?>&spatialdata=<?php echo $data; ?>"
    $.ajax({
	type: "POST",
	//url: "<?php echo $baseAPIURL ?>/simplewfs/?",
	url: url,
	//token: "<?php echo $datatoken ?>",
	//spatialdata: <?php echo $data; ?>,
	data: xmlString,
	dataType: "xml",
	success: function(msg){
	    var stringMsg = new XMLSerializer().serializeToString(msg);
	    editLayer.clearLayers();
	    editLayer.pm.disable();
	    editLayer.closePopup();
	    editLayer.unbindPopup();
	    wms.setParams({fake: Date.now()}, false);//cache buster
	    wms.redraw();//reload wms
	    wms.setOpacity(1);
	},
	error: function(msg){
	    console.log("Error editing features. " + msg);
	}
    });

}

function deleteWFS(featureID, data, workspace, idField, editLayer, wms, mymap){
    //var typeName = workspace + ":" + data;
    var typeName = data;
    xmlString = `<wfs:Transaction service="WFS" version="1.0.0"
  xmlns:cdf="http://www.opengis.net/cite/data"
  xmlns:ogc="http://www.opengis.net/ogc"
  xmlns:wfs="http://www.opengis.net/wfs"
  xmlns:topp="http://www.openplans.org/topp">
  <wfs:Delete typeName="`+typeName+`">
    <ogc:Filter>
      <ogc:PropertyIsEqualTo>
        <ogc:PropertyName>`+idField+`</ogc:PropertyName>
        <ogc:Literal>`+featureID+`</ogc:Literal>
      </ogc:PropertyIsEqualTo>
    </ogc:Filter>
  </wfs:Delete>
</wfs:Transaction>`;
    var url = "<?php echo $baseAPIURL ?>/simplewfs/?token=<?php echo $datatoken; ?>&spatialdata=<?php echo $data; ?>"
$.ajax({
	type: "POST",
    //url: "<?php echo $baseAPIURL ?>/simplewfs/?",
    url: url,
	data: xmlString,
	dataType: "xml",
	success: function(msg){
	    var stringMsg = new XMLSerializer().serializeToString(msg);
	    editLayer.clearLayers();
	    //editLayer.pm.disable();
	    editLayer.closePopup();
	    wms.setParams({fake: Date.now()}, false);//cache buster
	    wms.redraw();//reload wms
	    wms.setOpacity(1);
	},
	error: function(msg){
	    console.log("Error adding features. " + msg);
	}
    });
    
}

function getFeatureType(msg, data){
    //get type of feature from wfs getfeatureinfo request (point, line or polygon)
    //console.log(msg);
    var s = new XMLSerializer();
    var a = msg.getElementsByTagName("xsd:element");
    //var describe = s.serializeToString(a);
    for (i=0; i < a.length; i++){
	//var curName = s.serializeToString(i.attributes.name);
	node = a[i];
	if (s.serializeToString(node.attributes.name) == "Shape"){
	    var curType = s.serializeToString(node.attributes.type);
	    return curType;
	}
    }
}

function getIDFromPopup(popupHTML){
    //currently pretty weak.  will only recognize OBJECTID as ID field
    parser = new DOMParser();
    xmlDoc = parser.parseFromString(popupHTML,"text/xml");
    var s = new XMLSerializer()
    var a = xmlDoc.getElementsByTagName("tr");
    for (i=0; i<a.length; i++){
	var node = a[i];
	var nodeString = s.serializeToString(node);
	if (nodeString.includes("OBJECTID")){
	    return nodeString.replace(/\D/g, "");
	}
    }
}

function getWFSFeatureFromId(baseURL, data, Id, token, spatialData){
    var dataString = "&typeNames="  + data;
    var IdString = "&featureID=" + Id;
    var tokenString = "&token=" + token;
    var spatialDataString = "&spatialdata=" + spatialData;
    //http://regionalroads.com:8080/geoserver/dev/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=dev%3AMFP_CapacityChange_LINE_dev&featureID=6&outputFormat=application%2Fjson
    var formatString = "&outputFormat=application%2Fjson";
    var wfsRequest = baseURL + "api.regionalroads.com/simplewfs/?&service=wfs&request=GetFeature" + dataString + IdString +formatString + tokenString + spatialDataString;
    return new Promise((resolve, reject)=>{
	$.ajax({
	    type: "GET",
	    url: wfsRequest,
	    dataType: "json",
	    success: function(featureData){
		resolve(featureData);
	    }
	});
    });
}

function getEditPopupForm(msg, featureProperties, fkJson){
    //create html form for use in popup.  Dynamically generated using fields / types from wfs getfeaturerequest
    var htmlForm = '<h2>Edit Feature</h2><form id="editAttributesForm" action="nada" onsubmit="return false">';
    var xmlDoc = msg;
    var s = new XMLSerializer();
    //var a, childNode = xmlDoc.getElementsByTagName("xsd:sequence").childNodes;
    var a = xmlDoc.getElementsByTagName("xsd:element");
    for (i=0; i < a.length; i++){
	//var curName = s.serializeToString(i.attributes.name);
	node = a[i];
	if (s.serializeToString(node.attributes.name) != "<?php echo $data ?>"){
	    var curName = s.serializeToString(node.attributes.name);
	    var curLabel = curName +"Label";
	    var curType = s.serializeToString(node.attributes.type);
	    var curNillable = s.serializeToString(node.attributes.nillable);
	    var curValue = featureProperties[curName];
	    //curName = curName.substr(curName.indexOf(":"),curName.length);
	    curType = curType.substr(curType.indexOf(":")+1,curType.length);
	    if (curType=='date'){
		curValue = curValue.substr(0,10);
	    }
	    //curNillable = curNillable.substr(curNillable.indexOf(":"),curNillable.length);
	    if (curName == "OBJECTID" || curName == "Shape"){
		htmlForm+='<label for="'+curName+'" style="display:none">'+curName+'</label>';		
	    }
	    else{
		htmlForm+='<label for="'+curName+'">'+curName+'</label><br>';
	    }	    
	    if(foreignKeyField(fkJson, curName)==false){
		var formType = getFormType(curType, curName);
		if (formType=="hidden"){
		    htmlForm+='<input type="'+formType+'" id="'+curName+'" name="'+curName+'" value="'+curValue+'">';
		}
		else if (formType=="textarea"){
		    htmlForm+='<textarea id="'+curName+'" name="'+curName+'" rows="5" cols="45">'+curValue+'</textarea><br>'
		}
		else{
		    htmlForm+='<input type="'+formType+'" id="'+curName+'" name="'+curName+'" value="'+curValue+'"><br>';
		}
		//console.log(htmlForm);
	    }
	    else{
		htmlForm+=getForeignKeyDropDown(fkJson, curName, curValue);
	    }
	}

    }
    htmlForm+='<button id="editAttributesButton">Save</button>';
htmlForm+='</form>';
    return htmlForm;
}

function subLayers(wmsLayer, newLayerName){
    wmsLayer.setParams({layers: newLayerName});
    wmsLayer.setParams({query_layers: newLayerName});
    wmsLayer.setParams({fake: Date.now()}, false);//cache buster
    wmsLayer.redraw();//reload wms
    }*/

//Main Program

$(document).ready(function() {// load document
    $( document ).tooltip({
	track: true,
	position: {
	    my: "center bottom+50"
	}
    });
    var editSession=false;
    /*$("#editToolbar").hide();
    <?php
    if($editsession==TRUE){
	echo "editSession = true;\n";
    }
    ?>*/
//var namespace = "dev";
    //var spatialData = "<?php #echo $data ?>";    //load feature attributes and types
    //var truncatedSpatialData = truncateSpatialData(spatialData);
    var baseAPIURL = "<?php echo $baseAPIURL; ?>";
    var baseURL = "<?php echo $baseURL; ?>";
    var token = "<?php echo $datatoken ?>";
    var mymap = new L.Map('mapid', { center: new L.LatLng(49.164511, -122.863108), zoom: 10, crs:L.CRS.EPSG3857, zoomControl: false });
	//var drawnItems = L.featureGroup().addTo(mymap);
	/*var mapBaseMap = L.tileLayer('', {
	    maxZoom: 18,
	    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
		'<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
		'Imagery ©? <a href="https://www.mapbox.com/">Mapbox</a>',
	    id: 'mapbox/streets-v11',
	    tileSize: 512,
	    zoomOffset: -1
        }).addTo(mymap);
    var mapLink = 
            '<a href="http://www.esri.com/">Esri</a>';
        var wholink = 
            'i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';
        var curBaseMap = L.tileLayer(
            '', {
            attribution: '&copy; '+mapLink+', '+wholink,
            maxZoom: 18,
            });*/
        //var mrn = "GM_MRN";
    //var termPermit = "GM_TermPermitRoutes_dev";
        //var osowHeight = "GM_OSOWRoutes_Height_dev";
        //var osowWeight = "GM_OSOWRoutes_Weight_dev";
        //var osowWidth = "GM_OSOWRoutes_Width_dev";
        //var overheadObstructions = "GM_OverheadStructures_dev"
        var trnSurvey = 'MFP_TruckRouteSurveyFull_dev';
        var provincialHighways = 'GM_ProvincialHighways_dev';
        var mrnLayer = L.tileLayer.betterWms(baseAPIURL +'/wms/?', {
	    layers: 'MFP_PavementConditionSurvey2020Full_dev',
	    query_layers: 'MFP_PavementConditionSurvey2020Full_dev',
	    token: token,
	    format: 'image/png',
	transparent: 'true',
	tiled:'true',
        //fake: Date.now(),
	styles: 'redline',
	srs:'EPSG:4326'
    }).addTo(mymap);
        var bcParkwayLayer = L.tileLayer.betterWms(baseAPIURL +'/wms/?', {
	    layers: 'BCP_BCParkwayPath_dev',
	    query_layers: 'BCP_BCParkwayPath_dev',
	    token: token,
	    format: 'image/png',
	transparent: 'true',
	tiled:'true',
        //fake: Date.now(),
	styles: 'redline',
	srs:'EPSG:4326'
    }).addTo(mymap);
        /*var mrnProvincialLayer = L.tileLayer.betterWms(baseAPIURL +'/wms/?', {
	    layers: 'MFP_PavementConditionSurvey2020Provincial_dev',
	    query_layers: 'MFP_PavementConditionSurvey2020Provincial_dev',
	    token: token,
	    format: 'image/png',
	transparent: 'true',
	tiled:'true',
        //fake: Date.now(),
	styles: 'redline',
	srs:'EPSG:4326'
    });
        var mrnMunicipalLayer = L.tileLayer.betterWms(baseAPIURL +'/wms/?', {
	    layers: 'MFP_PavementConditionSurvey2020Municipal_dev',
	    query_layers: 'MFP_PavementConditionSurvey2020Municipal_dev',
	    token: token,
	    format: 'image/png',
	transparent: 'true',
	tiled:'true',
	fake: Date.now(),
	styles: 'redline',
	srs:'EPSG:4326'
    });*/
    var provincialHighwayLayer = L.tileLayer.betterWms(baseAPIURL +'/wms/?', {
	layers: provincialHighways,
	query_layers: provincialHighways,
	token: token,
	format: 'image/png',
	transparent: 'true',
	tiled:'true',
    styles: 'ProvincialHighway',
	srs:'EPSG:4326'
        });
    var trnSurveyLayer = L.tileLayer.betterWms(baseAPIURL +'/wms/?', {
	layers: 'MFP_TruckRouteSurveyFull_dev',
	query_layers: 'MFP_TruckRouteSurveyFull_dev',
	token: token,
	format: 'image/png',
	transparent: 'true',
    styles: 'redline',
	tiled:'true',
	//fake: Date.now(),
	srs:'EPSG:4326'
    }).addTo(mymap);
    /*var trnSurveyProvincialLayer = L.tileLayer.betterWms(baseAPIURL +'/wms/?', {
	layers: 'MFP_TruckRouteSurveyProvincial_dev',
	query_layers: 'MFP_TruckRouteSurveyProvincial_dev',
	token: token,
	format: 'image/png',
	transparent: 'true',
    styles: 'redline',
	tiled:'true',
	//fake: Date.now(),
	srs:'EPSG:4326'
    });*/
    /*var trnSurveyMunicipalLayer = L.tileLayer.betterWms(baseAPIURL +'/wms/?', {
	layers: 'MFP_TruckRouteSurveyMunicipal_dev',
	query_layers: 'MFP_TruckRouteSurvey_dev',
	token: token,
	format: 'image/png',
	transparent: 'true',
	tiled:'true',
    styles: 'redline',
	//fake: Date.now(),
	srs:'EPSG:4326'
    });
    var municipalLayerGroup = L.layerGroup([trnSurveyMunicipalLayer, mrnMunicipalLayer]).addTo(mymap);
    var provincialLayerGroup = L.layerGroup([trnSurveyProvincialLayer, mrnProvincialLayer]).addTo(mymap);
    /*var overheadObstructionsLayer = L.tileLayer.betterWms(baseURL +'api.regionalroads.com/wms/?', {
	layers: overheadObstructions,
	query_layers: overheadObstructions,
	token: token,
	format: 'image/png',
	transparent: 'true',
	tiled:'true',
	fake: Date.now(),
	srs:'EPSG:4326'
    });*/
    var layerControl = {"MRN Pavement Condition Survey (690 km)": mrnLayer,
			"TRN LiDAR Survey (205 km)": trnSurveyLayer,
                        "BC Parkway (26 km)": bcParkwayLayer,
                        "Provincial Highway (Reference Layer)": provincialHighwayLayer             
		       };
    var baseMapControl = {"Imagery": curBaseMap,
                          "Map": mapBaseMap};
    //var layerControl = {"<?php echo $data; ?>": wmsLayer};
    var layerControlObj = L.control.layers(baseMapControl, layerControl, {
	collapsed: false,
	position: 'bottomleft'
    }).addTo(mymap);
    $('#basemapSelectorForm').change(function(){
        var selectedValue = $("input[name='basemap-selector']:checked").val();
	if (selectedValue=="basemap-map"){
	    var newBaseMap = L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
	    maxZoom: 18,
	    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
		'<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
		'Imagery ©? <a href="https://www.mapbox.com/">Mapbox</a>',
	    id: 'mapbox/streets-v11',
	    tileSize: 512,
	    zoomOffset: -1
	});
	}
	else if(selectedValue=="basemap-imagery"){
	    var newBaseMap = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '&copy; '+mapLink+', '+wholink,
            maxZoom: 18,
            });
	}
	curBaseMap.remove();
	curBaseMap = newBaseMap;
	curBaseMap.addTo(mymap);
	curBaseMap.bringToBack();
    });
	<?php
    if ($editsession==TRUE){
	echo "describeFeatureType(spatialData).then(msg=>{";
    }
	?>
/*	getForeignKeys(spatialData, token).then(fkJson=>{
	    //console.log(fkJson);
	    fkJson = JSON.parse(fkJson);	    
	    if (editSession==true){
		//if you are editing a layer you must have a corresponding 'display layer view' created with the suffix '_view'
		var wmsDisplayLayers = wmsLayers+"_view";
		subLayers(wmsLayer, wmsDisplayLayers);
	var featureType = getFeatureType(msg, spatialData);
	//make toolbar visible/active
	$("#editToolbar").show();
	var editLayer = L.featureGroup();
	var featureMode = '';
	mymap.on('pm:create', e => {	    
	    if (featureType=="gml:MultiPointPropertyType" || featureType=="gml:PointPropertyType"){
		//point
		editLayer.addLayer(e['marker']);
		mymap.pm.enableDraw('Marker');
		featureMode = 'Marker';
	    }
	    else if (featureType=="gml:MultiCurvePropertyType" || featureType=="gml:MultiLine" || featureType=="gml:Line"){
		//line
		editLayer.addLayer(e['layer']);
		mymap.pm.enableDraw('Line');
		featureMode = 'Line';
	    }
	    else if (featureType=="gml:MultiSurfacePropertyType" || featureType=="gml:Polygon" || featureType=="gml:MultiPolygon"){
		//polygon
		editLayer.addLayer(e['layer']);
		mymap.pm.enableDraw('Polygon');
		featureMode = 'Polygon';
	    }
	    editLayer.eachLayer(function(layer){
	    });	    
	});
	//Add Features
	var addFeatureSession = false;
	$("#cancelAddButton").click(function(){
	    addFeatureSession=false;
	    $(document).tooltip('disable');
	    $("#mapid").attr('title', '');
	    //disable draw mode	   
	    mymap.pm.disableDraw('Line');
	    mymap.pm.disableDraw('Point');
	    mymap.pm.disableDraw('Polygon');
	    editLayer.addTo(mymap);
	    editLayer.clearLayers();
	    mymap.removeLayer(editLayer);
	    mymap.eachLayer(function(layer){
	    });
	    mymap.closePopup();
	    wmsLayer.setParams({fake: Date.now()}, false);//cache buster
	    wmsLayer.redraw();//reload wms
	    wmsLayer.setOpacity(1);
	    mymap.on('click', wmsLayer.getFeatureInfo, wmsLayer);
	    $("#addbutton").html("Add Feature");
	    $("#cancelAddButton").hide();
	    $("#editbutton").show();
	    $("#addbutton").show()
	    $("#deleteButton").show();
	});
		$("#cancelEditButton").click(function(){
		    subLayers(wmsLayer, wmsDisplayLayers);
	    $(document).tooltip('disable');
	    $("#mapid").attr('title', '');
	    if (mymap.pm.globalDragModeEnabled()){
		mymap.pm.toggleGlobalDragMode();
	    }
	    if (mymap.pm.globalRemovalEnabled()){
		mymap.pm.toggleGlobalRemovalMode();
	    }
		    if (mymap.pm.globalEditEnabled()){
			mymap.pm.toggleGlobalEditMode();
		    }
	    editFeatureSession=false;
	    armEditClick=false;
	    addToFeatureSession=false;
	    //disable edit mode
	    mymap.pm.disableDraw('Line');
	    mymap.pm.disableDraw('Marker')
	    mymap.pm.disableDraw('Polygon')
	    editLayer.addTo(mymap);
	    editLayer.clearLayers();
	    mymap.removeLayer(editLayer);
	    mymap.closePopup();
	    editLayer.unbindPopup();
	    wmsLayer.setParams({fake: Date.now()}, false);//cache buster
	    wmsLayer.redraw();//reload wms
	    wmsLayer.setOpacity(1);
	    mymap.on('click', wmsLayer.getFeatureInfo, wmsLayer);
	    //enable popups
	    $("#editbutton").html("Edit Feature");
	    $("#addToFeatureButton").html("Add to Feature");
	    $("#addToFeatureButton").hide();
	    $("#cancelEditButton").hide();
	    $("#addbutton").show();
	    $("#editbutton").show();
	    $("#deleteButton").show();
	});
	$("#cancelDeleteButton").click(function(){
	    $(document).tooltip('disable');
	    $("#mapid").attr('title', '');
	    deleteFeatureSession=false;
	    armDeleteClick=false;
	    editLayer.addTo(mymap);
	    editLayer.clearLayers();
	    mymap.removeLayer(editLayer);
	    mymap.closePopup();
	    editLayer.unbindPopup();
	    subLayers(wmsLayer, wmsDisplayLayers);
	    wmsLayer.setOpacity(1);
	    mymap.on('click', wmsLayer.getFeatureInfo, wmsLayer);
	    $("#cancelDeleteButton").hide();
	    $("#deleteButton").html("Delete Feature");
	    $("#deleteButton").show();
	    $("#editbutton").show();
	    $("#addbutton").show();
	    
	});
	$("#addbutton").click(function(){
	    $("#cancelAddButton").show();
	    $("#editbutton").hide()
	    $("#deleteButton").hide()
	    if (addFeatureSession==false){
		addFeatureSession = true;
		$('#addbutton').html('Finish Feature');
		if (featureType=="gml:MultiPointPropertyType" || featureType=="gml:PointPropertyType"){
		    mymap.pm.enableDraw('Marker');
		}
		else if (featureType =="gml:MultiCurvePropertyType"){
		    mymap.pm.enableDraw('Line');
		}
		else if (featureType =="gml:MultiSurfacePropertyType"){
		    mymap.pm.enableDraw('Polygon');
		}
		else{
		    //unsupported
		}
	    }
	    else{
		addFeatureSession = false;
		$('#addbutton').html('Add Feature');
		mymap.pm.disableDraw('Marker');
		mymap.pm.disableDraw ('Line');
		mymap.pm.disableDraw ('Polygon');
		$("#addbutton").hide()
		//$("#editbutton").show();
		//$("#deleteButton").show();
		editLayer.addTo(mymap);
		if (editLayer['pm']['_layers'].length!=0){
		    var htmlForm = getPopupForm(msg, fkJson);
                    var popupContent = htmlForm;
                    editLayer.bindPopup(popupContent).openPopup();
		}
	    }
	});
	$("#mapid").on('click', '#addAttributesButton', function(){
	    addWFS("#addAttributesForm", editLayer, wmsLayer, featureType, "Insert", spatialData, mymap);
	    $(document).tooltip('disable');
	    $("#mapid").attr('title', '');
	    $("#addbutton").html("Add Feature");
	    $("#addbutton").show();
	    $("#editbutton").show();
	    $("#deleteButton").show();
	    $("#cancelAddButton").hide();
	});
	//Edit Feature
	var editFeatureSession = false;
	var editFeatureGroup = L.featureGroup();
	var curID, popupContent;
	var editXMLRequest;
	var armEditClick = false;
	var addToFeatureSession = false;
	$('#addToFeatureButton').click(function(){
	    //enavle draw mode
	    if (addToFeatureSession==false){
		$("#addToFeatureButton").html("Save");
		$("#editbutton").hide();
		addToFeatureSession=true;
		//hide edit button
		//disable edit mode on editLayer but leave it on the map
		editLayer.pm.disable();
		editLayer.closePopup();
		editLayer.unbindPopup();
		mymap.off('click', wmsLayer.getFeatureInfo, wmsLayer);
		//disable popup
		if (featureType=="gml:MultiPointPropertyType" || featureType=="gml:PointPropertyType"){
		    mymap.pm.enableDraw('Marker');
		}
		else if (featureType =="gml:MultiCurvePropertyType"){
		    mymap.pm.enableDraw('Line');
		}
		else if (featureType =="gml:MultiSurfacePropertyType"){
		    mymap.pm.enableDraw('Polygon');
		}
		else{
		    //unsupported
		}

	    }
	    else{
		addToFeatureSession=false;
		editFeatureSession=false;
		//add drawn features to editLayer
		mymap.pm.disableDraw('Marker');
		mymap.pm.disableDraw ('Line');
		mymap.pm.disableDraw ('Polygon');
		var editFormArray = $("editAttributesForm").serializeArray();
		editXMLRequest = buildXMLRequest(spatialData, "Update", featureType, "4326", "Shape", editLayer, editFormArray, curID);
		editWFS(editXMLRequest, wmsLayer, editLayer, mymap);
		$("#addToFeatureButton").html("Add To Feature");
		$("#addToFeatureButton").hide();
		//show edit button
		$("#editbutton").html("Edit Feature");
		$("#editbutton").show();
		$("#addbutton").show();
		$("#editbutton").show();
		$("#deleteButton").show();
		$("#cancelEditButton").hide();
		subLayers(wmsLayer, wmsDisplayLayers);
		mymap.on('click', wmsLayer.getFeatureInfo, wmsLayer);
		//change edit button html back to 'edit features'
		//perform editWFS
	    }
	});
		$("#editbutton").click(function(){
		    subLayers(wmsLayer, spatialData);
	    $("#cancelEditButton").show();
	    $("#addbutton").hide()
	    $("#deleteButton").hide()
	    $("#editbutton").hide();
	    armEditClick = true;
	    if (editFeatureSession==false){
		//$(document).tooltip( "option", "content", "Click on a feature to begin editing");
		//$("#mapid").dialog("option","title","Click on a feature to begin editing");
		$("#mapid").attr('title', 'Click on a feature');
		$(document).tooltip('enable');
		editFeatureSession=true;
		curID = undefined;
		mymap.on('popupopen', function(e) {
		    if (armEditClick){
			//show add to feature
			$('#addToFeatureButton').show();
			$(document).tooltip('disable');
			$("#mapid").attr('title', '');
			$("#editbutton").show();
			mymap.closePopup();
			popupContent = e['popup']['_content'];
			curID = getIDFromPopup(popupContent);
			mymap.off('click', wmsLayer.getFeatureInfo, wmsLayer);
			wmsLayer.setOpacity(0);
			getWFSFeatureFromId(baseURL, spatialData, curID, token, spatialData).then(featureData=>{
			    //mymap.openPopup();
			    var editPopupContent = getEditPopupForm(msg, featureData['features'][0]['properties'], fkJson);
			    //var geoJsonLayer = L.GeoJSON.geometryToLayer(data['features'][0]).addTo(mymap);
			    var geoJsonLayer = L.GeoJSON.geometryToLayer(featureData['features'][0]);
				editLayer.addLayer(geoJsonLayer);
			    //add geoJson Layer to FeatureGroup
			    if (featureType=='gml:MultiPointPropertyType' || featureType=='gml:PointPropertyType'){
				editLayer.addTo(mymap);
				editLayer.pm.enable();
				editLayer.setStyle({'color': '#e4f00a',
						    'weight':3});
				editLayer.bindPopup(editPopupContent);
				if (mymap.pm.globalEditEnabled()==false){
				    mymap.pm.toggleGlobalEditMode();
				}
			    }
			    editLayer.addTo(mymap);
			    editLayer.pm.enable();
			    editLayer.setStyle({'color': '#e4f00a',
						    'weight':3});
			    editLayer.bindPopup(editPopupContent);
			});
			armEditClick = false;
		    }
		});
		$('#editbutton').html('Save');
	    }
	    else{
		if (mymap.pm.globalDragModeEnabled()){
		    mymap.pm.toggleGlobalDragMode();
		}
		if (mymap.pm.globalRemovalEnabled()){
		    mymap.pm.toggleGlobalRemovalMode();
		    
		}
		if (mymap.pm.globalEditEnabled()){
		    mymap.pm.toggleGlobalEditMode();
		    
		}
		editFeatureSession = false;
		armEditClick=false;
		$('#editbutton').html('Edit Feature');
		var editFormArray = $("#editAttributesForm").serializeArray();
		editXMLRequest = buildXMLRequest(spatialData, "Update", featureType, "4326", "Shape", editLayer, editFormArray, curID);
		editWFS(editXMLRequest, wmsLayer, editLayer, mymap);
		$("#cancelEditButton").hide();		
		$("#addbutton").show();
		$("#deleteButton").show();
		$("#editbutton").show();
		$("#addToFeatureButton").hide();
		subLayers(wmsLayer, wmsDisplayLayers);		
		mymap.on('click', wmsLayer.getFeatureInfo, wmsLayer);
	    }	    	 
	});
		$("#mapid").on('click', '#editAttributesButton', function(){
		    $("#editbutton").trigger("click");	   
	});
	var deleteFeatureSession = false;
	var deleteFeatureGoup = L.featureGroup();
	var curDeleteID, deletePopupContent;
	var deleteXMLRequest;
	var armDeleteClick = false;
		$("#deleteButton").click(function(){
		    subLayers(wmsLayer, spatialData);		    
	    armDeleteClick = true;
	    $("#cancelDeleteButton").show();
	    $("#editbutton").hide();
	    $("#addbutton").hide();
	    $("#deleteButton").html('Confirm Delete');
	    $("#deleteButton").hide();
	    if (deleteFeatureSession==false){
		$("#mapid").attr('title', 'Click on a feature');
		$(document).tooltip('enable')
		curDeleteID = undefined;
		deleteFeatureSession = true;
		mymap.on('popupopen', function(e){
		    if (armDeleteClick){
			$(document).tooltip('disable');
			$("#mapid").attr('title', '');
			$("#deleteButton").show();
			mymap.closePopup();
			popupContent = e['popup']['_content'];
			armDeleteClick = false;
			curDeleteID = getIDFromPopup(popupContent);			
			mymap.off('click', wmsLayer.getFeatureInfo, wmsLayer);
			wmsLayer.setOpacity(0);
			getWFSFeatureFromId(baseURL, spatialData, curDeleteID, token, spatialData).then(data=>{
			    var geoJsonLayer = L.GeoJSON.geometryToLayer(data['features'][0]).addTo(mymap);
			    editLayer.addLayer(geoJsonLayer);
			    editLayer.addTo(mymap);
			    editLayer.setStyle({'color': '#f00a0a',
					       'weight':3});
			});
		    }
		});
	    }
	    else{
		$("#deleteButton").html('Delete Feature');
		deleteWFS(curDeleteID, spatialData, "dev", "OBJECTID", editLayer, wmsLayer, mymap);
		mymap.closePopup();
		$(document).tooltip('disable');
		$("#mapid").attr('title', '');
		$("#cancelDeleteButton").hide();
		$("#editbutton").show();
		$("#addbutton").show();
		$("#deleteButton").show();
		deleteFeatureSession = false;
		armDeleteClick=false;
		subLayers(wmsLayer, wmsDisplayLayers);
		mymap.on('click', wmsLayer.getFeatureInfo, wmsLayer);
	    }
	    //delete button becomes 'confirm delete'
	    //user clicks on wms feature they wish to delete
	    //add feature to editLayer in bright red
	    //reload wms to filter out feature just added to editLayer
	    //user clicks confirm delete
	    //modal comes up: "Are you sure you wish to delete this feature? OK / Cancel
	});
    //-------------------------------------------
	    }
	});
	<?php
    if ($editsession==TRUE){
	echo"\n});";
    }
	?>
*/
});

	</script>
    </body>
    </html>
