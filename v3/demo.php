<?php
  namespace DzordzuComponents\Security;
  include("SafeParser.v3.class.php");
  $parser = new SafeParser;
  //echo "\n<br>Work in progress...\n<br>";


  //$parser->preg_match_all("/\[(\w+)\]/", "[This] [is] 2nd [sample] (?) [of the] ['text']", $result);
  //$result = $parser->lookFor(["[", "]"], "\w+", "[Mama] [dala] Marysi[ Mama] bydlaka [smierdziela]", ["Mama"]);
  //print_r($parser->lookForTagTemplate("[/alertBox]"));
  //print_r($parser->getAttributes("href: xD.txt min: 2pxx max: 10px minlen: sdsd edfsf: dsfsd maxlength: 10 g m: ad"));
  print($parser->parse(
    "
      [h1]SafeParser Demo[/h1]
      [h2] By [a](href:=http://dzordzu.pl)Dzordzu[/a][/h2]
      This <awesome> <script> was created to protect [b]you[b] and your website from
      [alertBox] DANGERS [/alertBox]
    "
  ));

 ?>
