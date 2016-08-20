 <?php
/**
 * include template overwrites
 */
 
$path_airplaymusic = drupal_get_path('theme', 'airplaymusic');
  #include_once './' . $path_airplaymusic . '/functions/css.php';
  #include_once './' . $path_airplaymusic . '/functions/form.php';
  #include_once './' . $path_airplaymusic . '/functions/table.php';
  #include_once './' . $path_airplaymusic . '/functions/menu.php';
  #include_once './' . $path_airplaymusic . '/functions/system.php';
  #include_once './' . $path_airplaymusic . '/functions/date.php';
  #include_once './' . $path_airplaymusic . '/functions/misc.php';

/**
 * Implements HOOK_theme().
 */
function airplaymusic_theme(){
  return array(
    'nomarkup' => array (
      'render element' => 'element',
     ),
  );
}


function airplaymusic_section() {
  $section_path = explode('/', request_uri());
  $section_name = $section_path[1];
  $section_q = strpos($section_name, '?');

  if ($section_q !== FALSE) {
    $section_name = substr($section_name, 0, $section_q);
  }
  
  switch ($section_name) {
    case '':
      return 'section_home';
      break;
	case 'pladeforretninger':
      return 'section_guides';
      break;
	case 'pladeforretning':
      return 'section_guides';
      break;
	case 'musikfestival-guide':
      return 'section_guides';
      break;
	case 'koncert-guide':
      return 'section_guides';
      break;
    case 'journal':
      return 'section_journal';
      break;
    case 'about':
      return 'section_about';
      break;
    case 'work':
      return 'section_work';
      break;
    case 'resources':
      return 'section_resources';
      break;
    case 'contact':
      return 'section_contact';
      break;
    case 'search':
      return 'section_search';
      break;
    case 'user':
      return 'section_user';
      break;
    case 'users':
      return 'section_user';
      break;
    case 'filter':
      return 'section_filter';
      break;
    case 'admin':
      return 'section_admin';
      break;
    case 'kunstner':
      return 'section_artist';
      break;
    case 'artist':
      return 'section_artist';
      break;
    case 'music-search':
      return 'section_music_search';
      break;
    default:
      return 'section_404';
  }
}

function airplaymusic_process_html(&$vars) {
  $before = array(
    "/>\s\s+/",
    "/\s\s+</",
    "/>\t+</",
    "/\s\s+(?=\w)/",
    "/(?<=\w)\s\s+/"
  );

  $after = array('> ', ' <', '> <', ' ', ' ');


  $page_top = $vars['page_top'];
  $page_top = preg_replace($before, $after, $page_top);
  $vars['page_top'] = $page_top;


  if (!preg_match('/<pre|<textarea/', $vars['page'])) {
    $page = $vars['page'];
    $page = preg_replace($before, $after, $page);
    $vars['page'] = $page;
  }

  $page_bottom = $vars['page_bottom'];
  $page_bottom = preg_replace($before, $after, $page_bottom);
  $vars['page_bottom'] = $page_bottom . drupal_get_js('footer');
}
