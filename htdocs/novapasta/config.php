<?php
$conn = new mysqli('localhost', 'root', '', 'Easyglow1');

if ($conn->connect_error) {
    die(json_encode(['erro' => 'Ligação falhou']));
}
?>