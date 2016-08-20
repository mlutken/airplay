<?php 
// $Id$

function music_search_test( )
{
//	dfb("hat", "Log message");
// 	return "dsfdsf";
//	$a[] = music_search_test__get_data_album_song_rel2();
 	$a[] = music_search_test__get_data_album2();
//   	$a[] = music_search_test__get_data_album_max();
//    	$a[] = music_search_test__get_data_album_max();
//    	$a[] = music_search_test__get_data_album_max();
//    	$a[] = music_search_test__get_data_album_max();
// 	$a[] = music_search_test__get_data_album_min();
// 	$a[] = music_search_test__get_data_artist();
// 	$a[] = music_search_test__get_data_artist();

	
	
 	dprint_r ( $a );
// 	dfb ( $a, "a BEFORE" );
	airplay_insert_update_data($a);	
 	dprint_r ( $a );
// 	dfb ( $a, "a AFTER" );
	
	
// 	dprint_r ( airplay_base_media_format_name_to_id_array() );
//  	music_search_test__lookup_albums();
// 	music_search_test__lookup_artists();
// 	music_search_test__lookup_songs();
}




function music_search_test__get_data_album_min()
{
	$a = array ();
	$a['data_record_type'] = 'song';
	$a['artist_status']		= 1;
	
	$a['artist_name'] = 'Shu-bi-dua';
// 	$a['artist_url'] = 'http://shu-bi-dua.dk';
 	$a['genre_name'] = 'Pop/Rock';
	
 	$a['album_name'] = 'Shu-bi-dua 4';
// 	$a['album_year'] = 1977;
	
	$a['song_name'] = 'Sommergryder';
 	$a['song_year'] = 1977;
	
 	$a['record_label_name'] = 'Emi';
 	$a['record_label_url'] = 'http://emi.dk';
	
	$a['record_store_name'] = 'Bilka';
	$a['record_store_url'] = 'http://bilka.dk';
	
	$a['distribution_form_name'] = 'retail';
	$a['media_format_name'] = 'CD';
	$a['currency_name'] = 'DKK';
	$a['price_local'] = 113.3;
	$a['buy_at_url'] = 'http://fona.dk/default.aspx';
	
// // 	$a['force_update'] = "album_id";
	$a['do_update'] = array ();
	return $a;
}


function music_search_test__get_data_album_song_rel()
{
	$a = array ();
	$a['data_record_type'] = 'song';
	$a['artist_status']		= 1;
	
// 	$a['artist_name'] = 'Shu-bi-dua';
	$a['artist_name'] = 'Shubberne';
	$a['artist_url'] = 'http://shu-bi-dua.dk';
 	$a['genre_name'] = 'Pop/Rock';
	
 	$a['album_name'] = 'Shu-bi-dua 4';
// 	$a['album_year'] = 1977;
	
	$a['song_name'] = 'Sommergryder';
 	$a['song_year'] = 1977;
	
 	$a['record_label_name'] = 'Emi';
 	$a['record_label_url'] = 'http://emi.dk';
	
	$a['record_store_name'] = 'Bilka';
	$a['record_store_url'] = 'http://bilka.dk';
	
	$a['distribution_form_name'] = 'retail';
	$a['media_format_name'] = 'CD';
	$a['currency_name'] = 'DKK';
	$a['price_local'] = 113.3;
	$a['buy_at_url'] = 'http://fona.dk/default.aspx';
	
	$a['track_number'] = 7;
	
	$a['do_insert'] = DefaultTables::I()->getInsertList__song();
 // 	$a['do_insert'] = array_merge ( DefaultTables::I()->getInsertList__song(), array('album_song_rel') );
	
	$a['do_update'] = array
		( 
 			'song' => array( 'song_year', 'genre_id'  ),
 			'album_song_rel' => array( 'track_number' ),
			'DUMMY' => array( )
		);
// 	$a['do_update'] = array ();
	return $a;
}


function music_search_test__get_data_album_song_rel2()
{
	$a = array ();
	$a['data_record_type'] = 'song';
	$a['artist_status']		= 1;
	
	$a['artist_name'] = 'Turner, Tina';
	$a['artist_url'] = 'http://tinaturner.com';
	$a['genre_name'] = 'Pop/Rock'; // 'Classical'
	
 	$a['album_name'] = 'Tina';
	
	$a['song_name'] = 'The best';
 	$a['song_year'] = 1987;
	
 	$a['record_label_name'] = 'Emi';
 	$a['record_label_url'] = 'http://emi.dk';
	
	$a['record_store_name'] = 'Bilka';
	$a['record_store_url'] = 'http://bilka.dk';
	
	$a['distribution_form_name'] = 'retail';
	$a['media_format_name'] = 'CD';
	$a['currency_name'] = 'DKK';
	$a['price_local'] = 15.3;
	$a['buy_at_url'] = 'http://fona.dk/default.aspx';
	
	$a['track_number'] = 2;
	
	$a['do_insert'] = DefaultTables::I()->getInsertList__song();
 // 	$a['do_insert'] = array_merge ( DefaultTables::I()->getInsertList__song(), array('album_song_rel') );
	
	$a['do_update'] = array
		( 
 			'artist' => array( 'artist_url', 'genre_id'  ),
 			'song' => array( 'song_year', 'genre_id'  ),
 			'album_song_rel' => array( 'track_number' ),
			'buy_song' => array( 'price_local', 'currency_id', 'buy_at_url', 'timestamp_updated' ),
			'DUMMY' => array( )
		);
// 	$a['do_update'] = array ();
	return $a;
}


function music_search_test__get_data_album2()
{
	$a = array ();
	$a['data_record_type'] = 'album';
	$a['artist_status']		= 1;
	
	$a['artist_name'] = 'Turner, Tina';
	$a['artist_url'] = 'http://tinaturner.com';
	$a['genre_name'] = 'Pop/Rock'; // 'Classical'
	
 	$a['album_name'] = 'Tina';
	
	
 	$a['record_label_name'] = 'Emi';
 	$a['record_label_url'] = 'http://emi.dk';
	
	$a['record_store_name'] = 'Bilka';
	$a['record_store_url'] = 'http://bilka.dk';
	
	$a['distribution_form_name'] = 'retail';
	$a['media_format_name'] = 'CD';
	$a['currency_name'] = 'DKK';
	$a['price_local'] = 118.3;
	$a['buy_at_url'] = 'http://fona.dk/default.aspx';
	
	
	$a['do_insert'] = DefaultTables::I()->getInsertList__album();
	
	$a['do_update'] = array
		( 
 			'artist' => array( 'artist_url', 'genre_id'  ),
 			'album' => array( 'album_year', 'genre_id'  ),
			'buy_album' => array( 'price_local', 'currency_id', 'buy_at_url', 'timestamp_updated' ),
			'DUMMY' => array( )
		);
	return $a;
}


function music_search_test__get_data_album_max()
{
	$a = array ();
	$a['data_record_type'] = 'album';
	$a['artist_status']		= 1;
	
	$a['artist_name'] = 'Kim Larsen';
	$a['artist_url'] = 'http://www.kim-larsen.dk';
	$a['genre_name'] = 'Pop/Rock'; // HipHop/Rap ;  Pop/Rock
	
	$a['album_name'] = 'Midt om natten';
	$a['album_year'] = 1984;
	
	$a['song_name'] = 'Midt om natten';
	$a['song_year'] = 1984;
	
// 	$a['record_label_name'] = 'Sony Music';
// 	$a['record_label_url'] = 'http://sony-music.dk';
// 	$a['record_label_url'] = 'http://www.sony-music.dk';
	
 	$a['record_label_name'] = 'Emi';
 	$a['record_label_url'] = 'http://emi.dk';
// 	$a['record_label_url'] = 'http://www.emi.dk';
	
	$a['record_store_name'] = 'Bilka';
	$a['record_store_url'] = 'http://www.bilka.dk';
	
	$a['distribution_form_name'] = 'retail';
	$a['media_format_name'] = 'CD';
	$a['currency_name'] = 'DKK';
	$a['price_local'] = 21.50;
	$a['buy_at_url'] = 'http://fona.dk/default.aspx?album=1234';
	
// 	$a['do_insert'] = DefaultTables::I()->getInsertList__album();
	$a['do_insert'] = DefaultTables::I()->getInsertList__song();
	
// // 	$a['force_update'] = "artist_url";
	$a['do_update'] = array
		( 
// 			'artist' => array( 'artist_url'),
// 			'album' => array( 'album_year', 'genre_id' ),
 			'song' => array( 'song_year', 'genre_id', 'album_id' ),
// 			'buy_album' => array( 'price_local', 'currency_id', 'buy_at_url', 'timestamp_updated' ),
			'buy_song' => array( 'price_local', 'currency_id', 'buy_at_url', 'timestamp_updated' ),
			'DUMMY' => array( )
		);

	return $a;
}



function music_search_test__get_data_album_hest()
{
	$a = array ();
	$a['data_record_type'] = 'album';
	$a['artist_status']		= 1;
	
	$a['artist_name'] = 'Kim Larsen';
	$a['artist_url'] = 'http://www.kim-larsen.dk';
	$a['country_name'] = 'Denmark';
	$a['genre_name'] = 'HipHop/Rap';
	$a['do_insert'] = array('artist');
	return $a;
}

function music_search_test__get_data_album_lort()
{
	$a = array ();
	$a['record_store_name'] = 'Megastore.Se';
	$a['record_store_url'] = 'http://www.megastore.se';
	$a['buy_at_url'] = 'http://www.megastore.se/template/next%2CProduct.vm?itemid=980808';
	$a['artist_name'] = 'HEST';
	$a['album_name'] = 'Louden Up Now';
	$a['data_record_type'] = 'album';
	$a['price_local'] = 159;
	$a['currency_name'] = 'SEK';
	$a['artist_status'] = '1';
	$a['artist_url'] = '';
	$a['do_insert'] = array( 'record_store','artist','album','buy_album','buy_album_history' );
	return $a;
}

// 		<>Megastore.Se</record_store_name>
// 		<></record_store_url>
// 		<><![CDATA[]]></buy_at_url>
// 		<><![CDATA[]]></artist_name>
// 		<><![CDATA[]]></album_name>
// 		<></data_record_type>
// 		<></price_local>
// 		<></currency_name>
// 		<>1</artist_status>
// 		<></artist_url>
// 		<do_insert></do_insert>


function music_search_test__get_data_artist()
{
	$a = array ();
	$a['data_record_type'] = 'album';
	$a['artist_status']		= 1;
	
	$a['artist_name'] = 'Nordstrøm';
	$a['artist_url'] = 'http://www.nordstroem.dk';
	$a['country_name'] = 'Denmark';
	$a['genre_name'] = 'HipHop/Rap';
	$a['do_insert'] = array('artist');
	return $a;
}


function music_search_test__get_data_album_max_ready()
{
	$a = array ();
	$a['data_record_type'] = 'album';
	
	$a['album_name'] = 'Midt om natten';
	$a['album_id'] = 12;
	$a['genre'] = 'Pop/Rock';
	$a['genre_id'] = 1;
	$a['album_year'] = 1984;
	
	$a['artist_name'] = 'Kim Larsen';
	$a['artist_id'] = 5;
	$a['artist_url'] = 'kim-larsen.dk';
	
	$a['record_label_name'] = 'Emi';
	$a['record_label_id'] = 3;
	$a['record_label_url'] = 'http://emi.dk';
	
	$a['record_store_name'] = 'Fona';
	$a['record_store_id'] = 2;
	$a['record_store_url'] = 'http://fona.dk';
	
	$a['media_format_name'] = 'CD';
	$a['media_format_id'] = 5;
	$a['currency_name'] = 'DKK';
	$a['currency_id'] = 2;
	$a['price_local'] = 109;
	$a['buy_at_url'] = 'http://fona.dk/default.aspx?m=2&i=63&searchArtist=';
	
	
	return $a;
}


function music_search_test__lookup_albums()
{
	$aNew = array ("Den blå hund", "Nærmest lykkelig", "Reckless", "Bodies", "Dizzy Mizz Lizzy" );
	dprint_r ( $aNew );
	$aExists = airplay_base_lookup_albums( $aNew );
	dprint_r ( $aExists );
//	dfb($aExists, "Log message: $aExists");
}


function music_search_test__lookup_artists()
{
	$aNew = array ("Gnags", "Bryan Adams", "Kim Larsen", "Robbie williams", "DAD" );
	dprint_r ( $aNew );
	$aExists = airplay_base_lookup_artists( $aNew );
	dprint_r ( $aExists );
//	dfb($aExists, "Log message: $aExists");
}

function music_search_test__lookup_songs()
{
	$aNew = array ("Bodies", "Den Blå Hund", "Summer of 69");
	dprint_r ( $aNew );
	$aExists = airplay_base_lookup_songs( $aNew );
	dprint_r ( $aExists );
	//dfb($aExists, "Log message: $aExists");
}

