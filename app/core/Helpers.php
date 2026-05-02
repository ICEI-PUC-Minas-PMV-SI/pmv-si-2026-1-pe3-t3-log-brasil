<?php

declare(strict_types=1);

namespace App\Core;

final class Helpers
{
    /** Substitui entidades HTML mas mantém entrada legível para exibição. */
    public static function e(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $path): void
    {
        if ($path !== '' && $path[0] === '/') {
            $target = CONF_BASE_URL . $path;
        } else {
            $target = $path;
        }
        header('Location: ' . $target, true, 302);
        exit;
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return (string) $_SESSION['_csrf'];
    }

    public static function csrfVerify(?string $token): bool
    {
        return is_string($token)
            && isset($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $token);
    }

    /**
     * Valida e move upload para public/uploads/$subdir. Retorno relativo ao diretório uploads/.
     *
     * @param list<string> $mimeOk Ex.: ['image/jpeg','image/png','image/webp']
     *
     * @throws \RuntimeException
     */
    public static function saveUploadedSecure(array $file, string $subdir, array $mimeOk, int $maxBytes): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Falha no envio do arquivo.');
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || ! is_readable($tmp)) {
            throw new \RuntimeException('Upload temporário não disponível.');
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            throw new \RuntimeException('Arquivo muito grande ou vazio.');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmp);
        if (! in_array($mime, $mimeOk, true)) {
            throw new \RuntimeException('Tipo de arquivo não permitido.');
        }

        $map = [
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/webp' => '.webp',
        ];
        $ext = $map[$mime] ?? '';
        if ($ext === '') {
            throw new \RuntimeException('Extensão não mapeada para o tipo detectado.');
        }

        $subdir = trim(str_replace(['..', '\\'], '', $subdir), '/');
        if ($subdir === '') {
            throw new \RuntimeException('Subdiretório de upload inválido.');
        }

        $pubBase = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        $pub = $pubBase . str_replace('/', DIRECTORY_SEPARATOR, $subdir);
        if (! is_dir($pub) && ! mkdir($pub, 0775, true) && ! is_dir($pub)) {
            throw new \RuntimeException('Não foi possível criar diretório de upload.');
        }

        $nome = bin2hex(random_bytes(16)) . $ext;
        $dest = $pub . DIRECTORY_SEPARATOR . $nome;
        if (! move_uploaded_file($tmp, $dest)) {
            throw new \RuntimeException('Não foi possível gravar o arquivo enviado.');
        }

        return $subdir . '/' . $nome;
    }
}
