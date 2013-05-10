<?php 
require_once('/init/inc.init.php'); 
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        
        $data = $satisfying->getTopics();
        $feed = $data->data;
        $feed_total = $data->total;

        if ($feed) {
            foreach($feed as $item) { ?>
                <li data-filtertext="<?php echo strip_tags($item->content); ?>">
                    <a href="<?php echo 'detail?topic='.urlencode($item->id); ?>"> 
                         <h3><?php echo $satisfying->format_truncate($item->subject, 30); ?></h3>
                         <p>Last activity: <?php echo  $satisfying->format_date($item->last_active_at); ?></p>
                         <?php include('../includes/inc.topic-labels.php');?>
                     </a>
                  <span class="ui-li-count"><?php echo $item->reply_count; ?></span> 
                </li>  
<?php       }
        } 
} else {
    header('HTTP/1.1 405 Method Not Allowed');
}

?>