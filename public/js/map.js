async function initMap() {
    const {Map} = await google.maps.importLibrary("maps");
    const {AdvancedMarkerElement} = await google.maps.importLibrary("marker");
    const map = new Map(document.getElementById("map"), {
        center: {lat: -26.098144202186038, lng: 28.0477285203848},
        zoom: 10,
        mapId: "4504f8b37365c3d0",
    });

    let markerData = [];

    // Make an API request to get marker data
    fetch('/api/commuters/all')
        .then(response => response.json())
        .then(response => {
            // Assuming the API response is an array of marker objects

            let data = JSON.parse(response.commuters);

            //loop through the data
            for (let i = 0; i < data.length; i++) {
                //add to markers array
                markerData.push({
                        lat: parseFloat(data[i].home_address.latitude),
                        lng: parseFloat(data[i].home_address.longitude),
                        work: data[i].work_address.full_address,
                        type: data[i].type,
                        name: data[i].name,
                    }
                );
            }

            // Create an info window
            const infoWindow = new google.maps.InfoWindow();

            // Loop through the marker data and create markers
            markerData.forEach(data => {
                //split the work address to get the city
                let workCity = data.work.split(',')[1];
                if(workCity.includes(' Rd')
                || workCity.includes(' Ave')
                || workCity.includes(' St')
                || workCity.includes('-Jr')
                    || workCity.includes(' Dr')
                ){
                    workCity = data.work.split(',')[2];
                }
                const priceTag = document.createElement("div");

                if(data.type.localeCompare("passenger")){
                    priceTag.className = "price-tag passenger";
                }else{
                    priceTag.className = "price-tag driver";
                }

                priceTag.textContent = workCity;

                const marker = new AdvancedMarkerElement({
                    map,
                    position: {lat: data.lat, lng: data.lng},
                    content: priceTag,
                });

                // Add a click event listener to each marker to open the info window
                marker.addListener('click', () => {
                    infoWindow.setContent(`${data.name}(${data.type})<br>Work: ${data.work}`);
                    infoWindow.open(map, marker);
                });
            });

        });


}


window.initMap = initMap;
