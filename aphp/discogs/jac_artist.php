<?php 
	include("XmlStreamer.php");

	$xml_file = "discogs_20140401_artists.xml";


class SimpleXmlStreamer extends \Prewk\XmlStreamer
{
    public function processNode($xmlString, $elementName, $nodeIndex)
    {
        $xml = simplexml_load_string($xmlString);
		//<artist>
		//<images>
		//<image height="172" type="primary" uri="http://api.discogs.com/image/A-3767533-1396567573-3257.jpeg" uri150="http://api.discogs.com/image/A-150-3767533-1396567573-3257.jpeg" width="504" />
		//</images>
		//<id>3767533</id>
		//<name>Beheaded Baptist</name>
		//<data_quality>Needs Major Changes</data_quality>
		//</artist>

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