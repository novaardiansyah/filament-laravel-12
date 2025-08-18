<div style="padding-top: 30px;"></div>

@extends('layout.mails.main-light')

@section('title')
  {{ $data['subject'] }}
@endsection

@section('header')
  Hai {{ $data['recipient'] }},
@endsection

@section('content')
  {!! $data['message'] !!}
@endsection

<div style="padding-bottom: 30px;"></div>