<?php 
require_once('init/inc.init.php'); 
$product_feed = $satisfying->getProduct();
$satisfying->alertPageId = 'submit_idea';
?>
<!DOCTYPE html> 
<html> 
<head>  
<?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.head.php');?>
</head> 
<body> 
<div data-role="page" id="submit_idea" class="type-interior" data-url="<?php echo $satisfying->getCurPage(); ?>">  
   <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.header.php'); ?> 
   <div data-role="content">  
       <div class="content-primary">
           <div data-content-theme="d">
               <h2>Got a great Idea for us?</h2>
               <h4>Use the form below to submit your idea to Quickoffice.</h4>
               <p>If you have several features to recommend, please create a separate submission for each request.</p>
               <form action="<?php echo $satisfying->getCurPage(); ?>" method="post" id ="new_topic" data-ajax="false"> 
                   <?php 
                   // if we don't know what product they are using then we will ask
                   if(!$product_feed) {?>
                   <select name="topic[products]">
                       <option value="">Choose your Product</option>
                       <?php foreach($products_feed as $item) { ?> 
                        <option value="<?php echo $item->name ?>"><?php echo $item->name ?></option>
                       <?php } ?>
                    </select>
                   <?php } else { ?>
                   <img src="<?php echo str_replace('small','medium',$product_feed->image); ?>" align="bottom" /><h2><?php echo $product_feed->name; ?></h2>
                   <input name="topic[products]" type="hidden" value="<?php if($product_feed) echo $product_feed->name; ?>" />
                   <?php } ?>
                   <div style="color:red"><?php echo $errors_html; ?></div>
                   <label for="subject">Give your idea a great title<span style="color:red;font-wieght:bold;font-size:22px;">*</span></label>
                   <input type="text" name="topic[subject]" id="subject" value="<?php if ((isset($postData))&&(!empty($postData['topic']['subject']))) { echo htmlspecialchars($postData['topic']['subject'], ENT_QUOTES, 'UTF-8');} ?>" data-theme="d" />
                   <label for="additional_detail">Add some details (one or two paragraphs works best)</label>
                   <textarea cols="40" rows="8" name="topic[additional_detail]" id="additional_detail" placeholder="It would be awesome if..." data-theme="d"><?php if ((isset($postData))&&(!empty($postData['topic']['additional_detail']))) { echo htmlspecialchars($postData['topic']['additional_detail'], ENT_QUOTES, 'UTF-8');} ?></textarea>
                   <input name="topic[style]" type="hidden" value="idea" />
                   <input name="action" type="hidden" value="1" />
                   <button type="submit" data-theme="b">Submit</button>
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