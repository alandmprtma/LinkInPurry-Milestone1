var about = document.getElementById("about");
var aboutedit = document.getElementById("about-edit");
var c_location = document.getElementById("location");
var c_locationedit = document.getElementById("location-edit");
var c_locationeditbox = document.getElementById("location-edit-box");

var location_prevvalue = "";
function EditLocationToggle(){
    if (!c_location.hidden){ //editor closed state
        location_prevvalue = c_location.innerHTML;
        c_locationeditbox.value = c_location.innerHTML;
    }
    else{ //editor open state
        if (c_locationeditbox.value !== location_prevvalue){
            updateInfo('location');
        }
    }   
    c_location.hidden = !c_location.hidden;
    c_locationedit.hidden = !c_locationedit.hidden;
}

function EditAboutToggle(){
    if (!about.hidden){ //editor closed state

    }
    else{ //editor open state
        quill.root.innerHTML = about.innerHTML;
    }   
    about.hidden = !about.hidden;
    aboutedit.hidden = !aboutedit.hidden;
}


function updateInfo(thing){
    var payload = {};
    if (thing == 'location'){
        payload["location"] = c_locationeditbox.value;//get from editor
    }
    else if (thing == 'about'){
        payload["about"] = quill.root.innerHTML;
    }
    else{
        return null;
    }

    var xhr = new XMLHttpRequest();

    xhr.open('PUT', 'profile.php', true);

    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onreadystatechange = function() {
        if (xhr.readyState == XMLHttpRequest.DONE) {
            if (xhr.status == 200) {
                //
                console.log('Response:', xhr.responseText);
                onSuccess();
            } else {
                console.error('Error:', xhr.status, xhr.statusText);
            }
        }
    };

    var data = JSON.stringify(payload);

    xhr.send(data);
}

function onSuccess(){
    window.location.replace(window.location + "&success");
}