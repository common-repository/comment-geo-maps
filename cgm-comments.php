<?php // Do not delete these lines
  if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
    die ('Please do not load this page directly. Thanks!');

  if (!empty($post->post_password)) { // if there's a password
    if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
      ?>
      <p class="no-comments">This post is password protected. Enter the password to view comments.</p>
      <?php
      return;
    }
  }
?>

<style type="text/css">
<?php if (is_page()): ?>
.cgm_fullpage {
height: 600px; /* This is where we want to set the height. Should be viewport minus height of site header. */
}

.cgm_fullpage ul#site-menu {
margin:0 0 15px;
padding:13px 26px 33px 24px;
}

.cgm_fullpage #content {
width: 287px;
height: 100%;
padding: 0 20px;
overflow: auto;
position: relative;
margin-top: -35px;
background: #fff;
}

.cgm_fullpage .post {
margin: 20px 0 3em 0;
}

.cgm_fullpage .last-comment-author {
display: none;
}

.cgm_fullpage #content li.comment {
border-bottom: none;
}

.cgm_fullpage li.comment .comment-author,
.cgm_fullpage li.comment .comment-content {
width: 200px;
}

.cgm_fullpage li.comment .comment-author {
background: #F7F7F7;
border-top: 1px solid #E9ECED;
border-bottom: 1px solid #E9ECED;
font-size: 0.95em;
padding: 5px 4px;
}

.cgm_fullpage li.comment .comment-timestamp {
float: right;
text-align: right;
padding: 3px 0 0 0;
}

.cgm_fullpage #comment-form #comment {
width: 95%
}

.cgm_fullpage #commentsmap {
float: right;
width: 623px;
height: 100%;
margin-top: -35px !important;
}

.cgm_fullpage #cgm_location {
margin: 0.3em 0;
}

.cgm_fullpage #comment-form .standalone {
padding: 0.7em 0 0;
}

.cgm_fullpage #comment-form-button {
color:#01102D;
border:2px solid #E6E4E1;
padding: 0.5em;
margin: 0.5em;
text-decoration: none;
font-size: 1.2em;
}

.cgm_fullpage #comment-button-wrapper {
text-align: center;
}

.cgm_fullpage #num-comments {
text-align: right;
margin-top: 2em;
}

.cgm_fullpage .leave-comment {
float:right;
}

.cgm_fullpage form div.selfclear label {
float:left;
margin:0 0 0 -5em;
padding:0 0.5em 0 0;
text-align:right;
}

.cgm_fullpage form div.selfclear {
clear:both;
margin:0.8em 0;
padding:0 0 0 5em;
}

.cgm_fullpage #math {
clear: both;
margin: 0.8em 0;
}

.cgm_fullpage #math label {
float:left;
padding:0 0.5em 0 0;
text-align:right;
}

.cgm_fullpage #wrapper-ftm {
clear: both;
margin: 0 0 0 -5em;
}

<?php else: ?>

#commentsmap {
width:100%;
height:250px;
background: #fff;
}

a.show-comment-link {
background: transparent url(<?php global $cgm_root; echo $cgm_root?>/img/marker_small.png) no-repeat left;
padding: 10px 0 10px 20px;
float:left;
margin:5px 0 0 15px;
text-decoration: none;
}

#comment-list-footer .leave-comment a {
display:block;
padding-top:3px;
}

<?php endif;?>

#wrapper-ftm {
padding: 5px;
}

.olPopup h4 {
  font-size: 1em;
  margin: 0;
}

.olPopup .thumbnail {
  float: left;
  margin: 0 3px 3px 0;
}

.selfclear {
  clear: both;
}

</style>

<script type="text/javascript">
  function CGMSetupToggler() {
<?php if(is_page()):?>
     function hrefToID(href) {
       var start = href.indexOf('#');
       var length = href.length - start;
       return href.substr(start + 1, length);
     }

     function sectionhider(except) {
       $j(".js-toggle").hide();
       $j("#" + except).show();
     }

     $j(".js-ttoggler").click(function () {
         sectionhider(hrefToID(this.href));
         return false;
       });
     sectionhider('comment-list-toggle');
<?php endif;?>
  }

function validate_required(field,alerttxt)
{
with (field)
  {
  if (value==null||value=="")
    {
    alert(alerttxt);return false;
    }
  else
    {
    return true;
    }
  }
}

function validate_form(thisform)
{
with (thisform)
  {
  if (validate_required(email,"Please fill in the Mail field.")==false)
  {email.focus();return false;}
  if (validate_required(author,"Please fill in the Name field")==false)
  {author.focus();return false;}
  if (validate_required(cgm_location,"Please fill in the Location field")==false)
  {cgm_location.focus();return false;}
  if ($j('#mcspvalue').length && validate_required(mcspvalue,"Please fill in the spam protection field")==false)
  {mcspvalue.focus();return false;}
  if (validate_required(comment,"Please type a comment")==false)
  {comment.focus();return false;}
  }
}


fullheight_px = $j(window).height()-165;
fullheight_class = ".cgm_fullpage";

</script>


<?php
if (is_page()) {
    global $nosidebar;
    $nosidebar = true;
}
?>

<?php if ($comments || comments_open()) : ?>
<div id="comment-list-toggle" class="js-toggle">

<?php if (!is_page()):?>
<div id="comment-list-header" class="selfclear">

        <h3 id="comments"><?php comments_number('No Comments', 'One Comment', '% Comments' );?></h3>
        <?php the_last_commenter('', '<span class="last-comment-author">Last comment by ', '</span>'); ?>

  <?php if (comments_open()) : ?>
        <span class="leave-comment">
           <a class="js-ttoggler" href="#comment-list-footer" title="<?php _e("Leave a comment"); ?>">Leave a comment &raquo;</a>
        </span>
        <?php endif; ?>

</div>
<!-- /#comment-list-header -->

<?php else:?>
  <?php if (comments_open()) : ?>
        <div id="comment-button-wrapper">
                <a id="comment-form-button" style="background:#FCFCFA url(/wp-content/themes/woonerf/img/submit-bg.png) repeat-x scroll left bottom" class="js-ttoggler" href="#comment-form-toggle" title="<?php _e("Post your comment"); ?>">Post your comment</a>
        </div>
  <?php endif; ?>
  <p id="num-comments"><?php comments_number('No Comments', 'One Comment', '% Comments' );?></p>
<?php endif?>

<?php endif; ?>

<?php if (!is_page() and (comments_open() or $comments)) cgm_insert_map(); ?>
<?php cgm_insert_map_markers($comments); ?>

<?php if ($comments) : ?>
  <?php
  /* get the author email for this article and assign to a variable to prevent multiple evaluations */
  $author_url = get_the_author_url();
  ?>
  <ol class="comment-list">
  <?php foreach ($comments as $key => $comment) : ?>
  <?php
    /* Assigns 'author' as a class if the poster's url matches the author's url.
      [FIXME] This seems naive, as users could mischievously match a known url to appear to be the author.
    */
    $authorcommentclass = ($comment->comment_author_url == $author_url) ? ' authorcomment' : '';
    /* Changes every other comment to a different class */
    $oddcommentclass = ( empty( $oddcommentclass ) ) ? ' odd' : '';
  ?>
    <li <?php if (!is_page()) echo 'id="comment-' . $comment->comment_ID . '"'; ?> class="comment selfclear<?php echo $authorcommentclass; ?><?php echo $oddcommentclass; ?>">
      <?php global $Opencore_remote_url ?>


        <div class="credit">
                    <?php if (substr($comment->comment_author_url,0,strlen($Opencore_remote_url) + 7) == $Opencore_remote_url . 'people/') { ?>
                   <a href="<?php echo $comment->comment_author_url; ?>"><img class="thumbnail" src="<?php echo $comment->comment_author_url . '/portrait_square_fifty_thumb'; ?>" alt="Post Thumbnail" /></a>
                <?php }
                      else { ?>
 <a href="<?php echo $comment->comment_author_url; ?>"><img class="thumbnail" src="<?php echo get_bloginfo('template_url')?>/img/commenter_icon_anon_50.png" alt="Post Thumbnail" /></a>

                <?php } ?>&nbsp;
        </div>

<?php if (is_page()):?>
      <div class="comment-author">
        <span class="comment-timestamp" title="<?php comment_date('F j, Y') ?> at <?php comment_time(); // [TODO] are all times EST? If so, do we need to note that, now that we have streetsblogs in multiple timezones? ?>"><?php comment_date('m/d/y') ?>
        <?php if($comment->lat && $comment->lon):?>
          <a id="show-comment-link--<?php comment_ID() ?>" class="show-comment-link" href="#commentsmap" title="Show on Map">Map&raquo;</a>
        <?php endif;?>
        </span>
        <a href="<?php the_permalink(); ?>#comment-<?php comment_ID() ?>" class="comment-number">#<?php echo ($key+1); ?></a>
        <span><?php comment_author_link() ?></span>
      </div>
      <div class="comment-content selfclear">
        <?php do_action('display_crown', $comment->comment_ID); ?>
        <?php comment_text() ?>
        <?php if ($comment->comment_approved == '0') : ?>
        <p><em>Your comment is awaiting moderation.</em></p>
        <?php endif; ?>
      </div><!-- /.comment-content -->
      <div><span class="comment-actions"><?php edit_comment_link('EDIT','',''); ?><?php do_action("crown_link", $comment->comment_ID); ?><?php do_action("wots_link"); ?></span></div>
<?php else:?>
      <h4 class="comment-author"><?php comment_author_link() ?></h4>
           <?php if($comment->lat && $comment->lon):?>
              <a id="show-comment-link--<?php comment_ID() ?>" class="show-comment-link" href="#commentsmap" title="Show on Map">Map^</a>
           <?php endif;?>

      <div class="comment-content">

        <?php do_action('display_crown', $comment->comment_ID); ?>
        <?php comment_text() ?>
        <?php if ($comment->comment_approved == '0') : ?>
        <p><em>Your comment is awaiting moderation.</em></p>
        <?php endif; ?>
      </div><!-- /.comment-content -->

      <div class="comment-footer selfclear">

         <span class="comment-timestamp"><?php comment_date('F j, Y') ?> at <?php comment_time(); // [TODO] are all times EST? If so, do we need to note that, now that we have streetsblogs in multiple timezones? ?></span>
         <span class="permalink"><a href="<?php the_permalink(); ?>#comment-<?php comment_ID() ?>" title="Comment Permalink" rel="bookmark">Link</a></span>
         <span class="comment-actions"><?php edit_comment_link('EDIT','',''); ?><?php do_action("crown_link", $comment->comment_ID); ?><?php do_action("wots_link"); ?></span>
         <span class="comment-footer-right">
           <a href="<?php the_permalink(); ?>#comment-<?php comment_ID() ?>" class="comment-number"># <?php echo ($key+1); ?></a>
         </span>
      </div><!-- /.comment-footer -->
<?php endif;?>

    </li><!-- /.comment -->
  <?php endforeach; /* end for each comment */ ?>
  </ol><!-- /.comment-list -->


<?php endif; /* end if ($comments) conditional */ ?>


<?php if (is_page()):?>
   </div>

       <?php if (!comments_open() and $comments):?>
  <div id="comment-list-footer" class="selfclear">
  <span class="comments-closed">Comments are closed.</span>
  </div><!-- /#comment-list-footer -->
     <?php endif;?>

   <div id="comment-form-toggle" class="js-toggle">
   <span class="leave-comment">
     <a class="js-ttoggler" href="#comment-list-toggle" title="<?php _e("Comments List"); ?>">Comments list &raquo;</a>
   </span>
   <h3 id="comments">Leave a Comment</h3>

<?php else:?>

<?php if ('open' == $post->comment_status) : ?>
  <?php if (!is_page()):?>
      <div id="comment-form-toggle" class="js-toggle">
  <?php endif;?>
  <div id="comment-list-footer" class="selfclear">
  <span class="comments-open">Leave a Comment</span>
  </div><!-- /#comment-list-footer -->
<?php else : // comments are closed ?>
  <?php if ($comments) : ?>
   </div>
  <?php endif;?>

  <div id="comment-list-footer" class="selfclear">
  <span class="comments-closed">Comments are closed.</span>
  </div><!-- /#comment-list-footer -->
<?php endif; ?>

<?php endif?>



<?php if ('open' == $post->comment_status) : ?>

<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>">logged in</a> to post a comment.</p>
<?php else : ?>

<form onsubmit="return validate_form(this);" id="comment-form" action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post">
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
<?php #[FIXME] convert author_info and openplans_info to hyphen-delimited ids. This will also mean altering the OpenCore javascript functions ?>


<?php if ( $user_ID ) : ?>
<p>Logged in to <?php bloginfo('name'); ?> as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="Log out of this account">Logout &raquo;</a></p>
<?php if (!is_page()):?>
<div id="author_info">
<?php do_action("OC_OpenPlansAdminLink")?>
<input type="hidden" name="author" id="author" value="<?php echo $comment_author; ?>" />
<input type="hidden" name="email" id="email" value="<?php echo $comment_author_email; ?>" />
<input type="hidden" name="url" id="url" value="<?php echo $comment_author_url; ?>" />

      <div class="formrow selfclear">
        <label for="cgm_location">Location<br/>(e.g. "105 Lafayette St, 10013" or "Atlantic Ave and Smith St, Brooklyn")</label>
        <span id="cgm-error"></span>
        <input type="text" name="cgm_location" id="cgm_location" size="28" />
        <input type="submit" class="submit" name="find-address" id="find-address" value="find address"/>
        <div id="wrapper-ftm"><div id="finetunemap" style="width:100%; height:200px; display:none"></div></div>
        <input type="hidden" name="cgm-lat" id="cgm-lat" />
        <input type="hidden" name="cgm-lon" id="cgm-lon" />
      </div>
<?php else: //!is_page() ?>
<div>
<?php do_action("OC_OpenPlansAdminLink")?>
<input type="hidden" name="author" id="author" value="<?php echo $comment_author; ?>" />
<input type="hidden" name="email" id="email" value="<?php echo $comment_author_email; ?>" />
<input type="hidden" name="url" id="url" value="<?php echo $comment_author_url; ?>" />

      <div class="selfclear">
        <label for="cgm_location">Location</label>
        <input type="text" name="cgm_location" id="cgm_location" size="17" />
        <input type="submit" class="submit" name="find-address" id="find-address" value="find"/>
        <span id="cgm-error"></span>
        <div id="wrapper-ftm"><div id="finetunemap" style="width:100%; height:200px; display:none"></div></div>
        <input type="hidden" name="cgm-lat" id="cgm-lat" />
        <input type="hidden" name="cgm-lon" id="cgm-lon" />
      </div>

<?php endif; //!is_page() ?>

</div>
<div id="openplans_info">
</div>

<?php else : //$user_ID?>
<?php if (!is_page()):?>
<div id="author_info">
  <div class="formrow selfclear">
   <label for="author">Name <?php if ($req) echo "(<strong>required</strong>)"; ?><?php do_action("OC_OpenPlansLink")?></label>
    <input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="38" />
  </div><!-- /.formrow -->
  <div class="formrow selfclear">
     <label for="email">Mail (<strong><?php if ($req) echo "required, "; ?>not displayed</strong>)</label>
    <input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="38" />
  </div><!-- /.formrow -->

      <div class="formrow selfclear">
        <label for="cgm_location">Location<br/>(e.g. "105 Lafayette St, 10013" or "Atlantic Ave and Smith St, Brooklyn")</label>
        <span id="cgm-error"></span>
        <input type="text" name="cgm_location" id="cgm_location" size="28" />
        <input type="submit" class="submit" name="find-address" id="find-address" value="find address"/>
        <div id="wrapper-ftm"><div id="finetunemap" style="width:100%; height:200px; display:none"></div></div>
        <input type="hidden" name="cgm-lat" id="cgm-lat" />
        <input type="hidden" name="cgm-lon" id="cgm-lon" />
      </div>

  <div class="formrow selfclear">
    <label for="url">Your URL</label>
    <input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="38" />
  </div><!-- /.formrow -->

  <?php
  /****** Math Comment Spam Protection Plugin ******/
  if ( function_exists('math_comment_spam_protection') ) {
        $mcsp_info = math_comment_spam_protection();
  ?>
    <div class="formrow selfclear">
        <label for="mcspvalue">Spam protection: Sum of <?php echo $mcsp_info['operand1'] . ' + ' . $mcsp_info['operand2'] . ' ?' ?></label>
        <input type="text" name="mcspvalue" id="mcspvalue" value="" size="38" />
        <input type="hidden" name="mcspinfo" value="<?php echo $mcsp_info['result']; ?>" />
  </div>
  <?php } // if function_exists... ?>
</div><!-- /#author_info -->

<?php else: //!is_page() ?>
<div>
  <div class="selfclear">
   <p><?php do_action("OC_OpenPlansLink")?></p>
   <label for="author">Name</label>
   <input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="25"/>
  </div>
  <div class="selfclear">
     <label for="email">Mail</label>
    <input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="25" />
  </div>

      <div class="selfclear">
        <label for="cgm_location">Location</label>
        <input type="text" name="cgm_location" id="cgm_location" size="17" />
        <input type="submit" class="submit" name="find-address" id="find-address" value="find"/>
        <span id="cgm-error"></span>
        <div id="wrapper-ftm"><div id="finetunemap" style="width:100%; height:200px; display:none"></div></div>
        <input type="hidden" name="cgm-lat" id="cgm-lat" />
        <input type="hidden" name="cgm-lon" id="cgm-lon" />
      </div>

  <div class="selfclear">
    <label for="url">Your URL</label>
    <input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="25" />
  </div>

  <?php
  /****** Math Comment Spam Protection Plugin ******/
  if ( function_exists('math_comment_spam_protection') ) {
        $mcsp_info = math_comment_spam_protection();
  ?>
    <div id="math">
        <label for="mcspvalue">Spam protection: Sum of <?php echo $mcsp_info['operand1'] . ' + ' . $mcsp_info['operand2'] . ' ?' ?></label>
        <input type="text" name="mcspvalue" id="mcspvalue" value="" size="6" />
        <input type="hidden" name="mcspinfo" value="<?php echo $mcsp_info['result']; ?>" />
  </div>
  <?php } // if function_exists... ?>
</div><!-- /#author_info -->

<?php endif; //!is_page()?>


<div id="openplans_info">
</div>
<?php endif; ?>
<div>
  <label class="standalone" for="comment">Your Comment</label><?php /*<p><small><strong>XHTML:</strong> You can use these tags: <code><?php echo allowed_tags(); ?></code></small></p>*/ ?>
  <textarea id="comment" name="comment" class="oc-js-autosave" cols="60" rows="10"></textarea>
</div>
<input id="submit" name="submit" class="submit rightwise" type="submit" value="Post Your Comment" />
<?php do_action("OC_ProgressSpinner") ?>
<?php do_action('comment_form', $post->ID); ?>
</form>
</div></div>
<?php endif; // If registration required and not logged in ?>
<?php endif; // if you delete this the sky will fall on your head ?>