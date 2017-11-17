<?php
$errors = $this->session->flashdata('errors');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Редактирование изображения</title>

    <!-- Bootstrap -->
    <link href="/assets/newdesign/bootstrap337/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="/assets/newdesign/css/jquery.Jcrop.css" type="text/css" />
    <style type="text/css">
        #crop{
            display:none;
        }
        #cropresult{
            border:2px solid #ddd;
        }
        #target {
            max-height: 584px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if(!empty($errors)): ?>
            <div class="row">
                <div class="col-lg-6 col-lg-offset-3">
                    <div class="alert alert-danger" style="text-align: center;"><?= $errors; ?></div>
                </div>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-lg-12">
                <h4>Редактирование изображения:</h4>
            </div>
        </div>
        <form action="/cropper/do_crop" method="post">
            <input type="hidden" name="widget_id" value="<?=$widget_id; ?>"/>
            <input type="hidden" name="product_id" value="<?=$product_id; ?>"/>
            <input type="hidden" name="file_name" value="<?=$image; ?>"/>
            <div class="row">
                <div class="col-lg-12">
                    <img src="<?=$path . $image; ?>" id="target" alt="[Jcrop Example]" />
                    <br/><br/>
                    <button id="release" onclick="return false;" class="btn btn-default">Снять выделение</button>
                    <button id="crop" class="btn btn-success">Сохранить</button>

                    <div class="optlist offset" style="visibility: hidden;">
                        <label><input type="checkbox" id="ar_lock" checked />Соблюдать пропорции при выделении (1:2)</label>
<!--                        <label><input type="checkbox" id="size_lock" />min/max размер (80x80/350x350)</label>-->
                    </div>

                    <div class="inline-labels" style="visibility: hidden;">
                        <label>X1 <input type="text" size="4" id="x1" name="x1" /></label>
                        <label>Y1 <input type="text" size="4" id="y1" name="y1" /></label>
                        <label>X2 <input type="text" size="4" id="x2" name="x2" /></label>
                        <label>Y2 <input type="text" size="4" id="y2" name="y2" /></label>
                        <label>W <input type="text" size="4" id="w" name="w" /></label>
                        <label>H <input type="text" size="4" id="h" name="h" /></label>
                    </div>

<!--                    <p>Результаты:</p>-->
<!--                    <div id="cropresult"></div>-->
                </div>
            </div>
<!--            <div class="row" style="margin: 20px 0;">-->
<!--                <div class="col-lg-12">-->
<!--                    <input type="submit" name="submit_select" class="btn btn-success" value="Сохранить"/>-->
<!--                </div>-->
<!--            </div>-->
        </form>
    </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/assets/newdesign/bootstrap337/js/bootstrap.min.js"></script>
    <script src="/assets/newdesign/js/jquery.Jcrop.min.js"></script>
    <script src="/assets/newdesign/js/crop.js"></script>
</body>
</html>