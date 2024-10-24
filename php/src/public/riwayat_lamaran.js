console.log("OK!");

var lamaranData = null;
var currentPage = 1;
var entry_per_page = 5;

function capitalizeFirst(string) {
return string.charAt(0).toUpperCase() + string.slice(1);
}

//PAGE STARTS FROM 1
/**
* @param users : JSON
* @param page : int
* @param entryPerPage : int
**/

//should only need to be run once for pagination purposes
var fresh = true;
var total_lamaran = 0;

if (fresh){
    getcount()
    .then(function(response) {
        //
        total_lamaran = response;
        pagecount = Math.ceil(total_lamaran/entry_per_page);
        pageoffset(0);
        populateLamaran(1, entry_per_page, active_filter);
    })
    .catch(function(error){
        //call error reporter
        console.log(error);
    })
}

var active_filter = null;

function filter(toggle_button, filter){
    var buttons = document.getElementsByClassName('toggle_button');

    for (var i = 0; i < buttons.length; i++){
        buttons[i].classList.remove('active');
    }

    // remove active if same, 
    if (active_filter != filter) {
        toggle_button.classList.add('active');
        active_filter = filter;
        
    } else {
        active_filter = null;
    }
    //filter
    //refresh results and back to page 1
    currentPage = 1;
    populateLamaran(currentPage, entry_per_page, active_filter);
}

function getcount(filter){
    return new Promise((resolve,reject) => {

        var payload = "count";
        if (filter != null){
            payload += "&filter="+filter;
        }

        var xmlh = new XMLHttpRequest();
        xmlh.open('GET', '../riwayat_lamaran.php?' + payload);
        var failsafe = 0;
        xmlh.onreadystatechange = function () {
            if (xmlh.readyState == 4 && xmlh.status == 200) {
                console.log("Count",xmlh.response);
                resolve(xmlh.response);
            }
            else{
                failsafe ++;
                if (failsafe > 4){
                    reject("Cannot establish connection with server!");
                }
            }
        };
        xmlh.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xmlh.send();
    })
    
}


function getdata(limit, skip, filter){
    console.log(skip);
    return new Promise((resolve,reject) => {
        var xmlh = new XMLHttpRequest();

        var payload = "data";
        if (limit != null){
            payload = payload + "&limit=" + limit;
        }
        if (skip != null){
            payload = payload + "&skip=" + skip;
        }
        if (filter != null){
            payload = payload + "&filter=" + filter;
        }

        xmlh.open('GET', '../riwayat_lamaran.php?'+payload);
        var failsafe = 0;
        xmlh.onreadystatechange = function () {
            if (xmlh.readyState == 4 && xmlh.status == 200) {
                resolve(xmlh.response);
            }
            else{
                failsafe ++;
                if (failsafe > 4){
                    reject([]);
                }
            }
        };
        xmlh.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xmlh.send();
    })
}

var pageno = document.getElementById('pageno');
var pagecount = 0;
var pagecountdisplay = document.getElementById('lamar_count')

pageno.addEventListener('input', function() {
    // go away letters, numbers only please
    this.value = this.value.replace(/[^0-9]/g, '');
})
pageno.addEventListener('change',function() {
    populateLamaran(this.value, entry_per_page, active_filter);
})

var leftbutton = document.getElementById('leftbttn');
var rightbutton = document.getElementById('rightbttn');

function pageoffset(ammt){
    if (ammt != 0 && currentPage + ammt >= 1 && currentPage + ammt <= pagecount){
        currentPage = currentPage + ammt;
        populateLamaran(currentPage, w=entry_per_page, active_filter);
    }
    if (currentPage == 1) {
        leftbutton.hidden = true;
    }
    else{
        leftbutton.hidden = false;
    }
    if (currentPage == pagecount){
        rightbutton.hidden = true;
    }
    else{
        rightbutton.hidden = false;
    }
}

function populateLamaran(page, entryPerPage, filter) {
    const lamaranContainer = document.getElementById('lamaran-container');

    if (page > Math.ceil(total_lamaran/entryPerPage) || page < 1){
        console.warn("Incorrect pagination bruh");
        return null;
    }

    //get data first
    getdata(entryPerPage, (page-1) * entryPerPage, filter)
        .then(
        function(response){
            var first = true;
            var data = JSON.parse(response);

            //nuke lamarancontainer
            lamaranContainer.innerHTML = "";

            for (let i = 0; i < data.length; i++) {
                const lamaran = data[i];
                // insert template here
                const lamaranTemplate = `
                    <a class="lamaran" href="../detail_lowongan_jobseeker.php?lowongan_id=${lamaran["lamaran_id"]}">            
                    <span class="corpname">
                        <b> ${lamaran["company_name"]} </b> ${lamaran["posisi"]}
                    </span>
                    <span class="status ${lamaran["status"]}"> <b> ${capitalizeFirst(lamaran["status"])} </b> </span>
                    </a>`;

                if (first){
                    first = false;
                }
                else{
                    lamaranContainer.insertAdjacentHTML('beforeend', '<hr>');    
                }

                // insert the template into the user container
                lamaranContainer.insertAdjacentHTML('beforeend', lamaranTemplate);
            }

            pageno.value = page;
            getcount(filter).then(function(response) {
                total_lamaran = response;
                pagecount = Math.ceil(total_lamaran/entryPerPage);
                pagecountdisplay.innerHTML = pagecount;
                pageoffset(0);
            })
        })
        .catch(
        function(error){

        })
}