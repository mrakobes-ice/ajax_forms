<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<!-- ========== Footer Widgets ========== -->
        <a href="#" class="visible-lg projects-mail-form btn btn-black fixed">Обсудить проект</a>
        <footer class="footer-widgets">
          <div class="container">
            <div class="row section">

              <!-- About Us -->
              <div class="col-md-3 col-sm-6 mb-sm-100">
                <div class="widget about-widget">
                    <?$APPLICATION->IncludeFile("/_inc/footer-about.php", Array(), Array("MODE"=>"php"));?>
                </div><!-- / .widget -->
              </div><!-- / .col-md-3 -->

              <!-- Widget Instagram -->
              <div class="col-md-3 col-sm-6 mb-sm-100">
                <div class="widget gallery-widget iframe">
                  <h5 class="header-widget">Наша жизнь</h5>

                  <?/*<iframe src='/_inc/in/index.php' scrolling='no' frameborder='no'></iframe>*/?>
                    <script type="text/javascript">$('footer .widget.iframe').append("<noindex><iframe src='/inwidget/index.php?inline=3&view=6&toolbar=false' scrolling='no' style='border:none;width:260px;height:200px;overflow:hidden;'></iframe></noindex>");</script>

				  <?/* 
                  <ul>
                
                    <li><a href="http://placehold.it/650x450" class="gallery-widget-lightbox"><img src="http://placehold.it/87x87/999/eee" alt="Instagram Image"><div class="hover-link"><span class="linea-arrows-plus"></span></div></a></li>

                    <li><a href="http://placehold.it/650x450" class="gallery-widget-lightbox"><img src="http://placehold.it/87x87" alt="Instagram Image"><div class="hover-link"><span class="linea-arrows-plus"></span></div></a></li>

                    <li><a href="http://placehold.it/650x450" class="gallery-widget-lightbox"><img src="http://placehold.it/87x87/999/eee" alt="Instagram Image"><div class="hover-link"><span class="linea-arrows-plus"></span></div></a></li>

                    <li><a href="http://placehold.it/650x450" class="gallery-widget-lightbox"><img src="http://placehold.it/87x87" alt="Instagram Image"><div class="hover-link"><span class="linea-arrows-plus"></span></div></a></li>

                    <li><a href="http://placehold.it/650x450" class="gallery-widget-lightbox"><img src="http://placehold.it/87x87/999/eee" alt="Instagram Image"><div class="hover-link"><span class="linea-arrows-plus"></span></div></a></li>

                    <li><a href="http://placehold.it/650x450" class="gallery-widget-lightbox"><img src="http://placehold.it/87x87" alt="Instagram Image"><div class="hover-link"><span class="linea-arrows-plus"></span></div></a></li>

                  </ul>
				  */?>

                </div><!-- / .widget -->
              </div><!-- / .col-md-3 -->

              <!-- News -->
              <div class="col-md-3 col-sm-6 mb-sm-100">
                <div class="widget twitter-widget">
                  <h5 class="header-widget">Последние статьи</h5>
                  <ul>
                    <?
                    $arSelect = Array("NAME", "DETAIL_PAGE_URL");
                    $arFilter = Array("IBLOCK_ID"=>6, "ACTIVE"=>"Y", "SECTION_ID"=>18);
                    $res = CIBlockElement::GetList(Array("DATE_ACTIVE_FROM"=>"DESC"), $arFilter, false, Array("nPageSize"=>2), $arSelect);
                    while($ob = $res->GetNext())
                    {?> 
                    <li>
                      <a href="<?=$ob["DETAIL_PAGE_URL"]?>"><i class="fa fa-calendar"></i></a>
                      <p><?=$ob["NAME"]?><br><a href="<?=$ob["DETAIL_PAGE_URL"]?>">Читать новость</a></p>
                    </li>
                    <?}?>
                  </ul>
                </div><!-- / .widget -->
              </div><!-- / .col-md-3 -->

              <!-- Follow -->
              <div class="col-md-3 col-sm-6">
                <div class="widget newsletter-widget">
                  <h5 class="header-widget">оставайтесь на связи</h5>
                  <?/*
                  <form action="/ajax/mail.php" method="POST" class="form form-ajax">
                    <input type="hidden" name="form-id" value="subscription">                    	
                    	
                    <div class="form-group">                  
					  <input type="email" name="email" id="email-contact-1" class="form-control validate-locally" required placeholder="Ваш E-mail">
                      <span class="alert-error"></span>
                      <div style="display: none"><input type="text" name="botvalid" value=""></div>
                      <button type="submit"><i class="fa fa-send-o"></i></button>
                      <div class="ajax-message col-md-12 no-gap"></div>
                    </div>
                  </form>*/?>
                    <a href="/MM_kit.pdf" target="_blank" class="btn btn-light" style="width: 100%;padding: 8px 0;">ПРЕЗЕНТАЦИЯ КОМПАНИИ</a>
                    <a href="#" target="_blank" class="btn btn-black projects-mail-form" style="border:1px solid #fff;width: 100%;margin-top:15px; padding: 8px 0;">Написать нам</a>



                </div><!-- / .widget -->
              </div><!-- / .col-md-3 -->

            </div><!-- / .row -->
          </div><!-- / .container -->


          <!-- Copyright -->
          <div class="copyright">
            <div class="container">
              <div class="row">
                
                <div class="col-sm-6">
                  <small>&copy; 2016-<?=date("Y")?> Market Mentor digital agency</small>
                </div>

                <div class="col-sm-6">
                  <small><a href="#page-top" class="pull-right to-the-top">Вверх<i class="fa fa-angle-up"></i></a></small>
                </div>

              </div><!-- / .row -->
            </div><!-- / .container -->
          </div><!-- / .copyright -->

        </footer><!-- / .footer-widgets -->
      <div style="display: none;">
        <div class="box-modal" id="modal">
        <div class="box-modal_close arcticmodal-close">X</div>
          <form action="/ajax/mail.php" method="POST" class="form form-ajax modal-form">		  
				  <input type="hidden" name="form-id" value="order-callback">
				  
                  <!-- Name -->
                  <div class="form-group">
                    <label for="name-contact-2">Ваше имя</label>
                    <input type="text" name="name" id="name-contact-2" class="form-control validate-locally" placeholder="Введите ваше имя" required>
                    <span class="pull-right alert-error"></span>
                  </div>
                  <!-- Phone -->
                  <div class="form-group">
                    <label for="phone-contact-2">Ваш телефон</label>
				    <input type="text" name="phone" id="phone-contact-2" class="form-control validate-locally" required placeholder="Введите ваш телефон">
                  </div>
                  <div style="display: none">
                    <input type="text" name="botvalid" value="">
                  </div>
                  <input type="submit" class="btn-ghost" value="Жду ответ!">
                  <br>
                  <br>
                  <div class="ajax-message col-md-12 no-gap"></div>
           </form>
        </div>
    </div>
    <div style="display: none;">
        <div class="box-modal col-md-4" id="modal-project">
        <div class="box-modal_close arcticmodal-close">X</div>
          <form action="/ajax/mail.php" method="POST" class="form form-ajax modal-form" enctype="multipart/form-data">
				  <input type="hidden" name="form-id" value="modal-project">
                  <!-- Name -->
                  <div class="form-group">
                    <label for="name-project-1">Ваше имя</label>
                    <input type="text" name="name" id="name-project-1" class="form-control validate-locally" placeholder="Ваше имя" required>
                    <span class="pull-right alert-error"></span>
                  </div>
                  <!-- Phone -->
                  <div class="form-group">
                    <label for="phone-project-1">Ваш телефон</label>
				    <input type="text" name="phone" id="phone-project-1" class="form-control validate-locally" required placeholder="Ваш телефон">
                  </div>
                  <div class="form-group">
                      <label for="message-project-1">Ваше сообщение</label>
                      <textarea name="message" id="message-project-1" class="form-control" cols="42" rows="10"></textarea>
                      <span class="pull-right alert-error"></span>
                  </div>
                  <div class="form-group">
                      <div class="icon_file">
                      <label for="my_file">Прикрепить файл</label>
                          <span class="url_file"></span>
                      <input type="file" name="my_file" id="my_file">
                      </div>
                  </div>
                  <div style="display: none">
                    <input type="text" name="botvalid" value="">
                  </div>
                  <input type="submit" class="btn-ghost" value="Отправить">
              <div class="form-group">
                  <div class="checkbox">
                      <input id="editFormMainAgree" checked type="checkbox" class="agree">
                      <label for="editFormMainAgree">
                          Я согласен на обработку моих персональных данных в соответствии с <a href="/politika-konfidentsialnosti" target="_blank">политикой конфиденциальности</a>
                      </label>
                  </div>
              </div>
                  <div class="ajax-message col-md-12 no-gap"></div>
           </form>
        </div>
    </div>

    <!-- ========== Scripts ========== -->
    
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/google-fonts.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/jquery.easing.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/jquery.waypoints.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/bootstrap.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/bootstrap-hover-dropdown.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/smoothscroll.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/jquery.localScroll.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/jquery.scrollTo.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/jquery.stellar.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/jquery.parallax.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/slick.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/jquery.easypiechart.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/countup.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/isotope.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/jquery.magnific-popup.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/wow.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/jquery.ajaxchimp.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/jquery.maskedinput.min.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/vendor/animDots.js"></script>
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/jquery.arcticmodal-0.3.min.js"></script>
    
    <!-- Mentor JS -->
    <script src="<?= SITE_TEMPLATE_PATH ?>/assets/js/main.js"></script>
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function (d, w, c) {
            (w[c] = w[c] || []).push(function() {
                try {
                    w.yaCounter40046915 = new Ya.Metrika({
                        id:40046915,
                        clickmap:true,
                        trackLinks:true,
                        accurateTrackBounce:true,
                        webvisor:true
                    });
                } catch(e) { }
            });

            var n = d.getElementsByTagName("script")[0],
                s = d.createElement("script"),
                f = function () { n.parentNode.insertBefore(s, n); };
            s.type = "text/javascript";
            s.async = true;
            s.src = "https://mc.yandex.ru/metrika/watch.js";

            if (w.opera == "[object Opera]") {
                d.addEventListener("DOMContentLoaded", f, false);
            } else { f(); }
        })(document, window, "yandex_metrika_callbacks");
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/40046915" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->

</body>
</html>