<?php 
	include("XmlStreamer.php");

	$xml_file = "discogs_20140501_masters.xml";

class SimpleXmlStreamer extends \Prewk\XmlStreamer
{
    protected $pdo;
    protected $sql = array();
    protected $values = array();
	
    /**
     * Called after the constructor completed class setup
     */
    public function init()
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=airplay_music_v1;charset=utf8', 'airplay_user','Deeyl1819');
    }

    public function processNode($xmlString, $elementName, $nodeIndex)
    {
		/*
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
		*/

        $xml = simplexml_load_string($xmlString);
		$data_quality = (string)$xml->data_quality;
		//if ($data_quality == "Correct") {
		
			$master_id = (int)$xml->attributes()->id;
			$main_release = (int)$xml->main_release;
			$title = (string)$xml->title;
			$year = (string)$xml->year;
			$image_url = "";
			// Max 1000 pr day - otherwise mail them
			$image_count = count ($xml->images->image);
			if ($image_count > 0) {
				for ($i = 0; $i < $image_count; $i++) {
					if ($xml->images->image->attributes()->type == "primary") {
						$image_url = $xml->images->image->attributes()->uri;
					}
				}
			}
			$this->sql[] = '(?,?,?,?,?,?)';
			$this->values[] = $master_id;
			$this->values[] = $main_release;
			$this->values[] = $title;
			$this->values[] = $year;
			$this->values[] = $image_url;
			$this->values[] = $data_quality;
		//}
    }

    /**
     * Called after a file chunk was processed (16KB by default, see constructor)
     */
    public function chunkCompleted()
    {
        if($this->sql===array()) {
            return;
        }
        $command = $this->pdo->prepare('INSERT INTO discogs_masters VALUES '.implode(',',$this->sql));
        $command->execute($this->values);

        $this->sql = $this->values = array();
    }
}

	$streamer = new SimpleXmlStreamer($xml_file);
	if ($streamer->parse()) {
		echo "Finished successfully";
	} else {
		echo "Couldn't find root node";
	}

?>