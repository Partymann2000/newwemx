@props([
    'id', // unique ID for the chart
    'type' => 'line', // graph type (line, bar, area, etc.)
    'height' => 300, // the height of the graph
    'series' => [], // data for graphics
    'categories' => [], // categories for the X axis
    'colors' => ['#206bc4'], // graphics colors
])

<div id="{{ $id }}" style="height: {{ $height }}px;"></div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        if (window.ApexCharts) {
            new ApexCharts(document.getElementById('{{ $id }}'), {
                chart: {
                    type: "{{ $type }}",
                    fontFamily: 'inherit',
                    height: {{ $height }},
                    sparkline: { enabled: true },
                    animations: { enabled: false },
                },
                dataLabels: { enabled: false },
                fill: {
                    opacity: .16,
                    type: 'solid'
                },
                stroke: {
                    width: 2,
                    lineCap: "round",
                    curve: "smooth",
                },
                series: {!! json_encode($series) !!},
                tooltip: { theme: 'dark' },
                grid: { strokeDashArray: 4 },
                xaxis: {
                    labels: { padding: 0 },
                    tooltip: { enabled: false },
                    axisBorder: { show: false },
                    type: 'datetime',
                    categories: {!! json_encode($categories) !!},
                },
                yaxis: { labels: { padding: 4 } },
                colors: {!! json_encode($colors) !!},
                legend: { show: false },
            }).render();
        }
    });
</script>
