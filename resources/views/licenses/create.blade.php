@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-3">Додати новий сайт</h2>

        <form action="{{ route('licenses.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="domain" class="form-label">Домен сайту</label>
                <input type="text" name="domain" class="form-control" placeholder="site.com" required>
            </div>

            <button type="submit" class="btn btn-success">Створити</button>
            <a href="{{ route('licenses.index') }}" class="btn btn-secondary">Скасувати</a>
        </form>
    </div>
@endsection
