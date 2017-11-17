<?php
$widget_id = 37;
$dsn = "mysql:host=127.0.0.1;dbname=test;charset=utf8";
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);
$pdo = new PDO($dsn, 'root', 'king', $opt);
//$items = $pdo->query('select `id`, `title` from `sort` order by `order` asc')->fetchAll(PDO::FETCH_KEY_PAIR);
$stmt = $pdo->prepare('select `id`, `title` from `sort` where `widget_id` = :widget_id order by `order` asc');
$stmt->bindParam(':widget_id', $widget_id, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sortable - Testing</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
<!--    <link rel="stylesheet" href="/resources/demos/style.css">-->
    <style>
        #sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
        #sortable li { margin: 10px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; }
        #sortable li span { position: absolute; margin-left: -1.3em; }
    </style>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
    <script>
        $(document).ready(function() {
//            $( "#sortable" ).sortable();
            $('#sortable').sortable({
                axis: 'y',
                update: function (event, ui) {
                    var serialized_data = $('#sortable').sortable('serialize');//console.log($('#sortable').sortable('serialize'));
//                    $("#info").load("/sort_ajax.php?"+serialized_data+"&widget_id=<?//=$widget_id; ?>//");

                    // POST to server using $.post or $.ajax
                    $.ajax({
                        data: serialized_data,
                        type: 'POST',
                        url: '/sort_ajax.php?widget_id=<?=$widget_id; ?>'
                    });
                }
            });
//            $( "#sortable" ).disableSelection();
        } );
    </script>
</head>
<body>

<ul id="sortable">
    <?php foreach($items as $id => $title): ?>
    <li class="ui-state-default" id="items_<?= $id; ?>"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><?= $title; ?></li>
    <?php endforeach; ?>
<!--    <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 2</li>-->
<!--    <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 3</li>-->
<!--    <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 4</li>-->
<!--    <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 5</li>-->
<!--    <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 6</li>-->
<!--    <li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Item 7</li>-->
</ul>
<div id="info"></div>

</body>
</html>