<?php
function compile()
{
    $searches = array("(",")","{","}",",","=","-","- >",";","+","*","/",":");
    $replaces = array(" ( "," ) "," { "," } "," , "," = "," - "," -> "," ; "," + "," * "," / "," : ");

    foreach ( glob( __DIR__ . "/class/*.class" ) as $filePath )
    {
        $class = "namespace sazanami;";
        $className = "";
        $memberVariable = [];
        $memberVariableType = [];
        $localVariable = [];

        foreach (file($filePath) as &$value) 
        {
            preg_match_all('/".*?"/', $value, $matches,PREG_PATTERN_ORDER);

            for ($i = 0; $i <count($matches[0]); $i++)
            {
                $value = str_replace($matches[0][$i], "#".$i, $value);
            }
            $value = str_replace($searches, $replaces, $value);
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
                            if( $index+2 < $wordsLength && strcmp($words[$index+1],":") == 0 )
                            {
                                $memberVariableType[] = $words[$index+2];
                                $index+=2;
                            }
                            else
                            {
                                $memberVariableType[] = null;
                            }
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
        $class = substr($class, 0, -1);
        $class .= 
            '
                public function toJson()
                {
                    return json_encode( $this );
                }
                public function fromJson($json)
                {
                    if( $json == null )
                    {';
                        for( $i=0; $i<count($memberVariable); $i++ )
                        {
                            $class .= '$this->' . $memberVariable[$i] . '=null;';
                        }     
        $class .=
            '
                    }
                    else
                    {
            ';
        for( $i=0; $i<count($memberVariable); $i++ )
        {
            $class .= '$this->' . $memberVariable[$i] . '=';

            if( $memberVariableType[$i] != null )
            {
                $class .= 'new ' . $memberVariableType[$i] . '();';
                $class .= '$this->' . $memberVariable[$i] . '->fromJson('.'$json["' . $memberVariable[$i] . '"]);';
            }
            else
            {
                $class .= '$json["' . $memberVariable[$i] . '"];';
            }
        }
        $class .=
            '       }
                }
                public function save($filePath)
                {
                    file_put_contents( $filePath, $this->toJson() );
                }
                public function load($filePath)
                {
                    $this->fromJson( json_decode(file_get_contents($filePath), true) );
                }
                public static function saveArray($filePath, $array)
                {
                    if( is_array($array) )
                    {
                        file_put_contents( $filePath, json_encode($array) );
                    }
                    else
                    {
                        file_put_contents( $filePath, json_encode( array($array) ) );
                    }                
                }
                public static function loadArray($filePath)
                {
                    $array = json_decode(file_get_contents($filePath), true);

                    $result = [];
                    foreach ($array as &$value) 
                    {
                        $object = new ' . $className .'();
                        $object->fromJson( $value );
                        $result[] = $object;
                    } 
                    return $result;  
                }            
            }
            ';
        eval( $class );
    }
}
compile();
?>