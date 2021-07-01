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

function compare(a,b) {
    if (a.tourstart < b.tourstart)
        return -1;
    if (a.tourstart > b.tourstart)
        return 1;
    return 0;
}

moment.locale('ru');

(function($){
    $(document).ready(function() {
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

        function renderDetails() {
            var shipid = parseUrlQuery('ship');
            //var tourid = parseUrlQuery('tour');
            var ships_url = '/api/ajax/ships.json';
            var ships_img = '/api/ajax/shipsimages.json';
            var deck_img = '/api/ajax/schemes.json';
            var desc_url = '/api/data/' + shipid + '/cabins.json';
            //var tours_url = 'api/db/data/' + shipid + '/tours.json';
            //var route_url = 'api/db/data/' + shipid + '/' + tourid + '.json';


            $.getJSON(ships_url)
                .done(function (data) {
                    $('.shipname').html('<hr><h2>т/х "' + data[shipid] + '"</h2>');
                    $('title').text('т/х "' + data[shipid] + '"');
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
            //var tourid = parseUrlQuery('tour');
            var ship_url = 'https://www.cruise-volna.com/api/ajax/ship.php?shipid=' + shipid;
            //var tour_url = 'https://www.mosturflot.ru/api/ajax/?request=tour&routedetail=true&tariffs=true&loading=true&tourid=' + tourid;

            $.getJSON(ship_url)
                .done(function (data) {
                    var summary = '<li><label>Теплоход:</label>' + data.answer.shipname + '</li><li><label>Кают:</label>' + data.answer.shipcabinsqty + '</li>' + '<li><label>Мест:</label>' + data.answer.shiptoursqty + '</li>';
                    $('#summary').html(summary);
                    var titleimage = '<img src="https://' + data.answer.shiptitleimage + '" width="483" />';
                    $('.shipimg').html(titleimage);
                    $('.shipname').html('<hr><h2>т/х "' + data.answer.shipname + '"</h2>');
                    $('title').text('т/х "' + data.answer.shipname + '"');
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
                                    var img = '/assets/images/mosturflot/' + shipid + '/cabins/' + val.cabincategoryid + '.jpg';
                                    var desc = val.cabindesc.replace(/<.*?>/g, '');

                                    // $.each(val.cabinimages, function (i, el) {
                                    //     console.log(el);
                                    //     if (el.image && el.id == 1) {
                                    //
                                    //     }
                                    //
                                    // });
                                    //console.log(val.cabinimages[Object.keys(val.cabinimages)[0]].image);
                                    //var places = val.cabinplace === undefined ? 0 : val.cabinplace;
                                    cabins[order] = '<tr>' +
                                        '<td><a href="' + img + '" data-lightbox="cabin"><img src="' + img + '" alt="' + val.cabincategoryname + '" /></a></td>' +
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

    });
})(jQuery);
