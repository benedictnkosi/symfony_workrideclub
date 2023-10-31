//on page load
$(document).ready(function () {
    getJoiners("driver", "0", "driversToday");
    getJoiners("driver", "6", "drivers7days");
    getJoiners("passenger", "0", "passengersToday");
    getJoiners("passenger", "6", "passengers7days");
    getJoiners("driver", "365", "allDrivers");
    getJoiners("passenger", "365", "allPassengers");
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

let showToast = (message) =>{
    const liveToast = document.getElementById('liveToast')
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(liveToast)
    $('#toast-message').html('<div class="alert" role="alert">'+message+'</div>');
    toastBootstrap.show();
}