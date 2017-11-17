<?php
//var_dump($images, $widget_id, $product_id);
$images_path = '/uploads/shop/products/thumbs/';
$errors = $this->session->flashdata('errors');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Выбор изображения для редактирования</title>

    <!-- Bootstrap -->
    <link href="/assets/newdesign/bootstrap337/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style type="text/css">
        .tmb-img {
            max-width: 200px;
            max-height: 200px;
        }
        .a-center {
            height: 270px;
            text-align: center;
            margin-top: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        .radio-place {
            width: 100%;
            position: absolute;
            bottom: 0;
            text-align: center;
            padding-top: 10px;
            padding-bottom: 10px;
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
                <h4>Выберите одно из изображений товара для редактирования:</h4>
            </div>
        </div>
        <form action="/cropper/polygon" method="post" enctype="multipart/form-data">
            <input type="hidden" name="widget_id" value="<?=$widget_id; ?>"/>
            <input type="hidden" name="product_id" value="<?=$product_id; ?>"/>
        <div class="row">
            <?php
            if(!empty($images)){
                $i = 1;
                foreach ($images as $image) {
                    if(($i % 5) == 0){
                        echo '</div><div class="row">';
                    }
            ?>
            <div class="col-lg-3 a-center">
                <img src="<?=$images_path . $image->file_name; ?>" class="img-thumbnail tmb-img">
                <div class="radio-place">
                    <div class="radio">
                        <label>
                            <input type="radio" name="selected_image" id="optionsRadios<?=$i; ?>" value="<?= $image->id; ?>" <?=($i == 1) ? 'checked' : ''; ?>>
                        </label>
                    </div>
                </div>
            </div>
            <?php
                    $i++;
                }
            }
            ?>
        </div>
            <div class="row">
                <div class="col-lg-12">
                    <h5>или выберите заранее подготовленное вами изображение и загрузите его на сервер:</h5>
                    <input type="file" name="userfile" id="fname"/>
                </div>
            </div>
            <div class="row" style="margin: 20px 0;">
                <div class="col-lg-12">
                    <input type="submit" name="submit_select" class="btn btn-success" value="Продолжить"/>
                </div>
            </div>
        </form>
    </div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/assets/newdesign/bootstrap337/js/bootstrap.min.js"></script>
</body>
</html>