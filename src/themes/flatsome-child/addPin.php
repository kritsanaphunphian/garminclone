
<html>
<head>
    <title>Add Graphic to a Map Sample</title>
    <style type="text/css">
        body{overflow:hidden;font-family:sans-serif}
        .page,.map{position:absolute;left:0;top:0;right:0;bottom:0}
        .active{background-color:#FFD300!important;color:#000!important}
        .btnAdd:hover{color:#FFD300}
        .btnAdd{background-image:url(https://developer.nostramap.com/developer/V2/images/pin-trans.png);width:90px;background-color:#222;background-position:0 0;border:none;color:#FFF;padding:5px 9px 6px;cursor:pointer;background-image:linear-gradient(rgba(255,255,255,0.7) 0%,rgba(255,255,255,0) 100%)}
        #show{right:20px;top:20px;background-color:#FFF;border:1px solid #2D2F37;border-radius:3px;padding:15px;position:fixed;width:385px;vertical-align:middle;font-size:14px}
        #labelPanel{border:solid 1px #FFD300;padding:5px}
        .lblRow{margin-left:5px;margin-top:3px}
        .loadingWidget{position:absolute;width:100%;height:100%;background:#fff url(https://developer.nostramap.com/developer/V2/images/loader.gif) no-repeat fixed center center;filter:alpha(opacity=60);opacity:.6;z-index:10000;vertical-align:middle;top:0;left:0}
    </style>

    <script type="text/javascript" src="http://api.nostramap.com/nostraapi/v2.0?key=GBzFI)WzK3jAtLvjXXribxhFt(mf8e2hTCkwstM7rZY86tTd7eUt992Rs00W7Crw0sYHEry8OZOkhjjnpJFFpX0=====2"></script>

    <script type="text/javascript">
        var initExtent, map, point, lat, lon;
        var pointLayer, mp;
        var points = [];
        var lstLabel = [];
        var isFirstLoad = true;
        var dealers = { "features": [
        { "attributes": { "OBJECTID": 13, "ID": 184, "Type": "shop", "Name": "Minburi", "Lat": 13.82713, "Long": 100.73889 } }, 
        { "attributes": { "OBJECTID": 14, "ID": 169, "Type": "shop", "Name": "Bangkhen", "Lat": 13.881943, "Long": 100.600615 } }, 
        { "attributes": { "OBJECTID": 17, "ID": 166, "Type": "shop", "Name": "Chokchai 4", "Lat": 13.797753, "Long": 100.595355 } }, 
        { "attributes": { "OBJECTID": 18, "ID": 146, "Type": "shop", "Name": "Nongjok", "Lat": 13.8553836, "Long": 100.8606515 } }, 
        { "attributes": { "OBJECTID": 20, "ID": 144, "Type": "shop", "Name": "Pattanakarn", "Lat": 13.731823917938, "Long": 100.65054237843 } }, 
        { "attributes": { "OBJECTID": 21, "ID": 143, "Type": "shop", "Name": "Bangsaothong", "Lat": 13.58524, "Long": 100.79413 } }, 
        { "attributes": { "OBJECTID": 57, "ID": 92, "Type": "locker", "Name": "Ideo Blucove Sukhumvit", "Lat": 13.678537, "Long": 100.60862 } }, 
        { "attributes": { "OBJECTID": 58, "ID": 91, "Type": "locker", "Name": "Aequa Sukhumvit 49", "Lat": 13.738094, "Long": 100.578841 } }, 
        { "attributes": { "OBJECTID": 59, "ID": 90, "Type": "locker", "Name": "H Condo Sukhumvit 43", "Lat": 13.730169, "Long": 100.573049 } }, 
        { "attributes": { "OBJECTID": 61, "ID": 88, "Type": "locker", "Name": "M Ladprao", "Lat": 13.816456, "Long": 100.5621 } }, 
        { "attributes": { "OBJECTID": 66, "ID": 83, "Type": "locker", "Name": "Ideo Mobi Sathorn", "Lat": 13.75588, "Long": 100.565806 } }, 
        { "attributes": { "OBJECTID": 68, "ID": 81, "Type": "locker", "Name": "Q House Sukhumvit 79", "Lat": 13.707544, "Long": 100.600808 } }, 
        { "attributes": { "OBJECTID": 69, "ID": 80, "Type": "locker", "Name": "Ivy Ampio Ratchadapisek", "Lat": 13.773506, "Long": 100.573051 } }, 
        { "attributes": { "OBJECTID": 73, "ID": 76, "Type": "locker", "Name": "Noble Remix Thonglor", "Lat": 13.724481, "Long": 100.577253 } }, 
        { "attributes": { "OBJECTID": 76, "ID": 73, "Type": "locker", "Name": "FamilyMart Sukhumvit 20", "Lat": 13.732026, "Long": 100.56396 } }, 
        { "attributes": { "OBJECTID": 77, "ID": 72, "Type": "locker", "Name": "Aspire Sukhumvit 48", "Lat": 13.711345, "Long": 100.594189 } }, 
        { "attributes": { "OBJECTID": 78, "ID": 71, "Type": "locker", "Name": "Onyx Saphankwai", "Lat": 13.791424, "Long": 100.548524 } }, 
        { "attributes": { "OBJECTID": 80, "ID": 69, "Type": "locker", "Name": "Aspire Rama 9", "Lat": 13.754555, "Long": 100.566537 } }, 
        { "attributes": { "OBJECTID": 81, "ID": 68, "Type": "locker", "Name": "Ideo Mobi Rama 9", "Lat": 13.755868, "Long": 100.5658 } }, 
        { "attributes": { "OBJECTID": 82, "ID": 67, "Type": "locker", "Name": "Life Ratchadapisek 14", "Lat": 13.783032, "Long": 100.574586 } }, 
        { "attributes": { "OBJECTID": 85, "ID": 64, "Type": "locker", "Name": "Noble Refine Sukhumvit 26", "Lat": 13.728322, "Long": 100.570703 } },
        { "attributes": { "OBJECTID": 86, "ID": 63, "Type": "locker", "Name": "Noble Reveal Sukhumvit 63 (Ekkamai)", "Lat": 13.72103, "Long": 100.58462 } }, 
        { "attributes": { "OBJECTID": 87, "ID": 62, "Type": "locker", "Name": "Verve Sukhumvit 81 (Onnuch)", "Lat": 13.706424, "Long": 100.601754 } }, 
        { "attributes": { "OBJECTID": 88, "ID": 61, "Type": "locker", "Name": "The Issara Ladprao 12", "Lat": 13.80897, "Long": 100.567278 } }, 
        { "attributes": { "OBJECTID": 89, "ID": 60, "Type": "locker", "Name": "Ivy Sukhumvit 55 (Thonglor)", "Lat": 13.737176, "Long": 100.583553 } }, 
        { "attributes": { "OBJECTID": 90, "ID": 59, "Type": "locker", "Name": "Aguston Sukhumvit 22", "Lat": 13.724823, "Long": 100.564857 } }, 
        { "attributes": { "OBJECTID": 91, "ID": 58, "Type": "locker", "Name": "Ashton Sukhumvit 38", "Lat": 13.721033, "Long": 100.57922 } },
        { "attributes": { "OBJECTID": 92, "ID": 57, "Type": "locker", "Name": "Rhythm Sukhumvit 50", "Lat": 13.706746, "Long": 100.599578 } }, 
        { "attributes": { "OBJECTID": 93, "ID": 56, "Type": "locker", "Name": "Ideo Ladprao 5", "Lat": 13.807202, "Long": 100.572622 } }, 
        { "attributes": { "OBJECTID": 94, "ID": 55, "Type": "locker", "Name": "Ideo Mix Sukhumvit 103 (Udomsuk)", "Lat": 13.678852, "Long": 100.611272 } }, 
        { "attributes": { "OBJECTID": 96, "ID": 53, "Type": "shop", "Name": "Bangna", "Lat": 13.66923, "Long": 100.62913 } }, 
        { "attributes": { "OBJECTID": 100, "ID": 49, "Type": "shop", "Name": "Bang Pli", "Lat": 13.63685, "Long": 100.61025 } }, 
        { "attributes": { "OBJECTID": 101, "ID": 48, "Type": "shop", "Name": "Teparak", "Lat": 13.63685, "Long": 100.61025 } }, 
        { "attributes": { "OBJECTID": 102, "ID": 47, "Type": "shop", "Name": "Samut Prakarn", "Lat": 13.61185, "Long": 100.61832 } }, 
        { "attributes": { "OBJECTID": 104, "ID": 45, "Type": "shop", "Name": "Nawamin", "Lat": 13.82532, "Long": 100.65635 } }, 
        { "attributes": { "OBJECTID": 107, "ID": 42, "Type": "shop", "Name": "Ladkrabang", "Lat": 13.750783, "Long": 100.79408 } }, 
        { "attributes": { "OBJECTID": 108, "ID": 41, "Type": "shop", "Name": "Onnut", "Lat": 13.71124, "Long": 100.61077 } }, 
        { "attributes": { "OBJECTID": 110, "ID": 39, "Type": "shop", "Name": "Donmaung", "Lat": 13.92648, "Long": 100.59013 } }, 
        { "attributes": { "OBJECTID": 114, "ID": 35, "Type": "shop", "Name": "Saimai", "Lat": 13.921278, "Long": 100.684101 } }, 
        { "attributes": { "OBJECTID": 116, "ID": 33, "Type": "shop", "Name": "Happy Land", "Lat": 13.769284, "Long": 100.641658 } }, 
        { "attributes": { "OBJECTID": 119, "ID": 30, "Type": "shop", "Name": "Seacon Square", "Lat": 13.693535, "Long": 100.647867 } }, 
        { "attributes": { "OBJECTID": 122, "ID": 27, "Type": "shop", "Name": "MaungThong", "Lat": 13.913778, "Long": 100.551986 } }, 
        { "attributes": { "OBJECTID": 123, "ID": 26, "Type": "shop", "Name": "Kingkaew", "Lat": 13.649068, "Long": 100.715703 } }, 
        { "attributes": { "OBJECTID": 125, "ID": 24, "Type": "locker", "Name": "WashBox24KrungThonBuriKiosk1", "Lat": 13.81101, "Long": 100.564661 } }, 
        { "attributes": { "OBJECTID": 126, "ID": 23, "Type": "locker", "Name": "WashBox24KrungThonBuriKiosk2", "Lat": 13.811349, "Long": 100.564989 } }, 
        { "attributes": { "OBJECTID": 128, "ID": 21, "Type": "shop", "Name": "Ladprao", "Lat": 13.805358, "Long": 100.575501 } }, 
        { "attributes": { "OBJECTID": 132, "ID": 17, "Type": "bts", "Name": "BTSOnNutRabbitServicePoint", "Lat": 13.705894, "Long": 100.601077 } }, 
        { "attributes": { "OBJECTID": 139, "ID": 7, "Type": "bts", "Name": "BTSMoChitRabbitServicePoint", "Lat": 13.802721, "Long": 100.553745 } }, 
        { "attributes": { "OBJECTID": 140, "ID": 6, "Type": "shop", "Name": "Asok", "Lat": 13.745665, "Long": 100.562426 } }
        ] }

        
        nostra.onready = function () {
            initialize();
        };

        function initialize() {
            map = new nostra.maps.Map("map", {
                id: "mapTest",
                logo: true,
                scalebar: true,
                basemap: "streetmap",
                slider: true,
                level: 12,
                lat: 13.722944,
                lon: 100.530449
            });

            pointLayer = new nostra.maps.layers.GraphicsLayer(map, { id: "pointLayer", mouseOver: false });
            map.addLayer(pointLayer);

            map.events.load = function () {
                console.log("map loaded");
                isFirstLoad = false;
                map.disableDoubleClickZoom();
                hideLoading();
                plotLocationDataOnMap(dealers);
                addClusters(dealers);
            };
            map.events.layerAddComplete = function () {
                console.log("layer added");
                if (!isFirstLoad) {
                    hideLoading();
                }
            };
            map.events.click = function (evt) {
                lat = evt.mapPoint.getLatitude();
                lon = evt.mapPoint.getLongitude();

                var nostraCallout = new nostra.maps.Callout({ title: "Test", content: "POI_NAME: Test <br/>ROAD:  Test" });

                var nostraLabel = new nostra.maps.symbols.Label({
                    text: document.getElementById("lblText").value,
                    size: document.getElementById("lblSize").value,
                    position: document.getElementById("lblPos").value,
                    color: document.getElementById("lblColor").value,
                    haloColor: document.getElementById("lblHaloColor").value,
                    haloSize: document.getElementById("lblHaloSize").value,
                    xoffset: document.getElementById("lblX").value,
                    yoffset: document.getElementById("lblY").value
                });

                lstLabel.push(nostraLabel);

                var pointMarker = new nostra.maps.symbols.Marker({ "url": "", "width": 60, "height": 60, "attributes": { POI_NAME: "TestAttr", POI_ROAD: "TestAttr" }, callout: nostraCallout, label: nostraLabel });
                pointLayer.addMarker(lat, lon, pointMarker);

                if (document.getElementById("rdoHideLabel").checked) {
                    setLavelVisible(false);
                }
            };
            map.events.doubleClick = function (evt) {
                lat = evt.mapPoint.getLatitude();
                lon = evt.mapPoint.getLongitude();

                var circle = new nostra.maps.symbols.Circle({ radius: 3, color: "#FF0000", outline: "#FF0000", transparent: 1 });
                pointLayer.addCircle(lat, lon, circle);
                points.push([lat, lon]);
                points = [];
            };
            document.getElementById("rdoClick").onclick = function () {
                pointLayer.setMouseOver(false); 
            };
            document.getElementById("rdoOver").onclick = function () {
                pointLayer.setMouseOver(true);
            };
            document.getElementById("rdoShowLabel").onclick = function () {
                setLavelVisible(true);
            };
            document.getElementById("rdoHideLabel").onclick = function () {
                setLavelVisible(false);
            };
            document.getElementById("rdoShow").onclick = function () {
                pointLayer.show();
            };
            document.getElementById("rdoHide").onclick = function () {
                pointLayer.hide();
            };
        }
        function setLavelVisible(visible) {
            if (visible) {
                for (var i = 0; i < lstLabel.length; i++) {
                    pointLayer.showLabel(lstLabel[i]);
                }
            } else {
                for (var i = 0; i < lstLabel.length; i++) {
                    pointLayer.hideLabel(lstLabel[i]);
                }
            }
        }
        function addGraphic() {
            document.getElementById("btnPoint").className = "btnAdd";
            document.getElementById("lblY").value = "0";
            document.getElementById("labelPanel").style.display = "block";
            document.getElementById("btnPoint").className += ' active';
            document.getElementById("txtLabel").innerHTML = "Please click on the map to add point";
            document.getElementById("divPosition").style.display = "inline-block";
        }
        function clearGraphic() {
            pointLayer.clear();
            points = [];
        }
        function showLoading() {
            document.getElementById("dlgLoading").style.display = "block";
        }
        function hideLoading() {
            document.getElementById("dlgLoading").style.display = "none";
        }

        function plotLocationDataOnMap(dealers) {
            if (dealers.features.length > 0) {
                console.log(dealers.features.length);
                for (var i = 0; i < dealers.features.length; i++) {
                    var pinTitle = dealers.features[i].attributes.Name;
                    var pinContent = "ชนิด: " + dealers.features[i].attributes.Type + "<br/>Lat,Long: " + dealers.features[i].attributes.Lat + ", " + dealers.features[i].attributes.Long

                    var nostraCallout = new nostra.maps.Callout({ title: pinTitle, content: pinContent });
                    var pointMarker = new nostra.maps.symbols.Marker({
                        "url": "",//override image icon
                        "width": 48,
                        "height": 48,
                        "attributes": {
                            "POI_NAME": "TestAttr",
                            "POI_ROAD": "TestAttr"
                        },
                        "callout": nostraCallout,
                        "label": "" //nostraLabel
                    });
                    pointLayer.addMarker(dealers.features[i].attributes.Lat, dealers.features[i].attributes.Long, pointMarker);
                }
            }
        }
    </script>
</head>
<body class="tundra customClass">
    <div id="dlgLoading" class="loadingWidget">
    </div>
    <div id="map">
    </div>
    <div id="show">
        <div style="margin-bottom: 5px;">
            <div id="rdoPanel" style="display: inline-block">
                <label>Display Callout: </label>
                <input id="rdoClick" style="margin: 5px;" type="radio" name="type" value="click" checked>Click</input>
                <input id="rdoOver" style="margin: 5px;" type="radio" name="type" value="over">Mouse Hover</input>
            </div>
        </div>
        <div>
            <button id="btnPoint" class="btnAdd active" onclick="addGraphic();">
                AddPoint
            </button>
            <div id="labelPanel">
                <div>
                    <label>Display Label: </label>
                    <input id="rdoShowLabel" style="margin: 5px;" type="radio" name="showLabel" value="show" checked>Show</input>
                    <input id="rdoHideLabel" style="margin: 5px;" type="radio" name="showLabel" value="hide">Hide</input>
                </div>
                <div class="lblRow">
                    <div style="width: 50px; display: inline-block;">Text</div>
                    <input id="lblText" type="text" style="width: 177px;" value="testLabel" />
                    <div style="margin-left: 30px;width: 25px;display: inline-block;">Size</div>
                    <input id="lblSize" type="text" style="width: 50px;" value="10" />
                </div>
                <div class="lblRow">
                    <div style="width: 50px; display: inline-block;">Color</div>
                    <input id="lblColor" type="text" style="width: 50px;" value="#353535" />
                    <div style="margin-left: 5px; width: 60px; display: inline-block; ">HaloColor</div>
                    <input id="lblHaloColor" type="text" style="width: 50px;" value="#ffffff" />
                    <div style="margin-left: 3px; width: 52px; display: inline-block; ">HaloSize</div>
                    <input id="lblHaloSize" type="text" style="width: 50px;" value="1" />
                </div>
                <div class="lblRow">
                    <div id="divPosition" style="display:inline-block">
                        <div style="width:50px; display:inline-block;">Position</div>
                        <select id="lblPos" style="width: 70px;">
                            <option value="top">Top</option>
                            <option value="bottom">Bottom</option>
                        </select>
                    </div>
                    <div style="margin-left: 2px; width: 50px; display: inline-block; ">X-offset</div>
                    <input id="lblX" type="text" style="width: 47px;" value="0" />
                    <div style="margin-left: 5px; width: 50px; display: inline-block; ">Y-offset</div>
                    <input id="lblY" type="text" style="width: 50px;" value="0" />
                </div>
            </div>
        </div>
        <div style="margin-top:10px;">
            <button id="btnClear" style="width:130px"  class="btnAdd" onclick="clearGraphic();">
                Clear Graphic
            </button>
            <div id="rdoDisplay" style="display: inline-block">
                <label>Display Graphic: </label>
                <input id="rdoShow" style="margin: 5px;" type="radio" name="graphic" value="show" checked>Show</input>
                <input id="rdoHide" style="margin: 5px;" type="radio" name="graphic" value="hide">Hide</input>
            </div>
        </div>
        <div style="margin-top:10px;" id="txtLabel">
            Please click on the map to add point
        </div>
    </div>
</body>
</html>
