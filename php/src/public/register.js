window.onbeforeunload = function () {
    window.scrollTo(0, 0);
}

var buttonA = document.getElementById("buttonA");
var buttonB = document.getElementById("buttonB");
var splashtext = document.getElementById("splashtext");
var selection = document.getElementById("selection");
var information = document.getElementById("information");
var corpoinfo = document.getElementById("corpo-information");
var corplocation = document.getElementById("location")
var selectedType = "None";
var goodPassword = false;


function funcA() {
    console.log("TestA");
    splashtext.textContent = "One step away from opportunities!";
    information.scrollIntoView({ behavior: "smooth" });
    selectedType = "jobseeker";
    buttonA.classList.add('pickleft');
    uploadButton.innerHTML = "Submit!";
    uploadButton.ariaLabel = "Submit registration data";
}

function funcB() {
    console.log("TestB");
    splashtext.textContent = "One step away from meeting potential!";
    information.scrollIntoView({ behavior: "smooth" });
    selectedType = "company";
    buttonB.classList.add('pickright');
    uploadButton.innerHTML = "Next";
    uploadButton.ariaLabel = "Next section";
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
        //passwordReport.hidden = true;
        reportinput("checkpassword-report");
        goodPassword = true
    }
    else {
        //passwordReport.hidden = false;
        reportinput("checkpassword-report","Password is not matching!");
        goodPassword = false
    }
}

firstPassword.addEventListener("input", checkpasswordCorrect);
checkPassword.addEventListener("input", checkpasswordCorrect);

function checkEmail() {

    const email = emailbox.value;
    if (email == '' || email == null){
        reportinput("email-report","Email cannot be empty!");
        resolve(false);
    }
    //cleanse any errors
    return new Promise((resolve,reject) =>{
    const emailRegexCheck = /^[\w-\.]+@([\w-]+\.)+[\w-]+$/;
    if (!emailRegexCheck.test(email)){
        //warn bad email format!
        reportinput("email-report","Incorrect email format!");
        resolve(false);
    }
    //post request
    var xmlh = new XMLHttpRequest();
        xmlh.open('POST', '../auth/register.php');
        xmlh.onreadystatechange = function () {
            var failsafe = 0;
            if (xmlh.readyState == 4 && xmlh.status == 200) {
                try {
                    const ajaxrespone = JSON.parse(xmlh.response);

                    if (ajaxrespone["success"] == true) {
                        reportinput("email-report");
                        resolve(true);
                    }
                    else {
                        reportinput("email-report",ajaxrespone["reason"]+" ");
                        //warn bad email or smthn
                        resolve(false);
                    }
                } catch (error) {
                    //usually if the retval isnt json, something happened
                    console.warn(xmlh.response);
                }
            }
            else {
                failsafe ++;
                if (failsafe > 4){
                    reject("Cannot establish connection with server!");
                }
            }
        };

        xmlh.setRequestHeader("Content-Type", "application/json");
        
        var payload = {
            "intent" : "email-check",
            "email" : email
        }

        xmlh.send(JSON.stringify(payload));
    })
}

document.getElementById("password_visible").addEventListener("click", function () {
    if (this.classList.contains("fa-eye-slash")){
        firstPassword.type = 'text';
    }
    else {
        firstPassword.type = 'password';
    }
    this.classList.toggle("fa-eye");
    this.classList.toggle("fa-eye-slash");
})

document.getElementById("checkpassword_visible").addEventListener("click", function () {
    if (this.classList.contains("fa-eye-slash")){
        checkPassword.type = 'text';
    }
    else {
        checkPassword.type = 'password';
    }
    this.classList.toggle("fa-eye");
    this.classList.toggle("fa-eye-slash");
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

var canUploadVar = false

function canUpload() {
    if (corpo_mode){
        var clocation = corplocation.value
        if (clocation != null && clocation !== ""){
            reportinput("location-report")
            uploadButton.disabled = false;
            canUploadVar = true;
        }
        else{
            reportinput("location-report","Location cannot be empty!");
            uploadButton.disabled = true;
            canUploadVar = false;
        }

    }
    else if (namebox.value != "" && emailbox != "" && firstPassword != "" && checkPassword != "" && goodPassword) {
        checkEmail().then(function(response){
            if (response == true){
                uploadButton.disabled = false;
                canUploadVar = true;
            }
            else{
                uploadButton.disabled = true;
                canUploadVar = false;
            }
        }).catch(function(error){
            console.warn("checkemail failure");
            //error???
        })
        
    }
    else {
        console.log("huh");
        uploadButton.disabled = true;
        canUploadVar = false;
    }
}
namebox.addEventListener("change", canUpload);
emailbox.addEventListener("change", canUpload);
firstPassword.addEventListener("input", canUpload);
checkPassword.addEventListener("input", canUpload);

corplocation.addEventListener("change", function(){
    canUpload();
});

uploadButton.addEventListener("click", submit);
document.getElementById("submit_data_corpo").addEventListener("click", submit);

//go to corpo details first
var corpo_mode = false;


function submit() {
    //if JobSeeker, immediately upload
    if (canUploadVar) {
        if (selectedType == 'company' && !corpo_mode){
            uploadButton.disabled = true;
            corpo_mode = true;
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
            var antidouble = false;
            if (xmlh.readyState == 4 && xmlh.status == 200) {
                //Report success
                console.log("testlmao");
                try {
                    const ajaxrespone = JSON.parse(xmlh.response);
                    console.log(ajaxrespone);

                    if (ajaxrespone["success"] == true) {
                        antidouble = true;
                        console.log("redirecting...")
                        setTimeout(function () {
                            window.location.href = "../auth/login.html";
                        }, 3000);
                    }
                    else if (!antidouble){
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

        xmlh.setRequestHeader("Content-Type", "application/json");
        
        var payload = {
            "intent" : "register",
            "role" : selectedType,
            "nama" : nama,
            "email" : email,
            "password" : pass
        }
        if (corpo_mode){
            payload["location"] = document.getElementById("location").value,
            payload["about"] = quill.root.innerHTML
        }
        xmlh.send(JSON.stringify(payload));
    }
    else {
        console.error("Nice try!");
    }
}

function reportinput(id, info){
    var reportbox = document.getElementById(id);
    var parentcl = reportbox.parentNode;

    if (info != '' && info != null){
        reportbox.innerHTML = info;
        parentcl.classList.add("bad");
    }else{
        reportbox.innerHTML = "";
        parentcl.classList.remove("bad");
    }
}

function reportbox(success, reason) {
    if (success == true) {

    }
    else {

    }
}

function hide_reportbox() {

}

function returnto_info() {
    corpo_mode = false;
    canUpload();
    information.scrollIntoView({ behavior: "smooth" });
}

function returnto_pick() {
    selectedType = "None";
    selection.scrollIntoView({ behavior: "smooth" });
}
