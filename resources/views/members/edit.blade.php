@extends('layout.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Edit Member: {{ $member->name }}</h5>
            <form action="{{ route('members.update', $member->id) }}" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @include('members._form', ['buttonText' => 'Update Member'])
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
