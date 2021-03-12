class EditMap {
  //Front-end editing environment for RegionalRoads.com
  //Enables simple, intuitive editing of geographic features
  constructor(appToken, divId, options, sid) {
    this.appToken = appToken;
    this.appToken.check().then((data) => {
      $("#" + divId).append('<div id="mapId"></div>');
      this.mapDivId = "mapId";
      this.sid = sid;
      this.map = new L.Map(this.mapDivId, options.mapOptions);
      this.wfstLayers = [];
      this.addWfstLayers(options.wfstLayers);
      var featureGrouping = this.buildFeatureGrouping(options.featureGrouping);
      this.setFeatureGrouping(featureGrouping);
      this.addToFeatureSession = false;
      this.editSession = false;
      this.basemaps; //array of leaflet Basemaps
      this.currentBaseMap;
      this.writing = false;
      this.lastKeyPressed;
      this.map.on("baselayerchange", function (e) {
        this.currentBaseMap = e.layer;
      });
      ///dynamically add divs to controlContainer
      this.divList = [
        { property: "mapDiv", divId: this.mapDivId },
        { property: "editToolbar", divId: "editToolbar" },
        { property: "editModal", divId: "editModal" },
        { property: "commentModal", divId: "commentModal" },
        { property: "exportModal", divId: "exportModal" },
      ]; //divs required for functioning of EditMap
      this.buttonList = [
        { property: "addButton", divId: "addButton" },
        { property: "addAttributesButton", divId: "addAttributesButton" },
        { property: "cancelAddButton", divId: "cancelAddButton" },
        { property: "editButton", divId: "editButton" },
        { property: "deleteButton", divId: "deleteButton" },
        { property: "editAttributesButton", divId: "editAttributesButton" },
        { property: "confirmEditLayerButton", divId: "confirmEditLayerButton" },
        { property: "addToFeatureButton", divId: "addToFeatureButton" },
        { property: "cancelEditButton", divId: "cancelEditButton" },
        { property: "cancelDeleteButton", divId: "cancelDeleteButton" },
        { property: "closeEditModalButton", divId: "closeEditModalButton" },
        { property: "startEditButton", divId: "startEditButton" },
        { property: "cancelEditLayerButton", divId: "cancelEditLayerButton" },
        { property: "commentReplyButton", divClass: "commentReplyButton" },
        { property: "commentDeleteButton", divClass: "commentDeleteButton" },
        { property: "commentEditButton", divClass: "commentEditButton" },
        { property: "commentAddButton", divId: "commentAddButton" },
        { property: "commentSubmitButton", divId: "commentSubmitButton" },
        {
          property: "closeCommentModalButton",
          divId: "closeCommentModalButton",
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
        { property: "closeExportModalButton", divId: "closeExportModalButton" },
        { property: "exportLinkButton", divClass: "exportLinkButton" },
      ]; //buttons required for functioning of EditMap
      this.populateDivs = function (divList) {
        var that = this;
        divList.forEach(function (i) {
          that.setDiv(i["property"], i["divId"]);
        });
      };
      this.populateDivs(this.divList); //set up all divs in divList as EditMap properties
      this.populateButtons = function (buttonList) {
        var that = this;
        buttonList.forEach(function (j) {
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
          $(document).on(
            "click",
            buttonDivName,
            that[buttonClickName].bind(that)
          );
        });
      };
      this.populateButtons(this.buttonList); //set up all button in buttonList as EditMap properties
      if (options.editable) {
        this.editToolbar.show(); //show edit toolbar if map is editable
      }
      this.addFeatureSession = false; //not in add feature session to start
      var that = this;
      this.editFeatureSession = false; //not editing to start
      this.editLayer = L.featureGroup(); //empty Leaflet feature group
      this.armEditClick = false; //do not arm edit click to start
      this.armDeleteClick = false; //do not arm delete click to start
      this.baseAPIURL = options.baseAPIURL;
      this.tinyMCEOptions = {
        selector: "textarea",
        plugins: "link",
        toolbar:
          "bold italic underline strikethrough | insertfile image media template link anchor codesample | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | numlist bullist | removeformat | pagebreak | charmap emoticons | ltr rtl",
        menubar: false,
        branding: false,
        statusbar: false,
        extended_valid_elements: "span",
        custom_elements: "span",
        init_instance_callback: function (editor) {
          //editor.on('Load', that.detectTagging());
        },
      };
      this.map.on("pm:create", this.pmCreate.bind(this));
      this.map.on("popupopen", this.tinyMceInit.bind(this));
      document.addEventListener("getFeatureInfo", this.displayPopup.bind(this)); //listen for getFeatureInfo event, then open popup
      //document.addEventListener('commentIframeOpen', this.detectTagging.bind(this));
      $(document).tooltip({
        //change document tooltip
        //$(this.mapDiv).tooltip({//change document tooltip
        track: true,
        position: {
          my: "center bottom+50",
        },
      });
      this.populateLayerControl();
      this.populateLegend();
    });
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
          //if (curKey=="@" && (that.lastKeyPressed == undefined || that.lastKeyPressed == " ")) {
          //	console.log("TAG DETECTED!");
          //}
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
    for (let key in wfstLayers) {
      var wfstLayer = new WfstLayer(
        wfstLayers[key].name,
        this.appToken,
        wfstLayers[key].baseAPIURL
      );
      var wmsLayer = L.tileLayer.betterWms(
        wfstLayers[key].wmsLayer.url,
        wfstLayers[key].wmsLayer.options,
        this.appToken
      );
      wfstLayer.wmsLayer = wmsLayer;
      var editWmsLayer = L.tileLayer.betterWms(
        wfstLayers[key].editWmsLayer.url,
        wfstLayers[key].editWmsLayer.options,
        this.appToken
      );
      wfstLayer.editWmsLayer = editWmsLayer;
      wfstLayer.layerName = wfstLayers[key].layerName;
      wfstLayer.displayName = wfstLayers[key].displayName;
      wfstLayer.options = wfstLayers[key].options;
      this.wfstLayers.push(wfstLayer);
      if (wfstLayer.options.visible) {
        wfstLayer.editWmsLayer.addTo(this.map);
      }
    }
  }
  buildFeatureGrouping(featureGrouping) {
    var that = this;
    featureGrouping.forEach(function (i) {
      for (var j = 0; j < i.wfstLayers.length; j++) {
        i.wfstLayers[j] = that.getWfstLayerFromName(
          i.wfstLayers[j],
          "wfstLayerName"
        );
      }
    });
    return featureGrouping;
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
    //show popup
    if (e.err) {
      console.log(e.err);
      return;
    } // do nothing if there's an error
    if (e.content.length == 0) {
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
    if (this.armEditClick == true) {
      if (e.this != this.editableWfstLayer().editWmsLayer) {
        //while edit click is armed do not show irrelevant popups
        return;
      } else {
        //while edit click is armed the activeWfstLayer is the editableWfstLayer
        this.activeWfstLayer = this.editableWfstLayer();
      }
    } else {
      //while edit click is not armed the activeWfstLayer is whichever one was clicked
      //change to getWfstLayerFromWmsLayer!!
      //this.activeWfstLayer = this.getWfstLayerFromName(e.this.options.layers);
      this.activeWfstLayer = this.getWfstLayerFromWmsLayer(e.this);
      if (this.activeWfstLayer.options.displayPopup == false) {
        //if a WfstLayer explicity dissalows popups do not show it
        return;
      }
    }
    if (this.editFeatureSession == true) {
      //no getFeatureInfo popups while editing
      return;
    }
    this.activeWfstLayer.setFidField();
    var popupTitleHtml = "<h4>" + this.activeWfstLayer.displayName + "</h4>";
    var curFID = this.activeWfstLayer.getIDFromPopup(e.content);
    this.activeWfstLayer.curId = curFID;
    var evt = document.createEvent("Event");
    evt.initEvent("gotFeatureInfo", true, true);
    this.getPopup(this.activeWfstLayer.wmsLayer, e.latlng)
      .then((msg) => {
        if (msg.length == 0) {
          //cancel popup if there is no content
          L.popup({ maxWidth: 800 })
            .setLatLng(e.latlng)
            .setContent(
              "This popup cannot be displayed as the feature is missing required attributes.  Edit the feature to add all necessary attributes."
            )
            .openOn(e.this._map);
          document.dispatchEvent(evt);
          return;
        }
        msg = popupTitleHtml + msg;
        //console.log(msg);
        msg = this.activeWfstLayer.convertDateTime(msg);
        //parse DateTime
        if (this.activeWfstLayer.options.showComments == true) {
          this.getDataPermissions().then((permissions) => {
            this.getCurrentLayerPermissions(this.activeWfstLayer);
            if (this.currentLayerPermissions.comment) {
              this.activeWfstLayer
                .getComments()
                .then((data) => {
                  var formattedComments = this.sortComments(data);
                  var commentsHTML = this.printComments(formattedComments);
                  msg += commentsHTML;
                })
                .catch((data) => {
                  console.log("Error retrieving comments");
                })
                .finally((data) => {
                  L.popup({ maxWidth: 800 })
                    .setLatLng(e.latlng)
                    .setContent(msg)
                    .openOn(e.this._map);
                  document.dispatchEvent(evt);
                });
            } else {
              L.popup({ maxWidth: 800 })
                .setLatLng(e.latlng)
                .setContent(msg)
                .openOn(e.this._map);
              document.dispatchEvent(evt);
            }
          });
        } else {
          if (
            this.activeWfstLayer.externalPopup == false ||
            this.activeWfstLayer.externalPopup == undefined
          ) {
            L.popup({ maxWidth: 800 })
              .setLatLng(e.latlng)
              .setContent(msg)
              .openOn(e.this._map);
            document.dispatchEvent(evt);
          } else {
            $(this.externalPopupDiv).empty();
            $(this.externalPopupDiv).append(msg);
          }
        }
      })
      .catch((msg) => {
        console.log("Error opening popups");
      });
  }
  getWfstLayerFromWmsLayer(wmsLayer) {
    //var wmsParamsIgnoreList = ["fake"];
    var returnWfstLayer;
    this.wfstLayers.forEach(function (j) {
      let matchCount = 0;
      let totalCount = 0;
      for (const i in wmsLayer.wmsParams) {
        if (j.editWmsLayer.wmsParams[i] == wmsLayer.wmsParams[i]) {
          matchCount += 1;
        }
        totalCount += 1;
      }
      if (matchCount == totalCount) {
        returnWfstLayer = j;
      }
    });
    return returnWfstLayer;
  }
  setFeatureGrouping(featureGrouping) {
    //editMap.featureGrouping setter
    this.featureGrouping = featureGrouping;
    var wfstLayers = [];
    this.featureGrouping.forEach(function (i) {
      i.wfstLayers.forEach(function (j) {
        wfstLayers.push(j);
      });
    });
    //this.wfstLayers = wfstLayers;
  }
  /*generateExportModal() {
		var htmlString = '<button type="button" id="closeExportModalButton"><svg width="24" height="24"><path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path></svg></button>';
		htmlString += '<h4>Export Layers</h4>';
		htmlString += '<div id="layerListContainer">';
		htmlString += '<div id="layerList">';
		var that = this;
		var layerCount = 0;
		var links = [];
		this.featureGrouping.forEach(function (i) {
			var subLayerCount = 0;
			var addString = '<ul>';
			var fileName = i.displayName.replace(/[^A-Z0-9]/ig, "");
			var csvFileName = fileName + ".csv"
			addString += '<li>';
			addString += `<b>${i.displayName}</b>`;
			var csvId = `csvLink${fileName}`;
			addString += `<br><button id = "${csvId}" type="button" class="exportLinkButton" data-filename="${csvFileName}" data-type="csv">Download CSV</button>`;
			var typeNames = "";
			var layerCount = 0;
			i.wfstLayers.forEach(function (j) {
				if (layerCount > 0) {
					typeNames += ",";
				}
				var addString2 = '<ul>';
				if (j.displayName != undefined) {
					addString2 += '<h4>' + j.displayName + '</h4>';
					var zipFileName = `${fileName}.zip`;
					var kmlFileName = `${fileName}.kml`;
					var jsonFileName = `${fileName}.json`;
					addString2 += `<div class="exportLinks">
<button id="${j.displayName}Shapefile"class="exportLinkButton" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.wmsLayer.options.layers}&outputFormat=shape-zip" data-filename="${zipFileName}" data-type="zip">Shapefile</button>
			<button id="${j.displayName}Kml"class="exportLinkButton" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.wmsLayer.options.layers}&outputFormat=application/vnd.google-earth.kml+xml" data-filename="${kmlFileName}" data-type="kml">KML</button>
<button id="${j.displayName}Json"class="exportLinkButton" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.wmsLayer.options.layers}&outputFormat=application/json" data-filename="${jsonFileName}" data-type="json">GeoJson</button>
</div>`;
				}
				else {
					addString2 += `<h4>${j.name}</h4>`;
				}
				typeNames += j.wmsLayer.options.layers;
				addString2 += '</ul>';
				if (that.layerReadable(j.name)) {
					addString += addString2;
					subLayerCount += 1;
				}
				layerCount += 1;
			});
			addString += '</li>';
			addString += '</ul>';
			if (subLayerCount > 0) {
				htmlString += addString;
			}
			var csvIdSelector = '#' + csvId;
			var geoJsonIdSelector = '#' + "geoJsonLink" + i.displayName.replace(/[^A-Z0-9]/ig, "");
			var csvLink = `${that.baseAPIURL}/export/?data=${typeNames}`;
			var geoJsonLink = `${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${typeNames}&outputFormat=application/json`;
			links.push({ "csvIdSelector": csvIdSelector, "geoJsonIdSelector": geoJsonIdSelector, "csvLink": csvLink, "geoJsonLink": geoJsonLink });

		});
		htmlString += '</div>';
		htmlString += '</div>';
		this.exportModal.html(htmlString);
		links.forEach(function (k) {
			$(k['csvIdSelector']).attr("value", k['csvLink']);
		});
	}*/
  generateExportModal() {
    var htmlString =
      '<button type="button" id="closeExportModalButton"><svg width="24" height="24"><path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path></svg></button>';
    htmlString += "<h4>Export Layers</h4>";
    htmlString += '<div id="layerListContainer">';
    htmlString += '<div id="layerList">';
    var that = this;
    var layerCount = 0;
    var links = [];
    this.featureGrouping.forEach(function (i) {
      var subLayerCount = 0;
      var addString = "<ul>";
      var fileName = i.displayName.replace(/[^A-Z0-9]/gi, "");
      var csvFileName = fileName + ".csv";
      addString += "<li>";
      addString += `<b>${i.displayName}</b>`;
      var csvId = `csvLink${fileName}`;
      addString += `<br><button id = "${csvId}" type="button" class="exportLinkButton" data-filename="${csvFileName}" data-type="csv">Download CSV</button>`;
      var typeNames = "";
      var layerCount = 0;
      var masterLinksAdded = false;
      i.wfstLayers.forEach(function (j) {
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
		  <button id="${j.name}Shapefile"class="exportLinkButton" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.editWmsLayer.options.layers}&outputFormat=shape-zip" data-cqlfilter="1=1" data-filename="${zipFileName}" data-type="zip">Shapefile</button>
					  <button id="${j.name}Kml"class="exportLinkButton" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.editWmsLayer.options.layers}&outputFormat=application/vnd.google-earth.kml+xml" data-cqlfilter="1=1"  data-filename="${kmlFileName}" data-type="kml">KML</button>
		  <button id="${j.name}Json"class="exportLinkButton" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.editWmsLayer.options.layers}&outputFormat=application/json" data-filename="${jsonFileName}" data-cqlfilter="1=1" data-type="json">GeoJson</button>
		  </div>`;
        var exportLinksString = `<div class="exportLinks">
<button id="${j.name}Shapefile"class="exportLinkButton" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.editWmsLayer.options.layers}&outputFormat=shape-zip" data-cqlfilter="${cqlFilter}" data-filename="${zipFileName}" data-type="zip">Shapefile</button>
			<button id="${j.name}Kml"class="exportLinkButton" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.editWmsLayer.options.layers}&outputFormat=application/vnd.google-earth.kml+xml" data-cqlfilter="${cqlFilter}"  data-filename="${kmlFileName}" data-type="kml">KML</button>
<button id="${j.name}Json"class="exportLinkButton" type="button" value="${that.baseAPIURL}/simplewfs/?version=1.0.0&request=GetFeature&typeName=${j.editWmsLayer.options.layers}&outputFormat=application/json" data-filename="${jsonFileName}" data-cqlfilter="${cqlFilter}" data-type="json">GeoJson</button>
</div>`;
        typeNames += j.wmsLayer.options.layers;
        if (that.layerReadable(j.name)) {
          if (i.layerGroupOption == "filtered" && masterLinksAdded == false) {
            addString += `${masterLinksString}`;
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
        }

        layerCount += 1;
      });
      addString += "</li>";
      addString += "</ul>";
      //if (subLayerCount > 0) {
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
      '<button type="button" id="closeEditModalButton"><svg width="24" height="24"><path d="M17.3 8.2L13.4 12l3.9 3.8a1 1 0 01-1.5 1.5L12 13.4l-3.8 3.9a1 1 0 01-1.5-1.5l3.9-3.8-3.9-3.8a1 1 0 011.5-1.5l3.8 3.9 3.8-3.9a1 1 0 011.5 1.5z" fill-rule="evenodd"></path></svg></button>';
    htmlString += "<h4>Choose a layer to edit</h4>";
    htmlString += '<div id="layerListContainer">';
    htmlString += '<div id="layerList">';
    var that = this;
    var layerCount = 0;
    this.featureGrouping.forEach(function (i) {
      var subLayerCount = 0;
      var addString = "<ul>";
      addString += "<li>";
      addString += i.displayName;
      i.wfstLayers.forEach(function (j) {
        var addString2 = "<ul>";
        if (j.displayName != undefined) {
          addString2 += `<input type="radio" id="${j.name}EditSelector" name="EditSelector" value="${j.name}" required><label for="${j.name}EditSelector">${j.displayName}</label><br>`;
        } else {
          addString2 += `<input type="radio" id="${j.name}EditSelector" name="EditSelector" value="${j.name}" required><label for="${j.name}EditSelector">${j.name}</label><br>`;
        }
        addString2 += "</ul>";
        if (that.layerEditable(j.name)) {
          addString += addString2;
          subLayerCount += 1;
        }
      });
      addString += "</li>";
      addString += "</ul>";
      if (subLayerCount > 0) {
        htmlString += addString;
      }
    });
    htmlString += "</div>";
    htmlString += "</div>";
    htmlString +=
      '<div id="confirmEditLayerButtonContainer"><button type="button" id="confirmEditLayerButton" class=""><img src="/img/save.png" width="20" height="20" alt="Submit" title="Submit" /></button></div>';
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
  getPopup(wmsLayer, latlng) {
    //get popup of wmsLayer based on latlng
    wmsLayer.addTo(this.map);
    var that = this;
    var url = wmsLayer.getFeatureInfoUrl(latlng);
    return new Promise((resolve, reject) => {
      that.appToken.check().then((msg) => {
        var postData = { token: this.appToken.token };
        var postDataString = JSON.stringify(postData);
        $.ajax({
          type: "POST",
          //contentType: "xml",
          data: postDataString,
          url: url,
          success: function (data, status, xhr) {
            wmsLayer.remove();
            resolve(data);
          },
          error: function (xhr, status, error) {
            wmsLayer.remove();
            reject(false);
          },
        });
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
  closeExportModalButtonClick() {
    this.exportModal.css("display", "none");
  }
  exportButtonClick() {
    this.exportModal.html("");
    this.getDataPermissions().then((data) => {
      this.generateExportModal();
      //change display prop of exportModal obj to block
      //
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
      "https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw",
      {
        maxZoom: 18,
        attribution:
          'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
          '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
          'Imagery ©? <a href="https://www.mapbox.com/">Mapbox</a>',
        id: "mapbox/streets-v11",
        tileSize: 512,
        zoomOffset: -1,
      }
    ).addTo(this.map);
    this.currentBaseMap = mapBaseMap;
    mapBaseMap.bringToBack();
    var layerControl = {};
    var that = this;
    this.featureGrouping.forEach(function (i) {
      i.wfstLayers.forEach(function (j) {
        var wfstLayer = j;
        var layer = wfstLayer.editWmsLayer;
        if (wfstLayer.displayName != undefined) {
          layerControl[wfstLayer.displayName] = layer;
        } else {
          layerControl[wfstLayer.name] = layer;
        }
      });
    });
    var baseMapControl = {
      Imagery: imageBaseMap,
      Map: mapBaseMap,
    };
    this.layerControlObj = L.control.layers(baseMapControl, layerControl, {
      collapsed: false,
      position: "bottomright",
    });
    this.layerControlObj.addTo(this.map);
  }
  populateLegend() {
    //add legend images to layerControl
    for (var j = 0; j < this.wfstLayers.length; j++) {
      if (this.armEditClick == true) {
        var layer = this.wfstLayers[j].wmsLayer;
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
        var aTags = document.getElementsByTagName("span");
        var searchText = displayName;
        var legendImg = `${
          this.baseAPIURL
        }/wms/?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=30&HEIGHT=30&LAYER=${
          layer.wmsParams.layers
        }&token=${this.appToken.token}&${Date.now()}`;
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
              .attr("category", category);
            aTags[i].appendChild(lineBreak);
            aTags[i].appendChild(img);
            break;
          }
        }
      }
    }
  }
  startEditButtonClick() {
    //start edit session
    if (this.editSession == false) {
      this.getDataPermissions()
        .then((data) => {
          this.generateEditModal();
          this.editModal.css("display", "block");
        })
        .catch((data) => {
          console.log("Error getting permissions.");
        });
    } else {
      this.startEditButton.html("Start Editing");
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
      if (i.edit()) {
        i.edit(false);
      }
    });
  }
  editableWfstLayer() {
    //return EditMap's WfstLayer where WfstLayer.edit()==true
    var returnValue;
    this.wfstLayers.forEach(function (i) {
      if (i.edit()) {
        returnValue = i;
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
        this.getWfstLayerFromName(checkedLayer).edit(true);
        var that = this;
        this.getCurrentLayerPermissions();
        this.editModal.css("display", "none");
        this.showEditControls();
        this.startEditButton.html("Stop Editing");
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
      if (i.edit() == false) {
        i.editWmsLayer.setOpacity(opacity);
        if (visible) {
          //turn on popups
          //i.options.displayPopup=true;
          //that.map.on('click', i.editWmsLayer.getFeatureInfo, i.wmsLayer);
        } else {
          //turn off popups
          //that.map.off('click', i.editWmsLayer.getFeatureInfo, i.wmsLayer);
          //i.options.displayPopup=false;
        }
      }
    });
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
    if (this.editFeatureSession == false) {
      this.editFeatureSession = true;
      this.addButton.html("Finish Feature");
      if (
        this.editableWfstLayer().featureType == "gml:MultiPointPropertyType" ||
        this.editableWfstLayer().featureType == "gml:PointPropertyType"
      ) {
        this.map.pm.enableDraw("Marker");
        $(".leaflet-tooltip").css("top", "25px");
        $(".leaflet-tooltip").css("left", "-15px");
      } else if (
        this.editableWfstLayer().featureType == "gml:MultiCurvePropertyType"
      ) {
        this.map.pm.enableDraw("Line");
      } else if (
        this.editableWfstLayer().featureType == "gml:MultiSurfacePropertyType"
      ) {
        this.map.pm.enableDraw("Polygon");
      } else {
        console.log("Error: Unsupported geometry type.");
      }
    } else {
      this.addButton.html("Add Feature");
      this.stopDraw();
      this.addButton.hide();
      this.editFeatureSession = false;
      this.nonEditLayersVisible(true);
      this.editLayer.addTo(this.map);
      if (this.editLayer["pm"]["_layers"].length != 0) {
        var htmlForm = this.editableWfstLayer().getPopupForm();
        var popupContent = htmlForm;
        this.editLayer.bindPopup(popupContent).openPopup();
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
    if (this.addToFeatureSession == false) {
      this.addToFeatureButton.html("Save");
      this.editButton.hide();
      this.addToFeatureSession = true;
      this.editLayer.pm.disable();
      this.editLayer.closePopup();
      this.editLayer.unbindPopup();
      //that.editableWfstLayer().options.displayPopup=false;
      if (
        this.editableWfstLayer().featureType == "gml:MultiPointPropertyType" ||
        this.editableWfstLayer().featureType == "gml:PointPropertyType"
      ) {
        this.map.pm.enableDraw("Marker");
      } else if (
        this.editableWfstLayer().featureType == "gml:MultiCurvePropertyType"
      ) {
        this.map.pm.enableDraw("Line");
      } else if (
        this.editableWfstLayer().featureType == "gml:MultiSurfacePropertyType"
      ) {
        this.map.pm.enableDraw("Polygon");
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
          //this.startEditButton.show();
          //this.editButton.show();
          //this.addButton.show();
          //this.editButton.show();
          //this.deleteButton.show();
          this.cancelEditButton.hide();
          //this.showEditControls();
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
        .then((msg) => {})
        .catch((msg) => {
          console.log("Error adding features");
        })
        .finally((msg) => {
          this.addButton.html("Add Feature");
          //this.startEditButton.show();
          this.cancelAddButton.hide();
          //this.showEditControls();
          this.stopEditFeatureSession();
        });
    }
  }
  cancelAddButtonClick() {
    //cancel add button click
    //this.addFeatureSession=false;
    //this.startEditButton.show();
    this.addButton.html("Add Feature");
    this.cancelAddButton.hide();
    this.stopDraw();
    this.stopEditFeatureSession();
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
      $(document).tooltip("enable");
      this.curEditID = undefined;
      this.nonEditLayersVisible(false);
      var that = this;
      document.addEventListener("gotFeatureInfo", function (e) {
        if (that.armEditClick) {
          that.editFeatureSession = true;
          that.map.closePopup();
          that.addToFeatureButton.show();
          that.mapDiv.attr("title", "");
          $(document).tooltip("disable");
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
                that.editLayer.pm.enable();
                that.editLayer.setStyle({
                  color: "#e4f00a",
                  weight: 5,
                });
                that.editLayer.bindPopup(editPopupContent);
                if (that.map.pm.globalEditEnabled() == false) {
                  that.map.pm.toggleGlobalEditMode();
                }
              }
              that.editLayer.addTo(that.map);
              that.editLayer.pm.enable();
              that.editLayer.setStyle({
                color: "#e4f00a",
                weight: 5,
              });
              that.editLayer.bindPopup(editPopupContent);
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
          that.armEditClick = false;
        }
      });
      this.editButton.html("Save");
    } else {
      //check if all required fields are filled
      tinymce.triggerSave();
      var editFormArray = $("#editAttributesForm").serializeArray();
      this.editableWfstLayer()
        .updateFeature(this.editLayer)
        .then((msg) => {})
        .catch((msg) => {
          console.log("Error editing feature");
        })
        .finally((msg) => {
          this.editButton.html("Edit Feature");
          this.cancelEditButton.hide();
          //this.startEditButton.show();
          this.addToFeatureButton.hide();
          this.stopDraw();
          this.stopEditFeatureSession();
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
    //this.startEditButton.show();
    this.editButton.html("Edit Feature");
    this.addToFeatureButton.html("Add to Feature");
    this.addToFeatureButton.hide();
    this.cancelEditButton.hide();
    this.stopDraw();
    this.stopEditFeatureSession();
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
      $(document).tooltip("enable");
      this.nonEditLayersVisible(false);
      var that = this;
      document.addEventListener("gotFeatureInfo", function (e) {
        if (that.armDeleteClick == true) {
          that.editLayer.unbindPopup();
          that.editFeatureSession = true;
          that.mapDiv.attr("title", "");
          $(document).tooltip("disable");
          that.deleteButton.show();
          that.map.closePopup();
          that.armDeleteClick = false;
          that.editableWfstLayer().curDeleteId = that.editableWfstLayer().curId;
          that.editableWfstLayer().editWmsLayer.setOpacity(0);
          that
            .editableWfstLayer()
            .getWFSFeatureFromId(that.editableWfstLayer().curDeleteId)
            .then((data) => {
              var geoJsonLayer = L.GeoJSON.geometryToLayer(
                data["features"][0]
              ).addTo(that.map);
              that.editLayer.addLayer(geoJsonLayer);
              that.editLayer.addTo(that.map);
              that.editLayer.setStyle({
                color: "#f00a0a",
                weight: 5,
              });
            })
            .catch((data) => {
              that.deleteButton.html("Delete Feature");
              that.cancelDeleteButton.hide();
              that.startEditButton.show();
              that.stopEditFeatureSession();
              console.log("Error retrieving feature");
            });
        }
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
        });
    }
  }
  cancelDeleteButtonClick() {
    //cancel delete button click
    this.mapDiv.attr("title", "");
    $(document).tooltip("disable");
    //this.startEditButton.show();
    this.cancelDeleteButton.hide();
    this.deleteButton.html("Delete Feature");
    this.stopEditFeatureSession();
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
    $(document).tooltip("disable");
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
