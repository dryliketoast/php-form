<?PHP

class htmlTag
{
	public $type;
	public $attr;
	public $content;

	function __construct($type,$attr=null,$content=null)
	{
		$this->type = $type;
		$this->content = $content;
		if(is_object($attr) && get_class($attr) == 'tagAttr')
			$this->attr = $attr; // incoming data is already a tagAttr object
		else $this->attr = new tagAttr($attr); // pass incoming data to new tagAttr instance
	}

	function render($inject=false,$force=false)
	{
		$n = "$this->type";
		$a = "$this->attr";
		if($this->content && !$inject)
			$content = $this->content;
		else if(!$this->content && $inject)
			$content = $inject;
		else if($this->content && $inject)
			$content = $inject;
		else $content = null;

		if($a) $a = " $a";
		if($content || $force)
			return "<{$n}{$a}>{$content}</{$n}>";
		return "<{$n}{$a} />";
	}

	function set_attr($key,$value=null){ return $this->attr->set($key,$value); }
	function toggle_attr($key,$value=null,$force=null){ return $this->attr->toggle($key,$value,$force); }
	function get_attr($key=null){ return $this->attr->get($key); }

	function __toString() {
		return $this->render();
	}
}

class tagAttr
{
	private $data;

	function __construct($attr=false)
	{
		// push incoming array attributes to memory
		if(is_array($attr)) $this->set($attr);
	}

	function toggle($k,$v,$force=null)
	{
		$d = $this->get($k);
		if(!is_array($d) && !empty($d))
			$d = explode(' ',$d);
		elseif(empty($d)) $d = array();

		if(in_array($v,$d)) {
			// item was found: remove it
			if($force === null || $force !== true)
			foreach($d as $kk=>$vv)
				if($vv == $v) unset($d[$kk]);
		}else{
			// item not found: add it
			if($force === null || $force !== false)
			$d[] = $v;
		}
		// update the instance
		$this->set($k,$d);
	}

	function set($k,$v=null)
	{
		if(is_array($k)) {
			foreach($k as $kk=>$vv)
				$this->set($kk,$vv);
			return true;
		}
		return $this->data[$k] = $v;
	}

	function get($k=false) { return $k ? @$this->data[$k] : $this->data; }
	function del($k) { unset($this->data[$k]); }

	function render()
	{
		$o = array();
		if(!empty($this->data)) {
			foreach($this->data as $k=>$v) {
				if(is_array($v)) $v = trim(implode(' ',$v));
				// if(empty($v)) continue;
				$v = htmlentities($v);
				$o[] = "{$k}=\"{$v}\"";
			}
		}
		return implode(' ',$o);
	}

	function __toString() {
		return $this->render();
	}
}


