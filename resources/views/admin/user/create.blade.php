@extends('layouts.admin')

@section('title', 'Create User')

@section('content')

    <div class="row">
        <div class="col-xl-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <div class="m-0 font-weight-bold text-primary">Add User
                        <a href="{{ url('admin/users') }}" class="btn btn-primary btn-sm float-right">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    @include('layouts.partials.messages')

                    <form method="POST" action="{{ url('admin/users') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="small mb-1">Username</label>
                            <input class="form-control" name="username" type="text" placeholder="Enter your username"
                                value="{{ old('username') }}" required autofocus>
                            @if ($errors->has('username'))
                                <span class="text-danger text-left">{{ $errors->first('username') }}</span>
                            @endif
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">First name</label>
                                <input class="form-control" name="first_name" type="text"
                                    placeholder="Enter your first name" value="{{ old('first_name') }}" required autofocus>
                                @if ($errors->has('first_name'))
                                    <span class="text-danger text-left">{{ $errors->first('first_name') }}</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Middle name</label>
                                <input class="form-control" name="middle_name" type="text"
                                    placeholder="Enter your middle name" value="{{ old('middle_name') }}" />
                                @if ($errors->has('middle_name'))
                                    <span class="text-danger text-left">{{ $errors->first('middle_name') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Last Name</label>
                                <input class="form-control" name="last_name" type="text"
                                    placeholder="Enter your last name" value="{{ old('last_name') }}" required autofocus>
                                @if ($errors->has('last_name'))
                                    <span class="text-danger text-left">{{ $errors->first('last_name') }}</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Phone number</label>
                                <input class="form-control js-phone" name="contact" type="tel"
                                    placeholder="Enter your phone number" value="{{ substr(old('contact'), 3) }}" required
                                    autofocus>
                                @if ($errors->has('contact'))
                                    <span class="text-danger text-left">{{ $errors->first('contact') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small mb-1">Email address</label>
                            <input class="form-control" name="email" type="email" placeholder="Enter your email address"
                                value="{{ old('email') }}" required autofocus>
                            @if ($errors->has('email'))
                                <span class="text-danger text-left">{{ $errors->first('email') }}</span>
                            @endif
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Password</label>
                                <input class="form-control" name="password" type="password" placeholder="Enter new password"
                                    value="{{ old('password') }}" required>
                                @if ($errors->has('password'))
                                    <span class="text-danger text-left">{{ $errors->first('password') }}</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Confirm Password</label>
                                <input class="form-control" name="password_confirmation" type="password"
                                    placeholder="Confirm new password" value="{{ old('password_confirmation') }}" required>
                                @if ($errors->has('password_confirmation'))
                                    <span
                                        class="text-danger text-left">{{ $errors->first('password_confirmation') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Role</label>
                                {!! Form::select('roles[]', $roles, [], ['class' => 'custom-select']) !!}
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit">Save</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection
