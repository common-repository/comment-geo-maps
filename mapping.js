(function(){
$j = jQuery.noConflict();
defaultAddressText = "address or intersection";

/* Hack because using $(document).ready doesn't work with IE and OpenLayers 
 * because our mapping code creates a new namespace when creating the vector 
 * layer, and IE can't create new namespaces before the onload event has fired. 
 * */ 
function runOnLoad(func) { 
  if ($j.browser.msie) { 
    if (typeof(window.onload) == 'function') { 
      var old_init = window.onload; 
      window.onload = function() { 
        old_init(); 
        func(); 
      }; 
    } else { 
      window.onload = func; 
    } 
  } else { // Not IE 
    $j(document).ready(func); 
  } 
} 

runOnLoad(function() {
                     OpenLayers.ImgPath = cgm_root + "/img/custompanzoom/"
                     $j(fullheight_class).css('height', fullheight_px);
                     CGMInitmap();
                     $j("#find-address").click(function() {
                                                 $j("#cgm-error").html('');
                                                 var data = {'location':$j('#cgm_location').val()};
                                                 $j.getJSON('/cgm_geocode',
                                                            data,
                                                            function(data) {
                                                              if(data.status != 200) {
                                                                $j("#cgm-error").html("Sorry we could not find that address.  Please try another one.");
                                                                return;
                                                              }
                                                              $j("#finetunemap").show();
                                                              $j("#cgm-lat").val(data.lat);
                                                              $j("#cgm-lon").val(data.lon);
                                                              CGMRefreshFineMap();
                                                            });
                                                 return false;
                                              });

                     $j(".show-comment-link").click(function() {
                                                      var id = $j(this).attr('id').split('--')[1];
                                                      var layer = map.getLayersByName('Events')[0];
                                                      if (selectedfeature)
                                                        map.getControlsByClass('OpenLayers.Control.SelectFeature')[0].onUnselect(selectedfeature);
                                                      map.getControlsByClass('OpenLayers.Control.SelectFeature')[0].onSelect(commentmarkers[id]);
                                                      if (is_page)
                                                        return false;
                                                    });
                     CGMSetupToggler();
                     $j('input#cgm_location').each(function() {
                                                   if (this.value == "") {
                                                     this.value = defaultAddressText;
                                                   }
                                                 });

                     $j('input#cgm_location').focus(function() {
                                                    if (this.value == defaultAddressText) {
                                                      this.value = "";
                                                    }
                                                  }).blur(function() {
                                                            if (this.value == "") {
                                                              this.value = defaultAddressText;
                                                            }
                                                          });


});



CGMRefreshFineMap = function() {
  var lat = $j("#cgm-lat").val();
  var lon = $j("#cgm-lon").val();

  function initMap() {
    var bounds = new OpenLayers.Bounds(
        -2.003750834E7,-2.003750834E7,
      2.003750834E7,2.003750834E7
    );
    var options = {
      projection: new OpenLayers.Projection('EPSG:900913'),
      maxExtent: bounds
    };
    ftmap = new OpenLayers.Map('finetunemap', options);

    var navControl = ftmap.getControlsByClass('OpenLayers.Control.Navigation')[0];
    if (navControl) navControl.disableZoomWheel();

    var streetLayer = new OpenLayers.Layer.Google('Google Streets', {sphericalMercator: true, type: G_PHYSICAL_MAP, minZoomLevel:8 , maxZoomLevel: 15});
    //var streetLayer = new OpenLayers.Layer.CloudMade("CloudMade", {
    //                                               key: cgm_map_api_key,
    //                                               styleId: 998
    //                                             });
    ftmap.addLayer(streetLayer);
    var layerOptions = {
      projection: '900913',
      styleMap: new OpenLayers.StyleMap({
                                          externalGraphic: cgm_openlayers_path + "/img/marker.png",
                                          graphicYOffset: -18,
                                          pointRadius: 10,
                                          backgroundGraphic: cgm_root + "/img/marker_shadow.png",
                                          backgroundXOffset: 0,
                                          backgroundYOffset: -15,
                                          graphicZIndex: 11,
                                          backgroundGraphicZIndex: 10
                                        }),
      rendererOptions: {yOrdering: true}
    };
    ftmarkerLayer = new OpenLayers.Layer.Vector("Events", layerOptions);
    ftmap.addLayer(ftmarkerLayer);

  }

  function updateMap() {
    ftmarkerLayer.removeFeatures(ftmarkerLayer.features); // TODO: maybe just move marker?
  }

  if (typeof ftmap == 'undefined')
    initMap();
  else
    updateMap();

  var lonlat = new OpenLayers.LonLat(lon, lat);
  var marker = CGMCreateMarker(lonlat);
  ftmarkerLayer.addFeatures([marker]);

  var dropHandler = function(marker, pixel) {
    var lonlat2 = marker.geometry.getBounds().getCenterLonLat();
    lonlat2.transform(new OpenLayers.Projection('EPSG:900913'), new OpenLayers.Projection('EPSG:4326'));
    $j('#cgm-lat').val(lonlat2.lat);
    $j('#cgm-lon').val(lonlat2.lon);
  };
  var dragFeature = new OpenLayers.Control.DragFeature(ftmarkerLayer, {onComplete: dropHandler});
  ftmap.addControl(dragFeature);
  dragFeature.activate();

  lonlat.transform(new OpenLayers.Projection('EPSG:4326'), ftmap.getProjectionObject());
  ftmap.setCenter(lonlat, 7);

  var popup = new OpenLayers.Popup.FramedCloud(null, marker.geometry.getBounds().getCenterLonLat(),
                                                 null, 'Click and drag the marker<br/>to adjust the location',
                                                 {size: new OpenLayers.Size(1, 1), offset: new OpenLayers.Pixel(1,-1)},
                                                 true, function() { selectControl.unselect(marker); });
  marker.popup = popup
  ftmap.addPopup(popup);

  function onFeatureUnselect(feature) {
    if (!feature.popup)
      return;
    ftmap.removePopup(feature.popup);
    feature.popup.destroy();
    feature.popup = null;
  }

  var selectControl = new OpenLayers.Control.SelectFeature(ftmarkerLayer,
    {onSelect: onFeatureUnselect, onUnselect: onFeatureUnselect});
  ftmap.addControl(selectControl);
  selectControl.activate();
  setTimeout(function() {onFeatureUnselect(marker);}, 4000);
}



CGMInitmap = function() {
  var bounds = new OpenLayers.Bounds(
      -2.003750834E7,-2.003750834E7,
    2.003750834E7,2.003750834E7
  );
  var options = {
    projection: new OpenLayers.Projection('EPSG:900913'),
    maxExtent: bounds
  };
  if(is_page) 
    options['controls'] = [
      new OpenLayers.Control.Navigation(),
      new OpenLayers.Control.PanZoomBar({zoomStopHeight: 5}),
      new OpenLayers.Control.ScaleLine(),
      new OpenLayers.Control.Attribution()
    ];

  map = new OpenLayers.Map('commentsmap', options);
  var navControl = map.getControlsByClass('OpenLayers.Control.Navigation')[0];
  if (navControl) navControl.disableZoomWheel();

  var streetLayer = new OpenLayers.Layer.Google('Google Streets', {sphericalMercator: true, type: G_PHYSICAL_MAP, minZoomLevel:8 , maxZoomLevel: 15});
  //var streetLayer = new OpenLayers.Layer.CloudMade("CloudMade", {
  //                                                 key: cgm_map_api_key,
  //                                                 styleId: 998
  //                                               });
  map.addLayer(streetLayer);

  var layerOptions = {
    projection: '900913',
    styleMap: new OpenLayers.StyleMap({
                                        externalGraphic: cgm_root + "/img/marker.png",
                                        graphicYOffset: -18,
					graphicXOffset: -16,
                                        pointRadius: 10,
                                        backgroundGraphic: cgm_root + "/img/marker_shadow.png",
                                        backgroundXOffset: -1,
                                        backgroundYOffset: -15,
                                        graphicZIndex: 11,
                                        backgroundGraphicZIndex: 10
                                      }),
    rendererOptions: {yOrdering: true}
  };
  var eventsLayer = new OpenLayers.Layer.Vector("Events", layerOptions);
  map.addLayer(eventsLayer);

  commentmarkers = [];
  selectedfeature = [];
  for (var i=0; i<points.length; i++) {
    var lonlat = new OpenLayers.LonLat(points[i][1], points[i][2]);
    var marker = CGMCreateMarker(lonlat);
    marker.attributes.description = points[i][3];
    eventsLayer.addFeatures([marker]);
    commentmarkers[points[i][4]] = marker;
  }

  function onSelectFeature(feature) {
    if (selectedfeature.popup) 
      onFeatureUnselect(selectedfeature);

    selectedfeature = feature;
    if (feature.popup) {
      map.addPopup(feature.popup);
      return;
    }
    
    var popup = new OpenLayers.Popup.FramedCloud(null, feature.geometry.getBounds().getCenterLonLat(),
                                                 null, feature.attributes.description,
                                                 {size: new OpenLayers.Size(1, 1), offset: new OpenLayers.Pixel(1,-15)},
                                                 true, function() { selectControl.unselect(feature); });
    feature.popup = popup
    map.addPopup(popup);
  }

  function onFeatureUnselect(feature) {
    if (!feature.popup)
      return;
    map.removePopup(feature.popup);
    feature.popup.destroy();
    feature.popup = null;
  }

  var selectControl = new OpenLayers.Control.SelectFeature(eventsLayer,
    {onSelect: onSelectFeature, onUnselect: onFeatureUnselect});
  map.addControl(selectControl);
  selectControl.activate();

  if (!points.length){
    var center = new OpenLayers.LonLat(-73.955607, 40.738067);
    center.transform(new OpenLayers.Projection('EPSG:4326'), map.getProjectionObject());
    map.setCenter(center, 4);
  } else {
    if (points.length == 1) {
      var lonlat = new OpenLayers.LonLat(points[0][1], points[0][2]);
      lonlat.transform(new OpenLayers.Projection('EPSG:4326'), map.getProjectionObject());
      map.setCenter(lonlat, 5);
    } else {
      map.zoomToExtent(eventsLayer.getDataExtent());
    }
    var bookmarkid = window.location.hash.replace(/^#comment-/, '');
    var newmarker = commentmarkers[bookmarkid]
    if(newmarker){
      onSelectFeature(newmarker);
    }
  }

};

CGMCreateMarker = function(lonlat) {
  var lonlatClone = lonlat.clone().transform(new OpenLayers.Projection('EPSG:4326'), new OpenLayers.Projection('EPSG:900913'));
  var marker = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Point(lonlatClone.lon, lonlatClone.lat));
  return marker;
};
 })();