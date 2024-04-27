<?php

$connection = new mysqli('db', 'root', 'lionPass', 'url_shortener');

if (!$connection) {
    die("Database Connection failed");
}
