<div class="space-y-4">
    <div class="grid gap-3 md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 p-3">
            <div class="text-xs font-semibold text-gray-500">Dana diterima</div>
            <div class="text-sm font-bold text-gray-950">Rp {{ number_format($review['summary']['funds_received'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 p-3">
            <div class="text-xs font-semibold text-gray-500">Pengeluaran valid</div>
            <div class="text-sm font-bold text-gray-950">Rp {{ number_format($review['summary']['valid_expense'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 p-3">
            <div class="text-xs font-semibold text-gray-500">Sisa dana</div>
            <div class="text-sm font-bold text-gray-950">Rp {{ number_format($review['summary']['remaining_fund'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 p-3">
            <div class="text-xs font-semibold text-gray-500">Saldo user</div>
            <div class="text-sm font-bold text-gray-950">Rp {{ number_format($review['summary']['user_balance_total'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="space-y-2">
        @foreach ($review['items'] as $item)
            <div class="rounded-lg border {{ $item['passed'] ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-bold text-gray-950">{{ $item['label'] }}</div>
                        <div class="mt-1 text-xs font-medium text-gray-600">{{ $item['note'] }}</div>
                    </div>
                    <div class="shrink-0 rounded-full px-2 py-1 text-xs font-bold {{ $item['passed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $item['passed'] ? 'PASS' : 'BELUM' }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="rounded-lg border {{ $review['can_finalize'] ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-800' }} p-3 text-sm font-bold">
        {{ $review['can_finalize'] ? 'Event siap difinalisasi dan dikunci sebagai selesai.' : 'Event belum siap finalisasi. Selesaikan item BELUM terlebih dahulu.' }}
    </div>
</div>
