window.onbeforeunload = function () {
    window.scrollTo(0, 0);
}

var buttonA = document.getElementById("buttonA");
var buttonB = document.getElementById("buttonB");
var splashtext = document.getElementById("splashtext");
var selection = document.getElementById("selection");
var information = document.getElementById("information");
var corpoinfo = document.getElementById("corpo-information");
var selectedType = "None";
var goodPassword = false;


function funcA() {
    console.log("TestA");
    splashtext.textContent = "One step away from opportunities!";
    information.scrollIntoView({ behavior: "smooth" });
    selectedType = "jobseeker";
    buttonA.classList.add('pickleft');
    uploadButton.innerHTML = "Submit!";
}

function funcB() {
    console.log("TestB");
    splashtext.textContent = "One step away from meeting potential!";
    information.scrollIntoView({ behavior: "smooth" });
    selectedType = "company";
    buttonB.classList.add('pickright');
    uploadButton.innerHTML = "Next";
}

buttonA.addEventListener("click", funcA);
buttonB.addEventListener("click", funcB);


//INPUT RELATED
var namebox = document.getElementById("fullname");
var emailbox = document.getElementById("email")
var firstPassword = document.getElementById("password");
var checkPassword = document.getElementById("confirmpassword");
var passwordReport = document.getElementById("password_report");

function checkpasswordCorrect() {
    var password1 = firstPassword.value;
    var password2 = checkPassword.value;
    console.log("change");
    if (password1 == password2) {
        passwordReport.hidden = true;
        goodPassword = true
    }
    else {
        passwordReport.hidden = false;
        goodPassword = false
    }
}

firstPassword.addEventListener("input", checkpasswordCorrect);
checkPassword.addEventListener("input", checkpasswordCorrect);

function checkEmail(email) {
    const emailRegexCheck = /^[\w-\.]+@([\w-]+\.)+[\w-]+$/;
    return emailRegexCheck.test(email);
}

document.getElementById("password_visible").addEventListener("change", function () {
    if (this.checked) {
        firstPassword.type = 'text';
    }
    else {
        firstPassword.type = 'password';
    }
})

document.getElementById("checkpassword_visible").addEventListener("change", function () {
    if (this.checked) {
        checkPassword.type = 'text';
    }
    else {
        checkPassword.type = 'password';
    }
})


function corpocheck(){
    if (document.getElementById("location").value != null && document.getElementById("location").value !== ""){
        return true;
    }
    else{
        return false;
    }
}

document.getElementById("location").addEventListener("input", function(){
    if (corpocheck()){
        document.getElementById("submit_data_corpo").disabled = false;
    }
    else{
        document.getElementById("submit_data_corpo").disabled = true;
    }
})

//upload check

var uploadButton = document.getElementById("submit_data");

function canUpload() {
    if (corpo_mode){
        var location = document.getElementById("location").value
        if (location != null && location !== ""){
            uploadButton.disabled = false;
            return true;
        }
        else{
            uploadButton.disabled = true;
            return false;
        }

    }
    else if (namebox.value != "" && emailbox != "" && firstPassword != "" && checkPassword != "" && goodPassword) {
        uploadButton.disabled = false;
        return true;
    }
    else {
        uploadButton.disabled = true;
        return false;
    }
}
namebox.addEventListener("input", canUpload);
emailbox.addEventListener("input", canUpload);
firstPassword.addEventListener("input", canUpload);
checkPassword.addEventListener("input", canUpload);

uploadButton.addEventListener("click", submit);
document.getElementById("submit_data_corpo").addEventListener("click", submit);

//go to corpo details first
var corpo_mode = false;

function submit() {
    //if JobSeeker, immediately upload
    if (canUpload()) {
        if (selectedType == 'company' && !corpo_mode){
            corpo_mode = true;
            canUpload();
            //slide to right
            corpoinfo.scrollIntoView({ behavior: "smooth" });
            return false;
        }
        else{
            console.log("Sending...")
        }
        var xmlh = new XMLHttpRequest();
        xmlh.open('POST', '../auth/register.php');
        xmlh.onreadystatechange = function () {
            if (xmlh.readyState == 4 && xmlh.status == 200) {
                //Report success
                console.log("testlmao");
                try {
                    const ajaxrespone = JSON.parse(xmlh.response);
                    console.log(ajaxrespone);

                    if (ajaxrespone["Success"] == true) {
                        setTimeout(function () {
                            window.location.href = "../login.html";
                        }, 3000);
                    }
                    else {
                        console.warn("Failure");
                    }
                } catch (error) {
                    console.warn(xmlh.response);
                }
            }
            else {
            }
        };

        const nama = namebox.value;
        const email = emailbox.value;
        const pass = password.value;

        xmlh.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        
        var payload = "role=" + selectedType + "&nama=" + nama + "&email=" + email + "&password=" + pass
        if (corpo_mode){
            payload += "&location=" + location + "&about=" + about;
        }
        xmlh.send(payload);
    }
    else {
        console.error("Nice try!");
    }
}

function report(success, reason) {
    if (success == true) {

    }
    else {

    }
}

function hide_report() {

}

function returnto_info() {
    information.scrollIntoView({ behavior: "smooth" });
}

function returnto_pick() {
    selection.scrollIntoView({ behavior: "smooth" });
}
