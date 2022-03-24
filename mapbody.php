<style>
    #mapId {
        height: 100%;
        cursor: pointer;
        clear: both;
    }

    #options {
        height: 100%;

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
<?php require __DIR__ . '/buildNumber.php'; ?>

<head>
    <!--<script src="react.production.min.js" crossorigin></script>
    <script src="react-dom.production.min.js" crossorigin></script>-->
    <!--<script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>-->
    <link rel="stylesheet" href="leaflet/leaflet.css" />
    <script src="leaflet/leaflet.js"></script>
    <link rel="stylesheet" type="text/css" href="leaflet-geoman.css">
    <link rel="stylesheet" href="leaflet-measure/leaflet-measure.css">
    <!--<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>-->
    <script src="jquery/jquery-3.4.1.min.js"></script>
    <script src="L.TileLayer.BetterWMS.js?<?php echo $_ENV['buildNumber']; ?>"></script>
    <script src="leaflet-geoman.min.js"></script>
    <script src="leaflet-measure/leaflet-measure.js"></script>
    <!--<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>-->
    <script src="jquery/jquery-ui.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/6uc033l4qvieb8jy3pxaj190siqq3ag35nqxzv7no2nvlrbq/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <!--<script src="tinymce/tinymce.min.js" referrerpolicy="origin"></script>-->
    <!--<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>-->
    <script src="apexcharts/apexcharts.js"></script>
    <script src="AppToken.js?<?php echo $_ENV['buildNumber']; ?>"></script>
    <script src="editMap.js?<?php echo $_ENV['buildNumber']; ?>"></script>
    <script src="Wfst.js?<?php echo $_ENV['buildNumber']; ?>"></script>
    <!--<script src="modal.js"> -->
    </script>
</head>

<body>
    <div class="clearfloat">
    </div>
    <div id="subTitleContainer">
        <h4></h4>
    </div>

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
        <div id="imgModal" class="fadein"></div>
        <div id="exportModal" class="fadein"></div>
        <div id="chartModal" class="fadein"></div>
        <div class="toolbar fadein" id="editToolbar" style="display:none">
            <div id="editbuttoncontainer">
                <button type="button" id="startEditButton" class="btn btn-primary btn-block btn-large fadein" style="display:none">Start Edit Session</button>
            </div>
            <div id="test"></div>
            <div id="addbuttoncontainer"><button type="button" id="addButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Add Features</button><button type="button" id="cancelAddButton" style="display:none" class="btn btn-primary btn-block btn-large fadein">Cancel</button></div>
            <div id="editbuttoncontainer"><button type="button" id="editButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Edit Features</button><button type="button" id="addToFeatureButton" style="display:none" class="fadein btn btn-primary btn-block btn-large">Add to Feature</button><button type="button" id="cancelEditButton" style="display:none" class=" fadein btn btn-primary btn-block btn-large">Cancel</button></div>
            <div id="deletebuttoncontainer"><button type="button" id="deleteButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Delete Features</button><button type="button" id="cancelDeleteButton" style="display:none" class="fadein btn btn-primary btn-block btn-large">Cancel</button></div>
        </div>
        <div id="rightToolbar">
            <div id="exportbuttoncontainer"><button type="button" id="exportButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Export Features</button></div>
            <div id="chartbuttoncontainer"><button type="button" id="chartButton" class="fadein btn btn-primary btn-block btn-large" style="display:none">Charts</button></div>
        </div>
    </div>
    <div id="editMapDiv"></div>
    <div id="popupDiv"></div>
    <script>
        $(document).ready(function() {
            var appToken = new AppToken();
            var baseAPIURL = "<?php echo $baseAPIURL; ?>";
            var mapName = "<?php echo $mapName ?>";
            var token;
            appToken.check().then(msg => {
                    token = appToken.token;
                })
                .catch((msg) => {
                    token = appToken.token;
                })
                .finally((msg) => {
                    var permissionsUrl = baseAPIURL + '/permissions/?mode=app';
                    getAppPermissions(permissionsUrl, token, mapName).then((response) => {
                        if (response) {
                            var optionsURL = baseAPIURL + '/mapoptions/?mapName=' + mapName;
                            getOptions(optionsURL, token).then(data => {
                                var editMapOptions = eval(data);
                                $("#subTitleContainer h4").html(editMapOptions.title);
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
    </script>
</body>

</html>