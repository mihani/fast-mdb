import { Loader } from "@googlemaps/js-api-loader"
const GOOGLE_STREET_VIEW_API = process.env.GOOGLE_STREET_VIEW_API;

document.addEventListener('DOMContentLoaded', (event) => {
    const loader = new Loader({
        apiKey: GOOGLE_STREET_VIEW_API,
        version: "weekly",
        url: 'https://maps.googleapis.com/maps/api/js',
    });

    let divMapData = document.querySelector('#street-view-map-pano').dataset;

    loader
        .load()
        .then(() => {
            const streetViewService = new google.maps.StreetViewService();
            let panorama = new google.maps.StreetViewPanorama(
                document.getElementById("street-view-map-pano"),
            );
            streetViewService.getPanorama({
                    location: {
                        lat: parseFloat(divMapData.latitude),
                        lng: parseFloat(divMapData.longitude)
                    },
                    radius: 50
                },
                (data, status) => {
                    if (status === 'OK'){
                        const location = data.location;
                        panorama.setPano(location.pano);
                        panorama.setPov({ heading: 0, pitch: 0 });
                    } else {
                        panorama.setVisible(false);
                    }
                }
            );
        });
});
