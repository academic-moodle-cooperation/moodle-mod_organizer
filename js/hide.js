function togglePastSlots() {
    var tBody = $("#slot_overview").find("tbody");
    
    var showPastSlots = $("#show_past_slots").is(":checked");
    var showMySlotsOnly = $("#show_my_slots_only").is(":checked");
    
    if(showPastSlots) {
        if(showMySlotsOnly) {
            tBody.find("tr.past_due.my_slot").show();
        } else {
            tBody.find("tr.past_due").show();
        }
    } else {
        tBody.find("tr.past_due").hide();
    }
    
    toggleInfo();
    
    var xmlhttp;
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    
    xmlhttp.open("GET", "showhide.php?showpastslots=" + (showPastSlots ? "1" : "0"), true);
    xmlhttp.send();
}

function toggleOtherSlots() {
    var tBody = $("#slot_overview").find("tbody");
    
    var showPastSlots = $("#show_past_slots").is(":checked");
    var showMySlotsOnly = $("#show_my_slots_only").is(":checked");
    
    if(!showMySlotsOnly) {
        if(showPastSlots) {
            tBody.find("tr").not("tr.info").show();
        } else {
            tBody.find("tr").not("tr.info").not("tr.past_due").show();
        }
    } else {
        tBody.find("tr").not("tr.info").not("tr.my_slot").hide();
    }
    
    toggleInfo();
    
    var xmlhttp;
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    
    xmlhttp.open("GET", "showhide.php?showmyslotsonly=" + (showMySlotsOnly ? "1" : "0"), true);
    xmlhttp.send();
}

function toggleLegend() {
    var legend = $("#infobox_legend_box");

    if(legend.is(":visible")) {
        legend.hide();
    } else {
        legend.show();
    }
}

function toggleInfo() {
    var tBody = $("#slot_overview").find("tbody");
    var noneExist = tBody.find("tr").not("tr.info").length == 0;
    var anyVisible = tBody.find("tr").not("tr.info").is(":visible");
    
    var showPastSlots = $("#show_past_slots").is(":checked");
    var showMySlotsOnly = $("#show_my_slots_only").is(":checked");
    
    tBody.find("tr.info").hide();
    if(!anyVisible) {
        if(noneExist) {
            tBody.find("tr.no_slots_defined").show();
        } else if(showPastSlots && !showMySlotsOnly) {
            tBody.find("tr.no_slots").show();
        } else if(showPastSlots && showMySlotsOnly) {
            tBody.find("tr.no_my_slots").show();
        } else if(!showPastSlots && showMySlotsOnly) {
            tBody.find("tr.no_due_my_slots").show();
        } else {
            tBody.find("tr.no_due_slots").show();
        }
    }
}