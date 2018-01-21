<?php
  namespace DzordzuComponents\Security\Interfaces;
  interface SafeParser {
    //API
    //public function setAttributes($array, $replace = false);
    //public function setTags($array, $replace = false);
    //public function setCustomTags($array, $replace = false);

    //public function showAttributes();
    //public function showSettings();
    //public function showTags();
    //public function showCustomTags();

    //public function verifySecurity($text);
    //public function check($text);
    public function parse(string $text);

  }



 ?>
