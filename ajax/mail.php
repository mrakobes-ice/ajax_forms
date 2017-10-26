<? header('Content-Type: application/json; charset=utf-8');
	include 'class/form-handler.php';
	//include 'class/providers/native-provider.php';
	include 'class/providers/bitrix-provider.php';
	//include 'class/providers/modx-provider.php';


    $fh = new FormHandler();
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

	});
		

	//-----------------Глобальные параметры отправки "по умолчанию"----------------
	//Основные параметры
	$fh->hideCopyTo = "spam@mmentor.ru";
    $fh->language = "ru-RU";

	$fh->SetupForm('modal-project',function($context, $settings){
	    $settings->useAntiSpam = false;
		$context->description = "Форма обсудить проект";
		$context->successMsg = "Сообщение отправлено!";
		$context->AddRules([
			'name',
			'phone',
			'city',
			'message',
            'my_file' => [
                'required' => false,
                'label' => 'Файл',
                /*'validators'=> 'attach'*/
                'validators'=>['attach'=>[
                    'extensions' => ['jpg','jpeg','png','gif','doc','csv','docx','zip','7z','rar','gz','xls','xlsx'],
                    'noValidMsg' => 'Не верный формат файла!'
                ]]
            ]
		]);
		$context->errorMsg = "Что то пошло не так:";
		$context->isRequiredMsg = "\"#label#\" не указан!";
        $settings->postTemplates[] = "SEND-PROJECT";
	});


	$fh->SetupForm('subscription',function($context, $settings){		
		$context->description = "Подписка";		
		$context->AddRules([			
			'city',
			'email'
		]);				
		$context->errorMsg = "Что то пошло не так:";
		$context->isRequiredMsg = "\"#label#\" не указан!";
        $settings->postTemplates[] = "SUBSCRIPTION";
	});

	
	$fh->SetupForm('contacts',function($context, $settings){		
		$context->description = "Контакты";
        $context->successMsg = "Сообщение отправлено!";
		$context->AddRules([
			'name',
			'phone',
			'city',
			'message' => [
                'required' => 'true',
				'validators' => [ 
					'regex' => [
						'noValidMsg' => 'Опишите вашу компанию.'
					]
				]
            ]
		]);				
		$context->errorMsg = "Что то пошло не так:";
		$context->isRequiredMsg = "\"#label#\" не указан!";
        $settings->postTemplates[] = "CONTACTS";
	});
	
	
	$fh->SetupForm('networking',function($context, $settings){		
		$context->description = "Партнерам/Нетворкинг";
        $context->successMsg = "Сообщение отправлено!";
		$context->AddRules([
			'name',
			'phone' => [
				'required' => 'false'
			],
			'email',
			'city',
			'message' => [ 
				'label' => 'О компании',
				'validators' => [ 
					'regex' => [
						'noValidMsg' => 'Опишите вашу компанию.'
					]
				]
			]
		]);				
		$context->errorMsg = "Что то пошло не так:";
		$context->isRequiredMsg = "Поле \"#label#\" не указано!";
        $settings->postTemplates[] = "NETWORKING";		
	});


    //хранилище шаблонов
	$fh->templates->Add("SEND-PROJECT", new MailTemplate(
		"Сообщение с сайта #SITE_NAME#.\n".
        "URL: #LOCATION_URL#\n".
		"Название формы:	#DESCRIPTION#\n\n".
		"Пользователь:		#USER_NAME#\n".
		"Телефон:			#USER_PHONE#\n".
        "Город:				#USER_CITY#\n".
		"Сообщение:			#MESSAGE#\n"
		,"Сообщение с сайта #SITE_NAME#.\n"));

	$fh->templates->Add("ORDER-CALLBACK", new MailTemplate(
		"Сообщение с сайта #SITE_NAME#.\n".
        "URL: #LOCATION_URL#\n".
		"Название формы:	#DESCRIPTION#\n\n".
		"Пользователь:		#USER_NAME#\n".
		"Телефон:			#USER_PHONE#\n".
        "Город:				#USER_CITY#\n"
		,"Сообщение с сайта #SITE_NAME#.\n"));


	$fh->templates->Add("SUBSCRIPTION", new MailTemplate(
		"Сообщение с сайта #SITE_NAME#.\n".
        "URL: #LOCATION_URL#\n".
		"Название формы:	#DESCRIPTION#\n\n".
		"Адрес:				#USER_EMAIL#\n".
        "Город:				#USER_CITY#\n"
		,"Сообщение с сайта #SITE_NAME#.\n"));

	$fh->templates->Add("CONTACTS", new MailTemplate(
		"Сообщение с сайта #SITE_NAME#.\n".
        "URL: #LOCATION_URL#\n".
		"Название формы:	#DESCRIPTION#\n\n".
		"Пользователь:		#USER_NAME#\n".
		"Телефон:			#USER_PHONE#\n".
		"Адрес:				#USER_EMAIL#\n".
        "Город:				#USER_CITY#\n".
		"Вопрос:			#MESSAGE#"
		,"Сообщение с сайта #SITE_NAME#.\n"));

	$fh->templates->Add("NETWORKING", new MailTemplate(
		"Сообщение с сайта #SITE_NAME#.\n".
        "URL: #LOCATION_URL#\n".
		"Название формы:	#DESCRIPTION#\n\n".
		"Пользователь:		#USER_NAME#\n".
		"Телефон:			#USER_PHONE#\n".
        "Город:				#USER_CITY#\n".
		"Вопрос:			#MESSAGE#"
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