<?php

function startsWithVowel($text) {
      // convert to ascii first to make our life easier
      $text = iconv( 'UTF-8', 'ASCII//TRANSLIT', $text);

      $search = array(
          '/\+/',              // plus gets replaced with string ' and '
          '/&/',              // ampersand gets replaced with string ' and '
          "/'/",              // apostrophes are replaced with nothing
          '/[^a-zA-Z0-9\s]/', // anything other than a-z, A-Z, 0-9 and whitespace becomes a space 
          '/\s\s+/',          // multiple spaces become a single space
          '/^\s+/',           // spaces at the beginning of a string are removed
          '/\s+$/'            // spaces at the end of the string are removed
      );
      $replace = array( 
          ' and ',
          ' and ',
          '',
          ' ', 
          ' ', 
          '', 
          ''
      );
      $text = preg_replace($search, $replace, $text);
      
      $text = strtolower( $text );
      $text = trim( $text );
      
      $char = strtolower(substr($text, 0, 1));
  
      return in_array($char, array('a', 'e', 'i', 'o', 'u'));
}

$reviews = array();
$url = "http://thetenwordreview.com/api/reviews/get?key=b0f9b471145908c821940dc2905de519&order=p&output=php&num=30";
if ( isset($_GET['username']) ) {
	$url .= "&user=".$_GET['username'];
}

$s = curl_init();
curl_setopt($s,CURLOPT_URL, $url);
curl_setopt($s,CURLOPT_HEADER,false);
curl_setopt($s,CURLOPT_RETURNTRANSFER,1);
$result = curl_exec($s);
curl_close( $s );

$reviews = unserialize($result);

$output = '';

foreach ( $reviews as $review ) {
	$image = urlencode("http://thetenwordreview.com/images/icons/".$review['u_username'].".png");
	$title = urlencode($review['r_title']);
	$term = urlencode($review['ri_ten_words']);
	
	$prefix = startsWithVowel($review['c_title']) ? 'an' : 'a';
	$tagline = urlencode(strtolower($prefix.' '.$review['c_title'].' review by '.$review['u_username']));
	
	$imgUrl = "http://thecodetrain.co.uk/code/ten-word-moo-card/image.php?img=$image&amp;title=$title&amp;term=$term&amp;tagline=$tagline";

	//$output .= "<p><img src='$imgUrl' alt='$title'></p>";

	$textCollection = <<<XML
	 	<text_collection>  
			<minicard>  
				<text_line>  
					<id>1</id>  
					<string>This is line 1</string>  
					<bold>true</bold>  
					<align>left</align>  
					<font>modern</font>  
					<colour>#ff0000</colour>  
				</text_line>  
				<text_line>  <id>2</id>  <string>This is line 2</string>  </text_line>  <text_line>  <id>3</id>  <string>This is line 3</string>  </text_line>  <text_line>  <id>4</id>  <string>This is line 4</string>  </text_line>  <text_line>  <id>5</id>  <string>This is line 5</string>  </text_line>  <text_line>  <id>6</id>  <string>This is line 6</string>  </text_line>  </minicard>  </text_collection> 
XML;

	$output .= <<<XML
<design>
	<image> 
		<url>$imgUrl</url> 
		<type>variable</type> 
	</image>
</design>
XML;

}

if (isset($_GET['buy'])) {
	//header("Location: ");

	$xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?> 
<moo xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.moo.com/xsd/api_0.7.xsd"> 
	<request> 
		<version>0.7</version> 
		<api_key>035b545b-6887-5756bae7-484ff7a2-d287</api_key> 
		<call>build</call> 
	</request> 
	<payload> 
		<products> 
			<product> 
				<product_type>minicard</product_type> 
				<designs> 
$output
				</designs>
			</product> 
		</products> 
	</payload> 
</moo>	
XML;

	$s = curl_init();
	curl_setopt($s,CURLOPT_URL, "http://www.moo.com/api/api.php");
	curl_setopt($s,CURLOPT_HEADER,false);
	curl_setopt($s,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($s, CURLOPT_POST, 1 );
	curl_setopt($s, CURLOPT_POSTFIELDS, "method=direct&xml=".urlencode($xml) );
	$result = curl_exec($s);
	curl_close( $s );
	
	$resDoc = simplexml_load_string($result);
	header("Location:".$resDoc->payload->start_url);
	exit;
}

?>