<?php 
	include("XmlStreamer.php");

	$xml_file = "discogs_20140501_releases.xml";

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
		<release id="208031" status="Accepted">
		<images>
			<image height="599" type="primary" uri="http://api.discogs.com/image/R-208031-1177074853.jpeg" uri150="http://api.discogs.com/image/R-150-208031-1177074853.jpeg" width="599" />
			<image height="599" type="secondary" uri="http://api.discogs.com/image/R-208031-1177070743.jpeg" uri150="http://api.discogs.com/image/R-150-208031-1177070743.jpeg" width="598" />
			<image height="599" type="secondary" uri="http://api.discogs.com/image/R-208031-1177070768.jpeg" uri150="http://api.discogs.com/image/R-150-208031-1177070768.jpeg" width="598" />
			<image height="86" type="secondary" uri="http://api.discogs.com/image/R-208031-1177070892.jpeg" uri150="http://api.discogs.com/image/R-150-208031-1177070892.jpeg" width="599" />
		</images>
		<artists>
			<artist><id>109519</id><name>Polar Pop</name><anv /><join>feat.</join><role /><tracks /></artist>
			<artist><id>153882</id><name>MC Grzimek</name><anv /><join /><role /><tracks /></artist>
		</artists>
		<title>Eisbär</title>
		<labels>
			<label catno="877 459-1" name="Metronome" />
		</labels>
		<extraartists>
			<artist><id>7549</id><name>Grauzone</name><anv /><join /><role>Producer</role><tracks /></artist>
			<artist><id>40303</id><name>Heinz Felber</name><anv /><join /><role>Producer</role><tracks /></artist>
			<artist><id>44154</id><name>Michael Rödiger</name><anv /><join /><role>Producer</role><tracks /></artist>
			<artist><id>61591</id><name>Torsten Fenslau</name><anv /><join /><role>Producer</role><tracks /></artist>
		</extraartists>
		<formats>
			<format name="Vinyl" qty="1" text="">
				<descriptions>
					<description>12"</description>
				</descriptions>
			</format>
		</formats>
		<genres>
			<genre>Electronic</genre>
		</genres>
		<styles>
			<style>Euro House</style>
		</styles>
		<country>Germany</country>
		<released>1990</released>
		<master_id>62497</master_id>
		<data_quality>Needs Vote</data_quality>
		<tracklist>
			<track><position>A</position><title>Eisbär (WWF-Mix)</title><duration>9:06</duration></track>
			<track><position>B</position><title>Babu The Polar Bear (Instrumental)</title><duration>9:06</duration></track>
		</tracklist>
		<videos>
			<video duration="550" embed="true" src="http://www.youtube.com/watch?v=eUElf8Xx04E"><title>Polar Pop feat. MC Grzimek - Eisbär (WWF-Mix)</title><description>Polar Pop feat. MC Grzimek - Eisbär (WWF-Mix)</description></video>
		</videos>
		<companies />
		</release>
		*/

        $xml = simplexml_load_string($xmlString);
		
			$format_desc = (string)$xml->formats->format->descriptions->description;
			if ($format_desc == "Album") {
				$release_id = (int)$xml->attributes()->id;
				$data_quality = (string)$xml->data_quality;
				$format = (string)$xml->formats->format->attributes()->name;
				$format_qty = (int)$xml->formats->format->attributes()->qty;
				$master_id = (int)$xml->master_id;
				$title = (string)$xml->title;

				$this->sql[] = '(?,?,?,?,?,?,?)';
				$this->values[] = $release_id;
				$this->values[] = $title;
				$this->values[] = $format;
				$this->values[] = $format_desc;
				$this->values[] = $format_qty;
				$this->values[] = $master_id;
				$this->values[] = $data_quality;
		}
    }

    /**
     * Called after a file chunk was processed (16KB by default, see constructor)
     */
    public function chunkCompleted()
    {
        if($this->sql===array()) {
            return;
        }
        $command = $this->pdo->prepare('INSERT INTO discogs_releases VALUES '.implode(',',$this->sql));
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