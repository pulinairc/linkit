<?php
  function minify_output($buffer) {
      $search = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s');
      $replace = array('>','<','\\1');
      if (preg_match("/\<html/i",$buffer) == 1 && preg_match("/\<\/html\>/i",$buffer) == 1) {
          $buffer = preg_replace($search, $replace, $buffer);
      }
      return $buffer;
  }

  $cachefile = 'cached-index.html';
  $cachetime = 1800;
  if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
      include($cachefile);
      echo "<!-- Amazing hand crafted super cache, generated ".date('H:i', filemtime($cachefile))." -->";
      exit;
  }
  ob_start('minify_output');
?>
<!doctype html>

<meta charset="utf-8" />

<title>100 viimeisintä linkkiä irkissä | www.pulina.fi</title>

<link rel="icon" href="urls.png" type="image/png" />
<link rel="shortcut icon" href="urls.png" type="image/x-icon" />
<link rel="bookmark icon" href="urls.png" type="image/x-icon" />
<link rel="stylesheet" href="linkit.css" type="text/css" /> 

<script src="http://code.jquery.com/jquery-latest.min.js"></script>
<script src="linkit.js"></script>
<script src="linkit.url.preview.js"></script>
<script>
$(function() {
$('a:visited').css('background','none transparent #fff -9999px -9999px !important');
});
</script>
<script>window.onload=function(){enableTooltips()};</script> 

</head>
<body>
<div id="wrapper">
<ul class="linkkilista">
<?php

    function page_title($url) {
        $fp = file_get_contents($url);
        if (!$fp) 
            return null;

        $res = preg_match("/<title>(.*)<\/title>/siU", $fp, $title_matches);
        if (!$res) 
            return null; 

        // Clean up title: remove EOL's and excessive whitespace.
        $title = preg_replace('/\s+/', ' ', $title_matches[1]);
        $title = trim($title);
        return $title;
    }

//urlgrab.pl:
$logitiedosto = "/var/www/html/urllog.log";
$file = $logitiedosto;

$fp = fopen($file, 'r');

if ($fp) {
$lines = array();

while (($line = fgets($fp)) !== false) {
$lines[] = $line;

while (count($lines) > 100)
array_shift($lines);
}

$reverse = array_reverse($lines);

foreach ($reverse as $line) {

$rivi = $line;
$poistettavat = array('Haamu');
$tilalle = array('');
$hieno = str_replace($poistettavat, $tilalle, $rivi);

$aikanyt = time();
//näin saataisiin epoch, mutta sitä ei tässä tapauksessa tarvita:
//$aikakunpastettu = mktime($tunnitt, $minuutitt, 00, $kuukausi, $paiva, $vuosi);

$epochi = explode(" ", $line);
$aikakunpastettu = $epochi[0];
$irkkaaja = $epochi[1];
$kanava = $epochi[2];
$linkki = $epochi[3];

        $chunks = array(
        array(60 * 60 * 24 * 365 , 'vuosi'),
        array(60 * 60 * 24 * 30 , 'kuukausi'),
        array(60 * 60 * 24 * 7, 'viikko'),
        array(60 * 60 * 24 , 'päivä'),
        array(60 * 60 , 'tunti'),
        array(60 , 'minuutti'),
        );

$aikapastettu = $aikanyt - $aikakunpastettu;

        for ($i = 0, $j = count($chunks); $i < $j; $i++)
                {
                $seconds = $chunks[$i][0];
                $name = $chunks[$i][1];

                if (($count = floor($aikapastettu / $seconds)) != 0)
                        {
                        break;
                        }
                }
        if ($count > 1 & $name == "päivä") {

//	$output = "$count";
       	$output = ($count == 1) ? '1 '.$name : "$count {$name}ä";

        } elseif ($count > 1 & $name == "vuosi") {

        $output = ($count == 1) ? '1 '.$name : "$count vuotta";

        } elseif ($count > 1 & $name == "kuukausi") {

        $output = ($count == 1) ? '1 '.$name : "$count kuukautta";

        } else {
        $output = ($count == 1) ? '1 '.$name : "$count {$name}a";
        }

        if ($i + 1 < $j)
                {
                $seconds2 = $chunks[$i + 1][0];
                $name2 = $chunks[$i + 1][1];

                if (($count2 = floor(($aikapastettu - ($seconds * $count)) / $seconds2)) != 0)
                        {
        if ($count2 > 1 & $name2 == "päivä") {

        $output .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 {$name2}ä";

        } else {
        $output .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 {$name2}a";
        }

                        }
                }

//kuvalinkkien päätteet:
$kuvat = array('jpg','png','gif');

if (preg_match("/Haamu/", $line) or preg_match("/root/", $line) or preg_match("/Meteorologi/", $line) or preg_match("/kummitus/", $line)) { echo ''; } else {

//validoidaan vähäsen:
$linkki = str_replace('&','&amp;',$linkki);

//poistetaan turha tyhjä väli linkin lopusta:
$linkki = substr($linkki,0,-1);

//katsotaan linkin kolme viimeistä merkkiä:
$urlin_tiedostomuoto = substr($linkki, -3);
//jos on kuva niin tehdään jännittäviä juttuja!
if (in_array($urlin_tiedostomuoto, $kuvat)) { 
//paikallisen kuvan sijainti:
$kuvatiedosto = str_replace('/','',$linkki);
$kuvatiedosto = str_replace(':','',$kuvatiedosto);
$paikallinen_tiedosto = '/home/rolle/public_html/ircpics/'.$kuvatiedosto.'';
//jos tiedostoa ei ole cache-kansiossa:
if (!file_exists($paikallinen_tiedosto)) {
//haetaan se sinne...
copy($linkki, $paikallinen_tiedosto);
}
echo '<li class="linkki"><a href="'.$linkki.'" title="'.$output.' sitten" rel="/ircpics/'.$kuvatiedosto.'" class="screenshot">'.basename($linkki).'</a><a href="'.$linkki.'" title="'.$output.' sitten" class="url screenshot" rel="/ircpics/'.$kuvatiedosto.'"><img src="http://www.google.com/s2/favicons?domain='.$linkki.'" />'.$linkki.'</a></li>';

//jos ei ole kuva niin tehdään vähemmän jännittäviä juttuja...
} else {
//linkin tulostus alkaa
//lyhennetään vähän linkin ulkonäköä:
$linkki2 = str_replace('http://','',$linkki);
$lyhytlinkki = explode("/", $linkki2);
$sivusto = explode(".", $lyhytlinkki[0]);
echo '<li class="linkki"><a href="'.$linkki.'" title="'.$output.' sitten" class="linkki '.$sivusto[0].''.$sivusto[1].'">'; 
echo page_title($linkki);
//jos linkki on ylipitkä niin pätkästään:
//ei nyt tarvetta huoh...
//echo substr($linkki,0,30); 
//if (!strlen($linkki) > 30) { 
//echo '...'; 

echo '</a><a href="'.$linkki.'" class="url"><img src="http://www.google.com/s2/favicons?domain='.$lyhytlinkki[0].'" />'.$linkki.'</a></li>
';
//echo '<span class="aika">('.$output.' sitten)</span><br />'; 
} 
}
}
//echo '<li class="linkki"><a class="showmore" href="linkit_500.php" title="Enemmän linkkejä. Moar fun."><b>MOAAAAAR</b></a></li>';
echo '<li class="linkki"><a class="showmore" href="http://peikko.us/pics/" title="Vain kuvia?"><b>RANDOM IRC PIC PLZ!</b></a></li>';

fclose($fp);
}


?>
</ul>
</div>
</body>
</html>
<?php
  $fp = fopen($cachefile, 'w');
  fwrite($fp, ob_get_contents());
  fclose($fp);
  ob_end_flush();