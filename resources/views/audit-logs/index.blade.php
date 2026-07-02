@if (Auth::user()->isSuperAdmin())
@extends('layouts.admin')

@section('title', 'Auditoria')
@section('eyebrow', 'Configuracion')
@section('page-title', 'Auditoria')
@section('page-description', 'Registro de acciones en la plataforma.')
@section('page-actions')
    <a href="{{ route('audit-logs.export', request()->query()) }}" class="admin-outline-link">Exportar CSV</a>
@endsection

@section('content')
    <div>
        @include('audit-logs.partials.table')
    </div>
@endsection
@else
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-900">Auditoria</h2>
    </x-slot>

    <div class="py-4 lg:py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('audit-logs.partials.table')
        </div>
    </div>
</x-app-layout>
@endif
