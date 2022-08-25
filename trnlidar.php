<?php
require 'headerpublic.php';
require_once '../support/environmentsettings.php';
$datatoken = 'public';
?>

<html>
<style>
	#mapid {
		height: 100%;
		cursor: pointer;
		clear: both;
	}

	table {
		font-family: arial, sans-serif;
		border-collapse: collapse;
		table-layout: fixed;
		width: 100%;
	}

	td {
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
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin="" />
	<link rel="stylesheet" type="text/css" href="leaflet-geoman.css">
	<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>
	<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.js"></script>
	<script src="L.TileLayer.BetterWMS.js"></script>
	<script src="leaflet-geoman.min.js"></script>
	<script src="AppToken.js"></script>
	<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
</head>

<body>
	<div class="clearfloat">
		<!--	    <a href="index.php" class="headerlink btn btn-primary btn-block btn-large" style="clear:right">< Return to Data</a>-->
	</div>
	<div id="titleContainer">
		<h4 style="margin: 3px;">2020 MRN Pavement Condition Assessment (PCA) & LiDAR Survey Scope (FINAL)</h4>
	</div>
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
	<div id="trnlegend"><img src="<?php echo $baseURL; ?>regionalroads.com/img/trnlidarlegend.png" style="width:300px;"></div>

	<div id="mapid"></div>

	<script>
		function getForeignKeys(data, token) {
			//call wfs DescribeFeatureType to get all fields and types
			return new Promise((resolve, reject) => {
				$.ajax({
					type: "GET",
					url: "<?php echo $baseURL ?>regionalroads.com/fkList.php?table=" + data + "&token=" + token,
					//dataType: "json",
					asynch: true,
					success: function(fkJson) {
						resolve(fkJson);
						//console.log(fkJson);
					}
				});
			});
		}

		function truncateSpatialData(spatialData) {
			var truncateString = "_dev";
			var stringLength = spatialData.length;
			var startInt = stringLength - truncateString.length;
			if (spatialData.substring(startInt, stringLength) == truncateString) {
				return spatialData.substring(0, startInt);
			}
		}

		//Main Program

		$(document).ready(function() { // load document
			var appToken = new AppToken();
			appToken.check().then(msg => {
				var token = appToken.token;
			}).catch(msg => {
				var token = appToken.token;
			}).finally(msg => {
				$(document).tooltip({
					track: true,
					position: {
						my: "center bottom+50"
					}
				});


				var baseAPIURL = "<?php echo $baseAPIURL; ?>";
				var baseURL = "<?php echo $baseURL; ?>";
				var token = "<?php echo $datatoken ?>";
				var mymap = new L.Map('mapid', {
					center: new L.LatLng(49.164511, -122.863108),
					zoom: 10,
					crs: L.CRS.EPSG3857,
					zoomControl: false
				});
				//var drawnItems = L.featureGroup().addTo(mymap);
				var mapBaseMap = L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
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
					'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
						attribution: '&copy; ' + mapLink + ', ' + wholink,
						maxZoom: 18,
					});
				var overheadObstructions = "GM_OverheadStructures_dev"
				var trnSurvey = 'MFP_TruckRouteSurvey_dev';
				var provincialHighways = 'GM_ProvincialHighways_dev';
				var mrnLayer = L.tileLayer.betterWms(baseAPIURL + '/wms/?', {
					layers: 'MFP_PavementConditionSurvey2020_dev',
					query_layers: 'MFP_PavementConditionSurvey2020_dev',
					token: token,
					format: 'image/png',
					transparent: 'true',
					tiled: 'true',
					//fake: Date.now(),
					styles: 'redline',
					srs: 'EPSG:4326'
				}, appToken).addTo(mymap);
				var mrnProvincialLayer = L.tileLayer.betterWms(baseAPIURL + '/wms/?', {
					layers: 'MFP_PavementConditionSurvey2020Provincial_dev',
					query_layers: 'MFP_PavementConditionSurvey2020Provincial_dev',
					token: token,
					format: 'image/png',
					transparent: 'true',
					tiled: 'true',
					//fake: Date.now(),
					styles: 'redline',
					srs: 'EPSG:4326'
				}, appToken);
				var mrnMunicipalLayer = L.tileLayer.betterWms(baseAPIURL + '/wms/?', {
					layers: 'MFP_PavementConditionSurvey2020Municipal_dev',
					query_layers: 'MFP_PavementConditionSurvey2020Municipal_dev',
					token: token,
					format: 'image/png',
					transparent: 'true',
					tiled: 'true',
					fake: Date.now(),
					styles: 'redline',
					srs: 'EPSG:4326'
				}, appToken);
				var provincialHighwayLayer = L.tileLayer.betterWms(baseAPIURL + '/wms/?', {
					layers: provincialHighways,
					query_layers: provincialHighways,
					token: token,
					format: 'image/png',
					transparent: 'true',
					tiled: 'true',
					styles: 'ProvincialHighway',
					srs: 'EPSG:4326'
				}, appToken).addTo(mymap);
				var trnSurveyLayer = L.tileLayer.betterWms(baseAPIURL + '/wms/?', {
					layers: 'MFP_TruckRouteSurvey_dev',
					query_layers: 'MFP_TruckRouteSurvey_dev',
					token: token,
					format: 'image/png',
					transparent: 'true',
					styles: 'redline',
					tiled: 'true',
					//fake: Date.now(),
					srs: 'EPSG:4326'
				}, appToken).addTo(mymap);
				var trnSurveyProvincialLayer = L.tileLayer.betterWms(baseAPIURL + '/wms/?', {
					layers: 'MFP_TruckRouteSurveyProvincial_dev',
					query_layers: 'MFP_TruckRouteSurveyProvincial_dev',
					token: token,
					format: 'image/png',
					transparent: 'true',
					styles: 'redline',
					tiled: 'true',
					//fake: Date.now(),
					srs: 'EPSG:4326'
				}, appToken);
				var trnSurveyMunicipalLayer = L.tileLayer.betterWms(baseAPIURL + '/wms/?', {
					layers: 'MFP_TruckRouteSurveyMunicipal_dev',
					query_layers: 'MFP_TruckRouteSurvey_dev',
					token: token,
					format: 'image/png',
					transparent: 'true',
					tiled: 'true',
					styles: 'redline',
					//fake: Date.now(),
					srs: 'EPSG:4326'
				}, appToken);
				var municipalLayerGroup = L.layerGroup([trnSurveyMunicipalLayer, mrnMunicipalLayer]).addTo(mymap);
				var provincialLayerGroup = L.layerGroup([trnSurveyProvincialLayer, mrnProvincialLayer]).addTo(mymap);
				var layerControl = {
					"MRN LiDAR Survey (675 km)": mrnLayer,
					"TRN LiDAR Survey (110 km)": trnSurveyLayer,
					"Provincial/Federal LiDAR Survey (92 km)": provincialLayerGroup,
					"Municipal LiDAR Survey (14 km)": municipalLayerGroup,
					"Provincial Highway (Reference Layer)": provincialHighwayLayer
				};
				var baseMapControl = {
					"Imagery": curBaseMap,
					"Map": mapBaseMap
				};
				var layerControlObj = L.control.layers(baseMapControl, layerControl, {
					collapsed: false,
					position: 'bottomleft'
				}).addTo(mymap);
				$('#basemapSelectorForm').change(function() {
					var selectedValue = $("input[name='basemap-selector']:checked").val();
					if (selectedValue == "basemap-map") {
						var newBaseMap = L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
							maxZoom: 18,
							attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
								'<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
								'Imagery ©? <a href="https://www.mapbox.com/">Mapbox</a>',
							id: 'mapbox/streets-v11',
							tileSize: 512,
							zoomOffset: -1
						});
					} else if (selectedValue == "basemap-imagery") {
						var newBaseMap = L.tileLayer(
							'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
								attribution: '&copy; ' + mapLink + ', ' + wholink,
								maxZoom: 18,
							});
					}
					curBaseMap.remove();
					curBaseMap = newBaseMap;
					curBaseMap.addTo(mymap);
					curBaseMap.bringToBack();
				});
			});
		});
	</script>
</body>

</html>