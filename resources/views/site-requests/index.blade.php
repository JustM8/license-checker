@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Логи запитів до API</h1>

        <form method="GET" class="mb-3 d-flex gap-2">
            <input type="text" name="domain" class="form-control" placeholder="Домен" value="{{ request('domain') }}">
            <select name="success" class="form-select">
                <option value="">Усі</option>
                <option value="1" {{ request('success') === '1' ? 'selected' : '' }}>Успішні</option>
                <option value="0" {{ request('success') === '0' ? 'selected' : '' }}>Помилки</option>
            </select>
            <button class="btn btn-primary">Фільтрувати</button>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>Дата</th>
                    <th>Домен</th>
                    <th>IP</th>
                    <th>User Agent</th>
                    <th>Результат</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($requests as $req)
                    <tr>
                        <td>{{ $req->created_at->format('d.m.Y H:i') }}</td>
                        <td>{{ $req->domain }}</td>
                        <td>{{ $req->ip }}</td>
                        <td>{{ $req->user_agent }}</td>
                        <td>{!! $req->status_label !!}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Немає записів</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $requests->withQueryString()->links() }}
        </div>
    </div>
@endsection
