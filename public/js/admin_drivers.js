//on page load
$(document).ready(function () {
    getAllDrivers();

    //add click event for id match-all-drivers
    $("#match-all-drivers").click(function(event){
        //get button from event and append spinner
        $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        matchAllDrivers();
    });
});

let matchAllDrivers = () => {
    let url = "api/drivers/matchall";
    $("#match-all-drivers").html("Loading...");
    $.ajax({
        url: url,
        type: "get",
        contentType: "application/json",
        success: function (response, textStatus, jqXHR) {
            //remove spinners from buttons
            $("#match-all-drivers").html("Match All Drivers");
            showToast(response.message);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $("#match-all-drivers").html("Match All Drivers");
            showToast("Request failed with status code: " + jqXHR.status);
        }
    });
}


let matchCommuter = (id) => {
    let url = "api/commuters/match/" + id;

    $.ajax({
        url: url,
        type: "get",
        contentType: "application/json",
        success: function (response, textStatus, jqXHR) {
            //remove spinners from buttons
            $(".match-button").html("Match");
            showToast(response.message);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $(".match-button").html("Match");
            showToast("Request failed with status code: " + jqXHR.status);
        }
    });
}

let unmatchCommuter = (id) => {
    let url = "api/commuters/unmatch/" + id;

    $.ajax({
        url: url,
        type: "get",
        contentType: "application/json",
        success: function (response, textStatus, jqXHR) {
            showToast(response.message);
            //remove spinners from buttons
            $(".unmatch-button").html("Unmatch");
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast("Request failed with status code: " + jqXHR.status);
            $(".unmatch-button").html("Unmatch");
        }
    });
}


let getAllDrivers = () => {

  let url = "/api/commuters/driver";

  $.ajax({
    url: url,
    type: "get",
    contentType: "application/json",
    success: function (response, textStatus, jqXHR) {
      //convert json string to json object
        let data = JSON.parse(response.commuters);

        //loop through the data
        for (let i = 0; i < data.length; i++) {
            //create tr element and append to tbody with id commuters-tbody
            let tr = $('<tr/>');
            tr.append("<td>" + data[i].id + "</td>");
            tr.append("<td>" + data[i].created.replace("+02:00","") + "</td>");
            tr.append("<td>" + data[i].name + "</td>");
            tr.append("<td><a href='https://api.whatsapp.com/send?phone="+data[i].phone+"&text=Hello'>" + data[i].phone + "</a></td>");

            //remove text after the last comma from data[i].home_address.full_address
            let home_address = data[i].home_address.full_address;
            let home_address_array = home_address.split(",");
            let home_address_array_length = home_address_array.length;
            let home_address_array_length_minus_one = home_address_array_length - 1;
            let home_address_array_sliced = home_address_array.slice(0, home_address_array_length_minus_one);
            let home_address_array_sliced_joined = home_address_array_sliced.join(",");
            tr.append("<td>" + home_address_array_sliced_joined + "</td>");
            //remove text after the last comma from data[i].work_address.full_address
            let work_address = data[i].work_address.full_address;
            let work_address_array = work_address.split(",");
            let work_address_array_length = work_address_array.length;
            let work_address_array_length_minus_one = work_address_array_length - 1;
            let work_address_array_sliced = work_address_array.slice(0, work_address_array_length_minus_one);
            let work_address_array_sliced_joined = work_address_array_sliced.join(",");
            tr.append("<td>" + work_address_array_sliced_joined + "</td>");

            if(data[i].last_match !== undefined){
                tr.append("<td>" + data[i].last_match.replace("+02:00","") + "</td>");
            }else{
                tr.append("<td>Not matched</td>");
            }
            tr.append("<td>" + data[i].travel_time + "</td>");

            //append a button to tr
            tr.append("<td><button class='btn btn-primary calc-button' data-id='"+data[i].id+"' style='padding:0'>Calculate</button></td>");
            tr.append("<td><button class='btn btn-primary match-button' data-id='"+data[i].id+"' style='padding:0'>Match</button></td>");

            $('#commuters-tbody').append(tr);
        }



        //add click event for class match-button us the data-id attribute to get the id of the commuter
        $(".match-button").click(function(event){
            //get button from event and append spinner
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            let id = $(this).attr("data-id");
            matchCommuter(id);
        });

        //add click event for class unmatch-button us the data-id attribute to get the id of the commuter
        $(".unmatch-button").click(function(event){
            //get button from event and append spinner
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
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
