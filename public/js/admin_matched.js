//on page load
$(document).ready(function () {
    getAlMatches(0);

    $('.li-status-filter').click(function(event){
        //set text for dropdownMenuButtonStatus and data-status attribute
        $('#dropdownMenuButtonStatus').text(event.target.getAttribute("data-status"));
        $('#dropdownMenuButtonStatus').attr("data-status", event.target.getAttribute("data-status"));
        // get attribute data-id and pass to getAlMatches
        getAlMatches($('#dropdownMenuButtonStatus').attr("data-status"));
    });

    $('.li-time-filter').click(function(event){
        //set text for dropdownMenuButtonStatus and data-status attribute
        $('#dropdownMenuButtonTime').text(event.target.getAttribute("data-time"));
        $('#dropdownMenuButtonTime').attr("data-time", event.target.getAttribute("data-time"));
        // get attribute data-id and pass to getAlMatches
        getAlMatches("0");
    });
});

let driverNames = [];


let getAlMatches = (driverId) => {

  let url = "/api/matches/" + driverId + "/" + $('#dropdownMenuButtonStatus').attr("data-status") + "/" + $('#dropdownMenuButtonTime').attr("data-time")

  $.ajax({
    url: url,
    type: "get",
    contentType: "application/json",
    success: function (response, textStatus, jqXHR) {
      //convert json string to json object
        $('#commuters-tbody').html("");
        let data = JSON.parse(response.matches);

        for (let i = 0; i < data.length; i++) {
            //create tr element and append to tbody with id commuters-tbody
            let tr = $('<tr/>');
            tr.append("<td><a target='_blank' href='/match?id="+data[i].id+"'>" + data[i].driver.name + "</a></td>");
            tr.append("<td><a target='_blank' href='/match?id="+data[i].id+"'>" + data[i].passenger.name + "</a></td>");
            tr.append("<td><a target='_blank' href='"+data[i].map_link+"'>" + data[i].driver.home_address.full_address.replace(", South Africa", "") + "</a></td>");
            tr.append("<td>" + data[i].driver.work_address.full_address.replace(", South Africa", "") + "</td>");

            tr.append("<td>" + data[i].passenger.home_address.full_address.replace(", South Africa", "") + "</td>");
            tr.append("<td>" + data[i].passenger.work_address.full_address.replace(", South Africa", "") + "</td>");
            tr.append("<td>" + data[i].additional_time + "</td>");
            tr.append("<td>" + data[i].status + "</td>");
            tr.append("<td>" + data[i].driver_status + "</td>");
            tr.append("<td>" + data[i].passenger_status + "</td>");
            $('#commuters-tbody').append(tr);

            //add unique driver names to driverNames array
            if(!driverNames.includes("<a class='dropdown-item' href='#' data-id='"+data[i].driver.id+"'>"+data[i].driver.name+"</a>")){
                driverNames.push("<a class='dropdown-item' href='#' data-id='"+data[i].driver.id+"'>"+data[i].driver.name+"</a>" );
            }

        }

        //loop through driverNames array and append to select with id driver-ul <li><a class="dropdown-item" href="#">Action</a></li>
        //on click, call getAlMatches with driver name as parameter
        // check if $('#driver-ul') is empty first
        if($('#driver-ul').html() === ""){
            for (let i = 0; i < driverNames.length; i++) {
                let li = $('<li/>');
                li.append(driverNames[i]);
                $('#driver-ul').append(li);

                li.click(function(){
                    // get attribute data-id and pass to getAlMatches
                    let id = $(this).children().attr("data-id");
                    getAlMatches(id);
                });
            }
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


