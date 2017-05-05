<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\models\Categoria;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

$url = Url::to(['/posts/search-ajax']);

$js = <<<EOT
    $('#search').on('keyup', function () {
        $('#lupa').removeClass('glyphicon-refresh glyphicon-refresh-animate').addClass('glyphicon-search');

        if ($('#search').val().length >= 2) {
            $('#lupa').removeClass('glyphicon-search').addClass('glyphicon-refresh glyphicon-refresh-animate');
        }

        $('#search').autocomplete({
            source: function( request, response ) {
                $.ajax({
                    method: 'get',
                    url: '$url',
                    data: {
                        q: $('#search').val()
                    },
                    success: function (data, status, event) {
                        $('#lupa').removeClass('glyphicon-refresh glyphicon-refresh-animate').addClass('glyphicon-search');
                        var d = JSON.parse(data);
                        response(d);
                    }
                });
            },
            minLength: 2,
            delay: 800,
            response: function(event, ui) {
                $('#lupa').removeClass('glyphicon-refresh glyphicon-refresh-animate').addClass('glyphicon-search');
            }
        }).data("ui-autocomplete")._renderItem = function( ul, item ) {
            return $( "<li>" )
            .attr( "data-value", item.value )
            .append( $( "<a>" ).html( item.label.replace(new RegExp('^' + this.term, 'gi'),"<strong>$&</strong>") ) )
            .appendTo( ul );
        }

    });
EOT;
$js2 = <<<EOT
    function obtenerNotificaciones() {
        $.get('/user/profile/notifications-ajax', function(data){
            populateNotifications(data);
        });
    }

    obtenerNotificaciones();

    var populateNotifications = function(notificationData){
        var notificaciones = JSON.parse(notificationData);
        $('.notification-icon').attr('data-count', notificaciones.length);
        $('.dropdown-notifications-list').empty();

        if (notificaciones.length != 0) {
            $('.notification-icon').removeClass('hidden-icon').addClass('show-icon');

            $(notificaciones).each(function(index, item){

                if (item['type'] == 0) {
                    $('.dropdown-notifications-list').append('<li class="notification">'+
                    '<a href="' + item['url'] + '" class="notification-link" data-id='+item['id']+'>Tu post ha sido aceptado.</a></li>');
                }
            });
            $('.notification > a').on('click', function(e) {
                $.get('/user/profile/notifications-read-ajax', {id: $(this).attr('data-id')});
            });
        } else {
            $('.notification-icon').removeClass('show-icon').addClass('hidden-icon');
            $('.dropdown-notifications-list').append('<li class="notification">'+
            'No tienes ninguna notificación pendiente.</li>');
        }
    }

    setInterval(function(){
        obtenerNotificaciones()
    }, 5000);

    $('#notification-all-read').on('click', function(e) {
        e.preventDefault();
        $('.notification-icon').removeClass('show-icon').addClass('hidden-icon');

        $('.notification-icon').attr('data-count', 0);
        $('.dropdown-notifications-list').empty();
        $('.dropdown-notifications-list').append('<li class="notification">'+
        'No tienes ninguna notificación pendiente.</li>');

        $.get('/user/profile/notifications-read-ajax', {id: 0});
    });

EOT;

AppAsset::register($this);
$this->registerJs($js);
if (!Yii::$app->user->isGuest) {
    $this->registerJs($js2);
}
$categorias = Categoria::find()->all();
$this->title = 'GKRI';

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <script src="https://use.fontawesome.com/a727822b2c.js"></script>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Html::img('@web/images/logo.png', ['alt'=>Yii::$app->name, 'class' => 'logo']),
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);

    ?>
    <ul class="navbar-nav navbar-left nav">
        <li><a href="/gracioso">Gracioso</a></li>
        <li><a href="/amor">Amor</a></li>
        <li><a href="/series">Series</a></li>
        <li><a href="/wtf">WTF</a></li>
        <li class="dropdown">
            <a href="/" data-toggle="dropdown" class="dropdown-toggle">Más<b class="caret"></b></a>
            <ul class="dropdown-menu multi-column columns-3">
                <div class="row">
                    <?php foreach ($categorias as $i => $categoria) {
                        if ($i == 0) { ?>
                            <div class="col-sm-4">
                                <ul class="multi-column-dropdown">
                                    <li><a href="/<?= $categoria->nombre_c ?>"><?= $categoria->nombre ?></a></li>
                        <?php }
                        elseif ($i == 5 || $i == 11) { ?>
                                    <li><a href="/<?= $categoria->nombre_c ?>"><?= $categoria->nombre ?></a></li>
                                </ul>
                            </div>
                            <div class="col-sm-4">
                                <ul class="multi-column-dropdown">
                        <?php
                        } elseif ($i == count($categorias) - 1) { ?>
                                    <li><a href="/<?= $categoria->nombre_c ?>"><?= $categoria->nombre ?></a></li>
                                </ul>
                            </div>
                        <?php
                        } else { ?>
                            <li><a href="/<?= $categoria->nombre_c ?>"><?= $categoria->nombre ?></a></li>
                        <?php }
                    } ?>
                </div>
            </ul>
        </li>
    </ul>
   <ul class="navbar-nav navbar-right nav">
       <li></li>
       <li class="dropdown">
           <a data-toggle="dropdown" class="dropdown-toggle"><i id="lupa" class="glyphicon glyphicon-search"></i></a>
           <ul class="dropdown-menu dropdown-menu-search">
               <li>
                   <?php ActiveForm::begin(['action' =>  ['/posts/search'], 'method' => 'get', 'options' => ['class' => 'navbar-form navbar-right','role' => 'search']]);?>
                   <input type="text" id="search" class="form-control" placeholder="Search" name="q">
                   <?php ActiveForm::end();?>
               </li>
           </ul>
       </li>
    <?php if (Yii::$app->user->isGuest) : ?>
        <li><a class="blanco" href="<?= Url::to('/user/security/login') ?>">Login</a></li>
        <li><a class="blanco" href="<?= Url::to('/user/register') ?>">Registrarse</a></li>
    <?php else :?>
        <li class="dropdown dropdown-notifications">
            <a data-toggle="dropdown" class="dropdown-toggle">
              <i data-count="0" class="glyphicon glyphicon-bell notification-icon hidden-icon"></i>
            </a>

            <div class="dropdown-container">

                <div class="dropdown-toolbar">
                    <div class="dropdown-toolbar-actions">
                        <a href="/" id="notification-all-read">Marcar todas como leídas</a>
                    </div>
                    <h3 class="dropdown-toolbar-title">Notificaciones</h3>
                </div>

                <ul class="dropdown-menu dropdown-notifications-list">

                    <!-- <li class="notification">
                        <div class="media">
                            <div class="media-left">
                              <div class="media-object">
                                <img data-src="holder.js/50x50?bg=cccccc" class="img-circle" alt="Name" />
                              </div>
                            </div>
                            <div class="media-body">
                              <strong class="notification-title"><a href="#">Dave Lister</a> commented on <a href="#">DWARF-13 - Maintenance</a></strong>
                              <p class="notification-desc">I totally don't wanna do it. Rimmer can do it.</p>

                              <div class="notification-meta">
                                <small class="timestamp">27. 11. 2015, 15:00</small>
                              </div>
                            </div>
                      </div>
                  </li> -->
                </ul>
            </div>
        </li>
        <li class="dropdown">
            <a id="imagen-avatar" class="dropdown-toggle" href="/u/xharly8" data-toggle="dropdown">
                <?= Html::img(Yii::$app->user->identity->profile->getAvatar(), ['class' => 'img-rounded little']) ?>
                <b class="caret"></b>
            </a>
            <ul class="dropdown-menu">
                <li><a href="<?= Url::to('/u/' . Yii::$app->user->identity->username) ?> " tabindex="-1">Mi Perfil</a></li>
                <li><a href="<?= Url::to('/settings/profile') ?>" tabindex="-1">Configuración</a></li>
                <li class="divider"></li>
                <li><a href="<?= Url::to('/user/security/logout') ?>" data-method="post" tabindex="-1">Logout</a></li>
            </ul>
        </li>
        <li><a class="boton-upload btn-primary" href="<?= Url::to('/posts/upload') ?>">Upload</a></li>
    <?php endif; ?>
    </ul> <?php

    NavBar::end();

    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; GKRI <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
