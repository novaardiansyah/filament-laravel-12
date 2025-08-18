@extends('layout.mails.main-light')

@section('title')
  {{ $data['subject'] }}
@endsection

@section('header')
  Hai {{ textCapitalize($data['recipient']) }},
@endsection

@section('content')
  {!! $data['body'] !!}
@endsection
