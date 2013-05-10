<script type="text/javascript">
$('[data-role=page]').live('pageshow', function (event, ui) {
    try {
        _gaq.push(['_setAccount', '<?php echo GOOGLE_ANALYTICS_ACCOUNT_ID; ?>']);
        _gaq.push(['_setDomainName', '<?php echo GOOGLE_ANALYTICS_DOMAIN; ?>']);
        var url  = location.href
        _gaq.push(['_trackPageview', url]);
    } catch(err) { }
});
</script>