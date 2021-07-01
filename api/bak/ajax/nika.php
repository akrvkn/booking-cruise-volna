<!DOCTYPE html>
<html lang="ru-RU">
<head>
<meta charset="UTF-8" /><body>
<?php
$NikaCruiseRiverSearch = new NikaCruiseRiverSearch();

$NikaCruiseRiverSearch->Key = 'ece60bd0b9b3c67a622639257236a68b';
$NikaCruiseRiverSearch->Charset = 'utf-8';

echo $NikaCruiseRiverSearch->run();


class NikaCruiseRiverSearch
{
    public $Version = '1.5';

    //////////////////////////////////////////////
    //                НАСТРОЙКИ                 //
    #Ваш ключ доступа к поиску
    public $Key = 'ece60bd0b9b3c67a622639257236a68b';

    #Кодировка используемая у Вас на сайте
    public $Charset = 'utf-8';

    #Формат данных
    public $Format = 'json';

    #Способ загрузки контента с сайта НИКИ (на выбор или cURL или file_get_contents)
    public $GetPageMode = 'file_get_contents';

    #Показывать специальные акции на стартовой.
    public $Adv = true;

    #Массив названий месяцев
    public $MonthRus = array(
        1 => 'Янв', 2 => 'Фев', 3 => 'Мар',
        4 => 'Апр', 5 => 'Май', 6 => 'Июн',
        7 => 'Июл', 8 => 'Авг', 9 => 'Сен',
        10 => 'Окт', 11 => 'Ноя', 12 => 'Дек'
    );


    #############################################
    #                ШАБЛОНЫ

    #шаблон вывода найденных круизов
    public $TemplateOutHeader = '<style type="text/css">
	#cruise_search_out {width:100%;border: 1px solid;text-align:center;vertical-align:middle;}
	#cruise_search_out img {border:0px;}
	</style>
	<p>Найдено %total_cruises% круизов</p>
	<table id="cruise_search_out" rules="all">
	<tr>
	<td width="65px">Даты круиза</td>
	<td width="42px">Кол-во<br />дней</td>
	<td width="32px">Язык</td>
	<td>Название круиза</td>
	<td>Цена</td>
	<td>Маршрут</td>
	<td width="110px">Теплоход</td>
	</tr>';
    public $TemplateOutContent = '<tr>
	<td>%start%<br />-<br />%stop%</td>
	<td>%days%</td>
	<td>%langs%</td>
	<td><a href="?show_cruise=on&tid=%id%">%name%</a></td>
	<td>%min_price%</td>
	<td><div>Страны: %countries%</div><div>Реки: %rivers%</div></td>
	<td><a href="?show_cruise=on&hid=%ship_id%">%ship_photo%<br />%ship%</a></td>
	</tr>';
    public $TemplateOutFooter = '</table>';
    #

    #шаблон формы поиска
    public $TemplateForm = '<style type="text/css">
	#cruise_search{width:837px;margin:0 auto;}
	#cruise_search p{font-weight:bold;padding:5px;}
	#cruise_search div {border:1px dotted;padding:5px;width:99%;margin:5 auto;}
	#cruise_search .companies{padding-right:15px;vertical-align:top;}
	#cruise_search .calend_m {border:1px solid;}
	#cruise_search .calend_m td {width:70px;height:25px;white-space: nowrap;}
	#cruise_search .calend_m #y {text-align:center;font-weight:bold;}
	#cruise_search .langs input {margin-left:20px;}
	#cruise_search .langs img {margin-bottom:-10px;border:0px;}
	#cruise_search .tablead{width:330px;padding:0px; border:0px;margin:0 auto;}
	#cruise_search .tablead td{width:200px;padding:25px;text-align:center; border:0px;}
	#cruise_search .tablead .blankad{padding:0px;}
	#cruise_search .tablead div {height:50px;border:0px;}
	</style>

	<form method="get" id="cruise_search">
	<table class="tablead">
		<tr>
			%ADVERTISING%
		</tr>
	</table>
	<div>
	    <h2><a href="?show_ships">Теплоходы</a></h2>
		<p>Круизные компании</p>
		%COMPANIES%
	</div>
	<div class="langs">
		<p>Язык обслуживания</p>
		%LANGS%
	</div>
	<div>
		<p>Дата круиза</p>
		%DateCruise%
	</div>
	<div>
		<p>Страны</p>
		%COUNTRIES%
	</div>
	<div>
		<p>Реки</p>
		%RIVERS%
	</div>
	<div>
		<label><input name="special" type="checkbox" /> Спец. предложение</label><br />
		<input name="search" type="submit" value="Подобрать круиз" />
		<input name="cruise_search" type="hidden" value="river" />
	</div>
	</form>';
    #

    #шаблон сообщения, если не найден ни один круиз
    public $TemplateSearchNull = '<div style="color:red;font-weight:bold;text-align:center;">Найдено 0 круизов. Попробуйте изменить Ваш запрос.</div>';

    #шаблон сообщения, если произошла ошибка при поиске
    public $TemplateSearchError = '<div style="color:red;font-weight:bold;text-align:center;">Произошла ошибка. Повторите Ваш запрос позже.</div>';

    #шаблон сообщения, если произошла ошибка при загрузке страницы с информацией о круизе или теплоходе
    public $TemplateContentError = '<div style="color:red;font-weight:bold;text-align:center;">Произошла ошибка. Не удалось загрузить страницу.</div>';

    #шаблон сообщения, если произошла ошибка при загрузке основных переменных с сайта ники (компании, реки, страны)
    public $TemplateStartError = '<div style="color:red;font-weight:bold;text-align:center;">Произошла ошибка. Не удалось инициализировать поиск.</div>';

    #
    #
    #############################################


    //                КОНЕЦ НАСТРОЕК            //
    //////////////////////////////////////////////


    public function run()
    {
        if (!function_exists('json_encode') && !function_exists('simplexml_load_string'))
            return 'PHP JSON or XML module not installed.';

        $Output = '';
        //если послан запрос на поиск
        if (isset($_REQUEST['cruise_search'])) {
            //запоминаем эту строчку поиска и передаем запрос на сервер НИКИ
            $QUERY = '';
            foreach ($_GET as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $key_arr => $val_arr) {
                        $QUERY .= '&' . $key . '[' . @urlencode($key_arr) . ']=on';
                    }
                } else
                    $QUERY .= '&' . $key . '=' . @urlencode($val);
            }

            $content = $this->GetPage('http://nikatravel.ru/river/river_search.php?key=' . $this->Key . '&format=' . $this->Format . $QUERY);

            //проверяем ответ, если не было ошибки, то парсим полученную информацию
            if (!empty($content) && !strstr(substr($content, 0, 20), 'ERROR_')) {
                if ($this->Format == 'json') {
                    $content = json_decode($content);
                    if (!stristr($this->Charset, 'utf-8')) {
                        for ($i = 0; $i < count($content); $i++) {
                            $content[$i]->name = iconv('utf-8', $this->Charset . '//IGNORE', $content[$i]->name);
                            $content[$i]->countries = iconv('utf-8', $this->Charset . '//IGNORE', $content[$i]->countries);
                            $content[$i]->rivers = iconv('utf-8', $this->Charset . '//IGNORE', $content[$i]->rivers);
                            $content[$i]->min_price = iconv('utf-8', $this->Charset . '//IGNORE', $content[$i]->min_price);
                        }
                    }
                } elseif ($this->Format == 'xml') {
                    $content = simplexml_load_string($content);
                    for ($i = 0; $i < count($content->cruise); $i++) {
                        $content->cruise[$i]->id = $content->cruise[$i]->attributes();
                        if (!stristr($this->Charset, 'utf-8')) {
                            $content->cruise[$i]->name = iconv('utf-8', $this->Charset . '//IGNORE', (string)$content->cruise[$i]->name);
                            $content->cruise[$i]->countries = iconv('utf-8', $this->Charset . '//IGNORE', (string)$content->cruise[$i]->countries);
                            $content->cruise[$i]->rivers = iconv('utf-8', $this->Charset . '//IGNORE', (string)$content->cruise[$i]->rivers);
                            $content->cruise[$i]->min_price = iconv('utf-8', $this->Charset . '//IGNORE', (string)$content->cruise[$i]->min_price);
                        }
                    }
                }

                //всего круизов
                $total_cruises = sizeof($content);
                if ($total_cruises > 0) {
                    $VarsNika = $this->get_vars();
                    $langs = !empty($VarsNika['LANGS']) ? $VarsNika['LANGS'] : array();

                    //выводим шапку
                    $Output = $this->Template($this->TemplateOutHeader, array('total_cruises' => $total_cruises));
                    //дальше выводим информацию о каждом круизе
                    foreach ($content as $cruise) {
                        $cruise->ship_photo = !empty($cruise->ship_photo) ? '<img src="' . $cruise->ship_photo . '" alt="Фото теплохода" />' : '';
                        $cruise->langs = isset($langs[$cruise->langs]) ? '<img src="' . $langs[$cruise->langs][1] . '" alt="' . $langs[$cruise->langs][0] . '" title="' . $langs[$cruise->langs][0] . '" />' : '';
                        $Output .= $this->Template($this->TemplateOutContent, $cruise);
                    }
                    //выводим конец шаблона
                    $Output .= $this->TemplateOutFooter;
                } else $Output = $this->TemplateSearchNull;
            } else $Output = $this->TemplateSearchError;
        } //если послан запрос на показ информации о круизе или корабле
        elseif (isset($_GET['show_cruise'])) {
            if (!empty($_GET['tid'])) {
                $tid = intval($_GET['tid']);
                $content = $this->GetPage('http://nikatravel.ru/river/simple_content.php?key=' . $this->Key . '&tid=' . $tid);
            } elseif (!empty($_GET['hid'])) {
                $hid = intval($_GET['hid']);
                $content = $this->GetPage('http://nikatravel.ru/river/simple_content.php?key=' . $this->Key . '&hid=' . $hid);
            }

            //если удалось загрузить страницу, то выводим её, иначе ошибку
            if (!empty($content) && !strstr(substr($content, 0, 20), 'ERROR_')) {
                if (!stristr($this->Charset, 'utf-8'))
                    $Output = iconv('utf-8', $this->Charset . '//IGNORE', $content);
                else $Output = $content;
            } else $Output = $this->TemplateContentError;
        } elseif (isset($_GET['show_ships'])) {
            $content = $this->GetPage('http://nikatravel.ru/river/get_ships.php?key=' . $this->Key);
            //если удалось загрузить страницу, то выводим её, иначе ошибку
            if (!empty($content) && !strstr(substr($content, 0, 20), 'ERROR_')) {
                if (!stristr($this->Charset, 'utf-8'))
                    $Output = iconv('utf-8', $this->Charset . '//IGNORE', $content);
                else $Output = $content;
            } else $Output = $this->TemplateContentError;
        } //иначе выводим форму поиска
        else {
            $VarsNika = $this->get_vars();

            if (empty($VarsNika)) {
                return $this->TemplateStartError;
            }


            $tmp = array(
                'COMPANIES' => $this->Array2Companies($VarsNika['COMPANIES'], $VarsNika['COMPANIES_SERVICE']),
                'COUNTRIES' => $this->Array2Data('countries', $VarsNika['COUNTRIES']),
                'RIVERS' => $this->Array2Data('rivers', $VarsNika['RIVERS']),
                'DateCruise' => $this->DateCruise($VarsNika['max_date']),
                'LANGS' => $this->Langs($VarsNika['LANGS'])
            );

            if ($this->Adv) {
                $tmp['ADVERTISING'] = '';
                foreach ($VarsNika['ADVERTISING'] as $adv) {

                    if ($this->Format == 'xml')
                        $adv = htmlspecialchars_decode($adv);

                    if (!stristr($this->Charset, 'utf-8'))
                        $tmp['ADVERTISING'] .= '<td>' . iconv('utf-8', $this->Charset . '//IGNORE', $adv) . '</td>';
                    else
                        $tmp['ADVERTISING'] .= '<td>' . $adv . '</td>';
                }
            }
            if (empty($tmp['ADVERTISING']))
                $tmp['ADVERTISING'] .= '<td class="blankad">&nbsp;</td>';

            $Output = $this->Template($this->TemplateForm, $tmp); //Вывод на экран
        }

        return $Output;
    }


    #### FUNCTIONS ####
    public function get_vars()
    {
        $content = $this->GetPage('http://nikatravel.ru/river/get_vars.php?key=' . $this->Key . '&format=' . $this->Format);

        if ($this->Format == 'json') {
            $VarsNika = json_decode($content, true);
            foreach ($VarsNika as $key => $val) {
                if (is_object($val))
                    $VarsNika[$key] = (array)$VarsNika[$key];
            }
        } else {
            $VarsNika = simplexml_load_string($content);

            if (is_object($VarsNika)) {
                $Arr = array();

                foreach ($VarsNika as $key => $val) {
                    $Arr[$key][(string)$val->attributes()->id] = (string)$val;
                }

                $VarsNika = $Arr;
                $VarsNika['max_date'] = $VarsNika['max_date']['max_date'];
            }
        }

        return $VarsNika;
    }

    protected function DateCruise($max_date)
    {
        #Обработка дат
        if (preg_match("#^[0-9]{4}-[0-9]{2}$#", $max_date)) {
            $DATES['max_year'] = date("Y", strtotime($max_date));
            $DATES['max_month'] = date("m", strtotime($max_date));
        } else {
            $DATES['max_year'] = date("Y") + 1;
            $DATES['max_month'] = date("m");
        }

        $DATES['now_year'] = date("Y");
        $DATES['now_month'] = date("m");


        $ret = '<table><tr>';
        for ($y = $DATES['now_year']; $y <= $DATES['max_year']; $y++) {
            $ret .= '<td><table class="calend_m" rules="all"><tr><td id="y" colspan="3">' . $y . '</td></tr><tr>';
            $m = 1;

            if (($y != $DATES['now_year']) && ($y < $DATES['max_year'])) {
                $DATES['now_month'] = 12;
                while ($m < 13) {
                    $ret .= '<td><label><input name="month[' . $y . '-' . sprintf("%02d", $m) . ']" type="checkbox" />' . $this->MonthRus[$m] . '</label></td>';
                    if ($m % 3 == 0) $ret .= '</tr><tr>';
                    $m++;
                }
                $ret .= '</tr></table></td>';
            } elseif ($y == $DATES['max_year']) {
                $DATES['now_month'] = $DATES['max_month'] + 1;
                while ($DATES['now_month'] != $m) {
                    $ret .= '<td><label><input name="month[' . $y . '-' . sprintf("%02d", $m) . ']" type="checkbox" />' . $this->MonthRus[$m] . '</label></td>';
                    if ($m % 3 == 0) $ret .= '</tr><tr>';
                    $m++;
                }
                while ($m < 13) {
                    $ret .= '<td>' . $this->MonthRus[$m] . '</td>';
                    if ($m % 3 == 0) $ret .= '</tr><tr>';
                    $m++;
                }
                $ret .= '</tr></table></td>';
            } else {
                while ($DATES['now_month'] != $m) {
                    $ret .= '<td>' . $this->MonthRus[$m] . '</td>';
                    if ($m % 3 == 0) $ret .= '</tr><tr>';
                    $m++;
                }
                while ($m < 13) {
                    $ret .= '<td><label><input name="month[' . $y . '-' . sprintf("%02d", $m) . ']" type="checkbox" />' . $this->MonthRus[$m] . '</label></td>';
                    if ($m % 3 == 0) $ret .= '</tr><tr>';
                    $m++;
                }
                $ret .= '</tr></table></td>';
            }
        }
        $ret .= '</td></tr></table>';
        return $ret;
    }


    protected function Array2Data($name, $arr, $break = 5)
    {
        asort($arr);
        $ret = '<table><tr>';
        $i = 0;
        foreach ($arr as $key => $val) {
            if ($i % $break == 0 && $i != 0)
                $ret .= '</tr><tr>';

            if (!stristr($this->Charset, 'utf-8'))
                $val = iconv('utf-8', $this->Charset . '//IGNORE', $val);

            $ret .= '<td><label><input name="' . $name . '[' . $key . ']" type="checkbox" />' . $val . '</label></td>';
            $i++;
        }
        while ($i % $break != 0) {
            $ret .= '<td>&nbsp;</td>';
            $i++;
        }
        $ret .= '</tr></table>';
        return $ret;
    }


    protected function Array2Companies($companies, $services)
    {
        foreach ($companies as $key => $val)
            $companies[intval($key)] = $val;

        $ret = '<table><tr>';

        foreach ($services as $key => $val) {
            if (!stristr($this->Charset, 'utf-8'))
                $val = iconv('utf-8', $this->Charset . '//IGNORE', $val);
            $ret .= '<td class="companies"><b>' . $val . '</b><br />';

            $key = explode(',', $key);
            foreach ($key as $id) {
                if (!stristr($this->Charset, 'utf-8'))
                    $companies[intval($id)] = iconv('utf-8', $this->Charset . '//IGNORE', $companies[$id]);
                $ret .= '<label><input name="company[' . $id . ']" type="checkbox" />' . $companies[$id] . '</label><br />';
            }
            $ret .= '</td>';
        }
        $ret .= '</tr></table>';

        return $ret;
    }


    protected function Langs($langs)
    {
        $ret = '';
        foreach ($langs as $key => $val) {
            if (!empty($val[1])) {
                $val[1] = '<img src="' . $val[1] . '" alt="' . $val[0] . '" title="' . $val[0] . '" />';
            } else {
                $val[1] = $val[0];
            }
            $checked = ($key == -1) ? ' checked="checked"' : '';
            $ret .= '<label><input type="radio" name="langs" value="' . $key . '"' . $checked . ' />' . $val[1] . '</label>';
        }
        return $ret;
    }


    protected function Template($tpl, $arr)
    {
        foreach ($arr as $key => $val) {
            if (empty($val)) $val = '&nbsp;';
            $tpl = str_replace('%' . $key . '%', $val, $tpl);
        }

        return $tpl;
    }


    protected function GetPage($url, $method = 'get', $postdata = '')
    {
        $content = '';

        if (stristr($this->GetPageMode, 'curl') or stristr($method, 'post') or !empty($postdata)) {
            if (!function_exists('curl_init'))
                exit('cURL support disabled.');
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            if (stristr($method, 'post') && !empty($postdata)) {
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
            }
            $content = curl_exec($curl);
            curl_close($curl);
        } else {
            $content = @file_get_contents($url);
        }

        return $content;
    }
}

?>
</body></html>
