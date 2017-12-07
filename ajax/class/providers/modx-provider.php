<?


    class MODXProvider extends BaseProvider{
		 /** Параменты Mail
            
             * $subscription        -   тема сообщения
             * $from                -   почтовый адрес отправителя
             * $to                  -   список адресов-целей через запятую
             * $copyTo              -   список адресов-целей которым нужно отправить копию письма, через запятую
             * $hideCopyTo          -   список адресов-целей которым нужно отправить копию письма (но эти адреса не будут числится в строке "кому"), через запятую
             * $importance          -   важность сообщения (1 - High, 3 - Normal, 5 - Lower)
             * $language            -   идентификатор языка содержимого ("ru-RU")
             * $headers             -   коллекция заголовков почтового протокола
             * $attachments         -   коллекция файловых вложения (ключ - название файла, значение - путь к сохраненному передаваемому файлу)
             * $charset             -   кодировка содержимого (UTF-8)
             
            Параметры SMTP
                        
            * $usedSMTP             -   использовать SMTP или "стандартный" метод отправки (bool)
            * $timeout              -   таймаут отправки в сек. (10)
            * $secureLevel          -   метод шифрования ("none" | "TLS" | "SSL")
            * $remoteAddreses       -   массив адресов SMTP-хостов в формате "address:port"
            * $authorization        -   используется ли SMTP-авторизация (bool)
            * $user                 -   логин SMTP хоста
            * $password             -   пароль SMTP хоста 
            
            Основные параметры
            
            * $api                  -   экземпляр API используемого для отправки
            * $templateStructName   -   строковое название структуры, представляющей оформление письма в родительном падеже (например, "шаблона письма"... в MODX может быть "чанка")
            * $templateName         -   название шаблона
            * $template             -   строка стандартного шаблона (с уже обработанными заполнителями) или null
            * $fields               -   справочник полей
            * $isText               -   если true то шаблон будет кодироваться HtmlEncode (значение не кодировано!). в противном случае будет создаваться 2 представления шаблон
			
			* $proto 				-   защищенное свойство, содержащее префикс протокола (он определяется автоматически, в зависимости от "$secureLevel")
			* $defaultPort			-   защищенное свойство, содержащее номер порта "по умолчанию" для текущего протокола ("$secureLevel")
        */
                
        public function __construct(){ 
            $this->api = null;
            $this->templateStructName = "шаблон письма";            
        }
        
        protected function __handle(){
            
            
        }
	}
	$_GLOBALS['CUR_PROVIDER'] = new NativeProvider();
    






    /*
    *	Принимаемые параметры:
    *	$from - email отправителя
    *	$fromName - имя отправителя
    *	$to - email получателя
    *	$replyTo - email Reply To
    *	$bccTo - email "скрытая копия"
    *	$template - чанк с шаблоном письма
    *	$subject - Тема письма
    *	$params - массив с дополнительными данными для формы
    */
    $message = $modx->parseChunk($template, $params);
    $modx->getService('mail', 'mail.modPHPMailer');
    $modx->mail->set(modMail::MAIL_BODY, $message);
    $modx->mail->set(modMail::MAIL_FROM, $from);
    $modx->mail->set(modMail::MAIL_FROM_NAME, $fromName);
    $modx->mail->set(modMail::MAIL_SUBJECT, $subject);
    $modx->mail->address('to', $to);
    if ( $bccTo ) $modx->mail->address('bcc', $bccTo);
    if ( $replyTo ) $modx->mail->address('reply-to', $replyTo);
    $modx->mail->setHTML(true); 
    $sent = $modx->mail->send();
    if ( !$sent ) {
        $modx->log(modX::LOG_LEVEL_ERROR,'[EmailIt] Возникла проблема при отправке письма: '.$modx->mail->mailer->ErrorInfo);
    }
    $modx->mail->reset();



    /* --------------------------------------------------------------------------------------------- ------------------------------------------------------------------------------------- */
    //отвечаем только на AJAX запросы
    if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') { return; }
    //получаем значение ИД, при неправильном запросе - выход
    $id  = filter_input(INPUT_POST, 'id' );
    if (empty($id)) return;
    //определяем название ресурса, где произошла отправка сообщения
    $resource = $modx->getObject('modResource',$id);
    $pagetitle = $resource->get('pagetitle');
    //фильтруем остальные данные получаемые в запросе
    $name  = filter_input(INPUT_POST, 'name' );
    $email  = filter_input(INPUT_POST, 'email' );
    $tel  = filter_input(INPUT_POST, 'tel' );
    $msg  = filter_input(INPUT_POST, 'msg' );
    //получаем системную настройку emailsender для получателя сообщений
    $recepient = $modx->getConfig('emailsender');
    //инициализируем modSwiftMailer и отправляем сообщение на почту
    $modx->getService('mail', 'mail.modSwiftMailer');
    $modx->mail->address('to', $recepient, 'Recepient');
    $modx->mail->address('sender', $email, $name);
    $modx->mail->subject($pagetitle);
    //хтмл-код самого сообщения с данными
    $modx->mail->body("<h2>{$name}</h2> задал(а) вопрос на странице {$pagetitle} <p>{$msg} <br/> <b>email</b> {$email} <br/> <b>Телефон</b> {$tel}</p>
    ");
    $modx->mail->send();
    $modx->mail->reset();













    define('MODX_API_MODE', true);

    $path = explode("assets", realpath(dirname(__FILE__)));

    @include($path[0] . '/config.core.php');

    if (!defined('MODX_CORE_PATH')) {
        define('MODX_CORE_PATH', $path[0] . 'core/');
    }
    @include_once MODX_CORE_PATH . 'model/modx/modx.class.php';

    $modx = new modX();
    $modx->initialize('web');

    //-------------------------------------------//

    if (isset($_POST['form_id'])) {
        $fields = array();
        $messdata = array();
        $inc_file = '../data/increment.txt'; //файл инкремента
        $fdata = file_get_contents ( $inc_file );
        $fdata = intval($fdata) + 1;
        file_put_contents($inc_file, $fdata);
        $messdata['subject'] = 'Спланируйте для меня проект: запрос №'.$fdata.' от посетителя сайта usabilitylab.ru';
        //$messdata['to'] = $modx->getOption('contact-mail-1');
        // old 
        //$messdata['to'] = 'a.mann@galagodigital.ru';
        $messdata['to'] = 'info@usabilitylab.net';

        switch ($_POST['form_id']) {
            case 'prj-form':
                if (!empty($_POST['prj-name']) && !empty($_POST['prj-contact'])) {
                    $fields['prj-name'] = filter_input(INPUT_POST, 'prj-name');
                    $fields['prj-contact'] = filter_input(INPUT_POST, 'prj-contact');
                    $fields['prj-comment'] = filter_input(INPUT_POST, 'prj-comment');
                    $fields['page_uri'] = filter_input(INPUT_POST, 'page_uri');
                    $fields['page_title'] = filter_input(INPUT_POST, 'page_title');
                    $fields['prj-label'] = 'Нужен план проекта';

                    if(!empty($_POST['mess_subject'])){
                        $subj = filter_input(INPUT_POST, 'mess_subject');
                        switch ($subj){
                            case 'case':
                                $messdata['subject'] = 'Соберите для меня кейс: запрос №'.$fdata.' от посетителя сайта usabilitylab.ru';
                                $fields['prj-label'] = 'Нужен кейс';
                                break;
                        }

                    }

                    $message = $modx->getChunk('tpl-prj-mail', $fields);

                    header('Content-Type: application/json; charset=UTF-8');
                    runPhpMailer($message, $messdata, $modx, $fdata);
                } else {
                    exit('Пожалуйста заполните обязательне поля');
                }
                break;
        }
    }


    function runPhpMailer($message, $messdata, $modx, $num = 45768)
    {
        $modx->getService('mail', 'mail.modPHPMailer');
        $modx->mail->set(modMail::MAIL_BODY, $message);
        $modx->mail->set(modMail::MAIL_FROM, 'no-replay@usabilitylab.net');
        //$modx->mail->set(modMail::MAIL_FROM_NAME, $mailFromName);
        //$modx->mail->set(modMail::MAIL_SENDER, $mailSender);
        $modx->mail->set(modMail::MAIL_SUBJECT, $messdata['subject']);
        $modx->mail->address('to', $messdata['to']);
        $modx->mail->address('to', 'spam@mmentor.ru');
        $modx->mail->setHTML(true);

        if (!$modx->mail->send()) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: ');
            //echo 'error';
            echo json_encode(array('result'=>'error'));
            return false;
        }

        $modx->mail->reset();
        //echo 'done';
        echo json_encode(array('result'=>'done','number'=>$num));
        return true;
    }
?>