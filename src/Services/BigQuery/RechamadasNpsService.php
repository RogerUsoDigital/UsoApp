<?php

declare(strict_types=1);

namespace App\Services\BigQuery;

use App\Repositories\BigQueryRepository;
use RuntimeException;

class RechamadasNpsService
{
    private ?string $mensagemConfiguracaoGoogleCloud = null;

    public function __construct(
        private BigQueryRepository $bigQueryRepository,
        private array $config = []
    ) {
        if (empty($this->config)) {
            $this->config = require __DIR__ . '/../../../config/config.php';
        }
    }

    public function executar(array $dados, ?string $empresa): array
    {
        // 1. valida corpo
        $validacao = $this->validarEntrada($dados);
        if ($validacao !== null) {
            return $validacao;
        }

        // 2. extrai cliente / numero / documento
        $cliente = $dados['cliente'] ?? null;
        $numero = $dados['numero'] ?? null;
        $documento = $dados['documento'] ?? null;

        // 3. valida empresa
        if (empty($empresa)) {
            return [
                'http_status' => 400,
                'variables' => [
                    'constulta_rechamadas_status' => 'false',
                    'constulta_rechamadas_msg' =>
                        'Faltando parâmetros na URL: emp, ds ou tab.',
                ],
            ];
        }

        // 4. obtém configuração Google Cloud
        $configuracaoGoogleCloud = $this->obterConfiguracaoGoogleCloud($empresa);
        if ($configuracaoGoogleCloud === null) {
            return [
                'http_status' => 400,
                'variables' => [
                    'constulta_rechamadas_status' => 'false',
                    'constulta_rechamadas_msg' => $this->mensagemConfiguracaoGoogleCloud,
                ],
            ];
        }

        // 5. monta query
        $query = $this->montarQuery(
            (string) $cliente,
            $numero === null ? null : (string) $numero,
            $documento === null ? null : (string) $documento
        );

        // 6. executa BigQuery
        try {
            $consultaBQ = $this->bigQueryRepository->query(
                (string) $configuracaoGoogleCloud['conta_auth'],
                (string) $configuracaoGoogleCloud['id_projeto'],
                $query,
                ['cliente' => (string) $cliente]
                    + array_filter([
                        'numero' => $numero ? (string) $numero : null,
                        'documento' => $documento ? (string) $documento : null,
                    ])
            );
        } catch (RuntimeException) {
            $consultaBQ = [];
        }

        // 7. trata retorno
        if (!$consultaBQ) {
            $consultaBQ = 'Nenhum dado encontrado ou erro na consulta.';
        }

        // 8. retorna resposta
        return [
            'http_status' => 200,
            'variables' => [
                'constulta_rechamadas_status' => 'true',
                'constulta_rechamadas_dados' => $consultaBQ,
            ],
        ];
    }

    private function validarEntrada(array $dados): ?array
    {
        if (empty($dados)) {
            return [
                'http_status' => 400,
                'variables' => [
                    'constulta_rechamadas_status' => 'false',
                    'constulta_rechamadas_msg' =>
                        'O corpo da requisição está vazio.',
                ],
            ];
        }

        return null;
    }

    private function obterConfiguracaoGoogleCloud(string $empresa): ?array
    {
        $googleCloud = $this->config['dadosGoogleCloud'] ?? [];
        $contaAuth = $googleCloud['conta'][$empresa] ?? null;
        $idProjeto = $googleCloud['projeto'][$empresa] ?? null;

        if ($contaAuth === null || $idProjeto === null) {
            $this->mensagemConfiguracaoGoogleCloud =
                'Dados de configuração ausentes para a empresa fornecida.';
            return null;
        }

        $arquivoAutenticacao = $googleCloud['autenticacao'][$contaAuth] ?? null;
        if ($arquivoAutenticacao === null) {
            $this->mensagemConfiguracaoGoogleCloud =
                'Arquivo de autenticação ausente para a conta de autenticação fornecida.';
            return null;
        }

        return [
            'conta_auth' => $contaAuth,
            'id_projeto' => $idProjeto,
            'arquivo_autenticacao' => $arquivoAutenticacao,
        ];
    }

    private function montarQuery(
        string $cliente,
        ?string $numero,
        ?string $documento
    ): string {
        if ($numero && $documento) {
            return 'SELECT * FROM `bigquery-usodigital.views.vw_rechamadas_e_nps_diario` '
                . 'WHERE EMPRESA = @cliente AND (NUMERO = @numero OR DOCUMENTO = @documento)';
        }

        if ($numero && !$documento) {
            return 'SELECT * FROM `bigquery-usodigital.views.vw_rechamadas_e_nps_diario` '
                . 'WHERE EMPRESA = @cliente AND NUMERO = @numero';
        }

        if ($documento && !$numero) {
            return 'SELECT * FROM `bigquery-usodigital.views.vw_rechamadas_e_nps_diario` '
                . 'WHERE EMPRESA = @cliente AND DOCUMENTO = @documento';
        }

        return 'SELECT * FROM `bigquery-usodigital.views.vw_rechamadas_e_nps_diario` '
            . 'WHERE EMPRESA = @cliente';
    }
}
