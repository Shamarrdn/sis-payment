@extends('layouts.app')
@section('title', 'QR الطالب')
@section('page-heading', 'QR ملف الطالب')
@section('user-name', auth()->user()->name)

@section('content')
@php $profileUrl = route('affairs.student.card', $student); @endphp
<div class="text-center card border-0 shadow-sm p-5 mx-auto" style="max-width:400px;">
    <div id="qrcode" class="d-flex justify-content-center mb-3"></div>
    <p class="fw-bold">{{ $student->name }}</p>
    <p class="small text-muted">{{ $profileUrl }}</p>
    <a href="{{ route('affairs.student.card', $student) }}" class="btn btn-outline-primary btn-sm">فتح الكارت</a>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>new QRCode(document.getElementById('qrcode'),{text:@json($profileUrl),width:200,height:200});</script>
@endsection
