<div class="card dashboard-card">
    <div class="card-header">
        <span>{{ $title }}</span>
        <i class="fas {{ $icon }}"></i>
    </div>
    <div class="card-value">{{ number_format($value) }}</div>
    <div class="card-footer">{{ $footer }}</div>
</div>
