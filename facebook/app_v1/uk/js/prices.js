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

function ap_sortPriceTable() {
    try {
    jQuery("table.list-price-table").tablesorter(
    {
        sortList: [[4,0],[4,0]],
        headers: { 
            0: { sorter: false},
            1: { sorter: false},
            2: { sorter: false},
			3: { sorter: false}
        } 
    });
    } catch (err) { }
    ap_colorOddEvenPriceTable();
};

function ap_colorOddEvenPriceTable() {
    var table = jQuery('table.list-price-table');
    var rows = jQuery('tbody > tr',table);
    
    jQuery.each(rows, function(index, row) {
        var title = jQuery('td:eq(0)',row).text();
        if (title != "") {
            if (index%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
            jQuery(this).removeClass("odd");
            jQuery(this).removeClass("even");
            jQuery(this).addClass(sclass);
            table.append(row);
        } else {
            jQuery(this).hide();
        }
    });
};


function parseJSONItemsForArtistPageAlbums(json) {

    try {
    var record_store_name = "";
    jQuery.each(json, function(i, jsons) {
        if (i == "items") {
            jQuery.each(jsons, function(j, items) {
                jQuery.each(items, function(k, item) {
                    if (item.record_store_class_name == "spotify" && oAlbumJSONspotify == "") {
                       oAlbumJSONspotify = json;
                    } else if (item.record_store_class_name == "rdio" && oAlbumJSONrdio == "") {
                       oAlbumJSONrdio = json;
                    } else if (item.record_store_class_name == "deezer" && oAlbumJSONdeezer == "") {
                       oAlbumJSONdeezer = json;
                    } else if (item.record_store_class_name == "wimp" && oAlbumJSONwimp == "") {
                       oAlbumJSONwimp = json;
                    } else if (item.record_store_class_name == "napster" && oAlbumJSONnapster == "") {
                       oAlbumJSONnapster = json;
                    } else if (item.record_store_class_name == "itunes" && oAlbumJSONitunes == "") {
                       oAlbumJSONitunes = json;
                    } else if (item.record_store_class_name == "7digital" && oAlbumJSON7digital == "") {
                       oAlbumJSON7digital = json;
                    }
                    jQuery("table.list-price-table tr.album-page-" + ArtistAlbumPage).each(function() {	
                        var ItemNameLower = "";
                        try {ItemNameLower = jQuery("td.title", this).text();} catch(err) {}
                        if (ItemNameLower.toLowerCase() == item.item_name.toLowerCase()) {
                            if (item.record_store_class_name == "itunes" || item.record_store_class_name == "7digital") {
                                //var item_min_price = parseInt(jQuery("td.list-artist-price-format span.min-price",this).text().replace(",", "").replace(".", ""), 10);
                                //var item_max_price = parseInt(jQuery("td.list-artist-price-format span.max-price",this).text().replace(",", "").replace(".", ""), 10);
                                var item_num_price = parseInt(jQuery("td.buy_at span.price_count",this).text(), 10);
                                item_num_price++;
								if (item_num_price == 1) {
									jQuery("td.buy_at a",this).html(token_wiev_price.replace("[ITEM_COUNT]", item_num_price));
								} else {
									jQuery("td.buy_at a",this).html(token_wiev_prices.replace("[ITEM_COUNT]", item_num_price));
								}
                                //var this_item_price = item.price_local;
                                //this_item_price = (this_item_price * oCurrencyToEuro[item.currency_code] * oCurrencyFromEuro[jQuery("select.tableSelect option:selected").text()]);
                                //jQuery("td.buy_at span.price_count",this).text(item_num_price);
                                /*if (parseInt(this_item_price,10) > item_max_price) {
                                    jQuery("td.list-artist-price-format span.max-price",this).text(ap_PriceFormat(this_item_price));
                                }*/
                                /*if (parseInt(this_item_price,10) < item_min_price || item_min_price == 0) {
                                    jQuery("td.list-artist-price-format span.min-price",this).text(ap_PriceFormat(this_item_price));
                                }*/
                                /*var media_count = parseInt(jQuery("#product_albums #media_format_" + item.media_format_name.toLowerCase() + " span.count").text().replace("(", "").replace(")", ""),10);
                                var media_count_all = parseInt(jQuery("#product_albums .media_format_all span.count").text().replace("(", "").replace(")", ""),10);
                                media_count++;
                                media_count_all++;
                                jQuery("#product_albums #media_format_" + item.media_format_name.toLowerCase()).show();
                                jQuery("#product_albums #media_format_" + item.media_format_name.toLowerCase() + " span.count").text("(" + media_count + ")");
                                jQuery("#product_albums .media_format_all span.count").text("(" + media_count_all + ")");*/
                            } else {
                                jQuery("td.streaming span." + item.record_store_class_name,this).show();
                                var buy_at_url = item.buy_at_url;
                                if (item.affiliate_url && item.affiliate_encode_times) {
                                    buy_at_url = ap_EncodeAffiliateLink(item.buy_at_url, item.affiliate_url, item.affiliate_encode_times);
                                }
                                jQuery("td.streaming span." + item.record_store_class_name + " a", this).attr("href", buy_at_url);
                                record_store_name = item.record_store_class_name;
                            }
                        }
                    });
                });
            });
        } else if (i == "item_count") {
            /*jQuery.each(jsons, function(j, items) {
                if (parseInt(items.item_count,10) >= 0) {
                    jQuery('#artist_wiki div.albums .streaming_' + items.name + ' #count').text(items.item_count);
                    record_store_name = items.name;
                }
            });*/
        }
    });
   /* if (record_store_name == "itunes" || record_store_name == "7digital") {
	} else {
        ap_BuildStreamingIconsAnimationIndexes('#artist_wiki div.albums', record_store_name);
    }*/
    } catch (err) { }
};



function parseJSONItemsForArtistPageSongs(json) {

    try {
    var record_store_name = "";
    jQuery.each(json, function(i, jsons) {
        if (i == "items") {
            jQuery.each(jsons, function(j, items) {
                jQuery.each(items, function(k, item) {
                    if (item.record_store_class_name == "spotify" && oSongJSONspotify == "") {
                       oSongJSONspotify = json;
                    } else if (item.record_store_class_name == "rdio" && oSongJSONrdio == "") {
                       oSongJSONrdio = json;
                    } else if (item.record_store_class_name == "deezer" && oSongJSONdeezer == "") {
                       oSongJSONdeezer = json;
                    } else if (item.record_store_class_name == "wimp" && oSongJSONwimp == "") {
                       oSongJSONwimp = json;
                    } else if (item.record_store_class_name == "napster" && oSongJSONnapster == "") {
                       oSongJSONnapster = json;
                    } else if (item.record_store_class_name == "itunes" && oSongJSONitunes == "") {
                       oSongJSONitunes = json;
                    } else if (item.record_store_class_name == "7digital" && oSongJSON7digital == "") {
                       oSongJSON7digital = json;
                    }
                    jQuery("table.list-price-table tr.song-page-" + ArtistSongPage).each(function() {
                        var ItemNameLower = "";
                        try {ItemNameLower = jQuery("td.title",this).text().toLowerCase();} catch(err) {}
                        if (ItemNameLower == item.item_name.toLowerCase()) {
                            if (item.record_store_class_name == "itunes" || item.record_store_class_name == "7digital") {
                                //var item_min_price = parseInt(jQuery("td.list-artist-price-format span.min-price",this).text().replace(",", "").replace(".", ""), 10);
                                //var item_max_price = parseInt(jQuery("td.list-artist-price-format span.max-price",this).text().replace(",", "").replace(".", ""), 10);
                                var item_num_price = parseInt(jQuery("td.buy_at span.price_count",this).text(), 10);
                                item_num_price++;
								if (item_num_price == 1) {
									jQuery("td.buy_at a",this).html(token_wiev_price.replace("[ITEM_COUNT]", item_num_price));
								} else {
									jQuery("td.buy_at a",this).html(token_wiev_prices.replace("[ITEM_COUNT]", item_num_price));
								}
                                //jQuery("td.buy_at span.price_count",this).text(item_num_price);
                                //var this_item_price = item.price_local;
                                //this_item_price = (this_item_price * oCurrencyToEuro[item.currency_code] * oCurrencyFromEuro[jQuery("select.tableSelect option:selected").text()]);
                                /*if (parseInt(this_item_price,10) > item_max_price) {
                                    jQuery("td.list-artist-price-format span.max-price",this).text(ap_PriceFormat(this_item_price));
                                }
                                if (parseInt(this_item_price,10) < item_min_price || item_min_price == 0) {
                                    jQuery("td.list-artist-price-format span.min-price",this).text(ap_PriceFormat(this_item_price));
                                }*/
                                /*var media_count = parseInt(jQuery("#product_songs #media_format_" + item.media_format_name.toLowerCase() + " span.count").text().replace("(", "").replace(")", ""),10);
                                var media_count_all = parseInt(jQuery("#product_songs .media_format_all span.count").text().replace("(", "").replace(")", ""),10);
                                media_count++;
                                media_count_all++;
                                jQuery("#product_songs #media_format_" + item.media_format_name.toLowerCase()).show();
                                jQuery("#product_songs #media_format_" + item.media_format_name.toLowerCase() + " span.count").text("(" + media_count + ")");
                                jQuery("#product_songs .media_format_all span.count").text("(" + media_count_all + ")");*/
                            } else {
                                jQuery("td.streaming span." + item.record_store_class_name,this).show();
                                var buy_at_url = item.buy_at_url;
                                if (item.affiliate_url && item.affiliate_encode_times) {
                                    buy_at_url = ap_EncodeAffiliateLink(buy_at_url, item.affiliate_url, item.affiliate_encode_times);
                                }
                                jQuery("td.streaming span." + item.record_store_class_name + " a", this).attr("href", buy_at_url);
                                record_store_name = item.record_store_class_name;
                            }
                        }
                    });
                });
            });
        } else if (i == "item_count") {
            /*jQuery.each(jsons, function(j, items) {
                if (parseInt(items.item_count,10) >= 0) {
                    jQuery('#artist_wiki div.songs div.streaming_' + items.name + ' span#count').text(items.item_count);
                    record_store_name = items.name;
                }
            });*/
        }
    });
    /*if (record_store_name == "itunes" || record_store_name == "7digital") {
	} else {
        ap_BuildStreamingIconsAnimationIndexes('#artist_wiki div.songs', record_store_name);
    }*/
    } catch (err) { console.log(err); }
};



function parseJSONItemsForAlbumPage(json) {

    try {
    var record_store_name = "";
    jQuery.each(json, function(i, jsons) {
        if (i == "items") {
            jQuery.each(jsons, function(j, items) {
                jQuery.each(items, function(k, item) {
                    /*try {
                        var media_count = parseInt(jQuery("#media_format_" + item.media_format_name.toLowerCase() + " span.count").text().replace("(", "").replace(")", ""),10);
                        media_count++;
                        jQuery("#media_format_" + item.media_format_name.toLowerCase()).show();
                        jQuery("#media_format_" + item.media_format_name.toLowerCase() + " span.count").text("(" + media_count + ")");
                        if (item.media_format_name.toLowerCase() != "streaming") {
                            var media_count_all = parseInt(jQuery("#media_formats .media_format_all span.count").text().replace("(", "").replace(")", ""),10);
                            media_count_all++;
                            jQuery("#media_formats .media_format_all span.count").text("(" + media_count_all + ")");
                        }                        
                    } catch (err) {}*/
                    var row = jQuery('table.list-price-table tbody tr').length;
                    if (row%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
                    var this_item_price = item.price_local;
                    //this_item_price = (this_item_price * oCurrencyToEuro[item.currency_code] * oCurrencyFromEuro[jQuery("select.tableSelect:first option:selected").text()]);
					this_item_price = (this_item_price * oCurrencyToEuro[item.currency_code] * oCurrencyFromEuro['DKK']);
                    if (this_item_price == 0) {
                        var price_text  = "-";
                    } else {
                        //var price_text  = ap_PriceFormat(this_item_price) + '&nbsp;' + jQuery("select.tableSelect:first option:selected").text();
						var price_text  = ap_PriceFormat(this_item_price) + '&nbsp;' + 'DKK';
                    }
                    var html = '';
                    var buy_at_url = item.buy_at_url;
                    if (item.affiliate_url && item.affiliate_encode_times) {
                        buy_at_url = ap_EncodeAffiliateLink(buy_at_url, item.affiliate_url, item.affiliate_encode_times);
                    }
                    html += '<tr class=\'' +  sclass + '\'>';
                    html += '<td class=\'title\'>' + item.item_name + '<\/td>';
                    html += '<td class=\'media_format\'>' + item.media_format_name + '<\/td>';
					html += '<td class=\'price\'>' + price_text + '<\/td>';
                    html += '<td class=\'buy_at\'><a target=\'_blank\' href=\'' + buy_at_url + '\'>' + item.record_store_name + '<\/a><\/td>';
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
            /*jQuery.each(jsons, function(j, items) {
                if (parseInt(items.item_count,10) >= 0) {
                    jQuery('#album_wiki div.albums div.streaming_' + items.name + ' span#count').text(items.item_count);
                    record_store_name = items.name;
                }
            });*/
        }
    });
    /*if (record_store_name == "itunes" || record_store_name == "7digital") {
	} else {
        ap_BuildStreamingIconsAnimationIndexes('', record_store_name);
    }*/
    } catch (err) { console.log(err); }
};


function parseJSONItemsForSongPage(json) {

    try {
    var record_store_name = "";
    jQuery.each(json, function(i, jsons) {

        if (i == "items") {
            jQuery.each(jsons, function(j, items) {
                jQuery.each(items, function(k, item) {
                    /*try {
                        var media_count = parseInt(jQuery("#media_format_" + item.media_format_name.toLowerCase() + " span.count").text().replace("(", "").replace(")", ""),10);
                        media_count++;
                        jQuery("#media_format_" + item.media_format_name.toLowerCase()).show();
                        jQuery("#media_format_" + item.media_format_name.toLowerCase() + " span.count").text("(" + media_count + ")");
                        if (item.media_format_name.toLowerCase() != "streaming") {
                            var media_count_all = parseInt(jQuery("#media_formats .media_format_all span.count").text().replace("(", "").replace(")", ""),10);
                            media_count_all++;
                            jQuery("#media_formats .media_format_all span.count").text("(" + media_count_all + ")");
                        }
                    } catch (err) {}*/
                    var row = jQuery('table.list-price-table tr').length;
                    if (row%2 != 0) { sclass = 'odd'; } else { sclass = 'even'; }
                    var this_item_price = item.price_local;
                    //this_item_price = (this_item_price * oCurrencyToEuro[item.currency_code] * oCurrencyFromEuro[jQuery("select.tableSelect:first option:selected").text()]);
					this_item_price = (this_item_price * oCurrencyToEuro[item.currency_code] * oCurrencyFromEuro['DKK']);
                    if (this_item_price == 0) {
                        var price_text  = "-";
                    } else {
                        //var price_text  = ap_PriceFormat(this_item_price) + '&nbsp;' + jQuery("select.tableSelect:first option:selected").text();
						var price_text  = ap_PriceFormat(this_item_price) + '&nbsp;' + 'DKK';
                    }
                    var html = '';
                    var buy_at_url = item.buy_at_url;
                    if (item.affiliate_url && item.affiliate_encode_times) {
                        buy_at_url = ap_EncodeAffiliateLink(buy_at_url, item.affiliate_url, item.affiliate_encode_times);
                    }
                    html += '<tr class=\'' +  sclass + '\'>';
                    html += '<td class=\'title\'>' + item.item_name + '<\/td>';
                    //html += '<td class=\'list-song-price-format\'><div class=\'text\'>' + item.media_format_name + '<\/div><div class=\'price\'>' + price_text + '<\/div><\/td>';
					html += '<td class=\'media_format\'>' + item.media_format_name + '<\/td>';
					html += '<td class=\'price\'>' + price_text + '<\/td>';
                    html += '<td class=\'buy_at\'><a target=\'_blank\' href=\'' + buy_at_url + '\'>' + item.record_store_name + '<\/a><\/td>';
                    if (ap_PriceFormat(this_item_price) == "0.00" || ap_PriceFormat(this_item_price) == "0,00") {
                        html += '<td class=\'list-song-price-value\'>1000000<\/td>';
                    } else {
                        html += '<td class=\'list-song-price-value\'>' + ap_PriceFormat(this_item_price).replace(".","").replace(",","") + '<\/td>';
                    }
                    html += '<\/tr>';
                    jQuery('table.list-price-table').append(html);
                    ap_sortPriceTable();
                    record_store_name = item.record_store_class_name;
                });
            });
        } else if (i == "item_count") {
            /*jQuery.each(jsons, function(j, items) {
                if (parseInt(items.item_count,10) >= 0) {
                    jQuery('#song_wiki div.songs div.streaming_' + items.name + ' span#count').text(items.item_count);
                    record_store_name = items.name;
                }
            });*/
        }
    });
    /*if (record_store_name == "itunes" || record_store_name == "7digital") {
	} else {
        ap_BuildStreamingIconsAnimationIndexes('', record_store_name);
    }*/
    } catch (err) { console.log(err); }
};

