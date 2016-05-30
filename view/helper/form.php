<?php 
require_once "service/validateUtil.php";
Class form extends appHelper
{
	private $message;
	protected $modelName = "default";
	private $modelObj;
	private $status = 0; //0 vide, 1 error, 2 valid required, 3 valid
	protected $champsObligatoires = array();
	
	public function loadEditor($html){ //Fonction a appeller si on veut utiliser l'editeur
		$html->addJs('wysihtml5/wysihtml5-0.3.0');
		$html->addJs('wysihtml5/parser_rules/advanced');
		$html->addCss('wysihtml5/WYSIHTML5');
		$html->addCss('wysihtml5/stylesheet');
		$html->addCss('http://yui.yahooapis.com/2.9.0/build/reset/reset-min');
		
	}
	private function getPost(){
		$class_name_len = strlen($this->modelName);
		
		foreach($_POST as $post_key => $val){
			//Cas particulier : recuperation du hidden correspondant au submit enfonce etape2
			if(substr($val, 0, 10)  == 'FormSubmit' && strpos($post_key, '#')!=false ){
				Logger::tracerErreur('Err : ' . $val);
				$laSubmitHidden=explode('#', $post_key);
				$hidval = $_POST['submit_'.$this->modelName.'_'.$laSubmitHidden[1].'#'.$laSubmitHidden[2]];
				$hidpost_key=$laSubmitHidden[1];
				$this->modelObj->$hidpost_key = $hidval;
				$this->status = 1;
				$post_key = $laSubmitHidden[0];
			}
			
			//On récupère les données que du form ayant le meme nom de modèle grace au préfixage
			if($this->modelName == substr($post_key,0,$class_name_len)){			
				$attributName = str_replace($this->modelName."_","",$post_key);								
				$this->modelObj->$attributName = $val;
				$this->status = 1;
			}//else echo "<p> attribut d'un autre formulaire ignor&eacute;  : <br />".$post_key." (mettre le meme model pour le form que pour le result !)</p>";
			
		}
	}
	public function getValidForm(){
		if($this->status == 0) return false;
		$datas = $this->modelObj->getData();
		
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			Logger::tracerdebug('Envoi de form : ajaxRequest');
		}elseif (!isset($datas['submit']) || $datas['submit'] != 'FormSubmit'){
			Logger::tracerdebug('Valide Form : pas de submit, donc pas d\'envoi');
			return false;
		}
		unset($datas['submit']);
		$validForm = validate_util::isValide($this->modelObj);
		
		if($validForm)						
			if($this->modelObj->isValid()){
				$this->status = 3;
			}else{
				$this->status = 2;
			}
			
		$result =  !$datas||$this->status != 3 ||!$validForm?false:$datas;
		if($result != false && $this->modelObj instanceof formServiceInterface ){
			$this->modelObj->saveValidForm();
		}
		return $result;
	}
	public function setMessage($name, $val){
		validate_util::setMessage($name, $val);
	}

	public function setModel($model){
		$this->modelObj = $model;
		$this->champsObligatoires = $this->modelObj->validates; //Permet la validation par HTML5 des champs non remplis, fonctionnalite desactive si jsController utilise
		$this->modelName = get_class($model);
		$this->getPost();
	}

	public function openForm($argument = array()){
		if(!isset($argument['id']))
			$argument['id'] = 'form_'.$this->modelName;
			
		$formArg['content']= '';
		foreach ($argument as $clef => $val){
			$formArg['content'] .= $clef.'="'.$val.'" ';
		}
		echo '<form '.$formArg['content'].'>';
	}
	public function end(){
		return '</form>';
	}
	
	public function getValidates($champ)
	{
		
	}
	public function getListArguments($elem,$arguments = array()){
		/*$data = $this->modelObj->getData();
		if(isset($data[$elem])) $arguments['value'] = $data[$elem];*/
		if(is_array($arguments));
		$list = "";
		foreach($arguments as $key => $argument){
			if($argument!=null && $key != 'option' && $key != 'label')
				$list .= $key.'="'.$argument.'" ';
		}
		return substr($list, 0, -1);
	}
	public function label($name,$label){
		if(isset($label)){
			//$name=str_replace($this->modelName.'_', '', $name);
// 			$name = $this->modelName.'_'.$name;
			return '<label id="'.$this->modelName.'_'.$name.'Label" for="'.$this->modelName.'_'.$name.'">'.$label.'</label>';
		}
	}
	public function message($name){
		if($this->status == 2)
		return validate_util::getMessage($name);
		else
		return "";
	}
	protected function messageHtml($name){
		$validError = validate_util::getMessage($name);
		$message = null;
		if(isset($validError)){
			$message = "<div class='required' id='".$this->modelName."_".$name."Message' >".$validError."</div>";
			//$message = "<output for='".$this->modelName."_".$name."' class='required' id='".$this->modelName."_".$name."Message' >".$validError."</output>";
		}
		return $message;
	}
	public function textarea($name, $arguments = array()){
		$value = '';
		if(isset($arguments['value'])){
			$value = $arguments['value'];
			unset($arguments['value']);
		}else
			$value = $this->modelObj->$name;
		if(isset($this->champsObligatoires [$name])){
			$arguments['required'] = 'required';
		}
		$arguments['id'] = $this->modelName."_".$name;
		
		
		$argumentList = $this->getListArguments($name,$arguments);
		$label = isset($arguments['label']) ? $this->label($name,$arguments['label']) : '';
		$textarea =  $label."<textarea name='".$this->modelName."_".$name."' $argumentList>".$value."</textarea>";
        $textarea .= $this->messageHtml($name);
		return $textarea;
	}
	public function areaeditor($name, $arguments = array(), $loadJs = true){
		$arguments['id'] = $this->modelName."_".$name;
		
		$res = '';
		if(isset($arguments['toolbar'])){
			if ($arguments['toolbar'] == true){		
				$res .= '<div id="wysihtml5-editor-toolbar">
				<header>
				<ul class="commands">
				<li data-wysihtml5-command="bold" title="Make text bold (CTRL + B)" class="command"></li>
				<li data-wysihtml5-command="italic" title="Make text italic (CTRL + I)" class="command"></li>
				<li data-wysihtml5-command="insertUnorderedList" title="Insert an unordered list" class="command"></li>
				<li data-wysihtml5-command="insertOrderedList" title="Insert an ordered list" class="command"></li>
				<li data-wysihtml5-command="createLink" title="Insert a link" class="command"></li>
				<li data-wysihtml5-command="insertImage" title="Insert an image" class="command"></li>
				<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h1" title="Insert headline 1" class="command"></li>
				<li data-wysihtml5-command="formatBlock" data-wysihtml5-command-value="h2" title="Insert headline 2" class="command"></li>
				<li data-wysihtml5-command-group="foreColor" title="Color the selected text" class="fore-color command">
				<ul>
				<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="silver"></li>
				<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="gray"></li>
				<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="maroon"></li>
				<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="red"></li>
				<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="purple"></li>
				<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="green"></li>
				<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="olive"></li>
				<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="navy"></li>
				<li data-wysihtml5-command="foreColor" data-wysihtml5-command-value="blue"></li>
				</ul>
				</li>
				<li data-wysihtml5-command="insertSpeech" title="Insert speech" class="command"></li>
				<li data-wysihtml5-action="change_view" title="Show HTML" class="action"></li>
				</ul>
				</header>
				<div data-wysihtml5-dialog="createLink" style="display: none;">
				<label>
				Link:
				<input data-wysihtml5-dialog-field="href" value="http://">
				</label>
				<a data-wysihtml5-dialog-action="save">OK</a>&nbsp;<a data-wysihtml5-dialog-action="cancel">Cancel</a>
				</div>
				
				<div data-wysihtml5-dialog="insertImage" style="display: none;">
				<label>
				Image:
				<input data-wysihtml5-dialog-field="src" value="http://">
				</label>
				<a data-wysihtml5-dialog-action="save">OK</a>&nbsp;<a data-wysihtml5-dialog-action="cancel">Cancel</a>
				</div>
				</div>';
			}
			unset($arguments['toolbar']);
		}		
		$res .= $this->textarea($name, $arguments);
		if($loadJs)
			$res .= '<script>
				var editor = new wysihtml5.Editor("'.$arguments['id'].'", {
					toolbar:     "wysihtml5-editor-toolbar",
					// stylesheets: ["http://yui.yahooapis.com/2.9.0/build/reset/reset-min.css", "css/editor.css"],
					parserRules: wysihtml5ParserRules
				});				
				editor.on("load", function() {
					var composer = editor.composer;
					composer.selection.selectNode(editor.composer.element.querySelector("h1"));
				});
			</script>';
		return $res;
	}
	public function select($name,$arguments = array()){
		$select = '';
		if(isset($arguments['label'])){
			$select = $this->label($name,$arguments['label']);
		}
		$option = $arguments['option'];
		unset($arguments['option']);
		$argumentList = $this->getListArguments($name,$arguments);
		$select .= "<select name='".$this->modelName."_".$name."' id='".$this->modelName."_".$name."' $argumentList >";
		$data = $this->modelObj->getData();
		$selectionName = isset($data[$name])?$data[$name]:false;
		$i = 0;
		foreach($option as $key => $option){
			if(is_array($option)){ //Cas liste avec categories dans les données en entrée
				$select .= '<optgroup label="'.$key.'">';
				foreach($option as $key => $option){
					$select .= $this->getOptionStr($i.'_'.$key, $option, $i.'_'.$key == $selectionName || (isset($arguments['selected']) and $arguments['selected'] == $i.'_'.$key));
				}
				$select .= '</optgroup>';
			}else
				$select .= $this->getOptionStr($key, $option, $key == $selectionName || (isset($arguments['selected']) and $arguments['selected'] == $key));
			$i++;
		}
		$select .= "</select>";
		return $select;
	}
	
	private function getOptionStr($key, $option, $selected = false){		
		if($selected){
			return '<option value='.$key.' selected='.$key.'>'.$option.'</option>';
		}
		else{
			return '<option value='.$key.' >'.$option.'</option>';;
		}
	}
	public function input($name,$arguments = array()){
		if(!isset($arguments['value'])){
			$arguments['value'] = $this->modelObj->$name ;
		}
		if(!isset($arguments['id'])){
			$arguments['id'] = $this->modelName."_".$name;
		}
		if(!isset($arguments['type'])){
			$arguments['type'] = 'text';
		}else if($arguments['type'] == 'date' && is_object($arguments['value']) && (get_class($arguments['value']) == 'Date' ||  get_class($arguments['value']) == 'DateTime')){
			$arguments['value'] = $arguments['value']->format('Y-m-d');
		}
		if(isset($this->champsObligatoires [$name])){
			$arguments['required'] = 'required';
		}	
		
		$argumentList = $this->getListArguments($name,$arguments);
		$label = isset($arguments['label']) ? $this->label($name,$arguments['label']) : '';
			
		$input = $label."<input name='".$this->modelName."_".$name."' $argumentList/>";
		$input .= $this->messageHtml($name);
		return $input;
	}
	public function password($name,$arguments = array()){
		if(isset($this->champsObligatoires [$name])){
			$arguments['required'] = 'required';
		}
		$argumentList = $this->getListArguments($name,$arguments);
		$label = isset($arguments['label']) ? $this->label($name,$arguments['label']) : '';
		$input = $label."<input type='password' name='".$this->modelName."_".$name."' id='".$this->modelName."_".$name."' $argumentList />";
		$input .= $this->messageHtml($name);
		return $input;
	}
	public function  submit($label,$arguments = array()){		
// 		return "<input type='submit' value='$name' $argumentList />";
		$lshide='';$lsName='';
		//Cas Particulier ... TODO
		if(isset($arguments['name'])){
			if(!isset($arguments['value']))$arguments['value']="";
			$lsName=$arguments['name'].'#'.rand(1,100);
			$lshide='<input type="hidden" name="submit_'.$this->modelName.'_'.$lsName.'" value="'.$arguments['value'].'" />';
			$lsName='#'.$lsName;
		}
		
		$arguments['value']='FormSubmit';
		$arguments['id'] = $this->modelName."_submit";
		$arguments['name'] = $this->modelName."_submit".$lsName;
		$argumentList = $this->getListArguments($label,$arguments);
		return "<button $argumentList >$label</button>".$lshide;
	}
	public function button($name,$value, $content,$arguments = array(), $idSansIndex=''){
		$hid='';
		if($idSansIndex=='')$idSansIndex=$arguments['id'];
		if(!isset($arguments['id']))$arguments['id'] = $this->modelName."_".$name;
		$arguments['name'] = $this->modelName."_".$name;
		if(!isset($arguments['class']))$arguments['class']='';		
		if(!is_array($value) && !is_array($content)){	
			$arguments['value']=$value;			
			if(!isset($this->modelObj->$name) && $this->modelObj->$name == $value){
				$arguments['class'] .= ' btn_enfonce';
				$hid="<input type='hidden' name='".$arguments['name']."' value='".$value."' id='".$idSansIndex."hid'/>";
			}else{
				$arguments['class'] .= ' btn_inactif';
			}
						
		}else{
// 			$arguments['class'].=' btnMultiLib';
			if(isset($this->modelObj->$name)){
				$arguments['value']=$value[0];
				$content=$content[0];
			}else{				
				$id=array_search($this->modelObj->$name, $value);
				$arguments['value']=$value[($id+1)%count($value)];
				$content=$content[$id];
				$hid="<input type='hidden' name='".$arguments['name']."' value='".$value."'  id='".$idSansIndex."hid' />";
			}			
		}
	 	
	 	unset($arguments['name']);
		$argumentList = $this->getListArguments($name,$arguments);
		return "<button $argumentList >$content</button>".$hid;
	}
	public function file($name,$arguments = array()){
		$argumentList = $this->getListArguments($name,$arguments);
		$input = $this->label($name,$arguments['label'])."<input type='file' name='$name' $argumentList />";
		$input .= isset($arguments['maxFileSize'])?"<input type='hidden' name='MAX_FILE_SIZE' value='".$arguments['maxFileSize']."' />":'';
		$validError = validate_util::getMessage($name);
		$input .= $this->messageHtml($name);
		return $input;
	}
// 	public function button($name,$arguments = array()){
// 		$argumentList = $this->getListArguments($name,$arguments);
// 		return "<input type='button' value='$name' $argumentList />";
// 	}
	public function radio($name,$arguments = array()){
		if(isset($data[$name]) && isset($arguments['value']) && $arguments['value'] ==  $data[$name]) $arguments['checked']='checked';
		$argumentList = $this->getListArguments($name,$arguments);
		return $this->label($name,$arguments['label'])."<input type='radio' name='".$this->modelName."_".$name."' id='".$this->modelName."_".$name."' $argumentList />";
	}
	public function  checkbox($name,$arguments = array()){
		if(isset($data[$name])) $arguments['checked']='checked';
		$argumentList = $this->getListArguments($name,$arguments);
		$label = isset($arguments['label']) ? $this->label($name,$arguments['label']) : '';
		return $label."<input type='checkbox' name='".$this->modelName."_".$name."' id='".$this->modelName."_".$name."' $argumentList />";
	}
	public function addFormData($name, $value){
		echo "<p><input name='".$this->modelName."_".$name."' id='".$this->modelName."_".$name."' type='hidden' value='".$value."' />";
	}

}

	
						
