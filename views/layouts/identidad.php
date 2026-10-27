<?php
require_once __DIR__.'/../../models/botica.php';
$fvBotica=(new Botica())->actual();
$fvNombreBotica=$fvBotica['nombre'] ?? 'Mi botica';
$fvEsc=static fn($value)=>htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');
