@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav class="d-flex justify-content-center mt-4">
            <div class="d-flex flex-column align-items-center">
                <ul class="pagination mb-2 shadow-sm rounded-pill overflow-hidden custom-pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link border-0">&laquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <button type="button" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" class="page-link border-0" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled">&laquo;</button>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link border-0">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}" aria-current="page"><span class="page-link border-0 bg-coral text-white">{{ $page }}</span></li>
                                @else
                                    <li class="page-item" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}"><button type="button" class="page-link border-0 text-naval fw-medium" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}">{{ $page }}</button></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <button type="button" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" class="page-link border-0" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled">&raquo;</button>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link border-0">&raquo;</span>
                        </li>
                    @endif
                </ul>
                <p class="small text-muted mb-0">
                    Mostrando <span class="fw-bold text-naval">{{ $paginator->firstItem() }}</span> a <span class="fw-bold text-naval">{{ $paginator->lastItem() }}</span> de <span class="fw-bold text-naval">{{ $paginator->total() }}</span> resultados
                </p>
            </div>
        </nav>
        
        <style>
            .custom-pagination .page-link {
                color: #1B263B;
                padding: 0.5rem 1rem;
                transition: all 0.2s ease;
            }
            .custom-pagination .page-link:hover {
                background-color: rgba(255, 107, 107, 0.1);
                color: #FF6B6B;
            }
            .custom-pagination .page-item.active .page-link {
                background-color: #FF6B6B !important;
                border-color: #FF6B6B !important;
                color: white !important;
            }
            .custom-pagination .page-item.disabled .page-link {
                color: #6c757d;
                background-color: #f8f9fa;
            }
            .bg-coral { background-color: #FF6B6B !important; }
            .text-naval { color: #1B263B !important; }
        </style>
    @endif
</div>
