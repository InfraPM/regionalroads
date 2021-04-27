<?php
require 'headerpublic.php';
require_once '../support/environmentsettings.php';
$datatoken = 'public';
?>
<style>
	#editMapDiv {
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
		<h4>2021 BICCS & WITT Eligibility</h4>
	</div>
	<div class="toolbar" id="basemapSelector" style="display:none">
		<form id="basemapSelectorForm" action="nada">
			<h4>Basemaps</h4>
			<input type="radio" id="basemap-imagery" name="basemap-selector" value="basemap-imagery" checked><label for="basemap-imagery">Imagery</label><br>
			<input type="radio" id="basemap-map" name="basemap-selector" value="basemap-map"><label for="basemap-map">Map</label><br>

		</form>
	</div>
	<div id="layerGroupSelector">
		<input type="radio" id="biccs" name="mode" value="biccs" checked>
		<label for="biccs">Select All BICCS Eligibility Layers</label><br>
		<input type="radio" id="witt" name="mode" value="witt">
		<label for="witt">Select All WITT Eligibility Layers</label><br>
	</div>
	<div id="editMapDiv"></div>

	<script>
		//Main Program

		$(document).ready(function() { // load document
			$('#biccs').click(function() {
				$('input[type="checkbox"][category="biccs"]:not(:checked)').trigger('click');
				$('input[type="checkbox"][category="both"]:not(:checked)').trigger('click');
				$('input[type="checkbox"][category="witt"]:checked').trigger('click');
			});
			$("#witt").click(function() {
				$('input[type="checkbox"][category="biccs"]:checked').trigger('click');
				$('input[type="checkbox"][category="both"]:not(:checked)').trigger('click');
				$('input[type="checkbox"][category="witt"]:not(:checked)').trigger('click');
			});
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
				this.title = "2021 BICCS / WITT Eligibility",
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
						layer3: {
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
						layer1: {
							name: "WITT_FTDA" + datasetSuffix,
							layerName: "WITT_FTDA" + datasetSuffix,
							displayName: "Frequent Transit Development Areas",
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
									layers: 'WITT_FTDA' + datasetSuffix,
									label: 'Frequent Transit Development Areas',
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
									layers: 'WITT_FTDA' + datasetSuffix,
									label: 'Frequent Transit Development Areas',
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
							name: "WITT_Top20BusBuffer400m" + datasetSuffix,
							layerName: "WITT_Top20BusBuffer400m" + datasetSuffix,
							displayName: "400m Walking Buffer (Top 20th Percentile Bus Stop)",
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
									layers: 'WITT_Top20BusBuffer400m' + datasetSuffix,
									label: '400m Walking Buffer (Top 20th Percentile Bus Stop)',
									category: 'witt',
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
									layers: 'WITT_Top20BusBuffer400m' + datasetSuffix,
									label: '400m Walking Buffer (Top 20th Percentile Bus Stop)',
									category: 'witt',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}
						},
						layer4: {
							name: "WITT_BLineBuffer800m" + datasetSuffix,
							layerName: "WITT_BLineBuffer800m" + datasetSuffix,
							displayName: "800m Walking Buffer (Rapid Bus Stops & Skytrain Stations)",
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
									layers: 'WITT_BLineBuffer800m' + datasetSuffix,
									label: '800m Walking Buffer (Rapid Bus Stops & Skytrain Stations)',
									category: 'witt',
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
									layers: 'WITT_BLineBuffer800m' + datasetSuffix,
									label: '800m Walking Buffer (Rapid Bus Stops & Skytrain Stations)',
									category: 'witt',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer5: {
							name: "BICCS_CycleZoneAnalysisMunicipal" + datasetSuffix,
							layerName: "BICCS_CycleZoneAnalysisMunicipal" + datasetSuffix,
							displayName: "High Cycling Potential (Top 20% within Municipality)",
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
									layers: 'BICCS_CycleZoneAnalysisMunicipal' + datasetSuffix,
									label: 'High Cycling Potential (Top 20% within Municipality)',
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
									layers: 'BICCS_CycleZoneAnalysisMunicipal' + datasetSuffix,
									label: 'High Cycling Potential (Top 20% within Municipality)',
									category: 'biccs',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer6: {
							name: "BICCS_CycleZoneAnalysisRegional" + datasetSuffix,
							layerName: "BICCS_CycleZoneAnalysisRegional" + datasetSuffix,
							displayName: "High Cycling Potential (Top 20% within Region)",
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
									layers: 'BICCS_CycleZoneAnalysisRegional' + datasetSuffix,
									label: 'High Cycling Potential (Top 20% within Region)',
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
									layers: 'BICCS_CycleZoneAnalysisRegional' + datasetSuffix,
									label: 'High Cycling Potential (Top 20% within Region)',
									category: 'biccs',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer7: {
							name: "BICCS_MBN" + datasetSuffix,
							layerName: "BICCS_MBN" + datasetSuffix,
							displayName: "Major Bikeway Network",
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
									layers: 'BICCS_MBN' + datasetSuffix,
									label: 'Major Bikeway Network',
									category: 'reference',
									token: token,
									format: 'image/png',
									transparent: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_MBN' + datasetSuffix,
									label: 'Major Bikeway Network',
									category: 'reference',
									token: token,
									format: 'image/png',
									transparent: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer18: {
							name: "GM_MRN" + datasetSuffix,
							layerName: "GM_MRN" + datasetSuffix,
							displayName: "Major Road Network",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: false,
								displayPopup: true
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'GM_MRN' + datasetSuffix,
									label: 'Major Road Network',
									category: 'reference',
									token: token,
									format: 'image/png',
									transparent: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'GM_MRN' + datasetSuffix,
									label: 'Major Road Network',
									styles: 'MRNBackground',
									category: 'reference',
									token: token,
									format: 'image/png',
									transparent: 'true',
									srs: 'EPSG:4326'
								}
							}
						},
						layer8: {
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
						layer9: {
							name: "BCRTC_SkytrainLines" + datasetSuffix,
							layerName: "BCRTC_SkytrainLines" + datasetSuffix,
							displayName: "Rapid Transit",
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
									layers: 'BCRTC_SkytrainLines' + datasetSuffix,
									label: 'Rapid Transit',
									category: 'reference',
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
									layers: 'BCRTC_SkytrainLines' + datasetSuffix,
									label: 'Rapid Transit',
									category: 'reference',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer10: {
							name: "WITT_FTN" + datasetSuffix,
							layerName: "WITT_FTN" + datasetSuffix,
							displayName: "FTN",
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
									layers: 'WITT_FTN' + datasetSuffix,
									label: 'FTN',
									category: 'reference',
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
									layers: 'WITT_FTN' + datasetSuffix,
									label: 'FTN',
									category: 'reference',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer11: {
							name: "CMBC_BusRoutes" + datasetSuffix,
							layerName: "CMBC_BusRoutes" + datasetSuffix,
							displayName: "Bus Routes",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: false,
								displayPopup: true
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'CMBC_BusRoutes' + datasetSuffix,
									query_layers: 'CMBC_BusRoutes' + datasetSuffix,
									label: 'Bus Route',
									category: 'reference',
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
									layers: 'CMBC_BusRoutes' + datasetSuffix,
									query_layers: 'CMBC_BusRoutes' + datasetSuffix,
									label: 'Bus Route',
									category: 'reference',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer12: {
							name: "CMBC_RapidBusRoutes" + datasetSuffix,
							layerName: "CMBC_RapidBusRoutes" + datasetSuffix,
							displayName: "Rapid Bus Routes",
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
									layers: 'CMBC_RapidBusRoutes' + datasetSuffix,
									query_layers: 'CMBC_BusRoutes' + datasetSuffix,
									label: 'Rapid Bus Route',
									category: 'reference',
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
									layers: 'CMBC_RapidBusRoutes' + datasetSuffix,
									query_layers: 'CMBC_BusRoutes' + datasetSuffix,
									label: 'Rapid Bus Route',
									category: 'reference',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer13: {
							name: 'CMBC_RapidBusStops' + datasetSuffix,
							layerName: 'CMBC_RapidBusStops' + datasetSuffix,
							displayName: "Rapid Bus Stops",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: false,
								displayPopup: true
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'CMBC_RapidBusStops' + datasetSuffix,
									query_layers: 'CMBC_RapidBusStops' + datasetSuffix,
									label: 'Rapid Bus Stop',
									category: 'reference',
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
									layers: 'CMBC_RapidBusStops' + datasetSuffix,
									query_layers: 'CMBC_RapidBusStops' + datasetSuffix,
									label: 'Rapid Bus Stop',
									category: 'reference',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer14: {
							name: 'BICCS_FerryTerminal' + datasetSuffix,
							layerName: 'BICCS_FerryTerminal' + datasetSuffix,
							displayName: "Ferry Terminals",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: false,
								displayPopup: true
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_FerryTerminal' + datasetSuffix,
									query_layers: 'BICCS_FerryTerminal' + datasetSuffix,
									label: 'Ferry Terminal',
									category: 'reference',
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
									layers: 'BICCS_FerryTerminal' + datasetSuffix,
									query_layers: 'BICCS_FerryTerminal' + datasetSuffix,
									label: 'Ferry Terminal',
									category: 'reference',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer15: {
							name: 'BCRTC_SkytrainStations' + datasetSuffix,
							layerName: 'BCRTC_SkytrainStations' + datasetSuffix,
							displayName: "Transit Stations",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: false,
								displayPopup: true
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BCRTC_SkytrainStations' + datasetSuffix,
									query_layers: 'BCRTC_SkytrainStations' + datasetSuffix,
									label: 'Transit Stations',
									category: 'reference',
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
									layers: 'BCRTC_SkytrainStations' + datasetSuffix,
									query_layers: 'BCRTC_SkytrainStations' + datasetSuffix,
									label: 'Transit Stations',
									category: 'reference',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer16: {
							name: 'WITT_FTNStops' + datasetSuffix,
							layerName: 'WITT_FTNStops' + datasetSuffix,
							displayName: "FTN Stop",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: false,
								displayPopup: true
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'WITT_FTNStops' + datasetSuffix,
									query_layers: 'WITT_FTNStops' + datasetSuffix,
									label: 'FTN Stop',
									category: 'reference',
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
									layers: 'WITT_FTNStops' + datasetSuffix,
									query_layers: 'WITT_FTNStops' + datasetSuffix,
									label: 'FTN Stop',
									category: 'reference',
									token: token,
									format: 'image/png',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer17: {
							name: 'CYCLE_BikeParkades' + datasetSuffix,
							layerName: 'CYCLE_BikeParkades' + datasetSuffix,
							displayName: "Bike Parkades",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: false,
								displayPopup: true
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'CYCLE_BikeParkades' + datasetSuffix,
									query_layers: 'CYCLE_BikeParkades' + datasetSuffix,
									label: 'Bike Parkade',
									category: 'reference',
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
									layers: 'CYCLE_BikeParkades' + datasetSuffix,
									query_layers: 'CYCLE_BikeParkades' + datasetSuffix,
									label: 'Bike Parkade',
									category: 'reference',
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
							"displayName": "Urban Centres",
							"wfstLayers": ['WITT_UrbanCentres' + datasetSuffix]
						},
						{
							"displayName": "Frequent Transit Development Areas",
							"wfstLayers": ["WITT_FTDA" + datasetSuffix]
						},

						{
							"displayName": "400m Walking Buffer (Top 20th Percentile Bus Stop)",
							"wfstLayers": ["WITT_Top20BusBuffer400m" + datasetSuffix]
						},

						{
							"displayName": "800m Walking Buffer (Rapid Bus Stops & Skytrain Stations)",
							"wfstLayers": ["WITT_BLineBuffer800m" + datasetSuffix]
						},

						{
							"displayName": "High Cycling Potential (Top 20% within Municipality)",
							"wfstLayers": ["BICCS_CycleZoneAnalysisMunicipal" + datasetSuffix]
						},

						{
							"displayName": "High Cycling Potential (Top 20% within Region)",
							"wfstLayers": ["BICCS_CycleZoneAnalysisRegional" + datasetSuffix]
						},

						{
							"displayName": "Major Bikeway Network",
							"wfstLayers": ["BICCS_MBN" + datasetSuffix]
						},

						{
							"displayName": "Major Bikeway Network 1 km Buffer",
							"wfstLayers": ["BICCS_MBNBuffer1km" + datasetSuffix]
						},

						{
							"displayName": "BCRTC_SkytrainLines" + datasetSuffix,
							"wfstLayers": ["BCRTC_SkytrainLines" + datasetSuffix]
						},

						{
							"displayName": "FTN" + datasetSuffix,
							"wfstLayers": ["WITT_FTN" + datasetSuffix]
						},

						{
							"displayName": "Major Road Network" + datasetSuffix,
							"wfstLayers": ["GM_MRN" + datasetSuffix]
						},

						{
							"displayName": "Bus Routes",
							"wfstLayers": ["CMBC_BusRoutes" + datasetSuffix]
						},

						{
							"displayName": "Rapid Bus Routes",
							"wfstLayers": ["CMBC_RapidBusRoutes" + datasetSuffix]
						},

						{
							"displayName": "Rapid Bus Stops",
							"wfstLayers": ["CMBC_RapidBusStops" + datasetSuffix]
						},

						{
							"displayName": "Ferry Terminals",
							"wfstLayers": ["BICCS_FerryTerminal" + datasetSuffix]
						},

						{
							"displayName": "Transit Stations",
							"wfstLayers": ["BCRTC_SkytrainStations" + datasetSuffix]
						},

						{
							"displayName": "FTN Stop",
							"wfstLayers": ["WITT_FTNStops" + datasetSuffix]
						},

						{
							"displayName": "Bike Parkades",
							"wfstLayers": ["CYCLE_BikeParkades" + datasetSuffix]
						}
					];
			}
			var appToken = new AppToken();
			appToken.check().then(msg => {
				var token = appToken.token;
			}).catch(msg => {
				var token = appToken.token;
			}).finally(msg => {
				var editMap = new EditMap(appToken, "editMapDiv", options);
			});
		});
	</script>
</body>

</html>