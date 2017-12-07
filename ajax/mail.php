<?
//require($_SERVER["DOCUMENT_ROOT"]."/wp-load.php");
header('Content-Type: application/json; charset=utf-8');
	include 'class/form-handler.php';
	include 'class/providers/native-provider.php';
	//include 'class/providers/bitrix-provider.php';
	//include 'class/providers/modx-provider.php';


    $fh = new FormHandler();
    $fh->debug = false;
	$fh->SetupValidator(function($validator){
		//установка валидаторов, шаблонов правил...
		$validator->AddRuleTemplate('name',[ 
			'associateID' => 'USER_NAME',
			'required' => 'true',
			'label' => 'Имя',
			'validators'=>[ 
				'regex' => [
					'pattern'=>'^[А-Яа-я]+$',
					'noValidMsg' => 'Укажите ваше "Имя" русскими буквами.'
				]
			]
		]);
		$validator->AddRuleTemplate('phone',[ 
			'associateID' => 'USER_PHONE',
			'required' => 'true',
			'label' => 'Телефон',
			'validators'=>'phone'
		]);
		$validator->AddRuleTemplate('city',[
			'associateID' => 'USER_CITY',
			'required' => 'true',
			'validators'=> 'any' 
		]);
		$validator->AddRuleTemplate('email',[ 
			'associateID' => 'USER_EMAIL',
			'required' => 'true',
			'validators'=>'email'
		]);
		$validator->AddRuleTemplate('message',[
            'associateID' => 'MESSAGE',
			'required' => false,
			'label' => 'Сообщение',
			'validators'=>'any',
            /*'validators'=>[
                'regex' => [
                    'pattern'=>'.{30,}',
                    'noValidMsg' => 'Не введено сообщения.'
                ]
            ]*/
        ]);
		$validator->AddRuleTemplate('company',[
            'associateID' => 'COMPANY',
			'required' => true,
			'label' => 'Название вашей компании',
			'validators'=>'any'
        ]);
	});
		

	//-----------------Глобальные параметры отправки "по умолчанию"----------------
	//Основные параметры
    $fh->to = "skobelkinsa@mmentor.ru"; //.get_option('themadmin')["email"];
    $fh->from = "reply@blabla.ru";
	$fh->hideCopyTo = "spam@mmentor.ru";
	//$fh->hideCopyTo = "spam@mmentor.ru,skobelkinsa@mmentor.ru,".get_option('themadmin')["email"];
    $fh->language = "ru-RU";

	$fh->SetupForm('order callback',function($context, $settings){
	    $settings->useAntiSpam = true;
		$context->description = "Форма заказа звонка";
		$context->successMsg = "Сообщение отправлено!";
		$context->AddRules([
			'name',
			'phone',
			'email',
		]);
		$context->errorMsg = "Что то пошло не так:";
		$context->isRequiredMsg = "\"#label#\" не указан!";
        $settings->postTemplates[] = "SEND-CALLBACK";
	});

    //хранилище шаблонов
	$fh->templates->Add("SEND-CALLBACK", new MailTemplate(
		"Сообщение с сайта #SITE_NAME#.\n".
        "URL: #LOCATION_URL#\n".
		"Пользователь:		#USER_NAME#\n".
		"Телефон:			#USER_PHONE#\n".
        "Почта:				#USER_EMAIL#\n"
		,"Сообщение с сайта #SITE_NAME#.\n"));

	//-----------------Локальные параметры отправки----------------
    //Параметры шаблона
//  $fh->templates["name"]->body = "";        //строковое представление письма
//  $fh->templates["name"]->isText = true;      //если true то шаблон будет кодироваться HtmlEncode. в противном случае будет создаваться 2 представления шаблона

	//Основные параметры
//  $fh->templates["name"]->from = "noreplay@domain.com";
//  $fh->templates["name"]->to = "info@domain.com";
//  $fh->templates["name"]->copyTo = "info@domain.com";
//	$fh->templates["name"]->hideCopyTo = "spam@domain.com";
//  $fh->templates["name"]->subscription = "bla bla bla";
//	$fh->templates["name"]->importance = 3;        //1 - High, 3 - Normal, 5 - Lower
//	$fh->templates["name"]->language = "ru-RU";
//	$fh->templates["name"]->headers;               //Dictionary.Add('header_name','value')
//	$fh->templates["name"]->attachments;           //Dictionary.Add('file_name','uploaded_file_path')
//	$fh->templates["name"]->usedSMTP = false;
//  $fh->templates["name"]->media;                 //Dictionary.Add('image.img','uploaded_file_path')
//  $fh->templates["name"]->authData;			   //Dictionary, аналогичен глобальному полю... используется для переопределения (не обязателен)

//  $fh->templates["name"]->charset = "UTF-8";

	//Параметры SMTP
//	$fh->templates["name"]->authorization = true;  //если true, то используются свойства "user" и "password"
//	$fh->templates["name"]->timeout = 10;
//	$fh->templates["name"]->secureLevel = "none";  //"none" | "TLS" | "SSL"
//	$fh->templates["name"]->remoteAddreses = [];   //массив адресов SMTP-серверов (адрес может содержать порт, т.е. "address:port")

	$fh->Handle(); //Проверка и отправка    
?>