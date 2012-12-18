function detectChange(sender) {
    var name = sender.name;
    if(name == "now") {
        name = "availablefrom";
    }

    var modfields = $("input[name='mod_" + name + "']");
    if(modfields.length != 0) {
        if(modfields[0].value == 1) {
            return;
        }
        modfields[0].value = 1;
    }

    var modicons = $("img[name='" + name + "_warning']");
    if(modicons.length != 0) {
        modicons[0].src = modicons[0].src.split(".png", 1)[0] + "2.png";
        modicons[0].title = $("input[name='warningtext2']")[0].value;
    }
}


function toggleAvailableFrom(sender) {
    var parent = $(sender).parent();
    $(":input[name^='availablefrom']").not(":input[name^='availablefrom[now]']").attr("disabled", sender.checked);
    if(sender.checked) {
        $("<input />")
            .attr("type", "hidden")
            .attr("name", "availablefrom")
            .val("0")
            .appendTo(parent);
    } else {
        $(":hidden[name^='availablefrom']").remove();
    }
}

function detectChangeSelect(sender) {
    var name = sender.name;
    
    var modfields = document.getElementsByName("mod_" + name);
    var modicons = document.getElementsByName(name + "_warning");

    if(modfields[0].value == 1) {
        var sel = sender.options.selectedIndex;
        if(sender.options[sel].value == -1) {
            modfields[0].value = 0;

            modicons[0].src = modicons[0].src.split("warning", 1)[0] + "warning.png";
            modicons[0].title = document.getElementsByName("warningtext1")[0].value;
        }
        return;
    }
    modfields[0].value = 1;

    modicons[0].src = modicons[0].src.split("warning", 1)[0] + "warning2.png";
    modicons[0].title = document.getElementsByName("warningtext2")[0].value;
}

function detectChangeDuration(sender) {
    var name = sender.name.split("[")[0];

    var modfields = document.getElementsByName("mod_" + name); 
    if(modfields.length != 0) {
        modfields[0].value = 1;
    }

    var modicons = document.getElementsByName(name + "_warning");
    if(modicons.length != 0) {
        modicons[0].src = modicons[0].src.split("warning", 1)[0] + "warning2.png";
        modicons[0].title = document.getElementsByName("warningtext2")[0].value;
    }
}

function resetEditForm() {
    $("input[name^='mod_']").val(0);
    
    var defaultIconSrc = $("img[name$='_warning']").attr("src").split("warning", 1)[0] + "warning.png";
    var defaultTitle = $("img[name$='warningtext1']").val();
    
    $("img[name$='_warning']")
        .attr("src", defaultIconSrc)
        .attr("title", defaultTitle);
}