<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\BionexoController;
use App\Http\Controllers\NotaFiscalController;
use App\Http\Controllers\AjudaController;
use App\Http\Controllers\HistoricoController;

// Auth routes (no middleware)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth.custom')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Pedidos
    Route::get('/pedidos/novo', [PedidoController::class, 'create'])->name('pedidos.create');
    Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store');
    Route::get('/pedidos/{id}', [PedidoController::class, 'show'])->name('pedidos.show');
    Route::get('/pedidos/{id}/editar', [PedidoController::class, 'edit'])->name('pedidos.edit');
    Route::put('/pedidos/{id}', [PedidoController::class, 'update'])->name('pedidos.update');
    Route::post('/pedidos/{id}/aprovar', [PedidoController::class, 'aprovar'])->name('pedidos.aprovar');
    Route::put('/pedidos/{id}/status', [PedidoController::class, 'updateStatus'])->name('pedidos.updateStatus');
    Route::post('/pedidos/{id}/itens', [PedidoController::class, 'addItem'])->name('pedidos.addItem');
    Route::delete('/pedidos/{pedidoId}/itens/{itemId}', [PedidoController::class, 'removeItem'])->name('pedidos.removeItem');
    Route::put('/pedidos/{pedidoId}/itens/{itemId}/receber', [PedidoController::class, 'receberItem'])->name('pedidos.receberItem');
    Route::put('/pedidos/{pedidoId}/itens/{itemId}/atender', [PedidoController::class, 'atenderItem'])->name('pedidos.atenderItem');
    Route::delete('/pedidos/{id}', [PedidoController::class, 'destroy'])->name('pedidos.destroy');
    Route::post('/pedidos/{id}/espelho', [PedidoController::class, 'uploadEspelho'])->name('pedidos.uploadEspelho');
    Route::post('/pedidos/{id}/confirmar-espelho', [PedidoController::class, 'confirmarEspelho'])->name('pedidos.confirmarEspelho');
    Route::post('/pedidos/parse-pdf', [PedidoController::class, 'parsePdf'])->name('pedidos.parsePdf');

    // Histórico
    Route::get('/historico', [HistoricoController::class, 'index'])->name('historico');

    // Itens (catalog)
    Route::get('/itens', [ItemController::class, 'index'])->name('itens.index');
    Route::post('/itens', [ItemController::class, 'store'])->name('itens.store');
    Route::get('/itens/buscar', [ItemController::class, 'buscar'])->name('itens.buscar');

    // Usuários
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    // Relatórios
    Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios');

    // Transferências (Remanejamentos)
    Route::get('/transferencias', [TransferenciaController::class, 'index'])->name('transferencias.index');
    Route::post('/transferencias', [TransferenciaController::class, 'store'])->name('transferencias.store');
    Route::post('/transferencias/{id}/confirmar', [TransferenciaController::class, 'confirmar'])->name('transferencias.confirmar');

    // Bionexo
    Route::get('/bionexo', [BionexoController::class, 'index'])->name('bionexo');
    Route::post('/bionexo/convert', [BionexoController::class, 'convert'])->name('bionexo.convert');
    Route::post('/bionexo/export', [BionexoController::class, 'export'])->name('bionexo.export');

    // Notas Fiscais
    Route::post('/pedidos/{id}/notas-fiscais', [NotaFiscalController::class, 'store'])->name('notas-fiscais.store');
    Route::put('/notas-fiscais/{id}/status', [NotaFiscalController::class, 'updateStatus'])->name('notas-fiscais.updateStatus');
    Route::post('/notas-fiscais/parse-xml', [NotaFiscalController::class, 'parseXml'])->name('notas-fiscais.parseXml');

    // Ajuda
    Route::get('/ajuda', [AjudaController::class, 'index'])->name('ajuda');
});
