<?
	include 'class/providers/native-provider.php';
	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
	use Bitrix\Main\Mail;

	class BitrixProvider extends NativeProvider{
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
			parent::__construct();
            $this->templateStructName = "шаблона письма FormHandler или Bitrix";            
        }
        
        protected function __handle(){
            //глобальные параметры			
				
            $rsSites = CSite::GetByID(SITE_ID); 
            $arSite = $rsSites->Fetch();
            $siteFields = array();
            $siteFields['DEFAULT_EMAIL_FROM'] = $arSite['EMAIL'];

            if(is_null($this->site) || $this->site === "")
                $this->site 				= $arSite['SITE_NAME'];

            if(!isset($this->fields['SITE_NAME']) || $this->fields['SITE_NAME'] === "")
                $this->fields['SITE_NAME'] 	= $arSite['SITE_NAME'];

            if(is_null($this->charset) || $this->charset === "")
                $this->charset 				= $arSite['CHARSET'];
            
            if(is_null($this->from) || $this->from === "")
                $this->from = $arSite['EMAIL'];
            
            if(is_null($this->to) || $this->to === "")
                $this->to = $arSite['EMAIL'];
            
            if(is_null($this->template) || trim($this->template) === ""){
               //параметры шаблона Bitrix
                
                
               //стандартный шаблон не используется - извлекаем шаблон письма Bitrix (по идентификатору почтового события)
                $filter = array();
                $matches = null;
                if(preg_match('/([A-Z_a-z]+)\[([0-9]+)\]/u', $this->templateName, $matches) === 1){
                    //указан ID шаблона
                    $filter['TYPE_ID'] = $matches[1];
                    $filter['ID'] = $matches[2];
                    $rsMT = CEventType::GetList(["TYPE_ID" => $matches[1]]);
                }
                else{
                    //Указан ID типа
                    $filter['TYPE_ID'] = $this->templateName;
                    $rsMT = CEventType::GetList(["TYPE_ID" => $this->templateName]);
                }
                $filter['SITE_ID'] = SITE_ID;
                $filter["ACTIVE"]  = "Y";
				
                $rsME = CEventMessage::GetList($by="event_name",$order="asc",$filter);				
							
				
                //Собираем массив идентификаторов языков
                $lids = array();
                while(($arMT = $rsMT->Fetch()) !== false){
                    if(isset($lids[$arMT["ID"]]) === false)
                        $lids[$arMT["ID"]] = $arMT["LID"] . '-' . strtoupper($arMT["LID"]);
                }
                
                while(($arME = $rsME->Fetch()) !== false){                
					$arME = $arME + $this->fields + $siteFields;
									
					              
                	//патчим поля объекта (все свойства объекта не инициализированы, т.к. не используется стандартный шаблон)
					$this->subscription = CAllEvent::ReplaceTemplate($arME['SUBJECT'], $arME);
                    $this->from         = CAllEvent::ReplaceTemplate($arME['EMAIL_FROM'], $arME);
                    
                    if(strpos($arME['EMAIL_TO'],'#EMAIL_TO#') !== false && $this->to === ""){
                        throw new Exception('');
                    }
                    else if(strpos($arME['EMAIL_TO'],'#EMAIL_TO#') !== false || strpos($arME['EMAIL_TO'],'#DEFAULT_EMAIL_FROM#') !== false){ 
					    $this->to        = $arME['DEFAULT_EMAIL_FROM'];
                    }
                    else{
                        $this->to        = $arME['EMAIL_TO'];
                    }
                                        
					if($arME['CC'] !== "") //<--------------------------------------------------------------------------------- !!!!!!!!!!!!!!!!!!!!!!!!
						$this->copyTo       = $arME['CC'];
					
					if($arME['BCC'] !== "")
						$this->hideCopyTo   = $arME['BCC'];
					
					
					$this->importance   = $arME['PRIORITY'] !== "" ? $arME['PRIORITY'] : 3;
					$this->language     = $lids[$arME['EVENT_MESSAGE_TYPE_ID']];

					//патчим шаблон           
					$this->isText 		= $arME['BODY_TYPE'] === 'text';
					$this->template     = CAllEvent::ReplaceTemplate($arME['MESSAGE'], $arME);
                    
                    //запрашиваем вложения
                    $rsATT = Mail\Internal\EventMessageAttachmentTable::GetList(array('filter' => array('EVENT_MESSAGE_ID' => $arME['ID'])));
                    while(($arATT = $rsATT->Fetch()) !== false){
                        $fs = CFile::GetList(array("FILE_SIZE"=>"desc"), array('ID'=>$arATT["FILE_ID"]))->Fetch();
                        $this->attachments[$fs["ORIGINAL_NAME"]] = $fs["SUBDIR"] . "/" . $fs["FILE_NAME"];
					}
					
                    $this->fields = array();
                    
					return parent::__handle();
				}
            }
            else{				
				return parent::__handle();
			}
        }
	}

	$_GLOBALS['CUR_PROVIDER'] = new BitrixProvider();
?>