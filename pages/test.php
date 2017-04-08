<?php
echo"
<!DOCTYPE html>
<html>
<body>";


$a1=array("a"=>"red","b"=>"green","c"=>"blue");
$a2=array("a"=>"red","b"=>"blue","c"=>"green");

$result=array_diff_assoc($a1,$a2);
print_r($result);

echo"</body>
</html>";


?>