
var ajax_url = '/api/db/cruises.json';
var booking_url = '/detailes?com=inf&tour=';
var mtf_url = '/detailes?com=mtf&tour=';
var vdh_url = '/detailes?com=vdh&tour=';
var ru_RU = {
    "processing": "Подождите...",
    "search": "Поиск по всей таблице:",
    "lengthMenu": "Показать _MENU_ записей",
    "info": "Записи с _START_ до _END_ из _TOTAL_ записей",
    "infoEmpty": "Записи с 0 до 0 из 0 записей",
    "infoFiltered": "(отфильтровано из _MAX_ записей)",
    "infoPostFix": "",
    "loadingRecords": "Загрузка записей...",
    "zeroRecords": "Записи отсутствуют.",
    "emptyTable": "Нет данных",
    "paginate": {
        "first": "Первая",
        "previous": "Назад",
        "next": "Вперёд",
        "last": "В конец"
    },
    "aria": {
        "sortAscending": ": активировать для сортировки столбца по возрастанию",
        "sortDescending": ": активировать для сортировки столбца по убыванию"
    }
};

var ul = $('#ships-list');

if(ul.length > 0) {
    var mtf_ships = $.getJSON('/api/db/mtfships.json');
    var mtf_ships_images = $.getJSON('/api/db/mtfimages.json');
    var ships = $.getJSON('/api/db/ships.json');
    var ships_images = $.getJSON('api/db/shipsimages.json');

    $.when(mtf_ships, mtf_ships_images, ships, ships_images)
        .done(function (mtf_ships, mtf_ships_images, ships, ships_images) {
            // Executed when both requests complete successfully
            // Both results are available here

            renderShipsList(mtf_ships[0], mtf_ships_images[0], 'mtf');
            renderShipsList(ships[0], ships_images[0], 'inf');

        });
}

function renderShipsList(ships, images, com){
    return $.map(ships, function(ship, i) {
        if(images[i]) {
            var li = $('<li>'),
                img = $('<img>', {'src': images[i], 'width': 150}),
                span = $('<span>'),
                a = $('<a>', { 'href': '/ship?com=' + com + '&ship=' + i, 'html': ship});
            span.append(a);
            li.append(img);
            li.append(span);
            ul.append(li);
        }
    });
}


function compare(a,b) {
    if (a.tourstart < b.tourstart)
        return -1;
    if (a.tourstart > b.tourstart)
        return 1;
    return 0;
}

function parseUrlQuery(q) {
            var res = '';
            if(location.search) {
                var pair = (location.search.substr(1)).split('&');
                for(var i = 0; i < pair.length; i ++) {
                    var param = pair[i].split('=');
                    if(param[0]===q)
                        res = decodeURIComponent(param[1]);
                }
            }
            return res;
        }

$.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {

        if($('#holiday').prop('checked')) {
            var fri = data[0].indexOf('пятница');
            var thu = data[0].indexOf('четверг');
            var weekend = fri + thu;
            console.log(weekend);
            var dd = parseInt(data[2]); // use data for the days column
            if(weekend >= 0 && dd <= 4){
                return true;
            }else{
                return false;
            }
        }
        return true;
    }
);

/* Custom filtering function which will search data in column 2 between two values */
$.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {
        var cruisedays;
        var cruise_days = document.getElementById('cruise-days');
        var get_days = parseUrlQuery('days')===''? 0 : parseInt(parseUrlQuery('days'));
        if (typeof(cruise_days) != 'undefined' && cruise_days != null)
        {
            cruisedays = parseInt(cruise_days.value) === 0? get_days : parseInt(cruise_days.value);
        }

        var dd = parseInt( data[2] ) || 0; // use data for the days column

        if ( cruisedays === 0 ) {
            return true;
        }
        else if ( dd <= cruisedays && cruisedays === 3)
        {
            return true;
        }
        else if( dd <= cruisedays && cruisedays === 7 && dd > 3 )
        {
            return true;
        }
        else if  (cruisedays === 10 && cruisedays <= dd) {
            return true;
        }
        return false;
    }
);

$.fn.dataTableExt.afnFiltering.push(
    function( oSettings, aData, iDataIndex ) {

        var iFini = parseUrlQuery('date_from') === ''? $('#date_from').val() : parseUrlQuery('date_from');
        var iFfin = parseUrlQuery('date_to') === ''? '' : parseUrlQuery('date_to');
        var iStartDateCol = 0;
        var iEndDateCol = 1;
        var loc = 'ru';
        var forma = 'YYYY DD MMM, dddd';

        iFini = '' === iFini ? 0 : parseInt( moment( iFini, 'DD/MM/YYYY', loc, true ).format( 'x' ), 10 );
        iFfin = '' === iFfin ? 0 : parseInt( moment( iFfin, 'DD/MM/YYYY', loc, true ).format( 'x' ), 10 );

        var dfrom = aData[iStartDateCol].replace(/<.*?>/g, '');
        var dend = aData[iEndDateCol].replace(/<.*?>/g, '');

        //console.log(dfrom);
        var datofini= parseInt( moment( dfrom, forma, loc, true ).format( 'x' ), 10 );
        //console.log(dfrom);
        var datoffin= parseInt( moment( dend, forma, loc, true ).format( 'x' ), 10 );

        if ( iFini === 0 && iFfin === 0 )
        {
            return true;
        }
        else if ( iFini <= datofini && iFfin === 0)
        {
            return true;
        }
        else if ( iFfin >= datoffin && iFini === 0)
        {
            return true;
        }
        else if (iFini <= datofini && iFfin >= datoffin)
        {
            return true;
        }
        return false;
    }
);

function renderDetails() {
    var shipid = parseUrlQuery('ship');
    //var tourid = parseUrlQuery('tour');
    var ships_url = '/api/db/data/ships.json';
    var ships_img = '/api/db/data/shipsimages.json';
    var deck_img = '/api/db/data/schemes.json';
    var desc_url = '/api/db/data/' + shipid + '/description.json';
    var cabins_url = '/api/db/data/' + shipid + '/cabins.json';


    $.getJSON(ships_url)
        .done(function (data) {
            $('.shipname').html('<hr><h2>т/х "' + data[shipid] + '"</h2>');
            $("input[name='ship']").val(data[shipid]);
        });
    $.getJSON(ships_img)
        .done(function (data) {
            $('.shipimg').html('<img src="' + data[shipid] + '" width="350" />');
        });
    $.getJSON(deck_img)
        .done(function (data) {
            $('.deckplan').html('<a href="' + data[shipid] + '" data-lightbox="deckplan"><img src="' + data[shipid] + '" width="350" /></a>');
        });
    $.getJSON(desc_url)
        .done(function (data) {
            $('.description').html('<p><br><br>' + data + '</p>');
        });

    /** datatables **/
    var cabins = $('#cabins').DataTable({
        "dom": 'rt',
        "ajax": {
            "url": cabins_url,
            "dataSrc": function (db) {
                var json = db.data;
                var data = [];
                for (var key in json) {
                    if (json.hasOwnProperty(key)) {
                        //json[key].price = '';
                        //json[key].places = '';
                        data.push(json[key]);
                    }
                }

                //data.sort(compare);

                return data;
            }
        },
        "language": ru_RU,
        "columns": [
            {"data": "img", "class": "dt-cell-img"},
            {"data": "type", "class": "dt-cell-cat"},
            {"data": "desc", "class": "dt-cell-desc"}
        ],
        "columnDefs": [
            {
                "targets": [0, 1, 2],
                "sortable": false
            },
            {
                "render": function (data, type, row) {
                    if (row.img === undefined) {
                        return '<img src="https://placehold.it/150x150" height="150" width="150" />';
                    }
                    return '<a href="' + row.img + '" data-lightbox="cabin"><img src="' + row.img + '" height="150" width="100%" /></a>';

                },
                "targets": [0]
            }
        ]

    });//end datatable
}

function renderMTF() {
    var shipid = parseUrlQuery('ship');
    var ship_url = 'https://www.mosturflot.ru/api/ajax/ship.php?shipid=' + shipid;

    $.getJSON(ship_url)
        .done(function (data) {
            var summary = '<li><label>Теплоход:</label>' + data.answer.shipname + '</li><li><label>Кают:</label>' + data.answer.shipcabinsqty + '</li>' + '<li><label>Мест:</label>' + data.answer.shiptoursqty + '</li>';
            $('#summary').html(summary);
            var titleimage = '<img src="https://' + data.answer.shiptitleimage + '" width="483" />';
            $('.shipimg').html(titleimage);
            $('.shipname').html('<hr><h2>т/х "' + data.answer.shipname + '"</h2>');
            var desc = data.answer.shipdesc.replace(/<a.*?>.*<\/a>/g, '');
            desc = desc.replace(/<.*?>/g, '');
            $('.deckplan').html('<a href="https://booking.mosturflot.ru/rivercruises/ships/' + shipid + '/deckplan" data-lightbox="deckplan"><img src="https://booking.mosturflot.ru/rivercruises/ships/' + shipid + '/deckplan" width="350" /></a>');
            $('.description').html('<p><br><br>' + desc + '</p>');
            var cabins = [];
            var categories = [];
            var cabin_names = {
                1: "Люкс с балконом",
                2: "Панорама Люкс с балконом",
                3: "Полулюкс с балконом",
                4: "П/Люкс А с балконом",
                5: "П/Люкс Б с балконом",
                6: "Л с балконом",
                7: "1А с балконом",
                8: "1Б с балконом",
                9: "Люкс",
                10: "П/Люкс",
                11: "ПЛ1",
                12: "ПЛ2",
                13: "ПЛ",
                14: "Л",
                15: "Л1",
                16: "1",
                17: "1А",
                18: "1Б",
                19: "1С",
                20: "2А",
                21: "2Б"
            };
            var order = 0;
            $.each(data.answer.shipcabins, function (key, val) {
                if ($.inArray(val.cabincategoryname, categories) == -1) {

                    $.each(cabin_names, function (x, y) {
                        if (y.toUpperCase() === val.cabincategoryname.toUpperCase()) {
                            //console.log(x);
                            order = x;


                        }
                    });

                    categories.push(val.cabincategoryname);
                    var img = 'www.mosturflot.ru/assets/images/mtf/ships/230x130/cabin.jpg';
                    var desc = val.cabindesc.replace(/<.*?>/g, '');


                    $.each(val.cabinimages, function (i, el) {
                        if (el.image && el.id == 1) {
                            img = el.image;
                        }

                    });
                    cabins[order] = '<tr>' +
                        '<td><a href="https://' + img + '" data-lightbox="cabin"><img src="https://' + img + '" alt="' + val.cabincategoryname + '" /></a></td>' +
                        '<td><span>' + val.cabincategoryname + '</span></td>' +
                        '<td><p>' + desc + '</p></td>' +
                        '</tr>';
                }
            });

            var table = '<table class="table table-striped table-bordered">';
            $.each(cabins, function (m, n) {
                if (n !== undefined) {
                    table += n;
                }
            });

            $('.cabins').html(table + '</table>');
        });
}


(function($){
    $(document).ready(function() {
        /*** all cruises **/
        $('#cruise-days').on('change', function() { rivercruises.draw();} );
        $('#date_from').on('change', function() { rivercruises.draw();} );
        $('#holiday').on('change', function(e){
            //console.log($(this).prop('checked'));
            rivercruises.draw();
        });
        var rivercruises = $('#datatable').DataTable( {
            "dom": '<"dt-top"i>rft<"bottom"p><"clear">',
            "ordering": false,
            "ajax": {
                "url": ajax_url,
                "dataSrc": function ( json ) {
                    var data = [];
                    var now = moment().utc().valueOf();
                    for(var key in json){
                        if(json.hasOwnProperty(key)){
                            var isafter = moment(now).isBefore(json[key].tourstart);
                            if(isafter) {
                                json[key].year = moment(json[key].tourstart, moment.ISO_8601).format('YYYY');
                                json[key].cruisestart = moment(json[key].tourstart, moment.ISO_8601).format('YYYY <br>DD MMM,<br> dddd');
                                json[key].tourfinish = moment(json[key].tourfinish, moment.ISO_8601).format('YYYY <br>DD MMM,<br> dddd');
                                //json[key].tourcabinsfree = json[key].tourcabinsfree==false?'':json[key].tourcabinsfree;
                                data.push(json[key]);
                            }
                        }
                    }

                    data.sort(compare);

                    return data;
                }
            },
            "language":ru_RU,
            "columns": [
                { "data": "cruisestart", "class": "dt-cell-date", responsivePriority: 0 },
                { "data": "tourfinish", "class": "dt-cell-date", responsivePriority: 1 },
                { "data": "tourdays", "class": "dt-cell-days", responsivePriority: 4 },
                { "data": "shipname", "class": "dt-cell-ship", responsivePriority: 2 },
                { "data": "tourroute", "class": "dt-cell-route", responsivePriority: 3 },
                { "data": "tourminprice", "class": "dt-cell-price", responsivePriority: 5 },
                { "data": "tourcabinsfree", "class": "dt-cell-cabins", responsivePriority: 6 }
            ],
            "columnDefs": [
                {
                    "targets": [1, 2, 3, 4, 5, 6],
                    "sortable": false
                },
                {
                    "render": function ( data, type, row ) {

                        return '<div>' + row.cruisestart + '</div>';

                    },
                    "targets": [0]
                },
                {
                    "render": function ( data, type, row ) {

                        return '<div>' + row.tourfinish + '</div>';

                    },
                    "targets": [1]
                },
                {
                    "render": function ( data, type, row ) {

                        var link = booking_url;

                        if(row.company === 'mtf'){
                            link = mtf_url;
                        }

                        if(row.company === 'vdh'){
                            link = vdh_url;
                        }

                        return '<a href="' + link + row.tourid + '&ship=' + row.shipid + '">' + row.tourroute + '</a>';

                    },
                    "targets": [4]
                },
                {
                    "render": function ( data, type, row ) {
                        if(row.tourcabinsfree||row.tourcabinsfree>0){
                            return row.tourcabinsfree;
                        }

                        return '<img src="/assets/images/icons/call-center.svg" />';

                    },
                    "targets": [6]
                }
            ]/**,
            "fnRowCallback": function( nRow , aData) {
                $(nRow).on('click', function() {
                    window.location.href = booking_url+aData.tourid+'&ship='+aData.shipid;
                });
            }*/
            ,
            "initComplete": function () {
                this.api().columns(3).every( function () {
                    var column = this;
                    var get_ship = parseUrlQuery('ship');
                    if (get_ship.length > 0 ) {
                        if(get_ship.indexOf(',') > 0){
                            var s_ships = get_ship.split(',');
                            var reg_ships = s_ships.join('|');
                            column.search( reg_ships, true, false ).draw();
                        }else{
                            column.search( get_ship ).draw();
                        }
                    }
                    var select = $('#ships')
                        .on( 'change', function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );

                            column
                                .search( val ? '^'+val+'$' : '', true, false )
                                .draw();
                        } );
                    column.data().unique().sort().each( function ( d, j ) {
                        select.append( '<option value="'+d+'">'+d+'</option>' )
                    } );
                });
                this.api().columns(0).every( function () {
                    var column = this;
                    var get_season = parseUrlQuery('season')===''?$('#season').val():parseUrlQuery('season');
                    $('#season').on("change", function(){
                        get_season = $('#season').val();
                        column.search( get_season ).draw();
                    });
                    if (get_season.length > 0) {
                        column.search( get_season ).draw();
                    }
                });
                this.api().columns(4).every( function () {
                    //console.log($('#city').val());
                    var col = this;
                    var get_city = parseUrlQuery('city')===''?$('#city').val():parseUrlQuery('city');
                    if (get_city.length > 0) {
                        //console.log(get_city);
                        col.search( get_city ).draw();
                    }
                    $('#city')
                        .on( 'change', function () {
                            col.search($('#city').val() ).draw();
                            //console.log(city);
                        } );
                });
            }

        } );

        var rivercruises = $('#spec').DataTable( {
            "dom": '<"dt-top">rft<"bottom"p><"clear">',
            "ordering": false,
            "ajax": {
                "url": ajax_url,
                "dataSrc": function ( json ) {
                    var data = [];
                    for(var key in json){
                        if(json.hasOwnProperty(key)){
                            json[key].year = moment(json[key].tourstart, moment.ISO_8601).format('YYYY');
                            json[key].cruisestart = moment(json[key].tourstart, moment.ISO_8601).format('YYYY <br>DD MMM,<br> dddd');
                            json[key].tourfinish = moment(json[key].tourfinish, moment.ISO_8601).format('YYYY <br>DD MMM,<br> dddd');
                            data.push(json[key]);
                        }
                    }

                    data.sort(compare);

                    return data;
                }
            },
            "language":ru_RU,
            "columns": [
                { "data": "cruisestart", "class": "dt-cell-date", responsivePriority: 0 },
                { "data": "tourdays", "class": "dt-cell-days", responsivePriority: 4 },
                { "data": "shipname", "class": "dt-cell-ship", responsivePriority: 2 },
                { "data": "tourroute", "class": "dt-cell-route", responsivePriority: 3 }
            ],
            "columnDefs": [
                {
                    "targets": [0, 1, 2, 3],
                    "sortable": false
                },
                {
                    "render": function ( data, type, row ) {

                        return '<div>' + row.cruisestart + '</div>';

                    },
                    "targets": [0]
                },
                {
                    "render": function ( data, type, row ) {

                        var link = booking_url;

                        if(row.company === 'mtf'){
                            link = mtf_url;
                        }

                        if(row.company === 'vdh'){
                            link = vdh_url;
                        }

                        return '<a href="' + link + row.tourid + '&ship=' + row.shipid + '">' + row.tourroute + '</a>';

                    },
                    "targets": [3]
                },
            ],
            "initComplete": function () {
                this.api().columns(2).every( function () {
                    var column = this;
                    var reg_ships = 'Солнеч|Фурманов|озеро|соната';
                    column.search( reg_ships, true, false ).draw();


                });
            }

        } );

        var com = parseUrlQuery('com');
        switch(com){
            case 'mtf':
                renderMTF();
                break;
            case 'inf':
                renderDetails();
                break;
            default:
                break;
        }

    } );
})(jQuery);
