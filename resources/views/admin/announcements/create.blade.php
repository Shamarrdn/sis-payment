@extends('layouts.app')
@section('title', 'إعلان جديد')
@section('page-heading', 'إعلان جديد')
@section('user-name', auth()->user()->name)

@section('content')
@include('admin.announcements._form', ['announcement' => null])
@endsection
