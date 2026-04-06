<?php

namespace App\Http\Controllers;

use App\Models\NotaFiscal;
use App\Models\NotaFiscalItem;
use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NotaFiscalController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'xml_file' => 'required|file|max:5120',
        ]);

        $pedido = Pedido::with('itens.item')->findOrFail($id);

        try {
            $xmlFile = $request->file('xml_file');
            $xmlContent = file_get_contents($xmlFile->getRealPath());
            $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);

            if ($xml === false) {
                return redirect()->back()->withErrors(['xml_file' => 'Arquivo XML inválido.']);
            }

            // Register NFe namespace
            $ns = $xml->getNamespaces(true);
            $nfeNs = $ns[''] ?? $ns['nfe'] ?? null;

            if ($nfeNs) {
                $xml->registerXPathNamespace('nfe', $nfeNs);
                $infNfe = $xml->xpath('//nfe:infNFe');
            } else {
                $infNfe = $xml->xpath('//infNFe');
            }

            if (empty($infNfe)) {
                // Try direct access for non-namespaced XML
                $infNfe = [$xml->NFe->infNFe ?? $xml->infNFe ?? null];
            }

            $nfeData = $infNfe[0] ?? null;

            if (!$nfeData) {
                return redirect()->back()->withErrors(['xml_file' => 'Estrutura do XML da NFe não reconhecida.']);
            }

            // Extract header data
            $ide = $nfeData->ide ?? null;
            $emit = $nfeData->emit ?? null;
            $total = $nfeData->total->ICMSTot ?? null;

            $numero = (string) ($ide->nNF ?? '');
            $serie = (string) ($ide->serie ?? '');
            $dataEmissao = (string) ($ide->dhEmi ?? $ide->dEmi ?? '');
            $chaveAcesso = '';

            // Extract chave de acesso from infNFe Id attribute
            $infNfeId = (string) ($nfeData->attributes()['Id'] ?? '');
            if (Str::startsWith($infNfeId, 'NFe')) {
                $chaveAcesso = Str::after($infNfeId, 'NFe');
            }

            $valorTotal = (float) ($total->vNF ?? 0);
            $fornecedorNome = (string) ($emit->xNome ?? '');
            $fornecedorCnpj = (string) ($emit->CNPJ ?? '');

            // Store XML file
            $xmlPath = $xmlFile->store('notas_fiscais/xml', 'local');

            DB::beginTransaction();

            // Create NotaFiscal record
            $notaFiscal = NotaFiscal::create([
                'id'              => Str::uuid()->toString(),
                'pedido_id'       => $pedido->id,
                'numero'          => $numero,
                'serie'           => $serie,
                'chave_acesso'    => $chaveAcesso,
                'data_emissao'    => $dataEmissao ?: null,
                'valor_total'     => $valorTotal,
                'fornecedor_nome' => $fornecedorNome,
                'fornecedor_cnpj' => $fornecedorCnpj,
                'xml_path'        => $xmlPath,
                'status'          => 'pendente',
                'uploaded_by'     => session('usuario')->id,
            ]);

            // Extract items from XML
            $dets = $nfeData->det ?? [];
            $nfItens = [];

            foreach ($dets as $det) {
                $prod = $det->prod;
                $nfItens[] = [
                    'codigo'        => (string) ($prod->cProd ?? ''),
                    'descricao'     => (string) ($prod->xProd ?? ''),
                    'quantidade'    => (float) ($prod->qCom ?? 0),
                    'valor_unitario' => (float) ($prod->vUnCom ?? 0),
                    'valor_total'   => (float) ($prod->vProd ?? 0),
                ];
            }

            // Build lookup map of pedido items by normalized codigo
            $pedidoItensMap = [];
            foreach ($pedido->itens as $pedidoItem) {
                if ($pedidoItem->item) {
                    $normalizedCodigo = mb_strtolower(trim($pedidoItem->item->codigo));
                    $pedidoItensMap[$normalizedCodigo] = $pedidoItem;
                }
            }

            // Create NF items and auto-match
            foreach ($nfItens as $nfItem) {
                $normalizedCodigo = mb_strtolower(trim($nfItem['codigo']));
                $matchedPedidoItem = $pedidoItensMap[$normalizedCodigo] ?? null;

                $confronto = 'nao_encontrado';
                $pedidoItemId = null;

                if ($matchedPedidoItem) {
                    $pedidoItemId = $matchedPedidoItem->id;
                    $expectedQty = $matchedPedidoItem->quantidade_atendida ?: $matchedPedidoItem->quantidade;

                    if ((float) $nfItem['quantidade'] == (float) $expectedQty) {
                        $confronto = 'conforme';
                    } else {
                        $confronto = 'divergente_qtd';
                    }
                }

                NotaFiscalItem::create([
                    'id'              => Str::uuid()->toString(),
                    'nota_fiscal_id'  => $notaFiscal->id,
                    'pedido_item_id'  => $pedidoItemId,
                    'codigo'          => $nfItem['codigo'],
                    'descricao'       => $nfItem['descricao'],
                    'quantidade'      => $nfItem['quantidade'],
                    'valor_unitario'  => $nfItem['valor_unitario'],
                    'valor_total'     => $nfItem['valor_total'],
                    'confronto'       => $confronto,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Nota fiscal importada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao importar nota fiscal: ' . $e->getMessage(), [
                'pedido_id' => $id,
                'trace'     => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withErrors(['xml_file' => 'Erro ao processar XML: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pendente,conferida,divergente',
        ]);

        $notaFiscal = NotaFiscal::findOrFail($id);
        $notaFiscal->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status da nota fiscal atualizado!');
    }

    public function parseXml(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|max:5120',
        ]);

        try {
            $xmlFile = $request->file('xml_file');
            $xmlContent = file_get_contents($xmlFile->getRealPath());
            $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);

            if ($xml === false) {
                return response()->json(['error' => 'Arquivo XML inválido.'], 422);
            }

            // Register NFe namespace
            $ns = $xml->getNamespaces(true);
            $nfeNs = $ns[''] ?? $ns['nfe'] ?? null;

            if ($nfeNs) {
                $xml->registerXPathNamespace('nfe', $nfeNs);
                $infNfe = $xml->xpath('//nfe:infNFe');
            } else {
                $infNfe = $xml->xpath('//infNFe');
            }

            if (empty($infNfe)) {
                $infNfe = [$xml->NFe->infNFe ?? $xml->infNFe ?? null];
            }

            $nfeData = $infNfe[0] ?? null;

            if (!$nfeData) {
                return response()->json(['error' => 'Estrutura do XML da NFe não reconhecida.'], 422);
            }

            $ide = $nfeData->ide ?? null;
            $emit = $nfeData->emit ?? null;
            $total = $nfeData->total->ICMSTot ?? null;

            $infNfeId = (string) ($nfeData->attributes()['Id'] ?? '');
            $chaveAcesso = '';
            if (Str::startsWith($infNfeId, 'NFe')) {
                $chaveAcesso = Str::after($infNfeId, 'NFe');
            }

            $itens = [];
            $dets = $nfeData->det ?? [];
            foreach ($dets as $det) {
                $prod = $det->prod;
                $itens[] = [
                    'codigo'        => (string) ($prod->cProd ?? ''),
                    'descricao'     => (string) ($prod->xProd ?? ''),
                    'quantidade'    => (float) ($prod->qCom ?? 0),
                    'valor_unitario' => (float) ($prod->vUnCom ?? 0),
                    'valor_total'   => (float) ($prod->vProd ?? 0),
                ];
            }

            return response()->json([
                'numero'          => (string) ($ide->nNF ?? ''),
                'serie'           => (string) ($ide->serie ?? ''),
                'chave_acesso'    => $chaveAcesso,
                'data_emissao'    => (string) ($ide->dhEmi ?? $ide->dEmi ?? ''),
                'valor_total'     => (float) ($total->vNF ?? 0),
                'fornecedor_nome' => (string) ($emit->xNome ?? ''),
                'fornecedor_cnpj' => (string) ($emit->CNPJ ?? ''),
                'itens'           => $itens,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao parsear XML: ' . $e->getMessage());

            return response()->json(['error' => 'Erro ao processar XML: ' . $e->getMessage()], 500);
        }
    }
}
