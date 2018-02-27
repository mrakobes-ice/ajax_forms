<?
	include 'form-validator-class.php';
    include 'csv-class.php';

	/* Класс FormHandler предоставляет универсальный интерфейс обработки формы с отправкой результата по e-Mail.
	   Для почтовый отправок используется специальный интерфейс поставщиков, гарантирующий независимость от особенностей различных технологических API. */
	
	abstract class SendOptions{
		public $subscription 	= null;
		public $from 			= null;
		public $to 				= null;
		public $copyTo 			= null;
		public $hideCopyTo 		= null;
		public $importance 		= null;     //0 - auto, 1 - High, 3 - Normal, 5 - Lower
		public $language 		= null;
		public $headers 		= null;
		public $attachments 	= null;
        public $media 			= null;          //коллекция медиа-содержимого HTML-письма (картинки, видеофайлы, аудиофайлы...) не являющиеся вложением
		public $usedSMTP 		= null;	
		
		public $charset 		= null;
		public $timeout 		= null;		
		
		//параметры SMTP
		public $authorization	= null;
		public $secureLevel 	= null;     //"none" | TLS | SSL
		public $remoteAddreses 	= null;
		public $authData		= null;		//справочник данных аутентификации
		public $user			= null;
        public $password		= null;
	}

	//priority: 1
	class MailTemplate extends SendOptions{
		public $body 	= "";
        public $isText 	= true; //если true то шаблон будет кодироваться HtmlEncode. в противном случае будет создаваться 2 представления шаблон
        
		public function __construct($body="",$subscription=null,$from="#DEFAULT_EMAIL_FROM#",$to="#EMAIL_TO#"){
			$this->body 		= $body;
			$this->from 		= $from;
			$this->to 			= $to;
			$this->subscription = $subscription;
			$this->headers 		= new Dictionary();
			$this->attachments 	= new Dictionary();   
            $this->media 		= new Dictionary();
			$this->authData		= new Dictionary();
		}	
	}

	class InternalSendOptions{
		public $mail 					= null; //объект API отправки, предоставляемый конкретным поставщиком
		public $postTemplates 			= null;
		public $callbackTemplates 		= null;	
		public $useAntiSpam 			= true;
		public $postValidationHandler 	= null;
	}


	//Базовый класс для поставщиков логики рассылки
	//SendOptions target
	abstract class BaseProvider extends SendOptions{
		public $api 					= null;
		public $templateStructName 		= "шаблона письма FormHandler"; //название действующей структуры письма (выводимое в исключении)
            
        public $templateName 			= "";
        public $template 				= null;
        public $fields 					= array();
        public $isText 					= false;
            
		//спец-поля заполнителей		
        public $__description 			= "";
        public $site 					= "";
		private $location_url 			= "";	
		private $defaultPort 			= -1;
		private $proto 					= "";
        
        public function cleanSendOptions(){
			$this->subscription 		= null;
            $this->from 				= null;
            $this->to 					= null;
            $this->copyTo 				= null;
            $this->hideCopyTo 			= null;
            $this->importance 			= 3;
            $this->language 			= null;
            $this->usedSMTP 			= false;
            $this->charset 				= "UTF-8";
            $this->timeout 				= 10;
            $this->authorization 		= true;
            $this->secureLevel 			= "none";
            $this->authData				= null;
			
            $this->headers 				= null;
            $this->attachments 			= null;
            $this->media 				= null;
            $this->remoteAddreses 		= null;
		}
		public function applySendOptions($options){
            if(!is_null($options->subscription) && trim($options->subscription) 	!== ""){
			   $this->subscription 		= $options->subscription;
            }
            
            if(!is_null($options->from) 		&& trim($options->from) 			!== "#DEFAULT_EMAIL_FROM#"){
                $this->from 			= $options->from;
            }
            
            if(!is_null($options->to) 			&& trim($options->to)				!== "#EMAIL_TO#"){
                $this->to 				= $options->to;
            }
            
            if(!is_null($options->copyTo) 		&& trim($options->copyTo) 			!== ""){
                $this->copyTo 			= $options->copyTo;
            }
            
            if(!is_null($options->hideCopyTo) 	&& trim($options->hideCopyTo) 		!== ""){
                $this->hideCopyTo 		= $options->hideCopyTo;
            }
			
            if(!is_null($options->language) 	&& trim($options->language) 		!== ""){
                $this->language 		= $options->language;
            }
            
            if(!is_null($options->charset) 		&& trim($options->charset) 			!== ""){
                $this->charset 			= $options->charset;
            }
            
            if(!is_null($options->secureLevel) 	&& trim($options->secureLevel) 		!== ""){
                $this->secureLevel 		= $options->secureLevel;
            }
            
            if(!is_null($options->user) 		&& trim($options->user) 			!== ""){
                $this->user 			= $options->user;
            }
            
            if(!is_null($options->password) 	&& trim($options->password) 		!== ""){
                $this->password 		= $options->password;
            } 
			
			
			
            
            if(!is_null($options->importance)){  		//int
                $this->importance 		= $options->importance;
            }
                        
            if(!is_null($options->usedSMTP)){ 			//bool
                $this->usedSMTP 		= $options->usedSMTP;
            }
            
            if(!is_null($options->timeout)){ 			//int
                $this->timeout 			= $options->timeout;
            }
            
            if(!is_null($options->authorization)){ 		//bool
                $this->authorization 	= $options->authorization;
            }
			
			
            
			
			//Сложные поля
            if(!is_null($options->headers) && $options->headers->Count() > 0){				//Dictionary                
                
				if(!is_null($this->headers))				
					 $this->headers->Join($options->headers);
				else $this->headers = $options->headers;
				
            }
			
			if(!is_null($options->authData) && $options->authData->Count() > 0){			//Dictionary                
                
				if(!is_null($this->authData))				
					 $this->authData->Join($options->authData);
				else $this->authData = $options->authData;
				
            }
            
            if(!is_null($options->attachments) && $options->attachments->Count() > 0){		//Dictionary
                $this->attachments 		= $options->attachments;
            }
            
            if(!is_null($options->media) && $options->media->Count() > 0){					//Dictionary
                $this->media 			= $options->media;
            }    
            
            if(!is_null($options->remoteAddreses) && count($options->remoteAddreses) > 0){
                $this->remoteAddreses 	= $options->remoteAddreses;
            }         
		}
        protected abstract function __handle();
        
        protected function patchTemplate(){
            if(!is_null($this->template) && count($this->fields) > 0){
                foreach($this->fields as $key => $value){
                    $this->fields[$key]	= htmlspecialchars(trim($value), ENT_QUOTES); ///<----------------------------- htmlspecialchars processing
                    $this->template 	= fillLabel($this->template, $this->fields[$key], $key);
                    
                    //Также патчим описание
					$this->subscription = fillLabel($this->subscription, $this->fields[$key], $key);
                }
            }
        }
        public function Send(){
			$this->location_url 		= $_SERVER['HTTP_REFERER'];
            
            //Патчим базовый шаблон           
            $this->fields["DESCRIPTION"]  = $this->__description;
            $this->fields["SITE_NAME"] 	  = $this->site;
            $this->fields["LOCATION_URL"] = $this->location_url;
            $this->fields["SERVER_NAME"]  = $_SERVER['HTTP_HOST'];
            		
			if($this->secureLevel === "none"){
				$this->proto 			= "";                        
				$this->defaultPort 		= 25;
			}
			else if($this->secureLevel === "SSL"){
				$this->proto 			= "ssl://";
				$this->defaultPort 		= 465;
			}
			else if($this->secureLevel === "TLS"){
				$this->proto 			= "tls://";
				$this->defaultPort 		= 465;
			}
			
			
			if($this->usedSMTP && (is_null($this->remoteAddreses) || count($this->remoteAddreses) === 0)){
				throw new Exception("Должнен быть задан адрес хотя-бы одного SMTP-сервера (remoteAddreses)!");
				die();
			}
			
			//Убедимся что для всех SMTP-серверов указаны данные аутентификации
			if($this->usedSMTP && $this->authorization){
				if(is_null($this->authData) || $this->authData->Count() === 0){
					throw new Exception("Аутентификационных данных не существует!");
					die();
				}
				else{		
					$address = null;
					foreach($this->remoteAddreses as $a){
						if($this->authData->ExistsOf($a)){
							$address = $a;
							break;
						}
					}
					
					if(!is_null($address)){
						throw new Exception('Не указаны данные аутентификации для SMTP "' . $address . '"!');
						die();				
					}
				}				
			}
            return $this->__handle();
        }	
	}

	//priority: 2, default SendOptions
	class FormHandler extends SendOptions{		
		public 			$ASPath 		= "antispamDB.csv";
		
		//значение должно быть установлено объективно, в зависимости от кол-ва форм на сайте,
		public 			$ASCountValid 	= 3; //которыми пользователь фактически может воспользоваться сразу
		private 		$validator 		= null;
		public 			$templates 		= null;
		public 			$site 			= "";
		public          $debug          = false;
		public function __construct(){
			global $_GLOBALS;
			//Проверяем установлен ли поставщик:	
			
			if(!isset($_GLOBALS['CUR_PROVIDER'])){
				throw new Exception("Не установлен ни один поставщик отправки!");
				die();
			}
			else if($_GLOBALS['CUR_PROVIDER'] instanceof BaseProvider === false){
				throw new Exception('Поставщик должен быть классом производным от "BaseProvider"!');
				die();
			}			
			
			$this->validator 			= new FormValidator();	
			$this->templates 			= new Dictionary();
			$this->templates->itemTypeValidator = function($item){ //используется классом Dictionary для проверки добавляемых элементов
				return $item instanceof MailTemplate;
			};
			
			$this->headers = new Dictionary();
			$this->authData	= new Dictionary();
		}
		public function __set($name, $value){
			if($name == 'uploadDir' && $this->validator instanceof FileValidator){
                $this->validator->uploadDir 	= $value;
			}   
		}
		public function __get($name){
			if($name == 'uploadDir' && $this->validator instanceof FileValidator){
				return $this->validator->uploadDir;
			}
			else if($name == 'subscription'){
				return null;
			}
			else if($name == 'importance'){
				return null;
			}
			else if($name == 'copyTo'){
				return null;
			}
			else if($name == 'attachments'){
				return null;
			}
		}
		
		//lazy functions
		public function SetupValidator($callback){
			$callback($this->validator);		
		}
		public function SetupForm($context, $callback){			
			global $_GLOBALS;
			
			$this->validator->AddContext($context);
			$c = $this->validator->context[$context];
			
			$c->additionals 					= new InternalSendOptions();
			$c->additionals->mail 				= $_GLOBALS['CUR_PROVIDER']->api;
			$c->additionals->postTemplates 		= array();
			$c->additionals->callbackTemplates 	= array();
			
			$callback($c, $c->additionals);
		}
		
		//generic handling
		public function Handle(){
			global $_GLOBALS;
			$result = array();
			$result['status'] 			= 'success';

			//1. Получаем параметры текущего контекста:
            if($this->debug){
                echo "Получение контекста - ";
            }
			$cur_context_name 			= $this->validator->GetCurrentContext();
            if(!isset($this->validator->context[$cur_context_name])) die();

			$cur_context 				= $this->validator->context[$cur_context_name];
			$mail_settings 				= $cur_context->additionals;

            if($this->debug){
                echo "ОК" . PHP_EOL;
            }
			
			/*if(count($mail_settings->postTemplates) == 0 && count($mail_settings->callbackTemplates) == 0){				
				throw new Exception('Укажите название хотя-бы одного "' . $_GLOBALS['CUR_PROVIDER']->templateStructName . '" отправки или обратного вызова!');
				die();
			}*/
			
			//2. Выполняем анти-спам проверку:
            if($this->debug){
                echo "Антисапм-проверка - ";
            }
            if((isset($_POST['botvalid']) && $_POST['botvalid'] !== "") || 
               (isset($_GET['botvalid']) && $_GET['botvalid'] !== "")) die();

            if($this->debug){
                echo "ОК" . PHP_EOL;
            }

			if($mail_settings->useAntiSpam){				
				$k 						= $this->ASCountValid;
				$times 					= 60*60*30;
				$ipv4 					= $_SERVER['REMOTE_ADDR'];

				$csv 					= new parseCSV();
				$csv->auto($this->ASPath);
				foreach ($csv->data as $value){
					$ip 				= $value['ip'];
					$time 				= (int)$value['time'];
					if($ip == $ipv4){
						if($time+$times >= time()){
							$k--;
							if($k === 0){
								$result['status'] 	= 'fail';
								$result['message'] 	= "Вы привысили допустимое кол-во обращений!";
								break;
							}
						}
					}
				}
				unset($csv);
			}	
						
			
			//3. Выполняем валидацию:
            if($this->debug){
                echo "Валидация - ";
            }
			if($result['status'] === 'success'){
				$result 					= $this->validator->Valid($cur_context_name);
                if($result===NULL){
                    //throw new Exception("Error!!! form-handler 455");
                    return NULL;
                }
			}
            if($this->debug){
                echo "ОК" . PHP_EOL;
            }
			
			//3.1. Постобработка
            if($this->debug){
                echo "Постобработка - ";
            }
			if($result['status'] === 'success'){
				if(!is_null($mail_settings->postValidationHandler) && is_callable($mail_settings->postValidationHandler)){
					$result2 			    = call_user_func($mail_settings->postValidationHandler);
					
					if($result2 !== false){
						$result['status'] 	= 'fail';
						$result['message'] 	= $result2;
						unset($result['data']);						
					}
				}
			}
            if($this->debug){
                echo "ОК" . PHP_EOL;
            }
			
			//4. Выполняем отправку:
			if($result['status'] === 'success'){
                if($this->debug){
                    echo "Отправка... ".PHP_EOL;
                }
				$_GLOBALS['CUR_PROVIDER']->__description 	          	  = $cur_context->description;
				$_GLOBALS['CUR_PROVIDER']->site = $this->site;
				
                //переносим вложения в общую коллекцию отправки
				$attachments = array();

                if($this->debug){
                    echo "---- Обработка вложений - ";
                }
				if(isset($result['attachments'])){
					foreach($result['attachments'] as $k => $v){
						$attachments[$k] = $v;
						unset($result['data'][$k]);
					}
				}
                if($this->debug){
                    echo "ОК" . PHP_EOL;
                }

                
				//Гарантируем что никто не использовал данные свойства (они не используются глобально - только в шаблонах)
				$this->attachments 		= null;
				$this->media 			= null;
				$this->subscription 	= null;
				$this->importance 		= null;
				
				
				//обработка шаблонов отправки
                if($this->debug){
                    echo "---- Обработка шаблонов отправки (". count($mail_settings->postTemplates) . ") - ";
                }
                $send_success = false;
				if(count($mail_settings->postTemplates) > 0){                    
                    foreach($mail_settings->postTemplates as $pt_name){	
                        $_GLOBALS['CUR_PROVIDER']->cleanSendOptions();  
                        $_GLOBALS['CUR_PROVIDER']->applySendOptions($this);                        
                        $_GLOBALS['CUR_PROVIDER']->templateName 	  = $pt_name;
                        						
                        if(isset($this->templates[$pt_name])){  
                            $pt = $this->templates[$pt_name];                            
                            $_GLOBALS['CUR_PROVIDER']->applySendOptions($pt);
                            $_GLOBALS['CUR_PROVIDER']->template 	  = $pt->body;
                            $_GLOBALS['CUR_PROVIDER']->isText 		  = $pt->isText;
                        }            
						else $_GLOBALS['CUR_PROVIDER']->template 	  = null;                        
                        
                        if($_GLOBALS['CUR_PROVIDER']->to === "#DEFAULT_EMAIL_FROM#" ||
                           $_GLOBALS['CUR_PROVIDER']->to === "#EMAIL_TO#")
                           $_GLOBALS['CUR_PROVIDER']->to = $this->from;
                        
                        
                        //патчим поля их псевдонимами
                        foreach($result['data'] as $k => $v){
                            if(isset($result['associations'][$k])){
                                $k1 = $result['associations'][$k];
                                $_GLOBALS['CUR_PROVIDER']->fields[strtoupper($k1)] 	= $v;                                
                            }                
                            else{
                                $_GLOBALS['CUR_PROVIDER']->fields[strtoupper($k)] 	= $v; 
                            }
                        }
                        
                        //применяем вложения
						if(count($attachments) > 0){
							if(is_null($_GLOBALS['CUR_PROVIDER']->attachments))
								$_GLOBALS['CUR_PROVIDER']->attachments = new Dictionary();
							
							$_GLOBALS['CUR_PROVIDER']->attachments->Join($attachments);							
						}
                        	                        					
                        $send_success = $_GLOBALS['CUR_PROVIDER']->Send();

                    }
				}
                if($this->debug && !$send_success){
                    echo "ОК" . PHP_EOL;
                }
				
				//обработка шаблонов обратной отправки
                if($this->debug){
                    echo "---- Обработка шаблонов обратной отправки (". count($mail_settings->callbackTemplates) . ") - ";
                }
				if(count($mail_settings->callbackTemplates) > 0 && $send_success === false){                    
                    foreach($mail_settings->callbackTemplates as $pt_name){	
                        $_GLOBALS['CUR_PROVIDER']->cleanSendOptions();  
                        $_GLOBALS['CUR_PROVIDER']->applySendOptions($this);                        
                        $_GLOBALS['CUR_PROVIDER']->templateName 	= $pt_name;
                        							
                        if(isset($this->templates[$pt_name])){  
                            $pt = $this->templates[$pt_name];                            
                            $_GLOBALS['CUR_PROVIDER']->applySendOptions($pt);
                            $_GLOBALS['CUR_PROVIDER']->template 	= $pt->body;
                            $_GLOBALS['CUR_PROVIDER']->isText 		= $pt->isText;
                        }
                        
                        if($_GLOBALS['CUR_PROVIDER']->to === "#DEFAULT_EMAIL_FROM#" ||
                           $_GLOBALS['CUR_PROVIDER']->to === "#EMAIL_TO#")
                           $_GLOBALS['CUR_PROVIDER']->to = $this->from;
                                        
                        $send_success = $_GLOBALS['CUR_PROVIDER']->Send();                        	
                    }
				}
                if($this->debug && !$send_success){
                    echo "ОК" . PHP_EOL;
                }
                
                if($send_success !== false){
                    $result['status'] = 'fail';
                    $result['message'] = "Send fail!";
                    $result['data'] = array('send' => $send_success);
                }
                
                
				
				//Делаем новую запись успешной отправки
				if($mail_settings->useAntiSpam){
					$csv = new parseCSV();
					$csv->save($this->ASPath, array(array($ipv4, time())), true, array('ip','time'));
					unset($csv);
				}
				
			}
			
            if(isset($result['associations']))
                unset($result['associations']);
            
			if(isset($result['attachments']))
				unset($result['attachments']);
			
			if($result['status'] === 'success')
				unset($result['data']);
			
			//5. Возвращаем ответ клиенту:
			header('Content-Type: application/json;');

			echo json_encode($result);

            if($this->debug){
                echo "Результат отправленный клиенту:" . PHP_EOL;
                var_dump(json_encode($result));
            }

			if(!is_null($_GLOBALS['CUR_PROVIDER']->attachments)) {
                foreach ($_GLOBALS['CUR_PROVIDER']->attachments as $el) {
                    unlink($el);
                }
            }
		}
	}	
    

    //пример поставщика почтовой логики
    class MyProvider extends BaseProvider{        
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
             * $timeout             -   таймаут отправки в сек. (10)
             
            Параметры SMTP
                        
            * $usedSMTP             -   использовать SMTP или "стандартный" метод отправки (bool)
            * $secureLevel          -   метод шифрования ("none" | "TLS" | "SSL")
            * $remoteAddreses       -   массив адресов SMTP-хостов в формате "address:port"
            * $authorization        -   используется ли SMTP-авторизация (bool)
			* $authData				-   коллекция данных аутентификации на SMTP-серверах (ключи - адреса SMTP-серверов в формате "address:port", а данные - пары "login:password")
            
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
    /*	$_GLOBALS['CUR_PROVIDER'] = new MyProvider(); */

?>