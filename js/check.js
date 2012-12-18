function checkGroupMembersOnly() {
    var groupCheckbox = document.getElementById("id_isgrouporganizer");
    var groupMembersOnlyCheckbox = document.getElementById("id_groupmembersonly");
    if(groupCheckbox.checked) {
        groupMembersOnlyCheckbox.checked = true;
    } else {
        groupMembersOnlyCheckbox.checked = false;
    }
}

function toggleAvailableFrom(box) {
	$("select[name^=availablefrom_x]").prop("disabled", !box.checked);
}