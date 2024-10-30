<?php
/*
Plugin Name: Comment Geo Maps
Plugin URI: http://oss.openplans.org/commentgeomaps/
Description: Turns posts in a particular category and pages with a certain parent into geo-based issue maps.  Commenters are prompted to enter a location and then the comments are plotted as a points on a map.
Author: Chris Abraham
Version: 0.2
Author URI: http://cjyabraham.com

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St - 5th Floor, Boston, MA  02110-1301, USA.

*/

require_once('extra-comment-fields.php');

$cgm_root = get_option('siteurl') . '/wp-content/plugins/' . basename(dirname(__FILE__));
//$cgm_openlayers_path = 'http://openlayers.org/dev';
$cgm_openlayers_path = 'http://openlayers.org/api/2.8-rc5';
$cgm_settings = get_option('cgm_settings');


function cgm_activated() {
  global $cgm_settings, $post;
  if(!is_page() && in_category($cgm_settings['geo_map_category'])) {
    return true;
  } elseif (is_page() && $post->post_parent == $cgm_settings['geo_map_parent_page']) {
    return true;
  }
  return false;
}

function cgm_pageclass() {
  global $cgm_settings, $post;
  if (is_page() && $post->post_parent == $cgm_settings['geo_map_parent_page']) {
    echo 'cgm_fullpage';
  }

}


register_activation_hook(__FILE__, 'cgm_install');
function cgm_install() {
        global $wpdb;

        $defaults = array(
                          'map_api_key' => "",
                          'geo_map_category' => "",
                          'geo_map_parent_page' => "",
                          );

        add_option('cgm_settings', $defaults, 'Options for Comment Geo Maps');

        if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."comments_geo'") != $wpdb->prefix.'comments_geo') {
                $sql = "CREATE TABLE `".$wpdb->prefix."comments_geo` (
                                `comment_ID` BIGINT NOT NULL ,
                                `location` VARCHAR(80),
                                `lon` FLOAT(10,6),
                                `lat` FLOAT(10,6),
                                PRIMARY KEY ( `comment_id` )
                                )";

                require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
                dbDelta($sql);
        }

}


add_action('admin_menu', 'cgm_options_page');
function cgm_options_page()
{
        if (function_exists('add_options_page'))
        {
          add_options_page('Comments Geo Maps', 'Comment Geo Maps', 8, 'comment-geo-maps/comment-geo-maps.php', 'cgm_options_subpanel');
        }
}

function cgm_options_subpanel()
{
        global $cgm_settings;

        if (isset($_POST['cgm_save_changes']))
        {
                check_admin_referer('cgm_save_changes');
                $cgm_settings['map_api_key'] = $_POST['cgm_map_api_key'];
                $cgm_settings['geo_map_category'] = $_POST['cgm_geo_map_category'];
                $cgm_settings['geo_map_parent_page'] = $_POST['cgm_geo_map_parent_page'];
                update_option('cgm_settings', $cgm_settings);
                $status_message =  "<h3>Your new options have been updated<h3>";
        } else {
                $status_message = '';
        }
        ?>
        <div class="wrap">
                <h2>Comment Geo Maps</h2>
                <?php echo $status_message;?>
                <form action="" method="post">
                    <?php wp_nonce_field('cgm_save_changes'); ?>
                    <input type="hidden" name="cgm_save_changes" value="true" />
                    <table class="form-table">
                      <tr valign="top">
                        <th scope="row">Map API key</th>
                        <td><input type="text" size="100" name="cgm_map_api_key" id="cgm_map_api_key" value="<?php echo $cgm_settings['map_api_key']?>" />
                      </tr>
                      <tr valign="top">
                        <th scope="row">Comment Geo Maps Category</th>
                        <td><select name="cgm_geo_map_category" id="cgm_geo_map_category">
                        <?php $categories = get_categories(array('hide_empty' => false)); ?>
                        <?php foreach($categories as $cat):?>
                            <option <?php if ($cat->term_id==$cgm_settings['geo_map_category']) echo 'selected="selected"';?> value="<?php echo $cat->term_id;?>"><?php echo $cat->name;?></option>
                        <?php endforeach;?>
                        </select>
                            <div>All posts in this category will have mapping enabled for their comments.</div></td>
                      </tr>
                      <tr valign="top">
                        <th scope="row">Comment Geo Maps Parent Page</th>
                        <td><select name="cgm_geo_map_parent_page" id="cgm_geo_map_parent_page">
                        <?php $pages = get_pages(); ?>
                        <?php foreach($pages as $pagg):?>
                            <option <?php if ($pagg->ID==$cgm_settings['geo_map_parent_page']) echo 'selected="selected"';?> value="<?php echo $pagg->ID;?>"><?php echo $pagg->post_title;?></option>
                        <?php endforeach;?>
                        </select>
                            <div>All pages with this parent will have mapping enabled for their comments.</div></td>
                      </tr>
                    </table>
                    <p class="submit">
                    <input type="submit" name="submit" value="Save Changes" />
                    </p>
                    <input type="hidden" name="page_options" value="cgm_map_api_key,cgm_geo_map_category" />
               </form>
        </div>
        <?php
}

function cgm_dogeocode($location) {
  global $cgm_settings;
  $base_url = "http://maps.google.com/maps/geo?output=csv" . "&key=" . $cgm_settings['map_api_key'];
  $request_url = $base_url . "&q=" . urlencode($location);

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $request_url);
  curl_setopt($ch, CURLOPT_HEADER,0);
  curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}


add_filter('preprocess_comment', 'cgm_geocode');
function cgm_geocode($comment_data) {
  if (($_POST['cgm-lon'] && $_POST['cgm-lat']) || !$_POST['cgm_location']) {
    // no need to geocode
    return $comment_data;
  }

  $data = cgm_dogeocode($_POST['cgm_location']);
  $status = substr($data,0,3);
  if (strcmp($status, "200") == 0) {
    // Successful geocode
    $data = explode(",",$data);
    $_POST['cgm-lon'] = $data[3];
    $_POST['cgm-lat'] = $data[2];
    return $comment_data;
  } else {
    // failure to geocode
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>WordPress &rsaquo; Error</title>
        <link rel="stylesheet" href="<?php echo get_option('siteurl');?>/wp-admin/css/install.css" type="text/css" />
</head>
<body id="error-page">
<p>Error: The Location you entered ("<?php echo $_POST['cgm_location'] ?>") could not be found.  Please press Back and try again.</p></body>
</html>
<?php
    die();
  }
}

function cgm_insert_map() {
  global $cgm_root, $cgm_settings, $cgm_openlayers_path;
  if (!cgm_activated()) return;

  echo '<script type="text/javascript" src="' . $cgm_openlayers_path . '/OpenLayers.js"></script>'."\n";
  echo '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $cgm_settings['map_api_key'] . '" type="text/javascript"></script>';
//  echo '<script type="text/javascript" src="' . $cgm_root . '/cloudmade/cloudmade.js"></script>';
  echo '<script type="text/javascript" src="' . $cgm_root. '/mapping.js"></script>'."\n";
  echo '<div id="commentsmap"></div>' . "\n";

}

function cgm_insert_map_markers($comments) {
  global $cgm_root, $cgm_openlayers_path, $comment, $cgm_settings;
  if (!cgm_activated()) return;

  $coords_script = '<script type="text/javascript">' . "\n" .
    "var cgm_openlayers_path = '$cgm_openlayers_path';\n" .
    "var cgm_root = '$cgm_root';\n" .
    "var cgm_map_api_key = '" . $cgm_settings['map_api_key'] . "';\n";

  if (is_page())
    $coords_script .= "var is_page = true;\n";
  else
    $coords_script .= "var is_page = false;\n";


  $points = array();
  foreach($comments as $comment) {
    if($comment->lat && $comment->lon) {
      $popup = "<div style='padding: 0px;'><div class='credit'>";
      if (stristr(get_option('siteurl'), 'streetsblog')) {
        if (substr($comment->comment_author_url,0,strlen($Opencore_remote_url) + 7) == $Opencore_remote_url . 'people/')
          $popup .= "<a href='" . $comment->comment_author_url . "'><img class='thumbnail' src='" . $comment->comment_author_url . "/portrait_square_fifty_thumb' alt='Post Thumbnail' /></a>";
        //else
        //$popup .= "<a href='" . $comment->comment_author_url . "'><img class='thumbnail' src='" . get_bloginfo('template_url') . "/img/commenter_icon_anon_50.png' alt='Post Thumbnail' /></a>";
      } elseif (stristr(get_option('siteurl'), 'gothamschools')){
        if (in_array((int)$comment->user_id, array(2, 11)))
          $commenter_role = 'staff';
        else
          $commenter_role = ($comment->extra_role) ? $comment->extra_role : 'citizen';

        $default_icon_path = get_bloginfo('template_url') . '/images/role-' . $commenter_role . '-avatar.png';
        $avatar = get_avatar( $comment, $size = '38', $default = $default_icon_path);
        $popup .= $avatar;
      } else {
        $avatar = get_avatar( $comment, $size = '38');
        $popup .= "<a href='" . $comment->comment_author_url . "'>" . $avatar . "</a>";
      }
      $popup .= "<h4 class='comment-author'>" . get_comment_author_link() . "</h4>" . get_comment_date('F j, Y');
      $popup .= "</div>";
      $popup .= "<div class='comment-content selfclear'>";
      $popup .= apply_filters('comment_text', get_comment_text() );
      $popup .= '</div></div>';

      $points[] = array($comment->location, $comment->lon, $comment->lat, $popup, $comment->comment_ID);
    }
  }
  $coords_script .= "var points = " . json_encode($points) . ";\n</script>\n";
  echo $coords_script;
}

add_action('template_redirect', 'cgm_request');
function cgm_request() {
        if (preg_match(',/cgm_geocode\?(.*?)$,', $_SERVER['REQUEST_URI'])) {
                header("HTTP/1.0 200 OK");
                include (ABSPATH.'/wp-content/plugins/comment-geo-maps/find_location_request.php');
                exit;
        }
}

add_action('wp_head', 'cgm_style');
function cgm_style() {
  if (!cgm_activated()) return;
?>
<style type="text/css">
#cgm-error {
float:left;
color: red;
margin-top: 0.3em;
}

#cgm-info {
float:left;
color: green;
margin-top: 0.3em;
}

#commentsmap_OpenLayers_ViewPort {
z-index: 1;
}

</style>
<?php
}

add_action('init', 'add_jquery');
function add_jquery() {
  wp_enqueue_script('jquery');
}


?>