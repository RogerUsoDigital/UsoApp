<?php

declare(strict_types=1);

namespace App\Controllers\BigQuery;

use App\Http\Request;
use App\Http\Response;
use OpenApi\Attributes as OA;
use App\Services\BigQuery\IndicadoresChatService;

#[OA\Info(
    title: "API USOApp",
    version: "1.0.0",
    description: "Documentação da API USOApp"
)]
#[OA\Tag(name: "Consulta", description: "Endpoints de Consultas BigQuery")]
class ConsultaController
{
    public function __construct(private IndicadoresChatService $service) {}

    #[OA\Post(path: "/indicadores-chats-finalizados/{empresa}")]
    #[OA\Parameter(
        name: "empresa",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Parameter(
        name: "emp",
        in: "query",
        required: false,
        schema: new OA\Schema(type: "string")
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
        $result = $this->service->executar(
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
        in: "query",
        required: true,
        schema: new OA\Schema(type: "string")
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
        return Response::json(
            [
                'mensagem' => 'TESTE'
            ],
            200
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
