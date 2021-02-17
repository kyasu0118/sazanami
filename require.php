<?php
foreach ( glob( __DIR__ . "/class/*.class" ) as $filePath )
{
    $class = "namespace sazanami;";
    $className = "";
    $memberVariable = [];
    $localVariable = [];

    foreach (file($filePath) as &$value) 
    {
        preg_match_all('/".*?"/', $value, $matches,PREG_PATTERN_ORDER);

        for ($i = 0; $i <count($matches[0]); $i++)
        {
            $value = str_replace($matches[0][$i], "#".$i, $value);
        }

        $value = str_replace("(", " ( ", $value);
        $value = str_replace(")", " ) ", $value);
        $value = str_replace("{", " { ", $value);
        $value = str_replace("}", " } ", $value);
        $value = str_replace(",", " , ", $value);
        $value = str_replace("=", " = ", $value);
        $value = str_replace("-", " - ", $value);
        $value = str_replace("- >", " -> ", $value);
        $value = str_replace(";", " ; ", $value);
        $value = str_replace("+", " + ", $value);
        $value = str_replace("*", " * ", $value);
        $value = str_replace("/", " / ", $value);
        $words = preg_split("/[\s]+/", $value);
        $words = array_filter($words, function($value){
            return empty($value) == false || $value === '0' || $value === 0;
        });
        $words = array_values($words);
        $wordsLength = count($words);

        for ($i = 0; $i <$wordsLength; $i++)
        {
            for ($j = 0; $j <count($matches[0]); $j++)
            {
                if( strcmp($words[$i],"#".$j) == 0 )
                {
                    $words[$i] = $matches[0][$j];
                    break;
                }
            }
        }
 
        if( $wordsLength > 0 )
        {
            $index = 0;
            switch($words[0])
            {                
                case "class":
                    $className = $words[1];
                    $class .= $value;
                    break;
                case "public":
                case "protected":
                case "private":
                    $class .= $words[0] . " ";
                    $index = 1;
                    if( strcmp($words[1], "static") == 0 )
                    {
                        $class .= "static";
                        $index++;
                    }
                    // member variable.
                    if( strcmp($words[$index], "var") == 0 )
                    {
                        $index++;
                        $memberVariable[] = $words[$index];
                        $class .= "$" . $words[$index];
                        $index++;      
                       
                        for ($i = $index; $i <$wordsLength; $i++)
                        {
                            $doll = "";
                            for ($j = 0; $j<count($localVariable); $j++)
                            {
                                if( strcmp($words[$i],$localVariable[$j]) == 0 )
                                {
                                    $doll = "$";
                                    break;
                                }
                            }
                            if( strcmp($doll, "") == 0 )
                            { 
                                for ($j = 0; $j<count($memberVariable); $j++)
                                {
                                    if( strcmp($words[$i],$memberVariable[$j]) == 0 )
                                    {
                                        $doll = '$this';
                                        break;
                                    }
                                }
                            }
                            $class .= $doll . $words[$i];
                        }
                    }
                    // member method.
                    else
                    {
                        $class .= " function ";
                        if( strcmp($words[$index], $className) == 0 )
                        {
                            $class .= "__construct";
                        }
                        else if( strcmp($words[$index], "~".$className) == 0 )
                        {
                            $class .= "__destruct";
                        }
                        else
                        {
                            $class .= $words[$index];
                        }
                        $index++;
                        $localVariable = [];

                        for ($i = $index; $i <$wordsLength; $i++)
                        {
                            if( strcmp(",", $words[$i]) != 0 && 
                                strcmp("(", $words[$i]) != 0 &&
                                strcmp(")", $words[$i]) != 0 )
                            {
                                $localVariable[] = $words[$i];
                                $class .= "$";
                            }
                            $class .= $words[$i];
                        }
                    }
                    break;
                default:                
                    for ($i = 0; $i <$wordsLength; $i++)
                    {
                        if( strcmp($words[$i],"var") == 0 )
                        {
                            $localVariable[] = $words[$i+1];
                        }
                        else if( strcmp($words[$i],"as") == 0 )
                        {
                            $class .= " as ";
                            $localVariable[] = $words[$i+1];
                        }
                        else if( strcmp($words[$i],"return") == 0 )
                        {
                            $class .= "return ";
                        }                        
                        else if( strcmp($words[$i],"new") == 0 )
                        {
                            $class .= "new ";
                        }
                        else
                        {
                            $doll = "";
                            for ($j = 0; $j<count($localVariable); $j++)
                            {
                                if( strcmp($words[$i],$localVariable[$j]) == 0 )
                                {
                                    $doll = "$";
                                    break;
                                }
                            }
                            if( strcmp($doll, "") == 0 )
                            { 
                                for ($j = 0; $j<count($memberVariable); $j++)
                                {
                                    if( strcmp($words[$i],$memberVariable[$j]) == 0 )
                                    {
                                        $doll = '$this->';
                                        break;
                                    }
                                }
                            }
                            $class .= $doll . $words[$i];
                        }
                    }
                    break;
            }
        }
    }
    eval( $class );
}
?>