<?php
class Taglib_PFL extends HTML_Template_Nest_Taglib {

  protected $tags = array(
      "css-additions" => "Taglib_PFL_CssAdditions",
      "js-additions" => "Taglib_PFL_JsAdditions",
      "css-addition" => "Taglib_PFL_CssAddition",
      "js-addition" => "Taglib_PFL_JsAddition",
  );
}

class Taglib_PFL_CssAdditions extends HTML_Template_Nest_Tag
{
  public static $additions = array();
  public function init() {
    if(count(Taglib_PFL_CssAdditions::$additions) == 0) {
      return true;
    }
    foreach(Taglib_PFL_CssAdditions::$additions as $addition) {
      $this->renderer->appendChild($addition);
    }
    Taglib_PFL_CssAdditions::$additions = array();

    return false;
  }

  public function start() {
  }

  public function end() {
  }
}
class Taglib_PFL_CssAddition extends HTML_Template_Nest_Tag
{

  public function init() {
    $doneProcessing = true;

    $children = $this->renderer->getChildren();
    foreach ($children as $child) {
      $doneProcessing = false;
      \Taglib_PFL_CssAdditions::$additions[] = $child;
    }

    foreach ($children as $child) {
      $this->renderer->removeChild($child);
    }
    return $doneProcessing;
  }

  public function end() {
  }
}


class Taglib_PFL_JsAdditions extends HTML_Template_Nest_Tag
{
  public static $additions = array();
  public function init() {
    if(count(\Taglib_PFL_JsAdditions::$additions) == 0) {
      return true;
    }
    foreach(\Taglib_PFL_JsAdditions::$additions as $addition) {
      $this->renderer->appendChild($addition);
    }
    \Taglib_PFL_JsAdditions::$additions = array();

    return false;
  }

  public function end() {
  }
}
class Taglib_PFL_JsAddition extends HTML_Template_Nest_Tag
{
  public function init() {
    $doneProcessing = true;
    
    $children = $this->renderer->getChildren();
    foreach ($children as $child) {
      $doneProcessing = false;
      \Taglib_PFL_JsAdditions::$additions[] = $child;
    }
    
    foreach ($children as $child) {
      $this->renderer->removeChild($child);
    }
    return $doneProcessing;    
  }

  public function end() {
  }
}

