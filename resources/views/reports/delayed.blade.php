@extends('layouts.app')
@section('title', 'طلبات متأخرة')
@section('page-heading', 'تقرير الطلبات المتأخرة')
@section('user-name', auth()->user()->name)
@section('content')
@include('reports._open-requests-table', ['payments' => $payments, 'title' => 'طلبات تجاوزت وقت التنفيذ المتوقع', 'showDelayed' => true])
@endsection
