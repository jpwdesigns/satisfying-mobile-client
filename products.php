<?php 
require_once('init/inc.init.php');

?>
<!DOCTYPE html> 
<html> 
<head>  
<?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.head.php');?>
</head> 
<body> 
   <div data-role="page" class="type-interior" id="products" data-url="<?php echo $satisfying->getCurPage(); ?>">   
   <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.header.php'); ?>
   <div data-role="content">
       <div class="content-primary">
           <h2>Products</h2>
           <ul data-role="listview" data-theme="c" data-dividertheme="b" data-counttheme="b">  
                <?php  
                    foreach($products_feed as $item) { ?>  

                    <li>  

                         <a href="<?php echo 'topics?product=' . urlencode($item->id); ?>">
                             <img src="<?php echo str_replace('small','large',$item->image);?>" alt="<?php echo $item->name ;?>" width="80" height="80" />  
                             <h1><?php $name_pieces = explode(' ',$item->name); echo str_replace($name_pieces[1],$name_pieces[1].'<br />',$item->name); ?></h1>
                         </a> 

                   </li>  

                <?php  } ?>  
            </ul>
       </div>
       <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.sidebar.php');?>
   </div>  
   <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.footer.php');?>  
 </div>
<?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.ga.foot.php'); ?>
</body>
</html>
<?php //echo $path; ?>