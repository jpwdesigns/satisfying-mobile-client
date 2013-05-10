<?php

if (!(isset($_COOKIE['hide_updates'])) || (empty($_COOKIE['hide_updates']))) {
    $item = $satisfying->getUpdateMessage();
    if ($item) {
       $item = $item->data[0];
$satisfying->debug($item); //die();
         //foreach($update as $item) {
    ?>
    <div class="ui-bar ui-bar-e" id="update_message">
        <h3>Latest News</h3>
        <div class="dismiss_update_message">
            <a href="#" class="dismiss_update_message" data-role="button" data-icon="delete" data-iconpos="notext">
                Dismiss
            </a>
        </div>
        <p><?php echo $satisfying->format_truncate($item->subject, 120); ?><?php //echo $satisfying->format_truncate($item->content, 120); ?></p>
    </div>
    <?php }}//} ?>