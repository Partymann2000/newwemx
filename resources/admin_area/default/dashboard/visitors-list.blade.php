@props(['countryUsers'])

<div class="col-lg-4">
    <div class="card" style="height: 35rem">
        <div class="card-body card-body-scrollable card-body-scrollable-shadow">
            <div class="divide-y">
                @foreach($countryUsers as $country => $count)
                    <div class="d-flex">
                        <div class="me-2">
                            <span class="flag flag-xs flag-country-{{ strtolower($country) }}"></span>
                        </div>
                        <div class="flex-fill">
                            {{ \App\Facades\World::countryName($country) }}
                        </div>
                        <div>
                            {{ $count }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

