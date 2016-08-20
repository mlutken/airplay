UPDATE airplay_music.settings SET settings_value = (SELECT COUNT(*) FROM artist) WHERE settings_name = 'artist__count';
UPDATE airplay_music.settings SET settings_value = (SELECT COUNT(*) FROM album) WHERE settings_name = 'album__count';
UPDATE airplay_music.settings SET settings_value = (SELECT COUNT(*) FROM song) WHERE settings_name = 'song__count';