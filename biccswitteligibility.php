<?php
require 'headerpublic.php';
require_once '../support/environmentsettings.php';
require __DIR__ . '/buildNumber.php';
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
		font-size: 1em !important;
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
	<script src="AppToken.js?<?php echo $_ENV['buildNumber']; ?>"></script>
	<script src="editMap.js?<?php echo $_ENV['buildNumber']; ?>"></script>
	<script src="Wfst.js?<?php echo $_ENV['buildNumber']; ?>"></script>
</head>

<body>
	<div class="clearfloat">
		<!--	    <a href="index.php" class="headerlink btn btn-primary btn-block btn-large" style="clear:right">< Return to Data</a>-->
	</div>
	<div id="titleContainer">
		<h4 style="margin: 3px;">2023 BICCS & WITT Eligibility</h4>
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
		<label for="biccs">View BICCS Allocated Eligibility Layers</label><br>
		<input type="radio" id="biccs-competitive" name="mode" value="biccs-competitive">
		<label for="biccs">View BICCS Competitive and Recovery Eligibility Layers</label><br>
		<input type="radio" id="witt" name="mode" value="witt">
		<label for="witt">View WITT Allocated and Competitive Eligibility Layers</label><br>
	</div>
	<div id="editModal" class="fadein"></div>
	<div id="commentModal" class="fadein"></div>
	<div id="imgModal" class="fadein"></div>
	<div id="exportModal" class="fadein"></div>
	<div id="chartModal" class="fadein"></div>
	<div class="toolbar fadein" id="editToolbar" style="display:none">
		<div id="editbuttoncontainer">
			<!--<button type="button" id="startEditButton" class="btn btn-primary btn-block btn-large fadein" style="display:none">Start Edit Session</button>-->
			<button type="button" id="startEditButton" class="btn-modal btn-large btn-block" style="display:none">Start Edit Session</button>
		</div>
		<div id="test"></div>
		<!--<div id="addbuttoncontainer"><button type="button" id="addButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Add Features</button><button type="button" id="cancelAddButton" style="display:none" class="btn btn-primary btn-block btn-large fadein">Cancel</button></div>
            <div id="editbuttoncontainer"><button type="button" id="editButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Edit Features</button><button type="button" id="addToFeatureButton" style="display:none" class="fadein btn btn-primary btn-block btn-large">Add to Feature</button><button type="button" id="cancelEditButton" style="display:none" class=" fadein btn btn-primary btn-block btn-large">Cancel</button></div>
            <div id="deletebuttoncontainer"><button type="button" id="deleteButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Delete Features</button><button type="button" id="cancelDeleteButton" style="display:none" class="fadein btn btn-primary btn-block btn-large">Cancel</button></div>-->
		<div id="addbuttoncontainer"><button type="button" id="addButton" class="fadein btn-modal btn-large btn-block" style="display:none">Add Features</button><button type="button" id="cancelAddButton" style="display:none" class="fadein btn-modal btn-large btn-block">Cancel</button></div>
		<div id="editbuttoncontainer"><button type="button" id="editButton" class="fadein btn-modal btn-large btn-block" style="display:none">Edit Features</button><button type="button" id="addToFeatureButton" style="display:none" class="fadein btn-modal btn-large btn-block">Add to Feature</button><button type="button" id="cancelEditButton" style="display:none" class="fadein btn-modal btn-large btn-block">Cancel</button></div>
		<div id="deletebuttoncontainer"><button type="button" id="deleteButton" class="fadein btn-modal btn-large btn-block" style="display:none">Delete Features</button><button type="button" id="cancelDeleteButton" style="display:none" class="fadein btn-modal btn-large btn-block">Cancel</button></div>
	</div>
	<div id="rightToolbar">
		<!---<div id="exportbuttoncontainer"><button type="button" id="exportButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Export Features</button></div>-->
		<div id="exportbuttoncontainer"><button type="button" id="exportButton" class="fadein btn-modal btn-large btn-block" style="display:none">Export Features</button></div>
		<!--<div id="chartbuttoncontainer"><button type="button" id="chartButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Charts</button></div>-->
		<div id="chartbuttoncontainer"><button type="button" id="chartButton" class="fadein btn-modal btn-large btn-block" style="display:none">Charts</button></div>
	</div>
	<div id="editMapDiv"></div>

	<script>
		//Main Program

		$(document).ready(function() { // load document			
			$('#biccs').click(function() {
				$('input[type="checkbox"][category="biccs"]').parent().css("display", "inline");
				$('input[type="checkbox"][category="biccs-and-biccs-competitive"]').parent().css("display", "inline");
				$('input[type="checkbox"][category="biccs-and-witt"]').parent().css("display", "inline");
				$('input[type="checkbox"][category="biccs-and-biccs-competitive-and-witt"]').parent().css("display", "inline");

				$('input[type="checkbox"][category="biccs"][default-visibility="visible"]:not(:checked)').trigger('click');
				$('input[type="checkbox"][category="biccs-and-biccs-competitive"][default-visibility="visible"]:not(:checked)').trigger('click');
				$('input[type="checkbox"][category="biccs-and-witt"][default-visibility="visible"]:not(:checked)').trigger('click');
				$('input[type="checkbox"][category="biccs-and-biccs-competitive-and-witt"][defualtVisiblity="visible"]:not(:checked)').trigger('click');

				$('input[type="checkbox"][category="biccs"][default-visibility="invisible"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-and-biccs-competitive"][default-visibility="invisible"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-and-witt"][default-visibility="invisible"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-and-biccs-competitive-and-witt"][defualtVisiblity="invisible"]:checked').trigger('click');

				$('input[type="checkbox"][category="biccs-competitive"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-competitive"]').parent().css("display", "none");
				$('input[type="checkbox"][category="witt"]:checked').trigger('click');
				$('input[type="checkbox"][category="witt"]:checked').parent().css("display", "none");
				$('input[type="checkbox"][category="witt"]').parent().css("display", "none");
				$('input[type="checkbox"][category="biccs-competitive-and-witt"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-competitive-and-witt"]').parent().css("display", "none");
			});
			$('#biccs-competitive').click(function() {
				$('input[type="checkbox"][category="biccs-competitive"][default-visibility="visible"]:not(:checked)').trigger('click');
				$('input[type="checkbox"][category="biccs-and-biccs-competitive"][default-visibility="visible"]:not(:checked)').trigger('click');
				$('input[type="checkbox"][category="biccs-and-biccs-competitive-and-witt"][default-visibility="visible"]:not(:checked)').trigger('click');
				$('input[type="checkbox"][category="biccs-competitive"][default-visibility="invisible"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-and-biccs-competitive"][default-visibility="invisible"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-and-biccs-competitive-and-witt"][default-visibility="invisible"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-competitive"]').parent().css("display", "inline");
				$('input[type="checkbox"][category="biccs-and-biccs-competitive"]').parent().css("display", "inline");
				$('input[type="checkbox"][category="biccs-and-biccs-competitive-and-witt"]').parent().css("display", "inline");
				$('input[type="checkbox"][category="biccs"]:checked').trigger('click');
				$('input[type="checkbox"][category="witt"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-and-witt"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs"]').parent().css("display", "none");
				$('input[type="checkbox"][category="witt"]').parent().css("display", "none");
				$('input[type="checkbox"][category="biccs-and-witt"]').parent().css("display", "none");
			});
			$("#witt").click(function() {
				$('input[type="checkbox"][category="biccs"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-competitive"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-and-biccs-competitive"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs"]').parent().css("display", "none");
				$('input[type="checkbox"][category="biccs-competitive"]').parent().css("display", "none");
				$('input[type="checkbox"][category="biccs-and-biccs-competitive"]').parent().css("display", "none");

				$('input[type="checkbox"][category="biccs-and-witt"][default-visibility="visible"]:not(:checked)').trigger('click');
				$('input[type="checkbox"][category="biccs-competitive-and-witt"][default-visibility="visible"]:not(:checked)').trigger('click');
				$('input[type="checkbox"][category="biccs-and-biccs-competitive-and-witt"][default-visibility="visible"]:not(:checked)').trigger('click');
				$('input[type="checkbox"][category="witt"][default-visibility="visible"]:not(:checked)').trigger('click');

				$('input[type="checkbox"][category="biccs-and-witt"][default-visibility="invisible"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-competitive-and-witt"][default-visibility="invisible"]:checked').trigger('click');
				$('input[type="checkbox"][category="biccs-and-biccs-competitive-and-witt"][default-visibility="invisible"]:checked').trigger('click');
				$('input[type="checkbox"][category="witt"][default-visibility="invisible"]:checked').trigger('click');

				$('input[type="checkbox"][category="biccs-and-witt"]').parent().css("display", "inline");
				$('input[type="checkbox"][category="biccs-competitive-and-witt"]').parent().css("display", "inline");
				$('input[type="checkbox"][category="biccs-and-biccs-competitive-and-witt"]').parent().css("display", "inline");
				$('input[type="checkbox"][category="witt"]').parent().css("display", "inline");
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
					this.collapseLegend = false,
					this.allowExport = true,
					this.mapOptions = {
						center: new L.LatLng(49.164511, -122.863108),
						zoom: 10,
						crs: L.CRS.EPSG3857,
						zoomControl: false,
						minZoom: 10,
						//maxZoom: 15
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
									category: 'biccs-and-biccs-competitive-and-witt',
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'WITT_UrbanCentres' + datasetSuffix,
									label: 'Urban Centres',
									category: 'biccs-and-biccs-competitive-and-witt',
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
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
									category: 'biccs-and-witt',
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'WITT_FTDA' + datasetSuffix,
									label: 'Frequent Transit Development Areas',
									category: 'biccs-and-witt',
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
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
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'WITT_Top20BusBuffer400m' + datasetSuffix,
									label: '400m Walking Buffer (Top 20th Percentile Bus Stop)',
									category: 'witt',
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
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
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'WITT_BLineBuffer800m' + datasetSuffix,
									label: '800m Walking Buffer (Rapid Bus Stops & Skytrain Stations)',
									category: 'witt',
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
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
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_CycleZoneAnalysisMunicipal' + datasetSuffix,
									label: 'High Cycling Potential (Top 20% within Municipality)',
									category: 'biccs',
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
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
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_CycleZoneAnalysisRegional' + datasetSuffix,
									label: 'High Cycling Potential (Top 20% within Region)',
									category: 'biccs',
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer7: {
							name: "BICCS_MBN2050" + datasetSuffix,
							layerName: "BICCS_MBN2050" + datasetSuffix,
							displayName: "Major Bikeway Network",
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
									layers: 'BICCS_MBN2050' + datasetSuffix,
									label: 'Major Bikeway Network',
									category: 'biccs-and-biccs-competitive',
									defaultVisibility: 'visible',
									token: token,
									styles: 'MBN',
									format: 'image/png8',
									transparent: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_MBN2050' + datasetSuffix,
									label: 'Major Bikeway Network',
									category: 'biccs-and-biccs-competitive',
									defaultVisibility: 'visible',
									styles: 'MBN',
									token: token,
									format: 'image/png8',
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
								displayPopup: false
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'GM_MRN' + datasetSuffix,
									label: 'Major Road Network',
									category: 'biccs-and-biccs-competitive-and-witt',
									defaultVisibility: 'invisible',
									token: token,
									format: 'image/png8',
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
									category: 'biccs-and-biccs-competitive-and-witt',
									defaultVisibility: 'invisible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									srs: 'EPSG:4326'
								}
							}
						},
						layer8: {
							name: "BICCS_MBN2050Buffer1km" + datasetSuffix,
							layerName: "BICCS_MBN2050Buffer1km" + datasetSuffix,
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
									layers: 'BICCS_MBN2050Buffer1km' + datasetSuffix,
									label: 'Major Bikeway Network 1 km Buffer',
									category: 'biccs-and-biccs-competitive',
									defaultVisibility: 'invisible',
									token: token,
									styles: 'MBNBuffer',
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_MBN2050Buffer1km' + datasetSuffix,
									label: 'Major Bikeway Network 1 km Buffer',
									category: 'biccs-and-biccs-competitive',
									defaultVisibility: 'invisible',
									styles: 'MBNBuffer',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						/*layer9: {
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
									format: 'image/png8',
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
									format: 'image/png8',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},*/
						/*layer10: {
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
									format: 'image/png8',
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
									format: 'image/png8',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},*/
						/*layer11: {
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
									format: 'image/png8',
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
									format: 'image/png8',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},*/
						/*layer12: {
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
									format: 'image/png8',
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
									format: 'image/png8',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},*/
						/*layer13: {
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
									format: 'image/png8',
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
									format: 'image/png8',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},*/
						/*layer14: {
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
									format: 'image/png8',
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
									format: 'image/png8',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},*/
						/*layer15: {
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
									format: 'image/png8',
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
									format: 'image/png8',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},*/
						/*layer16: {
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
									format: 'image/png8',
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
									format: 'image/png8',
									transparent: 'true',
									tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},*/
						layer17: {
							name: 'CYCLE_BikeParkades' + datasetSuffix,
							layerName: 'CYCLE_BikeParkades' + datasetSuffix,
							displayName: "Bike Parkades",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: true,
								displayPopup: true
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'CYCLE_BikeParkades' + datasetSuffix,
									query_layers: 'CYCLE_BikeParkades' + datasetSuffix,
									label: 'Bike Parkade',
									category: 'biccs-and-biccs-competitive',
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'CYCLE_BikeParkades' + datasetSuffix,
									query_layers: 'CYCLE_BikeParkades' + datasetSuffix,
									label: 'Bike Parkade',
									category: 'biccs-and-biccs-competitive',
									defaultVisibility: 'visible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						/*layer18: {
							name: "WHSE_ADMIN_BOUNDARIES.CLAB_INDIAN_RESERVES",
							layerName: "WHSE_ADMIN_BOUNDARIES.CLAB_INDIAN_RESERVES",
							displayName: "First Nations Reserves",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: "https://openmaps.gov.bc.ca/geo/pub/WHSE_ADMIN_BOUNDARIES.CLAB_INDIAN_RESERVES/ows/",
							options: {
								visible: true,
								displayPopup: true,
								type: "external/wms"
							},
							editWmsLayer: {
								url: "https://openmaps.gov.bc.ca/geo/pub/WHSE_ADMIN_BOUNDARIES.CLAB_INDIAN_RESERVES/wms/?",
								options: {
									layers: "WHSE_ADMIN_BOUNDARIES.CLAB_INDIAN_RESERVES",
									label: "First Nations Reserves",
									token: "",
									category: "biccs-and-biccs-competitive-and-witt",
									defaultVisibility: 'visible',
									format: "image/png8",
									zIndex: 2,
									styles: "375",
									transparent: "true",
									externalPopup: false,
									externalPopupDiv: "#popupDiv",
								},
							},
							wmsLayer: {
								url: this.baseAPIURL + "/wms/?",
								options: {
									layers: "WHSE_ADMIN_BOUNDARIES.CLAB_INDIAN_RESERVES",
									label: "First Nations Reserves",
									query_layers: "WHSE_ADMIN_BOUNDARIES.CLAB_INDIAN_RESERVES",
									token: "",
									category: "biccs-and-biccs-competitive-and-witt",
									defaultVisibility: 'visible',
									format: "image/png8",
									zIndex: 2,
									opacity: 0,
									transparent: "true",
									externalPopup: false,
									externalPopupDiv: "#popupDiv",
								},
							},
						},*/
						layer19: {
							name: "BICCS_Equity2022" + datasetSuffix,
							layerName: "BICCS_Equity2022" + datasetSuffix,
							displayName: "Social Equity",
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
									layers: 'BICCS_Equity2022' + datasetSuffix,
									label: 'Cycling Equity Score',
									category: 'biccs-competitive',
									defaultVisibility: 'invisible',
									styles: 'BICCS_EquityScore2022',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_Equity2022' + datasetSuffix,
									label: 'Cycling Demand Score',
									category: 'biccs-competitive',
									defaultVisibility: 'invisible',
									token: token,
									styles: 'BICCS_EquityScore2022',
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							}

						},
						layer20: {
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
									category: 'biccs-competitive',
									defaultVisibility: 'invisible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'BICCS_DemandEquity' + datasetSuffix,
									label: 'Latent Demand',
									category: 'biccs-competitive',
									defaultVisibility: 'invisible',
									styles: 'BICCS_DemandScore',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							}
						},
						layer21: {
							name: "CYCLE_StateOfCycling" + datasetSuffix,
							layerName: "CYCLE_StateOfCycling" + datasetSuffix,
							displayName: "State Of Cycling in Metro Vancouver",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							styles: 'CyclingComfort',
							options: {
								visible: true,
								displayPopup: false
							},
							wmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'CYCLE_StateOfCycling' + datasetSuffix,
									label: 'State Of Cycling',
									category: 'biccs-and-biccs-competitive',
									defaultVisibility: 'invisible',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'CYCLE_StateOfCycling' + datasetSuffix,
									label: 'State Of Cycling',
									category: 'biccs-and-biccs-competitive',
									defaultVisibility: 'invisible',
									styles: 'CyclingComfort',
									token: token,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							}
						},
						layer22: {
							name: "MFP_Projects_Point_public" + datasetSuffix,
							layerName: "MFP_Projects_Point_public" + datasetSuffix,
							displayName: "Municipal Funding Projects (Spot Improvements)",
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
									layers: 'MFP_Projects_Point_public_view' + datasetSuffix,
									label: 'Municipal Funding Projects (Spot Improvements)',
									category: 'biccs-and-biccs-competitive-and-witt',
									defaultVisibility: 'invisible',
									query_layers: "MFP_Projects_Point_public_view",
									token: token,
									feature_count: 10,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'MFP_Projects_Point_public' + datasetSuffix,
									label: 'Municipal Funding Projects (Spot Improvements)',
									category: 'biccs-and-biccs-competitive-and-witt',
									defaultVisibility: 'invisible',
									styles: 'MFP_Projects_Point_public',
									token: token,
									feature_count: 10,
									format: 'image/png8',
									transparent: 'true',
									//tiled: 'true',
									srs: 'EPSG:4326'
								}
							}
						},
						layer23: {
							name: "MFP_Projects_Line_public" + datasetSuffix,
							layerName: "MFP_Projects_Line_public" + datasetSuffix,
							displayName: "Municipal Funding Projects (Route Improvements)",
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
									layers: 'MFP_Projects_Line_public_view' + datasetSuffix,
									label: 'Municipal Funding Projects (Route Improvements)',
									query_layers: "MFP_Projects_Line_public_view",
									category: 'biccs-and-biccs-competitive-and-witt',
									defaultVisibility: 'invisible',
									//token: token,
									feature_count: 10,
									format: 'image/png8',
									zIndex: 2,
									opacity: 0,
									transparent: 'true',
									//tiled: 'true',
									//srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'MFP_Projects_Line_public' + datasetSuffix,
									label: 'Municipal Funding Projects (Route Improvements)',
									category: 'biccs-and-biccs-competitive-and-witt',
									defaultVisibility: 'invisible',
									styles: 'MFP_Projects_Line_public',
									// token: token,
									feature_count: 10,
									format: 'image/png8',
									zIndex: 2,
									transparent: 'true',
									//tiled: 'true',
									//srs: 'EPSG:4326'
								}
							}
						},
						layer24: {
							name: "WITT_SidewalkInventory" + datasetSuffix,
							layerName: "WITT_SidewalkInventory" + datasetSuffix,
							displayName: "Sidewalk Inventory",
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
									layers: 'WITT_SidewalkInventory' + datasetSuffix,
									label: 'Sidewalk Inventory',
									query_layers: "WITT_SidewalkInventory",
									category: 'witt',
									defaultVisibility: 'invisible',
									//token: token,
									feature_count: 10,
									format: 'image/png8',
									zIndex: 2,
									opacity: 0,
									transparent: 'true',
									//tiled: 'true',
									//srs: 'EPSG:4326'
								}
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: 'WITT_SidewalkInventory' + datasetSuffix,
									label: 'Sidewalk Inventory',
									category: 'witt',
									defaultVisibility: 'invisible',
									styles: 'SidewalkCount',
									// token: token,
									feature_count: 10,
									format: 'image/png8',
									zIndex: 2,
									transparent: 'true',
									//tiled: 'true',
									//srs: 'EPSG:4326'
								}
							}
						},
						layer25: {
							name: "MFP_FNReservesTreatyLands",
							layerName: "MFP_FNReservesTreatyLands",
							displayName: "First Nations Reserves and Tsawwassen Treaty Lands",
							token: token,
							//baseURL : baseURL,
							baseAPIURL: baseAPIURL,
							options: {
								visible: true,
								displayPopup: false,
							},
							editWmsLayer: {
								url: this.baseAPIURL + '/wms/?',
								options: {
									layers: "MFP_FNReservesTreatyLands",
									label: "First Nations Treaty Lands",
									format: "image/png8",
									category: "biccs-and-biccs-competitive-and-witt",
									defaultVisibility: 'visible',
									zIndex: 2,
									styles: "BeigePolygon",
									transparent: "true",
									externalPopup: false,
									externalPopupDiv: "#popupDiv",
								},
							},
							wmsLayer: {
								url: this.baseAPIURL + "/wms/?",
								options: {
									layers: "MFP_FNReservesTreatyLands",
									label: "First Nations Treaty Lands",
									query_layers: "MFP_FNReservesTreatyLands",
									format: "image/png8",
									category: "biccs-and-biccs-competitive-and-witt",
									defaultVisibility: 'visible',
									zIndex: 2,
									opacity: 0,
									transparent: "true",
									externalPopup: false,
									externalPopupDiv: "#popupDiv",
								},
							},
						},
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
							displayName: "First Nations Reserves and Tsawwassen Treaty Lands",
							layerGroupOption: "single",
							wfstLayers: ["MFP_FNReservesTreatyLands"],
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
							"displayName": "Major Bikeway Network 1 km Buffer",
							"wfstLayers": ["BICCS_MBN2050Buffer1km" + datasetSuffix]
						},
						{
							"displayName": "Major Road Network" + datasetSuffix,
							"wfstLayers": ["GM_MRN" + datasetSuffix]
						},
						{
							"displayName": "Major Bikeway Network",
							"wfstLayers": ["BICCS_MBN2050" + datasetSuffix]
						},
						{
							"displayName": "Sidewalk Inventory",
							"wfstLayers": ['WITT_SidewalkInventory' + datasetSuffix]
						},
						{
							"displayName": "Status of Completion - Regional Cycling Network",
							"wfstLayers": ['CYCLE_StateOfCycling' + datasetSuffix]
						},
						{
							"displayName": "Social Equity",
							"wfstLayers": ['BICCS_Equity2022' + datasetSuffix]
						},
						{
							"displayName": "Latent Demand",
							"wfstLayers": ['BICCS_Demand' + datasetSuffix]
						},
						{
							"displayName": "Municipal Funding Projects (Route Improvements)",
							"wfstLayers": ['MFP_Projects_Line_public' + datasetSuffix]
						},
						{
							"displayName": "Municipal Funding Projects (Spot Improvements)",
							"wfstLayers": ['MFP_Projects_Point_public' + datasetSuffix]
						},
						/*{
							"displayName": "BCRTC_SkytrainLines" + datasetSuffix,
							"wfstLayers": ["BCRTC_SkytrainLines" + datasetSuffix]
						},

						{
							"displayName": "FTN" + datasetSuffix,
							"wfstLayers": ["WITT_FTN" + datasetSuffix]
						},*/
						/*
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
												},*/
						/*{
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
						},*/

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
				var mapDivId = "editMapDiv";
				document.addEventListener("legendLoaded", function() {
					$('#biccs').trigger('click');
				});
				var editMap = new EditMap(appToken, mapDivId, options);
			});
		});
	</script>
</body>

</html>