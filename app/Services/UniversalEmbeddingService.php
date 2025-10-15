<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Customer;
use App\Models\Promotion;
use App\Models\UniversalEmbedding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UniversalEmbeddingService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.openai.api_key', '');
        $this->baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $this->model = (string) config('services.openai.embedding_model', 'text-embedding-3-small');
    }

    /**
     * Check if the service is configured.
     */
    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Generate embedding for a given text.
     */
    public function generateEmbedding(string $text): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Embedding service not configured. Please set OPENAI_API_KEY.');
        }

        if (empty(trim($text))) {
            return [];
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/embeddings', [
                    'model' => $this->model,
                    'input' => mb_convert_encoding($text, 'UTF-8', 'auto'),
                    'encoding_format' => 'float'
                ]);

            if (!$response->successful()) {
                Log::error('OpenAI Embedding API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Failed to generate embedding: ' . $response->status());
            }

            $data = $response->json();
            return $data['data'][0]['embedding'] ?? [];

        } catch (\Exception $e) {
            Log::error('Universal Embedding Service Error', [
                'message' => $e->getMessage(),
                'text_length' => strlen($text),
                'text_preview' => substr($text, 0, 100)
            ]);
            throw $e;
        }
    }

    /**
     * Generate embeddings for a product.
     */
    public function generateProductEmbeddings(Product $product): void
    {
        $embeddings = [
            'name' => $this->generateEmbedding($product->name),
            'description' => $this->generateEmbedding($product->description ?? ''),
            'fragrance_notes' => $this->generateEmbedding($this->formatFragranceNotes($product)),
            'combined' => $this->generateEmbedding($this->formatProductCombinedContent($product))
        ];

        foreach ($embeddings as $type => $embedding) {
            if (!empty($embedding)) {
                UniversalEmbedding::updateOrCreate(
                    [
                        'embeddable_type' => Product::class,
                        'embeddable_id' => $product->id,
                        'content_type' => $type
                    ],
                    [
                        'content_text' => $this->getProductContentByType($product, $type),
                        'embedding' => $embedding,
                        'model_name' => $this->model,
                        'metadata' => [
                            'brand' => $product->brand,
                            'category' => $product->category,
                            'price' => $product->selling_price,
                            'stock' => $product->stock,
                            'is_active' => $product->is_active
                        ]
                    ]
                );
            }
        }

        Log::info('Generated embeddings for product', [
            'product_id' => $product->id,
            'product_name' => $product->name
        ]);
    }

    /**
     * Generate embeddings for an order.
     */
    public function generateOrderEmbeddings(Order $order): void
    {
        $embeddings = [
            'order_info' => $this->generateEmbedding($this->formatOrderInfo($order)),
            'customer_info' => $this->generateEmbedding($this->formatOrderCustomerInfo($order)),
            'delivery_info' => $this->generateEmbedding($this->formatOrderDeliveryInfo($order)),
            'combined' => $this->generateEmbedding($this->formatOrderCombinedContent($order))
        ];

        foreach ($embeddings as $type => $embedding) {
            if (!empty($embedding)) {
                UniversalEmbedding::updateOrCreate(
                    [
                        'embeddable_type' => Order::class,
                        'embeddable_id' => $order->id,
                        'content_type' => $type
                    ],
                    [
                        'content_text' => $this->getOrderContentByType($order, $type),
                        'embedding' => $embedding,
                        'model_name' => $this->model,
                        'metadata' => [
                            'order_number' => $order->order_number,
                            'status' => $order->status,
                            'type' => $order->type,
                            'total_amount' => $order->total_amount,
                            'final_amount' => $order->final_amount,
                            'order_date' => $order->order_date?->format('Y-m-d'),
                            'customer_id' => $order->customer_id
                        ]
                    ]
                );
            }
        }

        Log::info('Generated embeddings for order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number
        ]);
    }

    /**
     * Generate embeddings for a shipment.
     */
    public function generateShipmentEmbeddings(Shipment $shipment): void
    {
        $embeddings = [
            'tracking_info' => $this->generateEmbedding($this->formatShipmentTrackingInfo($shipment)),
            'delivery_info' => $this->generateEmbedding($this->formatShipmentDeliveryInfo($shipment)),
            'combined' => $this->generateEmbedding($this->formatShipmentCombinedContent($shipment))
        ];

        foreach ($embeddings as $type => $embedding) {
            if (!empty($embedding)) {
                UniversalEmbedding::updateOrCreate(
                    [
                        'embeddable_type' => Shipment::class,
                        'embeddable_id' => $shipment->id,
                        'content_type' => $type
                    ],
                    [
                        'content_text' => $this->getShipmentContentByType($shipment, $type),
                        'embedding' => $embedding,
                        'model_name' => $this->model,
                        'metadata' => [
                            'order_code' => $shipment->order_code,
                            'tracking_code' => $shipment->tracking_code,
                            'status' => $shipment->status,
                            'carrier' => $shipment->carrier,
                            'cod_amount' => $shipment->cod_amount,
                            'shipping_fee' => $shipment->shipping_fee
                        ]
                    ]
                );
            }
        }

        Log::info('Generated embeddings for shipment', [
            'shipment_id' => $shipment->id,
            'tracking_code' => $shipment->tracking_code
        ]);
    }

    /**
     * Generate embeddings for a customer.
     */
    public function generateCustomerEmbeddings(Customer $customer): void
    {
        $embeddings = [
            'profile' => $this->generateEmbedding($this->formatCustomerProfile($customer)),
            'contact_info' => $this->generateEmbedding($this->formatCustomerContactInfo($customer)),
            'combined' => $this->generateEmbedding($this->formatCustomerCombinedContent($customer))
        ];

        foreach ($embeddings as $type => $embedding) {
            if (!empty($embedding)) {
                UniversalEmbedding::updateOrCreate(
                    [
                        'embeddable_type' => Customer::class,
                        'embeddable_id' => $customer->id,
                        'content_type' => $type
                    ],
                    [
                        'content_text' => $this->getCustomerContentByType($customer, $type),
                        'embedding' => $embedding,
                        'model_name' => $this->model,
                        'metadata' => [
                            'name' => $customer->name,
                            'phone' => $customer->phone,
                            'email' => $customer->email,
                            'customer_type' => $customer->customer_type,
                            'total_spent' => $customer->total_spent,
                            'total_orders' => $customer->total_orders,
                            'is_active' => $customer->is_active
                        ]
                    ]
                );
            }
        }

        Log::info('Generated embeddings for customer', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name
        ]);
    }

    /**
     * Generate embeddings for a promotion.
     */
    public function generatePromotionEmbeddings(Promotion $promotion): void
    {
        $embeddings = [
            'promotion_info' => $this->generateEmbedding($this->formatPromotionInfo($promotion)),
            'terms_conditions' => $this->generateEmbedding($this->formatPromotionTerms($promotion)),
            'combined' => $this->generateEmbedding($this->formatPromotionCombinedContent($promotion))
        ];

        foreach ($embeddings as $type => $embedding) {
            if (!empty($embedding)) {
                UniversalEmbedding::updateOrCreate(
                    [
                        'embeddable_type' => Promotion::class,
                        'embeddable_id' => $promotion->id,
                        'content_type' => $type
                    ],
                    [
                        'content_text' => $this->getPromotionContentByType($promotion, $type),
                        'embedding' => $embedding,
                        'model_name' => $this->model,
                        'metadata' => [
                            'code' => $promotion->code,
                            'name' => $promotion->name,
                            'type' => $promotion->type,
                            'discount_value' => $promotion->discount_value,
                            'is_active' => $promotion->is_active,
                            'start_at' => $promotion->start_at?->format('Y-m-d H:i:s'),
                            'end_at' => $promotion->end_at?->format('Y-m-d H:i:s')
                        ]
                    ]
                );
            }
        }

        Log::info('Generated embeddings for promotion', [
            'promotion_id' => $promotion->id,
            'promotion_code' => $promotion->code
        ]);
    }

    // Product formatting methods
    private function formatFragranceNotes(Product $product): string
    {
        $notes = [];
        
        if ($product->top_notes) {
            $notes[] = "Top notes: " . $product->top_notes;
        }
        if ($product->heart_notes) {
            $notes[] = "Heart notes: " . $product->heart_notes;
        }
        if ($product->base_notes) {
            $notes[] = "Base notes: " . $product->base_notes;
        }
        if ($product->fragrance_family) {
            $notes[] = "Fragrance family: " . $product->fragrance_family;
        }

        return implode('. ', $notes);
    }

    private function formatProductCombinedContent(Product $product): string
    {
        $content = [];
        
        $content[] = $product->name;
        
        if ($product->brand) {
            $content[] = "Brand: " . $product->brand;
        }
        
        if ($product->description) {
            $content[] = $product->description;
        }
        
        if ($product->fragrance_family) {
            $content[] = "Fragrance family: " . $product->fragrance_family;
        }
        
        if ($product->gender) {
            $content[] = "Gender: " . $product->gender;
        }
        
        if ($product->style) {
            $content[] = "Style: " . $product->style;
        }
        
        if ($product->season) {
            $content[] = "Season: " . $product->season;
        }
        
        $notes = $this->formatFragranceNotes($product);
        if ($notes) {
            $content[] = $notes;
        }
        
        if ($product->ingredients) {
            $content[] = "Ingredients: " . $product->ingredients;
        }

        return implode('. ', $content);
    }

    private function getProductContentByType(Product $product, string $type): string
    {
        switch ($type) {
            case 'name':
                return $product->name;
            case 'description':
                return $product->description ?? '';
            case 'fragrance_notes':
                return $this->formatFragranceNotes($product);
            case 'combined':
                return $this->formatProductCombinedContent($product);
            default:
                return '';
        }
    }

    // Order formatting methods
    private function formatOrderInfo(Order $order): string
    {
        $info = [];
        $info[] = "Order: " . $order->order_number;
        $info[] = "Status: " . $order->status_text;
        $info[] = "Type: " . $order->type_text;
        $info[] = "Total: " . number_format($order->total_amount) . " VND";
        $info[] = "Final: " . number_format($order->final_amount) . " VND";
        
        if ($order->notes) {
            $info[] = "Notes: " . $order->notes;
        }

        return implode('. ', $info);
    }

    private function formatOrderCustomerInfo(Order $order): string
    {
        $info = [];
        $info[] = "Customer: " . $order->customer_name;
        $info[] = "Phone: " . $order->phone;
        
        if ($order->customer) {
            $info[] = "Email: " . ($order->customer->email ?? 'N/A');
            $info[] = "Address: " . ($order->customer->address ?? 'N/A');
        }

        return implode('. ', $info);
    }

    private function formatOrderDeliveryInfo(Order $order): string
    {
        $info = [];
        $info[] = "Delivery Address: " . $order->delivery_address;
        $info[] = "Ward: " . $order->ward;
        $info[] = "City: " . $order->city;
        $info[] = "Payment: " . $order->payment_method;
        
        if ($order->delivery_date) {
            $info[] = "Delivery Date: " . $order->delivery_date->format('Y-m-d');
        }

        return implode('. ', $info);
    }

    private function formatOrderCombinedContent(Order $order): string
    {
        $content = [];
        $content[] = $this->formatOrderInfo($order);
        $content[] = $this->formatOrderCustomerInfo($order);
        $content[] = $this->formatOrderDeliveryInfo($order);
        
        return implode('. ', $content);
    }

    private function getOrderContentByType(Order $order, string $type): string
    {
        switch ($type) {
            case 'order_info':
                return $this->formatOrderInfo($order);
            case 'customer_info':
                return $this->formatOrderCustomerInfo($order);
            case 'delivery_info':
                return $this->formatOrderDeliveryInfo($order);
            case 'combined':
                return $this->formatOrderCombinedContent($order);
            default:
                return '';
        }
    }

    // Shipment formatting methods
    private function formatShipmentTrackingInfo(Shipment $shipment): string
    {
        $info = [];
        $info[] = "Tracking Code: " . $shipment->tracking_code;
        $info[] = "Order Code: " . $shipment->order_code;
        $info[] = "Status: " . $shipment->status;
        $info[] = "Carrier: " . $shipment->carrier;
        
        return implode('. ', $info);
    }

    private function formatShipmentDeliveryInfo(Shipment $shipment): string
    {
        $info = [];
        $info[] = "Recipient: " . $shipment->recipient_name;
        $info[] = "Phone: " . $shipment->recipient_phone;
        $info[] = "Address: " . $shipment->address_line;
        $info[] = "Province: " . $shipment->province;
        $info[] = "Ward: " . $shipment->ward;
        $info[] = "COD: " . number_format($shipment->cod_amount) . " VND";
        $info[] = "Shipping Fee: " . number_format($shipment->shipping_fee) . " VND";

        return implode('. ', $info);
    }

    private function formatShipmentCombinedContent(Shipment $shipment): string
    {
        $content = [];
        $content[] = $this->formatShipmentTrackingInfo($shipment);
        $content[] = $this->formatShipmentDeliveryInfo($shipment);
        
        return implode('. ', $content);
    }

    private function getShipmentContentByType(Shipment $shipment, string $type): string
    {
        switch ($type) {
            case 'tracking_info':
                return $this->formatShipmentTrackingInfo($shipment);
            case 'delivery_info':
                return $this->formatShipmentDeliveryInfo($shipment);
            case 'combined':
                return $this->formatShipmentCombinedContent($shipment);
            default:
                return '';
        }
    }

    // Customer formatting methods
    private function formatCustomerProfile(Customer $customer): string
    {
        $info = [];
        $info[] = "Name: " . $customer->name;
        $info[] = "Type: " . $customer->customer_type;
        $info[] = "Gender: " . ($customer->gender ?? 'N/A');
        
        if ($customer->birthday) {
            $info[] = "Birthday: " . $customer->birthday->format('Y-m-d');
        }
        
        $info[] = "Total Spent: " . number_format($customer->total_spent) . " VND";
        $info[] = "Total Orders: " . $customer->total_orders;
        
        if ($customer->note) {
            $info[] = "Note: " . $customer->note;
        }

        return implode('. ', $info);
    }

    private function formatCustomerContactInfo(Customer $customer): string
    {
        $info = [];
        $info[] = "Phone: " . $customer->phone;
        $info[] = "Email: " . ($customer->email ?? 'N/A');
        $info[] = "Address: " . ($customer->address ?? 'N/A');
        $info[] = "City: " . ($customer->city ?? 'N/A');
        $info[] = "District: " . ($customer->district ?? 'N/A');
        $info[] = "Ward: " . ($customer->ward ?? 'N/A');
        $info[] = "Source: " . ($customer->source ?? 'N/A');

        return implode('. ', $info);
    }

    private function formatCustomerCombinedContent(Customer $customer): string
    {
        $content = [];
        $content[] = $this->formatCustomerProfile($customer);
        $content[] = $this->formatCustomerContactInfo($customer);
        
        return implode('. ', $content);
    }

    private function getCustomerContentByType(Customer $customer, string $type): string
    {
        switch ($type) {
            case 'profile':
                return $this->formatCustomerProfile($customer);
            case 'contact_info':
                return $this->formatCustomerContactInfo($customer);
            case 'combined':
                return $this->formatCustomerCombinedContent($customer);
            default:
                return '';
        }
    }

    // Promotion formatting methods
    private function formatPromotionInfo(Promotion $promotion): string
    {
        $info = [];
        $info[] = "Code: " . $promotion->code;
        $info[] = "Name: " . $promotion->name;
        $info[] = "Type: " . $promotion->type;
        $info[] = "Scope: " . $promotion->scope;
        $info[] = "Discount: " . $promotion->discount_value . "%";
        
        if ($promotion->max_discount_amount) {
            $info[] = "Max Discount: " . number_format($promotion->max_discount_amount) . " VND";
        }
        
        if ($promotion->min_order_amount) {
            $info[] = "Min Order: " . number_format($promotion->min_order_amount) . " VND";
        }

        return implode('. ', $info);
    }

    private function formatPromotionTerms(Promotion $promotion): string
    {
        $terms = [];
        
        if ($promotion->description) {
            $terms[] = "Description: " . $promotion->description;
        }
        
        if ($promotion->min_items) {
            $terms[] = "Min Items: " . $promotion->min_items;
        }
        
        if ($promotion->applicable_product_ids) {
            $terms[] = "Applicable Products: " . count($promotion->applicable_product_ids) . " products";
        }
        
        if ($promotion->applicable_category_ids) {
            $terms[] = "Applicable Categories: " . count($promotion->applicable_category_ids) . " categories";
        }
        
        if ($promotion->applicable_customer_group_ids) {
            $terms[] = "Applicable Customer Groups: " . count($promotion->applicable_customer_group_ids) . " groups";
        }
        
        $terms[] = "Stackable: " . ($promotion->is_stackable ? 'Yes' : 'No');
        $terms[] = "Priority: " . $promotion->priority;
        
        if ($promotion->usage_limit) {
            $terms[] = "Usage Limit: " . $promotion->usage_limit;
        }
        
        if ($promotion->usage_limit_per_customer) {
            $terms[] = "Per Customer Limit: " . $promotion->usage_limit_per_customer;
        }

        return implode('. ', $terms);
    }

    private function formatPromotionCombinedContent(Promotion $promotion): string
    {
        $content = [];
        $content[] = $this->formatPromotionInfo($promotion);
        $content[] = $this->formatPromotionTerms($promotion);
        
        return implode('. ', $content);
    }

    private function getPromotionContentByType(Promotion $promotion, string $type): string
    {
        switch ($type) {
            case 'promotion_info':
                return $this->formatPromotionInfo($promotion);
            case 'terms_conditions':
                return $this->formatPromotionTerms($promotion);
            case 'combined':
                return $this->formatPromotionCombinedContent($promotion);
            default:
                return '';
        }
    }

    /**
     * Generate embeddings for all data types.
     */
    public function generateAllEmbeddings(): array
    {
        $results = [
            'products' => 0,
            'orders' => 0,
            'shipments' => 0,
            'customers' => 0,
            'promotions' => 0,
            'errors' => []
        ];

        // Generate product embeddings
        try {
            $products = Product::where('is_active', true)->get();
            foreach ($products as $product) {
                $this->generateProductEmbeddings($product);
                $results['products']++;
            }
        } catch (\Exception $e) {
            $results['errors'][] = "Products: " . $e->getMessage();
        }

        // Generate order embeddings
        try {
            $orders = Order::all();
            foreach ($orders as $order) {
                $this->generateOrderEmbeddings($order);
                $results['orders']++;
            }
        } catch (\Exception $e) {
            $results['errors'][] = "Orders: " . $e->getMessage();
        }

        // Generate shipment embeddings
        try {
            $shipments = Shipment::all();
            foreach ($shipments as $shipment) {
                $this->generateShipmentEmbeddings($shipment);
                $results['shipments']++;
            }
        } catch (\Exception $e) {
            $results['errors'][] = "Shipments: " . $e->getMessage();
        }

        // Generate customer embeddings
        try {
            $customers = Customer::where('is_active', true)->get();
            foreach ($customers as $customer) {
                $this->generateCustomerEmbeddings($customer);
                $results['customers']++;
            }
        } catch (\Exception $e) {
            $results['errors'][] = "Customers: " . $e->getMessage();
        }

        // Generate promotion embeddings
        try {
            $promotions = Promotion::where('is_active', true)->get();
            foreach ($promotions as $promotion) {
                $this->generatePromotionEmbeddings($promotion);
                $results['promotions']++;
            }
        } catch (\Exception $e) {
            $results['errors'][] = "Promotions: " . $e->getMessage();
        }

        return $results;
    }

    /**
     * Get comprehensive embedding statistics.
     */
    public function getComprehensiveStats(): array
    {
        $stats = UniversalEmbedding::getStatsByType();
        
        $totalEmbeddings = UniversalEmbedding::count();
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalShipments = Shipment::count();
        $totalCustomers = Customer::count();
        $totalPromotions = Promotion::count();

        return [
            'total_embeddings' => $totalEmbeddings,
            'by_type' => $stats,
            'coverage' => [
                'products' => [
                    'total' => $totalProducts,
                    'with_embeddings' => UniversalEmbedding::where('embeddable_type', Product::class)->distinct('embeddable_id')->count(),
                    'percentage' => $totalProducts > 0 ? round((UniversalEmbedding::where('embeddable_type', Product::class)->distinct('embeddable_id')->count() / $totalProducts) * 100, 2) : 0
                ],
                'orders' => [
                    'total' => $totalOrders,
                    'with_embeddings' => UniversalEmbedding::where('embeddable_type', Order::class)->distinct('embeddable_id')->count(),
                    'percentage' => $totalOrders > 0 ? round((UniversalEmbedding::where('embeddable_type', Order::class)->distinct('embeddable_id')->count() / $totalOrders) * 100, 2) : 0
                ],
                'shipments' => [
                    'total' => $totalShipments,
                    'with_embeddings' => UniversalEmbedding::where('embeddable_type', Shipment::class)->distinct('embeddable_id')->count(),
                    'percentage' => $totalShipments > 0 ? round((UniversalEmbedding::where('embeddable_type', Shipment::class)->distinct('embeddable_id')->count() / $totalShipments) * 100, 2) : 0
                ],
                'customers' => [
                    'total' => $totalCustomers,
                    'with_embeddings' => UniversalEmbedding::where('embeddable_type', Customer::class)->distinct('embeddable_id')->count(),
                    'percentage' => $totalCustomers > 0 ? round((UniversalEmbedding::where('embeddable_type', Customer::class)->distinct('embeddable_id')->count() / $totalCustomers) * 100, 2) : 0
                ],
                'promotions' => [
                    'total' => $totalPromotions,
                    'with_embeddings' => UniversalEmbedding::where('embeddable_type', Promotion::class)->distinct('embeddable_id')->count(),
                    'percentage' => $totalPromotions > 0 ? round((UniversalEmbedding::where('embeddable_type', Promotion::class)->distinct('embeddable_id')->count() / $totalPromotions) * 100, 2) : 0
                ]
            ],
            'model_used' => $this->model
        ];
    }
}
