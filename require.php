<?php
foreach ( glob( __DIR__ . "/class/*.class" ) as $filePath )
{
  $class = "namespace sazanami;";
  $className = "";
  $memberVariable = [];
  $localVariable = [];
  
  foreach (file($filePath) as &$value) 
  {
      $words = preg_split("/[\s,():;]+/", $value);
      $words = array_filter($words, function($value){
          return empty($value) == false || $value === '0' || $value === 0;
      });
      $words = array_values($words);
      $wordsLength = count($words);
  
      if( $wordsLength > 0 )
      {
          switch($words[0])
          {                
              case "class":
                  $className = $words[1];
                  $class .= $value;
                  break;
              case "var":
                  $localVariable[] = $words[1];
                  $class .= "$" . $words[1];
                  for ($i = 2; $i <$wordsLength; $i++)
                  {
                      $class .= $words[$i];
                  }
                  $class .= ";";
                  break;
              case "public":
              case "protected":
              case "private":
                  $header = $words[0];
                  if( strcmp($words[1], "static") == 0 )
                  {
                      $header .= " static";
                      unset($words[1]);
                      $words = array_values($words);
                      $wordsLength--;
                  }
                  // member variable.
                  if( strcmp($words[1], "var") == 0 )
                  {
                      $memberVariable[] = $words[2];

                      $value = preg_replace(
                        '/([\s,();=])('.$words[2].')([\s,();=])/', "$1 $" . $words[2]. " $3", $value);

                      $value = preg_replace(
                        '/([\s,();=])(var)([\s,();=])/', "", $value);

                      $class .= $value;
                      break;
                  }
                  $class .= $header . " function ";
  
                  // member method.
                  if( strcmp($words[1], $className) == 0 )
                  {
                      $class .= "__construct";
                  }
                  else if( strcmp($words[1], "~".$className) == 0 )
                  {
                      $class .= "__destruct";
                  }
                  else
                  {
                      $class .= $words[1];
                  }
                  $localVariable = [];
  
                  $class .= "(";
                  for ($i = 2; $i <$wordsLength; $i++)
                  {
                      $localVariable[] = $words[$i];
                      if( $i > 2 )
                      {
                          $class .= ",";
                      }
                      $class .= "$" . $words[$i];
                  }
                  $class .= ")";
                  break;
              default:
                  for ($i = 0; $i<count($localVariable); $i++)
                  {
                      $value = preg_replace(
                          '/([\s,();=])('.$localVariable[$i].')([\s,();=])/', "$1 $" . $localVariable[$i]. " $3", $value);
                  }
                  for ($i = 0; $i<count($memberVariable); $i++)
                  {
                      $value = preg_replace(
                          '/([\s,();=])('.$memberVariable[$i].')([\s,();=])/',
                            "$1 " . '$this->' . $memberVariable[$i] . " $3", $value);
                  }
                  $class .= $value;
                  break;
          }
      }
  }
  eval( $class );
}
?>