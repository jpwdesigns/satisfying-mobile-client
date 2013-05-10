<?php
require_once('init/inc.init.php');
?>
<!DOCTYPE html> 
<html> 
<head>  
<?php include('../includes/inc.head.php');?>
</head> 
<body> 
<div data-role="dialog" id="loginRequired">
    <div data-role="header" data-theme="d">
            <h1>Login Required</h1>
    </div>
    <div data-role="content" data-theme="c">
            <h1>Login Required</h1>
            <p>Press OK to continue to login or Cancel to cancel.</p>
            <a href="<?php echo $satisfying->cleanUri($_GET['redir'].'&login=true'); ?>" data-role="button" data-ajax="false" data-theme="b">OK</a>       
            <a href="<?php echo $satisfying->cleanUri($_GET['redir']); ?>" data-role="button" data-theme="c">Cancel</a>    
    </div>
</div>
</body>
</html>
