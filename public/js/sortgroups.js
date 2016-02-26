/* global Sortable */

$(window).load(function () {
    var el = document.getElementById('items');
    var sortable = new Sortable(el, {
        group: 'groups',
        animation: 150,
        scroll: true,
        scrollSensitivity: 30,
        scrollSpeed: 10
    });
    
    var el = document.getElementById('elements');
    var sortable = new Sortable(el, {
        group: 'groups',
        animation: 150,
        scroll: true,
        scrollSensitivity: 30,
        scrollSpeed: 10
    });
});