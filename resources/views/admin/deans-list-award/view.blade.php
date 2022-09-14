@extends('layouts.admin')

@section('title', $courses->course)

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div class="h3 mb-0 text-gray-800">{{ $courses->course }} - Dean's List Applicants</div>
</div>

@if(session('message'))
<div class="alert alert-success">{{ session('message') }}</div>
@endif
<div class="row">
    <div class="col-md-12 mb-2">
        <form>
            <div class="form-row mb-2">
                <div class="col-lg-3 col-xs-12">
                    <select id="year" class="custom-select" id="inlineFormCustomSelectPref">
                        <option value="All" selected>All Year Levels</option>
                        <option value="2nd-Year">2nd Year</option>
                        <option value="3rd-Year">3rd Year</option>
                        <option value="4th-Year">4th Year</option>
                      </select>
                </div>
                <div class="col-auto col-xs-12">
                    <button type="button" class="btn btn-secondary view-accepted" formtarget="_blank">
                        <i class="fa fa-download fa-sm text-white-100"></i>&ensp;Approved Students
                    </button>
                </div>
                <div class="col-auto col-xs-12">
                    <button type="button" class="btn btn-danger view-rejected" formtarget="_blank">
                        <i class="fa fa-download fa-sm text-white-100"></i>&ensp;Rejected Students
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <div class="m-0 font-weight-bold text-primary">Students
                    <a href="{{ url('admin/deans-list-award') }}" class="btn btn-primary btn-sm float-right">Back</a>
                </div>
            </div>
            <div class="card-body">
                <input type="hidden" value="{{ $courses->course_code }}" id="course_id">
                <div class="form-group">
                    <select id='status' class="custom-select col-lg-2 col-sm-12">
                        <option value="">All</option>
                        <option value="1">Approved</option>
                        <option value="0">Pending</option>
                        <option value="2">Rejected</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-data-dl" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Student No.</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Course</th>
                                <th>Year Level</th>
                                <th width="">1st Sem GWA</th>
                                <th>2nd Sem GWA</th>
                                <th class="text-center">Image</th>
                                <th class="text-center">Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <a href="{{ url('admin/deans-list-award/'.$courses->course_code. '/view-approved-students-pdf')}}" target="__blank"
                        class="btn btn-sm btn-primary mt-2 mb-3">
                        <i class="fa fa-download fa-sm text-white-100"></i>&ensp;Print Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
