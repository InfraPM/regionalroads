<style>
 #mapId { height: 100%;
     cursor: pointer;
     clear: both;
 }
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
     font-size: 1em !important;
 }

 tr:nth-child(even) {
     background-color: #dddddd;
 }
</style>
<head>
    <link rel="stylesheet" href="/leaflet/leaflet.css" />
    <script src="/leaflet/leaflet.js"></script>
    <link rel="stylesheet" type="text/css" href="leaflet-geoman.css">
    <script
	    src="https://code.jquery.com/jquery-3.4.1.min.js"
	    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
	    crossorigin="anonymous"></script>
    <script src="L.TileLayer.BetterWMS.js"></script>
    <script src="leaflet-geoman.min.js"></script>
    <script
	src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"
	integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E="
	crossorigin="anonymous"></script>
    <script src="https://cdn.tiny.cloud/1/6uc033l4qvieb8jy3pxaj190siqq3ag35nqxzv7no2nvlrbq/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="editMap.js"></script>
    <script src="Wfst.js"></script>
    </head>
    <body>
	<div  class="clearfloat" style="padding-top:20px;padding-bottom:20px">
    </div>
    <div id="titleContainer"><h4></h4></div>
    
    <div id="controlContainer">
    <div class="toolbar" id="basemapSelector" style="display:none">
        <h4>Basemaps</h4>
    <form id="basemapSelectorForm" action="nada">

    <input type="radio" id="basemap-imagery" name="basemap-selector" value="basemap-imagery" checked><label for="basemap-imagery">Imagery</label><br>
    <input type="radio" id="basemap-map" name="basemap-selector" value="basemap-map"><label for="basemap-map">Map</label><br>
    
</form>
    </div>
    <div id="editModal" class="fadein"></div>
    <div id="commentModal" class="fadein"></div>
    <div class="toolbar fadein" id="editToolbar" style="display:none">
    <div id="editbuttoncontainer">
    <button type="button" id="startEditButton" class="btn btn-primary btn-block btn-large fadein">Start Editing</button>
    </div>
    <div id="addbuttoncontainer"><button type="button" id="addButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Add Features</button><button type="button" id="cancelAddButton" style="display:none" class="btn btn-primary btn-block btn-large fadein">Cancel</button></div>
    <div id="editbuttoncontainer"><button type="button" id="editButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Edit Features</button><button type="button" id="addToFeatureButton" style="display:none" class="fadein btn btn-primary btn-block btn-large">Add to Feature</button><button type="button" id="cancelEditButton" style="display:none" class=" fadein btn btn-primary btn-block btn-large">Cancel</button></div>
    <div id="deletebuttoncontainer"><button type="button" id="deleteButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Delete Features</button><button type="button" id="cancelDeleteButton" style="display:none" class="fadein btn btn-primary btn-block btn-large">Cancel</button></div>
    </div>
    </div>
    <div id="editMapDiv"></div>
    <div id ="popupDiv"></div>
    <script>
     $(document).ready(function() {	 
	 var baseAPIURL = "<?php echo $baseAPIURL; ?>";
	 var token = "<?php echo $datatoken; ?>";
	 var mapName = "<?php echo $mapName ?>";
	 var optionsURL = baseAPIURL + '/mapoptions/?mapName=' + mapName;
	 getOptions(optionsURL, token).then(data=>{
	     var editMapOptions = eval(data);
	     $("#titleContainer h4").html(editMapOptions.title);	
	     var editMap = new EditMap("editMapDiv", editMapOptions);
	 });
	 function getOptions(url, token){
	     var postData = {"token": token};
	     var postDataString = JSON.stringify(postData);
	     return new Promise((resolve, reject)=>{
		 $.ajax({
		     type: "POST",
		     url: url,
		     data: postDataString,
		     dataType: "text",
		     success: function(data){
			 resolve(data);
		     }
		 });
	     });
	 }
     });
    </script>
    </body>
    </html>
