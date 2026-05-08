@props([
    'title',
    'value',
    'percentage',
    'percentageIcon',
    'percentageColor',
    'progress',
    'timeRange',
    'description',
])

<div class="col-sm-6 col-lg-3">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="subheader">{{ $title }}</div>
                <div class="ms-auto lh-1">
                    <div class="dropdown">
                        <a class="dropdown-toggle text-secondary" href="#" data-bs-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">{{ $timeRange }}</a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item active" href="#">Last 7 Days</a>
                            <a class="dropdown-item" href="#">Last 30 Days</a>
                            <a class="dropdown-item" href="#">Last 3 Months</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="h1 mb-3">{{ $value }}</div>
            <div class="d-flex mb-2">
                <div>{{ $description }}</div>
                <div class="ms-auto">
                    <span class="text-{{ $percentageColor }} d-inline-flex align-items-center lh-1">
                        {{ $percentage }}%
                        {!! $percentageIcon !!}
                    </span>
                </div>
            </div>
            <div class="progress progress-sm">
                <div class="progress-bar bg-primary" style="width: {{ $progress }}%" role="progressbar"
                     aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"
                     aria-label="{{ $progress }}% Complete">
                    <span class="visually-hidden">{{ $progress }}% Complete</span>
                </div>
            </div>
        </div>
    </div>
</div>
