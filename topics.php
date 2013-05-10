<?php 
require_once('init/inc.init.php'); 
$product_feed = $satisfying->getProduct();
$data = $satisfying->getTopics();
$feed = $data->data;

// variables used for pagination
$feed_total = $data->total;
$rand = mt_rand(4,10);

if (isset($_GET['sort']) && $_GET['sort']=='most_replies') { $heading = "Hot Topics"; }
else if (isset($_GET['people']) && $_GET['people']=='quickoffice_faq') { $heading = "FAQ's"; }
else { $heading = "Topics"; }

// build unique pageid
$pageid = '';
foreach ($satisfying->params as $key => $val) {
    $pageid .= '_'.$key.'-'.$val;
}
$satisfying->alertPageId = 'topics'.$pageid;
?>
<!DOCTYPE html> 
<html> 
<head>  
<?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.head.php'); ?>
</head> 
<body> 
<div data-role="page" id="topics<? echo $pageid; ?>" class="type-interior" data-url="<?php echo $satisfying->getCurPage(); ?>">  
   <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.header.php'); ?> 
   <div data-role="content">  
       <div class="content-primary">
           <h2><?php if($product_feed) echo $product_feed->name. ' '; ?><?php echo $heading; ?></h2>
           <?php if ($feed) { ?>
            <ul class="topics-list" id="topics-list-<?php echo $rand; ?>" data-role="listview" data-theme="c" data-dividertheme="d" data-counttheme="b" data-filter="true" data-inset="true">  
                <?php  
                    foreach($feed as $item) { ?>    
                    <li data-filtertext="<?php echo strip_tags($item->content); ?>">
                        <a href="<?php echo 'detail?topic='.urlencode($item->id); ?>"> 
                             <h3><?php echo $satisfying->format_truncate($item->subject, 30); ?></h3>
                             <p>Last activity: <?php echo  $satisfying->format_date($item->last_active_at); ?></p>
                             <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.topic-labels.php'); ?>
                         </a>
                      <span class="ui-li-count"><?php echo $item->reply_count; ?></span> 

                   </li>  
                <?php  } ?>  
            </ul>
           <?php 
                if ($feed_total > count($feed)) {
                    echo '<a class="loadmore-topics" id="loadmore-topics-'.$rand.'" href="javascript:;" rel="'.http_build_query($satisfying->params).'" alt="1">Load more topics</a>';
                }
           } ?>
           <div data-content-theme="d">
               <h2>Don't see what you're looking for?</h2>
               <h4>Ask the community your question:</h4>
               <div style="color:red"><?php echo $errors_html ?></div>
               <form action="<?php echo $satisfying->getCurPage(); ?>" method="post" id ="new_topic" data-ajax="false"> 
                   <fieldset data-role="controlgroup" data-type="horizontal"  data-mini="true">
                        <input type="radio" name="topic[style]" id="radio-view-a" value="question"  />
	         	<label for="radio-view-a">Question</label>
	         	<input type="radio" name="topic[style]" id="radio-view-b" value="problem"  />
	         	<label for="radio-view-b">Problem</label>
	         	<input type="radio" name="topic[style]" id="radio-view-c" value="praise"  />
	         	<label for="radio-view-c">Praise</label>
	         	<input type="radio" name="topic[style]" id="radio-view-d" value="idea"  />
	         	<label for="radio-view-d">Idea</label>                        
                   </fieldset>
                   <label for="subject">Give your topic a great title <span style="color:red;font-wieght:bold;font-size:22px;">*</span></label>
                   <input type="text" name="topic[subject]" id="subject" value="<?php if ((isset($postData))&&(!empty($postData['topic']['subject']))) { echo htmlspecialchars($satisfying->format_reply($postData['topic']['subject']), ENT_QUOTES, 'UTF-8');} ?>" data-theme="d" />
                   <label for="additional_detail">Add some details (one or two paragraphs works best) <span style="color:red;font-wieght:bold;font-size:22px;">*</span></label>
                   <textarea cols="40" rows="8" name="topic[additional_detail]" id="additional_detail" placeholder="I need help with..." data-theme="d"><?php if ((isset($postData))&&(!empty($postData['topic']['additional_detail']))) { echo htmlspecialchars($satisfying->format_reply($postData['topic']['additional_detail']), ENT_QUOTES, 'UTF-8');} ?></textarea>
                   <input name="topic[products]" type="hidden" value="<?php if($product_feed) echo $product_feed->name; ?>" />
                   <input type="hidden" value="<?php echo urlencode($satisfying->feed_root.'/companies/'.$satisfying->company.'/topics.json'); ?>" name="url">
                   <input name="action" type="hidden" value="1" />
                   <button type="submit" class="submit" data-theme="b">Submit</button>
               </form>
           </div>
       </div>
       <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.sidebar.php');?>
   </div>  
   <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.footer.php');?>  
 </div>
<?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.ga.foot.php'); ?>
</body>
</html>