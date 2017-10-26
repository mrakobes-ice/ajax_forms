<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); 
//header("X-Frame-Options: ALLOW-FROM https://www.instagram.com");
header("X-Frame-Options: ALLOW-FROM https://www.youtube.com");
?>
    <!DOCTYPE HTML>
<html class="no-js" lang="ru-RU">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><? $APPLICATION->ShowTitle() ?></title>
        <meta name="yandex-verification" content="be5d1e0a60382522" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="author" content="Market Mentor" />
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-107862203-1"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-107862203-1');
        </script>
                <!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="<?=SITE_TEMPLATE_PATH ?>/favicon.png" />
	    <meta property="og:image" content="images/preview_image.png" />
        <link rel="apple-touch-icon" href="<?=SITE_TEMPLATE_PATH ?>/assets/images/apple-touch-icon.png" />
        <!-- Bootstrap -->
        <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH ?>/assets/styles/vendor/bootstrap.min.css">
        <!-- Fonts -->
        <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH ?>/assets/fonts/et-lineicons/css/style.css">
        <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH ?>/assets/fonts/linea-font/css/linea-font.css">
        <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH ?>/assets/fonts/fontawesome/css/font-awesome.min.css">
        <!-- Slider -->
        <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH ?>/assets/styles/vendor/slick.css">
        <!-- Lightbox -->
        <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH ?>/assets/styles/vendor/magnific-popup.css">
        <!-- Animate.css -->
        <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH ?>/assets/styles/vendor/animate.css">

        <!-- Definity CSS -->
        <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH ?>/assets/styles/main.css">
        <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH ?>/assets/styles/responsive.css">
        <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH ?>/assets/styles/jquery.arcticmodal-0.3.css">

        <!-- JS -->
        <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/modernizr-2.8.3.min.js"></script>
        <? $APPLICATION->ShowHead(); ?>
        <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/jquery-2.1.4.min.js"></script>

		<script src="http://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=ru-RU" type="text/javascript"></script>
    </head>
<body id="page-top" data-spy="scroll" data-target=".navbar">
<div class="bx-panel"><? $APPLICATION->ShowPanel() ?></div>
    <!--[if lt IE 8]>
        <p class="browserupgrade">Ваш браузер <strong>устарел</strong>. Пожайлуста <a href="http://browsehappy.com/">обновите Ваш браузер</a>.</p>
    <![endif]-->

    <!-- ========== Preloader ========== -->
    <div class="preloader">
        <div class="ajax-loader">
            <div class="ajax-loader-logo">
                <!-- circle -->
                <div class="ajax-loader-circle">
                    <svg class="ajax-loader-circle-spinner"
                         viewBox="0 0 500 500"
                         xml:space="preserve">
        <circle
                cx="250" cy="250" r="239" />
      </svg>
                </div>

                <!-- Letters -->
                <div class="ajax-loader-letters">
                    <img src="<?=SITE_TEMPLATE_PATH ?>/assets/images/hero/ag2-logo.png" alt="Loading...">
                </div>
            </div>
        </div>
      <!--<img src="<?=SITE_TEMPLATE_PATH ?>/assets/images/loader.svg" alt="Loading...">-->
    </div>
    
    <!-- ========== Navigation ========== -->
    <?global $USER;?>
    <div id="top-menu" class="navbar navbar-default navbar-fixed-top mega navbar-inverse navbar-trans" role="navigation" style="<?if ($GLOBALS["APPLICATION"]->GetCurPage() == "/"):?>border-bottom:0;<?endif;?><?if($USER->IsAdmin()):?>top: 40px;<?endif;?>">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!-- Logo -->
          <a class="navbar-brand" href="<?if ($GLOBALS["APPLICATION"]->GetCurPage() != "/"):?>/<?else:?>#<?endif;?>"><img src="<?=SITE_TEMPLATE_PATH ?>/assets/images/logo-light.png" alt="Market Mentor"></a>
        </div><!-- / .navbar-header -->
        <!-- Navbar Links -->
        <?$APPLICATION->IncludeComponent(
            "bitrix:menu", 
            "top_menu", 
            array(
                "ALLOW_MULTI_SELECT" => "N",
                "CHILD_MENU_TYPE" => "left",
                "DELAY" => "N",
                "MAX_LEVEL" => "2",
                "MENU_CACHE_GET_VARS" => array(
                ), 
                "MENU_CACHE_TIME" => "3600",
                "MENU_CACHE_TYPE" => "N",
                "MENU_CACHE_USE_GROUPS" => "Y",
                "ROOT_MENU_TYPE" => "top",
                "USE_EXT" => "Y",
                "COMPONENT_TEMPLATE" => "top_menu"
            ),
            false
        );?>
      </div><!-- / .container -->
    </div><!-- / .navbar -->

    <?if ($GLOBALS["APPLICATION"]->GetCurPage() != "/"):?>
    <!-- ========== Header/Page Title - Medium Dark ========== -->
        <header class="page-title pt-dark">
          <div class="container">
            <div class="row"> 
              <div class="col-sm-6">
                <h1><?$APPLICATION->ShowTitle(false)?></h1>
              	</div>
              	<?$APPLICATION->IncludeComponent("bitrix:breadcrumb", "navigation", Array(
					"PATH" => "",
					"SITE_ID" => "s1",
					"START_FROM" => "0",
					"COMPONENT_TEMPLATE" => ".default"
					),
					false
				);?>
            </div>
          </div>
        </header> 
	<?endif;?>