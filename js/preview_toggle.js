function toggleColumn(sender, column) {
    var col = document.getElementById("col_" + column);
    col.checked = sender.checked;
    
    var cols = document.getElementsByName(column + "_cell");
    for(var i = 0; i < cols.length; i++) {
        cols[i].style.display = sender.checked ? "" : "none";
    }
}