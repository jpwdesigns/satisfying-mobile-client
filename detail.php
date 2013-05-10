<?php 
require_once('init/inc.init.php');
$feed = $satisfying->getTopic(); 
if (!$feed) { header('HTTP/1.0 404 Not Found'); die(); }
$official_replies_data = $satisfying->getOfficialReplies();
$official_replies_feed = $official_replies_data->data;
$official_replies_total= $official_replies_data->total;
$replies_data = $satisfying->getReplies();
$replies_feed = $replies_data->data;
$replies_total= $replies_data->total;
$style = $feed->style;

function cmp($a, $b)
{
    if ($a->created_at == $b->created_at) {
        return 0;
    }
    return ($a->created_at < $b->created_at) ? -1 : 1;
}
usort($replies_feed,'cmp');
$satisfying->alertPageId = 'topic_'.$feed->id;
?>
<!DOCTYPE html> 
<html> 
<head>  
<?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.head.php'); ?>
</head> 
<body> 
   <div data-role="page" id="topic_<?php echo $feed->id; ?>" class="type-interior" data-url="<?php echo $satisfying->getCurPage(); ?>">  
   <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.header.php'); ?>
   <div data-role="content">  
       <div class="content-primary">
           <h1><?php echo $satisfying->format_reply($feed->subject); ?></h1>
           <?php if ((int)$feed->author->id === (int)$user_id) {
               echo '<div class="toolbox"><div><a href="" data-role="button" data-icon="qo-edit" data-iconpos="notext" class="editbutton" rel="topic_content_'.$feed->id.'"  data-name="topic[additional_detail]" data-url="'.$feed->url.'.json">Edit</a></div><div>Edit</div></div>';
           } ?>
            <p id="topic_content_<?php echo $feed->id; ?>"><?php echo $satisfying->format_reply($feed->content); ?></p>
            <div style="float:left;">
               <p><img src="<?php echo $feed->author->avatar_url_medium ;?>" alt="<?php echo $satisfying->format_strip_delux($feed->author->name); ?>"/><br><b><?php echo $satisfying->format_reply($feed->author->name) ;?></b>
                    <br />
                    <small><?php echo $satisfying->format_date($feed->created_at); ?></small>
                </p> 
            </div>
            <div style="float:right;padding-top:22px;">
                <?php  if ((int)$feed->me_too_count > 0) { ?>
                <b class="meTooCount_<?php echo $feed->id; ?>"><?php echo $feed->me_too_count; ?></b>
                <small class="meTooCount_<?php echo $feed->id; ?>"><?php $peeps = ((int)$feed->me_too_count > 1 ? 'people have' : 'person has'); echo $peeps; ?> this <?php echo $style; ?> <?php if (isset($feed->authenticated_user->me_too) && ((int)$feed->authenticated_user->me_too > 0)) { echo ' including you'; } ?></small>
                <div class="clear"></div>
                <?php }  ?>
                <?php if ((int)$feed->follower_count > 0) { ?>
                <b class="followerCount_<?php echo $feed->id; ?>"><?php echo $feed->follower_count; ?></b>
                <small class="followerCount_<?php echo $feed->id; ?>"><?php $peeps = ((int)$feed->follower_count > 1 ? 'people follow' : 'person follows'); echo $peeps; ?> this <?php echo $style; ?> <?php if (isset($feed->authenticated_user->following) && ((int)$feed->authenticated_user->following > 0)) { echo ' including you'; } ?></small>               
                <?php } ?>
                <div style="text-align:right;margin:.5em 5px;">                            
                    <form action="#" method="get">
                        <fieldset data-role="controlgroup" data-type="horizontal" class="ui-btn-icon-left ui-btn-inner qo-checkbox">
                            <input type="checkbox" name="metoobutton-<?php echo $feed->id; ?>" id="metoobutton-<?php echo $feed->id; ?>" rel="<?php echo $feed->id; ?>" <?php if (isset($feed->authenticated_user->me_too) && ((int)$feed->authenticated_user->me_too > 0)) { echo "checked disabled"; } ?> class="metoobutton" />
                            <label for="metoobutton-<?php echo $feed->id; ?>" data-theme="c" data-icon="qo-plus1">Me Too</label>
                            <input type="checkbox" name="followbutton-<?php echo $feed->id; ?>" id="followbutton-<?php echo $feed->id; ?>" rel="<?php echo $feed->id; ?>" class="followbutton" <?php $extra=''; if (isset($feed->authenticated_user->following) && ((int)$feed->authenticated_user->following > 0)) { echo "checked"; $extra='ing'; } ?> />
                            <label for="followbutton-<?php echo $feed->id; ?>" data-theme="c" data-icon="qo-heart">Follow<?php echo $extra ?></label>
                        </fieldset>
                    </form>
                </div>
            </div>
            <?php
            // Official response block
            ?>
            <?php if ((int)$official_replies_total > 0) { ?>
            <div class="ui-body ui-body-e" style="margin-bottom: 1em;">
                <h2>Official Reply</h2>
                <?php  foreach($official_replies_feed as $item) { ?>  
                    <h3><?php echo $satisfying->format_truncate($item->content,30); ?></h3>
                    <?php if ((int)$item->author->id === (int)$user_id) {
                        echo '<div class="toolbox"><div><a href="" data-role="button" data-icon="qo-edit" data-iconpos="notext" data-theme="c" class="editbutton" rel="reply_content_'.$item->id.'" data-name="reply[content]" data-url="'.$item->url.'.json">Edit</a></div><div>Edit</div></div>';
                    } ?>
                    <p id="reply_content_<?=$item->id; ?>"><?php echo $satisfying->format_reply($item->content); ?>
                    <div style="float:left">
                        <p><img src="<?php echo $item->author->avatar_url_medium; ?>" alt="<?php echo $satisfying->format_strip_delux($item->author->name); ?>"/><br><small><b><?php echo $satisfying->format_reply($item->author->name); ?></b></small>
                            <br /><small><?php echo $satisfying->format_date($item->created_at); ?></small>
                        </p>
                    </div>
                    <div style="clear:both;height:50px;"></div>
                <?php  }// end foreach ?> 
            </div>
            <?php  }//end Official reply_count if ?> 
            <?php if ((int)$feed->reply_count > 0) { ?>
            <div class="ui-body ui-body-b" style="margin-bottom: 1em;">
                <h2>Replies</h2>
                <?php  if ($replies_feed) {
                    foreach($replies_feed as $item) { ?>  
                    <div data-role="collapsible" data-collapsed="false" id="<?php echo (int)$item->id; ?>" data-theme="b">                    
                         <h3><?php echo $satisfying->format_truncate($item->content,30); ?></h3>
                         <?php if ((int)$item->author->id === (int)$user_id) {
                               echo '<div class="toolbox"><div><a href="" data-role="button" data-icon="qo-edit" data-iconpos="notext" data-theme="c" class="editbutton" rel="reply_content_'.$item->id.'" data-name="reply[content]" data-url="'.$item->url.'.json">Edit</a></div><div>Edit</div></div>';
                           } ?>
                         <p id="reply_content_<?php echo $item->id; ?>"><?php echo $satisfying->format_reply($item->content); ?>
                         <div style="float:left">
                             <p><img src="<?php echo $item->author->avatar_url_medium; ?>" alt="<?php echo $satisfying->format_strip_delux($item->author->name); ?>"/><br><small><b><?php echo $satisfying->format_reply($item->author->name); ?></b></small>
                                 <br /><small><?php echo $satisfying->format_date($item->created_at); ?></small>
                             </p>
                         </div>
                         <?php // do not show star button for your own replies
                         if ($item->author->id !== $user_id) {
                         ?>
                         <div style="float:right;padding-top:60px;">
                            <div style="text-align:right">
                                <form action="#" method="get">
                                    <fieldset data-theme="c" data-role="controlgroup" data-type="horizontal" class="ui-btn-icon-left ui-btn-inner qo-checkbox">
                                        <input data-theme="c" type="checkbox" name="starbutton-<?php echo $item->id; ?>" id="starbutton-<?php echo $item->id; ?>" rel="<?php echo $item->id; ?>" class="starbutton" <?php $extra=''; if (isset($item->authenticated_user->starred) && ((int)$item->authenticated_user->starred > 0)) { echo "checked"; } ?> />
                                        <label for="starbutton-<?php echo $item->id; ?>" data-theme="c" data-icon="qo-star">Good Answer</label>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                        <?php } ?>
                         <div style="clear:both"></div>
                             <?php if (isset($item->comment_count) && (int)$item->comment_count > 0) {
                                 //get comments on comment
                                    $comments_data = $satisfying->getComments((int)$item->id);
                                    $comments_feed = $comments_data->data;
                                    usort($comments_feed,'cmp');
                                    $comments_total= $comments_data->total;
                                    
                                    if ($comments_feed) {
                                    foreach($comments_feed as $itemz) { ?>
                                        <div data-role="collapsible" data-theme="e" data-content-theme="c">
                                            <h3><?php echo $satisfying->format_truncate($itemz->content,100); ?></h3>
                                            <?php if ((int)$itemz->author->id === (int)$user_id) {
                                               echo '<div class="toolbox"><div><a href="" data-role="button" data-icon="qo-edit" data-iconpos="notext" data-theme="c" class="editbutton" rel="comment_content_'.$itemz->id.'" data-name="comment[content]" data-url="'.$itemz->url.'.json">Edit</a></div><div>Edit</div></div>';
                                           } ?>
                                            <p id="comment_content_<?php echo $itemz->id; ?>"><?php echo $satisfying->format_reply($itemz->content); ?>
                                            <p><img src="<?php echo $itemz->author->avatar_url_medium; ?>" alt="<?php echo $satisfying->format_strip_delux($itemz->author->name); ?>"/><br><small><b><?php echo $satisfying->format_reply($itemz->author->name); ?></b></small>
                                                <br /><small><?php echo $satisfying->format_date($itemz->created_at); ?></small>
                                        </div>
                                    <?php } // end foreach comment 
                                    } // end if comments_feed 
                                 } // end if comment_count ?>
                             <div data-role="collapsible" data-theme="d" data-content-theme="d">
                                 <h3>Comment</h3>
                                 <form action="<?php echo $satisfying->getCurPage(); ?>" method="post" id ="reply_<?php echo $feed->id; ?>" data-ajax="false" class="comment"> 
                                     <label for="textarea" class="ui-hidden-accessible">Textarea:</label>
                                     <textarea cols="40" rows="8" name="comment[content]" id="textarea" placeholder="Reply to <?php echo $satisfying->format_strip_delux($item->author->name); ?>" data-theme="d"></textarea>
                                     <input name="action" type="hidden" value="1">
                                     <input name="comment[parent_id]" type="hidden" value="<?php echo (int)$item->id ;?>">
                                     <input name="url" type="hidden" value="<?php echo urlencode($item->url); ?>">
                                     <p>
                                         <button class="submit" type="submit" data-theme="b">Submit</button>
                                     </p>
                                     <div style="clear:both;height:50px;"></div>
                                 </form>
                             </div>               
                    </div>
                <?php  }// end foreach ?> 
              <?php  }// end if ?>
            </div>
            <?php  }//end reply_count if ?> 
            <?php 
            // current page
            $p = ((isset($satisfying->params['page']) && $satisfying->params['page'] >1) ? $satisfying->params['page'] : 1);
            // last page
            $lp = ceil($replies_total / 30);
                if (($replies_total > 30) && ($p > 1)) {
                    echo '&nbsp;<a href="'.$satisfying->getPrevPage().'" data-ajax="true"><< Previous Page </a>&nbsp;';
                }
                if (($replies_total > 30) && ($p < $lp)) {
                    echo '&nbsp;<a href="'.$satisfying->getNextPage().'" data-ajax="true"> Next Page >></a>&nbsp;';
                }
           ?>
            <div data-content-theme="d" style="clear:both">
                    <h3>Post a Reply</h3><div style="color:red"><?php echo $errors_html_reply; ?></div>
                    <form action="<?php echo $satisfying->getCurPage(); ?>" method="post" id ="reply_<?php echo $feed->id; ?>" data-ajax="false"> 
                            <label for="textarea" class="ui-hidden-accessible">Textarea:</label>
                            <textarea cols="40" rows="8" name="reply[content]" id="reply" placeholder="Reply to This Topic" data-theme="d"><?php if ((isset($postData))&&(!empty($postData['reply']['content']))) { echo htmlspecialchars($satisfying->format_reply($postData['reply']['content']), ENT_QUOTES, 'UTF-8');} ?></textarea>
                            <input name="action" type="hidden" value="1">
                            <input name="url" type="hidden" value="<?php echo urlencode($feed->url); ?>">
                            <button type="submit" class="submit" data-theme="b">Submit</button>
                            <div style="clear:both;height:50px;"></div>
                    </form>
            </div>
       </div>
       <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.sidebar.php'); ?>
   </div>  
   <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.footer.php'); ?>  
 </div>
<?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.ga.foot.php'); ?>
</body>
</html>
