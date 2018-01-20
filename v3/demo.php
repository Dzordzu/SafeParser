<?php
  namespace DzordzuComponents\Security;
  include("SafeParser.v3.class.php");
  $parser = new SafeParser;
  //echo "\n<br>Work in progress...\n<br>";


  //$parser->preg_match_all("/\[(\w+)\]/", "[This] [is] 2nd [sample] (?) [of the] ['text']", $result);
  //$result = $parser->lookFor(["[", "]"], "\w+", "[Mama] [dala] Marysi[ Mama] bydlaka [smierdziela]", ["Mama"]);
  //print_r($parser->lookForTagTemplate("[/alertBox]"));
  //print_r($parser->getAttributes("href: xD.txt min: 2pxx max: 10px minlen: sdsd edfsf: dsfsd maxlength: 10 g m: ad"));
  print($parser->parse("[xD](href: secondOne)[a](href:=http://dzordzu.pl)FirstLink[/a][alertBox][a](href:=http://sources.dzordzu.pl hujowsto:=xDD)sxDDD xDDD[/a](href: podpucha.txt)[/alertBox]"));

 ?>
