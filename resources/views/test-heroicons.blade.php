@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Test Heroicons</h1>
    
    <div class="p-4">
        <p>Testing heroicon-o-search:</p>
        <x-heroicon-o-search class="w-6 h-6 text-blue-500" />
        
        <p>Testing heroicon-s-plus:</p>
        <x-heroicon-s-plus class="w-6 h-6 text-green-500" />
        
        <p>Testing heroicon-o-menu:</p>
        <x-heroicon-o-menu class="w-6 h-6 text-red-500" />
    </div>
</div>
@endsection
