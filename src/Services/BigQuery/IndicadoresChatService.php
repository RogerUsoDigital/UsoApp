<?php

declare(strict_types=1);

namespace App\Services\BigQuery;

use App\Repositories\BigQueryRepository;
use App\Repositories\Bigquery\IndicadoresRepository;
use RuntimeException;

class IndicadoresChatService
{
    private const MAX_INDICADORES = 10;

    public function __construct(
        private BigQueryRepository $bigQuery,
        private array $config = []
    ) {
        if (empty($this->config)) {
            $configFile = __DIR__ . '/../../../config/config.php';
            if (file_exists($configFile)) {
                $this->config = require $configFile;
            }
        }
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    public function executar(array $conteudo, ?string $empresa): array
    {
        // 1. valida entrada
        $validacao = $this->validarEntrada($conteudo, $empresa);

        if ($validacao !== null) {
            return $validacao;
        }

        // 2. extrai dados
        $dados = $this->extrairDados($conteudo, (string) $empresa);

        // 3. resolve configuração Google
        $configGoogle = $this->obterConfiguracaoGoogleCloud((string) $empresa);

        if (empty($configGoogle['success'])) {
            return $configGoogle;
        }

        $contaAuth = (string) $configGoogle['conta_auth'];
        $idProjeto = (string) $configGoogle['id_projeto'];

        // 4. executa indicadores no BigQuery
        $cliente = (string) ($dados['cliente'] ?? '');
        $numero  = (string) ($dados['numero']  ?? '');
        $indicadores = $dados['indicador_array'] ?? [];

        try {
            $dadosBQArray = $this->consultarIndicadores(
                $indicadores,
                $cliente,
                $numero,
                $contaAuth,
                $idProjeto
            );
        } catch (RuntimeException $e) {
            error_log('Erro ao consultar BigQuery: ' . $e->getMessage());
            return $this->erro('Erro ao consultar os indicadores no BigQuery.');
        }

        // 5. adiciona dados_adicionais
        $dadosBQArray = $this->adicionarDadosAdicionais(
            $dadosBQArray,
            $dados['dados_adicionais'] ?? null
        );

        // 6. monta bodyFinal
        $idChat = $dados['id_chat'] ?? null;
        $portal = (string) ($dados['portal'] ?? '');

        if (($this->config['app']['env'] ?? '') === 'development') {
            $portal = 'homolog';
        }

        $bodyFinal = $this->montarBodyFinal($idChat, $numero, $dadosBQArray);

        // 7. resolve configuração do banco
        $configBanco = $this->obterConfiguracaoBanco($portal);

        if ($configBanco === null) {
            return $this->erro('Configuração de banco não encontrada para o portal informado.');
        }

        // 8. conecta ao banco
        try {
            $indicadoresRepository = $this->criarRepositoryIndicadores($configBanco);
        } catch (RuntimeException $e) {
            return $this->erro('Falha ao conectar ao banco de dados.');
        }

        // 9. valida e verifica ID_CHAT
        $idChatInt = $this->resolverIdChat($idChat);

        if ($idChatInt === null) {
            return $this->erro('ID_CHAT inválido ou não informado.');
        }

        try {
            $jaExiste = $indicadoresRepository->existsByChatId($idChatInt);
        } catch (RuntimeException $e) {
            return $this->erro('Erro ao verificar ID_CHAT no banco de dados.');
        }

        if ($jaExiste) {
            return [
                'http_status' => 200,
                'variables' => [
                    'error_code' => '409',
                    'constulta_indicadores_status' => 'false',
                    'constulta_indicadores_msg' => 'ID_CHAT já registrado. Nenhuma inserção realizada.'
                ]
            ];
        }

        // 10. prepara indicadores para o INSERT
        $indicadoresPreparados = $this->prepararIndicadoresParaInsert($dadosBQArray);

        // 11. monta a resposta do INSERT
        try {
            $resultadoInsert = $indicadoresRepository->insert(
                $idChatInt,
                $numero,
                $indicadoresPreparados
            );
        } catch (RuntimeException $e) {
            error_log('Erro ao inserir indicadores: ' . $e->getMessage());
            return $this->erro('Erro ao inserir indicadores no banco de dados.');
        }

        $respostaSTMT = !empty($resultadoInsert['success'])
            ? 'Dados inseridos com sucesso.'
            : 'Nenhum dado inserido ou erro na inserção.';

        return [
            'http_status' => 200,
            'variables' => [
                'constulta_indicadores_status' => 'true',
                'constulta_indicadores_dados' => $bodyFinal,
                'consulta_indicadores_resposta' => $respostaSTMT,
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Validação e extração
    // -------------------------------------------------------------------------

    private function validarEntrada(array $conteudo, ?string $empresa): ?array
    {
        if (empty($conteudo)) {
            return $this->erro('O corpo da requisição está vazio.');
        }

        if (empty($empresa)) {
            return $this->erro('Faltando parâmetros na URL: emp.');
        }

        return null;
    }

    private function extrairDados(array $conteudo, string $empresa): array
    {
        $indicador = $conteudo['indicador'] ?? null;

        $indicadorArray = [];

        if ($indicador !== null) {
            $indicadorArray = array_map(
                'trim',
                explode(',', trim($indicador, '[]'))
            );
        }

        return [
            'empresa'         => $empresa,
            'cliente'         => $conteudo['cliente']         ?? null,
            'numero'          => $conteudo['numero']          ?? null,
            'documento_1'     => $conteudo['documento_1']     ?? null,
            'documento_2'     => $conteudo['documento_2']     ?? null,
            'indicador'       => $indicador,
            'indicador_array' => $indicadorArray,
            'id_chat'         => $conteudo['id_chat']         ?? null,
            'id_empresa'      => $conteudo['id_empresa']      ?? null,
            'id_departamento' => $conteudo['id_departamento'] ?? null,
            'portal'          => $conteudo['portal']          ?? null,
            'dados_adicionais'=> $conteudo['dados_adicionais']?? null,
        ];
    }

    // -------------------------------------------------------------------------
    // Google Cloud / BigQuery
    // -------------------------------------------------------------------------

    private function obterConfiguracaoGoogleCloud(string $empresa): array
    {
        $googleCloud = $this->config['dadosGoogleCloud'] ?? [];

        $contaAuth = $googleCloud['conta'][$empresa] ?? null;
        $idProjeto = $googleCloud['projeto'][$empresa] ?? null;

        if ($contaAuth === null || $idProjeto === null) {
            return [
                'success' => false,
                'http_status' => 200,
                'variables' => [
                    'error_code' => '400',
                    'constulta_indicadores_status' => 'false',
                    'constulta_indicadores_msg' =>
                        'Dados de configuração ausentes para a empresa fornecida.'
                ]
            ];
        }

        $arquivoAutenticacao = $googleCloud['autenticacao'][$contaAuth] ?? null;

        if ($arquivoAutenticacao === null) {
            return [
                'success' => false,
                'http_status' => 200,
                'variables' => [
                    'error_code' => '400',
                    'constulta_indicadores_status' => 'false',
                    'constulta_indicadores_msg' =>
                        'Arquivo de autenticação ausente para a conta de autenticação fornecida.'
                ]
            ];
        }

        return [
            'success'              => true,
            'conta_auth'           => $contaAuth,
            'id_projeto'           => $idProjeto,
            'arquivo_autenticacao' => $arquivoAutenticacao,
        ];
    }

    private function getQueryIndicador(string $indicador, string $cliente, string $numero): ?string
    {
        $clienteSanitizado = addslashes($cliente);
        $numeroSanitizado  = addslashes($numero);

        return match ($indicador) {
            'rechamada_hoje' => sprintf(
                "SELECT COUNT(*) AS total FROM `bigquery-usodigital.views.vw_indicadores_chat_finalizados_diario` WHERE empresa = '%s' AND numero = '%s'",
                $clienteSanitizado,
                $numeroSanitizado
            ),

            'ultimo_nps' => sprintf(
                "SELECT IFNULL(
                    (
                        SELECT CAST(nps AS STRING) FROM `bigquery-usodigital.views.vw_indicadores_chat_finalizados_diario`
                        WHERE empresa = '%s' AND numero = '%s' AND nps IS NOT NULL
                        ORDER BY CAST(chat_id AS INT64) DESC LIMIT 1
                    ),
                    'SEM DADOS'
                ) AS total",
                $clienteSanitizado,
                $numeroSanitizado
            ),

            'rechamada_departamento_hoje' => sprintf(
                "SELECT STRING_AGG(
                    FORMAT('%%s: %%d', departamento, total),
                    '  ||  '
                ) AS total
                FROM (
                    SELECT departamento, COUNT(*) AS total
                    FROM `bigquery-usodigital.views.vw_indicadores_chat_finalizados_diario`
                    WHERE empresa = '%s' AND numero = '%s' GROUP BY departamento
                )",
                $clienteSanitizado,
                $numeroSanitizado
            ),

            default => null,
        };
    }

    private function consultarIndicadores(
        array $indicadores,
        string $cliente,
        string $numero,
        string $contaAuth,
        string $idProjeto
    ): array {
        $dadosBQArray = [];

        foreach ($indicadores as $indicador) {
            $query = $this->getQueryIndicador($indicador, $cliente, $numero);

            if ($query === null) {
                continue;
            }

            $dadosBQ = $this->bigQuery->query($contaAuth, $idProjeto, $query);

            $dadosBQArray[$indicador] = $dadosBQ[0]['total'] ?? 0;
        }

        return $dadosBQArray;
    }

    private function adicionarDadosAdicionais(array $dadosBQArray, mixed $dadosAdicionais): array
    {
        if (!empty($dadosAdicionais)) {
            $dadosBQArray['dados_adicionais'] = $dadosAdicionais;
        }

        return $dadosBQArray;
    }

    // -------------------------------------------------------------------------
    // Banco de dados
    // -------------------------------------------------------------------------

    /**
     * Resolve a configuração do banco a partir do portal informado.
     * Lê exclusivamente do config (populado pelo .env).
     *
     * @return array{host: string, database: string, username: string, password: string}|null
     */
    private function obterConfiguracaoBanco(string $portal): ?array
    {
        $portals = $this->config['portals'] ?? [];

        if (!isset($portals[$portal])) {
            return null;
        }

        $cfg = $portals[$portal];

        if (empty($cfg['host']) || empty($cfg['database']) || empty($cfg['username'])) {
            return null;
        }

        return [
            'host'     => $cfg['host'],
            'database' => $cfg['database'],
            'username' => $cfg['username'],
            'password' => $cfg['password'] ?? '',
        ];
    }

    /**
     * Converte o id_chat recebido do payload para inteiro válido (>= 1).
     * Retorna null se o valor for inválido, vazio ou zero.
     */
    /** @param array{host: string, database: string, username: string, password: string} $configBanco */
    protected function criarRepositoryIndicadores(array $configBanco): IndicadoresRepository
    {
        return new IndicadoresRepository($configBanco);
    }

    private function resolverIdChat(mixed $idChat): ?int
    {
        if ($idChat === null || $idChat === '' || $idChat === false) {
            return null;
        }

        if (!is_numeric($idChat)) {
            return null;
        }

        $valor = (int) $idChat;

        return $valor >= 1 ? $valor : null;
    }

    // -------------------------------------------------------------------------
    // Montagem de dados
    // -------------------------------------------------------------------------

    private function montarBodyFinal(mixed $idChat, string $numero, array $dadosBQArray): array
    {
        return [
            'id_chat'     => $idChat,
            'numero'      => $numero,
            'indicadores' => $dadosBQArray,
        ];
    }

    /**
     * Prepara os indicadores do BigQuery para o INSERT na tb_neg_indicadores.
     *
     * - Respeita o limite de 10 indicadores (INDICADOR_1..INDICADOR_10 / CONTEUDO_1..CONTEUDO_10).
     * - Remove a chave 'dados_adicionais' pois ela não é uma coluna de indicador.
     * - Retorna array indexado de 1 a 10; posições sem indicador ficam com null.
     *
     * Exemplo de retorno:
     * [
     *   1 => ['indicador' => 'rechamada_hoje', 'conteudo' => 10],
     *   2 => ['indicador' => 'ultimo_nps',     'conteudo' => '9'],
     *   3 => ['indicador' => null, 'conteudo' => null],
     *   ...
     * ]
     *
     * @return array<int, array{indicador: string|null, conteudo: mixed}>
     */
    private function prepararIndicadoresParaInsert(array $dadosBQArray): array
    {
        // Remove metadados que não são indicadores do BigQuery
        $somenteBQ = array_filter(
            $dadosBQArray,
            fn ($chave) => $chave !== 'dados_adicionais',
            ARRAY_FILTER_USE_KEY
        );

        // Respeita o limite legado de 10 colunas
        $limitados = array_slice($somenteBQ, 0, self::MAX_INDICADORES, true);

        // Transforma em estrutura indexada de 1 a MAX_INDICADORES
        $preparados = [];
        $posicao = 1;

        foreach ($limitados as $indicador => $conteudo) {
            $preparados[$posicao] = [
                'indicador' => $indicador,
                'conteudo'  => $conteudo,
            ];
            $posicao++;
        }

        // Preenche posições restantes com null para manter a estrutura completa
        while ($posicao <= self::MAX_INDICADORES) {
            $preparados[$posicao] = [
                'indicador' => null,
                'conteudo'  => null,
            ];
            $posicao++;
        }

        return $preparados;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function erro(string $mensagem): array
    {
        return [
            'http_status' => 200,
            'variables' => [
                'error_code' => '400',
                'constulta_indicadores_status' => 'false',
                'constulta_indicadores_msg' => $mensagem
            ]
        ];
    }
}
