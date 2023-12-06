//on page load
$(document).ready(function () {
    getUnmatched();
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

    generateRegistrationsGraph();
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

let getUnmatched = () => {

    let url = "/api/stats/unmatched";

    $.ajax({
        url: url,
        type: "get",
        contentType: "application/json",
        success: function (response, textStatus, jqXHR) {
            //convert json string to json object

            $('#unmatched').html(response.count);

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

function generateRegistrationsGraph(){
    let url = "/api/registrations/daily"
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (registrations) {
            if(registrations.result_code !== undefined){
                if(registrations.result_code === 1){
                    return;
                }
            }
            let chartStatus = Chart.getChart("lineChartRegistrations"); // <canvas> id
            if (chartStatus !== undefined) {
                chartStatus.destroy();
            }

            const labels = [];
            const dataPoints = [];

            registrations.forEach(function (registration) {
                labels.push(registration.day);
                dataPoints.push(registration.count);
            });

            var ctxL = document.getElementById("lineChartRegistrations").getContext('2d');
            var myLineChart = new Chart(ctxL, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Registrations",
                        data: dataPoints,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true
                }
            });
        },
        error: function (xhr) {

        }
    });

}