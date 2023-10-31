//on page load
$(document).ready(function () {
    getMatch();
    //on li-driver-status click event call updateStatus function with driver commuter type and status
    $('.li-driver-status').click(function (event) {
        updateStatus("driver", event.target.getAttribute('data-status'));
    });

    $('.li-passenger-status').click(function (event) {
        updateStatus("passenger", event.target.getAttribute('data-status'));
    });

    $('#hide-match').click(function () {
        updateStatus("match", "hidden");
    });
});


let getMatch = () => {

    //read id from url
    let myUrl = window.location.href;
    let id = myUrl.substring(myUrl.lastIndexOf('=') + 1);

  let url = "/api/match/" + id;

  $.ajax({
    url: url,
    type: "get",
    contentType: "application/json",
    success: function (response, textStatus, jqXHR) {
      //convert json string to json object
        if(response.code.localeCompare("R00") === 0){
            let match = JSON.parse(response.match);
            //driver string
            //if phone number start with zero, make it start with +27
            if(match.driver.phone.startsWith("0")){
                match.driver.phone = match.driver.phone.replace("0", "+27 ");
            }

            //if phone number start with 27, make it start with +27
            if(match.driver.phone.startsWith("27")){
                match.driver.phone = match.driver.phone.replace("27", "+27 ");
            }

            //remove spaces from number
            match.driver.phone = match.driver.phone.replaceAll(" ", "");

            let driver = "Name: " +  match.driver.name
                + "<br>Phone: <a href='https://api.whatsapp.com/send?phone="+match.driver.phone+"&text=Hello " +match.driver.name + ", We found a match for your daily commute. WorkRide.co.za'>" + match.driver.phone + "</a>"
                + "<br>Email: " + match.driver.email
                + "<br>Home Address: " + match.driver.home_address.full_address
                + "<br>Work Address: " + match.driver.work_address.full_address
                + "<br>Status: " + match.driver_status;
            $('#driver-details').html(driver);

            let passenger = "Name: " +  match.passenger.name
                + "<br>Phone: <a href='https://api.whatsapp.com/send?phone="+match.passenger.phone+"&text=Hello " +match.passenger.name + ", We found a lift club for you. WorkRide.co.za'>" + match.passenger.phone + "</a>"
                + "<br>Email: " + match.passenger.email
                + "<br>Home Address: " + match.passenger.home_address.full_address
                + "<br>Work Address: " + match.passenger.work_address.full_address
                + "<br>Status: " + match.passenger_status;
            $('#passenger-details').html(passenger);

            //mathc details
            let matchDetails =  "Additional Minutes: " + match.additional_time
                + "<br>Total Trip Minutes: " + match.total_trip
                + "<br>Home To Home KM: " + match.distance_home
                + "<br>Work to Work KM: " + match.distance_work
                + "<br>Home To Home Minutes: " + match.duration_home
            + "<br>Work To Work Minutes: " + match.duration_work
            + "<br><a target='_blank' href='"+match.map_link+"'>Google Map</a>"
            + "<br>Status: " + match.status;

            $('#match-details').html(matchDetails);
        }
    },
    error: function (jqXHR, textStatus, errorThrown) {
        showToast("Request failed with status code: " + jqXHR.status);
    }
  });
};

let updateStatus = (commuterType, status) => {
    let myUrl = window.location.href;
    let id = myUrl.substring(myUrl.lastIndexOf('=') + 1);

    let url = "/api/update/match";
    const data = {
        id: id,
        status: status,
        commuter_type: commuterType,
    };

    $.ajax({
        url: url,
        type: "put",
        contentType: "application/json",
        data: JSON.stringify(data),
        success: function (response, textStatus, jqXHR) {
            showToast(response.message);
            getMatch();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast("Request failed with status code: " + jqXHR.status);
        }
    });
};

let showToast = (message) =>{
    const liveToast = document.getElementById('liveToast')
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(liveToast)
    $('#toast-message').html('<div class="alert" role="alert">'+message+'</div>');
    toastBootstrap.show();
}
