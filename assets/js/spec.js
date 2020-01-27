moment.locale('ru');
var ajax_url = '/api/db/cruises.json';
var booking_url = '/detailes?com=inf&tour=';
var mtf_url = '/detailes?com=mtf&tour=';
var vdh_url = '/detailes?com=vdh&tour=';
var ru_RU = {
    "processing": "Подождите...",
    "search": "Поиск:",
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


(function($){
    $(document).ready(function() {
        /*** all cruises **/

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

    } );
})(jQuery);
