@extends('layout.mails.main-light')

@section('title')
  {{ $data['subject'] }}
@endsection

@section('header')
  Hai {{ explode('@', $data['email'])[0] }},
@endsection

@section('content')
  {!! $data['body'] !!}
@endsection
