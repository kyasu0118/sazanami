<?php
namespace sazanami;
require_once "../require.php";

$htmlTemplate = new HtmlTemplate("plain");
$htmlTemplate->write( "hoge.html", "test", "きたきた");

?>