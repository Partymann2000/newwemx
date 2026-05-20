<div>
    <div class="row g-3 mb-4">
        @foreach (range(1, 3) as $i)
            <div class="col-md-4">
                <div class="card placeholder-glow">
                    <div class="card-body">
                        <div class="placeholder col-6 mb-2"></div>
                        <div class="placeholder col-8"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card placeholder-glow">
        <div class="card-header">
            <div class="placeholder col-4"></div>
        </div>
        <div class="card-body">
            @foreach (range(1, 4) as $i)
                <div class="mb-4">
                    <div class="placeholder col-5 mb-2"></div>
                    <div class="placeholder col-12"></div>
                    <div class="placeholder col-10"></div>
                </div>
            @endforeach
        </div>
    </div>
</div>
