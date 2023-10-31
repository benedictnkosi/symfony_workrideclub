// This sample uses the Places Autocomplete widget to:
// 1. Help the user select a place
// 2. Retrieve the address components associated with that place
// 3. Populate the form fields with those address components.
// This sample requires the Places library, Maps JavaScript API.
// Include the libraries=places parameter when you first load the API.
// For example: <script
// src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">
let autocomplete;
let workAutocomplete;
let address1Field;
let address2Field;
let postalField;

$(document).ready(function () {
  $("#register-form").submit(function (event) {
    event.preventDefault();
  });

  $("#register-form").validate({
    // Specify validation rules
    rules: {
      name: {
        required: true,
        maxlength: 50,
      },
      home_address: {
        required: true,
        maxlength: 200,
      },
      work_address: {
        required: true,
        maxlength: 200,
      },
    },
    submitHandler: function () {
      signup();
    },
  });
});

function initAutocomplete() {
  address1Field = document.querySelector("#home_address");
  address2Field = document.querySelector("#work_address");
  // Create the autocomplete object, restricting the search predictions to
  // addresses in the US and Canada.
  autocomplete = new google.maps.places.Autocomplete(address1Field, {
    componentRestrictions: { country: ["za"] },
    fields: ["address_components", "geometry"],
    types: ["address"],
  });

  workAutocomplete = new google.maps.places.Autocomplete(address2Field, {
    componentRestrictions: { country: ["za"] },
    fields: ["address_components", "geometry"],
    types: ["address"],
  });
  // When the user selects an address from the drop-down, populate the
  // address fields in the form.
}


function getHomePlacesElement(name) {
  // Get the place details from the autocomplete object.
  const place = autocomplete.getPlace();

  if (address1Field.value.trim() === '') {
      showToast("Please enter an address");
      return;
    }

    if (name === "latitude") {
          return place.geometry.location.lat();
        }

        if (name === "longitude") {
          return place.geometry.location.lng();
        }

  for (const component of place.address_components) {
    // @ts-ignore remove once typings fixed
    const componentType = component.types[0];

    switch (componentType) {
      case "locality":
        if (name === "suburb") {
          return component.long_name;
        }

        break;
       case "administrative_area_level_2": {
               if (name === "city") {
                 return component.long_name;
               }
               break;
        }
      case "administrative_area_level_1": {
        if (name === "state") {
          return component.short_name;
        }
        break;
      }
      case "country": {
        if (name === "country") {
          return component.long_name;
        }
        break;
      }
      case "route": {
        if (name === "street_name") {
          return component.short_name;
        }
        break;
      }
    }


  }
  return false;
}

function getWorkPlacesElement(name) {
  // Get the place details from the autocomplete object.
  const place = workAutocomplete.getPlace();

  if (address2Field.value.trim() === '') {
      showToast("Please enter an address");
      return;
    }

if (name === "latitude") {
        return place.geometry.location.lat();
      }

      if (name === "longitude") {
        return place.geometry.location.lng();
      }

  for (const component of place.address_components) {
    // @ts-ignore remove once typings fixed
    const componentType = component.types[0];

    switch (componentType) {
      case "locality":
        if (name === "suburb") {
          return component.long_name;
        }

        break;
       case "administrative_area_level_2": {
               if (name === "city") {
                 return component.long_name;
               }
               break;
        }
      case "administrative_area_level_1": {
        if (name === "state") {
          return component.short_name;
        }
        break;
      }
      case "country": {
        if (name === "country") {
          return component.long_name;
        }
        break;
      }
      case "route": {
        if (name === "street_name") {
          return component.short_name;
        }
        break;
      }
    }

  }



  return false;
}

let signup = () => {
//check if home address is selected
  // Check if the user's input is not empty
  if (address1Field.value.trim() === '') {
    showToast("Please enter home address");
    return;
  }

  if (address2Field.value.trim() === '') {
    showToast("Please enter work address");
    return;
  }

  //check if radio button is selected with name commuterType
    if (!$("input[name='commuterType']:checked").val()) {
        showToast("Please select commuter type");
        return;
    }

  const name = $("#name").val().trim();

  const home_address = address1Field.value;
  const home_suburb = getHomePlacesElement("suburb");
  const home_address_city = getHomePlacesElement("city");
  const home_address_state = getHomePlacesElement("state");
  const home_address_country = getHomePlacesElement("country");
  const home_address_lat = getHomePlacesElement("latitude");
  const home_address_long = getHomePlacesElement("longitude");

  //get selected radio button from commuterType
    const commuterType = $("input[name='commuterType']:checked").val();

  const work_address = address2Field.value;
  const work_suburb = getWorkPlacesElement("suburb");
  const work_city = getWorkPlacesElement("city");
  const work_address_lat = getWorkPlacesElement("latitude");
  const work_address_long = getWorkPlacesElement("longitude");

  const facebook = $("#facebook-link").val().trim();
  //check that this is a valid link url
    if (facebook !== "") {
        if (!validURL(facebook)) {
            showToast("Please enter a valid facebook link");
            return;
        }
    }
  let url = "/api/commuter/create";
  const data = {
    name: name,
    phone: facebook,
    type: commuterType,
    home_address: home_address,
    home_address_city: home_address_city,
    home_address_state: home_address_state,
    home_address_country: home_address_country,
    home_address_lat: home_address_lat,
    home_address_long: home_address_long,
    home_suburb: home_suburb,
    country: home_address_country,

    work_address: work_address,
    work_address_lat: work_address_lat,
    work_address_long: work_address_long,
    work_city: work_city,
    work_suburb: work_suburb
  };

  //add a spinner to the input button to indicate loading has started. the button id is signu
  // Get the button element
  var button = document.getElementById("signup");

  // Get the spinner element
  var spinner = document.getElementById("spinner");

  // Change the button text to the spinner text
  button.value = "Please wait...";

  // Show the spinner
  spinner.style.display = "inline";

  $.ajax({
    url: url,
    type: "post",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: function (response, textStatus, jqXHR) {
      if (jqXHR.status === 201) {
                  // Request was successful (status code 200)
                  //navigate to thank you page
                    window.location.href = "/thank-you";
              } else {
                  // Handle other status codes if needed
                  showToast(response.message);
              }
      //remove spinner from button
      button.value = "Register";
    },
    error: function (jqXHR, textStatus, errorThrown) {
        showToast("Request failed with status code: " + jqXHR.status);
      button.value = "Register";
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

window.initAutocomplete = initAutocomplete;

let showToast = (message) =>{
    const liveToast = document.getElementById('liveToast')
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(liveToast)
    $('#toast-message').html('<div class="alert" role="alert">'+message+'</div>');
    toastBootstrap.show();
}
