class EditMap {
  //Front-end editing environment for RegionalRoads.com
  //Enables simple, intuitive editing of geographic features
  constructor(appToken, divId, options) {
    this.appToken = appToken;
    this.appToken
      .check()
      .then((data) => {
        $("#" + divId).append('<div id="mapId"></div>');
        this.mapDivId = "mapId";
        this.options = options;
        this.editMode = options.editMode;
        this.chartList = options.charts;
        this.currentChart = {};
        this.currentApexChart;
        this.showLegend = options.showLegend;
        this.collapseLegend = options.collapseLegend;
        this.allowExport = options.allowExport;
        this.showCharts = options.showCharts;
        this.baseAPIURL = options.baseAPIURL;
        this.map = new L.Map(this.mapDivId, options.mapOptions);
        if (options.measureTool != undefined) {
          if (options.measureTool) {
            this.measureOptions = {
              position: "bottomleft",
              primaryLengthUnit: "meters",
              secondaryLengthUnit: "kilometers",
              primaryAreaUnit: "sqmeters",
              secondaryAreaUnit: "acres",
              activeColor: "#fca103",
              completedColor: "#fca103",
            };
            this.measureControl = L.control.measure(this.measureOptions);
            this.measureControl.addTo(this.map);
          }
        }
        this.wfstLayers = [];
        this.popupLayer;
        this.expectedPopups = 0;
        this.getDataPermissions().then((permissions) => {
          this.addWfstLayers(options.wfstLayers)
            .then((msg) => {
              var featureGrouping = this.buildFeatureGrouping(
                options.featureGrouping
              );
              this.setFeatureGrouping(featureGrouping);
              this.addToFeatureSession = false;
              this.editSession = false;
              this.basemaps; //array of leaflet Basemaps
              this.currentBaseMap;
              this.writing = false;
              this.lastKeyPressed;
              this.popupWfstLayers = [];
              this.popupPromiseArray = [];
              this.popupArray = [];
              this.popupIndex = 0;
              this.popupOpen = false;
              this.popup;
              this.autoZoom = false;
              this.map.on("popupopen", () => {
                this.popupOpen = true;
              });
              this.map.on("popupclose", () => {
                this.popupOpen = false;
                if (this.popupLayer != undefined) {
                  this.popupLayer.remove();
                }
                this.popupPromiseArray = [];
                this.popupWfstLayers = [];
                this.popupArray = [];
                this.popupIndex = 0;
              });
              this.map.on("baselayerchange", function (e) {
                this.currentBaseMap = e.layer;
              });
              this.map.on("moveend", () => {
                if (this.autoZoom) {
                  this.popupLayer.remove();
                  this.popupLayer.addTo(this.map);
                  this.popup.setLatLng(this.map.getCenter());
                  this.autoZoom = false;
                }
              });
              //dynamically add divs to controlContainer
              this.divList = [
                { property: "mapDiv", divId: this.mapDivId },
                { property: "editToolbar", divId: "editToolbar" },
                { property: "editModal", divId: "editModal" },
                { property: "commentModal", divId: "commentModal" },
                { property: "exportModal", divId: "exportModal" },
                { property: "imgModal", divId: "imgModal" },
                { property: "chartModal", divId: "chartModal" },
              ]; //divs required for functioning of EditMap
              this.buttonList = [
                { property: "addButton", divId: "addButton" },
                {
                  property: "addAttributesButton",
                  divId: "addAttributesButton",
                },
                { property: "cancelAddButton", divId: "cancelAddButton" },
                { property: "editButton", divId: "editButton" },
                { property: "deleteButton", divId: "deleteButton" },
                {
                  property: "editAttributesButton",
                  divId: "editAttributesButton",
                },
                {
                  property: "confirmEditLayerButton",
                  divId: "confirmEditLayerButton",
                },
                { property: "addToFeatureButton", divId: "addToFeatureButton" },
                { property: "cancelEditButton", divId: "cancelEditButton" },
                { property: "cancelDeleteButton", divId: "cancelDeleteButton" },
                {
                  property: "closeEditModalButton",
                  divId: "closeEditModalButton",
                },
                { property: "startEditButton", divId: "startEditButton" },
                {
                  property: "cancelEditLayerButton",
                  divId: "cancelEditLayerButton",
                },
                {
                  property: "commentReplyButton",
                  divClass: "commentReplyButton",
                },
                {
                  property: "commentDeleteButton",
                  divClass: "commentDeleteButton",
                },
                {
                  property: "commentEditButton",
                  divClass: "commentEditButton",
                },
                { property: "commentAddButton", divId: "commentAddButton" },
                {
                  property: "commentSubmitButton",
                  divId: "commentSubmitButton",
                },
                {
                  property: "closeCommentModalButton",
                  divId: "closeCommentModalButton",
                },
                { property: "nextPopupButton", divId: "nextPopupButton" },
                {
                  property: "previousPopupButton",
                  divId: "previousPopupButton",
                },
                {
                  property: "editFeaturePopupButton",
                  divId: "editFeaturePopupButton",
                },
                {
                  property: "zoomToActiveLayerButton",
                  divId: "zoomToActiveLayerButton",
                },
                {
                  property: "closeChartModalButton",
                  divId: "closeChartModalButton",
                },
                {
                  property: "closeImgModalButton",
                  divId: "closeImgModalButton",
                },
                {
                  property: "commentEditSubmitButton",
                  divId: "commentEditSubmitButton",
                },
                {
                  property: "commentDeleteSubmitButton",
                  divId: "commentDeleteSubmitButton",
                },
                {
                  property: "commentReplySubmitButton",
                  divId: "commentReplySubmitButton",
                },
                { property: "exportButton", divId: "exportButton" },
                { property: "chartButton", divId: "chartButton" },
                { property: "backToChartButton", divId: "backToChartButton" },
                {
                  property: "closeExportModalButton",
                  divId: "closeExportModalButton",
                },
                { property: "exportLinkButton", divClass: "exportLinkButton" },
                { property: "chartLinkButton", divClass: "chartLinkButton" },
              ]; //buttons required for functioning of EditMap
              this.populateDivs = function (divList) {
                var that = this;
                divList.forEach(function (i) {
                  that.setDiv(i["property"], i["divId"]);
                });
              };
              this.populateDivs(this.divList); //set up all divs in divList as EditMap properties
              this.populateButtons = function () {
                var that = this;
                this.buttonList.forEach(function (j) {
                  var button = j["property"];
                  if (j["divId"] != undefined) {
                    var buttonDivId = j["divId"];
                    var buttonDivName = "#" + button;
                    that.setDiv(button, buttonDivId);
                  } else if (j["divClass"] != undefined) {
                    var buttonDivId = j["divClass"];
                    var buttonDivName = "." + button;
                    that.setDiv(button, buttonDivId, "class");
                  }
                  var buttonClickName = button + "Click";
                  var parent = that[j["property"]].parentNode;
                  var curFunction = that[buttonClickName].bind(that);
                  $(document).on("click", buttonDivName, curFunction);
                  j["clickFunction"] = curFunction;
                });
              };
              this.populateButtons(); //set up all button in buttonList as EditMap properties
              if (options.editable) {
                this.editToolbar.show(); //show edit toolbar if map is editable
              }
              this.addFeatureSession = false; //not in add feature session to start
              var that = this;
              this.mapDiv.on("click", "a.imgpopup", function (event) {
                event.preventDefault();
                that.generateImageModal($(this).attr("href"));
                that.sizeImage($("#imgModal img"));
                let imgHeight = $("#imgModal img").height();
                let imgWidth = $("#imgModal img").width();
                that.sizeModal(that.imgModal, imgWidth, imgHeight);
                that.imgModal.show();
              });
              this.editFeatureSession = false; //not editing to start
              this.editLayer = L.featureGroup(); //empty Leaflet feature group
              this.armEditClick = false; //do not arm edit click to start
              this.armDeleteClick = false; //do not arm delete click to start
              //this.baseAPIURL = options.baseAPIURL;
              this.tinyMCEOptions = {
                selector: "textarea",
                forced_root_block: "",
                plugins: "link",
                toolbar:
                  "bold italic underline strikethrough | insertfile image media template link anchor codesample | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | numlist bullist | removeformat | pagebreak | charmap emoticons | ltr rtl",
                menubar: false,
                branding: false,
                statusbar: false,
                extended_valid_elements: "span",
                custom_elements: "span",
                urlconverter_callback: function (url, node, on_save, name) {
                  var convertedUrl = url.replaceAll("%20", " ");
                  return convertedUrl;
                },
                init_instance_callback: function (editor) {
                  //editor.on('Load', that.detectTagging());
                },
              };
              this.map.on("pm:create", this.pmCreate.bind(this));
              this.map.on("popupopen", this.tinyMceInit.bind(this)); //listen for getFeatureInfo event, then open popup //document.addEventListener('commentIframeOpen', this.detectTagging.bind(this)); //$(document).tooltip({
              this.getFeatureInfoListener = this.displayPopup.bind(this);
              document
                .getElementById(this.mapDivId)
                .addEventListener(
                  "getFeatureInfo",
                  this.getFeatureInfoListener
                );
              this.mapDiv.tooltip({
                track: true,
                position: {
                  my: "center bottom+50",
                },
              });
              var showEditControls = false;
              var that = this;
              this.featureGrouping.forEach(function (i) {
                if (i.geoJsonLayers == undefined) {
                  i.wfstLayers.forEach(function (j) {
                    if (that.layerEditable(j.name)) {
                      showEditControls = true;
                    }
                  });
                }
              });
              if (showEditControls) {
                this.startEditButton.show();
              }
              this.populateLayerControl();
              this.populateLegend();
              if (this.allowExport) {
                this.exportButton.show();
              } else {
                this.exportButton.hide();
              }
              if (this.showCharts) {
                this.chartButton.show();
              } else {
                this.chartButton.hide();
              }
            })
            .catch((msg) => {
              console.log("Error adding WFST Layers", msg);
            });
        });
      })
      .catch((msg) => {
        console.log("Error getting permissions");
      });
  }
  sizeModal(modal, maxWidth = 0, maxHeight = 0) {
    let docHeight = $(document).height();
    let docWidth = $(document).width();
    let curHeight = docHeight - 100;
    let curWidth = docWidth - 100;
    if (maxHeight == 0) {
      modal.css("max-height", curHeight);
    } else {
      modal.css("max-height", maxHeight);
    }
    if (maxWidth == 0) {
      modal.css("max-width", curWidth);
    } else {
      modal.css("max-width", maxWidth);
    }
    let curTop = (docHeight - modal.height()) / 2;
    let curLeft = (docWidth - modal.width()) / 2;
    modal.css("top", curTop);
    modal.css("left", curLeft);
  }
  sizeImage(image) {
    var maxWidth = $(window).width() - 100;
    var maxHeight = $(window).height() - 100;
    var ratio = 0;
    var width = image.width();
    var height = image.height();
    if (width > maxWidth) {
      ratio = maxWidth / width;
      image.css("width", maxWidth);
      image.css("height", height * ratio);
      height = height * ratio;
      width = width * ratio;
    }
    if (height > maxHeight) {
      ratio = maxHeight / height;
      image.css("height", maxHeight);
      image.css("width", width * ratio);
      width = width * ratio;
      height = height * ratio;
    }
  }
  tinyMceInit() {
    tinyMCE.remove("textarea");
    tinyMCE.init(this.tinyMCEOptions);
  }
  detectTagging() {
    var that = this;
    $(document.getElementById("commentEntry_ifr").contentWindow.document).keyup(
      function (e) {
        let curKey = e.originalEvent.key;
        console.log(e.originalEvent.target.innerText);
        if (that.writing == true) {
          scanTextForTag(e.originalEvent.target.innerText);
          if (e.originalEvent.target.innerText == "@alan") {
            console.log("REPLACE!");
            var editor = tinymce.get("commentEntry"); // use your own editor id here - equals the id of your textarea
            var content = editor.getContent();
            content = content.replace(
              "@alan",
              '<span data-userid="1" style="color:red;">@alan</span>'
            );
            editor.setContent(content);
          }
          that.lastKeyPressed = curKey;
        }
      }
    );
    function scanTextForTag(text) {
      for (let i = 0; i < text.length; i++) {
        if (i == 0) {
          if (text.charAt(i) == "@") {
            console.log("TAG DETECTED!");
          } else {
            console.log("NO TAG DETECTED!");
          }
        } else {
          if (text.charAt(i) == "@" && text.charAt(i - 1) == " ") {
            console.log("TAG DETECTED!");
          } else {
            console.log("NO TAG DETECTED!");
          }
        }
      }
    }
  }
  addWfstLayers(wfstLayers) {
    //make promise
    return new Promise((resolve, reject) => {
      var that = this;
      (async function addLayers() {
        for (let key in wfstLayers) {
          if (wfstLayers[key].options.type == undefined) {
            var curLayerType = "wfst";
          } else if (
            wfstLayers[key].options.type.toLowerCase() == "external/wms"
          ) {
            var curLayerType = "external/wms";
          } else if (
            wfstLayers[key].options.type.toLowerCase() == "external/geojson"
          ) {
            var curLayerType = "external/geojson";
          }
          if (curLayerType == "wfst") {
            if (that.dataPermissions.read.includes(wfstLayers[key].name)) {
              var wfstLayer = new WfstLayer(
                wfstLayers[key].name,
                that.appToken,
                wfstLayers[key].baseAPIURL
              );
              wfstLayer.zoomTo = wfstLayers[key].zoomTo;
              wfstLayer.layerName = wfstLayers[key].layerName;
              wfstLayer.displayName = wfstLayers[key].displayName;
              wfstLayer.options = wfstLayers[key].options;
              wfstLayers[key].wmsLayer.options["mapDivId"] = that.mapDivId;
              wfstLayer.bounds = wfstLayers[key].bounds;
              var wmsLayer = L.tileLayer.betterWms(
                wfstLayers[key].wmsLayer.url,
                wfstLayers[key].wmsLayer.options,
                that.appToken
              );
              wfstLayer.wmsLayer = wmsLayer;
              wfstLayers[key].editWmsLayer.options["mapDivId"] = that.mapDivId;
              var editWmsLayer = L.tileLayer.betterWms(
                wfstLayers[key].editWmsLayer.url,
                wfstLayers[key].editWmsLayer.options,
                that.appToken
              );
              wfstLayer.editWmsLayer = editWmsLayer;
              await wfstLayer.getBounds();
              if (wfstLayer.bounds != undefined) {
                that.map.fitBounds(wfstLayer.bounds);
                wfstLayer.editMode = "edit";
              } else {
                wfstLayer.editMode = "add";
              }
              if (wfstLayer.error != true) {
                that.wfstLayers.push(wfstLayer);
                if (wfstLayer.options.visible) {
                  wfstLayer.editWmsLayer.addTo(that.map);
                }
              }
            }
          } else if (curLayerType == "external/wms") {
            var wfstLayer = new WfstLayer(
              wfstLayers[key].name,
              that.appToken,
              wfstLayers[key].baseAPIURL
            );
            wfstLayer.zoomTo = wfstLayers[key].zoomTo;
            wfstLayer.layerName = wfstLayers[key].layerName;
            wfstLayer.displayName = wfstLayers[key].displayName;
            wfstLayer.options = wfstLayers[key].options;
            wfstLayer.type = "external/wms";

            wfstLayers[key].wmsLayer.options["mapDivId"] = that.mapDivId;
            wfstLayers[key].wmsLayer.options.type = "external/wms";
            wfstLayer.bounds = wfstLayers[key].bounds;
            var wmsLayer = L.tileLayer.betterWms(
              wfstLayers[key].wmsLayer.url,
              wfstLayers[key].wmsLayer.options,
              that.appToken
            );
            wmsLayer.options.type = "external/wms";
            wfstLayer.wmsLayer = wmsLayer;
            wfstLayers[key].editWmsLayer.options["mapDivId"] = that.mapDivId;
            wfstLayers[key].editWmsLayer.options.type = "external/wms";
            var editWmsLayer = L.tileLayer.betterWms(
              wfstLayers[key].editWmsLayer.url,
              wfstLayers[key].editWmsLayer.options,
              that.appToken
            );
            editWmsLayer.options.type = "external/wms";
            wfstLayer.editWmsLayer = editWmsLayer;
            await wfstLayer.getBounds();
            if (wfstLayer.bounds != undefined) {
              that.map.fitBounds(wfstLayer.bounds);
              wfstLayer.editMode = "edit";
            } else {
              wfstLayer.editMode = "add";
            }
            if (wfstLayer.error != true) {
              that.wfstLayers.push(wfstLayer);
              if (wfstLayer.options.visible) {
                wfstLayer.editWmsLayer.addTo(that.map);
              }
            }
          } else if (curLayerType == "external/geojson") {
            var geoJsonTitle = wfstLayers[key].displayName;
            var styleFunction = wfstLayers[key].style;
            var filterFunction = wfstLayers[key].filter;
            var geoJsonUrl = wfstLayers[key].url;
            var pointToLayerFunction = wfstLayers[key].pointToLayer;
            var geometryType;
            await $.ajax({
              type: "GET",
              url: geoJsonUrl,
              success: function (data) {
                geometryType = data.features[0].geometry.type;
                var popupTitle = "<h4>" + geoJsonTitle + "</h4>";
                var geoJsonLayer = L.geoJSON(data, {
                  style: styleFunction,
                  filter: filterFunction,
                  pointToLayer: pointToLayerFunction,
                  onEachFeature: function (feature, layer) {
                    layer.on("click", function (e) {
                      var popupHTML = "<table>";
                      var geometry = feature.geometry;
                      for (let i in feature.properties) {
                        popupHTML +=
                          "<tr><td>" +
                          i.replace("_", " ") +
                          "</td><td>" +
                          feature.properties[i] +
                          "</td></tr>";
                      }
                      popupHTML += "</table>";
                      var popupObj = {};
                      popupObj.geometry = geometry;
                      popupObj.geometryName = "GEOMETRY";
                      popupObj.popupContent = popupTitle + popupHTML;
                      popupObj.popupHtml = popupHTML;
                      that.popupArray.push(popupObj);
                      if (that.popupOpen == false) {
                        that.popup = L.popup({ maxWidth: 800 })
                          .setLatLng(e.latlng)
                          .setContent(
                            that.addPopupLinks(
                              that.popupArray[that.popupIndex].popupContent
                            )
                          )
                          .openOn(that.map);
                        var position = L.DomUtil.getPosition(
                          that.popup.getElement()
                        );
                        L.DomUtil.setPosition(
                          that.popup.getElement(),
                          position
                        );
                        var draggable = new L.Draggable(
                          that.popup.getElement()
                        );
                        draggable.enable();
                        that.addPopupLayer();
                      }
                    });
                  },
                });
                wfstLayers[key]["geoJsonLayer"] = geoJsonLayer;
                if (wfstLayers[key].options.visible) {
                  geoJsonLayer.addTo(that.map);
                }
                if (geometryType.toLowerCase().includes("point")) {
                  wfstLayers[key].svgLegend = that.generateSvgFromStyle(
                    pointToLayerFunction,
                    geometryType
                  );
                } else if (
                  geometryType.toLowerCase().includes("line") ||
                  geometryType.toLowerCase().includes("polygon")
                ) {
                  wfstLayers[key].svgLegend = that.generateSvgFromStyle(
                    styleFunction,
                    geometryType
                  );
                }
                that.wfstLayers.push(wfstLayers[key]);
              },
              error: function (data) {
                console.log("Error retrieving external geojson layer.");
              },
            }).catch((err) => {
              console.log(
                "Error adding external geojson layer(s), you may need to connect to VPN"
              );
            });
          }
        }
        resolve(true);
      })();
    });
  }
  generateSvgFromStyle(styleFunction, geometryType) {
    var svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("xmlns", "http://www.w3.org/2000/svg");
    svg.setAttribute("version", "1.1");
    if (geometryType.toLowerCase().includes("point")) {
      var svgGeom = "circle";
      geometryType = "point";
    } else if (geometryType.toLowerCase().includes("line")) {
      var svgGeom = "line";
      geometryType = "line";
    } else if (geometryType.toLowerCase().includes("polygon")) {
      var svgGeom = "polygon";
      geometryType = "polygon";
    }
    var svgElement = document.createElementNS(
      "http://www.w3.org/2000/svg",
      svgGeom
    );
    var styleResult = styleFunction();
    svg.setAttribute("height", "30");
    svg.setAttribute("width", "30");
    if (geometryType == "point") {
      svgElement.setAttribute("cx", "12");
      svgElement.setAttribute("cy", "12");
      svgElement.setAttribute("r", "10");
      for (let i in styleResult.options) {
        if (i == "fillColor") {
          svgElement.setAttribute("fill", styleResult.options[i]);
        } else if (i == "weight") {
          svgElement.setAttribute("stroke-width", styleResult.options[i]);
        } else if (i == "color") {
          svgElement.setAttribute("stroke", styleResult.options[i]);
        } else if (i == "opacity") {
          svgElement.setAttribute("stroke-opacity", styleResult.options[i]);
          svgElement.setAttribute("fill-opacity", styleResult.options[i]);
        } else if (i == "fillOpacity") {
          svgElement.setAttribute("fill-opacity", styleResult.options[i]);
        }
      }
    } else if (geometryType == "line") {
      svgElement.setAttribute("x1", "3");
      svgElement.setAttribute("y1", "27");
      svgElement.setAttribute("x2", "27");
      svgElement.setAttribute("y2", "3");
      for (let i in styleResult) {
        if (i == "color") {
          svgElement.setAttribute("stroke", styleResult[i]);
        } else if (i == "weight") {
          svgElement.setAttribute("stroke-width", styleResult[i]);
        } else if (i == "opacity") {
          svgElement.setAttribute("fill-opacity", styleResult[i]);
          svgElement.setAttribute("stroke-opacity", styleResult[i]);
        } else if (i == "fillOpacity") {
          svgElement.setAttribute("fill-opacity", styleResult[i]);
        }
      }
    } else if (geometryType == "polygon") {
      svgElement.setAttribute("points", "3,3 3,27 27,27 27,3");
      for (let i in styleResult) {
        if (i == "fillColor") {
          svgElement.setAttribute("fill", styleResult[i]);
        } else if (i == "weight") {
          svgElement.setAttribute("stroke-width", styleResult[i]);
        } else if (i == "color") {
          svgElement.setAttribute("stroke", styleResult[i]);
        } else if (i == "opacity") {
          svgElement.setAttribute("fill-opacity", styleResult[i]);
          svgElement.setAttribute("stroke-opacity", styleResult[i]);
        } else if (i == "fillOpacity") {
          svgElement.setAttribute("fill-opacity", styleResult[i]);
        }
      }
    }
    svg.appendChild(svgElement);
    return svg;
  }
  buildFeatureGrouping(featureGrouping) {
    var that = this;
    featureGrouping.forEach(function (i) {
      if (i.geoJsonLayers != undefined) {
        var index = "geoJsonLayers";
      } else {
        var index = "wfstLayers";
      }
      for (var j = 0; j < i[index].length; j++) {
        if (index == "geoJsonLayers") {
          var curWfstLayer = that.getWfstLayerFromName(
            i[index][j],
            "wfstLayerName"
          );
        } else {
          var curWfstLayer = that.getWfstLayerFromName(
            i[index][j],
            "wfstLayerName"
          );
        }
        if (curWfstLayer != undefined) {
          i[index][j] = curWfstLayer;
        }
      }
    });
    return featureGrouping;
  }
  getGeoJsonLayerFromName(name) {
    this.wfstLayers.forEach((i) => {
      if (i.name == name) {
        return i;
      }
    });
  }
  setDiv(property, divID, mode = "id") {
    //set divId as EditMap property
    var fullId;
    if (mode == "id") {
      fullId = "#" + divID;
    } else if (mode == "class") {
      fullId = "." + divID;
    }
    this[property] = $(fullId);
  }
  displayPopup(e) {
    var error = false;
    try {
      if (typeof e.content == "string") {
        var jsonContent = JSON.parse(e.content);
      } else {
        var jsonContent = e.content;
      }
      if (jsonContent["error"].length > 0) {
        error = true;
      }
    } catch (error) {
      error = true;
    }
    if (error) {
      //return;
    }
    //show popup
    if (e.err) {
      //return;
    } // do nothing if there's an error
    if (!jsonContent || jsonContent.features.length == 0) {
      //do not show blank popup
      return;
    } else if (
      e.content ==
      `<?xml version="1.0" encoding="UTF-8" standalone="no"?><!DOCTYPE ServiceExceptionReport SYSTEM "http://regionalroads.com:8080/geoserver/schemas/wms/1.1.1/WMS_exception_1_1_1.dtd"> <ServiceExceptionReport version="1.1.1" >   <ServiceException code="OperationNotSupported" locator="QUERY_LAYERS">
	Either no layer was queryable, or no layers were specified using QUERY_LAYERS
</ServiceException></ServiceExceptionReport>`
    ) {
      //do not show popup exception
      return;
    }
    e.this.externalPopup = false; //temporary until external popups are implemented
    if (e.this.wmsParams.type != "external/wms") {
      if (this.armEditClick == true || this.armDeleteClick == true) {
        if (e.this != this.editableWfstLayer().editWmsLayer) {
          //while edit click is armed do not show irrelevant popups
          return;
        } else {
          //while edit click is armed the activeWfstLayer is the editableWfstLayer
          this.activeWfstLayer = this.editableWfstLayer();
          this.activeWfstLayer.setFidField();
          this.activeWfstLayer.curId = jsonContent.features[0].id.split(".")[1];
          if (this.editMode == "integrated") {
            var editEvt = new Event("gotFeatureInfo");
            document.getElementById(this.mapDivId).dispatchEvent(editEvt);
          }
        }
      } else {
        //while edit click is not armed the activeWfstLayer is whichever one was clicked
        this.activeWfstLayer = this.getWfstLayerFromWmsLayer(e.this);
      }
      if (this.editFeatureSession == true) {
        //no getFeatureInfo popups while editing
        return;
      }
      if (
        this.popupWfstLayers.includes(this.activeWfstLayer) == false &&
        this.activeWfstLayer.options.displayPopup != false
      ) {
        this.popupWfstLayers.push(this.activeWfstLayer);
        this.popupPromiseArray.push(
          this.getPopup(this.activeWfstLayer, e.latlng, jsonContent)
        );
      }
    } else {
      if (this.editFeatureSession == true) {
        //no getFeatureInfo popups while editing
        return;
      }
      if (this.armEditClick != true && this.armDeleteClick != true) {
        var curContent = this.formatSimplePopup(jsonContent, e.this.wmsParams);
        var promise = new Promise((resolve, reject) => {
          resolve(curContent);
        });
        this.popupPromiseArray.push(promise);
      }
    }
    Promise.all(this.popupPromiseArray)
      .then((msgArray) => {
        if (this.popupOpen == false) {
          this.popupPromiseArray = [];
          this.popupWfstLayers = [];
          this.popupArray = [];
          this.popupIndex = 0;
        }
        msgArray.forEach((msgObject) => {
          msgObject.forEach((msg) => {
            if (this.popupArray.includes(msg) == false) {
              if (msg.popupContent.length > 0) {
                this.popupArray.push(msg);
              }
            }
          });
        });
        if (this.popupArray.length == 0) {
          return;
        }
        this.activeWfstLayer = this.popupArray[this.popupIndex].activeWfstLayer;
        try {
          this.activeWfstLayer.setFidField();
          this.activeWfstLayer.curId = this.activeWfstLayer.getIDFromPopup(
            this.popupArray[this.popupIndex].popupContent
          );
        } catch (error) {
          //keep going
        }
        //remove tooltip
        this.mapDiv.attr("title", "");
        this.mapDiv.tooltip("disable");
        if (this.popupOpen) {
          this.popup.setContent(
            this.addPopupLinks(this.popupArray[this.popupIndex].popupContent)
          );
          var position = L.DomUtil.getPosition(this.popup.getElement());
          L.DomUtil.setPosition(this.popup.getElement(), position);
          var draggable = new L.Draggable(this.popup.getElement());
          draggable.enable();
        } else {
          this.popup = L.popup({ maxWidth: 800 })
            .setLatLng(e.latlng)
            .setContent(
              this.addPopupLinks(this.popupArray[this.popupIndex].popupContent)
            )
            .openOn(e.this._map);
          var position = L.DomUtil.getPosition(this.popup.getElement());
          L.DomUtil.setPosition(this.popup.getElement(), position);
          var draggable = new L.Draggable(this.popup.getElement());
          draggable.enable();
          this.addPopupLayer();
        }
      })
      .catch((msg) => {
        this.popupWfstLayers = [];
        this.popupPromiseArray = [];
        console.log("Error opening popups", msg);
      });
  }
  addPopupLinks(msg) {
    var popupLinkContainerDisplay,
      previousPopupButtonDisabled,
      nextPopupButtonDisabled;
    if (this.popupIndex == 0 && this.popupArray.length == 1) {
      //do not display popup navigation links
      popupLinkContainerDisplay = "display:none";
    } else if (this.popupIndex == 0 && this.popupArray.length > 1) {
      //display only next popup link
      popupLinkContainerDisplay = "";
      previousPopupButtonDisabled = "disabled";
      nextPopupButtonDisabled = "";
    } else if (
      this.popupIndex > 0 &&
      this.popupArray.length > 1 &&
      this.popupIndex < this.popupArray.length - 1
    ) {
      //display both popup links
      popupLinkContainerDisplay = "";
      previousPopupButtonDisabled = "";
      nextPopupButtonDisabled = "";
    } else {
      //display only previous popup link
      popupLinkContainerDisplay = "";
      previousPopupButtonDisabled = "";
      nextPopupButtonDisabled = "disabled";
    }
    var popupLinks = `<div id="popupLinkContainer" style="float:right"><div id="popupButtonsContainer" style="float:right; ${popupLinkContainerDisplay}">
    ${this.popupIndex + 1} of ${this.popupArray.length}
    <button id="previousPopupButton" ${previousPopupButtonDisabled}><</button><button id="nextPopupButton" ${nextPopupButtonDisabled}>></button>
    </div>
    <div>
    <button id="zoomToActiveLayerButton" class="buttonToLink">Zoom to Selected</button>
    </div>
    </div>`;
    return popupLinks + msg;
  }
  getWfstLayerFromWmsLayer(wmsLayer) {
    //now includes editWmsLayer AND wmsLayer ()
    var returnWfstLayer;
    this.wfstLayers.forEach(function (j) {
      if (j.geoJsonLayer == undefined) {
        let wmsLayerMatchCount = 0;
        let editWmsLayerMatchCount = 0;
        let wmsLayerTotalCount = 0;
        let editWmsLayerTotalCount = 0;
        for (const i in wmsLayer.wmsParams) {
          if (j.editWmsLayer.wmsParams[i] == wmsLayer.wmsParams[i]) {
            editWmsLayerMatchCount += 1;
          }
          if (j.wmsLayer.wmsParams[i] == wmsLayer.wmsParams[i]) {
            wmsLayerMatchCount += 1;
          }
          wmsLayerTotalCount += 1;
          editWmsLayerTotalCount += 1;
        }
        if (
          wmsLayerMatchCount == wmsLayerTotalCount ||
          editWmsLayerMatchCount == editWmsLayerTotalCount
        ) {
          returnWfstLayer = j;
        }
      }
    });

    return returnWfstLayer;
  }
  setFeatureGrouping(featureGrouping) {
    //editMap.featureGrouping setter
    this.featureGrouping = [];
    var index;
    featureGrouping.forEach((feature) => {
      var add = false;
      if (feature.geoJsonLayers != undefined) {
        index = "geoJsonLayers";
      } else {
        index = "wfstLayers";
      }
      feature[index].forEach((wfstLayer) => {
        try {
          if (this.dataPermissions.read.includes(wfstLayer.name)) {
            add = true;
          } else if (wfstLayer.options.type == "external/wms") {
            add = true;
          } else if (wfstLayer.options.type == "external/geojson") {
            add = true;
          }
        } catch (error) {
          add = false;
        }
      });
      if (add) {
        this.featureGrouping.push(feature);
      }
    });
  }
  generateChartModal() {
    var htmlString =
      '<button type="button" id="closeChartModalButton" class="btn-modal"><svg width="24" height="24"><path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path></svg></button>';
    htmlString += "<h4>Choose a chart to display</h4>";
    htmlString += '<div id="chartButtonContainer">';
    htmlString += "<ul>";
    for (let key in this.chartList) {
      //this.currentChart = this.chartList[key];
      var chartName = this.chartList[key].displayName;
      var viewName = this.chartList[key].viewName;
      if (this.layerReadable(viewName)) {
        htmlString +=
          '<li><button type="button" class="chartLinkButton btn-modal" data-chartName="' +
          chartName +
          '" data-viewName="' +
          viewName +
          '">' +
          chartName +
          "</button></li>";
      }
    }
    htmlString += "</ul>";
    htmlString += "</div>";
    this.chartModal.html(htmlString);
    this.chartModal.css("height", "");
    this.chartModal.css("width", "");
    this.sizeModal(this.chartModal);
    this.chartModal.show();
  }
  getCurrentChart(chartName) {
    for (let key in this.chartList) {
      if (this.chartList[key].displayName == chartName) {
        this.currentChart = this.chartList[key];
      }
    }
  }
  chartLinkButtonClick() {
    var curElement = this.exportLinkButton.prevObject[0].activeElement;
    var viewName = curElement.getAttribute("data-viewName");
    var chartName = curElement.getAttribute("data-chartName");
    var options = this.getCurrentChart(chartName);
    var maxHeight = $(document).height();
    var maxWidth = $(document).width();
    this.sizeModal(this.chartModal, maxWidth, maxHeight);
    this.generateChart();
  }
  backToChartButtonClick() {
    this.currentApexChart.destroy();
    this.generateChartModal();
  }
  generateChart() {
    var htmlString =
      '<button type="button" id="backToChartButton" class="btn-modal">< Back to Charts</button>';
    htmlString +=
      '<button type="button" id="closeChartModalButton" class="btn-modal"><svg width="24" height="24"><path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path></svg></button>';
    htmlString += `<h4>${this.currentChart.displayName}</h4>`;
    htmlString += '<div id="chartContainer">';
    htmlString += "</div>";
    this.chartModal.html(htmlString);
    this.currentChart.options.xaxis = {};
    this.getChart(this.currentChart.viewName, this.currentChart.options)
      .then((msg) => {
        for (let i = 0; i < msg.series.length; i++) {
          for (let key in msg.series[i]) {
            this.currentChart.options.series[i][key] = msg.series[i][key];
          }
        }
        for (let key in msg.xaxis) {
          this.currentChart.options.xaxis[key] = msg.xaxis[key];
        }
        for (let key in msg.chart) {
          this.currentChart.options.chart[key] = msg.chart[key];
        }
        let docHeight = $(document).height();
        let docWidth = $(document).width();
        this.currentChart.options.chart.height = $(document).height() - 500;
        this.currentChart.options.chart.width = $(document).width() - 500;
        this.currentApexChart = new ApexCharts(
          document.querySelector("#chartContainer"),
          this.currentChart.options
        );
        this.currentApexChart.render();
        this.sizeImage($("#chartContainer .apexcharts-canvas"));
        let imgHeight = $("#chartContainer .apexcharts-canvas").height();
        let imgWidth = $("#chartContainer .apexcharts-canvas").width();
        var maxHeight = $(document).height();
        var maxWidth = $(document).width();
        this.chartModal.css("width", $(document).width());
        this.sizeModal(this.chartModal, imgWidth + 100, imgHeight + 300);
      })
      .catch((msg) => {
        console.log("Error retrieving chart.");
        this.closeChartModalButtonClick();
      });
  }
  getChart(viewName, options) {
    //ajax request for chart options
    return new Promise((resolve, reject) => {
      this.appToken.check().then((msg) => {
        var xmlString =
          '<?xml version="1.0" encoding="UTF-8"?><token>' +
          this.appToken.token +
          "</token>";
        var self = this;
        var ajaxUrl =
          this.baseAPIURL +
          "/chart/?viewName=" +
          viewName +
          "&dataFormat=" +
          options.dataFormat +
          "&chartType=" +
          options.chart.type +
          "&token=" +
          self.appToken.token;
        if (options.nullCategory != undefined) {
          ajaxUrl += "&nullCategory=" + options.nullCategory;
        }
        $.ajax({
          type: "POST",
          url: ajaxUrl,
          beforeSend: function () {
            self.appToken.check().then((data) => {
              //refresh token if needed
            });
          },
          success: function (msg) {
            resolve(msg);
          },
          error: function (msg) {
            reject(false);
          },
        });
      });
    });
  }
  generateImageModal(href) {
    var htmlString =
      '<button type="button" id="closeImgModalButton"><svg width="24" height="24"><path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path></svg></button>';
    htmlString += '<div id="imgContainer">';
    htmlString += '<img src="' + href + '" height=600px width=1344px>';
    htmlString += "</div>";
    this.imgModal.html(htmlString);
  }
  generateExportModal() {
    var htmlString =
      '<button type="button" id="closeExportModalButton" class="btn-modal"><svg width="24" height="24"><path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path></svg></button>';
    htmlString += "<h4>Export Layers</h4>";
    htmlString += '<div id="layerListContainer">';
    htmlString += '<div id="layerList">';
    var that = this;
    var layerCount = 0;
    var links = [];
    function SortArray(x, y) {
      if (x.displayName < y.displayName) {
        return -1;
      }
      if (x.displayName > y.displayName) {
        return 1;
      }
      return 0;
    }
    var sortedFeatureGrouping = this.featureGrouping.sort(SortArray);
    sortedFeatureGrouping.forEach(function (i) {
      if (i.geoJsonLayer == undefined) {
        var subLayerCount = 0;
        var addString = "<ul>";
        var fileName = i.displayName.replace(/[^A-Z0-9]/gi, "");
        var csvFileName = fileName + ".csv";
        addString += "<li>";
        addString += `<b>${i.displayName}</b>`;
        var csvId = `csvLink${fileName}`;
        addString += `<br><button id = "${csvId}" type="button" class="exportLinkButton btn-modal" data-filename="${csvFileName}" data-type="csv">Download CSV</button>`;
        var typeNames = "";
        var layerCount = 0;
        var masterLinksAdded = false;
        var curWfstLayer;
        i.wfstLayers.forEach(function (j) {
          curWfstLayer = j;
          if (j.options.type != "external/geojson") {
            if (that.layerReadable(j.wmsLayer.options.layers)) {
              var addString2 = "";
              if (layerCount > 0) {
                typeNames += ",";
              }
              if (
                i.layerGroupOption == "multiple" ||
                i.layerGroupOption == "filtered"
              ) {
                addString2 += "<ul>";
                if (j.displayName != undefined) {
                  addString2 += "<h4>" + j.displayName + "</h4>";
                } else {
                  addString2 += `<h4>${j.name}</h4>`;
                }
              }
              var zipFileName = `${fileName}.zip`;
              var kmlFileName = `${fileName}.kml`;
              var jsonFileName = `${fileName}.json`;
              var cqlFilter = j.editWmsLayer.wmsParams.cql_filter;
              var masterLinksString = `<div class="masterLinks">
		  <button id="${j.name}Shapefile"class="exportLinkButton btn-modal" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.wmsLayer.options.layers}&outputFormat=shape-zip" data-cqlfilter="1=1" data-filename="${zipFileName}" data-type="zip">Shapefile</button>
					  <button id="${j.name}Kml"class="exportLinkButton btn-modal" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.wmsLayer.options.layers}&outputFormat=application/vnd.google-earth.kml+xml" data-cqlfilter="1=1"  data-filename="${kmlFileName}" data-type="kml">KML</button>
		  <button id="${j.name}Json"class="exportLinkButton btn-modal" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.wmsLayer.options.layers}&outputFormat=application/json" data-filename="${jsonFileName}" data-cqlfilter="1=1" data-type="json">GeoJson</button>
		  </div>`;
              var exportLinksString = `<div class="exportLinks">
<button id="${j.name}Shapefile"class="exportLinkButton btn-modal" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.wmsLayer.options.layers}&outputFormat=shape-zip" data-cqlfilter="${cqlFilter}" data-filename="${zipFileName}" data-type="zip">Shapefile</button>
			<button id="${j.name}Kml"class="exportLinkButton btn-modal" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.wmsLayer.options.layers}&outputFormat=application/vnd.google-earth.kml+xml" data-cqlfilter="${cqlFilter}"  data-filename="${kmlFileName}" data-type="kml">KML</button>
<button id="${j.name}Json"class="exportLinkButton btn-modal" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.wmsLayer.options.layers}&outputFormat=application/json" data-filename="${jsonFileName}" data-cqlfilter="${cqlFilter}" data-type="json">GeoJson</button>
</div>`;
              if (i.layerGroupOption == "filtered") {
                typeNames = j.wmsLayer.options.layers;
              } else {
                typeNames += j.wmsLayer.options.layers;
              }
              //if (that.layerReadable(j.wmsLayer.options.layers)) {
              if (
                i.layerGroupOption == "filtered" &&
                masterLinksAdded == false
              ) {
                addString += `${masterLinksString}`;
                addString2 += `${exportLinksString}</ul>`;
                addString += addString2;
                masterLinksAdded = true;
              } else if (
                i.layerGroupOption == "filtered" &&
                masterLinksAdded == true
              ) {
                addString2 += `${exportLinksString}</ul>`;
                addString += addString2;
              } else if (
                (i.layerGroupOption == "single" ||
                  i.layerGroupOption == undefined) &&
                masterLinksAdded == false
              ) {
                addString += `${masterLinksString}</ul>`;
                masterLinksAdded = true;
              } else if (i.layerGroupOption == "multiple") {
                addString2 += `${masterLinksString}</ul>`;
                addString += addString2;
              }
              //addString2 += "</ul>";
              subLayerCount += 1;
              //}
            }
          }
          layerCount += 1;
        });
        addString += "</li>";
        addString += "</ul>";
        //if (subLayerCount > 0) {
        if (that.layerReadable(curWfstLayer.name)) {
          htmlString += addString;

          //}
          var csvIdSelector = "#" + csvId;
          var geoJsonIdSelector =
            "#" + "geoJsonLink" + i.displayName.replace(/[^A-Z0-9]/gi, "");
          var csvLink = `${that.baseAPIURL}/export/?data=${typeNames}`;
          var geoJsonLink = `${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${typeNames}&outputFormat=application/json`;
          links.push({
            csvIdSelector: csvIdSelector,
            geoJsonIdSelector: geoJsonIdSelector,
            csvLink: csvLink,
            geoJsonLink: geoJsonLink,
          });
        }
      }
    });
    htmlString += "</div>";
    htmlString += "</div>";
    this.exportModal.html(htmlString);
    links.forEach(function (k) {
      $(k["csvIdSelector"]).attr("value", k["csvLink"]);
    });
  }
  generateEditModal() {
    //generate editModal based on user's permissions
    var htmlString =
      '<button type="button" id="closeEditModalButton" class="btn-modal"><svg width="24" height="24"><path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path></svg></button>';
    htmlString += "<h4>Choose a layer to edit</h4>";
    htmlString += '<div id="layerListContainer">';
    htmlString += '<div id="layerList">';
    var that = this;
    var layerCount = 0;
    function SortArray(x, y) {
      if (x.displayName < y.displayName) {
        return -1;
      }
      if (x.displayName > y.displayName) {
        return 1;
      }
      return 0;
    }
    var sortedFeatureGrouping = this.featureGrouping.sort(SortArray);
    sortedFeatureGrouping.forEach(function (i) {
      if (i.geoJsonLayers == undefined) {
        var subLayerCount = 0;
        var addString = "<ul>";
        addString += "<li>";
        addString += i.displayName;
        i.wfstLayers.forEach(function (j) {
          var addString2 = "<ul>";
          if (j.displayName != undefined) {
            addString2 += `<input type="radio" id="${j.layerName}EditSelector" name="EditSelector" value="${j.layerName}" required><label for="${j.layerName}EditSelector">${j.displayName}</label><br>`;
          } else {
            addString2 += `<input type="radio" id="${j.layerName}EditSelector" name="EditSelector" value="${j.layerName}" required><label for="${j.layerName}EditSelector">${j.name}</label><br>`;
          }
          addString2 += "</ul>";
          if (that.layerEditable(j.editWmsLayer.options.layers)) {
            addString += addString2;
            subLayerCount += 1;
          }
        });
        addString += "</li>";
        addString += "</ul>";
        if (subLayerCount > 0) {
          htmlString += addString;
        }
      }
    });
    htmlString += "</div>";
    htmlString += "</div>";
    htmlString +=
      '<div id="confirmEditLayerButtonContainer"><button type="button" id="confirmEditLayerButton" class="btn-modal"><img src="/img/save.png" width="20" height="20" alt="Submit" title="Submit" /></button></div>';
    this.editModal.html(htmlString);
  }
  layerReadable(layerName) {
    var readable = false;
    if (this.dataPermissions["read"].includes(layerName)) {
      readable = true;
    }
    return readable;
  }
  layerEditable(layerName) {
    //layer is editable (true/false) based on unique layerName
    var editable = false;
    if (this.dataPermissions["modify"].includes(layerName)) {
      editable = true;
    } else if (this.dataPermissions["delete"].includes(layerName)) {
      editable = true;
    } else if (this.dataPermissions["insert"].includes(layerName)) {
      editable = true;
    }
    return editable;
  }
  getCurrentId(wmsLayer, latlng) {
    //get currentId of wmsLayer based on latlng
    wmsLayer.addTo(this.map);
    var that = this;
    var url = wmsLayer.getFeatureInfoUrl(latlng);
    return new Promise((resolve, reject) => {
      that.appToken.check().then((data) => {
        var postData = "<token>" + this.appToken.token + "</token>";
        $.ajax({
          type: "POST",
          url: url,
          data: postData,
          //Type: "xml",
          success: function (data, status, xhr) {
            var curId = that.activeWfstLayer.getIDFromPopup(data);
            that.activeWfstLayer.curId = curId;
            wmsLayer.remove();
            resolve(true);
          },
          error: function (xhr, status, error) {
            wmsLayer.remove();
            reject(false);
          },
        });
      });
    });
  }
  formatSimplePopup(jsonContent, wmsParams) {
    var returnArray = [];
    var popupTitle = "<h4>" + wmsParams.label + "</h4>";
    for (let i = 0; i < jsonContent.features.length; i++) {
      const curFeature = jsonContent.features[i];
      var currentFeature = {};
      var popupHtml = `<html>
        <head>`;
      popupHtml += `<title>
            
          </title>
        </head>
    <body><table class="featureInfo">`;
      for (let i in curFeature.properties) {
        popupHtml += `<tr><td>${i.replace(/_/g, " ")}:</td><td>${
          curFeature.properties[i]
        }</td></tr>`;
      }
      popupHtml += "</table></body></html>";
      currentFeature.popupHtml = popupHtml;
      currentFeature.geometry = curFeature.geometry;
      currentFeature.geometry_name = curFeature.geometry_name;
      currentFeature.popupContent = popupTitle + popupHtml;
      returnArray.push(currentFeature);
    }
    return returnArray;
  }
  async addCommentsToPopups(
    jsonData,
    activeWfstLayer,
    popupTitle,
    editWmsLayerContent,
    getComments
  ) {
    var returnArray = [];
    for (let i = 0; i < jsonData.features.length; i++) {
      const curFeature = jsonData.features[i];
      //activeWfstLayer.setFidField();
      const curId = editWmsLayerContent.features[i].id.split(".")[1];
      activeWfstLayer.curId = curId;
      var currentFeature = {};
      currentFeature.OBJECTID =
        editWmsLayerContent.features[i].properties.OBJECTID;
      currentFeature.activeWfstLayer = activeWfstLayer;
      var commentsHtml = "";
      if (getComments) {
        const comments = await currentFeature.activeWfstLayer.getComments();
        const formattedComments = this.sortComments(comments);
        commentsHtml = this.printComments(formattedComments);
      }
      currentFeature.comments = commentsHtml;
      currentFeature.popupTitle = popupTitle;
      currentFeature.geometry = curFeature.geometry;
      currentFeature.geometry_name = curFeature.geometry_name;
      currentFeature.type = curFeature.type;
      var popupHtml = `<html>
        <head>`;
      if (this.editMode != "integrated") {
        if (this.armEditClick) {
          popupHtml += `<button id="editFeaturePopupButton">Edit Feature</button>`;
        } else if (this.armDeleteClick) {
          popupHtml += `<button id="editFeaturePopupButton">Delete Feature</button>`;
        }
      }
      popupHtml += `<title>
            
          </title>
        </head>
    <body><table class="featureInfo">`;
      for (let i in curFeature.properties) {
        popupHtml += `<tr><td>${i.replace(/_/g, " ")}:</td><td>${
          curFeature.properties[i]
        }</td></tr>`;
      }
      popupHtml += "</table></body></html>";
      currentFeature.popupHtml = popupHtml;
      currentFeature.popupContent = this.activeWfstLayer.convertDateTime(
        popupTitle + popupHtml + commentsHtml
      );
      returnArray.push(currentFeature);
    }
    return returnArray;
  }
  formatJsonPopup(jsonData, activeWfstLayer, popupTitle, editWmsLayerContent) {
    return new Promise((resolve, reject) => {
      var returnArray = [];
      //activeWfstLayer.options.showComments = false;
      var getComments = false;
      if (activeWfstLayer.options.showComments == true) {
        this.getDataPermissions()
          .then((permissions) => {
            this.getCurrentLayerPermissions(activeWfstLayer);
            if (this.currentLayerPermissions.comment) {
              getComments = true;
            }
            var returnArray = this.addCommentsToPopups(
              jsonData,
              activeWfstLayer,
              popupTitle,
              editWmsLayerContent,
              getComments
            );
            resolve(returnArray);
          })
          .catch(() => {
            console.log("Error getting permissions");
            reject(false);
          });
      } else {
        var returnArray = this.addCommentsToPopups(
          jsonData,
          activeWfstLayer,
          popupTitle,
          editWmsLayerContent,
          getComments
        );
        resolve(returnArray);
      }
    });
  }
  getPopup(activeWfstLayer, latlng, editWmsLayerContent) {
    //get popup of wmsLayer based on latlng
    var popupTitleHtml = "<h4>" + activeWfstLayer.displayName + "</h4>";
    var wmsLayer = activeWfstLayer.wmsLayer;
    wmsLayer.addTo(this.map);
    var that = this;
    var url = wmsLayer.getFeatureInfoUrl(latlng);
    return new Promise((resolve, reject) => {
      that.appToken
        .check()
        .then((msg) => {
          var postData = { token: this.appToken.token };
          var postDataString = JSON.stringify(postData);
          $.ajax({
            type: "POST",
            //contentType: "xml",
            data: postDataString,
            url: url,
            success: (data, status, xhr) => {
              var jsonData = JSON.parse(data);
              var popupObject;
              that
                .formatJsonPopup(
                  jsonData,
                  activeWfstLayer,
                  popupTitleHtml,
                  editWmsLayerContent
                )
                .then((data) => {
                  popupObject = data;
                  if (jsonData.length == 0) {
                    resolve(popupObject);
                  }
                  resolve(popupObject);
                  wmsLayer.remove();
                })
                .catch(() => {
                  console.log("Error retrieving popups");
                  reject(false);
                });
            },
            error: function (xhr, status, error) {
              wmsLayer.remove();
              console.log(error);
              reject(false);
            },
          });
        })
        .catch(() => {
          console.log("Error retrieving token.");
          reject(false);
        });
    });
  }
  printComments(commentsJson) {
    //format comments for display
    var commentString = '<div id=comments">';
    commentString += '<div id="commentHeader">';
    commentString +=
      '<h4>Comments</h4><button id="commentAddButton"><img src="/img/comment.png" width="20" height="20" alt="Add Comment" title="Add Comment" /></button>';
    commentString += "</div>";
    commentsJson.forEach(function (i) {
      commentString += '<div class="comment">';
      var convertedTimestamp = new Date(i["Timestamp"]);
      var formattedTimestamp = convertedTimestamp.toString();
      var convertedModifiedTimestamp = new Date(i["ModifiedTimestamp"]);
      var formattedModifiedTimestamp = convertedModifiedTimestamp.toString();
      commentString += '<div class="commentContainer">';
      commentString += `<div class="comment">${i["Comment"]}</div>`;
      commentString += `<p class="commentUser">${i["UserName"]}</p>`;
      commentString += `<p class="commentDate">Commented: ${formattedTimestamp}</p>`;
      if (i["ModifiedTimestamp"] != "" && i["ModifiedTimestamp"] != undefined) {
        commentString += `<p class="commentDate">Last Modified: ${formattedModifiedTimestamp}</p>`;
      }
      commentString += "</div>";
      commentString += '<div id="commentButtonContainer">';
      commentString += `<button class="commentReplyButton" title="Reply" value="${i["CommentId"]}"><img src="/img/reply.png" width="20" height="20" alt="Reply" title="Reply" /></button>`;
      if (i["RequesterOwnsComment"]) {
        commentString += `<button class="commentEditButton" value="${i["CommentId"]}"><img src="/img/edit.png" width="20" height="20" alt="Edit Comment" title="Edit Comment" /></button>`;
        if (i.Replies.length == 0) {
          commentString += `<button class="commentDeleteButton" value="${i["CommentId"]}"><img src="/img/delete.png" width="20" height="20" alt="Delete" title="Delete" /></button>`;
        }
      }
      commentString += "</div>";
      commentString += '<ul class="replies">';
      i.Replies.forEach(function (j) {
        var convertedTimestamp = new Date(j["Timestamp"]);
        var formattedTimestamp = convertedTimestamp.toString();
        var convertedModifiedTimestamp = new Date(j["ModifiedTimestamp"]);
        var formattedModifiedTimestamp = convertedModifiedTimestamp.toString();
        commentString += "<li>";
        commentString += `<div class="comment">${j["Comment"]}</div>`;
        commentString += `<p class="commentUser">${j["UserName"]}</p>`;
        commentString += `<p class="commentDate">Replied: ${formattedTimestamp}</p>`;
        if (
          j["ModifiedTimestamp"] != "" &&
          j["ModifiedTimestamp"] != undefined
        ) {
          commentString += `<p class="commentDate">Last Modified: ${formattedModifiedTimestamp}</p>`;
        }
        commentString += "</li>";
        commentString += '<div id="replyButtonContainer">';
        if (j["RequesterOwnsComment"]) {
          commentString += `<button class="commentEditButton" value="${j["CommentId"]}"><img src="/img/edit.png" width="20" height="20" alt="Edit Comment" title="Edit Comment" /></button>`;
          commentString += `<button class="commentDeleteButton" value="${j["CommentId"]}"><img src="/img/delete.png" width="20" height="20" alt="Delete Comment" title="Delete Comment" /></button>`;
        }
        commentString += "</div>";
      });

      commentString += "</ul>";
      commentString += "</div>";
    });
    commentString += "</div>";
    return commentString;
  }
  sortComments(commentsJson) {
    //sort comments by date and replies
    var formattedComments = [];
    var replies = [];
    for (var i = 0; i < commentsJson.length; i++) {
      var curComment = {};
      if (commentsJson[i]["ReplyId"] != null) {
        replies.push(commentsJson[i]);
      } else {
        curComment["CommentId"] = commentsJson[i]["CommentId"];
        curComment["Replies"] = [];
        curComment["OBJECTID"] = commentsJson[i]["OBJECTID"];
        curComment["UserName"] = commentsJson[i]["UserName"];
        curComment["Comment"] = commentsJson[i]["Comment"];
        curComment["Timestamp"] = commentsJson[i]["Timestamp"];
        curComment["ModifiedTimestamp"] = commentsJson[i]["ModifiedTimestamp"];
        curComment["RequesterOwnsComment"] =
          commentsJson[i]["RequesterOwnsComment"];
        curComment["CommentType"] = commentsJson[i]["CommentType"];
        formattedComments.push(curComment);
      }
    }
    formattedComments.sort(function (a, b) {
      return a.Timestamp - b.Timestamp;
    });
    replies.sort(function (a, b) {
      return a.Timestamp - b.Timestamp;
    });
    replies.forEach(function (j) {
      formattedComments.forEach(function (k) {
        if (k["CommentId"] == j["ReplyId"]) {
          k["Replies"].push(j);
        }
      });
    });
    return formattedComments;
  }
  closeChartModalButtonClick() {
    this.currentChart = {};
    try {
      this.currentApexChart.destroy();
    } catch (e) {
    } finally {
      this.chartModal.html("");
      this.chartModal.css("display", "none");
    }
  }
  closeExportModalButtonClick() {
    this.exportModal.css("display", "none");
  }
  closeImgModalButtonClick() {
    this.imgModal.css("display", "none");
  }
  chartButtonClick() {
    this.getDataPermissions().then((msg) => {
      var maxHeight = $(document).height();
      var maxWidth = $(document).width();
      this.sizeModal(this.chartModal, maxWidth, maxHeight);
      this.generateChartModal();
    });
  }
  exportButtonClick() {
    this.exportModal.html("");
    this.getDataPermissions().then((data) => {
      var featureGrouping = this.buildFeatureGrouping(
        this.options.featureGrouping
      );
      this.setFeatureGrouping(featureGrouping);
      this.generateExportModal();
      //change display prop of exportModal obj to block
      this.sizeModal(this.exportModal, 500, 400);
      this.exportModal.css("display", "block");
    });
  }
  exportLinkButtonClick() {
    var curElement = this.exportLinkButton.prevObject[0].activeElement;
    var fileName = curElement.getAttribute("data-filename");
    var dataType = curElement.getAttribute("data-type");
    var cqlFilter = curElement.getAttribute("data-cqlfilter");
    var link = curElement.value;
    this.appToken.check().then((data) => {
      var token = this.appToken.token;
      link += "&download=true&token=" + token + "&cql_filter=" + cqlFilter;
      var downloadLink = document.createElement("a");
      downloadLink.href = link;
      downloadLink.setAttribute("download", fileName);
      downloadLink.click();
    });
  }
  commentAddButtonClick() {
    this.commentModal.html("");
    this.generateAddCommentModal();
    this.sizeModal(this.commentModal, 500, 400);
    this.commentModal.css("display", "block");
    this.writing = true;
    this.tinyMceInit();
  }
  closeCommentModalButtonClick() {
    this.commentModal.css("display", "none");
    this.writing = false;
    tinyMCE.remove("textarea");
  }
  commentSubmitButtonClick() {
    tinyMCE.triggerSave();
    var comment = $("#commentEntry").val();
    var commentStatus = "Active";
    var commentType = "Internal";
    this.activeWfstLayer
      .addComment(comment, commentStatus, commentType)
      .then((data) => {})
      .catch((data) => {
        console.log("Error adding comment");
      })
      .finally((data) => {
        this.commentModal.css("display", "none");
        tinyMCE.remove("textarea");
        this.map.closePopup();
        this.writing = false;
      });
  }
  commentReplyButtonClick() {
    this.commentModal.html("");
    var curCommentId = this.commentReplyButton.prevObject[0].activeElement
      .value;
    this.curCommentId = curCommentId;
    var htmlString =
      '<button type="button" id="closeCommentModalButton"><svg width="24" height="24"><path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path></svg></button>';
    htmlString += "<h4>Add Reply</h4>";
    htmlString +=
      '<input type="number" id="replyId" name="replyId" min="10" max="100" value="' +
      curCommentId +
      '" hidden>';
    htmlString += '<label for="commentEntry">Comment:</label>';
    htmlString +=
      '<textarea id="commentEntry" name="commentEntry" rows="5" cols="45" required></textarea><br>';
    htmlString +=
      '<button type="button" id="commentReplySubmitButton"><img src="/img/save.png" width="20" height="20" alt="Submit" title="Submit" /></button>';
    this.commentModal.html(htmlString);
    this.commentModal.css("display", "block");
    this.writing = true;
    this.tinyMceInit();
  }
  commentReplySubmitButtonClick() {
    tinyMCE.triggerSave();
    var comment = $("#commentEntry").val();
    var commentStatus = "Active";
    var commentType = "Internal";
    this.activeWfstLayer
      .addComment(comment, commentStatus, commentType, this.curCommentId)
      .then((data) => {})
      .catch((data) => {
        console.log("Error adding reply");
      })
      .finally((data) => {
        this.commentModal.css("display", "none");
        tinyMCE.remove("textarea");
        this.map.closePopup();
        this.writing = false;
      });
  }
  commentDeleteButtonClick() {
    var curCommentId = this.commentDeleteButton.prevObject[0].activeElement
      .value;
    this.curCommentId = curCommentId;
    var htmlString =
      '<button type="button" id="closeCommentModalButton"><svg width="24" height="24"><path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path></svg></button>';
    htmlString += "<h2>Delete Comment</h2>";
    htmlString += "<p>Are you sure?</p>";
    htmlString +=
      '<button type="button" id="commentDeleteSubmitButton"><img src="/img/save.png" width="20" height="20" alt="Submit" title="Submit" /></button>';
    this.commentModal.html(htmlString);
    this.commentModal.css("display", "block");
    this.tinyMceInit();
  }
  commentDeleteSubmitButtonClick() {
    this.activeWfstLayer.getComments(this.curCommentId).then((data) => {
      var comment = data[0]["Comment"];
      var token = this.appToken.token;
      var commentStatus = "Inactive";
      var commentType = data[0]["CommentType"];
      var commentId = this.curCommentId;
      this.activeWfstLayer
        .updateComment(commentId, comment, commentStatus, commentType)
        .then((msg) => {})
        .catch((msg) => {
          console.log("Error deleting comment");
        })
        .finally((msg) => {
          this.commentModal.css("display", "none");
          tinyMCE.remove("textarea");
          this.map.closePopup();
        });
    });
  }
  nextPopupButtonClick() {
    this.popupIndex++;
    try {
      this.activeWfstLayer = this.popupArray[this.popupIndex].activeWfstLayer;
      this.activeWfstLayer.curId = this.popupArray[this.popupIndex].OBJECTID;
    } catch (error) {
      //keep going
    }
    document.getElementsByClassName(
      "leaflet-popup-content"
    )[0].innerHTML = this.addPopupLinks(
      this.popupArray[this.popupIndex].popupContent
    );
    this.popupLayer.remove();
    this.addPopupLayer();
  }
  previousPopupButtonClick() {
    this.popupIndex--;
    try {
      this.activeWfstLayer = this.popupArray[this.popupIndex].activeWfstLayer;
      this.activeWfstLayer.curId = this.popupArray[this.popupIndex].OBJECTID;
    } catch (error) {
      //keep going
    }
    document.getElementsByClassName(
      "leaflet-popup-content"
    )[0].innerHTML = this.addPopupLinks(
      this.popupArray[this.popupIndex].popupContent
    );
    this.popupLayer.remove();
    this.addPopupLayer();
  }
  zoomToActiveLayerButtonClick() {
    this.autoZoom = true;
    this.map.fitBounds(this.popupLayer.getBounds());
  }
  addPopupLayer() {
    var highlightColour = "#03d3fc";
    var feature = this.popupArray[this.popupIndex].geometry;
    if (feature.type.toLowerCase().includes("point")) {
      this.popupLayer = L.geoJSON(feature, {
        pointToLayer: function (feature, latlng) {
          return L.circleMarker(latlng, {
            radius: 8,
            fillColor: highlightColour,
            color: highlightColour,
            weight: 3,
            opacity: 1,
            fillOpacity: 0.6,
          });
        },
      });
    } else if (feature.type.toLowerCase().includes("line")) {
      this.popupLayer = L.geoJSON(feature, {
        style: function () {
          return { color: highlightColour, weight: 5 };
        },
      });
    } else {
      //polygon
      this.popupLayer = L.geoJSON(feature, {
        style: function () {
          return {
            color: highlightColour,
            weight: 5,
            fillOpacity: 0.6,
            fillColor: highlightColour,
          };
        },
      });
    }
    this.popupLayer.addTo(this.map);
  }
  commentEditButtonClick() {
    this.commentModal.html("");
    var commentId = this.commentEditButton.prevObject[0].activeElement.value;
    this.curCommentId = commentId;
    this.activeWfstLayer
      .getComments(commentId)
      .then((data) => {
        var htmlString =
          '<button type="button" id="closeCommentModalButton"><svg width="24" height="24"><path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path></svg></button>';
        htmlString += "<h4>Edit Comment</h4>";
        htmlString += '<label for="commentEntry">Comment:</label>';
        htmlString +=
          '<textarea id="commentEntry" name="commentEntry" rows="5" cols="45" required>' +
          data[0]["Comment"] +
          "</textarea><br>";
        htmlString +=
          '<button type="button" id="commentEditSubmitButton"><img src="/img/save.png" width="20" height="20" alt="Submit" title="Submit" /></button>';
        this.commentModal.html(htmlString);
        this.commentModal.css("display", "block");
        this.writing = true;
        this.tinyMceInit();
      })
      .catch((data) => {
        console.log("Error retreiving comment");
      });
  }
  commentEditSubmitButtonClick() {
    tinyMCE.triggerSave();
    var comment = $("#commentEntry").val();
    var commentStatus = "Active";
    var commentType = "Internal";
    this.activeWfstLayer
      .updateComment(this.curCommentId, comment, commentStatus, commentType)
      .then((data) => {})
      .catch((data) => {
        console.log("Error editing comment");
      })
      .finally((data) => {
        this.curCommentId = undefined;
        this.commentModal.css("display", "none");
        tinyMCE.remove("textarea");
        this.map.closePopup();
        this.writing = false;
      });
  }
  generateAddCommentModal() {
    //genereate a blank comment form
    var htmlString =
      '<button type="button" id="closeCommentModalButton"><svg width="24" height="24"><path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path></svg></button>';
    htmlString += "<h4>Add Comment</h4>";
    htmlString += '<label for="commentEntry">Comment:</label>';
    htmlString +=
      '<textarea id="commentEntry" name="commentEntry" rows="5" cols="45" required></textarea><br>';
    htmlString +=
      '<button type="button" id="commentSubmitButton"><img src="/img/save.png" width="20" height="20" alt="Submit" title="Submit" /></button>';
    this.commentModal.html(htmlString);
    this.writing = true;
  }
  populateLayerControl() {
    //populate layerControl based on featureGrouping
    if (this.layerControlObj != undefined) {
      this.layerControlObj["_layers"].forEach(function (l) {
        if (l.name == "Imagery" || l.name == "Map") {
          l.layer.remove();
        }
      });
      this.layerControlObj.remove();
      this.layerControlObj = undefined;
    }
    var mapLink = '<a href="http://www.esri.com/">Esri</a>';
    var wholink =
      "i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community";
    var imageBaseMap = L.tileLayer(
      "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
      {
        attribution: "&copy; " + mapLink + ", " + wholink,
        maxZoom: 18,
      }
    );
    var mapBaseMap = L.tileLayer(
      "https://tiles.stadiamaps.com/tiles/osm_bright/{z}/{x}/{y}{r}.png",
      {
        maxZoom: 18,
        attribution:
          '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
      }
    ).addTo(this.map);
    var darkBaseMap = L.tileLayer(
      "https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}{r}.png",
      {
        maxZoom: 18,
        attribution:
          '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
      }
    );
    var neutralBaseMap = L.tileLayer(
      "https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png",
      {
        maxZoom: 18,
        attribution:
          '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
      }
    );
    this.currentBaseMap = mapBaseMap;
    mapBaseMap.bringToBack();
    var layerControl = {};
    var that = this;
    this.featureGrouping.forEach(function (i) {
      if (i.wfstLayers != undefined) {
        var index = "wfstLayers";
      } else if (i.geoJsonLayers != undefined) {
        var index = "geoJsonLayers";
      }
      i[index].forEach(function (j) {
        var wfstLayer = j;
        try {
          if (j.options.type == "external/geojson") {
            layerControl[wfstLayer.displayName] = wfstLayer.geoJsonLayer;
          } else {
            if (
              that.layerReadable(j.editWmsLayer.options.layers) ||
              j.options.type == "external/wms"
            ) {
              var layer = wfstLayer.editWmsLayer;
              if (wfstLayer.displayName != undefined) {
                layerControl[wfstLayer.displayName] = layer;
              } else {
                layerControl[wfstLayer.layerName] = layer;
              }
            }
          }
        } catch (e) {
          console.log(
            "Layer " + j + " not loaded due to permissions issue.",
            e
          );
        }
      });
    });
    var baseMapControl = {
      Imagery: imageBaseMap,
      "Bright Map": mapBaseMap,
      "Neutral Map": neutralBaseMap,
      "Dark Map": darkBaseMap,
    };
    if (layerControl == {}) {
      this.layerControlObj = L.control.layers(baseMapControl, {
        collapsed: true,
        position: "bottomright",
      });
    } else {
      this.layerControlObj = L.control.layers(baseMapControl, layerControl, {
        collapsed: true,
        position: "bottomright",
      });
    }
    this.layerControlObj.addTo(this.map);
  }
  populateLegend() {
    //add legend images to layerControl
    for (var j = 0; j < this.wfstLayers.length; j++) {
      if (this.wfstLayers[j].options.type == "external/geojson") {
        var layer = this.wfstLayers[j].geoJsonLayer;
      } else {
        var layer = this.wfstLayers[j].editWmsLayer;
      }
      if (layer.wmsParams != undefined) {
        if (this.wfstLayers[j].displayName != undefined) {
          var displayName = this.wfstLayers[j].displayName;
        } else {
          var displayName = this.wfstLayers[j].name;
        }
        var category = this.wfstLayers[j].editWmsLayer.wmsParams.category;
        var defaultVisibility = this.wfstLayers[j].editWmsLayer.wmsParams
          .defaultVisibility;
        var aTags = document.getElementsByTagName("span");
        var searchText = displayName;
        var legendImg = `${this.wfstLayers[j].baseAPIURL}/wms/?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=30&HEIGHT=30&LAYER=${layer.wmsParams.layers}`;
        if (this.wfstLayers[j].options.type != "external/wms") {
          legendImg += `&token=${this.appToken.token}`;
        }
        legendImg += `&${Date.now()}`;
        if (layer.wmsParams.styles != undefined) {
          legendImg += "&style=" + layer.wmsParams.styles;
        }
        var img = document.createElement("img");
        img.src = legendImg;
        var lineBreak = document.createElement("br");
        for (var i = 0; i < aTags.length; i++) {
          if (aTags[i].innerText.trim() == searchText) {
            var parent = aTags[i].parentElement;
            $(parent)
              .find("input[type='checkbox']")
              .prop("name", displayName)
              .attr("category", category)
              .attr("default-visibility", defaultVisibility);
            aTags[i].innerHTML = displayName;
            aTags[i].appendChild(lineBreak);
            aTags[i].appendChild(img);
            break;
          }
        }
      } else {
        var displayName = this.wfstLayers[j].displayName;
        var aTags = document.getElementsByTagName("span");
        var searchText = displayName;
        var svgElement = this.wfstLayers[j].svgLegend;
        var lineBreak = document.createElement("br");
        for (var i = 0; i < aTags.length; i++) {
          if (aTags[i].innerText.trim() == searchText) {
            var parent = aTags[i].parentElement;
            $(parent).find("input[type='checkbox']").prop("name", displayName);
            aTags[i].innerHTML = displayName;
            aTags[i].appendChild(lineBreak);
            aTags[i].appendChild(svgElement);
            break;
          }
        }
      }
    }
    var legendLoadedEvt = new Event("legendLoaded");
    document.dispatchEvent(legendLoadedEvt);
  }
  startEditButtonClick() {
    //start edit session
    if (this.editSession == false) {
      this.getDataPermissions()
        .then((data) => {
          if (this.editMode == "integrated") {
            this.wfstLayers.forEach(function (i) {
              if (i.bounds != undefined) {
                i.edit(true);
              }
            });
            if (
              this.editableWfstLayer() == undefined ||
              this.editableWfstLayer().editMode == "add"
            ) {
              this.generateEditModal();
              this.sizeModal(this.editModal, 500, 400);
              this.editModal.css("display", "block");
            } else if (this.editableWfstLayer().editMode == "edit") {
              this.getCurrentLayerPermissions();
              if (this.currentLayerPermissions["modify"]) {
                this.editButton.click();
              }
            }
          } else {
            this.generateEditModal();
            this.sizeModal(this.editModal, 500, 400);
            this.editModal.css("display", "block");
          }
        })
        .catch((data) => {
          console.log("Error getting permissions.", data);
        });
    } else {
      this.startEditButton.html("Start Edit Session");
      this.addButton.hide();
      this.editButton.hide();
      this.deleteButton.hide();
      this.editSession = false;
      this.stopEditing();
    }
  }
  stopEditing() {
    //stop edit ession
    this.wfstLayers.forEach(function (i) {
      if (i.geoJsonLayer == undefined) {
        if (i.edit()) {
          i.edit(false);
        }
      }
    });
  }
  editableWfstLayer(parameter = undefined) {
    //return EditMap's WfstLayer where WfstLayer.edit()==true
    var returnValue;
    this.wfstLayers.forEach(function (i) {
      if (i.geoJsonLayer == undefined) {
        if (i.edit()) {
          if (parameter != undefined) {
            i[parameter["paramName"]] = parameter["paramValue"];
          } else {
            returnValue = i;
          }
        }
      }
    });
    return returnValue;
  }

  closeEditModalButtonClick() {
    //close the edit Modal
    this.editModal.css("display", "none");
  }
  confirmEditLayerButtonClick() {
    //confirm selection of edit layer in edit modal
    if (this.editSession == false) {
      this.editSession = true;
      var checkedRadio = this.editModal.find("input[type='radio']:checked");
      var checkedLayer = checkedRadio.val();
      if (checkedLayer != undefined) {
        this.getWfstLayerFromName(checkedLayer, "wfstLayerName").edit(true);
        var that = this;
        this.getCurrentLayerPermissions();
        this.editModal.css("display", "none");
        this.showEditControls();
        this.startEditButton.html("Stop Edit Session");
        if (this.map.hasLayer(this.editableWfstLayer().editWmsLayer) == false) {
          this.editableWfstLayer().editWmsLayer.setOpacity(1);
          this.editableWfstLayer().editWmsLayer.addTo(this.map);
          this.populateLegend();
        }
      } else {
        //radio button not checked, make title red
        $("#editModal h4").css("color", "red");
        this.editSession = false;
      }
    }
    if (this.editMode == "integrated") {
      this.getCurrentLayerPermissions();
      if (this.currentLayerPermissions["insert"]) {
        this.addButton.click();
      }
    }
  }
  nonEditLayersVisible(visible) {
    //All non-editable layers opacity toggle
    if (visible) {
      var opacity = 1;
    } else {
      var opacity = 0;
    }
    var that = this;
    this.wfstLayers.forEach(function (i) {
      if (i.geoJsonLayer == undefined) {
        if (i.edit() == false) {
          i.editWmsLayer.setOpacity(opacity);
        }
      } else {
        if (opacity == 1) {
          if (i.options.visible) {
            i.geoJsonLayer.addTo(that.map);
          }
        } else {
          i.geoJsonLayer.remove();
        }
      }
    });
    this.populateLegend();
  }
  cancelEditLayerButtonClick() {
    //cancel button click event for editModal
    this.editFeatureSession = false;
    this.editWfstLayer = undefined;
    this.editModal.css("display", "none");
    this.editSession = false;
  }
  showEditControls() {
    //show edit controls based on currentLayerPermissions
    if (this.editMode != "integrated") {
      this.startEditButton.show();
      if (this.currentLayerPermissions["insert"] == true) {
        this.addButton.show();
      }
      if (this.currentLayerPermissions["modify"] == true) {
        this.editButton.show();
      }
      if (this.currentLayerPermissions["delete"] == true) {
        this.deleteButton.show();
      }
    } else {
      this.startEditButton.html("Start Edit Session");
      this.startEditButton.show();
    }
  }
  getDataPermissions() {
    //get the current user's data permissions
    return new Promise((resolve, reject) => {
      this.appToken.check().then((msg) => {
        var url = this.baseAPIURL + "/permissions/";
        var postData = { token: this.appToken.token };
        var postDataString = JSON.stringify(postData);
        var that = this;
        $.ajax({
          type: "POST",
          url: url,
          data: postDataString,
          dataType: "json",
          //contentType: "json",
          success: function (data) {
            that.dataPermissions = data;
            resolve(true);
          },
          error: function (data) {
            reject(false);
          },
        });
      });
    });
  }
  getCurrentLayerPermissions(wfstLayer = this.editableWfstLayer()) {
    //based on the current user's data permissions structure
    //an object to define permissions for a single layer
    var currentLayerPermissions = {
      read: false,
      insert: false,
      modify: false,
      delete: false,
      comment: false,
    };

    for (let key in currentLayerPermissions) {
      if (this.dataPermissions[key].includes(wfstLayer.name)) {
        currentLayerPermissions[key] = true;
      }
    }
    this.currentLayerPermissions = currentLayerPermissions;
  }
  getWfstLayerFromName(name, option = "wmsLayerName") {
    //given a unique name return the WfstLayer object
    var returnWfstLayer;
    this.wfstLayers.forEach(function (i) {
      if (option == "wmsLayerName") {
        if (i.wmsLayer.options.layers == name) {
          returnWfstLayer = i;
        } else if (i.editWmsLayer.options.layers == name) {
          returnWfstLayer = i;
        }
      } else if (option == "wfstLayerName") {
        if (i.layerName == name) {
          returnWfstLayer = i;
        }
      }
    });
    return returnWfstLayer;
  }
  sum(a, b) {
    return a + b;
  }
  addButtonClick() {
    //add button click
    this.featureCount = 0;
    this.cancelAddButton.show();
    this.startEditButton.hide();
    this.editButton.hide();
    this.deleteButton.hide();
    this.editableWfstLayer().editWmsLayer.options.showPopup = false;
    var drawOptions = { continueDrawing: true };
    if (this.editFeatureSession == false) {
      this.editFeatureSession = true;
      if (this.editMode == "integrated") {
        this.addButton.html("Save");
        this.addButton.show();
      } else {
        this.addButton.html("Finish Feature");
      }
      if (
        this.editableWfstLayer().featureType == "gml:MultiPointPropertyType" ||
        this.editableWfstLayer().featureType == "gml:PointPropertyType"
      ) {
        this.map.pm.enableDraw("Marker", drawOptions);
        $(".leaflet-tooltip").css("top", "25px");
        $(".leaflet-tooltip").css("left", "-15px");
      } else if (
        this.editableWfstLayer().featureType == "gml:MultiCurvePropertyType"
      ) {
        this.map.pm.enableDraw("Line", drawOptions);
      } else if (
        this.editableWfstLayer().featureType == "gml:MultiSurfacePropertyType"
      ) {
        this.map.pm.enableDraw("Polygon", drawOptions);
      } else {
        console.log("Error: Unsupported geometry type.");
      }
    } else {
      if (this.editMode == "integrated") {
        this.addAttributesButtonClick();
        this.stopDraw();
        this.addButton.html("Add Feature");
        this.addButton.hide();
        this.editSession = false;
      } else {
        this.addButton.html("Add Feature");
        this.stopDraw();
        this.addButton.hide();
        this.editFeatureSession = false;
        this.nonEditLayersVisible(true);
        this.editLayer.addTo(this.map);
        if (this.editLayer["pm"]["_layers"].length == 0) {
          this.cancelAddButton.hide();
          this.stopEditFeatureSession();
        } else {
          var htmlForm = this.editableWfstLayer().getPopupForm();
          var popupContent = htmlForm;
          this.editLayer.bindPopup(popupContent).openPopup();
        }
      }
    }
  }
  pmCreate(e) {
    //on create of geoman feature
    if (this.editableWfstLayer().featureType == "gml:MultiPointPropertyType") {
      this.editLayer.addLayer(e["marker"]);
      this.map.pm.enableDraw("Marker");
      this.featureMode = "Marker";
    } else if (
      this.editableWfstLayer().featureType == "gml:PointPropertyType"
    ) {
      this.editLayer.addLayer(e["marker"]);
      this.map.pm.disableDraw("Marker");
      this.featureMode = "Marker";
    } else if (this.editableWfstLayer().featureType == "gml:Line") {
      this.editLayer.addLayer(e["layer"]);
      this.map.pm.disableDraw("Line");
      this.featureMode = "Line";
    } else if (this.editableWfstLayer().featureType == "gml:Polygon") {
      this.editLayer.addLayer(e["layer"]);
      this.map.pm.disableDraw("Polygon");
      this.featureMode = "Polygon";
    } else if (
      this.editableWfstLayer().featureType == "gml:MultiCurvePropertyType" ||
      this.editableWfstLayer().featureType == "gml:MultiLine"
    ) {
      this.editLayer.addLayer(e["layer"]);
      this.map.pm.enableDraw("Line");
      this.featureMode = "Line";
    } else if (
      this.editableWfstLayer().featureType == "gml:MultiSurfacePropertyType" ||
      this.editableWfstLayer().featureType == "gml:MultiPolygon"
    ) {
      this.editLayer.addLayer(e["layer"]);
      this.map.pm.enableDraw("Polygon");
      this.featureMode = "Polygon";
    }
  }
  addToFeatureButtonClick() {
    //add to feature button click
    var editOptions = { continueDrawing: true };
    if (this.addToFeatureSession == false) {
      this.addToFeatureButton.html("Save");
      this.editButton.hide();
      this.addToFeatureSession = true;
      this.editLayer.pm.disable();
      this.editLayer.closePopup();
      this.editLayer.unbindPopup();
      if (
        this.editableWfstLayer().featureType == "gml:MultiPointPropertyType" ||
        this.editableWfstLayer().featureType == "gml:PointPropertyType"
      ) {
        this.map.pm.enableDraw("Marker", editOptions);
      } else if (
        this.editableWfstLayer().featureType == "gml:MultiCurvePropertyType"
      ) {
        this.map.pm.enableDraw("Line", editOptions);
      } else if (
        this.editableWfstLayer().featureType == "gml:MultiSurfacePropertyType"
      ) {
        this.map.pm.enableDraw("Polygon", editOptions);
      } else {
        console.log("Error: Unsupported geometry type.");
      }
    } else {
      this.stopDraw();
      var editFormArray = $("editAttributesForm").serializeArray();
      tinyMCE.triggerSave();
      this.editableWfstLayer()
        .updateFeature(this.editLayer)
        .then((data) => {})
        .catch((data) => {
          console.log("Error editing feature");
        })
        .finally((data) => {
          this.addToFeatureButton.html("Add To Feature");
          this.addToFeatureButton.hide();
          this.editButton.html("Edit Feature");
          this.cancelEditButton.hide();
          this.stopEditFeatureSession();
        });
    }
  }
  requiredFieldsFilled(formId) {
    var a = $("#" + formId + " input,textarea,select").filter(
      "[required]:visible"
    );
    var filled = true;
    $.each(a, function (key, val) {
      if (val.value == "" || val.value == undefined) {
        filled = false;
      }
    });
    return filled;
  }
  addAttributesButtonClick() {
    if (this.requiredFieldsFilled("addAttributesForm")) {
      tinyMCE.triggerSave();
      this.editableWfstLayer()
        .addFeature(this.editLayer)
        .then((msg) => {
          this.editableWfstLayer({ paramName: "editMode", paramValue: "edit" });
          this.stopEditFeatureSession();
        })
        .catch((msg) => {
          this.editableWfstLayer({ paramName: "editMode", paramValue: "add" });
          this.stopEditFeatureSession();
          if (this.editMode == "integrated") {
            this.stopEditing();
          }
          console.log("Error adding features");
        })
        .finally((msg) => {
          this.addButton.html("Add Feature");
          this.cancelAddButton.hide();
        });
    }
  }
  cancelAddButtonClick() {
    //cancel add button click
    this.addButton.html("Add Feature");
    this.cancelAddButton.hide();
    this.stopDraw();
    this.stopEditFeatureSession();
    if (this.editMode == "integrated") {
      //cancelling the add should cancel the
      //whole edit session in integrated mode
      this.startEditButton.click();
    }
  }
  editFeaturePopupButtonClick() {
    this.activeWfstLayer.curId = this.popupArray[this.popupIndex].OBJECTID;
    var editEvt = new Event("gotFeatureInfo");
    document.getElementById(this.mapDivId).dispatchEvent(editEvt);
  }
  editButtonClick() {
    //edit button click
    this.cancelEditButton.show();
    this.addButton.hide();
    this.startEditButton.hide();
    this.deleteButton.hide();
    this.editButton.hide();
    this.armEditClick = true;
    if (this.editFeatureSession == false) {
      this.mapDiv.attr("title", "Click on a feature to edit");
      this.mapDiv.tooltip("enable");
      this.mapDiv.trigger("mouseenter");
      this.curEditID = undefined;
      this.nonEditLayersVisible(false);
      var that = this;
      this.handleGotFeatureInfoEdit = function (e) {
        if (that.armEditClick) {
          that.editFeatureSession = true;
          that.map.closePopup();
          that.addToFeatureButton.show();
          that.mapDiv.attr("title", "");
          that.mapDiv.tooltip("disable");
          that.editButton.show();
          that.editableWfstLayer().curEditId = that.activeWfstLayer.curId;
          that.editableWfstLayer().editWmsLayer.setOpacity(0);
          that
            .editableWfstLayer()
            .getWFSFeatureFromId(that.editableWfstLayer().curEditId)
            .then((featureData) => {
              that.nonEditLayersVisible(true);
              var featureProperties = featureData["features"][0]["properties"];
              var editPopupContent = that
                .editableWfstLayer()
                .getEditPopupForm(featureProperties);
              var geoJsonLayer = L.GeoJSON.geometryToLayer(
                featureData["features"][0]
              );
              that.editLayer.addLayer(geoJsonLayer);
              if (
                that.editableWfstLayer().featureType ==
                "gml:MultiPointPropertyType"
              ) {
                that.editLayer.addTo(that.map);
                that.editLayer.pm.enable({
                  limitMarkersToCount: 20,
                });
                that.editLayer.setStyle({
                  color: "#e4f00a",
                  weight: 5,
                });
                if (that.editMode != "integrated") {
                  that.editLayer.bindPopup(editPopupContent);
                }
              }
              that.editLayer.addTo(that.map);
              that.editLayer.pm.enable({
                limitMarkersToCount: 20,
              });
              that.editLayer.setStyle({
                color: "#e4f00a",
                weight: 5,
              });
              if (that.editMode != "integrated") {
                that.editLayer.bindPopup(editPopupContent);
              }
            })
            .catch((featureData) => {
              that.editButton.html("Edit Feature");
              that.cancelEditButton.hide();
              that.startEditButton.show();
              that.addToFeatureButton.hide();
              that.stopDraw();
              that.stopEditFeatureSession();
              console.log("Error retrieving feature");
            });
          delete this.handleGotFeatureInfoEdit;
          that.armEditClick = false;
        }
      };
      document
        .getElementById(this.mapDivId)
        .addEventListener("gotFeatureInfo", this.handleGotFeatureInfoEdit, {
          once: true,
        });
      this.editButton.html("Save");
    } else {
      //check if all required fields are filled
      tinymce.triggerSave();
      var editFormArray = $("#editAttributesForm").serializeArray();
      this.editableWfstLayer()
        .updateFeature(this.editLayer)
        .then((msg) => {
          this.stopEditFeatureSession();
        })
        .catch((msg) => {
          console.log("Error editing feature");
          this.stopEditFeatureSession();
          if (this.editMode == "integrated") {
            this.stopEditing();
          }
        })
        .finally((msg) => {
          this.editButton.html("Edit Feature");
          this.cancelEditButton.hide();
          //this.startEditButton.show();
          this.addToFeatureButton.hide();
          this.stopDraw();
          //this.populateLegend();
        });
    }
  }
  editAttributesButtonClick() {
    //edit attributes button click
    if (this.requiredFieldsFilled("editAttributesForm")) {
      this.editButtonClick();
    }
  }
  cancelEditButtonClick() {
    //cancel edit button click
    this.editButton.html("Edit Feature");
    if (this.editMode == "integrated") {
      this.editButton.hide();
    }
    this.addToFeatureButton.html("Add to Feature");
    this.addToFeatureButton.hide();
    this.cancelEditButton.hide();
    this.stopDraw();
    this.stopEditFeatureSession();
    //this.populateLegend();
  }
  deleteButtonClick() {
    //delete button click
    this.editableWfstLayer().editWmsLayer.addTo(this.map);
    this.editableWfstLayer().editWmsLayer.setOpacity(1);
    this.armDeleteClick = true;
    this.cancelDeleteButton.show();
    this.startEditButton.hide();
    this.cancelEditButton.hide();
    this.addButton.hide();
    this.editButton.hide();
    this.deleteButton.html("Confirm Delete");
    this.deleteButton.hide();
    if (this.editFeatureSession == false) {
      this.mapDiv.attr("title", "Click on a feature to delete");
      this.mapDiv.tooltip("enable");
      this.nonEditLayersVisible(false);
      var that = this;
      this.handleGotFeatureInfoDelete = function (e) {
        if (that.armDeleteClick == true) {
          that.editLayer.unbindPopup();
          that.editFeatureSession = true;
          that.mapDiv.attr("title", "");
          that.mapDiv.tooltip("disable");
          that.deleteButton.show();
          that.map.closePopup();
          that.armDeleteClick = false;
          that.editableWfstLayer().curDeleteId = that.editableWfstLayer().curId;
          that.editableWfstLayer().editWmsLayer.setOpacity(0);
          that
            .editableWfstLayer()
            .getWFSFeatureFromId(that.editableWfstLayer().curDeleteId)
            .then((data) => {
              var highlightColour = "#fc1403";
              var feature = data;
              if (
                that
                  .editableWfstLayer()
                  .featureType.toLowerCase()
                  .includes("point")
              ) {
                var geoJsonLayer = L.geoJSON(feature, {
                  pointToLayer: function (feature, latlng) {
                    return L.circleMarker(latlng, {
                      radius: 8,
                      fillColor: highlightColour,
                      color: highlightColour,
                      weight: 3,
                      opacity: 1,
                      fillOpacity: 0.6,
                    });
                  },
                });
              } else if (
                that
                  .editableWfstLayer()
                  .featureType.toLowerCase()
                  .includes("line")
              ) {
                var geoJsonLayer = L.geoJSON(feature, {
                  style: function () {
                    return { color: highlightColour, weight: 5 };
                  },
                });
              } else {
                var geoJsonLayer = L.geoJSON(feature, {
                  style: function () {
                    return {
                      color: highlightColour,
                      weight: 5,
                      fillOpacity: 0.6,
                      fillColor: highlightColour,
                    };
                  },
                });
              }
              that.editLayer.addLayer(geoJsonLayer);
              that.editLayer.addTo(that.map);
            })
            .catch((data) => {
              that.deleteButton.html("Delete Feature");
              that.cancelDeleteButton.hide();
              that.startEditButton.show();
              that.stopEditFeatureSession();
              console.log("Error retrieving feature", data);
            });
        }
      };
      document
        .getElementById(this.mapDivId)
        .addEventListener("gotFeatureInfo", this.handleGotFeatureInfoDelete, {
          once: true,
        });
    } else {
      tinyMCE.triggerSave();
      this.editableWfstLayer()
        .deleteFeature()
        .then((msg) => {})
        .catch((msg) => {
          console.log("Error deleting feature");
        })
        .finally((msg) => {
          this.deleteButton.html("Delete Feature");
          this.cancelDeleteButton.hide();
          //this.startEditButton.show();
          this.stopEditFeatureSession();
          //this.populateLegend();
        });
      delete this.handleGotFeatureInfoDelete;
    }
  }
  /**
   * Tear down the current map.  Handy to use with Vue.js
   */
  destroy() {
    this.mapDiv.attr("title", "");
    this.mapDiv.tooltip("destroy");
    this.map.off();
    this.mapDiv.remove();
    var that = this;
    this.buttonList.forEach(function (i) {
      var curDiv = "#" + i.divId;
      var curClickFunction = i.clickFunction;
      $(document).off("click", curDiv, curClickFunction);
    });
    try {
      this.stopDraw();
      this.stopEditFeatureSession();
    } catch (e) {}
  }
  cancelDeleteButtonClick() {
    //cancel delete button click
    this.mapDiv.attr("title", "");
    this.mapDiv.tooltip("disable");
    this.cancelDeleteButton.hide();
    this.deleteButton.html("Delete Feature");
    this.stopEditFeatureSession();
    //this.populateLegend();
  }
  stopDraw() {
    //stop drawing / editing on map
    if (this.map.pm.globalDragModeEnabled()) {
      this.map.pm.toggleGlobalDragMode();
    }
    if (this.map.pm.globalRemovalEnabled()) {
      this.map.pm.toggleGlobalRemovalMode();
    }
    if (this.map.pm.globalEditEnabled()) {
      this.map.pm.toggleGlobalEditMode();
    }
    this.map.pm.disableDraw("Line");
    this.map.pm.disableDraw("Marker");
    this.map.pm.disableDraw("Polygon");
    $(".leaflet-tooltip").css("top", "");
    $(".leaflet-tooltip").css("left", "");
  }
  stopEditFeatureSession() {
    //cleanup on stop edit feature session
    tinyMCE.remove("textarea");
    this.armDeleteClick = false;
    this.armEditClick = false;
    this.mapDiv.attr("title", "");
    this.mapDiv.tooltip("disable");
    this.editLayer.addTo(this.map);
    this.editLayer.unbindPopup();
    this.editLayer.clearLayers();
    this.editLayer.remove();
    this.map.closePopup();
    this.editableWfstLayer().editWmsLayer.setParams(
      { fake: Date.now() },
      false
    );
    this.editableWfstLayer().editWmsLayer.addTo(this.map);
    this.editableWfstLayer().editWmsLayer.setOpacity(1);
    this.showEditControls();
    this.editFeatureSession = false;
    this.addToFeatureSession = false;
    this.nonEditLayersVisible(true);
  }
}
