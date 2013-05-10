<meta charset="utf-8">  
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" /> 
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, minimum-scale=1, maximum-scale=1">
<title>Quickoffice Support</title> 
<link rel="stylesheet" href="/css/jquery.mobile-1.1.0/jquery.mobile-1.1.0.min.css" />
<link rel="stylesheet" href="/css/common.min.css" />
<script src="/js/jquery-1.7.1.min.js"></script>
<?php if ($platform === "pc") { ?>
<script type='text/javascript'>
<!--
    $(document).bind('mobileinit',function(){
        $.mobile.selectmenu.prototype.options.nativeMenu = false;
        $.extend(  $.mobile , {
              defaultPageTransition: "none"
        }); 
    });
--> 
</script>
<?php } else { ?>
<script type='text/javascript'>
<!--
    $(document).bind('mobileinit',function(){
       $.extend(  $.mobile , {
              defaultPageTransition: "slide"
       });
    });
-->
</script>    
<?php } ?>
<script src="/js/jquery.mobile-1.1.0.min.js"></script>
<script src="/js/jquery.cookie.js"></script>
<script src="/js/common.min.js"></script>
<?php echo $satisfying->getAlert(); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . $root_dir .  'includes/inc.ga.head.php'); ?>
