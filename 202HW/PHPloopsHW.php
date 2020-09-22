<?php

$arr = [1,4,6,8,15,18,21,28];
$count = count($arr);

for($i=0;$i<$count;$i++){
	if($arr[$i]%2 == 0){
    	echo $arr[$i]."<br>\n";
    }
}

?>
