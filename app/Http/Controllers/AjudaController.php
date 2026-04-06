<?php

namespace App\Http\Controllers;

class AjudaController extends Controller
{
    public function index()
    {
        $sections = [
            [
                'id' => 'dashboard',
                'title' => 'Dashboard',
                'icon' => 'fa-gauge-high',
                'color' => '#001A72',
                'bg' => '#eff6ff',
                'description' => 'O Dashboard é a tela inicial do sistema. Nele você encontra os cartões de status dos pedidos (Total, Pendentes, Em Cotação, Realizados e Recebidos), atalhos rápidos para os principais módulos e alertas de divergências.',
                'roles' => ['Todos os perfis'],
                'steps' => [
                    ['title' => 'Acesse o sistema', 'desc' => 'Entre com seu usuário e senha na tela de login.'],
                    ['title' => 'Verifique os KPIs', 'desc' => 'Os cartões no topo mostram a quantidade de pedidos por status.'],
                    ['title' => 'Alertas de divergência', 'desc' => 'Se houver itens recebidos com divergência, um painel vermelho aparecerá.'],
                    ['title' => 'Acesse um módulo', 'desc' => 'Clique em qualquer cartão de acesso rápido para navegar diretamente.'],
                ],
                'tips' => [
                    'Pedidos aguardando aprovação não aparecem para compradores.',
                    'Clique no número de pedido na tabela para abrir o detalhe completo.',
                ],
                'faq' => [
                    ['q' => 'Por que não vejo todos os pedidos?', 'a' => 'Solicitantes veem apenas seus próprios pedidos. Compradores e admins veem todos.'],
                    ['q' => 'O sistema atualiza sozinho?', 'a' => 'Os dados são carregados do banco sempre que você acessa a página.'],
                ],
            ],
            [
                'id' => 'pedidos',
                'title' => 'Pedidos',
                'icon' => 'fa-cart-shopping',
                'color' => '#001A72',
                'bg' => '#eff6ff',
                'description' => 'O módulo de Pedidos permite criar solicitações de materiais, acompanhar o status em cada etapa do fluxo e confirmar o recebimento físico.',
                'roles' => ['Solicitante', 'Aprovador', 'Comprador', 'Admin'],
                'steps' => [
                    ['title' => 'Crie o pedido', 'desc' => 'Acesse "Novo Pedido", selecione a unidade e adicione os itens.'],
                    ['title' => 'Aguarde aprovação', 'desc' => 'O aprovador analisará e liberará o pedido.'],
                    ['title' => 'Acompanhe o atendimento', 'desc' => 'O comprador trabalhará na cotação e entrega.'],
                    ['title' => 'Confirme o recebimento', 'desc' => 'Ao receber os materiais, acesse o pedido e confirme cada item.'],
                ],
                'tips' => [
                    'Você pode importar itens via Excel ao criar um pedido.',
                    'Se um item chegou diferente do solicitado, registre a divergência.',
                ],
                'faq' => [
                    ['q' => 'Como criar um pedido?', 'a' => 'Acesse "Novo Pedido", selecione a unidade, adicione itens e salve.'],
                    ['q' => 'Como receber itens?', 'a' => 'Acesse o detalhe do pedido, informe a quantidade recebida de cada item e confirme.'],
                ],
            ],
            [
                'id' => 'transferencias',
                'title' => 'Transferências',
                'icon' => 'fa-right-left',
                'color' => '#9333ea',
                'bg' => '#f3e8ff',
                'description' => 'O módulo de Transferências permite remanejar itens entre pedidos e unidades diferentes.',
                'roles' => ['Comprador', 'Admin'],
                'steps' => [
                    ['title' => 'Acesse Transferências', 'desc' => 'No menu, clique em "Transferências".'],
                    ['title' => 'Selecione a origem', 'desc' => 'Escolha o pedido de origem e o item a transferir.'],
                    ['title' => 'Selecione o destino', 'desc' => 'Escolha o pedido de destino e informe a quantidade.'],
                    ['title' => 'Confirme recebimento', 'desc' => 'Quando o item chegar, informe a quantidade recebida.'],
                ],
                'tips' => [
                    'O remanejamento só funciona com pedidos ativos.',
                    'A unidade de destino pode confirmar a quantidade recebida.',
                ],
                'faq' => [
                    ['q' => 'O que é um remanejamento?', 'a' => 'É a transferência de itens de um pedido/unidade para outro.'],
                ],
            ],
            [
                'id' => 'relatorios',
                'title' => 'Relatórios',
                'icon' => 'fa-chart-bar',
                'color' => '#059669',
                'bg' => '#ecfdf5',
                'description' => 'O módulo de Relatórios apresenta indicadores de desempenho, funil de atendimento e análise por unidade.',
                'roles' => ['Comprador', 'Admin'],
                'steps' => [
                    ['title' => 'Acesse Relatórios', 'desc' => 'No menu, clique em "Relatórios".'],
                    ['title' => 'Aplique filtros', 'desc' => 'Use os filtros de unidade e data para refinar os dados.'],
                    ['title' => 'Analise os KPIs', 'desc' => 'Verifique o total de pedidos, taxa de atendimento e divergências.'],
                    ['title' => 'Imprima ou exporte', 'desc' => 'Use o botão Imprimir para gerar uma cópia.'],
                ],
                'tips' => [
                    'O funil de atendimento mostra a eficiência do processo.',
                    'Filtre por data para comparar períodos.',
                ],
                'faq' => [
                    ['q' => 'Como exportar relatórios?', 'a' => 'Use o botão Imprimir para gerar PDF via navegador.'],
                ],
            ],
            [
                'id' => 'itens',
                'title' => 'Catálogo de Itens',
                'icon' => 'fa-boxes-stacked',
                'color' => '#ea580c',
                'bg' => '#fff7ed',
                'description' => 'O catálogo contém todos os produtos disponíveis para inclusão nos pedidos. É possível buscar, filtrar por tipo e cadastrar novos itens.',
                'roles' => ['Comprador', 'Admin'],
                'steps' => [
                    ['title' => 'Acesse Itens', 'desc' => 'No menu, clique em "Itens".'],
                    ['title' => 'Busque itens', 'desc' => 'Use a barra de busca para encontrar por código, nome ou referência.'],
                    ['title' => 'Filtre por tipo', 'desc' => 'Use o seletor de tipo para ver apenas uma categoria.'],
                    ['title' => 'Cadastre novos', 'desc' => 'Clique em "Novo Item" para adicionar ao catálogo.'],
                ],
                'tips' => [
                    'O tipo de cada item é exibido com uma badge colorida.',
                    'Itens recém-cadastrados ficam disponíveis imediatamente para novos pedidos.',
                ],
                'faq' => [
                    ['q' => 'Posso editar um item existente?', 'a' => 'No momento, edição de itens é feita diretamente no banco de dados.'],
                ],
            ],
            [
                'id' => 'usuarios',
                'title' => 'Usuários',
                'icon' => 'fa-users-gear',
                'color' => '#7c3aed',
                'bg' => '#ede9fe',
                'description' => 'Gerenciamento de acessos, papéis e permissões granulares por módulo.',
                'roles' => ['Admin'],
                'steps' => [
                    ['title' => 'Acesse Usuários', 'desc' => 'No menu, clique em "Usuários".'],
                    ['title' => 'Crie ou edite', 'desc' => 'Use os botões para criar novo ou editar existente.'],
                    ['title' => 'Defina o nível', 'desc' => 'Escolha entre Solicitante, Aprovador, Comprador ou Admin.'],
                    ['title' => 'Configure permissões', 'desc' => 'Defina o escopo de visualização e módulos acessíveis.'],
                ],
                'tips' => [
                    'Cada perfil tem permissões padrão que podem ser personalizadas.',
                    'Admin vê todos os pedidos; Operador vê apenas os próprios.',
                ],
                'faq' => [
                    ['q' => 'Posso remover um usuário?', 'a' => 'Sim, clique no botão Excluir. Você não pode excluir a si mesmo.'],
                ],
            ],
        ];

        return view('ajuda.index', compact('sections'));
    }
}
