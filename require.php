<?php
foreach ( glob( "./Sazanami/*.php" ) as $filePath )
{
  require_once $filePath;
}
?>