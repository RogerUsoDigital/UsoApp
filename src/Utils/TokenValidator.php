<?php

declare(strict_types=1);

namespace App\Utils;

use App\Http\Request;

class TokenValidator
{
    private array $tokens;

    public function __construct(?array $tokens = null)
    {
        if ($tokens !== null) {
            $this->tokens = $tokens;
        } else {
            $configFile = __DIR__ . '/../../config/config.php';
            if (file_exists($configFile)) {
                $config = require $configFile;
                $this->tokens = $config['tokens_api'] ?? [];
            } else {
                $this->tokens = [];
            }
        }
    }

    public function validate(mixed $requestOrHeader, string $caso = '', string $empresa = ''): bool
    {
        $authHeader = null;

        if ($requestOrHeader instanceof Request) {
            $authHeader = $requestOrHeader->header('Auth')
                ?? $requestOrHeader->header('Authorization');
        } elseif (is_string($requestOrHeader) || $requestOrHeader === null) {
            $authHeader = $requestOrHeader;
        }

        if (empty($authHeader) || empty($caso) || empty($empresa)) {
            return false;
        }

        $tokenRecebido = $this->extractBearerToken((string) $authHeader);
        if ($tokenRecebido === null) {
            return false;
        }

        $tokenEsperado = $this->tokens[$caso][$empresa] ?? null;
        if ($tokenEsperado === null || $tokenEsperado === '') {
            return false;
        }

        return hash_equals((string) $tokenEsperado, $tokenRecebido);
    }

    private function extractBearerToken(?string $authHeader): ?string
    {
        if ($authHeader === null || trim($authHeader) === '') {
            return null;
        }

        $trimmed = trim($authHeader);
        if (preg_match('/^Bearer\s+(\S+)$/i', $trimmed, $matches) === 1) {
            return $matches[1];
        }

        // Se o header enviado for apenas o token direto (legado sem o prefixo Bearer)
        return $trimmed;
    }
}
