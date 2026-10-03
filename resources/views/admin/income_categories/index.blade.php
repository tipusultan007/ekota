@extends('layout.master')

@section('content')
    <div class="row">

        {{-- Create Income Category Form --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">{{ __('messages.create_income_category') }}</h6>
                    <form action="{{ route('admin.income-categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('messages.category_name') }}</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('messages.create') }}</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Income Categories List --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">{{ __('messages.income_categories') }}</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('messages.name') }}</th>
                                <th class="text-end">{{ __('messages.total_income') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($categories as $key => $category)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($category->incomes_sum_amount ?? 0, 2) }}
                                    </td>
                                    <td>
                                            <span class="badge bg-{{ $category->is_active ? 'success' : 'danger' }}">
                                                {{ $category->is_active ? __('messages.active') : __('messages.inactive') }}
                                            </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.income-categories.edit', $category->id) }}" class="btn btn-primary btn-xs">{{ __('messages.edit') }}</a>
                                        <form action="{{ route('admin.income-categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('messages.confirm_delete') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs">{{ __('messages.delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if($categories->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center">{{ __('messages.no_categories_found') }}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $categories->links() }}</div>
                </div>
            </div>
        </div>

    </div>
@endsection
