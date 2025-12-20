<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Invoice Controller
 * 
 * Mengelola invoice viewing & operations
 */
class InvoiceController extends Controller
{
    /**
     * Get invoice details
     * GET /api/v1/invoices/{id}
     */
    public function show($id)
    {
        $user = Auth::user();
        $invoice = Invoice::with(['items', 'payment'])->find($id);

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice tidak ditemukan',
            ], 404);
        }

        // Authorization
        if ($invoice->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'data' => [
                'invoice' => $invoice,
                'items' => $invoice->items,
                'status_label' => $invoice->getStatusLabel(),
                'formatted_total' => $invoice->getFormattedTotal(),
                'is_overdue' => $invoice->isOverdue(),
            ],
        ]);
    }

    /**
     * Get user invoices
     * GET /api/v1/invoices
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $invoices = Invoice::byUser($user->id)
            ->with(['payment', 'items'])
            ->orderBy('invoice_date', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'number' => $invoice->invoice_number,
                    'date' => $invoice->invoice_date->toDateString(),
                    'due_date' => $invoice->due_date->toDateString(),
                    'total' => $invoice->getFormattedTotal(),
                    'status' => $invoice->getStatusLabel(),
                    'is_overdue' => $invoice->isOverdue(),
                ];
            }),
            'links' => $invoices->links(),
        ]);
    }

    /**
     * Download invoice as PDF
     * GET /api/v1/invoices/{id}/download
     */
    public function download($id)
    {
        $user = Auth::user();
        $invoice = Invoice::with(['items', 'payment'])->find($id);

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice tidak ditemukan',
            ], 404);
        }

        // Authorization
        if ($invoice->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // TODO: Generate PDF using dompdf
        // For now, return invoice data
        return response()->json([
            'data' => [
                'invoice' => $invoice->load('items'),
                'message' => 'PDF generation ready - implement with dompdf',
            ],
        ]);
    }

    /**
     * Send invoice via email
     * POST /api/v1/invoices/{id}/send
     */
    public function send($id)
    {
        $user = Auth::user();
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice tidak ditemukan',
            ], 404);
        }

        // Authorization
        if ($invoice->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            // TODO: Send email dengan InvoiceMail::class
            // Mail::send(new InvoiceMail($invoice));

            $invoice->markSent();

            return response()->json([
                'data' => [
                    'invoice' => $invoice,
                ],
                'message' => 'Invoice berhasil dikirim',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim invoice',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
