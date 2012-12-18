function openNewWindow(url) {
    var boxes = $("input.checkbox_slot");

    var anythingchecked = false;
    for(var i = 0; i < boxes.length; i++) {
        if(boxes[i].checked) {
            anythingchecked = true;
            url += "&slots[]=" + boxes[i].value;
        }
    }
    
    window.open(url, anythingchecked ? "_blank" : "_self");
}