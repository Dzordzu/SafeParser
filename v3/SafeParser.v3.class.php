<?php
  namespace DzordzuComponents\Security;

  include("SafeParser.v3.interface.php");


  class SafeParser implements Interfaces\SafeParser {

    # constants
      const PATTERN_TAGS = "\/?\w+"; //accept words with possible / at the beginning
      const PATTERN_WORDS_ONLY = "\w+";
      const PATTERN_LINKS = "[\:\w\.\/\?\-_]+";
      const PATTERN_DEFAULT_HYPHEN = "\/?[\w\-]+";
      const PATTERN_HYPHEN = "[\w\-]+";
      const PATTERN_EXTENDED = "[\;\)\(\/\:\-\w\.]+";
      const PATTERN_SPACES = "[\w ]+";
      const PATTERN_LETTERS_ONLY = "[a-zA-Z]+";

      const TAGS_DEFAULT = [
        "div", "p", "span", "br",
        "h1", "h2","h3", "h4", "h5", "h6",
        "pre",
        "ul",  "ol",  "li",
        "table", "tr", "td", "th",
        "b", "u", "s",
        "a"
      ];
      const TAGS_STRICT = [
        "div", "span", "br", "p"
      ];

      const ATTRIBUTES_DEFAULT = [
        "value", "cols", "href", "draggable", "max", "maxlength", "minlength",
        "min", "rows", "bgcolor"
      ];
      const ATTRIBUTES_INPUT = [
        "selected", "type", "placeholder", "checked"
      ];
      const ATTRIBUTES_INDENTIFIERS = [
        "class", "name", "id"
      ];

      // levels should have increasing numbers
        const LVL_NONE = 0;
        const LVL_ALL = 1;
        const LVL_STRICT = 2;
        const LVL_SUPER = 3;
        const LVL_AVERAGE = 4;


    protected $enabledTags = self::TAGS_DEFAULT;
    protected $enabledAttributes = self::ATTRIBUTES_DEFAULT;
    protected $tagsTemplates = [
      "alertBox" => "<div class='alert' style='background-color: red; padding: 2px;'><p style='color: white'>"
    ];

    # Settings
    protected $settings = [

      "delimiters" => [
        "tags" => ["[", "]"],
        "attributes" => ["(", ")"],
        "tagTemplates" => ["[", "]"],
        "attributesNames" => ["", ":="]
      ],

      "patterns" => [
        "tags" => self::PATTERN_TAGS,
        "attributesNames" => self::PATTERN_HYPHEN,
        "tagTemplates" => self::PATTERN_TAGS,
        "values" => self::PATTERN_LINKS
      ],

      "levels" => [
        "security" => self::LVL_ALL,
        "report" => self::LVL_NONE
      ],

      "others" => [
        "usingEcho " => false,
        "devMode" => false,
      ]

    ];




    # Protected functions

    protected function print_r($array) {
      print("<pre>".htmlentities(print_r($array,true), ENT_QUOTES)."</pre>");
    }

    protected function searchByKeys(string $arrayName, string $arrayKey) {
      return isset($this->$arrayName[$arrayKey]);
    }

    protected function prepareRegExp(array $delimiters, string $pattern) {
      //preg_quote delimiters
        $delimiters[0] = preg_quote($delimiters[0], "/");
        $delimiters[1] = preg_quote($delimiters[1], "/");
      //return regExp (without starting end ending signs)
        return $delimiters[0]."($pattern)".$delimiters[1];
    }


    # preg_match*
    # BOTH HAVE PREG_OFFSET_CAPTURE

    protected function preg_match(string $pattern , string $subject , array &$matches=null, int $offset = null) {

      $offset = ($offset === null) ? 0 : $offset;

      // Define if purify
        switch($this->settings["levels"]["security"]) {
          default:
            $purify = true;
            break;
          case self::LVL_NONE:
            $purify = false;
            break;
        }

      //match first result
        $found = preg_match($pattern , $subject , $tmp , PREG_OFFSET_CAPTURE , $offset);
        //but if not found just don't care
          if($found === false || $found === 0) return $found;
        //set human readable names
          foreach($tmp as $elementKey => $element) {
            $matches[$elementKey]["value"] = $purify ? htmlspecialchars($tmp[$elementKey][0], ENT_QUOTES, "UTF-8") : $tmp[$elementKey][0];
            $matches[$elementKey]["start"] = $tmp[$elementKey][1];
            $matches[$elementKey]["end"] = $tmp[$elementKey][1] + strlen($tmp[$elementKey][0]);
          }


      return $found;
    }

    protected function preg_match_all(string $pattern , string $subject , array &$matches = null, int $offset = null) {
      $offset = ($offset === null) ? 0 : $offset;
      // Define if purify
        switch($this->settings["levels"]["security"]) {
          default:
            $purify = true;
            break;
          case self::LVL_NONE:
            $purify = false;
            break;
        }

      //set human readable names
        $ammount = preg_match_all($pattern , $subject , $tmp , PREG_OFFSET_CAPTURE , $offset);
        if($ammount === false || $ammount === 0) return $ammount;
        foreach($tmp as $partKey=>$part) foreach($part as $elementKey => $element) {
          $matches[$partKey][$elementKey]["value"] = $purify ? htmlspecialchars($tmp[$partKey][$elementKey][0], ENT_QUOTES, "UTF-8") : $tmp[$partKey][$elementKey][0];
          $matches[$partKey][$elementKey]["start"] = $tmp[$partKey][$elementKey][1];
          $matches[$partKey][$elementKey]["end"] = $tmp[$partKey][$elementKey][1] + strlen($tmp[$partKey][$elementKey][0]);
        }

      return $ammount;
    }


    //find next pattern(with delimiters in text)
    protected function lookFor(array $delimiters, string $pattern, string $text, int $offset = 0) {
      $final = [0, array()];
      $regexp = $this->prepareRegExp($delimiters, $pattern);
      $final[0] = $this->preg_match("/$regexp/", $text, $final[1], $offset);
      return $final;

    }

    protected function incr_lookFor(array &$lookFor, $val) {
      $lookFor[1][0]['start'] += $val;
      $lookFor[1][0]['end'] += $val;
      $lookFor[1][1]['start'] += $val;
      $lookFor[1][1]['end'] += $val;
    }

    protected function lookForTag(string $text) {
      $delimiters = $this->settings['delimiters']['tags'];
      $pattern = $this->settings['patterns']['tags'];
      $incrementBy = 0;

      while(1) {
        $searchResult = $this->lookFor($delimiters, $pattern, $text);

        if($searchResult[0] === 0) return false;

        if(in_array($searchResult[1][1]['value'], $this->enabledTags)) {
          $this->incr_lookFor($searchResult, $incrementBy);
          return $searchResult;
        }
        else if(in_array(substr($searchResult[1][1]['value'],1), $this->enabledTags) && substr($searchResult[1][1]['value'],0, 1) === "/") {
          $this->incr_lookFor($searchResult, $incrementBy);
          return $searchResult;
        }

        $incrementBy += $searchResult[1][0]['end'];
        $text = substr($text, $searchResult[1][0]['end']);
      }
    }

    protected function lookForTagTemplate(string $text) {
      //get data
        $delimiters = $this->settings["delimiters"]["tagTemplates"];
        $pattern = $this->settings ["patterns"]["tagTemplates"];
      $incrementBy = 0;

      while(1) {
          // find new tag with patterns and delimiters in text
            $searchResult = $this->lookFor($delimiters, $pattern, $text);

          //terminate if no result has been found
            if($searchResult[0] === 0) return false;

          //if tagTemplate is set
            if(isset($this->tagsTemplates[$searchResult[1][1]['value']])) {
              //add length of the cut text
                $this->incr_lookFor($searchResult, $incrementBy);
              return $searchResult;
            }

          //the same as above (but to the closing tagTemplate)
            else if(isset($this->tagsTemplates[substr($searchResult[1][1]['value'], 1)]) && substr($searchResult[1][1]['value'], 0, 1) === "/") {
              $this->incr_lookFor($searchResult, $incrementBy);
              return $searchResult;
            }
          //next position will be relative to the edited text - to avoid it add value to incrementBy
            $incrementBy += $searchResult[1][0]['end'];
          //shorten text
            $text = substr($text, $searchResult[1][0]['end']);
      }
    }

    protected function autoclose($text) {
        // set final
          $final= "";
        //reverse tags
          while(1) {
            //find positions of < or > signs
              $begin =strrpos($text, "<");
              $end = strrpos($text, ">");
            //terminate even if one of them is absent
              if($end === false || $begin === false) break;
            //take in into final

              $final.=substr($text, $begin, $end-$begin+1);
              $text = substr($text, 0, $begin);

          }

        return preg_replace("/<(\w+) ?[\w =\"\'\.\:\- ;]*>/", "</$1>", $final);
    }




    public function getAttributes(String $text) {
      $final = "";
      $settings = $this->settings;

      while(1) {
        //find first attribute
          $attr= $this->lookFor($settings['delimiters']['attributesNames'], $settings['patterns']['attributesNames'], $text);
        //check if it's enabled
          if(!in_array($attr[1][1]['value'], $this->enabledAttributes)) {
            //terminate if not found
              if($attr[0] === 0 || $i===2) break;
            //delete unnecessary text
            $text = substr(
              $text,
              $attr[1][0]['end']
            );
            if($attr[0] === 0) break;
            continue;
          }
        //add it to final
          $final .= $attr[1][1]['value'];
        //find next attribute start (I'm avoiding recurrency)
          $valuesEnd = $this->lookFor(
            $settings['delimiters']['attributesNames'],
            $settings['patterns']['attributesNames'],
            $text,
            $attr[1][0]['end'] //offset
          )[1][0]['start']; //it's NOT a mistake.
        //if not found, set to the text length - 1
          if($valuesEnd === null) $valuesEnd = strlen($text);
        //get values string
          $value = substr(
            $text,
            $attr[1][0]['end'],
            $valuesEnd - $attr[1][0]['end']
           );
        //paste value into attribute if its length > 0
          if(strlen($value)>0) $final.="='$value'";
        //add space after
          $final.=" ";
        //terminate if attr[0] = false
          if($attr[0] === 0) break;
        //cut the text
          $text = substr($text, $valuesEnd);
    }

        return $final;
    }




  # API
    public function __construct() {
    }

    public function getSettings(string $settingName = "all") {
      return $this->settings[$settingName];
    }

    public function parse($text) {
      //set variables
        $final = "";
        $attrDelimiters = $this->settings['delimiters']['attributes'];

        while(1) {
          //first of all find positions
            //look for permtted tags
              $endPos = 0;

              $tag = $this->lookForTag($text);
              $tagTemplate = $this->lookForTagTemplate($text);

              if($tag === false && $tagTemplate === false)  break;

              if(
                $tagTemplate === false
                || ($tag[0]  === 1 && $tagTemplate[0] === 1 && $tag[1][0]['start'] <= $tagTemplate[1][0]['start'])
              ) {
                $endPos = $tag[1][0]['end'];
                $tmp = substr($text, 0, $tag[1][0]['start']);
                $final.=htmlspecialchars($tmp, ENT_QUOTES, "UTF-8");

                if(substr($tag[1][1]['value'], 0, 1) != "/") {

                  $this->preg_match(
                    "/".preg_quote($attrDelimiters[0], "/")."/",
                    $text,
                    $attrStart,
                     $tag[1][0]['end']-1
                  );

                  $this->preg_match(
                    "/".preg_quote($attrDelimiters[1], "/")."/",
                    $text,
                    $attrEnd,
                     $tag[1][0]['end']
                  );

                  $endPos = $attrEnd[0]['end'];

                  $attr = substr($text, $attrStart[0]['end'], $attrEnd[0]['start'] - $attrStart[0]['end']);
                  $attr = $this->getAttributes($attr);
                }
                else $attr = "";

                $final.="<".$tag[1][1]['value']." ".$attr.">";
              }


              else if(
                $tag === false
                || ($tag[0]  === 1 && $tagTemplate[0] === 1 && $tag[1][0]['start'] > $tagTemplate[1][0]['start'])
              ) {
                $endPos = $tagTemplate[1][0]['end'];
                $tmp = substr($text, 0, $tagTemplate[1][0]['start']);
                $final.=htmlspecialchars($tmp, ENT_QUOTES, "UTF-8");

                if(substr($tagTemplate[1][1]['value'], 0, 1) === "/") $final.=$this->autoclose($this->tagsTemplates[substr($tagTemplate[1][1]['value'], 1)]);
                else $final.=$this->tagsTemplates[$tagTemplate[1][1]['value']];
              }

              $text = substr($text, $endPos);

        }
        $final.=htmlspecialchars($text, ENT_QUOTES, "UTF-8");
        return  $final;

    }


  }



?>
