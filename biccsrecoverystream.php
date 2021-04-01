<?php
require 'headerpublic.php';
require_once '../support/environmentsettings.php';
$datatoken = 'public';
?>
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
	<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
	<script src="https://cdn.tiny.cloud/1/6uc033l4qvieb8jy3pxaj190siqq3ag35nqxzv7no2nvlrbq/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
	<script src="AppToken.js"></script>
	<script src="editMap.js"></script>
	<script src="Wfst.js"></script>
</head>

<body>
	<div class="clearfloat" style="padding-top:20px">
		<!--	    <a href="index.php" class="headerlink btn btn-primary btn-block btn-large" style="clear:right">< Return to Data</a>-->
	</div>
	<div id="titleContainer">
		<h4>2021 BICCS Recovery Stream Eligibility</h4>
	</div>
	<div class="toolbar" id="basemapSelector" style="display:none">
		<form id="basemapSelectorForm" action="nada">
			<h4>Basemaps</h4>
			<input type="radio" id="basemap-imagery" name="basemap-selector" value="basemap-imagery" checked><label for="basemap-imagery">Imagery</label><br>
			<input type="radio" id="basemap-map" name="basemap-selector" value="basemap-map"><label for="basemap-map">Map</label><br>

		</form>
	</div>
	<div id="mapid"></div>

	<script>
		//Main Program

		$(document).ready(function() { // load document			
			$(document).tooltip({
				track: true,
				position: {
					my: "center bottom+50"
				}
			});
			var editSession = false;
			var baseAPIURL = "<?php echo $baseAPIURL; ?>";
			//var baseURL = "<?php echo $baseURL; ?>";
			var token = "<?php echo $datatoken ?>";
			var datasetSuffix = "";
			var options = new function() {
				this.title = "2021 BICCS Recovery Stream Eligibility",
					this.editable = false,
					this.mapOptions = {
						center: new L.LatLng(49.164511, -122.863108),
						zoom: 10,
						crs: L.CRS.EPSG3857,
						zoomControl: false,
						minZoom: 10,
						maxZoom: 15
					},
					this.token = token,
					this.baseAPIURL = baseAPIURL,
					this.wfstLayers = {
						layer1: {
							name: "WITT_UrbanCentres" + datasetSuffix,
							layerName: "WITT_UrbanCentres" + datasetSuffix,
							displayName: "Urban Centres",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: true,
								displayPopup: false
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'WITT_UrbanCentres' + datasetSuffix,
									label: 'Urban Centres',
									category: 'both',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'WITT_UrbanCentres' + datasetSuffix,
									label: 'Urban Centres',
									category: 'both',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}
						},
						layer2: {
							name: "BICCS_MBNBuffer1km" + datasetSuffix,
							layerName: "BICCS_MBNBuffer1km" + datasetSuffix,
							displayName: "Major Bikeway Network 1 km Buffer",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: true,
								displayPopup: false
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_MBNBuffer1km' + datasetSuffix,
									label: 'Major Bikeway Network 1 km Buffer',
									category: 'biccs',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_MBNBuffer1km' + datasetSuffix,
									label: 'Major Bikeway Network 1 km Buffer',
									category: 'biccs',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer3: {
							name: "BICCS_DemandEquity" + datasetSuffix,
							layerName: "BICCS_Equity" + datasetSuffix,
							displayName: "Cycling Equity Score",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: false,
								displayPopup: false
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_DemandEquity' + datasetSuffix,
									label: 'Cycling Equity Score',
									category: 'biccs',
									styles: 'BICCS_EquityScore',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_DemandEquity' + datasetSuffix,
									label: 'Cycling Demand Score',
									category: 'biccs',
									token: token,
									styles: 'BICCS_EquityScore',
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer4: {
							name: "BICCS_DemandEquity" + datasetSuffix,
							layerName: "BICCS_Demand" + datasetSuffix,
							displayName: "Latent Demand",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: false,
								displayPopup: false
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_DemandEquity' + datasetSuffix,
									label: 'Latent Demand',
									styles: 'BICCS_DemandScore',
									category: 'biccs',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_DemandEquity' + datasetSuffix,
									label: 'Latent Demand',
									category: 'biccs',
									styles: 'BICCS_DemandScore',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}
						},
						layer5: {
							name: "CYCLE_StateOfCycling" + datasetSuffix,
							layerName: "CYCLE_StateOfCycling" + datasetSuffix,
							displayName: "State Of Cycling in Metro Vancouver",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							styles: 'CyclingComfort',
							options: {
								visible: false,
								displayPopup: false
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'CYCLE_StateOfCycling' + datasetSuffix,
									label: 'State Of Cycling',
									category: 'biccs',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'CYCLE_StateOfCycling' + datasetSuffix,
									label: 'State Of Cycling',
									category: 'biccs',
									styles: 'CyclingComfort',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}
						}
					},
					this.featureGrouping = [{
							"displayName": "Latent Demand",
							"wfstLayers": ['BICCS_Demand' + datasetSuffix]
						},
						{
							"displayName": "Equity",
							"wfstLayers": ['BICCS_Equity' + datasetSuffix]
						},
						{
							"displayName": "Urban Centres",
							"wfstLayers": ['WITT_UrbanCentres' + datasetSuffix]
						},
						{
							"displayName": "Major Bikeway Network 1 km Buffer",
							"wfstLayers": ["BICCS_MBNBuffer1km" + datasetSuffix]
						},
						{
							"displayName": "Status of Completion - Regional Cycling Network",
							"wfstLayers": ['CYCLE_StateOfCycling' + datasetSuffix]
						}
					];
			}
			var appToken = new AppToken();
			appToken.check().then(msg => {
				var token = appToken.token;
				var editMap = new EditMap(appToken, "mapid", options);
			}).catch(msg => {
				var token = appToken.token;
			}).finally(msg => {
				var editMap = new EditMap(appToken, "mapid", options);
			});
		});
	</script>
</body>

</html>