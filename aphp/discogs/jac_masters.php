<?php 
	include("XmlStreamer.php");

	$xml_file = "discogs_20140401_masters.xml";


class SimpleXmlStreamer extends \Prewk\XmlStreamer
{
    public function processNode($xmlString, $elementName, $nodeIndex)
    {
        $xml = simplexml_load_string($xmlString);
		<master id="87974">
		<main_release>1343229</main_release>
		<images>
		<image height="595" type="primary" uri="http://api.discogs.com/image/R-1343229-1265330292.jpeg" uri150="http://api.discogs.com/image/R-150-1343229-1265330292.jpeg" width="600" />
		<image height="468" type="secondary" uri="http://api.discogs.com/image/R-1343229-1236797172.jpeg" uri150="http://api.discogs.com/image/R-150-1343229-1236797172.jpeg" width="541" />
		</images>
		<artists>
		<artist>
			<id>132509</id>
			<name>Geinoh Yamashirogumi</name>
			<anv />
			<join />
			<role />
			<tracks />
		</artist>
		</artists>
		<genres>
		<genre>Electronic</genre>
		<genre>Stage &amp; Screen</genre>
		</genres>
		<styles>
			<style>Soundtrack</style>
		</styles>
		<year>1988</year>
		<title>Symphonic Suite Akira</title>
		<data_quality>Correct</data_quality>
		<videos>
		<video duration="611" embed="true" src="http://www.youtube.com/watch?v=mXjVGN4Xoi4">
		<title>Geinoh Yamashirogumi - Akira OST - Shohmyoh</title>
		<description>Geinoh Yamashirogumi - Akira OST - Shohmyoh</description>
		</video>
		<video duration="865" embed="true" src="http://www.youtube.com/watch?v=ocsiEQ1sljA"><title>Akira - Geinoh Yamashirogumi - Requiem</title><description>Akira - Geinoh Yamashirogumi - Requiem</description></video>
		<video duration="4166" embed="true" src="http://www.youtube.com/watch?v=utA3TQF3pZk"><title>Geinoh Yamashirogumi - Akira OST (Full Album)</title><description>Geinoh Yamashirogumi - Akira OST (Full Album)</description>
		</video>
		</videos>
		</master>

        //$something = (string)$xml->artists->artist->name;
		$data_quality = (string)$xml->data_quality;
		if ($data_quality == "Correct") {
			$artist_id = (string)$xml->id;
			$artist_name = (string)$xml->name;
			// Max 1000 pr day - otherwise mail them
			$image_count = count ($xml->images->image);
			if ($image_count > 0) {
				for ($i = 1; $i <= $image_count; $i++) {
					var_dump($xml->images->image);
				}
			}
			echo "$nodeIndex: Extracted string '$artist_id' from parent node '$artist_name'\n";     
		//var_dump($xml->name);
		}
		
        //echo "$nodeIndex: Extracted string '$something' from parent node '$elementName'\n";     
        return true;
    }
}

$streamer = new SimpleXmlStreamer($xml_file);
if ($streamer->parse()) {
    echo "Finished successfully";
} else {
    echo "Couldn't find root node";
}

?>