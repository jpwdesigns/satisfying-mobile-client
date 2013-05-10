<div class="content-secondary">
      <form action="<?php echo $root_dir; ?>topics" method="get" id="searchForm">
       <ul data-role="listview" data-theme="b" data-dividertheme="d">
           <li class="hide_welcome"><select name="product" id="choose-product"  data-theme="b">
                   <option value="">Choose your Product</option>
                   <?php foreach($products_feed as $item) { ?> 
                    <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                   <?php } ?>
                </select>
           </li>
           <li class="hide_welcome"><input type="search" data-theme="e" name="q" id="search" value="" placeholder="Type your question here..."/></li>
           <li data-role="list-divider"  class="hide_welcome">
               Or browse below...
           </li> 
           <li data-icon="qo-huh"  class="ui-btn-icon-left">
                <a href="<?php echo $root_dir; ?>topics?people=quickoffice_faq"> 
                    FAQ's
                 </a>     
           </li>
           <li data-icon="qo-fire"  class="ui-btn-icon-left">
                <a href="<?php echo $root_dir; ?>topics?sort=most_replies"> 
                     Hot Topics
                 </a>      
           </li>
           <li data-icon="qo-light-bulb"  class="ui-btn-icon-left">
                <a href="<?php echo $root_dir; ?>submit_idea"> 
                    Submit An Idea
                 </a>    
           </li>
           <li data-icon="qo-all" class="ui-btn-icon-left">
                <a href="<?php echo $root_dir; ?>products" > 
                     Browse All Products
                 </a>   
           </li>
        </ul>
      </form>
</div>  