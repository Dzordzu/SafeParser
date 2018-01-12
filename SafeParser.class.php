<?php
  class SafeParser {

    protected $enabledTags = [
      "div",
      "p",
      "h1", "h2","h3", "h4", "h5", "h6",
      "span",
      "pre",
      "ul",  "ol",  "li",
      "table", "tr", "td", "th",
      "b", "u", "s",
      "br"
    ];

    protected $customElements = [
    ];

    protected $enabler = "[?]";
    protected $text;
    protected $searchSymbol = "?";
    protected $useEchoVar = false;

    protected function closeCustomElements($val) {
      // set final
        $final= "";
      //reverse tags
        while(1) {
          //find positions of < or > signs
            $begin = strrpos($val, "<");
            $end = strrpos($val, ">");
          //terminate even one of them is absent
            if($end === false || $begin === false) break;
          //take in into final
            $final.=substr($val, $begin, $end-$begin+1);
            $val = substr($val, 0, $begin);
      }
      //close tags and return
        return preg_replace("/<(\w+) ?[\w =\"\'\.:\-_]*>/", "</$1>", $final);
    }

    public function setText(string $text) {
      $this->text = $text;
    }

    public function setSearchSymbol(string $symbol) {
      $this->searchSymbol = $symbol;
    }

    public function setEnabler(string $format) {
      $this->enabler = $format;
    }

    public function setEcho(bool $value = true) {
      $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
      $this->useEchoVar = $value;
    }

    public function setCustomElement(string $name, string $value){
      $this->customElements[$name] = $value;
    }

    public function parse(string $text = NULL) {

      // check if user want to change his own value
        if($text === NULL) $text = $this->text;
      //get $enabler and searchSymbol
        $SS = $this->searchSymbol;

        $enabler = [
          $this->enabler,
          substr($this->enabler, 0, strpos($this->enabler, $SS)),
          substr($this->enabler, strpos($this->enabler, $SS) + strlen($SS))
        ];

      // set final value
        $final = "";
       // loop through the text
        $tag_start = strpos($text, $enabler[1]);
        while(1) {
          if($tag_start === false) break;
          // add text not in tag to final
            $final.=htmlspecialchars(substr($text, 0, $tag_start), ENT_QUOTES, "UTF-8");
          // find position of the end of the enabler
            $tag_end = strpos($text, $enabler[2]);
          // get tag name
            $position_tag_begin = $tag_start + strlen($enabler[1]);
            $distance_tag_end = $tag_end-$tag_start-1;
            $tagName = substr($text,$position_tag_begin , $distance_tag_end);
            $tagName = htmlspecialchars($tagName, ENT_QUOTES, "UTF-8");

          //move position tag end to the last
            $tag_end +=strlen($enabler[2]);
          // if there is enabler element
            if(in_array($tagName, $this->enabledTags)) {
              $final.="<$tagName>";
            }
          // if there is tag closing element
            else if(in_array(substr($tagName, 1), $this->enabledTags)) {
              $final.="<$tagName>";
            }
          // if there is custom tag enabler
            else if(isset($this->customElements[$tagName])) {
              $final.=$this->customElements[$tagName];
            }
          // if there is custom tag closing element
            else if(isset($this->customElements[substr($tagName, 1)])) {
              $final.=$this->closeCustomElements($this->customElements[substr($tagName, 1)]);
            }
          // if there is no good tag
            else {
              $distance_tag_end+=strlen($enabler[2]) + 1;
              $getFullTag = substr($text, $tag_start , $distance_tag_end);
              $final.=htmlspecialchars($getFullTag, ENT_QUOTES, "UTF-8");
            }
          // remove from text analised element
            $text = substr($text, $tag_end);
          // find new tag start
          $tag_start = strpos($text, $enabler[1]);
      }
      $final.=htmlspecialchars($text, ENT_QUOTES, "UTF-8");
      if($this->useEchoVar) echo $final;
      return $final;
    }

  }

  $SafeParser = new SafeParser;
  $SafeParser->setSearchSymbol('!'); // it shouldALWAYS be before setEnabler
  $SafeParser->setEnabler("[!}}"); //I know it's awful styled, but it's brilliant for example
  $SafeParser->setCustomElement("ownElement", "<p style='background-color: red'><a class='someClass' id='someId'>");
  echo $SafeParser->parse("[ownElement}} This is my own element [/ownElement}}");
?>
