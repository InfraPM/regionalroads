<!DOCTYPE HTML>
<html>
<header>
    <link rel="stylesheet" type="text/css" href="main.css">
</header>
<?php
require_once '../support/environmentsettings.php';
require __DIR__ . '/buildNumber.php';
$datatoken = 'public';
?>
<style>
    #editMapDiv {
        height: 100% !important;
        width: 100% !important;
        cursor: pointer;
        clear: both;
    }

    body {
        margin: 0 !important;
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
    <div class="toolbar" id="basemapSelector" style="display:none">
        <form id="basemapSelectorForm" action="nada">
            <h4>Basemaps</h4>
            <input type="radio" id="basemap-imagery" name="basemap-selector" value="basemap-imagery" checked><label for="basemap-imagery">Imagery</label><br>
            <input type="radio" id="basemap-map" name="basemap-selector" value="basemap-map"><label for="basemap-map">Map</label><br>

        </form>
    </div>
    <div id="editMapDiv"></div>

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
                            name: "MFP_Projects_Point_public" + datasetSuffix,
                            layerName: "MFP_Projects_Point_public" + datasetSuffix,
                            displayName: "Municipal Funding Projects (Spot Improvements)",
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
                                    layers: 'MFP_Projects_Point_public_view' + datasetSuffix,
                                    label: 'Municipal Funding Projects (Spot Improvements)',
                                    category: 'both',
                                    token: token,
                                    feature_count: 10,
                                    format: 'image/png',
                                    transparent: 'true',
                                    tiled: 'true',
                                    srs: 'EPSG:4326'
                                }
                            },
                            editWmsLayer: {
                                url: this.baseAPIURL + '/wms/?',
                                options: {
                                    layers: 'MFP_Projects_Point_public' + datasetSuffix,
                                    label: 'Municipal Funding Projects (Spot Improvements)',
                                    category: 'both',
                                    styles: 'MFP_Projects_Point_public',
                                    token: token,
                                    feature_count: 10,
                                    format: 'image/png',
                                    transparent: 'true',
                                    tiled: 'true',
                                    srs: 'EPSG:4326'
                                }
                            }
                        },
                        layer2: {
                            name: "MFP_Projects_Line_public" + datasetSuffix,
                            layerName: "MFP_Projects_Line_public" + datasetSuffix,
                            displayName: "Municipal Funding Projects (Route Improvements)",
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
                                    layers: 'MFP_Projects_Line_public_view' + datasetSuffix,
                                    label: 'Municipal Funding Projects (Route Improvements)',
                                    category: 'both',
                                    token: token,
                                    feature_count: 10,
                                    format: 'image/png',
                                    transparent: 'true',
                                    tiled: 'true',
                                    srs: 'EPSG:4326'
                                }
                            },
                            editWmsLayer: {
                                url: this.baseAPIURL + '/wms/?',
                                options: {
                                    layers: 'MFP_Projects_Line_public' + datasetSuffix,
                                    label: 'Municipal Funding Projects (Route Improvements)',
                                    category: 'both',
                                    styles: 'MFP_Projects_Line_public',
                                    token: token,
                                    feature_count: 10,
                                    format: 'image/png',
                                    transparent: 'true',
                                    tiled: 'true',
                                    srs: 'EPSG:4326'
                                }
                            }
                        },
                        layer3: {
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
                                    token: token,
                                    feature_count: 10,
                                    format: 'image/png',
                                    transparent: 'true',
                                    tiled: 'true',
                                    srs: 'EPSG:4326'
                                }
                            },
                            editWmsLayer: {
                                url: this.baseAPIURL + '/wms/?',
                                options: {
                                    layers: 'GM_MRN' + datasetSuffix,
                                    label: 'Major Road Network',
                                    token: token,
                                    feature_count: 10,
                                    styles: 'MRNBackground',
                                    format: 'image/png',
                                    transparent: 'true',
                                    tiled: 'true',
                                    srs: 'EPSG:4326'
                                }
                            }

                        },
                    },
                    this.featureGrouping = [{
                            "displayName": "MRN",
                            "wfstLayers": ['GM_MRN' + datasetSuffix]
                        }, {
                            "displayName": "Municipal Funding Projects (Route Improvements)",
                            "wfstLayers": ['MFP_Projects_Line_public' + datasetSuffix]
                        },
                        {
                            "displayName": "Municipal Funding Projects (Spot Improvements)",
                            "wfstLayers": ['MFP_Projects_Point_public' + datasetSuffix]
                        },

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