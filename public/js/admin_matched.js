//on page load
$(document).ready(function () {
    getAlMatches();
});



let getAlMatches = () => {

  let url = "/api/matches";

  $.ajax({
    url: url,
    type: "get",
    contentType: "application/json",
    success: function (response, textStatus, jqXHR) {
      //convert json string to json object
        let data = JSON.parse(response.matches);
        //loop through the data
        for (let i = 0; i < data.length; i++) {
            //create tr element and append to tbody with id commuters-tbody
            let tr = $('<tr/>');
            tr.append("<td>" + data[i].driver.name + "</td>");
            tr.append("<td>" + data[i].passenger.name + "</td>");
            tr.append("<td>" + data[i].driver.home_address.full_address.replace(", South Africa", "") + "</td>");
            tr.append("<td>" + data[i].passenger.home_address.full_address.replace(", South Africa", "") + "</td>");
            tr.append("<td>" + data[i].driver.work_address.full_address.replace(", South Africa", "") + "</td>");
            tr.append("<td>" + data[i].passenger.work_address.full_address.replace(", South Africa", "") + "</td>");
            tr.append("<td>" + data[i].additional_time + "</td>");
            tr.append("<td>" + data[i].driver_status + "</td>");
            tr.append("<td>" + data[i].passenger_status + "</td>");
            //add a link to update commuter
            tr.append("<td><a href='"+data[i].map_link+"'>Map</a></td>");
            $('#commuters-tbody').append(tr);
        }

        //add click event for class match-button us the data-id attribute to get the id of the commuter
        $(".match-button").click(function(){
            let id = $(this).attr("data-id");
            matchCommuter(id);
        });

        //add click event for class unmatch-button us the data-id attribute to get the id of the commuter
        $(".unmatch-button").click(function(){
            let id = $(this).attr("data-id");
            unmatchCommuter(id);
        });

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
