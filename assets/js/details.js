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

        function renderIFF() {
            var shipid = parseUrlQuery('ship');
            var tourid = parseUrlQuery('tour');
            let cruise_url = 'https://restapi.infoflot.com/cruises/' + tourid + '?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b';
            let ship_url = 'https://restapi.infoflot.com/ships/' + shipid + '?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b';
            let cabins_url = 'https://restapi.infoflot.com/cruises/' + tourid + '/cabins?key=407c8c353a23a14d40479eb4e4290a8a6d32b06b';
            const dataSet = [];
            $.when($.getJSON(ship_url), $.getJSON(cabins_url)).done(function (ships, cabs) {
                var shipdata = ships[0];
                var cab = cabs[0];
                $('.shipname').html('<hr><h2>т/х "' + shipdata['name'] + '"</h2>');
                $("input[name='ship']").val(shipdata['name']);
                $('.shipimg').html('<img src="' + shipdata['files']['photo']['path'] + '" width="350" />');
                $('.deckplan').html('<a href="' + shipdata['files']['scheme']['path'] + '" data-lightbox="deckplan"><img src="' + shipdata['files']['scheme']['path'] + '" width="350" /></a>');
                $('.description').html('<p><br><br>' + shipdata['descriptionBig'] + '</p>');

                var types = [];
                var co = 0;
                var typePrice = {};
                $.each(cab.prices, function(x, price){
                    //console.log(price.type_name);
                    typePrice[x] = price.prices.main_bottom.adult;
                });

                var imgsrc = 'https://placehold.it/150x150';

                $.each(shipdata['cabins'], function (i, v){

                    if(types.indexOf(v.typeId) === -1) {
                        types.push(v.typeId);
                        dataSet[co] = {};
                        var tr = '<tr><td>';

                        if(v['photos'].length > 0) {
                            imgsrc =  v['photos'][0]['filename'];
                        }
                        var image = '<img src="' + imgsrc + '" width="150">';
                        dataSet[co]['imgsrc'] = imgsrc;
                        dataSet[co]['name'] = v['typeName'];
                        var desc = v['typeFriendlyName'] === null ? '' : v['typeFriendlyName'];
                        dataSet[co]['desc'] = v['typeFriendlyName'] === null ? '' : v['typeFriendlyName'];
                        var price = typePrice[v.typeId].toString();
                        dataSet[co]['price'] = typePrice[v.typeId].toString();
                        tr = tr + image + '</td><td>' + v['typeName'] + '</td><td>' + desc + '</td><td>' + price + '</td></tr>';
                        $('#tourcabins').append(tr);
                        co++;
                    }
                });
                console.log(dataSet);
                if( dataSet.length > 0 ) {

                }
            });

            $.getJSON(cruise_url)
                .done(function (data) {
                    //console.log(data);
                    $('.cruise-meta').append('<li>' + data['route'] + '</li>').append('<li>Дата круиза: ' + moment(data['dateStart'], 'YYYY-MM-DDTHH:mm:ss+03:00').format('YYYY-MM-DD HH:mm') + ' - ' + moment(data['dateEnd'], 'YYYY-MM-DDTHH:mm:ss+03:00').format('YYYY-MM-DD HH:mm') + ' ( ' + data['days'] + ' дн.)' + ' </li>');
                    $("input[name='tour']").val(data['route']);
                    $("input[name='date']").val(data['dateStart'] );
                    const dataSet = [];
                    $.each(data['timetable'], function (i, v){
                        dataSet[i] = {};
                        dataSet[i]['city'] = v['place'];
                        dataSet[i]['date_start'] = moment(v['dateArrival'], 'YYYY-MM-DDTHH:mm:ss+03:00').format('YYYY-MM-DD HH:mm');
                        dataSet[i]['date_end'] = moment(v['dateDeparture'], 'YYYY-MM-DDTHH:mm:ss+03:00').format('YYYY-MM-DD HH:mm');
                        dataSet[i]['description'] = v['description'];
                    });
                    if (data.length !== 0) {
                        console.log(dataSet);
                        $('.program').prepend('<h2>Программа круиза</h2>');
                        var program = $('#program').DataTable({
                            "dom": 'rt',
                            "searching": false,
                            "ordering": false,
                            "data": dataSet,
                            // "ajax": {
                            //     "url": route_url,
                            //     "dataSrc": function (json) {
                            //         var data = [];
                            //         for (var key in json) {
                            //             if (json.hasOwnProperty(key)) {
                            //                 json[key].tourstart = moment(json[key].date_start + ' ' + json[key].time_start, 'DD.MM.YYYY HH:mm').format('YYYY-MM-DD HH:mm');
                            //                 data.push(json[key]);
                            //             }
                            //         }
                            //         data.sort(compare);
                            //         return data;
                            //     }
                            // },
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

        function renderMTFv3(){
            const shipId = parseUrlQuery('ship');
            const tourId = parseUrlQuery('tour');

            const citiesURL = 'https://api.mosturflot.ru/v3/rivercruises/tour-points?filter[tour-id]=' + tourId + '&include=tour,tour.ship,excursions,title-image,tour-rates&fields[tours]=ship-id,days,start,finish,route,&fields[ships]=id,name&per-page=10000';

            const shipURL = 'https://api.mosturflot.ru/v3/rivercruises/ships/' + shipId + '?include=title-image,ship-class,services,cabin-categories,staff,deckplan,on-board-name';

            const pricesURL = 'https://api.mosturflot.ru/v3/rivercruises/tours/' + tourId + '?include=tour-rates,ship-title-image,direction';

            const cabinsURL = 'https://api.mosturflot.ru/v3/rivercruises/ships/' + shipId + '/cabin-categories?include=title-image';
            if(shipId > 0 ) {
                $.when($.getJSON(shipURL), $.getJSON(cabinsURL)).done(function (ships, cabins) {
                    renderShipDescription(ships[0]);
                    //renderSingleShipCabins(cabins[0]);
                    if(tourId > 0 ) {
                        $.when($.getJSON(citiesURL), $.getJSON(pricesURL)).done(function (points, prices) {
                            renderTourPoints(points[0]);
                            getCabinPrices(cabins[0], prices[0]);
                            //renderRouteNotes(prices[0].data.attributes['route-note']);
                        });
                    }
                });
            }

        }

        function renderShipDescription(ship) {
            let shipId = ship.data.id;
            $("input[name='ship']").val(ship.data.attributes.name);
            //$("input[name='tour']").val(json.answer.tourroute);
            //$("input[name='date']").val(moment(json.answer.tourstart, moment.ISO_8601).format('DD.MM.YYYY'));
            $('.deckplan').html('<a href="https://booking.mosturflot.ru/rivercruises/ships/' + shipId + '/deckplan" data-lightbox="deckplan"><img src="https://booking.mosturflot.ru/rivercruises/ships/' + shipId + '/deckplan" width="350" /></a>');
            $('.description').html('<br><br>' + ship.data.attributes.description.replace(/<a\b[^>]*>(.*?)<\/a>/gi,"").replace(/(<([^>]+)>)/ig, "") );
            $('#summary').html('<li><label>Теплоход:</label>' + ship.data.attributes.name + '</li>');
            $('.shipimg').html('<img src="/assets/img/mtf/ships/' + shipId + '.jpg" width="483" alt="Теплоход" />');
            $('.shipname').html('<hr><h2>т/х "' + ship.data.attributes.name + '"</h2>');

        }

        function getCategoryPrices(prices){
            let pricelist = {};
            $.map(prices.included, function(included, index) {
                if (included.type === 'tour-rates') {
                    pricelist[included.attributes['category-id']] = included.attributes['price-main'] === null ? 0 : parseInt(included.attributes['price-main']);
                }
            });
            return pricelist;
        }

        function getCabinImages(cabins){
            let cabinimages = {};
            let cabinsImg = cabins.hasOwnProperty('included') === true ? cabins.included : [1];
            $.map(cabinsImg, function(img, id) {
                if (img.type === 'cabin-images') {
                    cabinimages[img.id] = img.links['image-url'];
                }
            });
            return cabinimages;
        }

        function getCabinPrices(cabins, prices){
            console.log(cabins);
            //console.log('img:', getCabinImages(cabins));
            let pricelist = getCategoryPrices(prices);
            let cabinimages = getCabinImages(cabins);
            let cabinsData = {};

            $.map(cabins.data, function(cat, i) {

                if (cat.relationships['title-image'].hasOwnProperty('data')) {
                    if (cabinsData.hasOwnProperty(cat.attributes['sort-order']) === false) {
                        cabinsData[cat.attributes['sort-order']] = {};
                    }
                    cabinsData[cat.attributes['sort-order']]['image'] = '<img src="' + cabinimages[cat.relationships['title-image'].data.id] + '" alt="' + cat.attributes.name + '" style="width: 150px;" />';
                    cabinsData[cat.attributes['sort-order']]['name'] = cat.attributes.name;
                    if (cat.attributes.description !== null) {
                        cabinsData[cat.attributes['sort-order']]['description'] = cat.attributes.description.replace(/<.*?>/g, '');
                    } else {
                        cabinsData[cat.attributes['sort-order']]['description'] = '';
                    }
                    if (pricelist.hasOwnProperty(cat.id)) {
                        cabinsData[cat.attributes['sort-order']]['price'] = pricelist[cat.id];
                    }
                }
            });
            renderCabins(cabinsData);
        }

        function renderCabins(cabinsData){
            //console.log('data:', cabinsData);
            const tCabins = $('#tourcabins');
            $.each(cabinsData, function(i, el){
                if(el.name !== 'Служебная') {
                    let cabinsHTML = '<tr>' +
                        '<td>' + el.image + '</td>' +
                        '<td><span>' + el.name + '</span></td>' +
                        '<td><p>' + el.description + '</p></td>' +
                        '<td><span>' + el.price + ' руб.</span></td>' +
                        '</tr>';
                    tCabins.append(cabinsHTML);
                }
            });
        }

        function renderTourPoints(points){
                console.log(points);
                let pr = getPointsExcursions(points);
                const program = $('#program');
                $.each(points.data, function(i, point){

                    let ex = '';
                    if(point.relationships.excursions.data){
                        $.each(point.relationships.excursions.data, function(k, v){
                            ex += pr[v.id];
                        });
                    }

                     let arrival = point.attributes.arrive === null ? ' - ' : moment(point.attributes.arrive, moment.ISO_8601).format('DD.MM.YYYY') + ' ' + point.attributes.arrive.substring(11, 16);
                     let departure = point.attributes.departure === null ? ' - ' : moment(point.attributes.departure, moment.ISO_8601).format('DD.MM.YYYY') + ' '  + point.attributes.departure.substring(11, 16);

                     let row = '<tr><td>' + point.attributes.name + '</td>' +
                        '<td>' + arrival + '</td>' +
                        '<td>' + departure + '</td>' +
                        '<td>' + ex + '</td></tr>';
                     program.append(row);
                });
        }

        function getPointsExcursions(points){
            let excursions = {};
            $.each(points.included, function(index, inc) {
                if (inc.type === 'tour-excursions') {
                    excursions[inc.id] = inc.attributes.description === null ? '' : inc.attributes.description.replace(/<.*?>/g, '');
                }
            });
            return excursions;
        }

        function renderDetailsVDH() {
            var shipid = parseUrlQuery('ship');
            var tourid = parseUrlQuery('tour');
            var ships_url = 'https://www.cruise-volna.ru/api/ajax/data/vdh/motorships.json';//'https://api.vodohod.com/json/v2/motorships.php?pauth=';
            var tour_url = 'https://www.cruise-volna.ru/api/ajax/data/vdh/' + shipid + '/' + tourid + '.json';


            $.getJSON(ships_url)
                .done(function (data) {
                    $('.shipname').html('<hr><h2>т/х "' + data[shipid]['name'] + '"</h2>');
                    $("input[name='ship']").val(data[shipid]['name']);
                    $('.shipimg').html('<img src="/assets/img/vdh/'+ data[shipid]['code'] + '.jpg" width="550" />');
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
                        $('#tourcabins').append(cabins_table);
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

        function renderDetailsVDHv2() {
            var shipid = parseUrlQuery('ship');
            var tourid = parseUrlQuery('tour');
            var ships_url = 'https://api.vodohod.com/json/v2/motorships.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7';
            var tour_url = 'https://api.vodohod.com/json/v2/cruise-days.php?pauth=v2-ba9fab12d2c4b8d005645d04492a7af7&cruise=' + tourid;


            $.getJSON(ships_url)
                .done(function (data) {
                    $('.shipname').html('<hr><h2>т/х "' + data[shipid]['name'] + '"</h2>');
                    $("input[name='ship']").val(data[shipid]['name']);
                    $('.shipimg').html('<img src="/assets/img/vdh/'+ data[shipid]['code'] + '.jpg" width="550" />');
                    $('.deckplan').html('<a href="' + data[shipid]['decks'] + '" data-lightbox="deckplan"><img src="' + data[shipid]['decks'] + '" width="350" /></a>');
                    $('.description').html( data[shipid]['description']);
                    $.each(data.rooms, function (id, row) {
                        var cabins_table = '<tr>' +
                            '<td>' + row['roomTypeName'] + '</td>' +
                            '<td>' + row['roomDescription'] + '</td>' +
                            '<td></td>' +
                            '<td></td>' +
                            '</tr>';
                        $('#tourcabins').append(cabins_table);
                    });
                });

            $.getJSON(tour_url)
                .done(function (tours) {
                    $('.cruise-meta').append('<li>' + tours['name'] + '</li>').append('<li>Дата круиза: ' + moment(tours['dateStart']).format('DD.MM.YYYY') + ' - ' + moment(tours['dateStop']).format('DD.MM.YYYY') + ' ( ' + tours['days'] + ' дн.)' + ' </li>');
                    $("input[name='tour']").val(tours['name']);
                    $("input[name='date']").val(tours['dateStart']);
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
                renderMTFv3();
                break;
            case 'vdh':
                renderDetailsVDH();
                break;
            case 'inf':
                renderIFF();
                break;
            default:
                break;
        }

    });
})(jQuery);
