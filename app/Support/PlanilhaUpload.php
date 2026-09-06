<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Recebe uma planilha enviada via upload e devolve um caminho ABSOLUTO e
 * legivel para leitura (Spout exige extensao .xlsx no nome).
 *
 * Estrategia tolerante a ambientes restritos (open_basedir, permissoes,
 * chroot do PHP-FPM):
 *   1. tenta storage/app/uploads_planilhas
 *   2. se o diretorio nao for gravavel, usa o temp do sistema
 *   3. tenta move_uploaded_file; se falhar, tenta copy
 *   4. confere que o arquivo ficou legivel — senao, lanca erro com a causa real
 */
class PlanilhaUpload
{
    public static function receber($file, ?string $filename = null): string
    {
        if (!$file) {
            throw new \RuntimeException(
                'Nenhum arquivo recebido. Verifique upload_max_filesize/post_max_size no PHP.'
            );
        }

        $tmp = $file instanceof UploadedFile ? $file->getRealPath() : (string) $file;
        if (!$tmp || !is_readable($tmp)) {
            throw new \RuntimeException(
                'Arquivo temporario do upload inacessivel (' . ($tmp ?: 'vazio') . '). ' .
                'Verifique upload_tmp_dir e open_basedir no PHP.'
            );
        }

        $filename = $filename ?: (uniqid() . '.xlsx');

        $dir = storage_path('app' . DIRECTORY_SEPARATOR . 'uploads_planilhas');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            Log::warning("uploads_planilhas nao gravavel ($dir), usando temp do sistema");
            $dir = rtrim(sys_get_temp_dir(), '/\\');
        }

        $filePath = $dir . DIRECTORY_SEPARATOR . $filename;

        $movido = @move_uploaded_file($tmp, $filePath);
        if (!$movido) {
            $erroMove = error_get_last()['message'] ?? '';
            if (!@copy($tmp, $filePath)) {
                $erroCopy = error_get_last()['message'] ?? '';
                throw new \RuntimeException(
                    "Nao foi possivel salvar a planilha em {$filePath}. " .
                    "move: [{$erroMove}] copy: [{$erroCopy}]"
                );
            }
        }

        clearstatcache(true, $filePath);
        if (!is_readable($filePath)) {
            throw new \RuntimeException(
                "Planilha salva em {$filePath} mas nao pode ser lida. " .
                'Verifique permissoes do diretorio e a diretiva open_basedir do PHP-FPM.'
            );
        }

        // Valida que e um XLSX de verdade (arquivo ZIP comeca com "PK").
        // Operadoras costumam exportar HTML ou XLS antigo com nome .xlsx —
        // o Spout nao consegue abrir e o erro generico confunde a backoffice.
        $tamanho = filesize($filePath);
        if ($tamanho === 0) {
            @unlink($filePath);
            throw new \RuntimeException('O arquivo enviado chegou vazio (0 bytes). Tente novamente.');
        }
        $inicio = (string) file_get_contents($filePath, false, null, 0, 200);
        $assinatura = substr($inicio, 0, 4);
        if (substr($assinatura, 0, 2) !== 'PK') {
            if ($assinatura === '%PDF') {
                @unlink($filePath);
                throw new \RuntimeException(
                    'O arquivo enviado e um PDF de contrato — este botao e para a PLANILHA (.xlsx) da operadora. ' .
                    'Para cadastrar um contrato em PDF, use o botao "Cadastrar via PDF".'
                );
            }
            $tipo = 'desconhecido';
            if ($assinatura === "\xD0\xCF\x11\xE0") {
                $tipo = 'XLS antigo (Excel 97-2003)';
            } elseif (stripos($inicio, '<html') !== false || stripos($inicio, '<!doctype') !== false || stripos($inicio, '<table') !== false) {
                $tipo = 'HTML disfarcado de planilha';
            } elseif (preg_match('/^[\x20-\x7E\r\n;,\t]+$/', substr($inicio, 0, 60))) {
                $tipo = 'texto/CSV';
            }
            $preview = substr(preg_replace('/[^\x20-\x7E]/', '.', $inicio), 0, 60);
            $hex = strtoupper(bin2hex(substr($inicio, 0, 8)));
            Log::warning("Upload de planilha invalida: tipo={$tipo} tamanho={$tamanho} hex={$hex} inicio=[{$preview}]");
            @unlink($filePath);
            throw new \RuntimeException(
                "O arquivo enviado nao e um XLSX valido (formato detectado: {$tipo}; " .
                "tamanho: {$tamanho} bytes; inicio: [{$preview}] hex: {$hex}). " .
                'Abra a planilha no Excel e salve como "Pasta de Trabalho do Excel (*.xlsx)" antes de enviar.'
            );
        }

        return $filePath;
    }
}
