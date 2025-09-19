@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Add New Member</h5>
            <form action="{{ route('members.store') }}" method="POST" enctype="multipart/form-data">
                @include('members._form', ['buttonText' => 'Create Member'])
            </form>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script>
        $(".flatpickr").flatpickr({
            altInput: true,
            dateFormat: 'Y-m-d',
            altFormat: 'd/m/Y'
        })
    </script>
@endpush
