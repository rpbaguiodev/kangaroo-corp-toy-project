@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('customers.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to customers</a>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Customer</h1>

            <form method="POST" action="{{ route('customers.update', $customer) }}">
                @csrf
                @method('PUT')
                @include('customers._form')

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        Update Customer
                    </button>
                    <a href="{{ route('customers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
