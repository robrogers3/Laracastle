@extends('robrogers3::layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4> Review Device</h4>
                        <h6>Did you sign in on this device?</h6>
                    </div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <div>
                            <dl>
                                <dt>Location</dt><dd> {{ $device->location() }}</dd>
                                <dt>Browser</dt><dd> {{ $device->description() }}</dd>
                                <dt>IP Address</dt><dd>{{ $device->ip() }}</dd>
                            </dl>

                            <div style="display:flex;">
                                <form style="margin-right: .5rem;" action="{{route('laracastle.report-device')}}" method="POST">
                                    @csrf
                                    <input type="hidden" name="token" value="{{$device->token()}}"/>
                                    <button class="btn btn-danger">No, it wasn't me</button>
                                </form>
                                <form action="{{route('laracastle.approve-device')}}" method="POST">
                                    @csrf
                                    {{method_field('DELETE')}}
                                    <input type="hidden" name="token" value="{{$device->token()}}"/>
                                <button class="btn btn-primary">Yes, this was me</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
@endsection
