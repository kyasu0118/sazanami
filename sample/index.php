<?php
namespace sazanami;
require_once "../require.php";

$file = new File("test.txt", "w");
$file->write("hoge");

?>