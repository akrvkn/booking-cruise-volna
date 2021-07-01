var ships_url = '/api/ajax/ships.json';
var ships_img = '/api/ajax/shipsimages.json';
var mtf_url = '/api/ajax/mtfships.json';
var mtf_img = '/api/ajax/mtfimages.json';

var ul = $('#ships-list');

var mtf_ships = $.getJSON(mtf_url);
var mtf_ships_images = $.getJSON(mtf_img);
var ships = $.getJSON(ships_url);
var ships_images = $.getJSON(ships_img);

$.when(mtf_ships, mtf_ships_images, ships, ships_images)
    .done(function(mtf_ships, mtf_ships_images, ships, ships_images) {
        // Executed when both requests complete successfully
        // Both results are available here
        renderMtfShipsList(mtf_ships[0], mtf_ships_images[0], 'mtf');
        renderShipsList(ships[0], ships_images[0], 'inf');
        //console.log(ships[0]);
    })
    .fail(function() {
        console.log('error');
    });
function renderMtfShipsList(ships, images, com){
    return $.map(ships, function(ship, i) {
        if(images[i]) {
            var li = $('<li>'),
                img = $('<img>', {'src': '/assets/images/mosturflot/' + i + '.jpg', 'width': 150}),
                span = $('<span>'),
                a = $('<a>', { 'href': '/ship?com=' + com + '&ship=' + i, 'html': ship});
            span.append(a);
            li.append(img);
            li.append(span);
            ul.append(li);
        }
    });
}

function renderShipsList(ships, images, com){
    return $.map(ships, function(ship, i) {
        //console.log(ship);
        if(images[i] && ship['company'] === 'iff') {
            var li = $('<li>'),
                img = $('<img>', {'src': images[i], 'width': 150}),
                span = $('<span>'),
                a = $('<a>', { 'href': '/ship?com=' + ship['company'] + '&ship=' + ship['shipid'], 'html': ship['shipname']});
            span.append(a);
            li.append(img);
            li.append(span);
            ul.append(li);
        }
    });
}
