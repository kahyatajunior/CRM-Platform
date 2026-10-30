<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $customer->name }} | Customer 360</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="max-w-7xl mx-auto py-10 px-4">
        <header class="mb-8 flex justify-between items-center">
            <div>
                <p class="text-sm uppercase text-sky-700 font-semibold">Customer profile</p>
                <h1 class="text-3xl font-bold">{{ $customer->name }}</h1>
            </div>
            <span class="rounded-full bg-green-100 text-green-800 px-3 py-1 text-sm font-medium">{{ $customer->status }}</span>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Customer overview</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div><span class="text-slate-500">Company:</span> <strong>{{ $customer->company ?? 'N/A' }}</strong></div>
                        <div><span class="text-slate-500">Email:</span> <strong>{{ $customer->email ?? 'N/A' }}</strong></div>
                        <div><span class="text-slate-500">Phone:</span> <strong>{{ $customer->phone ?? 'N/A' }}</strong></div>
                        <div><span class="text-slate-500">Source:</span> <strong>{{ $customer->source ?? 'N/A' }}</strong></div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Lead history</h2>
                    @forelse($customer->leads as $lead)
                        <div class="border-b py-3">
                            <div class="flex justify-between">
                                <span class="font-medium">{{ $lead->first_name }} {{ $lead->last_name }}</span>
                                <span class="text-xs uppercase bg-slate-100 px-2 py-1 rounded">{{ $lead->status }}</span>
                            </div>
                            <p class="text-sm text-slate-600 mt-1">{{ $lead->notes ?? 'No notes recorded' }}</p>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm">No lead history found.</p>
                    @endforelse
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Opportunities</h2>
                    @forelse($customer->opportunities as $opportunity)
                        <div class="border-b py-3 flex justify-between items-center">
                            <div>
                                <div class="font-medium">{{ $opportunity->title }}</div>
                                <div class="text-sm text-slate-500">{{ $opportunity->stage }} · {{ $opportunity->currency }} {{ $opportunity->value }}</div>
                            </div>
                            <span class="text-xs uppercase bg-sky-100 text-sky-800 px-2 py-1 rounded">{{ $opportunity->probability }}%</span>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm">No opportunities recorded.</p>
                    @endforelse
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Quotations</h2>
                    @forelse($customer->quotations as $quotation)
                        <div class="border-b py-3">
                            <div class="flex justify-between">
                                <span class="font-medium">Quote #{{ $quotation->id }}</span>
                                <span class="text-xs uppercase bg-amber-100 text-amber-800 px-2 py-1 rounded">{{ $quotation->status }}</span>
                            </div>
                            <div class="text-sm mt-1 text-slate-600">Total: {{ $quotation->total_amount }}</div>
                            @foreach($quotation->lines as $line)
                                <div class="text-xs mt-1 text-slate-500">- {{ $line->product_name }} x {{ $line->quantity }} @ {{ $line->unit_price }}</div>
                            @endforeach
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm">No quotations available.</p>
                    @endforelse
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">ERP orders</h2>
                    @forelse($customer->erpOrders as $order)
                        <div class="border-b py-3">
                            <div class="font-medium">{{ $order->reference }}</div>
                            <div class="text-sm text-slate-600">Status: {{ $order->status }} · Amount: {{ $order->amount }}</div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm">No ERP orders linked yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</body>
</html>
