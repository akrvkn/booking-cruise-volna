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
            var tourid = parseUrlQuery('tour');
            var ships_url = 'api/db/ships.json';
            var ships_img = 'api/db/shipsimages.json';
            var deck_img = 'api/db/schemes.json';
            var desc_url = 'api/db/data/' + shipid + '/description.json';
            var cabins_url = 'api/db/data/' + shipid + '/cabins.json';
            var tours_url = 'api/db/data/' + shipid + '/tours.json';
            var route_url = 'api/db/data/' + shipid + '/' + tourid + '.json';


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

            $.getJSON(tours_url)
                .done(function (tours) {
                    $('.cruise-meta').append('<li>' + tours[tourid]['cities'] + '</li>').append('<li>Дата круиза: ' + tours[tourid]['date_start'] + ' - ' + tours[tourid]['date_end'] + ' ( ' + tours[tourid]['days'] + ' дн.)' + ' </li>');
                    $("input[name='tour']").val(tours[tourid]['cities']);
                    $("input[name='date']").val(tours[tourid]['date_start']);

                    var cabins = $('#cabins').DataTable({
                        "dom": 'rt',
                        "ajax": {
                            "url": cabins_url,
                            "dataSrc": function (db) {
                                var json = db.data;
                                var data = [];
                                for (var key in json) {
                                    if (json.hasOwnProperty(key)) {
                                        json[key].price = '';
                                        json[key].places = '';
                                        data.push(json[key]);
                                    }
                                }
                                return data;
                            }
                        },
                        "language": ru_RU,
                        "columns": [
                            {"data": "img", "class": "dt-cell-img"},
                            {"data": "type", "class": "dt-cell-cat"},
                            {"data": "desc", "class": "dt-cell-desc"},
                            {"data": "price", "class": "dt-cell-price"},
                            {"data": "places", "class": "dt-cell-places"}
                        ],
                        "columnDefs": [
                            {
                                "targets": [0, 1, 2, 4],
                                "sortable": false
                            },
                            {
                                "render": function (data, type, row) {
                                    if (row.img === undefined) {
                                        return '<img src="https://placehold.it/150x150" width="150" />';
                                    }
                                    return '<a href="' + row.img + '" data-lightbox="cabin"><img src="' + row.img + '" width="100%" /></a>';

                                },
                                "targets": [0]
                            }
                        ],
                        "fnRowCallback": function (nRow, aData) {
                            $.each(tours[tourid]['prices'], function (key, val) {
                                if (aData.type === val.name) {
                                    aData.price = val.price;
                                    aData.places = val.places_free;
                                    $('.dt-cell-price', nRow).html(val.price);
                                    $('.dt-cell-places', nRow).html(val.places_free);

                                }
                            });
                        }

                    });//end datatable
                });//end tours callback

            $.getJSON(route_url)
                .done(function (data) {
                    //console.log(data);
                    if (data.length !== 0) {
                        $('.program').prepend('<h2>Программа круиза</h2>');
                        var program = $('#program').DataTable({
                            "dom": 'rt',
                            "searching": false,
                            "ordering": false,
                            "ajax": {
                                "url": route_url,
                                "dataSrc": function (json) {
                                    var data = [];
                                    for (var key in json) {
                                        if (json.hasOwnProperty(key)) {
                                            json[key].tourstart = moment(json[key].date_start + ' ' + json[key].time_start, 'DD.MM.YYYY HH:mm').format('YYYY-MM-DD HH:mm');
                                            data.push(json[key]);
                                        }
                                    }
                                    data.sort(compare);
                                    return data;
                                }
                            },
                            "language": ru_RU,
                            "columns": [
                                {"data": "city", "class": "dt-cell-city", "title": "Остановка"},
                                {"data": "date_start", "class": "dt-cell-cat", "title": "Дата прибытия"},
                                //{"data": "time_start", "class": "dt-cell-desc", "title": "Время прибытия"},
                                {"data": "date_end", "class": "dt-cell-price", "title": "Дата отхода"},
                                //{"data": "time_end", "class": "dt-cell-places", "title": "Время отхода"},
                                {"data": "description", "class": "dt-cell-places", "title": "Программа"}
                            ]

                        });//end datatable
                    }
                });
        }

        function renderMTF() {
            var shipid = parseUrlQuery('ship');
            var tourid = parseUrlQuery('tour');
            var ship_url = 'https://www.mosturflot.ru/api/ajax/ship.php?shipid=' + shipid;
            var tour_url = 'https://www.mosturflot.ru/api/ajax/?request=tour&routedetail=true&tariffs=true&loading=true&tourid=' + tourid;

            $.getJSON(ship_url)
                .done(function (data) {
                    var summary = '<li><label>Теплоход:</label>' + data.answer.shipname + '</li><li><label>Кают:</label>' + data.answer.shipcabinsqty + '</li>' + '<li><label>Мест:</label>' + data.answer.shiptoursqty + '</li>';
                    $('#summary').html(summary);

                    var titleimage = '<img src="https://' + data.answer.shiptitleimage + '" width="483" />';
                    //$('#shiptitleimage').html(titleimage);
                    $('.shipimg').html(titleimage);
                    $('.shipname').html('<hr><h2>т/х "' + data.answer.shipname + '"</h2>');
                    //$('#shipname').html('Теплоход ' + data.answer.shipname);



                    /** Description */
                        //$('.long-description').html(data.answer.shipdesc);
                    var desc = data.answer.shipdesc.replace(/<a.*?>.*<\/a>/g, '');
                    desc = desc.replace(/<.*?>/g, '');
                    $('.description').html('<p><br><br>' + desc + '</p>');
                    //$('.entry-title').html('Теплоход ' + data.answer.shipname);
                    //$('#deckplan').attr('src', 'https://'+ data.answer.shipdeckplan);

                    $.getJSON(tour_url)
                        .done(function (json) {
                            $("input[name='ship']").val(data.answer.shipname);
                            $("input[name='tour']").val(json.answer.tourroute);
                            $("input[name='date']").val(moment(json.answer.tourstart, moment.ISO_8601).format('DD.MM.YYYY'));
                            $('.deckplan').html('<a href="https://booking.mosturflot.ru/rivercruises/ships/' + shipid + '/deckplan" data-lightbox="deckplan"><img src="https://booking.mosturflot.ru/rivercruises/ships/' + shipid + '/deckplan" width="350" /></a>');
                            $('.cruise-meta').append('<li>' + json.answer.tourroute + '</li>').append('<li>Дата круиза: ' + moment(json.answer.tourstart, moment.ISO_8601).format('DD.MM.YYYY') + ' - ' + moment(json.answer.tourfinish, moment.ISO_8601).format('DD.MM.YYYY') + ' ( ' + json.answer.tourdays + ' дн.)' + ' </li>');
                            var ex = '';
                            //console.log(json.answer.tourroutedetail);

                            $.each(json.answer.tourroutedetail, function (i, k) {
                                //console.log(k.arrival);
                                var excurs = '';
                                $.each(k.excursions, function (e, n) {
                                    excurs = n.desc.replace(/<.*?>/g, '');
                                });
                                var arrival = k.arrival === false ? ' - ' : moment(k.arrival, moment.ISO_8601).format('DD.MM.YYYY h:mm');
                                var departure = k.departure === false ? ' - ' : moment(k.departure, moment.ISO_8601).format('DD.MM.YYYY h:mm');
                                ex += '<tr><td>' + k.cityname + '</td>' +
                                    '<td>' + arrival + '</td>' +
                                    '<td>' + departure + '</td>' +
                                    '<td>' + excurs + '</td></tr>'
                            });
                            $('#program').append(ex);
                            //console.log(json.answer.tourloading);
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

                                    $.each(json.answer.tourtariffs, function (k, v) {
                                        if (v.categoryname.toUpperCase() === val.cabincategoryname.toUpperCase()) {
                                            val.cabinprice = v.categoryminprice;
                                        }
                                    });

                                    $.each(json.answer.tourloading, function (t, m) {
                                        if (m.cabincategoryname.toUpperCase() === val.cabincategoryname.toUpperCase() && m.cabinstatus > 0) {
                                            if (val.cabinplace) {
                                                val.cabinplace += m.cabinstatus;
                                            } else {
                                                val.cabinplace = m.cabinstatus;
                                            }
                                            //console.log(val.cabinplace);
                                        }
                                    });

                                    categories.push(val.cabincategoryname);
                                    var img = 'www.mosturflot.ru/api/images/mtf/ships/230x130/cabin.jpg';
                                    var desc = val.cabindesc.replace(/<.*?>/g, '');


                                    $.each(val.cabinimages, function (i, el) {
                                        if (el.image && el.id == 1) {
                                            img = el.image;
                                        }

                                    });
                                    //console.log(val.cabinimages[Object.keys(val.cabinimages)[0]].image);
                                    var places = val.cabinplace === undefined ? 0 : val.cabinplace;
                                    cabins[order] = '<tr>' +
                                        '<td><a href="https://' + img + '" alt="' + val.cabincategoryname + '" data-lightbox="cabin"><img src="https://' + img + '" alt="' + val.cabincategoryname + '"></a></td>' +
                                        '<td><span>' + val.cabincategoryname + '</span></td>' +
                                        '<td><p>' + desc + '</p></td>' +
                                        '<td><span>' + val.cabinprice + '</span></td>' +
                                        '<td>' + places + '</td>' +
                                        '</tr>';
                                }
                            });

                            var table = '<table class="table table-striped table-bordered">';
                            $.each(cabins, function (m, n) {
                                console.log(n);
                                if (n !== undefined) {
                                    table += n;
                                }
                            });

                            $('.cabins').html(table + '</table>');
                        });
                });
        }

        /**function renderVDH() {
            var tourid = parseUrlQuery('tour');
            var tour_url = '/api/ajax/?vdh=cruise/' + tourid;
            var cabin_img_url = '/api/ajax/?vdh=room_group_img/';
            var cabins = {};
            $.getJSON(tour_url)
                .done(function (data) {
                    $('.shipname').html('<hr><h2>т/х "' + data.cruise.ship + '"</h2>');

                    $('.cruise-meta').append('<li>' + data.cruise.route + '</li>').append('<li>Дата круиза: ' + moment.unix(data.cruise.date_start).format('DD.MM.YYYY') + ' - ' + moment.unix(data.cruise.date_stop).format('DD.MM.YYYY') + ' </li>');

                    $("input[name='ship']").val(data.cruise.ship);
                    $("input[name='tour']").val(data.cruise.route);
                    $("input[name='date']").val(moment.unix(data.cruise.date_start).format('DD.MM.YYYY'));

                    $.getJSON('/api/db/vdhimages.json')
                        .done(function (json) {
                            var titleimage = '<img src="' + json[data.cruise.ship] + '" width="483" />';
                            $('.shipimg').html(titleimage);
                        });

                    $.each(data.prices, function (key, val) {
                        $.each(val, function (ke, va) {
                            $.each(va, function (k, v) {

                                if (typeof (cabins[v.room_group_id]) !== 'object') {
                                    cabins[v.room_group_id] = {
                                        'available': 0,
                                        'room_type': v.room_type,
                                        'price': parseInt(v.price),
                                        'name_place': v.name_place,
                                        'image': '',
                                        'title': ''
                                    };
                                    $.getJSON(cabin_img_url + v.room_group_id).done(function (img) {
                                        var img_src = '';
                                        if (img.length > 0) {
                                            img_src = img[0].url;
                                        }
                                        cabins[v.room_group_id]['image'] = img_src;
                                        $('#' + v.room_group_id).attr('src', img_src);
                                        $('#a' + v.room_group_id).attr('href', img_src);
                                    });
                                }
                                cabins[v.room_group_id]['available'] += v.available_rooms;
                                cabins[v.room_group_id]['room_type'] = v.room_type;
                                cabins[v.room_group_id]['name_place'] = v.name_place;

                            });

                        });

                    });
                    renderCabins(cabins);

                    function renderCabins(cab) {
                        return $.each(cab, function (i, o) {
                            var av = o.available === 0? '' : o.available;
                            //console.log(o.image);
                            var cabins_table = '<tr>' +
                                '<td><a href="" id="a' + i + '" data-lightbox="cabin"><img id="' + i + '" src="' + o.image + '" width="150" /></a></td>' +
                                '<td>' + o.room_type + '</td>' +
                                '<td>' + o.room_type + '</td>' +
                                '<td>' + o.price + '</td>' +
                                '<td>' + av + '</td>' +
                                '</tr>';
                            $('#cabins').append(cabins_table);
                        });
                    }


                    var ex = '';
                    $.each(data.programm, function (id, row) {
                        ex = '<tr><td width="100">' + row.place + '</td>' +
                            '<td width="120">' + moment.unix(row.date_start).format('DD.MM.YYYY<br> HH:mm') + '</td>' +
                            '<td width="120">' + moment.unix(row.date_stop).format('DD.MM.YYYY<br> HH:mm') + '</td>' +
                            '<td>' + row.description + '</td></tr>';
                        $('#program').append(ex);
                    });

                });
        }*/


        function renderDetailsVDH() {
            var shipid = parseUrlQuery('ship');
            var tourid = parseUrlQuery('tour');
            var ships_url = 'api/db/data/vdh/motorships.json';
            var tour_url = 'api/db/data/vdh/' + shipid + '/' + tourid + '.json';


            $.getJSON(ships_url)
                .done(function (data) {
                    $('.shipname').html('<hr><h2>т/х "' + data[shipid]['name'] + '"</h2>');
                    $("input[name='ship']").val(data[shipid]['name']);
                    $('.shipimg').html('<img src="https://vodohod.com/cruises/vodohod/'+ data[shipid]['code'] + '/' + data[shipid]['code'] + '-description-main.jpg" width="350" />');
                    $('.deckplan').html('<a href="' + data[shipid]['decks'] + '" data-lightbox="deckplan"><img src="' + data[shipid]['decks'] + '" width="350" /></a>');
                    $('.description').html( data[shipid]['description']);
                });

            $.getJSON(tour_url)
                .done(function (tours) {
                    $('.cruise-meta').append('<li>' + tours['name'] + '</li>').append('<li>Дата круиза: ' + moment(tours['dateStart']).format('DD.MM.YYYY') + ' - ' + moment(tours['dateStop']).format('DD.MM.YYYY') + ' ( ' + tours['days'] + ' дн.)' + ' </li>');
                    $("input[name='tour']").val(tours['name']);
                    $("input[name='date']").val(tours['dateStart']);
                    $.each(tours.tariffs[0].prices, function (id, row) {
                        var available = row.hasOwnProperty('available') === true ? row['available'] : '0';
                        var cabins_table = '<tr>' +
                            '<td>' + row['price_name'] + '</td>' +
                            '<td>' + row['rt_name'] + '</td>' +
                            '<td>' + row['rp_name'] + '</td>' +
                            '<td>' + row['price_value'] + '</td>' +
                            '<td>' + available + '</td>' +
                            '</tr>';
                        $('#cabins').append(cabins_table);
                    });
                    var ex = '';
                    $.each(tours.routeDays, function (id, row) {
                        ex = '<tr><td width="100">' + row.portName + '</td>' +
                            '<td width="120">' + row.timeStart.substr(0, 5) + '</td>' +
                            '<td width="120">' + row.timeStop.substr(0, 5) + '</td>' +
                            '<td>' + row.excursionHtml + '</td></tr>';
                        $('#program').append(ex);
                    });


                });//end tours callback
        }




        var com = parseUrlQuery('com');
        switch(com){
            case 'mtf':
                renderMTF();
                break;
            case 'vdh':
                renderDetailsVDH();
                break;
            case 'inf':
                renderDetails();
                break;
            default:
                break;
        }

    });
})(jQuery);
