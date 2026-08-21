<?php

declare(strict_types=1);

namespace App\Controllers\BigQuery;

use App\Http\Request;
use App\Http\Response;
use OpenApi\Attributes as OA;
use App\Services\BigQuery\IndicadoresChatService;
use App\Services\BigQuery\RechamadasNpsService;

#[OA\Info(
    title: "API USOApp",
    version: "1.0.0",
    description: "Documentação da API USOApp"
)]
#[OA\Tag(name: "Consulta", description: "Endpoints de Consultas BigQuery")]
class ConsultaController
{
    public function __construct(
        private IndicadoresChatService $indicadoresChatService,
        private RechamadasNpsService $rechamadasNpsService
    ) {}

    #[OA\Post(path: "/indicadores-chats-finalizados/{empresa}")]
    #[OA\Parameter(
        name: "empresa",
        in: "path",
        required: true,
        description: "Empresa usada quando o parâmetro emp não é informado.",
        schema: new OA\Schema(type: "string"),
        example: "uso"
    )]
    #[OA\Parameter(
        name: "emp",
        in: "query",
        required: false,
        description: "Empresa que sobrescreve o valor informado na rota.",
        schema: new OA\Schema(type: "string"),
        example: "uso"
    )]
    #[OA\RequestBody(
        required: true,
        description: "Dados necessários para consultar e registrar os indicadores do chat.",
        content: new OA\JsonContent(
            required: ["cliente", "numero", "indicador", "id_chat", "portal"],
            properties: [
                new OA\Property(property: "cliente", type: "string", example: "USO DIGITAL"),
                new OA\Property(property: "numero", type: "string", example: "5511999999999"),
                new OA\Property(
                    property: "indicador",
                    type: "string",
                    example: "[rechamada_hoje,ultimo_nps]",
                    description: "Lista de indicadores separada por vírgula."
                ),
                new OA\Property(property: "id_chat", type: "integer", example: 12345),
                new OA\Property(property: "portal", type: "string", example: "meuportal-online"),
                new OA\Property(property: "documento_1", type: "string", nullable: true, example: "12345678900"),
                new OA\Property(property: "documento_2", type: "string", nullable: true, example: "10987654321"),
                new OA\Property(property: "id_empresa", type: "string", nullable: true, example: "1"),
                new OA\Property(property: "id_departamento", type: "string", nullable: true, example: "10"),
                new OA\Property(
                    property: "dados_adicionais",
                    type: "object",
                    nullable: true,
                    additionalProperties: true,
                    example: ["origem" => "chat"]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Indicadores do chat",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "mensagem", type: "string"),
            ]
        )
    )]
    public function indicadoresChatsFinalizados(Request $request): Response
    {
        $result = $this->indicadoresChatService->executar(
            $request->body(),
            $request->query('emp', $request->route('empresa'))
        );

        return Response::json(
            $result,
            $result['http_status'] ?? 200
        );
    }

    #[OA\Post(path:'/rechamadasNps/{empresa}')]
    #[OA\Parameter(
        name: "empresa",
        in: "path",
        required: true,
        description: "Empresa usada quando o parâmetro emp não é informado.",
        schema: new OA\Schema(type: "string"),
        example: "uso"
    )]
    #[OA\Parameter(
        name: "emp",
        in: "query",
        required: false,
        description: "Empresa que sobrescreve o valor informado na rota.",
        schema: new OA\Schema(type: "string"),
        example: "uso"
    )]
    #[OA\RequestBody(
        required: true,
        description: "Critérios para consulta de rechamadas e NPS.",
        content: new OA\JsonContent(
            required: ["cliente"],
            properties: [
                new OA\Property(property: "cliente", type: "string", example: "USO DIGITAL"),
                new OA\Property(
                    property: "numero",
                    type: "string",
                    nullable: true,
                    example: "5511999999999",
                    description: "Quando informado com documento, a consulta usa numero OU documento."
                ),
                new OA\Property(
                    property: "documento",
                    type: "string",
                    nullable: true,
                    example: "12345678900",
                    description: "Quando informado com numero, a consulta usa numero OU documento."
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Rechamadas NPS",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "mensagem", type: "string"),
            ]
        )
    )]
    public function rechamadasNps(Request $request): Response
    {
        $result = $this->rechamadasNpsService->executar(
            $request->body(),
            $request->query('emp', $request->route('empresa'))
        );

        return Response::json(
            $result,
            $result['http_status'] ?? 200
        );
    }

    #[OA\Post(path:'/bd-nps/{empresa}')]
    #[OA\Parameter(
        name: "empresa",
        in: "query",
        required: true,
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "BD NPS",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "mensagem", type: "string"),
            ]
        )
    )]
    public function bdNps(Request $request): Response
    {
        return Response::json(
            [
                'mensagem' => 'TESTE'
            ],
            200
        );
    }
    
    #[OA\Post(path:'/resposta-sms/{empresa}')]
    #[OA\Parameter(
        name: "empresa",
        in: "query",
        required: true,
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Respostas SMS",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "mensagem", type: "string"),
            ]
        )
    )]
    public function respostaSms(Request $request): Response
    {
        return Response::json(
            [
                'mensagem' => 'TESTE'
            ],
            200
        );
    }
    
    #[OA\Post(path:'/helpdesk/{empresa}')]
    #[OA\Parameter(
        name: "empresa",
        in: "query",
        required: true,
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Helpdesk",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "mensagem", type: "string"),
            ]
        )
    )]
    public function helpdesk(Request $request): Response
    {
        return Response::json(
            [
                'mensagem' => 'TESTE'
            ],
            200
        );
    }
    
    #[OA\Post(path:'/status-meta/{empresa}')]
    #[OA\Parameter(
        name: "empresa",
        in: "query",
        required: true,
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Status Meta",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "mensagem", type: "string"),
            ]
        )
    )]
    public function statusMeta(Request $request): Response
    {
        return Response::json(
            [
                'mensagem' => 'TESTE'
            ],
            200
        );
    }
}

class_alias(ConsultaController::class, 'consultaController');
