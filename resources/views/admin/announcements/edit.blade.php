@extends('layouts.app')
@section('title', 'تعديل إعلان')
@section('page-heading', 'تعديل إعلان')
@section('user-name', auth()->user()->name)

@section('content')
@include('admin.announcements._form', ['announcement' => $announcement])
@endsection
