/**
 * Select a monthly calendar with javascript
 * 
 * @package rabe
 * @since version 1.0.0
 * @link http://stackoverflow.com/questions/985272/selecting-text-in-an-element-akin-to-highlighting-with-your-mouse
 */
function SelectText(element) {
    var doc = document
        , text = doc.getElementById(element)
        , range, selection
    ;    
    if (doc.body.createTextRange) {
        range = document.body.createTextRange();
        range.moveToElementText(text);
        range.select();
    } else if (window.getSelection) {
        selection = window.getSelection();        
        range = document.createRange();
        range.selectNodeContents(text);
        selection.removeAllRanges();
        selection.addRange(range);
    }
}

document.onclick = function(e) {    
    if (e.target.className === 'select-calendar') {
        SelectText('printable_calendar');
    }
};
