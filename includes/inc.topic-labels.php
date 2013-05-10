<div class="label label-<?=$item->style?>"><div><span><?=ucfirst($item->style)?></span></div></div>
 <?php 
 $style_status_array = array(
     'question'     =>  array (
             'none'     => false,
             'rejected' => "Doesn't need Answer",
             'pending'  => 'Acknowledged',
             'active'   => 'In Progress',
             'complete' => 'Answered'
     ),
     'problem'      =>  array (
             'none'     => false,
             'rejected' => 'Not a Problem',
             'pending'  => 'Acknowledged',
             'active'   => 'In Progress',
             'complete' => 'Solved'
     ),
     'praise'       =>  array (
             'none'     => false,
             'rejected' => false,
             'pending'  => false,
             'active'   => false,
             'complete' => false,
     ),
     'update'      =>  array (
             'none'     => false,
             'rejected' => false,
             'pending'  => false,
             'active'   => false,
             'complete' => false,
     ),
     'idea'         =>  array (
             'none'     => false,
             'rejected' => 'Not Planned',
             'pending'  => 'Under Consideration',
             'active'   => 'Planned',
             'complete' => 'Implemented'
     )
 );
 $style     = (string)$item->style;
 $status    = ((string)$item->status == null ? 'none' : (string)$item->status);
 if (is_string($style_status_array[$style][$status])) { ?>
    <div class="label label-<?=$status?>"><div><span><?=$style_status_array[$style][$status]?></span></div></div>
 <?php } ?>