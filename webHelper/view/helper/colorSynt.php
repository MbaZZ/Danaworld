<?php
/**
 * Description of ColorSynt
 *
 * @author MbZ
 * thanks to
 * dp.SyntaxHighlighter
 * Version: 1.5.1
 * http://www.dreamprojections.com/syntaxhighlighter
 * ©2004-2007 Alex Gorbatchev.
 */

class ColorSynt extends AppHelper{
    private $lastName;
	private $path = "colorSynt/";
	private $html;
    /**
     *
     * @param ViewController $html
     */
    public function init($html){
    	$this->html = $html;
    	
        $html->addCss($this->path."SyntaxHighlighter");
        $html->addjs($this->path."shCore");

       // $html->addJavascript($path."Scripts/shBrushC.js");
       // $html->addJavascript($path."Scripts/shBrushCSharp.js");
       // $html->addJavascript($path."Scripts/shBrushCpp.js");
       // $html->addJavascript($path."Scripts/shBrushCss.js");
       // $html->addJavascript($path."Scripts/shBrushJaba.js");
       // $html->addjs($path."Scripts/shBrushPhp.js");
       // $html->addJavascript($path."Scripts/shBrushSql.js");
       // $html->addJavascript($path."Scripts/shBrushXml.js");
       // $html->addJavascript($path."Scripts/shBrushPython.js");
        
    }
    public function addLanguage($language){
    	$this->html->addjs($this->path."shBrush".ucfirst($language));
    }
    public function getBeginBloc($language = "Php", $rows="20", $cols="20"){
        $this->lastName = "code".rand();
        return "<textarea cols='".$cols."' rows='".$rows."'  name='".$this->lastName."' class='".$language."'>";

    }
    public function beginBloc($language = "Php", $rows="20", $cols="20"){
        echo $this->getBeginBloc($language, $rows, $cols);
    }
    public function getEndBloc(){
        return '</textarea>
            <script language="JavaScript" type="text/javascript">
                dp.SyntaxHighlighter.HighlightAll("'.$this->lastName.'");
            </script>';
    }
    public function endBloc(){
        echo $this->getEndBloc();
    }
}
?>
