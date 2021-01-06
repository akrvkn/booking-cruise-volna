moment.locale('ru');
var ajax_url = 'https://www.cruise-volna.ru/api/ajax/cruises.json';
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

function getUrlParams (uri) {
    // http://stackoverflow.com/a/23946023/2407309
    if (typeof uri == 'undefined') {
        uri = window.location.search
    }
    var url = uri.split('#')[0]; // Discard fragment identifier.
    var urlParams = {};
    var queryString = url.split('?')[1];
    if (!queryString) {
        if (url.search('=') !== false) {
            queryString = url
        }
    }
    if (queryString) {
        var keyValuePairs = queryString.split('&');
        for (var i = 0; i < keyValuePairs.length; i++) {
            var keyValuePair = keyValuePairs[i].split('=');
            var paramName = keyValuePair[0];
            var paramValue = keyValuePair[1] || '';
            urlParams[paramName] = decodeURIComponent(paramValue.replace(/\+/g, ' '))
        }
    }
    return urlParams;
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

                        return '<a href="' + link + row.tourid + '&ship=' + row.shipid + '" target="_blank">' + row.tourroute + '</a>';

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

    } );
})(jQuery);
