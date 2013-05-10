<?php
require_once('init/inc.init.php');
?>
<!DOCTYPE html> 
<html> 
    <head>  
        <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir . 'includes/inc.head.php'); ?>
    </head>
    <body> 
        <div data-role="page" class="type-interior" id="welcome" data-url="<?php echo $satisfying->getCurPage(); ?>">  
            <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir . 'includes/inc.header.php'); ?>
            <div data-role="content">
                <?php //include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.status_update.php'); ?> 
                <div class="content-primary">
                    <div class="hide_wide">
                        <img src="http://placehold.it/350x56&text=Your+Logo" style="margin:18px 0;"  />
                        <form action="topics" method="get" id="searchForm">
                            <?php if (!isset($_COOKIE['inapp']) && !isset($_GET['inapp'])) { ?>
                                <div style="clear:both;height:12px;"></div>
                                <select name="product" id="choose-product"  data-theme="b">
                                    <option value="">Choose your Product</option>
                                    <?php foreach ($products_feed as $item) { ?> 
                                        <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                                    <?php } ?>
                                </select>
                            <?php } ?>
                            <div style="clear:both;height:10px;"></div>
                            <input type="search" data-theme="e" name="q" id="search" value="" placeholder="Type your question here..."/>
                            <button type="submit" data-theme="b" value="">Submit</button>
                        </form>
                        <div style="clear:both;height:12px;"></div>
                        <ul data-role="listview" data-theme="d" data-dividertheme="d" data-inset="true">    
                            <li data-role="list-divider">
                                Or browse below...
                            </li> 
                            <li data-icon="qo-huh"  class="ui-btn-icon-left">
                                <a href="topics?people=quickoffice_faq"> 
                                    FAQ's
                                </a>     
                            </li>
                            <li data-icon="qo-fire"  class="ui-btn-icon-left">
                                <a href="topics?sort=most_replies"> 
                                    Hot Topics
                                </a>      
                            </li>
                            <li data-icon="star"  class="ui-btn-icon-left">
                                <a href="billing_question"> 
                                    Contact Support
                                </a>    
                            </li>
                            <li data-icon="qo-light-bulb"  class="ui-btn-icon-left">
                                <a href="submit_idea"> 
                                    Submit An Idea
                                </a>    
                            </li>
                            <?php if (!isset($_COOKIE['inapp']) && !isset($_GET['inapp'])) { ?>
                                <li data-icon="qo-all"  class="ui-btn-icon-left">
                                    <a href="products"> 
                                        Browse All Products
                                    </a>   
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="show_wide">
                        <h1>Welcome</h1>
                        <p>To get started, please type your question or concern in the field below.</p>
                        <form action="topics" method="get" id="searchForm" class="ui-body ui-body-b ui-corner-all">
                                <select name="product" id="choose-product"  data-theme="b">
                                    <option value="">Choose your Product</option>
                                    <?php foreach ($products_feed as $item) { ?> 
                                        <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                                    <?php } ?>
                                </select>
                            <div style="clear:both;height:10px;"></div>
                            <input type="search" data-theme="e" name="q" id="search" value="" placeholder="Type your question here..."/>
                            <button type="submit" data-theme="b" value="">Submit</button>
                        </form>
                    </div>
                </div>
                <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir . 'includes/inc.sidebar.php'); ?>
            </div>  
            <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir . 'includes/inc.footer.php'); ?>  
        </div>
        <?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir . 'includes/inc.ga.foot.php'); ?>
    </body>
</html>
