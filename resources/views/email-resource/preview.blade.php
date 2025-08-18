<div style="padding-top: 30px;"></div>

@extends('layout.mails.main-light')

@section('title')
  {{ $data['subject'] }}
@endsection

@php
  $name = explode('@', $data['recipient'])[0];
  if (strlen($name) > 8) {
    $name = substr($name, 0, 8) . '..';
  }
@endphp

@section('header')
  Hai {{ textCapitalize($name) }},
@endsection

@section('content')
  {!! $data['message'] !!}
@endsection

<div style="padding-bottom: 30px;"></div>