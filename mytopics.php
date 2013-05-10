<?php 
require_once('init/inc.init.php'); 
$feed = $satisfying->getFollowd();
?>
<!DOCTYPE html> 
<html> 
<head>  
<?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.head.php');?>
    <script>
    $(function(){
        $('.unfollow').each(function() {
            $(this).click(function(e) {
                e.preventDefault();
                var tid = $(this).attr('id');
                $('#unfollowlink').attr('rev', tid);
            });
        });

        $('#unfollowlink').click(function(e) {
            e.preventDefault();
            $.mobile.showPageLoadingMsg();
            var tid = $(this).attr('rev');
            var ufrl = '<?php echo $root_dir; ?>ajax/unfollow?topic='+tid;

            $.ajax({
                  url: ufrl,
                  success: function() {
                      $('#li'+tid).remove();
                      $('#topics-list li').first().addClass('ui-corner-top');
                      $('#topics-list li').last().addClass('ui-corner-bottom');
                      $('#unfollow').dialog("close");
                  },
                  statusCode: {
                    400: function() {
                        $.mobile.hidePageLoadingMsg(); 
                    },
                     401: function() {
                        // this is where we need to redirect to login
                        window.location.replace("/mytopics?login=true");     
                        $.mobile.hidePageLoadingMsg(); 
                    },
                    404: function() {
                      $.mobile.hidePageLoadingMsg();
                      alert("Page Not Found");
                    },
                     500: function() {
                      $.mobile.hidePageLoadingMsg();
                      alert("Internal Server Error");
                    },
                     503: function() {
                      $.mobile.hidePageLoadingMsg();
                      alert("Service Unavailable");
                    }
                  }
            });
        });
    }); 
    </script>
</head> 
<body> 
<div data-role="page" id="mytopics" class="type-interior" data-url="<?php echo $satisfying->getCurPage(); ?>">  
   <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.header.php'); ?> 
   <div data-role="content">  
       <div class="content-primary">
           <h2>My Followed Topics</h2>
           <?php if ($feed) { ?>
            <ul data-role="listview" data-split-icon="qo-heart"  data-split-theme="d" data-counttheme="b" data-filter="true" data-inset="true" id="topics-list">  
                <?php  
                    foreach($feed as $item) { ?>    
                    <li data-filtertext="<?php echo strip_tags($item->content); ?>" id="li<?php echo $item->id; ?>">
                        <a href="<?php echo 'detail?topic='.urlencode($item->id); ?>"> 
                             <h3><?php echo $satisfying->format_truncate($item->subject, 30); ?></h3>
                             <p>Last activity: <?php echo  $satisfying->format_date($item->last_active_at); ?></p>
                             <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.topic-labels.php');?>
                         </a>
                      <span class="ui-li-count"><?php echo $item->reply_count; ?></span> 
                      <a href="#unfollow" id="<?php echo $item->id; ?>" class="unfollow" data-rel="dialog" data-iconshadow="false" data-transition="slideup">Unfollow</a>
                   </li>  
                <?php  } ?>  
            </ul>
           <?php } else { ?>
           <p>You are not currently following any topics. Once you follow a topic, me too a topic, reply to a topic, or create a topic it will appear in a list here.</p>
           <?php  }?>
       </div>
       <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.sidebar.php');?>
   </div>  
   <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.footer.php');?>  
 </div>
    <div data-role="page" id="unfollow">
        <div data-role="header" data-theme="e">
            <h1>Unfollow?</h1>
        </div>   
        <div data-role="content" data-theme="d">
            <h4>Are you sure you want to unfollow this topic?</h4>
            <p>If so, you will no longer receive email updates for new replies or comments to this topic.</p>
            <a href="mytopics" data-role="button" data-theme="b" data-ajax="false" id="unfollowlink">Unfollow</a>
            <a href="mytopics" data-role="button" data-rel="back">Cancel</a>
        </div>      
    </div>
<?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.ga.foot.php'); ?>
</body>
</html>