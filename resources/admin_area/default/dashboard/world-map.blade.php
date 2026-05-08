@props(['countryUsers' => []])

<div class="col-lg-8">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">
                Locations
            </h3>
            <div class="ratio ratio-21x9">
                <div>
                    <div id="map-world" class="w-100 h-100"></div>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
    <script>
        document.addEventListener("livewire:navigated", function () {
            const map = new jsVectorMap({
                selector: '#map-world',
                map: 'world',
                backgroundColor: 'transparent',
                regionStyle: {
                    initial: {
                        fill: tabler.getColor('body-bg'),
                        stroke: tabler.getColor('border-color'),
                        strokeWidth: 2,
                    }
                },
                zoomOnScroll: true,
                zoomButtons: false,

                // -------- Series --------
                visualizeData: {
                    scale: [tabler.getColor('bg-surface'), tabler.getColor('primary')],
                    values: {
                        @foreach($countryUsers as $country => $count)
                            "{{ $country }}": {{ $count }},
                        @endforeach
                    },
                },
            });
            window.addEventListener("resize", () => {
                map.updateSize();
            });
        });
    </script>
@endpush
