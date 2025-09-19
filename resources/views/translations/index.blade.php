@extends('layout.master')

@section('content')
    <h1 class="mb-4">{{ __('manage_translations') }}</h1>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
       <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ __('add_new_translation_key') }}</h5>
            <form action="{{ route('admin.translations.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="key" class="form-label">{{ __('key') }} <small>(e.g., new_feature_title)</small></label>
                        <input type="text" name="key" id="key" class="form-control" value="{{ old('key') }}" required pattern="[a-z0-9_]+">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="en" class="form-label">{{ __('english') }}</label>
                        <input type="text" name="en" id="en" class="form-control" value="{{ old('en') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="bn" class="form-label">{{ __('bengali') }}</label>
                        <input type="text" name="bn" id="bn" class="form-control" value="{{ old('bn') }}" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">{{ __('add_key') }}</button>
            </form>
        </div>
    </div>
    <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="{{ __('search_by_key') }}..." autocomplete="off">
    </div>
    <form id="translationForm" action="{{ route('admin.translations.update') }}" method="POST">
        @csrf
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                <tr>
                    <th scope="col">{{ __('key') }}</th>
                    <th scope="col">{{ __('english') }}</th>
                    <th scope="col">{{ __('bengali') }}</th>
                </tr>
                </thead>
                <tbody id="translationTable">
                @foreach ($translations as $key => $translation)
                    <tr data-key="{{ $key }}">
                        <td>{{ $key }}</td>
                        <td class="editable text-wrap" data-key="{{ $key }}" data-lang="en">
                            <span class="display-text">{{ $translation['en'] }}</span>
                            <input type="text" name="translations[{{ $key }}][en]" value="{{ $translation['en'] }}" class="edit-input form-control d-none">
                        </td>
                        <td class="editable text-wrap" data-key="{{ $key }}" data-lang="bn">
                            <span class="display-text">{{ $translation['bn'] }}</span>
                            <input type="text" name="translations[{{ $key }}][bn]" value="{{ $translation['bn'] }}" class="edit-input form-control d-none">
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-primary mt-3">{{ __('Save_All_Changes') }}</button>
    </form>
@endsection

@push('custom-scripts')
    <script>
        // Inline editing
        document.querySelectorAll('.editable').forEach(cell => {
            cell.addEventListener('click', function(e) {
                if (e.target.classList.contains('display-text')) {
                    const displayText = this.querySelector('.display-text');
                    const input = this.querySelector('.edit-input');

                    displayText.classList.add('d-none');
                    input.classList.remove('d-none');
                    input.focus();
                    this.classList.add('bg-light');
                }
            });
        });

        document.querySelectorAll('.edit-input').forEach(input => {
            input.addEventListener('blur', function() {
                const cell = this.parentElement;
                const displayText = cell.querySelector('.display-text');
                displayText.textContent = this.value;
                displayText.classList.remove('d-none');
                this.classList.add('d-none');
                cell.classList.remove('bg-light');
            });
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#translationTable tr');

            rows.forEach(row => {
                const key = row.getAttribute('data-key').toLowerCase();
                if (key.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
@endpush
