<?php 
	include("XmlStreamer.php");

	$xml_file = "discogs_20140501_artists.xml";

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
		//<artist>
		//<images>
		//<image height="172" type="primary" uri="http://api.discogs.com/image/A-3767533-1396567573-3257.jpeg" uri150="http://api.discogs.com/image/A-150-3767533-1396567573-3257.jpeg" width="504" />
		//</images>
		//<id>3767533</id>
		//<name>Beheaded Baptist</name>
		//<data_quality>Needs Major Changes</data_quality>
		//</artist>
		
        $xml = simplexml_load_string($xmlString);
		$data_quality = (string)$xml->data_quality;
		//if ($data_quality == "Correct") {
			$artist_id = (string)$xml->id;
			$artist_name = (string)$xml->name;
			$start_char = substr($artist_name, 0, 1);
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
			$this->sql[] = '(?,?,?,?,?)';
			$this->values[] = $artist_id;
			$this->values[] = $artist_name;
			$this->values[] = $image_url;
			$this->values[] = $data_quality;
			$this->values[] = $start_char;
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
        $command = $this->pdo->prepare('INSERT INTO discogs_artists VALUES '.implode(',',$this->sql));
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