<?php
require 'header.php';
require_once '../support/environmentsettings.php';
require __DIR__ . '/buildNumber.php';
$datatoken = 'public';
?>
<style>
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

	.leaflet-control-layers {
		right: 1%;
		max-width: 40%;
		position: fixed !important;
		bottom: 10px;
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
		<h4 style="margin: 0px;">2026 BICCS & WITT Eligibility</h4>
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
		<label for="biccs">View BICCS Competitive and Rapid Implementation Competitive Eligibility Layers</label><br>
		<input type="radio" id="witt" name="mode" value="witt">
		<label for="witt">View WITT Allocated and Competitive Eligibility Layers</label><br>
	</div>
	<div id="controlContainer">
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
			var appToken = new AppToken();
			var mapName = 'biccswitteligibility';
			appToken.check().then(msg => {
				var token = appToken.token;
			}).catch(msg => {
				var token = appToken.token;
			}).finally(msg => {
				var permissionsUrl = baseAPIURL + '/permissions/?mode=app';
				getAppPermissions(permissionsUrl, token, mapName).then((response) => {
					if (response) {
						var optionsURL = baseAPIURL + '/mapoptions/?mapName=' + mapName;
						getOptions(optionsURL, token).then(data => {
							var editMapOptions = eval(data);
							$("#subTitleContainer h4").html(editMapOptions.title);
							document.addEventListener("legendLoaded", function() {
								$('#biccs').trigger('click');
							});
							var editMap = new EditMap(appToken, "editMapDiv", editMapOptions);
						});
					} else {
						<?php
						if (isset($_SESSION['status'])) {
							if ($_SESSION['status'] == "loggedin") {
								echo 'window.location.replace("index.php");';
							} else {
								$redirectLink = ltrim($_SERVER['REQUEST_URI'], "/");
								$_SESSION['redirectLink'] = $redirectLink;
								echo 'window.location.replace("signin.php");';
							}
						} else {
							$redirectLink = ltrim($_SERVER['REQUEST_URI'], "/");
							$_SESSION['redirectLink'] = $redirectLink;
							echo 'window.location.replace("signin.php");';
						}
						?>

					}
				});
			});

			function getAppPermissions(url, token, mapName) {
				var postData = {
					"token": token
				};
				var postDataString = JSON.stringify(postData);
				return new Promise((resolve, reject) => {
					$.ajax({
						type: "POST",
						url: url,
						data: postDataString,
						//dataType: "json",
						Accept: "text/html",
						success: function(data) {
							if (data.read.includes(mapName)) {
								resolve(true);
							} else {
								resolve(false);
							}
						}
					});
				});
			}

			function getOptions(url, token) {
				var postData = {
					"token": token
				};
				var postDataString = JSON.stringify(postData);
				return new Promise((resolve, reject) => {
					$.ajax({
						type: "POST",
						url: url,
						data: postDataString,
						//dataType: "json",
						Accept: "text/html",
						success: function(data) {
							resolve(data);
						}
					});
				});
			}

		});
	</script>
</body>

</html>