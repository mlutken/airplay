/********************************************************************************************************************
********************************************************************************************************************/
//																			 Google Youtube
/********************************************************************************************************************
********************************************************************************************************************/

var g_allYoutubeVideoIDs       = [];
var g_currentYoutubeVideoID    = -1;

function playNextYoutubeVideo()
{
    if ( g_currentYoutubeVideoID == -1) return;
    var iNumVideos = g_allYoutubeVideoIDs.length;
    g_currentYoutubeVideoID = (g_currentYoutubeVideoID + 1) % iNumVideos
    playerYoutube.loadVideoById( g_allYoutubeVideoIDs[g_currentYoutubeVideoID] );
};

function getYoutubeVideoId( videoEntry )
{
    var href = videoEntry.link[0].href;
    var iStart  = href.search("\\?v=") + 3;
    var iEnd    = href.search("&feature");
    var videoID = href.slice(iStart, iEnd);
    return videoID;
};

function asyncYoutubeSearch(sSearchFor, iMaxResults, callBack )
{
    //create a JavaScript element that returns our JSON data.
    var script = document.createElement('script');
    script.setAttribute('id', 'asyncYoutubeSearchScriptID');
    script.setAttribute('type', 'text/javascript');
    script.setAttribute('src', 'http://gdata.youtube.com/feeds/' + 
            'videos?vq=' + sSearchFor + '&max-results=' + iMaxResults + '&' + 
            'alt=json-in-script&callback=' + callBack +'&' + 
            'orderby=relevance&sortorder=descending&format=5');
    //attach script to current page -  this will submit asynchronous
    //search request, and when the results come back callback 
    //function showMyVideos(data) is called and the results passed to it
    document.documentElement.firstChild.appendChild(script);
};


function parseYoutubeVideoDataAndLoadFirst(data)
{
    var feed = data.feed;
    var entries = feed.entry || [];
    for (var i = 0; i < entries.length; i++)
    {
        var entry = entries[i];
        g_allYoutubeVideoIDs[i] = getYoutubeVideoId(entry);
    }
    if ( entries.length > 0 ) {
        g_currentYoutubeVideoID = 0;
        var videoID = getYoutubeVideoId(entries[0]);
        playerYoutube.loadVideoById( videoID );
    }    
};


/********************************************************************************************************************
********************************************************************************************************************/
//																			 Google image
/********************************************************************************************************************
********************************************************************************************************************/
var g_allArtistImages           = [];
var g_currentArtistImageIndex   = -1;

function showNextArtistImage()
{
	if ( g_currentArtistImageIndex == -1)  return;
	var iNumVideos = g_allArtistImages.length;
	g_currentArtistImageIndex = (g_currentArtistImageIndex + 1) % iNumVideos;
	var artistImage = document.getElementById('artistImageID');
	artistImage.src = g_allArtistImages[g_currentArtistImageIndex];
};
 
function parseGoogleImageDataAndLoadFirst(data) 
{
	if (data.results && data.results.length > 0) {
		var results = data.results;
		var result = results[0];
		var artistImage = document.getElementById('artistImageID');
		artistImage.src = result.tbUrl;
		// TODO: If we need to have more images so that users can help us find one that is good 
		g_currentArtistImageIndex = 0;
		for (var i = 0; i < results.length; i++) {
			var result = results[i];
			g_allArtistImages[i] = result.tbUrl;
		}
	}
};
function getArtistPicture() {
    google.setOnLoadCallback(getArtistFromGoogle);
};

function getArtistFromGoogle() {
    var imageSearch = new google.search.ImageSearch();
    // Restrict image size: IMAGESIZE_SMALL , IMAGESIZE_MEDIUM, IMAGESIZE_LARGE, IMAGESIZE_EXTRA_LARGE
    imageSearch.setRestriction(google.search.ImageSearch.RESTRICT_IMAGESIZE,
                                google.search.ImageSearch.IMAGESIZE_LARGE); 
    imageSearch.setResultSetSize(1); // Only load 1 image 
    imageSearch.setSearchCompleteCallback(this, parseGoogleImageDataAndLoadFirst, [imageSearch]);
    imageSearch.execute(g_sSearchString);
};



/********************************************************************************************************************
********************************************************************************************************************/
//																			 Shared functions
/********************************************************************************************************************
********************************************************************************************************************/
function ap_EncodeAffiliateLink(buy_at_url, affiliate_url, encode_times) {
    for (entimes = 1; entimes <= encode_times; entimes++) {
        buy_at_url = encodeURIComponent(buy_at_url);
    }
    buy_at_url = affiliate_url.replace("[TARGET_URL]", buy_at_url);
    return buy_at_url;
};

function ap_PriceFormat(value) {
    value = (value / 100);
    var price = value.toFixed(2);
	price = price.replace(".", ",");
    return price;
};

/* Get Max count pr product */
function ap_GetMaxStreamingCountPrProduct(start_element) {
    var max_streaming_count = 0;
    jQuery.each(jQuery(start_element + ' #streamings div.animation span.count'), function() { 
        var count = parseInt(jQuery(this).html(), 10);
        if (max_streaming_count < count) {
            max_streaming_count = count;
        }
    });
    return max_streaming_count;
};

function ap_HideStreamingAnimation(element) {
console.log("ap_HideStreamingAnimation" + element);
	if (element == "album") {
		//jQuery("#artist_wiki div.wiki").show();
		jQuery("#artist_wiki div.albums").hide();
		//jQuery("#artist_wiki div.video").hide();
		if (jQuery("#artist_wiki #tab_video").length) {
			jQuery("#artist_wiki #tab_video").addClass("selected");
			jQuery("#artist_wiki #tab_wiki").removeClass("selected");
			jQuery("#artist_wiki div.wiki").hide();
			jQuery("#artist_wiki div.video").show();
		} else {
			jQuery("#artist_wiki div.wiki").show();
			jQuery("#artist_wiki div.video").hide();
			jQuery("#artist_wiki div.songs").hide();
			jQuery("#artist_wiki #tab_wiki").addClass("selected");
			jQuery("#artist_wiki #tab_video").removeClass("selected");
		}
		jQuery("#artist_wiki #tab_albums").removeClass("selected");
	} else if (element == "song") {
		if (jQuery("#artist_wiki #tab_video").length) {
			jQuery("#artist_wiki div.video").hide();
		}
		jQuery("#artist_wiki div.wiki").show();
		jQuery("#artist_wiki div.songs").hide();
	} else if (element == "item_page") {
		jQuery("#album_wiki div.wiki").show();
		jQuery("#song_wiki div.wiki").show();
		jQuery("#album_wiki div.albums").hide();
		jQuery("#song_wiki div.songs").hide();
		jQuery("#song_wiki #tab_wiki").addClass("selected");
		jQuery("#song_wiki #tab_songs").removeClass("selected");
	}
};

function ap_ShowStreamingAnimation(element) {
	if (element == "album") {
		jQuery("#artist_wiki div.wiki").hide();
		jQuery("#artist_wiki div.video").hide();
		jQuery("#artist_wiki div.albums").show();
		jQuery("#artist_wiki div.albums #streamings").show();
		jQuery("#artist_wiki #tab_albums").addClass("selected");
		jQuery("#artist_wiki #tab_video").removeClass("selected");
		jQuery("#artist_wiki #tab_wiki").removeClass("selected");
	} else if (element == "song") {
		jQuery("#artist_wiki div.wiki").hide();
		jQuery("#artist_wiki div.songs").show();
		jQuery("#artist_wiki div.songs #streamings").show();
	} else if (element == "item_page") {
		jQuery("#album_wiki div.wiki").hide();
		jQuery("#song_wiki div.wiki").hide();
		jQuery("#album_wiki div.albums").show();
		jQuery("#song_wiki div.songs").show();
		jQuery("#song_wiki #tab_songs").addClass("selected");
		jQuery("#song_wiki #tab_wiki").removeClass("selected");
	}
};

function ap_ShowVideo(element) {
	if (element == "album") {
		jQuery("#artist_wiki div.video").show();
		jQuery("#artist_wiki div.wiki").hide();
		jQuery("#artist_wiki div.albums").hide();
		jQuery("#artist_wiki div.albums #streamings").hide();
		jQuery("#artist_wiki #tab_video").addClass("selected");
		jQuery("#artist_wiki #tab_wiki").removeClass("selected");
		jQuery("#artist_wiki #tab_albums").removeClass("selected");
	} else if (element == "item_page") {
		jQuery("#song_wiki div.wiki").show();
		jQuery("#song_wiki div.songs").hide();
		jQuery("#song_wiki #tab_wiki").addClass("selected");
		jQuery("#song_wiki #tab_songs").removeClass("selected");
	}
};

function ap_ShowWikiFromTab(element) {
	if (element == "album") {
		jQuery("#artist_wiki div.wiki").show();
		jQuery("#artist_wiki div.video").hide();
		jQuery("#artist_wiki div.albums").hide();
		jQuery("#artist_wiki div.albums #streamings").hide();
		jQuery("#artist_wiki #tab_wiki").addClass("selected");
		jQuery("#artist_wiki #tab_video").removeClass("selected");
		jQuery("#artist_wiki #tab_albums").removeClass("selected");
	} else if (element == "item_page") {

	}
};

function ap_colorOddEvenPriceTable() {
    var table = jQuery('table.list-price-table');
    var rows = jQuery('tbody > tr:visible',table);
  
    jQuery.each(rows, function(index, row) {
        var title = jQuery('td:eq(0)',row).text();
        if (title != "") {
            if (index%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
            jQuery(this).removeClass("odd even");
            jQuery(this).addClass(sclass);
        } else {
            jQuery(this).hide();
        }
    });
};

function ap_sortPriceTable() {
    jQuery("table.list-price-table").tablesorter(
    {
        sortList: [[3,0],[3,0]],
        headers: { 
            0: { sorter: false},
            1: { sorter: false},
            2: { sorter: false},
			3: { sorter: false}
        } 
    });
    ap_colorOddEvenPriceTable();
};

function ap_sortItemNameTable(item_type) {
    if (item_type == 1) {
        jQuery("#product_albums table.list-price-table").tablesorter(
        {
            sortList: [[0,0]],
            headers: { 
                0: { sorter: false},
                1: { sorter: false},
                2: { sorter: false},
				3: { sorter: false}
            }
        });
    } else if (item_type == 2) {
        jQuery("#product_songs table.list-price-table").tablesorter(
        {
            sortList: [[0,0]],
            headers: { 
                0: { sorter: false},
                1: { sorter: false},
                2: { sorter: false},
				3: { sorter: false}
            }
        });
    } else if (item_type == 3) {
        jQuery("#product_merchandise table.list-price-table").tablesorter(
        {
            sortList: [[0,0]],
            headers: { 
                0: { sorter: false},
                1: { sorter: false},
                2: { sorter: false},
				3: { sorter: false}
            }
        });
    } else if (item_type == 4) {
        jQuery("#product_concert table.list-price-table").tablesorter(
        {
            sortList: [[5,0]],
            headers: { 
                0: { sorter: false},
                1: { sorter: false},
                2: { sorter: false},
				3: { sorter: false},
				4: { sorter: false},
				5: { sorter: false}
            }
        });
    }
};

/* Show / hide streaming text if needed.*/
function ap_HideStreamingIconsText(start_element) {
	try {
		var show = true;
		// Artist page - albums
		if (start_element == "#artist_wiki div.albums") {
			for (i=0; i < aStreamingToCompleteAlbums.length;i++) {
				if (aStreamingToCompleteAlbums[i][2] == false) {
					show = false;
				}
			}
			if (show == true) {
				jQuery("#artist_wiki div.albums div.notification div.searching").hide();
				jQuery("#artist_wiki div.albums div.notification div.finished").show();
				setTimeout(function() { ap_HideStreamingAnimation('album') }, 3000);
			}
		// Artist page - songs
		} else if (start_element == "#artist_wiki div.songs") {
			for (i=0; i < aStreamingToCompleteSongs.length;i++) {
				if (aStreamingToCompleteSongs[i][2] == false) {
					show = false;
				}
			}
			if (show == true) {
				jQuery("#artist_wiki div.songs div.notification div.searching").hide();
				jQuery("#artist_wiki div.songs div.notification div.finished").show();
				setTimeout(function() { ap_HideStreamingAnimation('song') }, 3000);
			}
		// Album or song page
		} else if (start_element == "") {
			for (i=0; i < aStreamingToComplete.length;i++) {
				if (aStreamingToComplete[i][2] == false) {
					show = false;
				}
			}
			if (show == true) {
				jQuery("div.streaming_dropdown div.notification div.searching").hide();
				jQuery("div.streaming_dropdown div.notification div.finished").show();
				setTimeout(function() { ap_HideStreamingAnimation('item_page') }, 3000);
			}
		}
	} catch (err) { console.log(err); }
};

/* Rebuild index and change icon if needed.*/
function ap_RebuildStreamingIconsAnimationIndexes(start_element) {
	try {
		var max_streaming_count = ap_GetMaxStreamingCountPrProduct(start_element);
		// Artist page - albums
		if (start_element == "#artist_wiki div.albums") {
			for (i=0; i < aStreamingToCompleteAlbums.length;i++) {
				if (aStreamingToCompleteAlbums[i][2] == true) {
					jQuery.each(jQuery(start_element + ' #streamings div'), function(index, value) {
						if (jQuery(this).attr("class").substring(0,10) == "streaming_") {
							if (aStreamingToCompleteAlbums[i][0] == jQuery(this).attr("class").substring(10)) {
								jQuery(this).children("div.animation").children("div.counter").children("span.count").show();
								var count = parseInt(jQuery(this).children("div.animation").children("div.counter").children("span.count").html(), 10);
								var ap_index = parseInt(((count/max_streaming_count)*10),10);
								if (ap_index == 0 && count != 0) { ap_index = 1; }
								if (ap_index >= 0 && ap_index <= 10) { } else { ap_index = 0; }
								aStreamingToCompleteAlbums[i][1] = ap_index;
								//jQuery(this).children("div.animation").children("div.image").children("img").attr("src", "/sites/all/themes/airplaymusic/assets/images/streaming_index_" + ap_index + ".png");
								//jQuery(this).children("div.animation").children("div.image").children("img").attr("width", "27");
								jQuery(this).children("div.animation").children("div.image").removeClass();
								jQuery(this).children("div.animation").children("div:eq(1)").addClass("image");
								jQuery(this).children("div.animation").children("div.image").addClass("image_" + ap_index);
								
							}
						}
					});
				}
			}
		}
		// Artist page - albums
		if (start_element == "#artist_wiki div.songs") {
			for (i=0; i < aStreamingToCompleteSongs.length;i++) {
				if (aStreamingToCompleteSongs[i][2] == true) {
					jQuery.each(jQuery(start_element + ' #streamings div'), function(index, value) {
						if (jQuery(this).attr("class").substring(0,10) == "streaming_") {
							if (aStreamingToCompleteSongs[i][0] == jQuery(this).attr("class").substring(10)) {
								jQuery(this).children("div.animation").children("div.counter").children("span.count").show();
								var count = parseInt(jQuery(this).children("div.animation").children("div.counter").children("span.count").html(), 10);
								var ap_index = parseInt(((count/max_streaming_count)*10),10);
								if (ap_index == 0 && count != 0) { ap_index = 1; }
								if (ap_index >= 0 && ap_index <= 10) { } else { ap_index = 0; }
								aStreamingToCompleteSongs[i][1] = ap_index;
								//jQuery(this).children("div.animation").children("div.image").children("img").attr("src", "/sites/all/themes/airplaymusic/assets/images/streaming_index_" + ap_index + ".png");
								//jQuery(this).children("div.animation").children("div.image").children("img").attr("width", "27");
								jQuery(this).children("div.animation").children("div.image").removeClass();
								jQuery(this).children("div.animation").children("div:eq(1)").addClass("image");
								jQuery(this).children("div.animation").children("div.image").addClass("image_" + ap_index);
							}
						}
					});
				}
			}
		}
		// Album or song page
		if (start_element == "") {
			for (i=0; i < aStreamingToComplete.length;i++) {
				if (aStreamingToComplete[i][2] == true) {
					jQuery.each(jQuery(start_element + ' #streamings div'), function(index, value) {
						if (jQuery(this).attr("class").substring(0,10) == "streaming_") {
							if (aStreamingToComplete[i][0] == jQuery(this).attr("class").substring(10)) {
								jQuery(this).children("div.animation").children("div.counter").children("span.count").show();
								var count = parseInt(jQuery(this).children("div.animation").children("div.counter").children("span.count").html(), 10);
								var ap_index = parseInt(((count/max_streaming_count)*10),10);
								if (ap_index == 0 && count != 0) { ap_index = 1; }
								if (ap_index >= 0 && ap_index <= 10) { } else { ap_index = 0; }
								aStreamingToComplete[i][1] = ap_index;
								//jQuery(this).children("div.animation").children("div.image").children("img").attr("src", "/sites/all/themes/airplaymusic/assets/images/streaming_index_" + ap_index + ".png");
								//jQuery(this).children("div.animation").children("div.image").children("img").attr("width", "27");
								jQuery(this).children("div.animation").children("div.image").removeClass();
								jQuery(this).children("div.animation").children("div:eq(1)").addClass("image");
								jQuery(this).children("div.animation").children("div.image").addClass("image_" + ap_index);
							}
						}
					});
				}
			}
		}
	} catch (err) { console.log(err); }
};

/*
    Function used to change animation to bars
*/
function ap_BuildStreamingIconsAnimationIndexes(start_element, record_store_name) {
    var max_streaming_count = ap_GetMaxStreamingCountPrProduct(start_element);
    
    jQuery.each(jQuery(start_element + ' #streamings div'), function(index, value) {
        if (jQuery(this).attr("class").substring(0,10) == "streaming_") {
            if (start_element == "#artist_wiki div.albums") {
                if (record_store_name == jQuery(this).attr("class").substring(10)) {
                    jQuery(this).children("div.animation").children("div.counter").children("span.count").show();
                    var count = parseInt(jQuery(this).children("div.animation").children("div.counter").children("span.count").html(), 10);
                    var ap_index = parseInt(((count/max_streaming_count)*10),10);
                    if (ap_index == 0 && count != 0) { ap_index = 1; }
                    if (ap_index >= 0 && ap_index <= 10) { } else { ap_index = 0; }
                    for (i=0; i < aStreamingToCompleteAlbums.length;i++) {
                        if (aStreamingToCompleteAlbums[i][0] == record_store_name) {
                            aStreamingToCompleteAlbums[i][1] = ap_index;
                            aStreamingToCompleteAlbums[i][2] = true;
                        }
                    }
                }
            } else if (start_element == "#artist_wiki div.songs") {
                if (record_store_name == jQuery(this).attr("class").substring(10)) {
                    jQuery(this).children("div.animation").children("div.counter").children("span.count").show();
                    var count = parseInt(jQuery(this).children("div.animation").children("div.counter").children("span.count").html(), 10);
                    var ap_index = parseInt(((count/max_streaming_count)*10),10);
                    if (ap_index == 0 && count != 0) { ap_index = 1; }
                    if (ap_index >= 0 && ap_index <= 10) { } else { ap_index = 0; }
                    for (i=0; i < aStreamingToCompleteSongs.length;i++) {
                        if (aStreamingToCompleteSongs[i][0] == record_store_name) {
                            aStreamingToCompleteSongs[i][1] = ap_index;
                            aStreamingToCompleteSongs[i][2] = true;
                        }
                    }
                }
            } else if (start_element == "") {
                if (record_store_name == jQuery(this).attr("class").substring(10)) {
                    jQuery(this).children("div.animation").children("div.counter").children("span.count").show();
                    var count = parseInt(jQuery(this).children("div.animation").children("div.counter").children("span.count").html(), 10);
                    var ap_index = parseInt(((count/max_streaming_count)*10),10);
                    if (ap_index == 0 && count != 0) { ap_index = 1; }
                    if (ap_index >= 0 && ap_index <= 10) { } else { ap_index = 0; }
                    for (i=0; i < aStreamingToComplete.length;i++) {
                        if (aStreamingToComplete[i][0] == record_store_name) {
                            aStreamingToComplete[i][1] = ap_index;
                            aStreamingToComplete[i][2] = true;
                        }
                    }
                }
            }
        }
    });
    ap_RebuildStreamingIconsAnimationIndexes(start_element);
    ap_HideStreamingIconsText(start_element);
};

function ap_select_artist_wiki_tab(selected_element) {
    jQuery("#artist_wiki div.albums").hide();
    jQuery("#artist_wiki div.albums #streamings").hide();
    jQuery("#artist_wiki div.songs").hide();
    jQuery("#artist_wiki div.songs #streamings").hide();
    jQuery("#artist_wiki div.wiki").hide();
    
    if (selected_element == 'albums') {
		jQuery("#artist_wiki div.wiki").show();
    } else if (selected_element == 'songs') {
        jQuery("#artist_wiki div.songs").show();
        jQuery("#artist_wiki div.songs #streamings").show();
    } else if (selected_element == 'merchandise' || selected_element == 'wiki' || selected_element == 'concert') {
        jQuery("#artist_wiki div.wiki").show();
    }
};

function ap_filter_titles_delayed(value, item_type) {
    if (item_type == 1) {
        ArtistAlbumPage = 0;
    } else if (item_type == 2) {
        ArtistSongPage = 0;
    } else if (item_type == 3) {
        ArtistMerchandisePage = 0;
    }
    
    if (value.length >= 3) {
        if (item_type == 1) {
            jQuery("#product_albums div.page-navigator").hide();
            ap_filter_albums(value);
            parseJSONItemsForIconsArtistPageAlbums(oAlbumJSONspotify, value);
            parseJSONItemsForIconsArtistPageAlbums(oAlbumJSONrdio, value);
            parseJSONItemsForIconsArtistPageAlbums(oAlbumJSONdeezer, value);
            parseJSONItemsForIconsArtistPageAlbums(oAlbumJSONwimp, value);
            parseJSONItemsForIconsArtistPageAlbums(oAlbumJSONnapster, value);
			parseJSONItemsForIconsArtistPageAlbums(oAlbumJSONitunes, value);
			parseJSONItemsForIconsArtistPageAlbums(oAlbumJSON7digital, value);
        } else if (item_type == 2) {
            jQuery("#product_songs div.page-navigator").hide();
            ap_filter_songs(value);
            parseJSONItemsForIconsArtistPageSongs(oSongJSONspotify, value);
            parseJSONItemsForIconsArtistPageSongs(oSongJSONrdio, value);
            parseJSONItemsForIconsArtistPageSongs(oSongJSONdeezer, value);
            parseJSONItemsForIconsArtistPageSongs(oSongJSONwimp, value);
            parseJSONItemsForIconsArtistPageSongs(oSongJSONnapster, value);
			parseJSONItemsForIconsArtistPageSongs(oSongJSONitunes, value);
			parseJSONItemsForIconsArtistPageSongs(oSongJSON7digital, value);
        } else if (item_type == 3) {
            jQuery("#product_merchandise div.page-navigator").hide();
            ap_filter_merchandise(value);
        }
    } else {
        if (item_type == 1) {
            jQuery("#product_albums div.page-navigator").show();
            PageArtistAlbumsFrontpage();
        } else if (item_type == 2) {
            jQuery("#product_songs div.page-navigator").show();
            PageArtistSongsFrontpage(0);
        } else if (item_type == 3) {
            jQuery("#product_merchandise div.page-navigator").show();
            PageArtistMerchandise(0);
        }
    }
    
    ap_sortItemNameTable(item_type);
    
    if (item_type == 1) {
        ap_colorOddEvenPriceTableAlbums();
    } else if (item_type == 2) {
        ap_colorOddEvenPriceTableSongs();
    } else if (item_type == 3) {
        ap_colorOddEvenPriceTableMerchandise();
    }
};

function ap_filter_titles(value, item_type) {
    try { window.clearTimeout(FilterTitles); }
    catch (err) { }
    FilterTitles = setTimeout(function() { ap_filter_titles_delayed(value, item_type) }, 400);
};

/********************************************************************************************************************
********************************************************************************************************************/
//																			 Artist page 
/********************************************************************************************************************
********************************************************************************************************************/
/********************************************************************************************************************
// Artist page - albums (functions)
********************************************************************************************************************/
function ap_ShowAlbumCoverArtistPage(image_id, image, width, height) {
	jQuery("#album_cover_" + image_id).css("width", width + "px");
	jQuery("#album_cover_" + image_id).css("height", height + "px");
	jQuery("#album_cover_" + image_id).css("background-image", "url("+ image + ")"); 
    jQuery("#album_cover_" + image_id).show();
};

function ap_HideAlbumCoverArtistPage(image_id) {
        jQuery("#album_cover_" + image_id).hide();

};

function ap_select_album_tab() {
    jQuery("#product_albums").show();
    jQuery("#product_merchandise").hide();
    jQuery("#product_songs").hide();
    jQuery("#product_concert").hide();
    jQuery("#product_tabs_album").addClass("selected");
	jQuery("#product_tabs_album").removeClass("deselected");
    jQuery("#product_tabs_song").removeClass("selected");
    jQuery("#product_tabs_song").addClass("deselected");
	jQuery("#product_tabs_merchandise").removeClass("selected");
    jQuery("#product_tabs_merchandise").addClass("deselected");
	jQuery("#product_tabs_concert").removeClass("selected");
    jQuery("#product_tabs_concert").addClass("deselected");
    jQuery("#search_title_album_container").show();
    jQuery("#search_title_song_container").hide();
    jQuery("#search_title_merchandise_container").hide();
    jQuery("#search_title_concert_container").hide();
    ap_select_artist_wiki_tab('albums');
};

function ap_filter_albums(value) {
    var table = jQuery('#product_albums table.list-price-table');
    var rows = jQuery('tbody > tr',table);

    jQuery.each(rows, function(index, row) {
        var title = jQuery('td:eq(0)',row).text();
        if (title != "") {
            if (title.toLowerCase().indexOf(value.toLowerCase()) >= 0) {
                if (index%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
                jQuery(this).removeClass("odd");
                jQuery(this).removeClass("even");
                jQuery(this).addClass(sclass);
                jQuery(this).show();
            } else {
                jQuery(this).hide();
            }
        } else {
            jQuery(this).hide();
        }
    });
};

function ap_colorOddEvenPriceTableAlbums() {
    var table = jQuery('#product_albums table.list-price-table');
    var rows = jQuery('tbody > tr',table);
    var count = 0;    
    jQuery.each(rows, function(index, row) {
        var title = jQuery('td:eq(0)',row).text();
        if (title != "") {
            if (jQuery(this).is(":visible") === true) {
                if (count%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
                jQuery(this).removeClass("odd");
                jQuery(this).removeClass("even");
                jQuery(this).addClass(sclass);
                count++;
            }
        } else {
            jQuery(this).hide();
        }
    });
};

function PageArtistAlbumsFrontpage() {
	ArtistPageAlbumItemsOnPage = 0;
	ap_ArtistPageAlbumShowMasters();
};

function PageArtistAlbumsAll() {
    jQuery("#product_albums table.list-price-table tbody > tr").show();
    
    parseJSONItemsForIconsArtistPageAlbumsAll(oAlbumJSONspotify);
    parseJSONItemsForIconsArtistPageAlbumsAll(oAlbumJSONrdio);
    parseJSONItemsForIconsArtistPageAlbumsAll(oAlbumJSONdeezer);
    parseJSONItemsForIconsArtistPageAlbumsAll(oAlbumJSONwimp);
	parseJSONItemsForIconsArtistPageAlbumsAll(oAlbumJSONnapster);
	parseJSONItemsForIconsArtistPageAlbumsAll(oAlbumJSON7digital);
	parseJSONItemsForIconsArtistPageAlbumsAll(oAlbumJSONitunes);
	ap_colorOddEvenPriceTableAlbums();
};

function PageArtistAlbumsByChars(char) {
	ArtistAlbumPage = char;
    var page_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    for (var i = 0; i < page_chars.length; i++) {
        jQuery("#product_albums tr.album-page-" + page_chars.charAt(i)).hide();
    }
    jQuery("#product_albums tr.album-page-other").hide();
    
    jQuery("#product_albums tr.album-page-" + char).show();
    
    parseJSONItemsForIconsArtistPageAlbums(oAlbumJSONspotify, char);
    parseJSONItemsForIconsArtistPageAlbums(oAlbumJSONrdio, char);
    parseJSONItemsForIconsArtistPageAlbums(oAlbumJSONdeezer, char);
    parseJSONItemsForIconsArtistPageAlbums(oAlbumJSONwimp, char);
	parseJSONItemsForIconsArtistPageAlbums(oAlbumJSONnapster, char);
    //parseJSONItemsForIconsArtistPageAlbums(oAlbumJSONitunes, char);
	//parseJSONItemsForIconsArtistPageAlbums(oAlbumJSON7digital, char);
	ap_colorOddEvenPriceTableAlbums();
};


function parseJSONItemsForIconsArtistPageAlbums(json, value) {
    try {
    jQuery.each(json, function(i, jsons) {
        if (i == "items") {
            jQuery.each(jsons, function(j, items) {
                jQuery.each(items, function(k, item) {
                    if (item.record_store_class_name == "spotify" && oAlbumJSONspotify == "") {
                       oAlbumJSONspotify = json;
                    }
                    if (item.record_store_class_name == "rdio" && oAlbumJSONrdio == "") {
                       oAlbumJSONrdio = json;
                    }
                    if (item.record_store_class_name == "deezer" && oAlbumJSONdeezer == "") {
                       oAlbumJSONdeezer = json;
                    }
                    if (item.record_store_class_name == "wimp" && oAlbumJSONwimp == "") {
                       oAlbumJSONwimp = json;
                    }
                    if (item.record_store_class_name == "napster" && oAlbumJSONnapster == "") {
                       oAlbumJSONnapster = json;
                    }
					if (item.record_store_class_name == "7digital" && oAlbumJSON7digital == "") {
                       oAlbumJSON7digital = json;
                    }
                    jQuery("#product_albums table.list-price-table tr.album-page-" + value).each(function() {
                        var ItemNameLower = "";
                        try {ItemNameLower = jQuery("td.list-artist-album div.title", this).text();} catch(err) {}
                        if (ItemNameLower.toLowerCase() == item.item_name.toLowerCase()) {
                            jQuery("td.list-artist-streaming span." + item.record_store_class_name,this).show();
                            var buy_at_url = item.buy_at_url;
                            if (item.affiliate_url && item.affiliate_encode_times) {
                                buy_at_url = ap_EncodeAffiliateLink(item.buy_at_url, item.affiliate_url, item.affiliate_encode_times);
                            }
                            jQuery("td.list-artist-streaming span." + item.record_store_class_name + " a", this).attr("href", buy_at_url);
                        }
                    });
                });
            });
        }
    });
    } catch (err) { }
};

function parseJSONItemsForIconsArtistPageAlbumsAll(json) {
    try {
		jQuery.each(json, function(i, jsons) {
			if (i == "items") {
				jQuery.each(jsons, function(j, items) {
					jQuery.each(items, function(k, item) {
						if (item.record_store_class_name == "spotify" && oAlbumJSONspotify == "") {
						   oAlbumJSONspotify = json;
						}
						if (item.record_store_class_name == "rdio" && oAlbumJSONrdio == "") {
						   oAlbumJSONrdio = json;
						}
						if (item.record_store_class_name == "deezer" && oAlbumJSONdeezer == "") {
						   oAlbumJSONdeezer = json;
						}
						if (item.record_store_class_name == "wimp" && oAlbumJSONwimp == "") {
						   oAlbumJSONwimp = json;
						}
						if (item.record_store_class_name == "napster" && oAlbumJSONnapster == "") {
						   oAlbumJSONnapster = json;
						}
						if (item.record_store_class_name == "7digital" && oAlbumJSON7digital == "") {
						   oAlbumJSON7digital = json;
						}
						//jQuery("#product_albums table.list-price-table tbody tr").each(function() {
						jQuery("#product_albums table.list-price-table tbody tr:visible").each(function() {
							var ItemNameLower = "";
							try {ItemNameLower = jQuery("td.list-artist-album div.title", this).text();} catch(err) {}
							if (ItemNameLower.toLowerCase() == item.item_name.toLowerCase()) {
								jQuery("td.list-artist-streaming span." + item.record_store_class_name,this).show();
								var buy_at_url = item.buy_at_url;
								if (item.affiliate_url && item.affiliate_encode_times) {
									buy_at_url = ap_EncodeAffiliateLink(item.buy_at_url, item.affiliate_url, item.affiliate_encode_times);
								}
								jQuery("td.list-artist-streaming span." + item.record_store_class_name + " a", this).attr("href", buy_at_url);
							}
						});
					});
				});
			}
		});
    } catch (err) { }
};

function parseJSONItemsForArtistPageAlbums(json) {
    try {
    var record_store_name = "";
    jQuery.each(json, function(i, jsons) {
        if (i == "items") {
            jQuery.each(jsons, function(j, items) {
                jQuery.each(items, function(k, item) {
					var record_store_class_name = item.record_store_class_name;
					var media_format_name = item.media_format_name;
                    if (record_store_class_name == "spotify" && oAlbumJSONspotify == "") {
                       oAlbumJSONspotify = json;
                    }
                    if (record_store_class_name == "rdio" && oAlbumJSONrdio == "") {
                       oAlbumJSONrdio = json;
                    }
                    if (record_store_class_name == "deezer" && oAlbumJSONdeezer == "") {
                       oAlbumJSONdeezer = json;
                    }
                    if (record_store_class_name == "wimp" && oAlbumJSONwimp == "") {
                       oAlbumJSONwimp = json;
                    }
                    if (record_store_class_name == "napster" && oAlbumJSONnapster == "") {
                       oAlbumJSONnapster = json;
                    }
                    if (record_store_class_name == "itunes" && oAlbumJSONitunes == "") {
                       oAlbumJSONitunes = json;
                    }
					if (record_store_class_name == "7digital" && oAlbumJSON7digital == "") {
                       oAlbumJSON7digital = json;
                    }
                    //jQuery("#product_albums tbody tr.album-page-" + ArtistAlbumPage).each(function() {
					jQuery("#product_albums tbody tr:visible").each(function() {
                        var ItemNameLower = "";
                        try {ItemNameLower = jQuery("td.list-artist-album div.title", this).text();} catch(err) {}
                        if (ItemNameLower.toLowerCase() == item.item_name.toLowerCase()) {
                            if (record_store_class_name == "itunes" || record_store_class_name == "7digital") {
                                var item_min_price = parseInt(jQuery("td.list-artist-price-format span.min-price",this).text().replace(",", "").replace(".", ""), 10);
                                var item_max_price = parseInt(jQuery("td.list-artist-price-format span.max-price",this).text().replace(",", "").replace(".", ""), 10);
                                var item_num_price = parseInt(jQuery("td.list-artist-price-format span.price_count",this).text(), 10);
                                item_num_price++;
                                var this_item_price = item.price_local;
                                this_item_price = (this_item_price * oCurrencyToEuro[item.currency_code] * oCurrencyFromEuro[jQuery("select.tableSelect option:selected").text()]);
                                jQuery("td.list-artist-price-format span.price_count",this).text(item_num_price);
                                if (parseInt(this_item_price,10) > item_max_price) {
                                    jQuery("td.list-artist-price-format span.max-price",this).text(ap_PriceFormat(this_item_price));
                                }
                                if (parseInt(this_item_price,10) < item_min_price || item_min_price == 0) {
                                    jQuery("td.list-artist-price-format span.min-price",this).text(ap_PriceFormat(this_item_price));
                                }
								if (parseInt(item_num_price,10) > 1) {
									jQuery("td.list-artist-price-format span.price_text",this).text(token_prices);
								}
                                var media_count = parseInt(jQuery("#product_albums #media_format_" + media_format_name.toLowerCase() + " span.count").text().replace("(", "").replace(")", ""),10);
                                var media_count_all = parseInt(jQuery("#product_albums .media_format_all span.count").text().replace("(", "").replace(")", ""),10);
                                media_count++;
                                media_count_all++;
                                jQuery("#product_albums #media_format_" + media_format_name.toLowerCase()).show();
                                jQuery("#product_albums #media_format_" + media_format_name.toLowerCase() + " span.count").text("(" + media_count + ")");
                                jQuery("#product_albums .media_format_all span.count").text("(" + media_count_all + ")");
                            } else {
                                jQuery("td.list-artist-streaming span." + record_store_class_name,this).show();
                                var buy_at_url = item.buy_at_url;
                                if (item.affiliate_url && item.affiliate_encode_times) {
                                    buy_at_url = ap_EncodeAffiliateLink(item.buy_at_url, item.affiliate_url, item.affiliate_encode_times);
                                }
                                jQuery("td.list-artist-streaming span." + record_store_class_name + " a", this).attr("href", buy_at_url);
                                record_store_name = record_store_class_name;
                            }
                        }
                    });
                });
            });
        } else if (i == "item_count") {
            jQuery.each(jsons, function(j, items) {
                if (parseInt(items.item_count,10) >= 0) {
                    jQuery('#artist_wiki div.albums .streaming_' + items.name + ' span.count').text(items.item_count);
                    record_store_name = items.name;
                }
            });
        }
    });
    if (record_store_name == "itunes" || record_store_name == "7digital") {
	} else {
        ap_BuildStreamingIconsAnimationIndexes('#artist_wiki div.albums', record_store_name);
    }
    } catch (err) { }
};

/* Function used to show/hide albums on Artist page - for showing masters */
function ap_ArtistPageAlbumShowMasters() {
	/* We have more then one page total - new layout */
	if (ArtistPageAlbumCount > ArtistPageAlbumItemPrPage) {
		jQuery('#product_albums table.list-price-table > tbody > tr').each(function(index, value) {
			if ((parseInt(jQuery(this).attr("item_count"), 10) > 9 || jQuery(this).attr("class").toLowerCase().indexOf("album-master-1") >= 0 )) {
				jQuery(this).show();
				ArtistPageAlbumItemsOnPage++;
			} else {
				jQuery(this).hide();
			}
		});
		/* Make sure table is correct odd even */
		if (ArtistPageAlbumItemsOnPage != 0) {
			ap_sortItemNameTable(1);
			ap_colorOddEvenPriceTable();
		}
	}
	if (ArtistPageAlbumItemsOnPage <= ArtistPageAlbumItemPrPage) {
		for (var i = 9; i >= 0; i--) {
			jQuery('#product_albums table.list-price-table > tbody > tr').each(function(index, value) {
				if ((parseInt(jQuery(this).attr("item_count"), 10) == i && jQuery(this).attr("class").toLowerCase().indexOf("album-master-0") >= 0 && ArtistPageAlbumItemsOnPage <= ArtistPageAlbumItemPrPage)) {
					jQuery(this).show();
					ArtistPageAlbumItemsOnPage++;
				}
			});
			if (ArtistPageAlbumItemsOnPage > ArtistPageAlbumItemPrPage) {
				break;
			}
		}
		/* Make sure table is correct odd even */
		if (ArtistPageAlbumItemsOnPage != 0) {
			ap_sortItemNameTable(1);
			ap_colorOddEvenPriceTable();
		}
	}
	/* Fall back for old layout */
	/*if (ArtistPageAlbumItemsOnPage == 0) {
		// hide all other then page 0 
		for (var i = 1; i <= ArtistAlbumPageCount; i++ ) {
			jQuery("td.album-page-" + i).hide();
		}
	}*/
};

/********************************************************************************************************************
// Artist page - songs (functions)
********************************************************************************************************************/
function ap_select_song_tab() {
    jQuery("#product_albums").hide();
    jQuery("#product_merchandise").hide();
    jQuery("#product_songs").show();
    jQuery("#product_concert").hide();
    jQuery("#product_songs #streamings").show();
    jQuery("#product_tabs_song").addClass("selected");
	jQuery("#product_tabs_song").removeClass("deselected");
    jQuery("#product_tabs_album").removeClass("selected");
    jQuery("#product_tabs_album").addClass("deselected");
	jQuery("#product_tabs_merchandise").removeClass("selected");
	jQuery("#product_tabs_merchandise").addClass("deselected");
    jQuery("#product_tabs_concert").removeClass("selected");
	jQuery("#product_tabs_concert").addClass("deselected");
    jQuery('#product_songs #streamings').show();
    jQuery("#search_title_album_container").hide();
    jQuery("#search_title_merchandise_container").hide();
    jQuery("#search_title_song_container").show();
    jQuery("#search_title_concert_container").hide();
	if (jQuery("#artist_wiki #tab_video")) {
		jQuery("#artist_wiki div.video").hide();
		ap_ShowWikiFromTab("album");
	}
    ap_select_artist_wiki_tab('songs');
};

function ap_filter_songs(value) {
    var table = jQuery('#product_songs table.list-price-table');
    var rows = jQuery('tbody > tr',table);

    jQuery.each(rows, function(index, row) {
        var title = jQuery('td:eq(0)',row).text();
        if (title != "") {
            if (title.toLowerCase().indexOf(value.toLowerCase()) >= 0) {
                if (index%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
                jQuery(this).removeClass("odd");
                jQuery(this).removeClass("even");
                jQuery(this).addClass(sclass);
                jQuery(this).show();
            } else {
                jQuery(this).hide();
            }
        } else {
            jQuery(this).hide();
        }
    });
};

function ap_colorOddEvenPriceTableSongs() {
    var table = jQuery('#product_songs table.list-price-table');
    var rows = jQuery('tbody > tr',table);
    var count = 0;
    jQuery.each(rows, function(index, row) {
        var title = jQuery('td:eq(0)',row).text();
        if (title != "") {
            if (jQuery(this).is(":visible") === true) {
                if (count%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
                jQuery(this).removeClass("odd");
                jQuery(this).removeClass("even");
                jQuery(this).addClass(sclass);
                count++;
            }
        } else {
            jQuery(this).hide();
        }
    });
};

function PageArtistSongsByChars(char) {
	ArtistSongPage = char;
    var page_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    for (var i = 0; i < page_chars.length; i++) {
        jQuery("#product_songs tr.song-page-" + page_chars.charAt(i)).hide();
    }
    jQuery("#product_songs tr.song-page-other").hide();
    
    jQuery("#product_songs tr.song-page-" + char).show();
    
    parseJSONItemsForIconsArtistPageSongs(oSongJSONspotify, char);
    parseJSONItemsForIconsArtistPageSongs(oSongJSONrdio, char);
    parseJSONItemsForIconsArtistPageSongs(oSongJSONdeezer, char);
    parseJSONItemsForIconsArtistPageSongs(oSongJSONwimp, char);
    parseJSONItemsForIconsArtistPageSongs(oSongJSONnapster, char);
	parseJSONItemsForArtistPageSongs(oSongJSONitunes);
	ap_colorOddEvenPriceTableSongs();
	//parseJSONItemsForIconsArtistPageSongs(oSongJSON7digital, char);
	//parseJSONItemsForIconsArtistPageSongs(oSongJSONitunes, char);

};
function PageArtistSongsFrontpage(page) {
	ArtistSongPage = page;
    for (var i = 0; i <= ArtistSongPageCount; i++ ) {
        jQuery("#product_songs tr.song-page-" + i).hide();
    }
    jQuery("#product_songs tr.song-page-" + page).show();
	ap_colorOddEvenPriceTableSongs();
};
/*
function PageArtistSongs(page) {
    ArtistSongPage = page;
    for (var i = 0; i <= ArtistSongPageCount; i++ ) {
        jQuery("#product_songs tr.song-page-" + i).hide();
    }
    jQuery("#product_songs tr.song-page-" + page).show();
    
    jQuery('#product_songs div.page-navigator div.page-item').each(function(i, obj) {
        if (i == ArtistSongPage) {
            jQuery(this).addClass("selected");
        } else {
            jQuery(this).removeClass("selected");
        }
    });
	parseJSONItemsForArtistPageSongs(oSongJSONspotify);
    parseJSONItemsForArtistPageSongs(oSongJSONrdio);
    parseJSONItemsForArtistPageSongs(oSongJSONdeezer);
    parseJSONItemsForArtistPageSongs(oSongJSONwimp);
    parseJSONItemsForArtistPageSongs(oSongJSONitunes);
    parseJSONItemsForArtistPageSongs(oSongJSONnapster);
	parseJSONItemsForArtistPageSongs(oSongJSON7digital);
};*/

function parseJSONItemsForIconsArtistPageSongs(json, value) {
    try {
    jQuery.each(json, function(i, jsons) {
        if (i == "items") {
            jQuery.each(jsons, function(j, items) {
                jQuery.each(items, function(k, item) {
                    if (item.record_store_class_name == "spotify" && oSongJSONspotify == "") {
                       oSongJSONspotify = json;
                    }
                    if (item.record_store_class_name == "rdio" && oSongJSONrdio == "") {
                       oSongJSONrdio = json;
                    }
                    if (item.record_store_class_name == "deezer" && oSongJSONdeezer == "") {
                       oSongJSONdeezer = json;
                    }
                    if (item.record_store_class_name == "wimp" && oSongJSONwimp == "") {
                       oSongJSONwimp = json;
                    }
                    if (item.record_store_class_name == "napster" && oSongJSONnapster == "") {
                       oSongJSONnapster = json;
                    }
					if (item.record_store_class_name == "7digital" && oSongJSON7digital == "") {
                       oSongJSON7digital = json;
                    }
                    jQuery("#product_songs table.list-price-table tr.song-page-" + value).each(function() {
                        var ItemNameLower = "";
                        try {ItemNameLower = jQuery("td.list-artist-song",this).text().toLowerCase();} catch(err) {}
                        if (ItemNameLower == item.item_name.toLowerCase()) {
                            jQuery("td.list-artist-streaming span." + item.record_store_class_name,this).show();
                            var buy_at_url = item.buy_at_url;
                            if (item.affiliate_url && item.affiliate_encode_times) {
                                buy_at_url = ap_EncodeAffiliateLink(buy_at_url, item.affiliate_url, item.affiliate_encode_times);
                            }
                            jQuery("td.list-artist-streaming span." + item.record_store_class_name + " a", this).attr("href", buy_at_url);
                        }
                    });
                });
            });
        }
    });
    } catch (err) { }
};


function parseJSONItemsForArtistPageSongs(json) {
    try {
    var record_store_name = "";
    jQuery.each(json, function(i, jsons) {
        if (i == "items") {
            jQuery.each(jsons, function(j, items) {
                jQuery.each(items, function(k, item) {
					var record_store_class_name = item.record_store_class_name;
					var media_format_name = item.media_format_name;
                    if (record_store_class_name == "spotify" && oSongJSONspotify == "") {
                       oSongJSONspotify = json;
                    }
                    if (record_store_class_name == "rdio" && oSongJSONrdio == "") {
                       oSongJSONrdio = json;
                    }
                    if (record_store_class_name == "deezer" && oSongJSONdeezer == "") {
                       oSongJSONdeezer = json;
                    }
                    if (record_store_class_name == "wimp" && oSongJSONwimp == "") {
                       oSongJSONwimp = json;
                    }
                    if (record_store_class_name == "napster" && oSongJSONnapster == "") {
                       oSongJSONnapster = json;
                    }
                    if (record_store_class_name == "itunes" && oSongJSONitunes == "") {
                       oSongJSONitunes = json;
                    }
					if (record_store_class_name == "7digital" && oSongJSON7digital == "") {
                       oSongJSON7digital = json;
                    }
                    //jQuery("#product_songs table.list-price-table tr.song-page-" + ArtistSongPage).each(function() {
					jQuery("#product_songs tbody tr:visible").each(function() {
                        var ItemNameLower = "";
                        try {ItemNameLower = jQuery("td.list-artist-song",this).text().toLowerCase();} catch(err) {}
                        if (ItemNameLower == item.item_name.toLowerCase()) {
                            if (record_store_class_name == "itunes" || record_store_class_name == "7digital") {
                                var item_min_price = parseInt(jQuery("td.list-artist-price-format span.min-price",this).text().replace(",", "").replace(".", ""), 10);
                                var item_max_price = parseInt(jQuery("td.list-artist-price-format span.max-price",this).text().replace(",", "").replace(".", ""), 10);
                                var item_num_price = parseInt(jQuery("td.list-artist-price-format span.price_count",this).text(), 10);
                                item_num_price++;
                                jQuery("td.list-artist-price-format span.price_count",this).text(item_num_price);
                                var this_item_price = item.price_local;
                                this_item_price = (this_item_price * oCurrencyToEuro[item.currency_code] * oCurrencyFromEuro[jQuery("select.tableSelect option:selected").text()]);
                                if (parseInt(this_item_price,10) > item_max_price) {
                                    jQuery("td.list-artist-price-format span.max-price",this).text(ap_PriceFormat(this_item_price));
                                }
                                if (parseInt(this_item_price,10) < item_min_price || item_min_price == 0) {
                                    jQuery("td.list-artist-price-format span.min-price",this).text(ap_PriceFormat(this_item_price));
                                }
								if (parseInt(item_num_price,10) > 1) {
									jQuery("td.list-artist-price-format span.price_text",this).text(token_prices);
								}
                                var media_count = parseInt(jQuery("#product_songs #media_format_" + media_format_name.toLowerCase() + " span.count").text().replace("(", "").replace(")", ""),10);
                                var media_count_all = parseInt(jQuery("#product_songs .media_format_all span.count").text().replace("(", "").replace(")", ""),10);
                                media_count++;
                                media_count_all++;
                                jQuery("#product_songs #media_format_" + media_format_name.toLowerCase()).show();
                                jQuery("#product_songs #media_format_" + media_format_name.toLowerCase() + " span.count").text("(" + media_count + ")");
                                jQuery("#product_songs .media_format_all span.count").text("(" + media_count_all + ")");
                            } else {
                                jQuery("td.list-artist-streaming span." + record_store_class_name,this).show();
                                var buy_at_url = item.buy_at_url;
                                if (item.affiliate_url && item.affiliate_encode_times) {
                                    buy_at_url = ap_EncodeAffiliateLink(buy_at_url, item.affiliate_url, item.affiliate_encode_times);
                                }
                                jQuery("td.list-artist-streaming span." + record_store_class_name + " a", this).attr("href", buy_at_url);
                                record_store_name = record_store_class_name;
                            }
                        }
                    });
                });
            });
        } else if (i == "item_count") {
            jQuery.each(jsons, function(j, items) {
                if (parseInt(items.item_count,10) >= 0) {
                    jQuery('#artist_wiki div.songs div.streaming_' + items.name + ' span.count').text(items.item_count);
                    record_store_name = items.name;
                }
            });
        }
    });
    if (record_store_name == "itunes" || record_store_name == "7digital") {
	} else {
        ap_BuildStreamingIconsAnimationIndexes('#artist_wiki div.songs', record_store_name);
    }
    } catch (err) { }
};

/********************************************************************************************************************
// Artist page - merchandise (functions)
********************************************************************************************************************/
function ap_ShowMerchandiseArtistPage(image_id, image, width, height) {
    jQuery("#merchandise_image_" + image_id).show();
	jQuery("#merchandise_image_" + image_id).css("width", width + "px");
	jQuery("#merchandise_image_" + image_id).css("height", height + "px");
	jQuery("#merchandise_image_" + image_id).css("background-image", "url("+ image + ")"); 
};

function ap_HideMerchandiseArtistPage(image_id) {
        jQuery("#merchandise_image_" + image_id).hide();
};

function PageArtistMerchandise(page) {
    ArtistMerchandisePage = page;
    for (var i = 0; i <= ArtistMerchandisePageCount; i++ ) {
        jQuery("#product_merchandise tr.merchandise-page-" + i).hide();
    }
    jQuery("#product_merchandise tr.merchandise-page-" + page).show();
    
    jQuery('#product_merchandise div.page-navigator div.page-item').each(function(i, obj) {
        if (i == ArtistMerchandisePage) {
            jQuery(this).addClass("selected");
        } else {
            jQuery(this).removeClass("selected");
        }
    });
};

function ap_select_merchandise_tab() {
    jQuery("#product_albums").hide();
    jQuery("#product_songs").hide();
    jQuery("#product_merchandise").show();
    jQuery("#product_concert").hide();
    jQuery("#product_tabs_merchandise").addClass("selected");
	jQuery("#product_tabs_merchandise").removeClass("deselected");
    jQuery("#product_tabs_album").removeClass("selected");
    jQuery("#product_tabs_album").addClass("deselected");
	jQuery("#product_tabs_song").removeClass("selected");
    jQuery("#product_tabs_song").addClass("deselected");
	jQuery("#product_tabs_concert").removeClass("selected");
    jQuery("#product_tabs_concert").addClass("deselected");
    jQuery("#search_title_album_container").hide();
    jQuery("#search_title_song_container").hide();
    jQuery("#search_title_merchandise_container").show();
    jQuery("#search_title_concert_container").hide();
    ap_select_artist_wiki_tab('merchandise');
};

function ap_filter_merchandise(value) {
    var table = jQuery('#product_merchandise table.list-price-table');
    var rows = jQuery('tbody > tr',table);

    jQuery.each(rows, function(index, row) {
        var title = jQuery('td:eq(0)',row).text();
        if (title != "") {
            if (title.toLowerCase().indexOf(value.toLowerCase()) >= 0) {
                if (index%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
                jQuery(this).removeClass("odd");
                jQuery(this).removeClass("even");
                jQuery(this).addClass(sclass);
                jQuery(this).show();
            } else {
                jQuery(this).hide();
            }
        } else {
            jQuery(this).hide();
        }
    });
};

function ap_colorOddEvenPriceTableMerchandise() {
    var table = jQuery('#product_merchandise table.list-price-table');
    var rows = jQuery('tbody > tr',table);
    var count = 0;
    jQuery.each(rows, function(index, row) {
        var title = jQuery('td:eq(0)',row).text();
        if (title != "") {
            if (jQuery(this).is(":visible") === true) {
                if (count%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
                jQuery(this).removeClass("odd");
                jQuery(this).removeClass("even");
                jQuery(this).addClass(sclass);
                count++;
            }
        } else {
            jQuery(this).hide();
        }
    });
};

/********************************************************************************************************************
// Artist page - concerts (functions)
********************************************************************************************************************/
function PageArtistConcert(page) {
    ArtistConcertPage = page;
    for (var i = 0; i <= ArtistConcertPageCount; i++ ) {
        jQuery("#product_concert tr.concert-page-" + i).hide();
    }
    jQuery("#product_concert tr.concert-page-" + page).show();
    
    jQuery('#product_concert div.page-navigator div.page-item').each(function(i, obj) {
        if (i == ArtistConcertPage) {
            jQuery(this).addClass("selected");
        } else {
            jQuery(this).removeClass("selected");
        }
    });
};

function ap_select_concert_tab() {
    jQuery("#product_albums").hide();
    jQuery("#product_songs").hide();
    jQuery("#product_merchandise").hide();
    jQuery("#product_concert").show();
    jQuery("#product_tabs_concert").addClass("selected");
	jQuery("#product_tabs_concert").removeClass("deselected");
	jQuery("#product_tabs_album").removeClass("selected");
	jQuery("#product_tabs_album").addClass("deselected");
    jQuery("#product_tabs_song").removeClass("selected");
	jQuery("#product_tabs_song").addClass("deselected");
    jQuery("#product_tabs_merchandise").removeClass("selected");
	jQuery("#product_tabs_merchandise").addClass("deselected");
    jQuery("#search_title_album_container").hide();
    jQuery("#search_title_song_container").hide();
    jQuery("#search_title_merchandise_container").hide();
    jQuery("#search_title_concert_container").show();
    ap_select_artist_wiki_tab('concert');
	ap_sortItemNameTable(4);
	ap_colorOddEvenPriceTableConcert();
};

function ap_colorOddEvenPriceTableConcert() {
    var table = jQuery('#product_concert table.list-price-table');
    var rows = jQuery('tbody > tr',table);
    var count = 0;
    jQuery.each(rows, function(index, row) {
        var title = jQuery('td:eq(0)',row).text();
        if (title != "") {
            if (jQuery(this).is(":visible") === true) {
                if (count%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
                jQuery(this).removeClass("odd");
                jQuery(this).removeClass("even");
                jQuery(this).addClass(sclass);
                count++;
            }
        } else {
            jQuery(this).hide();
        }
    });
};







/********************************************************************************************************************
********************************************************************************************************************/
// 																			ALBUM PAGE
/********************************************************************************************************************
********************************************************************************************************************/
function parseJSONItemsForAlbumPage(json) {
    try {
    var record_store_name = "";
    jQuery.each(json, function(i, jsons) {
        if (i == "items") {
            jQuery.each(jsons, function(j, items) {
                jQuery.each(items, function(k, item) {
                    try {
                        var media_count = parseInt(jQuery("#media_format_" + item.media_format_name.toLowerCase() + " span.count").text().replace("(", "").replace(")", ""),10);
                        media_count++;
                        jQuery("#media_format_" + item.media_format_name.toLowerCase()).show();
                        jQuery("#media_format_" + item.media_format_name.toLowerCase() + " span.count").text("(" + media_count + ")");
                        if (item.media_format_name.toLowerCase() != "streaming") {
                            var media_count_all = parseInt(jQuery("#media_formats .media_format_all span.count").text().replace("(", "").replace(")", ""),10);
                            media_count_all++;
                            jQuery("#media_formats .media_format_all span.count").text("(" + media_count_all + ")");
                        }                        
                    } catch (err) {}
                    var row = jQuery('table.list-price-table tr').length;
                    if (row%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
                    var this_item_price = item.price_local;
                    this_item_price = (this_item_price * oCurrencyToEuro[item.currency_code] * oCurrencyFromEuro[jQuery("select.tableSelect:first option:selected").text()]);
                    if (this_item_price == 0) {
                        var price_text  = "-";
                    } else {
                        //var price_text  = ap_PriceFormat(this_item_price) + '&nbsp;' + jQuery("select.tableSelect:first option:selected").text();
						var price_text  = ap_PriceFormat(this_item_price);
                    }
                    var html = '';
                    var buy_at_url = item.buy_at_url;
                    if (item.affiliate_url && item.affiliate_encode_times) {
                        buy_at_url = ap_EncodeAffiliateLink(buy_at_url, item.affiliate_url, item.affiliate_encode_times);
                    }
                    html += '<tr class=\'' +  sclass + '\'>';
                    html += '<td class=\'list-album-album\'>' + item.item_name + '<\/td>';
                    html += '<td class=\'list-album-price-format\'><div class=\'text\'>' + item.media_format_name + '<\/div><div class=\'price\'>' + price_text + '<\/div><\/td>';
					//onclick=\'_gaq.push([&quot;_trackEvent&quot;, &quot;ResultPageAlbums&quot;, &quot;Click&quot;, &quot;' + item.record_store_name + '&quot;]);\'
                    html += '<td class=\'list-album-buy-at-url\'><a target=\'_blank\' href=\'' + buy_at_url + '\' onclick=\'ga("send", "event", "ResultPageAlbums", "Click", "' + item.record_store_name + '");\'>' + item.record_store_name + '<\/a><\/td>';
                    if (ap_PriceFormat(this_item_price) == "0.00" || ap_PriceFormat(this_item_price) == "0,00") {
                        html += '<td class=\'list-album-price-value\'>1000000<\/td>';
                    } else {
                        html += '<td class=\'list-album-price-value\'>' + ap_PriceFormat(this_item_price).replace(".","").replace(",","") + '<\/td>';
                    }
                    html += '<\/tr>';
                    jQuery('table.list-price-table').append(html);
                    ap_sortPriceTable();
                    record_store_name = item.record_store_class_name;
                });
            });
        } else if (i == "item_count") {
            jQuery.each(jsons, function(j, items) {
                if (parseInt(items.item_count,10) >= 0) {
                    jQuery('#album_wiki div.albums div.streaming_' + items.name + ' span.count').text(items.item_count);
                    record_store_name = items.name;
                }
            });
        }
    });
    if (record_store_name == "itunes" || record_store_name == "7digital") {
	} else {
        ap_BuildStreamingIconsAnimationIndexes('', record_store_name);
    }
    } catch (err) { }
};



/********************************************************************************************************************
********************************************************************************************************************/
// 																			SONG PAGE
/********************************************************************************************************************
********************************************************************************************************************/
function parseJSONItemsForSongPage(json) {
    try {
    var record_store_name = "";
    jQuery.each(json, function(i, jsons) {

        if (i == "items") {
            jQuery.each(jsons, function(j, items) {
                jQuery.each(items, function(k, item) {
                    try {
                        var media_count = parseInt(jQuery("#media_format_" + item.media_format_name.toLowerCase() + " span.count").text().replace("(", "").replace(")", ""),10);
                        media_count++;
                        jQuery("#media_format_" + item.media_format_name.toLowerCase()).show();
                        jQuery("#media_format_" + item.media_format_name.toLowerCase() + " span.count").text("(" + media_count + ")");
                        if (item.media_format_name.toLowerCase() != "streaming") {
                            var media_count_all = parseInt(jQuery("#media_formats .media_format_all span.count").text().replace("(", "").replace(")", ""),10);
                            media_count_all++;
                            jQuery("#media_formats .media_format_all span.count").text("(" + media_count_all + ")");
                        }
                    } catch (err) {}
                    var row = jQuery('table.list-price-table tr').length;
                    if (row%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
                    var this_item_price = item.price_local;
                    this_item_price = (this_item_price * oCurrencyToEuro[item.currency_code] * oCurrencyFromEuro[jQuery("select.tableSelect:first option:selected").text()]);
                    if (this_item_price == 0) {
                        var price_text  = "-";
                    } else {
                        //var price_text  = ap_PriceFormat(this_item_price) + '&nbsp;' + jQuery("select.tableSelect:first option:selected").text();
						var price_text  = ap_PriceFormat(this_item_price);
                    }
                    var html = '';
                    var buy_at_url = item.buy_at_url;
                    if (item.affiliate_url && item.affiliate_encode_times) {
                        buy_at_url = ap_EncodeAffiliateLink(buy_at_url, item.affiliate_url, item.affiliate_encode_times);
                    }
                    html += '<tr class=\'' +  sclass + '\'>';
                    html += '<td class=\'list-song-song\'>' + item.item_name + '<\/td>';
                    html += '<td class=\'list-album-price-format\'><div class=\'text\'>' + item.media_format_name + '<\/div><div class=\'price\'>' + price_text + '<\/div><\/td>';
					//onclick=\'_gaq.push([&quot;_trackEvent&quot;, &quot;ResultPageSongs&quot;, &quot;Click&quot;, &quot;' + item.record_store_name + '&quot;]);\'
                    html += '<td class=\'list-song-buy-at-url\'><a target=\'_blank\' href=\'' + buy_at_url + '\' onclick=\'ga("send", "event", "ResultPageSongs", "Click", "' + item.record_store_name + '");\'>' + item.record_store_name + '<\/a><\/td>';
                    if (ap_PriceFormat(this_item_price) == "0.00" || ap_PriceFormat(this_item_price) == "0,00") {
                        html += '<td class=\'list-album-price-value\'>1000000<\/td>';
                    } else {
                        html += '<td class=\'list-album-price-value\'>' + ap_PriceFormat(this_item_price).replace(".","").replace(",","") + '<\/td>';
                    }
                    html += '<\/tr>';
                    jQuery('table.list-price-table').append(html);

                    ap_sortPriceTable();
                    record_store_name = item.record_store_class_name;
                });
            });
        } else if (i == "item_count") {
            jQuery.each(jsons, function(j, items) {
                if (parseInt(items.item_count,10) >= 0) {
                    jQuery('#song_wiki div.songs div.streaming_' + items.name + ' span.count').text(items.item_count);
                    record_store_name = items.name;
                }
            });
        }
    });
    if (record_store_name == "itunes" || record_store_name == "7digital") {
	} else {
        ap_BuildStreamingIconsAnimationIndexes('', record_store_name);
    }
    } catch (err) { }
};
