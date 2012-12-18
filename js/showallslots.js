function showMySlotsOnly(sender) {
    var show = sender;//document.getElementById("show_my_slots_only");
    var slots = document.getElementsByName("due my_slot");
    //slots.concat(document.getElementsByName("past_due my_slot"));
    //slots.concat(document.getElementsByName("my_slot"));
    for(var i = 0; i < slots.length; i++) {
        if(!show.checked) {
            slots[i].style.display = "";
        } else {
            slots[i].style.display = "none";
        }
    }

    slots = document.getElementsByName("my_slots_info");
    for(i = 0; i < slots.length; i++) {
        if(!show.checked) {
            slots[i].style.display = "none";
        } else {
            slots[i].style.display = "";
        }
    }
    
    var xmlhttp;
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    
    var userid = document.getElementById("userid").value;
    
    xmlhttp.open("GET", "showhide.php?showmyslotsonly=" + (show.checked ? "1" : "0") + "&userid=" + userid, true);
    xmlhttp.send();
}
