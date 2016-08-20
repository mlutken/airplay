<?php
    
    /*
        TODO
        - Fly affiliate tracking fra display sider til denne 
        - Vis album/sange der starter med fx Strangelove -> Strangelove (extend....
        - Kan kun flere streaming, hvis ikke fundet i de forrige.
        - try catch 
        - validate output 
    */
    
    if (isset($_POST["s"]))  { $s = $_POST["s"];   } else { $s = "";  } // Service (Youtube)
    if (isset($_POST["q"]))  { $q = $_POST["q"];   } else { $q = "";  } // Search string
    
    /* Create Airplay Music Proxy */
    $ap_proxy_streaming = new APProxyStreaming(array( $s, $q));
    $ap_proxy_streaming->setProxyURL();
    
    /* Set headers and extra headers if needed */
    $ap_proxy_streaming->outputHTTPHeader();
    //$ap_proxy_streaming->outputExtraHTTPHeader();

    print $ap_proxy_streaming->getProxiedContent();

    class APProxyStreaming {

        function __construct($params) {
            $this->service = $params[0];
            $this->search_for_words = $params[1];
            $this->proxy_url = "";
        }
        
        function setProxyURL() {
            if ($this->service == "youtube") {
                $this->proxy_url = "http://gdata.youtube.com/feeds/api/videos/-/%7Bhttp%3A%2F%2Fgdata.youtube.com%2Fschemas%2F2007%2Fcategories.cat%7DMusic/%7Bhttp%3A%2F%2Fgdata.youtube.com%2Fschemas%2F2007%2Fkeywords.cat%7D" . $this->search_for_words;
            }
        }
        
        /*
            Get file content from the webservice.
        */
        function getProxiedContent() {
            if ($this->proxy_url != "") {
                $file_contents = file_get_contents( $this->proxy_url );
                return $file_contents;
            }
        }
        
        /*
            Output headers
        */
        function outputHTTPHeader() {
            header('Content-Type: text/xml');
        }
        
        /*
            Output extra headers if needed.
        */
        function outputExtraHTTPHeader() {
            if ($this->record_store == "wimp") {
                //header('Accept: application/json');
                header('Accept:application/xml'); // Output format xml/json
                header('PartnerKey:8fb0d8ce-4e36-49bd-b540-a689ccc'); // Unique Key
            }
        }
    }
?>