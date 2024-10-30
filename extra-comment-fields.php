<?php
/*
This code adapted from Extra Comment Fields plugin by Nate Weiner.
 */


add_filter('comment_save_pre', 'cgm_ecf_edit_comment');
function cgm_ecf_edit_comment($comment_content) { global $wpdb;
        if ($_POST['action']=='editedcomment' && $_POST['location']) {
          $wpdb->query(
                       "UPDATE ".$wpdb->prefix."comments_geo SET
                                ". $_POST['location'] ."
                        WHERE comment_ID = '".$_POST['comment_ID']."'" );
        }
        return $comment_content;
}

add_action('delete_comment', 'cgm_ecf_delete_comment');
function cgm_ecf_delete_comment($id) { global $wpdb;
        $wpdb->query(
                        "DELETE FROM ".$wpdb->prefix."comments_geo
                        WHERE comment_id = '$id' LIMIT 1" );

}

add_action('comment_post', 'cgm_ecf_saveFields');
function cgm_ecf_saveFields($comment_id) {
        global $wpdb;
        $location = $_POST['cgm_location'];
        $lat = $_POST['cgm-lat'];
        $lon = $_POST['cgm-lon'];
        if ($location) {
          $result = $wpdb->query("INSERT INTO ".$wpdb->prefix."comments_geo
                (comment_ID, location, lon, lat) VALUES ('$comment_id', '$location', $lon, $lat)
                ");
        }
}

add_filter('comment_edit_pre', 'cgm_ecf_getComment');
function cgm_ecf_getComment($commentTxt) { global $comment, $comments;
        $comments[0] = $comment;
        $comments = cgm_ecf_addOnComments($comments);
        $comment = $comments[0];
        return $comment->comment_content;
}
add_filter('manage_comments_nav', 'cgm_ecf_addOnComments');
function cgm_ecf_addOnComments($overrideComments=0) { global $comments, $wpdb;
        //$comments = (($overrideComments)?($overrideComments):($comments));
        if (!empty($comments)) {

                $compareComments = $comments;
                reset($comments);
                $firstComment = current($comments);
                $sql = "SELECT xc.*,
                                FROM ".$wpdb->prefix."comments c, ".$wpdb->prefix."comments_geo xc
                                WHERE c.comment_ID <= '".$firstComment->comment_ID."' AND c.comment_ID = xc.comment_ID
                                ORDER BY c.comment_ID DESC
                                LIMIT 45";
                $result = $wpdb->get_results($sql);
                for($i=0; $i<count($result); $i++) {
                        $objectIndex = cgm_ecf_whatCommmentObject($result[$i]->comment_ID, $compareComments);
                        if (isset($comments[$objectIndex])) {
                          $nvar = 'location';
                          $comments[$objectIndex]->$nvar = $result[$i]->$nvar;
                          unset($compareComments[$objectIndex]);
                        }
                }
        }
        return $comments;
}


add_action('comments_array', 'cgm_ecf_getComments', 10, 2);
function cgm_ecf_getComments($comments, $post_id) {
        global $wpdb;
        $compareComments = $comments;

        if (!empty($comments)) {
                $result = $wpdb->get_results("SELECT xc.*
                                                                FROM " . $wpdb->prefix . "comments c, " . $wpdb->prefix . "comments_geo xc
                                                                WHERE c.comment_post_ID = '$post_id' AND c.comment_ID = xc.comment_ID");
                for($i=0; $i<count($result); $i++) {
                        $objectIndex = cgm_ecf_whatCommmentObject($result[$i]->comment_ID, $compareComments);
                        if ($objectIndex > -1) {
                          $comments[$objectIndex]->location = $result[$i]->location;
                          $comments[$objectIndex]->lon = $result[$i]->lon;
                          $comments[$objectIndex]->lat = $result[$i]->lat;
                        }
                        unset($compareComments[$objectIndex]);
                }
        }
        return $comments;
}
function cgm_ecf_whatCommmentObject($comment_id, $comments) {
        if (!empty($comments)) {
        foreach($comments as $objectIndex => $comment) {
                if ($comment->comment_ID == $comment_id) {
                        return $objectIndex;
                }
        }
        }
        return -1;
}

?>