<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'legacy-api',
        'env' => $_ENV['APP_ENV'] ?? 'development',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    ],

    // URL para o log do sistema
    'urlLog' => $_ENV['URL_LOG'] ?? '',

    // Dados para autenticação com o google cloud (usamos para o bigquery)
    'dadosGoogleCloud' => [
        'conta' => [
            'uso' => 'usodigital',
            'alares' => 'alares',
        ],
        'projeto' => [
            'uso' => 'bigquery-usodigital',
            'alares' => 'alares-analytics-usodigital',
        ],
        'autenticacao' => [
            'usodigital' => $_ENV['GOOGLE_BQ_USO_AUTH'] ?? '',
            'alares' => $_ENV['GOOGLE_BQ_ALARES_AUTH'] ?? '',
        ],
    ],

    'tratamentoBigQuery' => [
        'gupshup' => 'tratamento_bq_gupshup',
        'gupshup_meta' => 'tratamento_bq_gupshup_meta',
        'pontaltech_sms' => 'tratamento_bq_pontaltech_sms',
        'pontaltech_resposta_sms' => 'tratamento_bq_pontaltech_resposta_sms',
        'zenvia_ag_virtual' => 'tratamento_bq_zenvia_ag_virtual',
        'zenvia_ativas' => 'tratamento_bq_zenvia_ativas',
        'zenvia_recebidas' => 'tratamento_bq_zenvia_recebidas',
        'pontaltech_rcs_multimidia' => 'tratamento_bq_pontaltech_rcs_multimidia',
        'pontaltech_rcs_alcance' => 'tratamento_bq_pontaltech_rcs_alcance',
    ],

    // Dados relacionados à API Huggy
    'dados_huggy' => [
        'url_v2' => $_ENV['HUGGY_APIV2_URL'] ?? '',
        'url_v3' => $_ENV['HUGGY_APIV3_URL'] ?? '',
        'token_alares' => $_ENV['HUGGY_API_TOKEN_ALARES'] ?? '',
        'token_neobpo' => $_ENV['HUGGY_API_TOKEN_NEOBPO'] ?? '',
        ($_ENV['HUGGY_ID_CONTA_NEOBPO'] ?? 'HUGGY_ID_CONTA_NEOBPO') => $_ENV['HUGGY_API_TOKEN_NEOBPO'] ?? '',
        ($_ENV['HUGGY_ID_CONTA_ALARES'] ?? 'HUGGY_ID_CONTA_ALARES') => $_ENV['HUGGY_API_TOKEN_ALARES'] ?? '',
        ($_ENV['HUGGY_ID_CONTA_ALARES_COBRANCA'] ?? 'HUGGY_ID_CONTA_ALARES_COBRANCA') => $_ENV['HUGGY_API_TOKEN_ALARES_COBRANCA'] ?? '',
        ($_ENV['HUGGY_ID_CONTA_ALARES_TELEFONIA'] ?? 'HUGGY_ID_CONTA_ALARES_TELEFONIA') => $_ENV['HUGGY_API_TOKEN_ALARES_TELEFONIA'] ?? '',
        ($_ENV['HUGGY_ID_CONTA_ELO'] ?? 'HUGGY_ID_CONTA_ELO') => $_ENV['HUGGY_API_TOKEN_ELO'] ?? '',
        ($_ENV['HUGGY_ID_CONTA_RESGATE_CTI'] ?? 'HUGGY_ID_CONTA_RESGATE_CTI') => $_ENV['HUGGY_API_TOKEN_RESGATE_CTI'] ?? '',
        ($_ENV['HUGGY_ID_CONTA_T2'] ?? 'HUGGY_ID_CONTA_T2') => $_ENV['HUGGY_API_TOKEN_T2'] ?? '',
        ($_ENV['HUGGY_ID_CONTA_T5_BO_BLUEPHONE'] ?? 'HUGGY_ID_CONTA_T5_BO_BLUEPHONE') => $_ENV['HUGGY_API_TOKEN_T5_BO_BLUEPHONE'] ?? '',
        ($_ENV['HUGGY_ID_CONTA_USODIGITAL'] ?? 'HUGGY_ID_CONTA_USODIGITAL') => $_ENV['HUGGY_API_TOKEN_USODIGITAL'] ?? '',
        'token_generico_v3' => $_ENV['HUGGY_APIV3_TOKEN_GENERICO'] ?? '',
        'token_alares_v3' => $_ENV['HUGGY_APIV3_TOKEN_ALARES'] ?? '',
    ],

    // Dados relacionados à API GupShup
    'dados_gupshup' => [
        'url_v1' => $_ENV['GUPSHUP_APIV1_URL'] ?? '',
        'url_v1_app' => $_ENV['GUPSHUP_APIV1_URL_APP'] ?? '',
    ],

    // URLs de callback
    'urls_callback' => [
        ($_ENV['HUGGY_ID_CONTA_ALARES'] ?? 'HUGGY_ID_CONTA_ALARES') => $_ENV['CALLBACK_AIDA_ALARES'] ?? '',
        ($_ENV['HUGGY_ID_CONTA_NEOBPO'] ?? 'HUGGY_ID_CONTA_NEOBPO') => $_ENV['CALLBACK_AIDA_NEOBPO'] ?? '',
    ],

    // Múltiplos tokens para diferentes ambientes/cases
    'tokens_api' => [
        'uso' => [
            'alares' => $_ENV['TOKEN_REQ_USO_ALARES'] ?? '',
            'neobpo' => $_ENV['TOKEN_REQ_USO_NEOBPO'] ?? '',
            'geral' => $_ENV['TOKEN_REQ_USO_GERAL'] ?? '',
            'baldussi' => $_ENV['TOKEN_REQ_USO_BALDUSSI'] ?? '',
        ],
        'bigquery' => [
            'geral' => $_ENV['TOKEN_REQ_DEV_GERAL'] ?? '',
            'neobpo' => $_ENV['TOKEN_REQ_BQ_NEOBPO'] ?? '',
            'baldussi' => $_ENV['TOKEN_REQ_USO_BALDUSSI'] ?? '',
        ],
        'whatsapp' => [
            'geral' => $_ENV['TOKEN_REQ_WPP_GERAL'] ?? '',
            'zabbix' => $_ENV['TOKEN_REQ_WPP_ZABBIX'] ?? '',
        ],
        'alares' => [
            'geral' => $_ENV['TOKEN_REQ_ALARES_GERAL'] ?? '',
        ],
        'play7' => [
            'geral' => $_ENV['TOKEN_REQ_PLAY7_GERAL'] ?? '',
        ],
    ],

    // Dados de configuração do RabbitMQ
    'configRabbitMQ' => [
        'host' => $_ENV['RABBITMQ_HOST'] ?? '',
        'port' => $_ENV['RABBITMQ_PORT'] ?? '',
        'user' => $_ENV['RABBITMQ_USER'] ?? '',
        'pass' => $_ENV['RABBITMQ_PASS'] ?? '',
    ],

    // Dados relacionados à Zenvia
    'dados_zenvia' => [
        'url_avi' => $_ENV['URL_ZENVIA_AVI'] ?? '',
        'alares' => $_ENV['TOKEN_ZENVIA_ALARES'] ?? '',
        'neobpo' => $_ENV['TOKEN_ZENVIA_NEOBPO'] ?? '',
    ],

    // Dados de configuração de banco de dados
    'configBD' => [
        'host_srv_app' => $_ENV['DB_HOST_SRV_APP'] ?? '',
        'port_geral' => $_ENV['DB_PORT_GERAL'] ?? '',
        'user_appv3' => $_ENV['DB_USER_V3'] ?? '',
        'pass_appv3' => $_ENV['DB_PASS_V3'] ?? '',
        'nome_bd_analitco_appv3' => $_ENV['DB_NAME_ANALITC'] ?? '',
        'nome_bd_app_appv3' => $_ENV['DB_NAME_APP'] ?? '',
        'nome_bd_portal' => $_ENV['DB_NAME_PORTAL'] ?? '',
        'host_srv_pro' => $_ENV['DB_HOST_SRV_PRO'] ?? '',
        'host_srv_cloud' => $_ENV['DB_HOST_SRV_CLOUD'] ?? '',
        'user_portal_baldussi' => $_ENV['DB_USER_PORTAL_BALDUSSI'] ?? '',
        'pass_portal_baldussi' => $_ENV['DB_PASS_PORTAL_BALDUSSI'] ?? '',
    ],

    // Dados relacionados à API V1 ALARES
    'dados_alares' => [
        'url_v1_hmg' => $_ENV['ALARES_API_V1_HMG'] ?? '',
        'url_v1_prod' => $_ENV['ALARES_API_V1_PROD'] ?? '',
        'url_outage' => $_ENV['ALARES_API_OUTAGE'] ?? '',
        'integrationSecretHmg' => $_ENV['INTEGRATION_SECRET_HMG'] ?? '',
        'chaveBaseHmg' => $_ENV['CHAVE_BASE_ALARES_API_V1_HMG'] ?? '',
        'usuarioHmg' => $_ENV['USUARIO_ALARES_API_V1_HMG'] ?? '',
        'integrationSecretProd' => $_ENV['INTEGRATION_SECRET_PROD'] ?? '',
        'chaveBaseProd' => $_ENV['CHAVE_BASE_ALARES_API_V1_PROD'] ?? '',
        'chaveBaseCobrancaProd' => $_ENV['CHAVE_BASE_ALARES_API_V1_COBRANCA_PROD'] ?? '',
        'chaveBaseAtendimentoProd' => $_ENV['CHAVE_BASE_ALARES_API_V1_ATENDIMENTO_PROD'] ?? '',
        'usuarioProd' => $_ENV['USUARIO_ALARES_API_V1_PROD'] ?? '',
        'usuarioCobrancaProd' => $_ENV['USUARIO_ALARES_API_V1_COBRANCA_PROD'] ?? '',
        'usuarioAtendimentoProd' => $_ENV['USUARIO_ALARES_API_V1_ATENDIMENTO_PROD'] ?? '',
        'alaresKey_token_fraude' => $_ENV['ALARES_KEY_TOKEN_FRAUDE'] ?? '',
        'token_outage_check' => $_ENV['TOKEN_ALARES_OUTAGE_CHECK'] ?? '',
        'token_regras_negociacao' => $_ENV['TOKEN_ALARES_REGRAS_NEGOCIACAO'] ?? '',
        'chaveBaseCobrancaVozProd' => $_ENV['CHAVE_BASE_ALARES_API_VOZ_PROD'] ?? '',
        'usuarioCobrancaVozProd' => $_ENV['USUARIO_ALARES_API_VOZ_PROD'] ?? '',
        'integrationSecretVozProd' => $_ENV['INTEGRATION_SECRET_VOZ_PROD'] ?? '',
        'bot_criar_pedido' => [
            'url_hml' => 'https://middleware-comercial.hmg.alaresinternet.com.br/',
            'token_hml' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIyM2E0MWZkMy1lNWYxLTRhNjgtOWMxNS1jYTVhMTZiMzljMDYiLCJpYXQiOjE3NDA3NTc0MDIsImV4cCI6MTc0MDg0MzgwMn0.s_D-TNjgWQ7pe-DcYKAHIxuiCjH1w2Y6GsFjn_rWvNY',
            'url_prd' => 'https://middleware-comercial.prd.alaresinternet.com.br/',
            'token_prd' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJmMzQ0OGQ3OS1lNDQyLTRmMzktOTFlNy1mNzlmNzFhMzZkNTMiLCJpYXQiOjE3NDA3NTc0NDksImV4cCI6MTc0MDg0Mzg0OX0.B8oZzT6239g7Yb4SqcZGt2oQSbKvH8z9-ovENx5VyRY',
            'url_huggy' => 'https://api.huggy.app/v2/flows/',
        ],
    ],

    // Dados para autenticação com o RabbitMQ por ambiente
    'rabbitmq' => [
        'dotapp' => [
            'host' => $_ENV['RABBITMQ_HOST_APP'] ?? '',
            'usuario' => $_ENV['RABBITMQ_USUARIO_APP'] ?? '',
            'senha' => $_ENV['RABBITMQ_SENHA_APP'] ?? '',
        ],
        'usocall' => [
            'host' => $_ENV['RABBITMQ_HOST_USOCALL'] ?? '',
            'usuario' => $_ENV['RABBITMQ_USUARIO_USOCALL'] ?? '',
            'senha' => $_ENV['RABBITMQ_SENHA_USOCALL'] ?? '',
        ],
    ],

    // Webhooks internos
    'webhooksInternos' => [
        'zenvia_agv_usodigital' => [
            'empresa' => 'uso',
            'dataset' => 'usodigital',
            'tabela' => 'agente_virtual',
            'tratamento' => 'zenvia_ag_virtual',
        ],
        'zenvia_recebidas_usodigital' => [
            'empresa' => 'uso',
            'dataset' => 'usodigital',
            'tabela' => 'recebidas',
            'tratamento' => 'zenvia_recebidas',
        ],
        'zenvia_ativas_usodigital' => [
            'empresa' => 'uso',
            'dataset' => 'usodigital',
            'tabela' => 'ligacoes_ativas',
            'tratamento' => 'zenvia_ativas',
        ],
        'zenvia_agv_neobpo' => [
            'empresa' => 'uso',
            'dataset' => 'neobpo',
            'tabela' => 'agente_virtual',
            'tratamento' => 'zenvia_ag_virtual',
        ],
        'zenvia_recebidas_neobpo' => [
            'empresa' => 'uso',
            'dataset' => 'neobpo',
            'tabela' => 'recebidas',
            'tratamento' => 'zenvia_recebidas',
        ],
        'zenvia_ativas_neobpo' => [
            'empresa' => 'uso',
            'dataset' => 'neobpo',
            'tabela' => 'ligacoes_ativas',
            'tratamento' => 'zenvia_ativas',
        ],
        'zenvia_agv_myplace_office' => [
            'empresa' => 'uso',
            'dataset' => 'myplace_office',
            'tabela' => 'agente_virtual',
            'tratamento' => 'zenvia_ag_virtual',
        ],
        'zenvia_recebidas_myplace_office' => [
            'empresa' => 'uso',
            'dataset' => 'myplace_office',
            'tabela' => 'recebidas',
            'tratamento' => 'zenvia_recebidas',
        ],
        'zenvia_ativas_myplace_office' => [
            'empresa' => 'uso',
            'dataset' => 'myplace_office',
            'tabela' => 'ligacoes_ativas',
            'tratamento' => 'zenvia_ativas',
        ],
        'zenvia_agv_grupo_elo' => [
            'empresa' => 'uso',
            'dataset' => 'grupo_elo',
            'tabela' => 'agente_virtual',
            'tratamento' => 'zenvia_ag_virtual',
        ],
        'zenvia_recebidas_grupo_elo' => [
            'empresa' => 'uso',
            'dataset' => 'grupo_elo',
            'tabela' => 'recebidas',
            'tratamento' => 'zenvia_recebidas',
        ],
        'zenvia_ativas_grupo_elo' => [
            'empresa' => 'uso',
            'dataset' => 'grupo_elo',
            'tabela' => 'ligacoes_ativas',
            'tratamento' => 'zenvia_ativas',
        ],
        'zenvia_agv_tmkt_timfibra' => [
            'empresa' => 'uso',
            'dataset' => 'tmkt',
            'tabela' => 'tmkt_timfibra_agente_virtual',
            'tratamento' => 'zenvia_ag_virtual',
        ],
        'zenvia_recebidas_tmkt_timfibra' => [
            'empresa' => 'uso',
            'dataset' => 'tmkt',
            'tabela' => 'tmkt_timfibra_recebidas',
            'tratamento' => 'zenvia_recebidas',
        ],
        'zenvia_ativas_tmkt_timfibra' => [
            'empresa' => 'uso',
            'dataset' => 'tmkt',
            'tabela' => 'tmkt_timfibra_ligacoes_ativas',
            'tratamento' => 'zenvia_ativas',
        ],
        'zenvia_agv_tmkt_timlive' => [
            'empresa' => 'uso',
            'dataset' => 'tmkt',
            'tabela' => 'tmkt_timlive_agente_virtual',
            'tratamento' => 'zenvia_ag_virtual',
        ],
        'zenvia_recebidas_tmkt_timlive' => [
            'empresa' => 'uso',
            'dataset' => 'tmkt',
            'tabela' => 'tmkt_timlive_recebidas',
            'tratamento' => 'zenvia_recebidas',
        ],
        'zenvia_ativas_tmkt_timlive' => [
            'empresa' => 'uso',
            'dataset' => 'tmkt',
            'tabela' => 'tmkt_timlive_ligacoes_ativas',
            'tratamento' => 'zenvia_ativas',
        ],
        'zenvia_agv_tmkt_timlive_vendas' => [
            'empresa' => 'uso',
            'dataset' => 'tmkt',
            'tabela' => 'tmkt_timlive_vendas_agente_virtual',
            'tratamento' => 'zenvia_ag_virtual',
        ],
        'zenvia_recebidas_tmkt_timlive_vendas' => [
            'empresa' => 'uso',
            'dataset' => 'tmkt',
            'tabela' => 'tmkt_timlive_vendas_recebidas',
            'tratamento' => 'zenvia_recebidas',
        ],
        'zenvia_ativas_tmkt_timlive_vendas' => [
            'empresa' => 'uso',
            'dataset' => 'tmkt',
            'tabela' => 'tmkt_timlive_vendas_ligacoes_ativas',
            'tratamento' => 'zenvia_ativas',
        ],
        'zenvia_agv_alares_cob' => [
            'empresa' => 'uso',
            'dataset' => 'alares',
            'tabela' => 'alares_cobranca_agente_virtual',
            'tratamento' => 'zenvia_ag_virtual',
        ],
        'zenvia_recebidas_alares_cob' => [
            'empresa' => 'uso',
            'dataset' => 'alares',
            'tabela' => 'alares_cobranca_recebidas',
            'tratamento' => 'zenvia_recebidas',
        ],
        'zenvia_ativas_alares_cob' => [
            'empresa' => 'uso',
            'dataset' => 'alares',
            'tabela' => 'alares_cobranca_ligacoes_ativas',
            'tratamento' => 'zenvia_ativas',
        ],
        'zenvia_agv_alares_tec' => [
            'empresa' => 'uso',
            'dataset' => 'alares',
            'tabela' => 'alares_tecnico_agente_virtual',
            'tratamento' => 'zenvia_ag_virtual',
        ],
        'zenvia_recebidas_alares_tec' => [
            'empresa' => 'uso',
            'dataset' => 'alares',
            'tabela' => 'alares_tecnico_recebidas',
            'tratamento' => 'zenvia_recebidas',
        ],
        'zenvia_ativas_alares_tec' => [
            'empresa' => 'uso',
            'dataset' => 'alares',
            'tabela' => 'alares_tecnico_ligacoes_ativas',
            'tratamento' => 'zenvia_ativas',
        ],
        'pontal_sms_alares_cob' => [
            'empresa' => 'uso',
            'dataset' => 'alares',
            'tabela_envio' => 'alares_cobranca_envio_sms',
            'tratamento_envio' => 'pontaltech_sms',
            'tabela_resposta' => 'alares_cobranca_resposta_sms',
            'tratamento_resposta' => 'pontaltech_resposta_sms',
            'fila_resposta' => 'alares_resposta_sms',
            'fila_webhook' => 'alares_webhook_sms',
        ],
        'pontal_sms_alares_cob_bq_alares' => [
            'empresa' => 'alares',
            'dataset' => 'usodigital',
            'tabela_envio' => 'sms_status_events',
            'tratamento_envio' => 'pontaltech_sms',
            'fila_webhook' => 'alares_webhook_sms_bq_alares',
        ],
        'pontal_sms_usodigital' => [
            'empresa' => 'uso',
            'dataset' => 'usodigital',
            'tabela_envio' => 'envio_sms',
            'tratamento_envio' => 'pontaltech_sms',
            'tabela_resposta' => 'resposta_sms',
            'tratamento_resposta' => 'pontaltech_resposta_sms',
            'fila_resposta' => 'usodigital_resposta_sms',
            'fila_webhook' => 'usodigital_webhook_sms',
        ],
        'pontal_sms_motiva' => [
            'empresa' => 'uso',
            'dataset' => 'motiva',
            'tabela_envio' => 'envio_sms',
            'tratamento_envio' => 'pontaltech_sms',
            'tabela_resposta' => 'resposta_sms',
            'tratamento_resposta' => 'pontaltech_resposta_sms',
            'fila_resposta' => 'motiva_resposta_sms',
            'fila_webhook' => 'motiva_webhook_sms',
        ],
        'pontal_sms_allu' => [
            'empresa' => 'uso',
            'dataset' => 'allu',
            'tabela_envio' => 'envio_sms',
            'tratamento_envio' => 'pontaltech_sms',
            'tabela_resposta' => 'resposta_sms',
            'tratamento_resposta' => 'pontaltech_resposta_sms',
            'fila_resposta' => 'allu_resposta_sms',
            'fila_webhook' => 'allu_webhook_sms',
        ],
    ],

    // Contas e visualizações de alertas
    'contasAlertas' => [
        '343031' => [
            'banco-diario' => 'bigquery-usodigital.views_alares.vw_chat_finalizados_diario',
        ],
        '344413' => [
            'banco-diario' => 'bigquery-usodigital.views_alares.vw_chat_finalizados_diario',
        ],
        '341975' => [
            'banco-diario' => 'bigquery-usodigital.views_alares.vw_chat_finalizados_diario',
        ],
        '318772' => [
            'banco-diario' => 'bigquery-usodigital.views_usodigital.vw_chat_finalizados_diario',
        ],
        '328189' => [
            'banco-diario' => 'bigquery-usodigital.views_bluephone.vw_chat_finalizados_diario',
        ],
    ],

    // Integração zAPI
    'zAPI' => [
        'token' => $_ENV['TOKEN_ZAPI'] ?? '',
        'url' => $_ENV['URL_ZAPI'] ?? '',
        'contas' => [
            'zabbix' => [
                'instancia' => '3EDD241451154280A52576D5B3F5954F',
                'token' => '88AE69CB5D3FA3A531C61320',
            ],
        ],
    ],

    'portals' => [
        'meuportal-online' => [
            'host' => $_ENV['DB_MEUPORTAL_HOST'],
            'database' => $_ENV['DB_MEUPORTAL_DATABASE'],
            'username' => $_ENV['DB_MEUPORTAL_USERNAME'],
            'password' => $_ENV['DB_MEUPORTAL_PASSWORD'],
        ],

        'usodigital-net' => [
            'host' => $_ENV['DB_USODIGITAL_NET_HOST'],
            'database' => $_ENV['DB_USODIGITAL_NET_DATABASE'],
            'username' => $_ENV['DB_USODIGITAL_NET_USERNAME'],
            'password' => $_ENV['DB_USODIGITAL_NET_PASSWORD'],
        ],

        'usodigital-cloud' => [
            'host' => $_ENV['DB_USODIGITAL_CLOUD_HOST'],
            'database' => $_ENV['DB_USODIGITAL_CLOUD_DATABASE'],
            'username' => $_ENV['DB_USODIGITAL_CLOUD_USERNAME'],
            'password' => $_ENV['DB_USODIGITAL_CLOUD_PASSWORD'],
        ],

        'usodigital-pro' => [
            'host' => $_ENV['DB_USODIGITAL_PRO_HOST'],
            'database' => $_ENV['DB_USODIGITAL_PRO_DATABASE'],
            'username' => $_ENV['DB_USODIGITAL_PRO_USERNAME'],
            'password' => $_ENV['DB_USODIGITAL_PRO_PASSWORD'],
        ],
    ],

    'db' => [
        'host' => $_ENV['DB_HOST'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? '',
        'database' => $_ENV['DB_DATABASE'] ?? '',
        'username' => $_ENV['DB_USERNAME'] ?? '',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
    ],

    'http' => [
        'timeout' => (float) ($_ENV['HTTP_TIMEOUT'] ?? 30.0),
    ],
];
