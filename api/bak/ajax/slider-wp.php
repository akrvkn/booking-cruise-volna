<?php
//ini_set('display_errors', '1');
$cat = 188;

$ships = array(
	'189'=>'bulgakov',
	'190'=>'karamzin',
	'191'=>'victoria',
	'192'=>'anastasia',
	'193'=>'grin',
	'194'=>'esenin',
	'195'=>'krylov',
	'196'=>'krasin',
	'197'=>'obraztsov',
	'198'=>'repin',
	'199'=>'rublev',
	'200'=>'rossia',
	'201'=>'surikov',
	'202'=>'gogol',
	'204'=>'multi-lang',
	'205'=>'wine',
	'206'=>'onedaytrip',
	'207'=>'fedoskino',
	'208'=>'hram',
	'209'=>'animation',
	'210'=>'pv300',
	'211'=>'balet',
	'213'=>'autumn',
	'215'=>'bus_ivanovo',
	'216'=>'bus_kaluga',
	'217'=>'bus_kimri',
	'218'=>'bus_uglich',
	'219'=>'bus_vladimir',
	'220'=>'new_year_europe',
	'222'=>'blackfriday',
	'223'=>'newyear',
	'224'=>'knyazyuri',
	'228'=>'rostov',
	'229'=>'constructor',
	'230'=>'23february',
	'231'=>'8marta',
	'232'=>'sviyazhsk'
);
$cat_obj = json_decode(@file_get_contents('https://www.mosturflot.ru/wp-json/wp/v2/posts?categories=188'));

$slider = '';
$count = 52;
if(count($cat_obj)>0){
$iterate = 0;
foreach($cat_obj as $item){
	$iterate++;
	if($iterate == 7){
		break;
	}
	$count++;
	$ship = 'ship.jpg';
	$excerpt = 'СПЕЦПРЕДЛОЖЕНИЕ';
	foreach($item->categories as $num){
		if($num!= 188 && isset($ships[$num])){
			$ship = $ships[$num];
		}
	}
	
	$img = '/assets/images/mtf/slides/'.$ship.'.jpg';
	
	if($item->featured_media > 0){
		$media_obj = json_decode(@file_get_contents('https://www.mosturflot.ru/wp-json/wp/v2/media/'.$item->featured_media));
		$img = $media_obj->media_details->sizes->full->source_url;
	}
	
	$title = trim($item->title->rendered);
	$content = trim(strip_tags($item->content->rendered));
	$excerpt = mb_substr(mb_strtoupper(trim(strip_tags($item->excerpt->rendered)), 'UTF-8'), 0, 27, 'UTF-8');
	$link = trim($item->link);
	
	$slider .= '<!-- SLIDE  -->
<li data-index="rs-'.$count.'" data-transition="parallaxvertical" data-slotamount="default" data-hideafterloop="0" data-hideslideonmobile="off"  data-easein="default" data-easeout="default" data-masterspeed="default"  data-thumb=""  data-rotate="0"  data-saveperformance="off"  data-title="'.$excerpt.'" data-param1="" data-param2="" data-param3="" data-param4="" data-param5="" data-param6="" data-param7="" data-param8="" data-param9="" data-param10="" data-description="'.$content.'">
    <!-- MAIN IMAGE -->
    <img src="'.$img.'"  alt=""  data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg" data-no-retina>
    <!-- LAYERS -->

    <!-- LAYER NR. 5 -->
    <div class="tp-caption tp-shape tp-shapewrapper   tp-resizeme"
         id="slide-54-layer-3"
         data-x="[\'center\',\'center\',\'center\',\'center\']" data-hoffset="[\'0\',\'0\',\'0\',\'0\']"
         data-y="[\'middle\',\'middle\',\'middle\',\'middle\']" data-voffset="[\'0\',\'0\',\'0\',\'0\']"
         data-width="full"
         data-height="full"
         data-whitespace="normal"

         data-type="shape"
         data-basealign="slide"
         data-responsive_offset="on"

         data-frames=\'[{"delay":1000,"speed":1500,"frame":"0","from":"opacity:0;","to":"o:1;","ease":"Power3.easeInOut"},{"delay":"wait","speed":1000,"frame":"999","to":"opacity:0;","ease":"Power3.easeInOut"}]\'
         data-textAlign="[\'left\',\'left\',\'left\',\'left\']"
         data-paddingtop="[0,0,0,0]"
         data-paddingright="[0,0,0,0]"
         data-paddingbottom="[0,0,0,0]"
         data-paddingleft="[0,0,0,0]"

         style="z-index: 5;background-color:rgba(0, 0, 0, 0.35);"> </div>

    <!-- LAYER NR. 6 -->
    <div class="tp-caption Newspaper-Title   tp-resizeme"
         id="slide-54-layer-1"
         data-x="[\'left\',\'left\',\'left\',\'left\']" data-hoffset="[\'50\',\'50\',\'50\',\'30\']"
         data-y="[\'top\',\'top\',\'top\',\'top\']" data-voffset="[\'165\',\'135\',\'105\',\'130\']"
         data-fontsize="[\'50\',\'50\',\'50\',\'30\']"
         data-lineheight="[\'55\',\'55\',\'55\',\'35\']"
         data-width="[\'600\',\'600\',\'600\',\'420\']"
         data-height="none"
         data-whitespace="normal"

         data-type="text"
         data-responsive_offset="on"

         data-frames=\'[{"delay":1000,"speed":1500,"frame":"0","from":"y:[-100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;","mask":"x:0px;y:0px;s:inherit;e:inherit;","to":"o:1;","ease":"Power3.easeInOut"},{"delay":"wait","speed":1000,"frame":"999","to":"auto:auto;","mask":"x:0;y:0;s:inherit;e:inherit;","ease":"Power3.easeInOut"}]\'
         data-textAlign="[\'left\',\'left\',\'left\',\'left\']"
         data-paddingtop="[0,0,0,0]"
         data-paddingright="[0,0,0,0]"
         data-paddingbottom="[10,10,10,10]"
         data-paddingleft="[0,0,0,0]"

         style="z-index: 6; min-width: 600px; max-width: 600px; white-space: normal;font-family:Arimo;">'.$title.'</div>

    <!-- LAYER NR. 7 -->
    <div class="tp-caption Newspaper-Subtitle   tp-resizeme"
         id="slide-54-layer-2"
         data-x="[\'left\',\'left\',\'left\',\'left\']" data-hoffset="[\'50\',\'50\',\'50\',\'30\']"
         data-y="[\'top\',\'top\',\'top\',\'top\']" data-voffset="[\'140\',\'110\',\'80\',\'100\']"
         data-fontweight="[\'700\',\'900\',\'900\',\'900\']"
         data-width="none"
         data-height="none"
         data-whitespace="nowrap"

         data-type="text"
         data-responsive_offset="on"

         data-frames=\'[{"delay":1000,"speed":1500,"frame":"0","from":"y:[-100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;","mask":"x:0px;y:0px;s:inherit;e:inherit;","to":"o:1;","ease":"Power3.easeInOut"},{"delay":"wait","speed":1000,"frame":"999","to":"auto:auto;","mask":"x:0;y:0;s:inherit;e:inherit;","ease":"Power3.easeInOut"}]\'
         data-textAlign="[\'left\',\'left\',\'left\',\'left\']"
         data-paddingtop="[0,0,0,0]"
         data-paddingright="[0,0,0,0]"
         data-paddingbottom="[0,0,0,0]"
         data-paddingleft="[0,0,0,0]"

         style="z-index: 7; white-space: nowrap; font-weight: 700;font-family:Arimo;">'.$excerpt.'</div>

    <!-- LAYER NR. 8 -->
    <div class="tp-caption Newspaper-Button rev-btn "
         id="slide-54-layer-5"
         data-x="[\'left\',\'left\',\'left\',\'left\']" data-hoffset="[\'53\',\'53\',\'53\',\'30\']"
         data-y="[\'top\',\'top\',\'top\',\'top\']" data-voffset="[\'361\',\'331\',\'301\',\'245\']"
         data-width="none"
         data-height="none"
         data-whitespace="nowrap"

         data-type="button"
         data-responsive_offset="on"
         data-responsive="off"
         data-frames=\'[{"delay":1000,"speed":1500,"frame":"0","from":"y:[-100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;","mask":"x:0px;y:0px;s:inherit;e:inherit;","to":"o:1;","ease":"Power3.easeInOut"},{"delay":"wait","speed":1000,"frame":"999","to":"auto:auto;","mask":"x:0;y:0;s:inherit;e:inherit;","ease":"Power3.easeInOut"},{"frame":"hover","speed":"300","ease":"Power1.easeInOut","to":"o:1;rX:0;rY:0;rZ:0;z:0;","style":"c:rgba(0, 0, 0, 1);bg:rgba(255, 255, 255, 1);bc:rgba(255, 255, 255, 1);"}]\'
         data-textAlign="[\'left\',\'left\',\'left\',\'left\']"
         data-paddingtop="[12,12,12,12]"
         data-paddingright="[35,35,35,35]"
         data-paddingbottom="[12,12,12,12]"
         data-paddingleft="[35,35,35,35]"

         style="z-index: 8; white-space: nowrap;font-family:Arimo;border-color:rgba(255,255,255,0.25);border-width:1px 1px 1px 1px;outline:none;box-shadow:none;box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;cursor:pointer;"><a href="'.$link.'">ПОДРОБНЕЕ</a> </div>
	</li>';
	
}
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
//echo $slider;
file_put_contents('slider-main.html', $slider);

?>
