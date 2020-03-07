<?php
$vdh_cruises = json_decode(file_get_contents('https://www.rech-agent.ru/api/v1/cruises'), true);

$vdh = ['Пушкин', 'Горький', 'Симонов', 'Соболев', 'Радищев', 'Дзержинский', 'Жуков', 'Ростропович', 'Чичерин', 'Шашков', 'Федин', 'Толстой', 'Ленин', 'Чернышевский', 'Русь', 'Белинский', 'Петербург', 'Андропов', 'Кронштадт', 'Коротков', 'Новгород', 'Фрунзе', 'Будённый', 'Суворов', 'Кучкин'];

$pattern = '/'.implode('|', $vdh).'/siU';
$img = [];

foreach($vdh_cruises as $cruise){
    if(preg_match($pattern, $cruise['ship'])){
        //echo $cruise['ship']."\n";
        $img[$cruise['ship']] = $cruise['ship_photo_main'];
    }
}

file_put_contents('data/vdhimages.json', json_encode($img));
