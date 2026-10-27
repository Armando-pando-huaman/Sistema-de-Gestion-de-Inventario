<?php
class ExportarCsv {
    public static function celda($valor): string {
        $texto = (string)($valor ?? '');
        // Evita que Excel interprete textos del usuario como fórmulas.
        if (preg_match('/^[\s\x{FEFF}]*[=+@\-]/u', $texto) && !preg_match('/^-\d+(?:\.\d+)?$/D', $texto)) return "'".$texto;
        if (preg_match('/^[\t\r\n]/', $texto)) return "'".$texto;
        return $texto;
    }
    public static function descargar(string $nombre, array $columnas, iterable $filas): void {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$nombre.'.csv"');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        $salida = fopen('php://output', 'wb');
        fwrite($salida, "\xEF\xBB\xBF");
        fputcsv($salida, array_values($columnas), ';', '"', '');
        foreach ($filas as $fila) {
            $valores = [];
            foreach ($columnas as $clave => $titulo) $valores[] = self::celda($fila[$clave] ?? '');
            fputcsv($salida, $valores, ';', '"', '');
        }
        fclose($salida);
    }
}
