@extends('layout.master')

@section('content')
    <nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Loan Installment History</li>
    </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title">Loan Installment Log</h6>
                        <a href="{{ route('loan-installments.create') }}" class="btn btn-primary btn-sm">
                            <i data-lucide="plus" class="icon-sm me-2"></i> Make New Collection
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Receipt #</th>
                                <th>Member Name</th>
                                <th>Loan Account No</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Grace Amount</th>
                                <th>Payment Date</th>
                                <th>Collected By</th>
                                @role('Admin')
                                <th style="width: 120px;">Actions</th>
                                @endrole
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($installments as $installment)
                                <tr>
                                    <td>{{ $installment->id }}</td>
                                    <td>{{ $installment->member->name }}</td>
                                    <td>{{ $installment->loanAccount->account_no }}</td>
                                    <td class="text-end">{{ number_format($installment->paid_amount, 2) }}</td>
                                    <td class="text-end">{{ number_format($installment->grace_amount, 2) }}</td>
                                    <td>{{ $installment->payment_date->format('d M, Y') }}</td>
                                    <td>{{ $installment->collector->name }}</td>
                                    @role('Admin')
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('loan-installments.edit', ['loan_installment' => $installment->id]) }}" class="btn btn-primary btn-xs me-1">Edit</a>
                                            <form id="delete-installment-{{ $installment->id }}" action="{{ route('loan-installments.destroy', ['loan_installment' => $installment->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-xs" onclick="showDeleteConfirm('delete-installment-{{ $installment->id }}')">
                                                    {{__('messages.delete')}}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @endrole
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->hasRole('Admin') ? '7' : '6' }}" class="text-center">No loan installments found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        <div class="mt-4">
                            {{ $installments->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
