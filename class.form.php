<?PHP

// include html builder
require_once("engine/classes/class.tags.php");

// common regex validation strings
define('REGEX_PHONE','~^\+?(\d|\s)+$~'); // +44123456789
define('REGEX_NUMBER','~^(\d+)\,?(\d*)\.?(\d*)$~'); // 1,000,000
define('REGEX_INTEGER','~^(\d+)$~'); // 1234596789
define('REGEX_DECIMAL','~^\d+(\.\d+)?$~'); // 123.456
define('REGEX_DATE','~^(19[0-9]{2}|2[0-9]{3})[\-](0[1-9]|1[0-2])[\-](0[1-9]|[12][0-9]|3[01])$~'); // 2015-12-31
define('REGEX_DATETIME','~^(19[0-9]{2}|2[0-9]{3})[\-](0[1-9]|1[0-2])[\-](0[1-9]|[12][0-9]|3[01]) ([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$~'); // 2015-12-31 23:59
define('REGEX_TIME','~^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$~'); // 23:59
define('REGEX_EMAIL',"/^((?:(?:(?:[a-zA-Z0-9][\.\-\+_']?)*)[a-zA-Z0-9])+)\@((?:(?:(?:[a-zA-Z0-9][\.\-_]?){0,62})[a-zA-Z0-9])+)\.([a-zA-Z0-9]{2,6})$/"); // user@example.com


class form
{
	public $name = null;
	public $attr = null;
	public $elements = array();

	public function __construct($name) {
		$this->name = $name;
		$this->attr = new tagAttr();
	}

	public function &field($name,$caption='Undefined',$type='Undefined',$value=null,$default=null)
	{
		// make a new field if the element is not found
		if(!isset($this->elements[$name]))
			$this->elements[$name] = new formField($name,$caption,$type,$value,$default);

		// return symlink to the field element
		return $this->elements[$name];
	}

	public function &message($name,$content='Undefined',$type='span',$attr=null)
	{
		// make a new field if the element is not found
		if(!isset($this->elements[$name]))
			$this->elements[$name] = new htmlTag($type,$attr,$content);

		// return symlink to the message element
		return $this->elements[$name];
	}


	function set_attr($key,$value=null){ return $this->attr->set($key,$value); }
	function get_attr($key=null){ return $this->attr->get($key); }
	function toggle_attr($key,$value=null,$force=null){ return $this->attr->toggle($key,$value,$force); }

	public function render()
	{
		$echo = array();
		$echo[] = "<div class='form {$this->name}'>";
		foreach($this->elements as $name=>$el)
		{
			$echo[] = "\t<div class='form_row {$name}'>";
			if(get_class($el) == 'formField') {
				if($el->error->content)
					$echo[] = "\t\t<div class='form_error {$name}'>{$el->error}</div>";
				$echo[] = "\t\t<div class='form_caption {$name}'>{$el->caption}</div>";
				$echo[] = "\t\t<div class='form_field {$name}'>$el</div>";
			}elseif(get_class($el) == 'htmlTag') {
				$msg = $el->render($el->content);
				$echo[] = "\t\t<div class='form_message {$name}'>$msg</div>";
			}
			$echo[] = "\t</div>";
		}
		$echo[] = "<div class='clear'></div>";
		$echo[] = "</div>";
		return implode("\n",$echo);
	}

	public function set_defaults($input)
	{
		foreach($this->elements as $name=>$field)
			if(isset($input[$name])) $field->default = $input[$name];
	}

	public function add_field($name,$caption,$type,$value=null,$default=null)
	{
		// create new formField instance
		return $this->field($name,$caption,$type,$value,$default);
	}

	public function add_message($name,$content,$type='span',$attr=null)
	{
		// create new message instance
		return $this->message($name,$content,$type,$attr);
	}

	public function remove_element($name)
	{
		// remove a formField instance
		if(isset($this->elements[$name]))
			unset($this->elements[$name]);
	}

	public function elements($type=null)
	{
		$output = array();

		// extract specific elements by type
		if($type != null) {
			foreach($this->elements as $k=>$e) {
				if(preg_match("~^($type)$~",$e->type))
					$output[$k] = $e;
			}
			return $output;
		}

		// just return all of the elements
		return $this->elements;
	}

	function set_mandatory($fields,$update=null)
	{
		if(strtolower($fields) == 'all')
			$fields = array_keys($this->elements);

		// split field list into an array
		else $fields = explode(' ', $fields);

		// if update param received action it
		if($update == true || $update == false)
			$update = $update ? true : false;

		// loop through each field and set mandatory flag
		foreach($fields as $name=>$f) {
			if(!isset($this->elements[$f])) continue;
			$f = $this->elements[$f];
			if(is_object($f) && get_class($f) == 'formField')
				$f->mandatory($update);
		}
	}

	function error_check()
	{
		$error = array();
		foreach( $this->elements as $k=>$v)
		{
			if(get_class($v) != 'formField') continue;
			if($v->type == 'submit') continue;
			if($v->type == 'reset') continue;
			if($v->type == 'button') continue;

			// matched incoming key with a name in the form fields
			// continue to run checks on this field
			$caption = $v->caption->content;
			$type = $v->type;
			$value = $v->default;
			$dataFormat = $v->dataFormat;
			$is_mandatory = $v->mandatory();
			$is_empty = empty($value);

			// check if a value was given
			if($is_mandatory && $is_empty && ($value != '0'))
				$error[$k] = "$caption has not been specified"; // $caption";

			// multiple choice error checks
			if($v->type == 'checkbox' && isset($v->default) )
				foreach($v->default as $ck=>$cv)
					if(!array_key_exists($cv,$v->value))
						$error[$k] = "$caption was set to an unknown value";

			else if($v->type == 'dropdown' && !array_key_exists($v->default,$v->value) && !empty($v->default))
				$error[$k] = "$caption was set to an unknown value '{$v->default}'";

			else if($v->type == 'radio' && !array_key_exists($v->default,$v->value))
				$error[$k] = "$caption was set to an unknown value '{$v->default}'";

			// test string against a regex pattern
			if(!empty($value) && $this->checkFormat($v) === false)
			{
				// use custom error or generic error message?
				if($v->dataFormatError) $error[$k] = $v->dataFormatError;

				// generic error messages...
				else switch($type) {
					case 'int': 		$error[$k] = "$caption does not appear to be a whole number; only digits are allowed, please do not seperate thousands with comma's. Expected format: 123"; break;
					case 'decimal': 	$error[$k] = "$caption does not appear to be a decimal number. Please do not seperate thousands with comma's. Expected format is: 123.45"; break;
					case 'date':		$error[$k] = "$caption does not appear to be a valid date. Expected YYYY-MM-DD"; break;
					case 'datetime':	$error[$k] = "$caption does not appear to be a valid date and time. Expected input YYYY-MM-DD HH:MM"; break;
					case 'time':		$error[$k] = "$caption does not appear to be a valid time. Expected input HH:MM"; break;
					case 'email':		$error[$k] = "$caption does not appear to be a valid email address"; break;
					default:		$error[$k] = "$caption does not appear to be valid"; break;
				}
			}

			// add class to caption for fields with an error
			if(isset($error[$k]))
			{
				$v->caption->toggle_attr('class','error',true);
				$v->caption->set_attr('data-error',$error[$k]);
				// $v->caption->content = ($error[$k]);
				$v->error->content = $error[$k];
				$v->toggle_attr('class','error',true);
				$v->set_attr('data-error',$error[$k]);
			}
		}

		return $error;
	}

	function checkFormat($el)
	{
		if(!$el->dataFormat) return true;
		if(preg_match($el->dataFormat,$el->default))
			return true;
		return false;
	}

	function __toString(){
		return $this->render();
	}
}


class formField
{
	public $name;
	public $type;
	public $caption;
	public $value;
	public $default;
	public $error;
	public $mandatory = false;
	// regex string for error validation:
	public $dataFormat = null;
	// if regex fails, show this message:
	public $dataFormatError = null;
	public $attr;

	function __construct($name,$caption,$type,$value=null,$default=null)
	{
		// save variables
		$this->name = $name;
		$this->type = $type;
		$this->caption = new htmlTag('span',null,$caption);
		$this->value = $value;
		$this->default = $default;
		$this->error = new htmlTag('span',null);

		// define common field attributes
		$this->attr = new tagAttr();

		$this->set_attr('id', "field_{$name}");
		$this->set_attr('name', $name);
		$this->set_attr('title', htmlentities($caption));
		$this->set_attr('type', $type);

		$this->default = $default;
		$this->value = $value;

		// add common regex validation
		switch($this->type){
			case 'int': $this->dataFormat(REGEX_INTEGER); break;
			case 'email': $this->dataFormat(REGEX_EMAIL); break;
			case 'datetime': $this->dataFormat(REGEX_DATETIME); break;
			case 'date': $this->dataFormat(REGEX_DATE); break;
			case 'time': $this->dataFormat(REGEX_TIME); break;
		}

	}

	function set_attr($key,$value=null){ return $this->attr->set($key,$value); }
	function get_attr($key=null){ return $this->attr->get($key); }
	function toggle_attr($key,$value=null,$force=null){ return $this->attr->toggle($key,$value,$force); }

	function dataFormat($regex=null,$error=null)
	{
		// return the data format?
		if(!$regex) return $this->dataFormat;
		// update the regex for data validation process
		$this->dataFormat = $regex;
		$this->dataFormatError = $error;
	}

	function mandatory($update=null)
	{
		// if update param received action it
		if($update !== null)
		{
			$this->mandatory = $update ? true : false;
			// add/remove mandatroy class to the field
			$this->toggle_attr('class','mandatory',$update);
			// add/remove mandatroy class to the caption too
			$this->caption->toggle_attr('class','mandatory',$update);
		}
		// return the current setting
		return $this->mandatory;
	}

	function render()
	{
		// specify the value to put in the field
		if($this->default && $this->value)
			$this->set_attr('value',$this->default);
		else if(!$this->default && $this->value)
			$this->set_attr('value',$this->value);
		else if($this->default && !$this->value)
			$this->set_attr('value',$this->default);
		else if($this->default && $this->value)
			$this->set_attr('value','');

		// encode the html chars
		if(!is_array($this->value) && !is_object($this->value))
			$encoded_value = htmlentities($this->get_attr('value'));

		// add class type 'input' to all form elements
		$this->toggle_attr('class', "input", true);

		// build the required element
		switch( true )
		{
			case $this->type == 'hidden':
				$tag = new htmlTag('input', $this->attr);
				$el = $tag->render();
			break;

			case $this->type == 'button':
				$this->attr->set('type', "submit");
				$tag = new htmlTag('button', $this->attr);
				$el = $tag->render($value);
			break;

			case $this->type == 'datetime':
				$tag = new htmlTag('input', $this->attr);
				// $this->set_attr('type','text'); // disable html5 date fields
				if(!$tag->get_attr('placeholder'))
					$tag->set_attr('placeholder',"YYYY-MM-DD HH:MM:SS"); // date('Y-m-d')
				$el = $tag->render();
			break;

			case $this->type == 'date':
				$tag = new htmlTag('input', $this->attr);
				// $this->set_attr('type','text'); // disable html5 date fields
				if(!$tag->get_attr('placeholder'))
					$tag->set_attr('placeholder',"YYYY-MM-DD"); // date('Y-m-d')
				$el = $tag->render();
			break;

			case $this->type == 'time':
				$tag = new htmlTag('input', $this->attr);
				// $this->set_attr('type','text'); // disable html5 time fields
				if(!$tag->get_attr('placeholder'))
					$tag->set_attr('placeholder',"HH:MM"); // date('H:i')
				$el = $tag->render();
			break;

			case $this->type == 'email':
				$this->set_attr('type','text');
				$tag = new htmlTag('input', $this->attr);
				if(!$tag->get_attr('placeholder'))
					$tag->set_attr('placeholder',"user@company.com");
				$el = $tag->render();
			break;

			case $this->type == 'submit':
				$tag = new htmlTag('input', $this->attr);
				$el = $tag->render();
			break;

			case $this->type == 'reset':
				$tag = new htmlTag('input', $this->attr);
				$el = $tag->render();
			break;

			case $this->type == 'file':
				$tag = new htmlTag('input', $this->attr);
				$el = $tag->render();
			break;

			case $this->type == 'password':
				$tag = new htmlTag('input', $this->attr);
				$el = $tag->render();
			break;

			case $this->type == 'textarea':
				$this->attr->del('value');
				$this->set_attr('cols',24);
				$this->set_attr('rows',5);
				$tag = new htmlTag('textarea', $this->attr);
				$el = $tag->render($encoded_value,true);
			break;

			case $this->type == 'dropdown':
				if(!is_array($this->value)) return false;

				$options = array();
				foreach( $this->value as $k=>$caption )
				{
					$optAttr = new tagAttr();
					if($this->default == $k)
						$optAttr->set("selected","selected");

					$optAttr->set("value",$k);
					$tag = new htmlTag('option', $optAttr);
					$options[] = $tag->render($caption);
				}

				$this->attr->del('value');
				$this->attr->del('type');

				$options = implode('',$options);
				$el = new htmlTag('select', $this->attr);
				$el = $el->render($options);
			break;

			case $this->type == 'radio':
			case $this->type == 'checkbox':
				$echo = array();
				$echo[] = "<ul class='{$this->type}'>";
				if(!is_array($this->value))	return false;
				foreach( $this->value as $k=>$caption )
				{
					// $optAttr = new tagAttr();
					$caption = htmlspecialchars($caption);
					$this->attr->set("title",$caption);
					$this->attr->set("value",$k);
					$lk = "{$this->name}_{$k}";

					// checkbox allows multiple results
					if($this->type == 'checkbox')
						$this->attr->set('name', "{$this->name}[]");

					// ensure defaults are selected
					if($this->type == 'radio')
					{
						// only 1 option can be the default
						if($this->default == $k)
							$this->attr->set("checked","checked");
						else $this->attr->del("checked");
					}
					else if($this->type == 'checkbox')
					{
						// multiple options can be the default
						if(is_array($this->default) && in_array($k,$this->default))
							$this->attr->set("checked","checked");
						else $this->attr->del("checked");
					}


					// concat field name with option key
					$this->attr->set("id",$lk);

					$tag = new htmlTag('input', $this->attr);
					$tag = $tag->render();

					$label = new htmlTag('label', array('for'=>$lk));
					$label = $label->render($caption);

					$echo[] = "<li>$tag $label</li>";
				}
				$echo[] = "</ul>";

				$el = implode("\n",$echo);
				unset($echo);
			break;

			default:
			case $this->type == 'text':
				$tag = new htmlTag('input', $this->attr);
				$el = $tag->render();
			break;

		}
		// return the rendered html element
		return $el;
	}

	function __toString(){
		return $this->render();
	}
}


