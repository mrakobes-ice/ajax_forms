<?
	
	//Используется для подстановки названия поля в сообщения (#label#)
	function fillLabel($string, $replacment, $placeholder){
		return str_replace("#$placeholder#", $replacment, $string);
	}

    class FormValidator{
        public $context = null;  
        public $uploadDir = "tmp/";
        private $_rules = null;
        private $_validators = null;
        
        public function __construct(){
            $this->context = new Dictionary();
            $this->_validators = new Dictionary();
            $this->_rules = new Dictionary();
			
			//Регистрируем основные валидаторы
			$this->AddValidator(new FileValidator());
			$this->AddValidator(new AnyValidator()); 
			$this->AddValidator(new RegexValidator());
			$this->AddValidator(new EmailValidator());
            $this->AddValidator(new PhoneValidator());
        }
                
        public function AddRuleTemplate(){
			$rule = new ValidationRule();
			
			if(gettype(func_get_arg(0)) == "string"){
				$field = func_get_arg(0);
			}
			else throw new Exception('Первым параметром должно задаваться название поля!');
					
			if(func_num_args() > 0){				
				if(func_num_args() == 1){
					throw new Exception("Укажите параметры правила для поля \"$field\"!");
				}
				else{					
					if(is_array(func_get_arg(1))){
						$params = func_get_arg(1);
						if(isset($params['required'])){
							$rule->required = $params['required'];
							unset($params['required']);
						}
						
						if(isset($params['label'])){
							$rule->label = $params['label'];
							unset($params['label']);
						}
						
						if(isset($params['associateID'])){
							$rule->associateID = $params['associateID'];
                            unset($params['associateID']);
						}

						if(isset($params['validators'])){
                            $rule->validators = $params['validators'];
                            unset($params['validators']);
                        }
						else throw new Exception("Дожен быть задан хотя-бы один валидатор!");
                        
                        if(isset($params['noValidMsg'])){
							$rule->noValidMsg = $params['noValidMsg'];
							unset($params['noValidMsg']);
						}
					}					
				}
				
				
				if(!$this->_rules->ExistsOf(func_get_arg(0)))
					$this->_rules->Add(func_get_arg(0),$rule);
			}
			
        }        
        public function AddRuleTemplates($param){
            if(is_array($param)){
				foreach($param as $key => $value){										
					if(gettype($key) == "string")
						 $this->AddRuleTemplate($key,$value);				
					else $this->AddRuleTemplate($value);				
				}				
			}
        }        
        public function RemoveRuleTemplate($param){
            $this->_rules->Remove($param);
        }
                
        public function AddContext(){
			
            $vc = new ValidationContext();
            
            if(func_num_args() > 0){                
                if(func_num_args() == 2 && !is_null(func_get_arg(0))){
                    if(gettype(func_get_arg(1)) === "string"){
                        $vc->contextField = func_get_arg(1);       
                    }
                }
				
				if(!isset($this->context[null]))
					 $this->context->Add(func_get_arg(0), $vc);
				else throw new Exception("Использование контекстов запрешено! используйте \"".$this->context[null]."\"...");
            }
        }     
        
        //Добавляет новый контекст, созданный на основе правил существующих контекстов (param2 = ['contex1','context2',...]).
        //При пересечении правил "побеждает" самое последнее правило, но можно явно задать какие правила переопределять нельзя (повысить приоритет):
        //
        //  param2 = ['contex1'=>[rule1,rule2,...],'context2',...]
        //
        public function AddContextComp(){
            $vc = new ValidationContext();
            
            $fixed = array();
            
			if(!isset($this->context[null])){
				if(func_num_args() == 2){                 
					foreach(func_get_arg(1) as $key => $value){

						//Процесс наложения (только правила)
						$k = gettype($key) === "string" ? $key : $value;
						if(isset($this->context[$k])){  
							if(gettype($key) === "string"){                              

								foreach($value as $vv){                            
									if(isset($this->context[$k]->_rules[$vv]) && !isset($fixed[$vv])){                                        
										$vc->_rules = $this->context[$k]->_rules[$vv];                                        
										$fixed[$vv] = 0;
									}                                     
								} 
							}
							else{
								$vc->_rules = $this->context[$k]->_rules;
							}
						}                        
					}
					$this->context->Add(func_get_arg(0), vc);                
				}
			}
			else throw new Exception("Использование контекстов запрешено! используйте \"" . $this->context[null] . "\"...");
        }        
        public function RemoveContext($param){
            $this->context->Remove($param);
        }
                
        public function AddValidator($param){ 
			if(!is_null($param) && is_subclass_of($param,'ValidatorBase'))
            	$this->_validators->Add($param->name,$param);
			else throw new Exception("Валидатор должен быть наследником \"ValidatorBase\" или \"FileValidatorBase\"!");
        }
        public function RemoveValidator($param){
            if(isset($param))
				$this->_validators->Remove($param);
        }
                
		public function GetCurrentContext(){
			if(!isset($this->context[null])){
				foreach($this->context as $key => $value){
					$c1 = "";
					if(isset($_POST[$value->contextField]) && !$value->allowGet){
						$c1 = $_POST[$value->contextField];
					}
					else if(isset($_GET[$value->contextField]) && $value->allowGet){
						$c1 = $_GET[$value->contextField];
					}

					if($c1 === $key){						
						$c = $key;
						break;
					}
				}
			}
			else $c = null;
			
			return $c;
		}
		
        /** Проверяет поля формы
          * Возвращает результат валидации в виде JSON-строки...
          
            При успешной проверке:
                {
                    "code":"0",
                    "message":"Success...",
                    "errors":null
                }
                
            Если были найдены ошибки:
                {
                    "code":"1",
                    "message":"full description",
                    "errors":{
                        "field1":"field is required!",
                        "field2":"blablabla",
                        "field3":"blablalba",
                        ...
                    }
                }
        */
        public function Valid($_c = ""){
            $result = [
                "status" => "success",
                "data" => array()
            ];
            //Валидация полей
			
		    //1. Определение текущего контекста		   
		    if($this->context->Count() > 0){
				$c = $this->context[$_c !== "" ? $_c : $this->GetCurrentContext()];			
				
				if(!is_null($c)){
					
					//2. Перебираем все правила проверки в контексте
                    $validated      = array();   //проверенные роли
                    $attachments    = array();
                    $associated     = array();  //псевдонимы для полей (псевдонимы используются в шаблонах писем)
                    $is_end = false;
                    					
					do{	
                        $val_t = $c->_rules->current();						
						$field = $key = $c->_rules->key();
                        
						if(($c->_rules->next()) !== false){
                            /*  1. Сохраняем текущее кол-во валидных ролей и сравниваем его с текущим в конце полного перебора ролей;
                                2. Если кол-во валидных ролей изменилось - проводим обработку снова;
                                3. В противном случае - выходим из цикла (что означает ошибку обработки любой из ролей). */
                            
							$vt_count = count($validated); //кол-во проверенных полей
                            $is_end = false;
						}
                        else{
                            $c->_rules->rewind();
                            $is_end = true;
                        }
                        
                        if(is_null($val_t)){												
                             if($this->_rules->ExistsOf($key)){
                              	 //Правило не задано явно - наследуем все существующее шаблонное правило
								 $rule = $this->_rules[$key];
							 }
                             else throw new Exception("Не найдено подходящего шаблонного правила для поля \"". $key . "\"! задайте правило явно или создайте шаблонное правило...");								
                        }
                        else{	
							//Правило есть... проверяем существование шаблонного правила
							if($this->_rules->ExistsOf($key)){
								//Шаблонное правило есть - организуем слияние свойств правил и валидаторов
								$rule = $val_t;
								$rule->fillProps($this->_rules[$key]);	
							}
							else{
								//Шаблонного правила нет - правило контекста уникально...
								$rule = $val_t;
							}				
						}  
						
						$required = $rule->required;
						$label = is_null($rule->label) ? $field : $rule->label;
						$value = null;
                        
                        if(is_array($rule->validators)){
                            foreach($rule->validators as $k => $v){
                                $val_name = gettype($k) === "integer" ? $v : $k;

                                if($this->_validators[$val_name] instanceof FileValidator){
                                    $rule->isFile = true;
                                    break;
                                }
                            }
                        }
                        else $rule->isFile = $this->_validators[$rule->validators] instanceof FileValidator;
                                                
						//3. Обнаружение поля
                        if(!$rule->isFile){

                            if(isset($_POST[$field]) && !$c->allowGet){
                                $value = $_POST[$field];
                            }
                            else if(isset($_GET[$field]) && $c->allowGet){
                                $value = $_GET[$field];
                            }
                        }
                        else $value = $_FILES[$field];


                        //4. Проверка на Required
                        $_tvalue = ((!isset($_POST[$field]) || $_POST[$field] === "") && (!isset($_GET[$field]) || $_GET[$field] === "") && (!isset($_FILES[$field]) || $_FILES[$field]['size']===0));
						if($required && $_tvalue){
							//!!!!!!!!!!! ERROR FIELD IS REQUIRED!!!!!!!!!!!!!!!!!!
							
                            $result['status'] = 'fail';
                            $result['data'][$field] = fillLabel($c->isRequiredMsg, (count($rule->label) > 0 ? $rule->label : $field), "label");
                            
							unset($c->_rules[$key]);
							continue;
						}
						else if(!$required && $_tvalue){
                            unset($c->_rules[$key]);
							$validated[$key] = '[не указан]';
							
							if(!$rule->isFile && $rule->associateID !== "") 
								$associated[$field] = $rule->associateID;
							
							continue;                            
                        }
                        
						//5. Определение состояния
						if(isset($c->checkState) && is_callable($c->checkState)){
							$state = null;
							$state = $c->checkState($validated);
						}
						
						//6.1 Определение набора валидаторов
                        if(is_callable($rule->validators)){
                            $tm = $rule->validators($state);
                            if(!is_null($tm)){
								$rule->_vsbackup = $rule->validators;
								$rule->validators = $tm;
							}
                        }         
                        
                                                
						//6.2 Перебор валидаторов правила
                        if(!is_callable($rule->validators)){
							$is_valid = true;
							
							if(gettype($rule->validators) !== "string"){
								foreach($rule->validators as $validator => $params){ 

									//Определение и вызов валидатора
									if(gettype($validator) === "string"){
										//с заданными параметрами (переопределение)
										if($rule->isFile){
											$params['uploadDir']  = is_null($c->uploadDir) ? $this->uploadDir : $c->uploadDir;
										}             
										$r = $this->_validators[$validator]->__exec($value, $params);
									}
									else{ 
										//со стандартными параметрами
										$p = array();
										if($rule->isFile){
											$p['uploadDir']       = is_null($c->uploadDir) ? $this->uploadDir : $c->uploadDir;
										}

										$r = $this->_validators[$params]->__exec($value, $p);
									}                                                 

									if(gettype($r) == "array"){
										//!!!!!!!!!!!!!! VALIDATION ERROR !!!!!!!!!!!!!!!!

										$result['status']         = 'fail';
										$result['data'][$field]   = fillLabel($r, (count($rule->label) > 0 ? $rule->label : $field),"label");
										$is_valid                 = false;
									}	                            
								}
							}
							else{
								//Один валидатор без параметров								
								
								$p = array();
								if($rule->isFile){
									$p['uploadDir']               = is_null($c->uploadDir) ? $this->uploadDir : $c->uploadDir;
								}

								$r = $this->_validators[$rule->validators]->__exec($value, $p);			
                                
								if(gettype($r) == "array"){
									//!!!!!!!!!!!!!! VALIDATION ERROR !!!!!!!!!!!!!!!!

									$result['status']             = 'fail';
									$result['data'][$field]       = fillLabel($r, (count($rule->label) > 0 ? $rule->label : $field),"label");
									$is_valid                     = false;
								}	
							}						
							
							if(!$is_valid){
								unset($c->_rules[$key]);
								continue;		
							}
							
                        }
						else continue;
                        
						unset($c->_rules[$key]);
                        $validated[$field] = $value;
                        
                        if($rule->isFile){
                            $attachments[$value['name']] = $this->_validators[$rule->validators]->__path;
                        }
                        else if($rule->associateID !== "") 
							$associated[$field] = $rule->associateID;							
					}
					while($c->_rules->Count() > 0 && !($is_end && $vt_count === count($validated)));
                                    
                    if($result['status'] == 'fail'){
                        $result['message']      = fillLabel($c->errorMsg, count($result['errors']),"err-count");
                    }
                    else{
                        $result['data']         = $validated;
                        $result['message']      = $c->successMsg;
                        $result['attachments']  = $attachments;
                        $result['associations'] = $associated;
                    }
				}
				else return null;
		    }
			else return null;
            
            return $result;
        }        
    }

    //Внутрениий класс правила валидации поля
    final class ValidationRule{        
        public $required = null;
		public $label = null;
        public $isFile = null;  
		public $associateID = null; //идентификатор, используемый в пользовательских шаблонах
        public $validators = array(); //массив валидаторов или функция возвращающая массив|null
        public function __construct(){ }
		public function fillProps($rule){
			if(is_null($rule->required) && is_null($this->required)){
				$this->required = false;
			}
			else if(!is_null($rule->required) && is_null($this->required)){
				$this->required = $rule->required;
			}
			
			
			if(!is_null($rule->label) && is_null($this->label)){
				$this->label = $rule->label;
			}
			
			
			if(is_null($rule->isFile) && is_null($this->isFile)){
				$this->isFile = false;
			}
			else if(!is_null($rule->isFile) && is_null($this->isFile)){
				$this->isFile = $rule->isFile;
			}
			
			
			if(is_null($rule->associateID) && is_null($this->associateID)){
				$this->associateID = "";
			}
			else if(!is_null($rule->associateID) && is_null($this->associateID)){
				$this->associateID = $rule->associateID;
			}
			
			
			if(count($rule->validators) !== 0 && count($this->validators) === 0){
				$this->validators = $rule->validators;
			}
			else if(count($rule->validators) !== 0 && count($this->validators) !== 0){
				//Переопределение отдельных свойств валидатора
								
				foreach($rule->validators as $k => $v){
					if(!isset($this->validators[$k])){
						$this->validators[$k] = $v;
					}
					else{
						$this->validators[$k] = array_merge($v, $this->validators[$k]);
					}					
				}
			}		
		}
    }

    final class ValidationContext{		
		public $description = "";																		//Строковое описание контекста (например, "Форма заказа прайса")
        public $allowGet = false;                                                                       //Разрешено ли в этом контексте получать поля методом GET
        public $isRequiredMsg = "The field \"#label#\" is Required!";                                 //Стандартное сообщение об ошибке, получаемое при обнаружении отсутствующего "required" поля
        public $errorMsg = "This form is not valid! Check fields[#err-count#] and try again...";      //Общее сообщение об ошибке валидации формы
        public $successMsg = "Form successfully sent!";                                                 //Сообщение при успешной проверке формы
        public $contextField = "form-id";                                                               //Название поля, в котором находится название контекста
        public $uploadDir = null;
        public $_rules = null;
		public $additionals = "";
        
        public function __construct(){
            $this->_rules = new Dictionary();
        }
        
        public function AddRule(){
			$rule = new ValidationRule();
			
			if(gettype(func_get_arg(0)) == "string"){
				$field = func_get_arg(0);
			}
			else throw new Exception('Первым параметром должно задаваться название поля!');
					
			if(func_num_args() > 0){				
				if(func_num_args() > 1){					
					if(is_array(func_get_arg(1))){
						$params = func_get_arg(1);
						
						if(isset($params['required'])){
							if(gettype($params['required']) === 'string')
								$rule->required = $params['required'] === 'true' ? true : false;
							else $rule->required = (bool) $params['required'];
						}
						
						if(isset($params['label'])){
							$rule->label = $params['label'];
						}
						
						if(isset($params['associateID'])){
							$rule->associateID = $params['associateID'];
						}
                        
						if(isset($params['validators'])){     
							$rule->validators = $params['validators'];
						}
						//else throw new Exception("Дожен быть задан хотя-бы один валидатор!");                        
                        if(isset($params['noValidMsg'])){
							$rule->noValidMsg = $params['noValidMsg'];
						}
					}
					
					if(!isset($this->_rules[func_get_arg(0)]))
						$this->_rules->Add(func_get_arg(0),$rule);
					else $this->_rules[func_get_arg(0)] = $rule;
				}
				else{
					if(!$this->_rules->ExistsOf(func_get_arg(0)))
						$this->_rules->Add(func_get_arg(0),null);
				}			
			}
        }        
        public function AddRules($param){
            if(is_array($param)){
				foreach($param as $key => $value){										
					if(gettype($key) == "string")
						 $this->AddRule($key,$value);				
					else $this->AddRule($value);				
				}				
			}
        }        
        public function RemoveRule($param){
            $this->_rules->Remove($param);
        }     
    }

    class Dictionary implements ArrayAccess, Iterator{
        public $__content = array();
		public $itemTypeValidator = null;
        
        //array access
        public function offsetSet($offset, $value){
            if(!is_null($offset) && isset($offset)){
                if(isset($this->__content[$offset])){
                    $this->__content[$offset] = $value;
                }
                else{
                    //Не существует (не добавляем динамически! только через add())
                    throw new Exception("Такого ключа не существует!");
                }
            }
		}
		public function offsetGet($offset){
			return isset($this->__content[$offset]) ? $this->__content[$offset] : null;
		}
		public function offsetExists($offset){
			return isset($this->__content[$offset]);
		}
		public function offsetUnset($offset){
			unset($this->__content[$offset]);
		}

        //iterator
		public function rewind(){
			reset($this->__content);
		}
		public function current(){
			return current($this->__content);
		}
		public function key(){
			return key($this->__content);
		}
		public function next(){
			return next($this->__content);
		}
		public function valid(){
			$key = $this->key();
			return ($key !== NULL && $key !== false);
        }
                
        //Dictionary methods
        public function Add($key,$value){           
			if(!isset($this->__content[$key])){
				if(!is_null($this->itemTypeValidator) && is_callable($this->itemTypeValidator)){
					if(call_user_func($this->itemTypeValidator,$value) === false){
						throw new Exception("Добавляемый элемент имеет недопустимый тип данных!");
						die();
					}
				}

				$this->__content[$key] = $value;
			}
			else{
				throw new Exception("Элемент с таким ключем уже существует!");
				die();
			}  
        }
        public function Remove($key){
            if(isset($key) && !isset($this->__content[$key]))
                unset($this->__content[$key]);
        }
		public function Count(){
			return count($this->__content);
		}
        public function Join($source){
            if(is_array($source)){
                $this->__content = array_merge($this->__content, $source);
            }
            else if($source instanceof Dictionary){
                $this->__content = array_merge($this->__content, $source->__content);
            }
        }        
		public function ExistsOf($key){
			return isset($this->__content[$key]);
		}
		public function Contains($value, $strict = false){
			return in_array($value, $this->__content, $strict);
		}
    }
	

	
	//Валидаторы
    abstract class ValidatorBase{

		public $name = "base"; //уникальный идентификато под которым валидатор регистрируется в "реестре валидаторов"  
		
		//Блок обработки параметров "по умолчанию":
        public $def_options = array('noValidMsg' => "The field \"#label#\" is not valid!");
        public function __set($name, $value){
	        $this->def_options[$name] = $value;	   
		}
        public function __get($name){
			
			if(array_key_exists($name, $this->def_options)){
				return $this->def_options[$name];
			}
	
			$trace = debug_backtrace();
			trigger_error("Undefined property: $name in file " . $trace[0]['file'] . " on line " . $trace[0]['line'],  E_USER_NOTICE);
	   		return null;
		}
		public function __isset($name){
			return isset($this->def_options[$name]);
		}
		public function __unset($name){
			unset($this->def_options[$name]);
		}
			
        
		
		
        protected abstract function validate($value); //основная логика валидации
        public function __exec($value, $params){
            $result = false;
            $this->def_options = array_merge($this->def_options, $params);
            
            //Массив возвращается т.к. поля могут быть сгруппированы по названию (как в случае radio/checkbox)
            if(!is_array($value)){
                $result = $this->validate($value);
                
                if($result !== false) $result = array($result);
            }
            else{                
                foreach($value as $field){                    
                    if(($result2 = $this->validate($field)) !== false){
                        if($result === false) $result = array();
                        
                        $result[] = $result2;
                    }                    
                } 
            }
            return $result;
        }

        //Exec - основная функция валидатора, должна возвращать false (ОК) или описание ошибки
    }

    //Проверяет вложенный файл

    class FileValidator extends ValidatorBase{ 
		public function __construct(){ 
			$this->name = "attach";			
			$this->extensions = ['jpg','jpeg','png','gif','doc'];
			$this->maxSize = 1024*3*1024;     //В байтах
			$this->uploadDir = "tmp/";
			$this->contentValidationCallback = null;  //function($filePath)
            $this->noValidMsg = "Файл \"#FILE_NAME#\" имеет недопустимый формат!";
		}

        protected function validateFile($name, $tmp_name, $type, $error){
            $pathinfo = pathinfo($name);
            $result = false;

            //Проверка расширения			
            $ext = strtolower($pathinfo['extension']);
            if(!in_array($ext, $this->extensions)){
                $result = fillLabel($this->noValidMsg, $name, "FILE_NAME");
            }
			
			if($result === false){
				$this->__path = $this->uploadDir . md5($name . microtime(true)) . "." . $ext;
                
				if(!move_uploaded_file($tmp_name, $this->__path)){
					$result = "Файл \"" . $name . "\" не сохранен!";
				} 
								
				if(!$result && !is_null($this->contentValidationCallback)){
					$result = $this->contentValidationCallback($this->__path);
				}
			}
			return $result;
        }
		
		protected function validate($value){			            
            $result = false;
            //$this->def_options = array_merge($this->def_options, $params);
            $size = 0;
                        
            if(!is_array($value['name'])){
                $size = $value['size'];
                $result = $this->validateFile($value['name'],$value['tmp_name'],$value['type'],$value['error']);
                
                if($result !== false) $result = array($result);
            }
            else{
                for($i = 0; $i < count($value['name']); $i++){       
                    $size += $value['size'][$i];
                    if(($result2 = $this->validateFile($value['name'][$i],$value['tmp_name'][$i],$value['type'][$i],$value['error'][$i])) !== false){
                        if($result === false) $result = array();
                        
                        $result[] = $result2;
                    }                    
                } 
            }
            
            //Проверка на максимальный размер вложений					
            if($result === false && $size > $this->maxSize)
            {
                $result = array(fillLabel("Размер прикрепленного файла \"".$value['name']."\" превышает допустимый (#maxsize# байт) !", $this->maxSize, "maxsize"));
            }
            
            return $result;
		}
        
        public function __exec($value, $params){
            $this->def_options = array_merge($this->def_options, $params);
            return $this->validate($value);      
        }
    }
    
    class RegexValidator extends ValidatorBase{
        public function __construct(){ 
			$this->name = "regex";
			
			$this->pattern = "";
            $this->noValidMsg = 'Поле "#label#" имеет неверный формат!';           
		}	
        
        protected function validate($value){
            return !!preg_match("/$this->pattern/u",$value) ? false : $this->noValidMsg;
        }
    }

    class AnyValidator extends ValidatorBase{
        public function __construct(){ 
			$this->name = "any";         
		}	
        
        protected function validate($value){
            return false;
        }
	}

    class EmailValidator extends RegexValidator{
        public function __construct(){ 
			$this->name = "email";
			
			$this->pattern = "^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$";
            $this->noValidMsg = "Введеный E-Mail адрес недействителен!";            
		}	
        
        protected function validate($value){
            return parent::validate($value);
        }
    }
    
    class PhoneValidator extends RegexValidator{
        public function __construct(){ 
			$this->name = "phone";
			
			$this->pattern = "\+7 \([0-9][0-9][0-9]\) [0-9][0-9][0-9]\-[0-9][0-9]\-[0-9][0-9]";
            $this->noValidMsg = "Введеный номер телефона имеет неверный формат!";            
		}	
        
        protected function validate($value){
            return parent::validate($value);
        }
    }

    //rangeValidator
    //enumValidator
?>