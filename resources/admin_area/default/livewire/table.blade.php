<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

new class extends Component
{
    public string $title = '';
    public string $description = '';

    #[Locked]
    public array $columns = [];

    #[Locked]
    public array $rows = [];

    public bool $searchable = true;

    #[Url]
    public string $search = '';

    #[Url]
    public string $entries = '8'; // Show X entries per page

    #[Url]
    public int $page = 1; // Track current page

    public array $sortableColumns = [];

    #[Url]
    public string $sortColumn = '';

    #[Url]
    public string $sortDirection = 'asc';

    public array $hiddenColumns = [];

    public string $class = 'card';

    #[Computed]
    public function filteredRows()
    {
        $rows = $this->rows;

        // Apply search filter
        if (!empty($this->search) && $this->searchable) {
            $rows = array_values(array_filter($rows, function ($row) {
                foreach ($row as $cell) {
                    if (stripos(strip_tags($cell), $this->search) !== false) {
                        return true;
                    }
                }
                return false;
            }));
        }

        // Apply sorting
        if (in_array($this->sortColumn, $this->sortableColumns)) {
            $columnIndex = array_search($this->sortColumn, $this->columns);

            if ($columnIndex !== false) {
                usort($rows, function ($a, $b) use ($columnIndex) {
                    $valueA = strip_tags($a[$columnIndex] ?? '');
                    $valueB = strip_tags($b[$columnIndex] ?? '');

                    return $this->sortDirection === 'asc'
                        ? strnatcasecmp($valueA, $valueB)
                        : strnatcasecmp($valueB, $valueA);
                });
            }
        }

        return $rows;
    }

    #[Computed]
    public function paginatedRows()
    {
        $perPage = (int) $this->entries ?: 8;
        $page = max(1, $this->page);
        $rows = $this->filteredRows;

        $offset = ($page - 1) * $perPage;
        return array_slice($rows, $offset, $perPage);
    }

    #[Computed]
    public function totalPages()
    {
        return (int) ceil(count($this->filteredRows) / ((int) $this->entries ?: 8));
    }

    #[Computed]
    public function paginationRange(): array
    {
        $total = $this->totalPages;
        $current = $this->page;
        $delta = 2; // how many pages to show before/after current
        $range = [];
        $rangeWithDots = [];

        for ($i = 1; $i <= $total; $i++) {
            if (
                $i == 1 ||
                $i == $total ||
                ($i >= $current - $delta && $i <= $current + $delta)
            ) {
                $range[] = $i;
            }
        }

        $prev = null;
        foreach ($range as $i) {
            if ($prev !== null && $i - $prev > 1) {
                $rangeWithDots[] = '...';
            }
            $rangeWithDots[] = $i;
            $prev = $i;
        }

        return $rangeWithDots;
    }

    public function goToPage($page)
    {
        $this->page = max(1, min($page, $this->totalPages));
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function hideColumn(string $column): void
    {
        // if column is already in hiddenColumns, remove it
        if (in_array($column, $this->hiddenColumns)) {
            $this->hiddenColumns = array_diff($this->hiddenColumns, [$column]);
        } else {
            // otherwise, push it to hiddenColumns
            $this->hiddenColumns[] = $column;
        }
    }
}
?>

<div>
    <div class="{{ $class ? 'card' : '' }}">
        <div class="card-header">
            <h3 class="card-title">{{ $title ?? '' }}</h3>
{{--            <div class="card-actions">--}}
{{--                <div>--}}
{{--                    @foreach($columns as $column)--}}
{{--                        @if(empty($column))--}}
{{--                            @continue--}}
{{--                        @endif--}}
{{--                        <div class="form-selectgroup">--}}
{{--                            <label class="form-selectgroup-item">--}}
{{--                                <input type="checkbox" class="form-selectgroup-input" wire:click="hideColumn('{{ $column }}')" @if(!in_array($column, $hiddenColumns)) checked @endif />--}}
{{--                                <span class="form-selectgroup-label" style="font-size: 10px;">{{ $column }}</span>--}}
{{--                            </label>--}}
{{--                        </div>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>
        <div class="card-body border-bottom py-3">
            <div class="d-flex">
                <div class="text-secondary">
                    Show
                    <div class="mx-2 d-inline-block">
                        <input type="text" class="form-control form-control-sm" wire:model.change="entries" size="3" aria-label="Entries count">
                    </div>
                    entries
                </div>
                <div class="ms-auto text-secondary">
                    Search:
                    <div class="ms-2 d-inline-block">
                        <input type="text" wire:model.change="search" class="form-control form-control-sm" aria-label="Search">
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-selectable card-table table-vcenter text-nowrap datatable">
                <thead>
                <tr>
                    @foreach($columns as $column)
                        <th>
                            @if(in_array($column, $sortableColumns))
                                <a href="#" wire:click.prevent="sortBy('{{ $column }}')" class="d-flex align-items-center text-decoration-none text-reset gap-1">
                                    <span>{{ $column }}</span>

                                    @if($sortColumn === $column)
                                        {{-- Show active sort direction --}}
                                        @if($sortDirection === 'asc')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                 class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-up">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M6 15l6 -6l6 6" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                 class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-down">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M6 9l6 6l6 -6" />
                                            </svg>
                                        @endif
                                    @else
                                        {{-- Show inactive icon (default to up for clarity) --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                             class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-up" style="opacity: 0.4;">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M6 15l6 -6l6 6" />
                                        </svg>
                                    @endif
                                </a>
                            @else
                                {{ $column }}
                            @endif
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                    @if(empty($this->paginatedRows))
                        <tr>
                            <td colspan="{{ count($columns) }}" class="text-center text-secondary">
                                No records found.
                            </td>
                        </tr>
                    @endif

                    @foreach($this->paginatedRows as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td>
                                    {!! $cell !!}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="row g-2 justify-content-center justify-content-sm-between">
                <div class="col-auto d-flex align-items-center">
                    @php
                        $from = count($this->filteredRows) ? (($page - 1) * (int)$entries) + 1 : 0;
                        $to = min($from + (int)$entries - 1, count($this->filteredRows));
                    @endphp
                    <p class="m-0 text-secondary">
                        Showing <strong>{{ $from }} to {{ $to }}</strong> of <strong>{{ count($this->filteredRows) }} entries</strong>
                    </p>
                </div>
                <div class="col-auto">
                    <ul class="pagination m-0 ms-auto">
                        <li class="page-item {{ $page == 1 ? 'disabled' : '' }}">
                            <button class="page-link" wire:click="goToPage({{ $page - 1 }})" tabindex="-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6l6 6"/></svg>
                            </button>
                        </li>

                        @foreach ($this->paginationRange as $pageNum)
                            @if ($pageNum === '...')
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            @else
                                <li class="page-item {{ $page == $pageNum ? 'active' : '' }}">
                                    <button class="page-link" wire:click="goToPage({{ $pageNum }})">{{ $pageNum }}</button>
                                </li>
                            @endif
                        @endforeach

                        <li class="page-item {{ $page == $this->totalPages ? 'disabled' : '' }}">
                            <button class="page-link" wire:click="goToPage({{ $page + 1 }})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6l-6 6"/></svg>
                            </button>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</div>




