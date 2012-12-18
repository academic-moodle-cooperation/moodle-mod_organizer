function checkAll(sender) {
    var checked = sender.checked;    
    var tableRows = $("#slot_overview").find("tbody").find("tr");
        
    tableRows.find(":visible").find(":checkbox").prop("checked", checked ? true : false);
    tableRows.not(":visible").find(":checkbox").prop("checked", false)
    
    $("#slot_overview").find("thead").find(":checkbox").prop("checked", checked ? true : false);
    $("#slot_overview").find("tfoot").find(":checkbox").prop("checked", checked ? true : false);
}