@extends('robrogers3::layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        Reveiew Device
                    </div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <div>Location {{$device->location()}}</div>
                        <div>You are logged in!</div>
                        <div><small>Your account is protected by castle.io</small><div>
                    </div>
                </div>
            </div>
@endsection
