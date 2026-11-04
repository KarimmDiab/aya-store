$(function() {
	'use strict';
	var mapboxPublicToken = window.ayaStoreMapboxPublicToken || '';
	var tileUrl = mapboxPublicToken
		? 'https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=' + encodeURIComponent(mapboxPublicToken)
		: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
	var tileLayerOptions = {
		maxZoom: 18,
		attribution: mapboxPublicToken
			? 'Map data &copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors, ' + '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' + 'Imagery &copy; <a href="https://mapbox.com">Mapbox</a>'
			: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
	};
	if (mapboxPublicToken) {
		tileLayerOptions.id = 'mapbox.streets';
	}
	function addBaseLayer(map) {
		L.tileLayer(tileUrl, tileLayerOptions).addTo(map);
	}

	// Leaflet map
	var mymap = L.map('leaflet1').setView([51.505, -0.09], 13);
	addBaseLayer(mymap);

	// Adding a Popup
	var mymap2 = L.map('leaflet2').setView([51.505, -0.09], 13);
	addBaseLayer(mymap2);
	L.marker([51.5, -0.09]).addTo(mymap2).bindPopup("<b>Hello world!</b><br />I am a popup.").openPopup();

	// Adding a Circle
	var mymap3 = L.map('leaflet3').setView([51.505, -0.09], 13);
	addBaseLayer(mymap3);
	L.circle([51.508, -0.11], {
		color: 'red',
		fillColor: '#f03',
		fillOpacity: 0.5,
		radius: 500
	}).addTo(mymap3);
});
