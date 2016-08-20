<?php

require_once ('dynamic_music_lookup/BaseDynamicMusic.php');
require_once ('dynamic_music_lookup/iTunes.php');
require_once ('dynamic_music_lookup/Spotify.php');
require_once ('dynamic_music_lookup/WiMP.php');
require_once ('dynamic_music_lookup/Rdio.php');
require_once ('dynamic_music_lookup/Deezer.php');
require_once ('dynamic_music_lookup/Napster.php');
require_once ('dynamic_music_lookup/7Digital.php');


class DynamicMusicFactory
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public  function __construct()
    {
    }


    public function createDynamicMusicSearchProvider ( $record_store_name )
    {
        switch ( $record_store_name ) 
        {
            case 'itunes'           :   return new iTunes();
            case 'spotify'          :   return new Spotify();
            case 'wimp'            :   return new WiMP();
            case 'rdio'              :   return new Rdio();
            case 'deezer'         :   return new Deezer();
            case 'napster'        :   return new Napster();
			case '7digital'        :   return new SevenDigital();
            default                  :   return null;
        }
    }

}

?>