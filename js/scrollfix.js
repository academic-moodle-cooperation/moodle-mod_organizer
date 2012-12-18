function saveScroll() {
    var form = document.getElementById("mform1");
    form.scrollx.value = document.body.scrollLeft || document.documentElement.scrollLeft;
    form.scrolly.value = document.body.scrollTop || document.documentElement.scrollTop;
}

window.onload = function() {
    setTimeout(function() {
        var form = document.getElementById("mform1");
        if (typeof form.scrollx.value != "undefined" && typeof form.scrolly.value != "undefined") {
                window.scrollTo(form.scrollx.value, form.scrolly.value);
        }
    }, 100);
}

