@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-3">Список сайтів</h2>

        <a href="{{ route('licenses.create') }}" class="btn btn-primary mb-3">Додати сайт</a>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Домен</th>
                <th>Секрет</th>
                <th>Хеш</th>
                <th>Створено</th>
                <th>Діє до</th>
                <th>Статус</th>
                <th>Лого</th>
            </tr>
            </thead>
            <tbody>
            @foreach($licenses as $license)
                <tr>
                    <td>
                        <input type="text"
                               name="domain"
                               class="form-control form-control-sm"
                               value="{{ $license->domain }}"
                               data-id="{{ $license->id }}">
                    </td>
                    <td>
                        <input type="text"
                               name="secret"
                               class="form-control form-control-sm"
                               value="{{ $license->secret }}"
                               data-id="{{ $license->id }}">
                    </td>
                    <td><code title="{{ $license->hash }}" style="cursor: pointer;"
                              onclick="navigator.clipboard.writeText('{{ $license->hash }}')">
                            {{ \Str::limit($license->hash, 40) }}
                        </code></td>
                    <td>{{ $license->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <input type="date"
                               name="expired_at"
                               class="form-control form-control-sm"
                               value="{{ $license->expired_at ? $license->expired_at->format('Y-m-d') : '' }}"
                               data-id="{{ $license->id }}">
                    </td>
                    <td>
                        <select name="status"
                                class="form-select form-select-sm"
                                data-id="{{ $license->id }}">
                            <option value="1" {{ $license->status ? 'selected' : '' }}>Активна</option>
                            <option value="0" {{ !$license->status ? 'selected' : '' }}>Неактивна</option>
                        </select>
                    </td>
                    <td>
                        @if($license->branding_removed)
                            <span class="badge bg-danger">Бренд видалено</span>
                        @else
                            <span class="badge bg-success">Ок</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn btn-success btn-sm save-license" data-id="{{ $license->id }}">
                            💾 Зберегти
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal"
                                data-bs-target="#generateJsModal"
                                data-id="{{ $license->id }}"
                                data-secret="{{ $license->secret }}">
                            ⚙️ JS
                        </button>
                    </td>
                <td>
                    @if($license->status == 0)
                        <form method="POST" action="{{ route('licenses.reactivate', $license) }}">
                            @csrf
                            <button class="btn btn-warning btn-sm">Активувати</button>
                        </form>
                    @endif
                </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $licenses->links() }}
    </div>
    <div class="modal fade" id="generateJsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Генерація JS для WP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <strong>Secret:</strong>
                        <code id="license-secret"></code>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm" id="generate-js-btn">🛠 Згенерувати</button>
                        <a
                            id="downloadLink"
                            class="btn btn-success btn-sm d-none"
                            href="#"
                            download>
                            🧾 Скачати
                        </a>
                    </div>
                    <input type="text" readonly class="form-control mt-2 d-none" id="js-cdn-url">
                </div>
            </div>
        </div>
    </div>
    {{-- JS для збереження змін сайту --}}
    <script>
        document.querySelectorAll('.save-license').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const row = button.closest('tr');

                const domain = row.querySelector('input[name="domain"]').value;
                const secret = row.querySelector('input[name="secret"]').value;
                const expired_at = row.querySelector('input[name="expired_at"]').value;
                const status = row.querySelector('select[name="status"]').value;

                if (!confirm('Зберегти зміни для цього сайту?')) return;

                fetch(`/licenses/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ domain, secret, expired_at, status })
                })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message || 'Збережено успішно');
                        location.reload(); // можна прибрати, якщо хочеш без reload
                    })
                    .catch(() => alert('Помилка при збереженні'));
            });
        });
    </script>
    <script>
        let currentLicenseId = null;

        const modal = document.getElementById('generateJsModal');

        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            currentLicenseId = button.getAttribute('data-id');
            const secret = button.getAttribute('data-secret');

            document.getElementById('license-secret').innerText = secret;

            // Шлях до вже згенерованого файлу
            const downloadUrl = `/generated-js/license-${currentLicenseId}.js`;

            // Оновлюємо кнопку "Скачати"
            const downloadLink = document.getElementById('downloadLink');
            downloadLink.href = downloadUrl;
            downloadLink.classList.remove('d-none');

            // Оновлюємо інпут з URL
            const cdnUrl = document.getElementById('js-cdn-url');
            cdnUrl.value = downloadUrl;
            cdnUrl.classList.remove('d-none');
        });

        // Кнопка генерації — створення нового файлу
        document.getElementById('generate-js-btn').addEventListener('click', function () {
            fetch(`/licenses/${currentLicenseId}/generate-js`)
                .then(res => res.json())
                .then(data => {
                    const downloadLink = document.getElementById('downloadLink');
                    const cdnUrl = document.getElementById('js-cdn-url');

                    downloadLink.href = data.download_url;
                    cdnUrl.value = data.download_url;
                });
        });
    </script>

@endsection
