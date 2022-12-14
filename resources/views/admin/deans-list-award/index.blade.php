@extends('layouts.admin')

@section('title', 'Dean\'s List')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div class="h3 mb-0 text-gray-800">Programs - Dean's List</div>
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        <a href="{{ url('admin/deans-list-award/overall') }}" class="btn btn-secondary position-relative mr-2"><i
                class="fa-solid fa-user-group fa-sm"></i> Applicants
            @if ($pending >= 1)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $pending }}
                </span>
            @endif
        </a>
    </div>

    <div class="row">
        @foreach ($courses as $item)
            <div class="col-sm-3 mb-4">
                <a class="card lift h-100" href="{{ url('admin/deans-list-award/' . $item->course_code) }}">
                    <div class="card border-left-info shadow">
                        <img src="{{ asset('admin/img/bgimage-login.jpg') }}" class="card-img-top" alt="image">
                        <div class="card-body text-center">
                            <div class="text-md card-text font-weight-bold text-info text-uppercase pl-6 mt-4r mt-3">
                                {{ $item->course_code }}
                            </div>
                            <div class="col-12 mb-3">
                                <i class="fas fa-solid fa-award fa-2x text-info "></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>


@endsection
