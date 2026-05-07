<?php
$conn = new mysqli('localhost', 'root', '', 'easyglow1');

if ($conn->connect_error) {
    die(json_encode(['erro' => 'Ligação falhou']));
}
?>