function showPopup(e, title, content) {
    var posx = 0;
    var posy = 0;

    if (!e) {
    	var e = window.event;
    }
    
    if (e.pageX || e.pageY) {
        posx = e.pageX;
        posy = e.pageY;
    } else if (e.clientX || e.clientY) {
        posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
        posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
    }
    
    var titleBox = document.getElementById("organizer_popup_title");
    titleBox.innerHTML = "<h4>" + title + "</h4>";
    
    var contentBox = document.getElementById("organizer_popup_content");
    contentBox.innerHTML = "<br /><p>" + content + "</p>";
    
    $("#organizer_popup").show();
    
//    var popupBox = document.getElementById("organizer_popup");
//    popupBox.style.left = posx + 8;
//    popupBox.style.top = posy + 8;
//    popupBox.style.display = "";
    
    $("#organizer_popup").offset({left : posx, top: posy});
    
}


function hidePopup() {
	$("#organizer_popup").hide();
}