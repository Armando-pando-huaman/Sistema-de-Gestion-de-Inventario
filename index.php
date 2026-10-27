<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: public/'.($query !== '' ? '?'.$query : ''));
exit;
