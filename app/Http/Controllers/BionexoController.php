<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BionexoController extends Controller
{
    public function index()
    {
        return view('bionexo.index');
    }

    public function convert(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            $file = $request->file('file');

            $response = Http::attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )->post('http://localhost:5000/converter');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Erro ao converter PDF: ' . $response->body(),
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('Bionexo convert error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Erro ao comunicar com o serviço de conversão. Verifique se o serviço está ativo.',
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'data' => 'required',
        ]);

        try {
            $response = Http::post('http://localhost:5000/export', [
                'data' => $request->input('data'),
            ]);

            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                    ->header('Content-Disposition', 'attachment; filename="bionexo_export.xlsx"');
            }

            return response()->json([
                'error' => 'Erro ao exportar dados: ' . $response->body(),
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('Bionexo export error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Erro ao comunicar com o serviço de exportação. Verifique se o serviço está ativo.',
            ], 500);
        }
    }
}
