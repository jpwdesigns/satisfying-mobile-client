    <footer data-role="footer" class="ui-bar">
        <div style="font-weight:normal; font-size:12px;">
            <p>Copyright <?php echo date('Y'); ?> Quickoffice.com <a href="https://www.google.com/intl/en/policies/privacy/">Privacy</a>
            <?php if ($satisfying->debug===true) { ?>
                <a href="<?php echo $root_dir; ?>clear_cookies" data-role="button" data-icon="gear" rel="external" id="clear_cookies" style="float:right;margin:3px;" >Clear Cookies</a> 
            <?php } ?>
            </p>
        </div>
    </footer>