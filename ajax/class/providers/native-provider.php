<?	
	include 'class/mail-class.php';

	class NativeProvider extends BaseProvider{
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
            $this->api = new Mail($this->charset);         
        }
        
        private function getText($s){
            return strip_tags($s);
        }
        
        protected function __handle(){
			
			/*
			
				
				$this->importance 			= 3;
				$this->usedSMTP 			= false;
				$this->charset 				= "UTF-8";
				$this->timeout 				= 10;
				$this->authorization 		= true;
				$this->secureLevel 			= "none";
			
				$this->subscription 		= null;
				$this->from 				= null;
				$this->to 					= null;
				$this->copyTo 				= null;
				$this->hideCopyTo 			= null;
				$this->language 			= null;
				$this->user 				= null;
				$this->password 			= null;

				$this->headers 				= null;
				$this->attachments 			= null;
				$this->media 				= null;
				$this->remoteAddreses 		= null;
			
			
			*/
						
			$this->patchTemplate();
			
            if(is_null($this->template) || trim($this->template) === ""){
                throw new Exception("Шаблон сообщения \"" . $this->templateName . "\" не имеет тела!");
                die();
            }         
			
			if(!is_null($this->headers)){				
				//заголовки
				foreach($this->headers as $header => $value){
					$this->api->headers[$header] = $value;
				}
			}
            
            //устанавливаем базовые параметры отправки
			if(!is_null($this->from) && $this->from !== ""){
				$this->api->From($this->from);
			}
			else{
				throw new Exception('Не указан адрес отправителя!');
				die();
			}
				
			
			if(!is_null($this->to) && $this->to !== ""){
				$this->api->To(explode(',',$this->to));
			}
			else{
				throw new Exception('Не указан адрес доставки!');
				die();
			}
             
            
            if($this->isText){
                $this->api->Body($this->template, "text");
            }
            else{
                $this->api->Body($this->template, "html", getText($this->template));
            }
                
            //вложения
			if(!is_null($this->attachments)){
				foreach($this->attachments as $file_name => $path){
					$this->api->Attach( $path, $file_name, "", "attachment");
				}
			}
            
            //медиа-ресурсы письма
			if(!is_null($this->media)){
				foreach($this->media as $file_name => $path){
					$this->api->Attach( $path, $file_name, "");
				}
			}
            
            //тема
            if(!is_null($this->subscription) && $this->subscription !== "")
                $this->api->Subject($this->subscription);
            
            //копия
            if(!is_null($this->copyTo) && $this->copyTo !== "")
                $this->api->Cc(explode(',',$this->copyTo));
            
            //скрытая копия
            if(!is_null($this->hideCopyTo) && $this->hideCopyTo !== "")
                $this->api->Bcc(explode(',',$this->hideCopyTo));
            
            //важность
            $this->api->Priority($this->importance);
            
            //language 
			if(!is_null($this->language) && $this->language !== "")
            	$this->api->headers['Content-Language'] = $this->language;
			else{
				throw new Exception('Должен быть указан язык содержимого!');
				die();
			}
                        
            //SMTP
            $state = false;
            if($this->usedSMTP){
                foreach($this->remoteAddreses as $sa){ 
                    $sa = preg_replace("[a-z0-9]+://","",$sa);
                    $address_elements = explode(':',$sa);
					
					if($this->authorization){
						$auth = explode(':', $this->authData[$sa]);
						$login = $auth[0];
						$password = $auth[1];
					}
					else $login = $password = 0;					
                    
                    $this->api->smtp_on($this->proto . $address_elements[0], 
                                $login, $password, 
                                isset($address_elements[1]) ? $address_elements[1] : $this->defaultPort, 
                                $this->timeout);
					
                    $state = $this->api->Send();                    
                }                
            }
            else $state = $this->api->Send();
            
            if(!$state){
                return $this->api->status_mail['message'];              
            }
            else return false;
        }
	}
	$_GLOBALS['CUR_PROVIDER'] = new NativeProvider();
?>