<?php

$arr_index = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L',
            'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');



for($i=0; $i<25; $i++){
    for($j=0; $j<25; $j++){
        array_push($arr_index, $arr_index[$i] . $arr_index[$j]);
    }
}

foreach($arr_index as $key=>$value){
    echo $key . " => " . $value . "<br />";
}

echo"
<!DOCTYPE html>
<html>
<body>";



echo"</body>
</html>";


?>