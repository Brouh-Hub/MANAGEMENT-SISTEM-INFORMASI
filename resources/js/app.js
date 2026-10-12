import $ from 'jquery';

window.$ = $;
window.jQuery = $;

$(function () {
    console.log('jQuery loaded');
});

$.ajax({
    url: '/dashboard/data',
    type: 'GET',
    dataType: 'json',
    success: function (response) {
        console.log(response);
    },
    error: function (xhr) {
        console.error(xhr);
    }
});
