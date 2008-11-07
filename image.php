<?php

function removeMagicQuotes (&$postArray, $trim = false) {
	if (!get_magic_quotes_gpc()) {
		return;
	}

	foreach ($postArray as $key => $val) {
		if (is_array($val)) {
			removeMagicQuotes ($postArray[$key], $trim);
		} else {
			if ($trim == true) {
				$val = trim($val);
			}
			$postArray[$key] = stripslashes($val);
		}
	}   
}

function getCalculatedColor( $inputString ) {
  $numHexDigits = 16;
  $colors = array(
    array('r'=>'246', 'g'=>'130', 'b'=>'31'),
    array('r'=>'0',   'g'=>'0',   'b'=>'0'),
    array('r'=>'135', 'g'=>'200', 'b'=>'10'),
    array('r'=>'0',   'g'=>'173', 'b'=>'239'),
    array('r'=>'140', 'g'=>'40',  'b'=>'139'),
  );
  $hexDigit = substr( md5( $inputString ), -1 );
  $colorIndex = intval( sizeof($colors) * hexdec( $hexDigit ) / $numHexDigits );
  $rgb = $colors[$colorIndex];
  
  return $rgb;
}

removeMagicQuotes($_GET);

$imgPath = "http://thetenwordreview.com/images/icons/chewbeaker.png";
$title = "Roast Dinner";
$term = "Lamb pisses all over pork.  Roast potatoes are the best.";
$tagline = "a food review by chewbeaker";

$imgPath = $_GET['img'];
$title   = $_GET['title'];
$term    = $_GET['term'];
$tagline = $_GET['tagline'];
$background = getCalculatedColor($title.$term);

$im = imagecreatetruecolor(1024, 443);

$col = imagecolorallocate($im, $background['r'], $background['g'], $background['b']);
imagefill($im, 0, 0, $col);

$col = imagecolorallocate($im, 255, 255, 255);
imagefilledrectangle($im, 53, 120, 53+192+5, 120+192+5, $col);

$im = iconInsert($im, $imgPath);

imagefttext($im, 17.5, 0, 268, 78, $col, "./arialbd.ttf", $title);
$im = reviewTextInsert($im, $term, $col);
imagefttext($im, 17.5, 0, 268, 380, $col, "./arialbd.ttf", $tagline);

header("Content-type: image/png");
imagepng($im);


function iconInsert($im, $userIconPath){
    $userIcon = imagecreatefrompng($userIconPath);

   $insertWidth = imagesx($userIcon);
   $insertHeight = imagesy($userIcon);

   $imageWidth = imagesx($im);
   $imageHeight = imagesy($im);

   $overlapX = 56;
   $overlapY = 123;

//   imagecopymerge($im,$userIcon,$overlapX,$overlapY,0,0,$insertWidth,$insertHeight,100);
   imagecopyresampled($im,$userIcon,$overlapX,$overlapY,0,0,192,192,$insertWidth,$insertHeight);
   return $im;
}

function reviewTextInsert($im, $text, $col) {
    $fontFile = "./Arial Unicode.ttf";
    $maxFontSize = 50;
    $minFontSize = 20;
    $maxWidth = 650;
    $maxHeight = 192;
    $maxPieces = 3;
    
    $best = array(
        'pieces' => 5,
        'size' => $minFontSize
    );
    
    for ($i=1; $i<=$maxPieces; $i++) {
        $currentText = splitText($text, $i);
        $fontSize = $maxFontSize;
        while( $fontSize >= $minFontSize ) {
            $fontSize--;
            $bbox = imagettfbbox ($fontSize, 0, $fontFile, $currentText);
    
            $width  = abs($bbox[2] - $bbox[0]);
            $height = abs($bbox[1] - $bbox[7]);
    
            if ( $width <= $maxWidth && $height <= $maxHeight ) {
                if ( $fontSize > $best['size'] ) {
                    $best['pieces'] = $i;
                    $best['size'] = $fontSize;
                    $best['width'] = $width;
                    $best['height'] = $height;
                }
                continue;
            }
        }
    }
    
    $imHeight = imagesy($im);
    $bbox = imagettfbbox($best['size'], 0, $fontFile, $text);
    $baseHeight = abs($bbox[1] - $bbox[7]);
    
    imagefttext($im, $best['size'], 0, 268, ((($imHeight - $best['height']) / 2) + $best['size']), $col, $fontFile, splitText($text, $best['pieces']));//, array('linespacing'=>0.9));
    return $im;
}

function splitText($text, $pieces) {
    if ( 1 == $pieces ) {
        return trim($text);
    } else if ( 2 == $pieces ) {
        $halfPos = mb_strlen($text)/2;
        
        $firstSpacePos = mb_strpos($text, ' ', $halfPos);
        return trim(mb_substr($text, 0, $firstSpacePos))."\n".trim(mb_substr($text, $firstSpacePos+1));
    } else if ( 3 == $pieces ) {
        $thirdPos = mb_strlen($text)/3;
        
        $firstSpacePos = trim(mb_strpos($text, ' ', $thirdPos));
        
        return trim(mb_substr($text, 0, $firstSpacePos))."\n"
              .splitText(mb_substr($text, $firstSpacePos+1), 2);
    }
    
    
    
    return $text;
}
?>