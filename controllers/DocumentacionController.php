<?php
class DocumentacionController {
    public function index(): void {
        $secciones = require __DIR__.'/../docs/manual.php';
        require __DIR__.'/../views/documentacion/index.php';
    }
}
