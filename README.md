# SafeParser HTML

## Supported languages:
* PHP

## Description
Simple plugin, that allows you to protect your sites from XSS attacks.

### When to use
When you cannot users input data (it means always...)

### Is it hard to learn?
No. Understanding SafeParser is extremely easy, because it's you who sets all the rules and syntax.


## Dependencies
None

## Installation
1. Import module.php file to your project
2. Initialize SafeParser object with ```$SafeParser = new SafeParser;```

## Technical
### Default settings
#### Technical
|Name|Value|
|-|-|
|text| ""|
|enabler|[?]|
|searchSymbol|?|
|useEcho|false|
#### Enabled Tags

|Name|
|-|
|div|
|p|
|h1, h2, h3, h4, h5, h6|
|span|
|pre|
|ul, ol, li|
|table, tr, td, th,
|b, u, s|
|br|

__NOTE__: Enabled tags can always be changed. They are on the top of the SafeParser.class.php file

### Functions
|Name|Return type|Description|
|-|-|-|-|
|setText(string $text)|-|Sets text|
|setSearchSymbol(string $symbol)|-|Sets SearchSymbol|
|setEnabler(string $format)|-|Sets enabler|
|setCustomElement(string $tagName, string $tagSequence)| - | Sets new tag representing  sequence of predefined in $tagSequence tags. |
|setEcho(bool $value = true)| - | sets useEcho|
|parse(string $text = NULL)| string| Parses tags and customElements from objects text, or, if $text is not null, from given $text|

## Usage
### Introduction
The main purpose of this plugin is to increase awareness of the fact that preventing XSS can be done painlessly and quickly.
###  Default format
By default SafeParser looks for [?] tags and convert them to the <?> format. <br>
### Understanding SafeParser
The '?' sign is called (obviously) searched symbol. The '[?]' expression is called enabler.<br><br>
You can always change enabler just by typing ```$SafeParser->setEnabler($format);```<br>
 For example: ```$SafeParser->setEnabler("[?}}");``` - enabler will look for tag names hidden between '[' and '}}' symbols like [p}}.<br> <br>
However, if you don't like our '?' searched symbol, just type ```$SafeParser->setSearchSymbol(string $symbol);``` to change it.<br>
 For example: ```$SafeParser->setSearchSymbol('!');``` will change default search symbol to '!'.<br>
__WARNING:__ setSearchSymbol does NOT change enabler. You have to redefine it.

To parse tags simply use HTML tag name inside enabler ```$SafeParser->parse(string $text);```. <br>
For example: ```$SafeParser->parse("[p}} This is paragraph [/p}}");```<br>
__NOTICE:__ Always remember to close your tags!

With that knowledge you will understand following example
```php
<?php
$SafeParser = new SafeParser;
$SafeParser->setSearchSymbol('!'); // it shouldALWAYS be before setEnabler
$SafeParser->setEnabler("[!}}"); //I know it's awful styled, but it's brilliant for example
echo $SafeParser->parse("[p}} This is paragraph [/p}}");
/*Will print:
<p> This is paragraph </p>
*/
?>
```

### Own Elements
SafeParser allows you to create own elements. For example: you can make it to replace ```[ownElement]``` tag with ```<p style="background-color: red"><input class="some-class" disabled>``` tags.<br><br>
To do that:
1. Define element with ```setCustomElement(string $tagName, string $tagSequence)```. Example: ```setCustomElement(ownElement, "<p style="background-color: red"><input class="some-class" disabled>")```
2. Use it as a tag! (Remember to close your tag).

SafeParser will automatically add slashes and order your closing tags!
