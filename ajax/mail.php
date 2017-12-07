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
		$validator->AddRuleTemplate('company',[
            'associateID' => 'COMPANY',
			'required' => true,
			'label' => 'Название вашей компании',
			'validators'=>'any'
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
                'validators'=> 'attach'
                /*'validators'=>['attach'=>[
                    'extensions' => ['jpg','jpeg','png','gif','doc','csv','docx','zip','7z','rar','gz','xls','xlsx'],
                    'noValidMsg' => 'Не верный формат файла!'
                ]]*/
            ]
		]);
		$context->errorMsg = "Что то пошло не так:";
		$context->isRequiredMsg = "\"#label#\" не указан!";
        $settings->postTemplates[] = "SEND-PROJECT";
	});
    $fh->SetupForm('modalsub',function($context, $settings){
            $settings->useAntiSpam = false;
            $context->description = "Форма Субподряд";
            $context->successMsg = "Сообщение отправлено!";
            $context->AddRules([
                'company',
                'name',
                'phone',
                'email',
                'city',
                'message',
                'site'=>[
                    'associateID' => 'SITE',
                    'label' => 'Сайт вашей компани',
                    'validators'=>'any'
                ],
                'special'=>[
                    'associateID' => 'SPECIAL',
                    'required' => true,
                    'label' => 'Специализация вашей компании',
                    'validators'=>'any'
                ],
                'age'=>[
                    'associateID' => 'AGE',
                    'label' => 'Срок',
                    'validators'=>'any'
                ],
                'budget' => [
                    'associateID' => 'BUDGET',
                    'label' => 'Бюджет',
                    'validators'=>'any'
                ],
                'projdesc' => [
                    'associateID' => 'PROJDESC',
                    'label' => 'Описание',
                    'validators'=>'any'
                ],
                'my_file' => [
                    'label' => 'Файл',
                    'validators'=> 'attach'
                ]
            ]);
            $context->errorMsg = "Что то пошло не так:";
            $context->isRequiredMsg = "\"#label#\" не указан!";
            $settings->postTemplates[] = "SEND-SUB";
        });
    $fh->SetupForm('podryadchiku',function($context, $settings){
            $settings->useAntiSpam = false;
            $context->description = "Форма Подрядчику";
            $context->successMsg = "Сообщение отправлено!";
            $context->AddRules([
                'company',
                'site'=>[
                    'associateID' => 'SITE',
                    'label' => 'Сайт вашей компани',
                    'validators'=>'any'
                ],
                'special'=>[
                    'associateID' => 'SPECIAL',
                    'required' => true,
                    'label' => 'Специализация вашей компании',
                    'validators'=>'any'
                ],
                'technologies'=>[
                    'associateID' => 'TECH',
                    'label' => 'Стек технологий',
                    'validators'=>'any'
                ],
                'name',
                'phone',
                'email',
                'city',
                'message',
                'my_file' => [
                    'label' => 'Файл',
                    'validators'=> 'attach'
                ]
            ]);
            $context->errorMsg = "Что то пошло не так:";
            $context->isRequiredMsg = "\"#label#\" не указан!";
            $settings->postTemplates[] = "SEND-TECH";
        });
    $fh->SetupForm('frilance',function($context, $settings){
            $settings->useAntiSpam = false;
            $context->description = "Форма Фрилансеру";
            $context->successMsg = "Сообщение отправлено!";
            $context->AddRules([
                'special'=>[
                    'associateID' => 'SPECIAL',
                    'required' => true,
                    'label' => 'Специализация',
                    'validators'=>'any'
                ],
                'name',
                'phone',
                'email',
                'city',
                'message',
                'my_file' => [
                    'label' => 'Файл',
                    'validators'=> 'attach'
                ]
            ]);
            $context->errorMsg = "Что то пошло не так:";
            $context->isRequiredMsg = "\"#label#\" не указан!";
            $settings->postTemplates[] = "SEND-FREE";
        });
    $fh->SetupForm('agents',function($context, $settings){
            $settings->useAntiSpam = false;
            $context->description = "Форма Агенту";
            $context->successMsg = "Сообщение отправлено!";
            $context->AddRules([
                'exp'=>[
                    'associateID' => 'EXP',
                    'label' => 'Опыт работы',
                    'validators'=>'any'
                ],
                'name',
                'phone',
                'email',
                'city',
                'message',
                'my_file' => [
                    'label' => 'Файл',
                    'validators'=> 'attach'
                ]
            ]);
            $context->errorMsg = "Что то пошло не так:";
            $context->isRequiredMsg = "\"#label#\" не указан!";
            $settings->postTemplates[] = "SEND-AGENT";
        });
	$fh->SetupForm('modal-summary',function($context, $settings){
	    $settings->useAntiSpam = false;
		$context->description = "Вакансии";
		$context->successMsg = "Сообщение отправлено!";
		$context->AddRules([
			'name',
			'phone',
			'city',
			'message',
            'my_file' => [
                'required' => false,
                'label' => 'Файл',
                'validators'=> 'attach'
                /*'validators'=>['attach'=>[
                    'extensions' => ['jpg','jpeg','png','gif','doc','csv','docx','zip','7z','rar','gz','xls','xlsx'],
                    'noValidMsg' => 'Не верный формат файла!'
                ]]*/
            ]
		]);
		$context->errorMsg = "Что то пошло не так:";
		$context->isRequiredMsg = "\"#label#\" не указан!";
        $settings->postTemplates[] = "SEND-SUMMARY";
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
                'required' => false,
				'validators' => 'any'
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
	$fh->SetupForm('order-callback',function($context, $settings){
		$context->description = "Партнерам/Нетворкинг";
        $context->successMsg = "Сообщение отправлено!";
		$context->AddRules([
			'name',
			'phone' => [
				'required' => 'false'
			],
			'city'
		]);
		$context->errorMsg = "Что то пошло не так:";
		$context->isRequiredMsg = "Поле \"#label#\" не указано!";
        $settings->postTemplates[] = "ORDER-CALLBACK";
	});
	$fh->SetupForm('geograf',function($context, $settings){
		$context->description = "География присутствия";
        $context->successMsg = "Сообщение отправлено!";
		$context->AddRules([
			'name',
			'email',
			'message',
			'phone' => [
				'required' => 'false'
			],
			'city'
		]);
		$context->errorMsg = "Что то пошло не так:";
		$context->isRequiredMsg = "Поле \"#label#\" не указано!";
        $settings->postTemplates[] = "GEOGRAF";
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
    $fh->templates->Add("SEND-SUB", new MailTemplate(
		"Сообщение с сайта #SITE_NAME#.\n".
        "URL: #LOCATION_URL#\n".
		"Название формы:	#DESCRIPTION#\n\n".
		"Пользователь:		#USER_NAME#\n".
		"Телефон:			#USER_PHONE#\n".
        "Город:				#USER_CITY#\n".
		"Название компании:	#COMPANY#\n".
		"Сайт компании:		#SITE#\n".
		"Специализация:		#SPECIAL#\n".
		"О компании:		#MESSAGE#\n".
		"Срок выполнения:   #AGE#\n".
		"Бюджет:		    #BUDGET#\n".
		"Описание проекта:  #PROJDESC#\n"
		,"Сообщение с сайта #SITE_NAME#.\n"));
    $fh->templates->Add("SEND-TECH", new MailTemplate(
		"Сообщение с сайта #SITE_NAME#.\n".
        "URL: #LOCATION_URL#\n".
		"Название формы:	#DESCRIPTION#\n\n".
		"Пользователь:		#USER_NAME#\n".
		"Телефон:			#USER_PHONE#\n".
        "Город:				#USER_CITY#\n".
		"Название компании:	#COMPANY#\n".
		"Сайт компании:		#SITE#\n".
		"Специализация:		#SPECIAL#\n".
        "Стек технологий:   #TECH#\n".
        "Сообщение:		#MESSAGE#\n"
		,"Сообщение с сайта #SITE_NAME#.\n"));
    $fh->templates->Add("SEND-FREE", new MailTemplate(
		"Сообщение с сайта #SITE_NAME#.\n".
        "URL: #LOCATION_URL#\n".
		"Название формы:	#DESCRIPTION#\n\n".
		"Пользователь:		#USER_NAME#\n".
		"Телефон:			#USER_PHONE#\n".
        "Город:				#USER_CITY#\n".
        "Специализация:   #SPECIAL#\n".
        "Сообщение:		#MESSAGE#\n"
		,"Сообщение с сайта #SITE_NAME#.\n"));
    $fh->templates->Add("SEND-AGENT", new MailTemplate(
		"Сообщение с сайта #SITE_NAME#.\n".
        "URL: #LOCATION_URL#\n".
		"Название формы:	#DESCRIPTION#\n\n".
		"Пользователь:		#USER_NAME#\n".
		"Телефон:			#USER_PHONE#\n".
        "Город:				#USER_CITY#\n".
        "Опыт работы:   #EXP#\n".
        "Сообщение:		#MESSAGE#\n"
		,"Сообщение с сайта #SITE_NAME#.\n"));

    $fh->templates->Add("SEND-SUMMARY", new MailTemplate(
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
	$fh->templates->Add("GEOGRAF", new MailTemplate(
		"Сообщение с сайта #SITE_NAME#.\n".
        "URL: #LOCATION_URL#\n".
		"Название формы:	#DESCRIPTION#\n\n".
		"Пользователь:		#USER_NAME#\n".
		"Телефон:			#USER_PHONE#\n".
        "Город:				#USER_CITY#\n".
		"Где:			#MESSAGE#"
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