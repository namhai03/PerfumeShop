<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nService
{
    private string $n8nWebhookUrl;
    private string $n8nApiKey;

    public function __construct()
    {
        $this->n8nWebhookUrl = config('services.n8n.webhook_url', '');
        $this->n8nApiKey = config('services.n8n.api_key', '');
    }

    /**
     * Gửi thông báo cảnh báo tồn kho thấp
     */
    public function sendLowStockAlert(array $products): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->n8nApiKey,
                'Content-Type' => 'application/json'
            ])->post($this->n8nWebhookUrl . '/low-stock-alert', [
                'products' => $products,
                'timestamp' => now()->toISOString(),
                'alert_type' => 'low_stock'
            ]);

            if ($response->successful()) {
                Log::info('N8n low stock alert sent successfully', [
                    'products_count' => count($products),
                    'response' => $response->json()
                ]);
                return true;
            }

            Log::error('Failed to send N8n low stock alert', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Exception sending N8n low stock alert', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Gửi thông báo đơn hàng mới
     */
    public function sendNewOrderNotification(array $orderData): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->n8nApiKey,
                'Content-Type' => 'application/json'
            ])->post($this->n8nWebhookUrl . '/new-order', [
                'order' => $orderData,
                'timestamp' => now()->toISOString(),
                'notification_type' => 'new_order'
            ]);

            if ($response->successful()) {
                Log::info('N8n new order notification sent successfully', [
                    'order_id' => $orderData['id'] ?? null,
                    'response' => $response->json()
                ]);
                return true;
            }

            Log::error('Failed to send N8n new order notification', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Exception sending N8n new order notification', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Gửi báo cáo hàng ngày
     */
    public function sendDailyReport(array $reportData): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->n8nApiKey,
                'Content-Type' => 'application/json'
            ])->post($this->n8nWebhookUrl . '/daily-report', [
                'report' => $reportData,
                'timestamp' => now()->toISOString(),
                'report_type' => 'daily_summary'
            ]);

            if ($response->successful()) {
                Log::info('N8n daily report sent successfully', [
                    'date' => $reportData['date'] ?? null,
                    'response' => $response->json()
                ]);
                return true;
            }

            Log::error('Failed to send N8n daily report', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Exception sending N8n daily report', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}

