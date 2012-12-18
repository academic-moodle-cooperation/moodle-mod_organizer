function toggleAll(sender) {
    var checked = sender.checked;    
    var senderClass = sender.getAttribute("class").match(/allow\d+/g)[0];
    
    $(":input." + senderClass).val(checked ? 1 : 0);
}