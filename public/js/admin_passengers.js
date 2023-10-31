//on page load
$(document).ready(function () {
    getAllPassengers();
});


let getAllPassengers = () => {

  let url = "/api/commuters/passenger";

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
            if(!validURL(data[i].phone)){
                data[i].phone = formatPhoneNumber(data[i].phone);
                tr.append("<td><a target='_blank'  href='https://api.whatsapp.com/send?phone="+data[i].phone+"&text=Hello " + data[i].name + "'>" + data[i].phone + "</a></td>");
            }else{
                tr.append("<td><a target='_blank'  href='"+data[i].phone+"'>Facebook Chat</a></td>");
            }

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


            //append a select with id driver-status
            let select = $('<select/>');
            select.attr("id", "passenger-status");
            select.append("<option value=''>Select</option>");
            select.append("<option value='active'>Active</option>");
            select.append("<option value='unavailable'>Unavailable</option>");

            //set the selected option
            if(data[i].status === "active"){
                select.val("active");
            }else if(data[i].status === "unavailable"){
                select.val("unavailable");
            }

            //select change call the updateDriverStatus function
            select.change(function(){
                let status = $(this).val();
                updatePassengerStatus(data[i].id, status);
            });

            //append to tr
            let td = $('<td/>');
            td.append(select);
            tr.append(td);

            tr.append("<td>" + data[i].travel_time + "</td>");

            let confirmMessage = "This is workride.co.za. Please confirm that you are a "+data[i].type+". Travelling from " + data[i].home_address.full_address + " to " + data[i].work_address.full_address + ". Reply with YES or NO";
            tr.append("<td><a target='_blank' href='https://api.whatsapp.com/send?phone="+data[i].phone+"&text=" + confirmMessage + "'>Confirm</a></td>");


            $('#commuters-tbody').append(tr);
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

let updatePassengerStatus = (id, status) => {
    let url = "/api/update/commuter/status";
    const data = {
        id: id,
        status: status
    };

    $.ajax({
        url: url,
        type: "put",
        contentType: "application/json",
        data: JSON.stringify(data),
        success: function (response, textStatus, jqXHR) {
            showToast(response.message);
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

let showToast = (message) =>{
    const liveToast = document.getElementById('liveToast')
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(liveToast)
    $('#toast-message').html('<div class="alert" role="alert">'+message+'</div>');
    toastBootstrap.show();
}