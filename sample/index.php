<?php
namespace sazanami;
require_once "../require.php";

$monolog = new Monolog();

print_r( $monolog->toJson() );

?>