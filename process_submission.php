<?php

echo 'username ' . htmlspecialchars($_POST["username"]) . '!';
echo 'pw ' . htmlspecialchars($_POST["password"]) . '!';
echo 'picks ' . htmlspecialchars($_POST["picks"]) . '!';

/*
$decoded = json_decode($_POST["thisisjson"]);
echo $_POST["figure1"] + $_POST["figure2"];
echo $decoded->{'w11'};
echo json_encode($decoded);
*/
?>
