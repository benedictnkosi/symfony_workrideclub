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

            match.driver.phone = formatPhoneNumber(match.driver.phone);
            let driver = "Name: " +  match.driver.name;
            if(!validURL(match.driver.phone)){
                match.driver.phone = formatPhoneNumber(match.driver.phone);
                driver += "<br>Phone Number: <a target='_blank'  href='https://api.whatsapp.com/send?phone="+match.driver.phone+"&text=Hi " +match.driver.name + ", We found a match for you. workride.co.za'>" + match.driver.phone + "</a>";
            }else{
                driver += "<br> <a target='_blank'  href='"+match.driver.phone+"'>Facebook Chat</a>";
            }

            driver += "<br>Home Address: " + match.driver.home_address.full_address.replace(", South Africa", "")
                + "<br>Work Address: " + match.driver.work_address.full_address.replace(", South Africa", "")
                + "<br>Status: " + match.driver_status;
            $('#driver-details').html(driver);

            match.passenger.phone = formatPhoneNumber(match.passenger.phone);

            let passenger = "Name: " +  match.passenger.name
            if(!validURL(match.passenger.phone)){
                match.passenger.phone = formatPhoneNumber(match.passenger.phone);
                passenger += "<br>Phone Number: <a target='_blank'  href='https://api.whatsapp.com/send?phone="+match.passenger.phone+"&text=Hi " + match.passenger.name + ", We found a lift club for you. workride.co.za'>" + match.passenger.phone + "</a>";
            }else{
                passenger += "<br><a target='_blank'  href='"+match.passenger.phone+"'>Facebook Chat</a>";
            }

            passenger += "<br>Home Address: " + match.passenger.home_address.full_address.replace(", South Africa", "")
                + "<br>Work Address: " + match.passenger.work_address.full_address.replace(", South Africa", "")
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

//create url validate function
function validURL(str) {
    const pattern = new RegExp('^(https?:\\/\\/)?' + // protocol
        '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
        '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
        '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
        '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
        '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
    return !!pattern.test(str);
}

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


let formatPhoneNumber = (phoneNumberString) => {
    //if phone number start with zero, make it start with +27
    if(phoneNumberString.startsWith("0")){
        phoneNumberString = phoneNumberString.replace("0", "+27 ");
    }

    //if phone number start with 27, make it start with +27
    if(phoneNumberString.startsWith("27")){
        phoneNumberString = phoneNumberString.replace("27", "+27 ");
    }

    //remove spaces from number
    phoneNumberString = phoneNumberString.replaceAll(" ", "");
    return phoneNumberString;
}
