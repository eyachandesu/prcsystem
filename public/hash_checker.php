<?php
$hash = '$2y$12$kpHaxbVRagIDKr2eQon8JuVXbfc.tXqgd3S0XskuYmLk4/XLVVSuy';
$password_to_test = '12345';

if (password_verify($password_to_test, $hash)) {
    echo "Match found!";
} else {
    echo "Not a match.";
}
?>