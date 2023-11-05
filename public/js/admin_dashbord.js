//on page load
$(document).ready(function () {
    getJoiners("driver", "0", "driversToday");
    getJoiners("driver", "6", "drivers7days");
    getJoiners("passenger", "0", "passengersToday");
    getJoiners("passenger", "6", "passengers7days");
    getJoiners("driver", "365", "allDrivers");
    getJoiners("passenger", "365", "allPassengers");

    //facebook
    getFBJoiners("driver", "0", "fbDriversToday");
    getFBJoiners("driver", "6", "fbDrivers7days");
    getFBJoiners("passenger", "0", "fbPassengersToday");
    getFBJoiners("passenger", "6", "fbPassengers7days");
    getFBJoiners("driver", "365", "allFBDrivers");
    getFBJoiners("passenger", "365", "allFBPassengers");
});


let getJoiners = (type, days, elementId) => {

  let url = "/api/stats/new_commuters/"+type+"/"+days;

  $.ajax({
    url: url,
    type: "get",
    contentType: "application/json",
    success: function (response, textStatus, jqXHR) {
      //convert json string to json object

        $('#'+elementId).html(response.count);

    },
    error: function (jqXHR, textStatus, errorThrown) {
        showToast("Request failed with status code: " + jqXHR.status);
    }
  });
};


let getFBJoiners = (type, days, elementId) => {

    let url = "/api/stats/fb/new_commuters/"+type+"/"+days;

    $.ajax({
        url: url,
        type: "get",
        contentType: "application/json",
        success: function (response, textStatus, jqXHR) {
            //convert json string to json object

            $('#'+elementId).html(response.count);

        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast("Request failed with status code: " + jqXHR.status);
        }
    });
};
