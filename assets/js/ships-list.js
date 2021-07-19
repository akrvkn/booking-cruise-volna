var ships_url = '/api/ajax/ships.json';
var ships_img = '/api/ajax/shipsimages.json';
var mtf_url = '/api/ajax/mtfships.json';
var mtf_img = '/api/ajax/mtfimages.json';
var don_ships = '/api/ajax/don-ships.json';

//e0a97b55-9c82-40c6-a946-b5acca183999
//?token=token&controller=ship&action=getCollection
var ul = $('#ships-list');

var mtf_ships = $.getJSON(mtf_url);
var mtf_ships_images = $.getJSON(mtf_img);
var ships = $.getJSON(ships_url);
var ships_images = $.getJSON(ships_img);
var don_get = $.getJSON(don_ships);

$.when(mtf_ships, mtf_ships_images, ships, ships_images, don_get)
    .done(function(mtf_ships, mtf_ships_images, ships, ships_images, don) {
        // Executed when both requests complete successfully
        // Both results are available here
        renderMtfShipsList(mtf_ships[0], mtf_ships_images[0], 'mtf');
        renderShipsList(ships[0], ships_images[0], 'inf');
        renderDonShipsList(don[0])
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
                a = $('<a>', { 'href': '/ship?com=' + com + '&ship=' + i, 'html': ship}),
                ai = $('<a>', { 'href': '/ship?com=' + com + '&ship=' + i, 'html': img});
            span.append(a);
            li.append(ai);
            li.append(span);
            ul.append(li);
        }
    });
}

function renderDonShipsList(ships){
    //console.log(ships);
    return $.map(ships['DATA'], function(ship, i) {
        if(ship['CODE'] === 'bunin') {
            var li = $('<li>'),
                img = $('<img>', {'src': ship['MAIN_VIEW'][0]['IMAGE']['URL'], 'width': 150}),
                span = $('<span>'),
                a = $('<a>', { 'href': '/ship?com=don&ship=' + i, 'html': ship['NAME']}),
                ai = $('<a>', { 'href': '/ship?com=don&ship=' + i, 'html': img});
            span.append(a);
            li.append(ai);
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
                a = $('<a>', { 'href': '/ship?com=' + ship['company'] + '&ship=' + ship['shipid'], 'html': ship['shipname']}),
                ai = $('<a>', { 'href': '/ship?com=' + ship['company'] + '&ship=' + ship['shipid'], 'html': img});
            span.append(a);
            li.append(ai);
            li.append(span);
            ul.append(li);
        }
    });
}
