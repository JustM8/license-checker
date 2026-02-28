<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateLicenseKey extends Command
{
    protected $signature = 'license:generate {domain} {--secret=default_secret}';

    protected $description = 'Генерує SHA256 хеш для домену + секретного ключа';

    public function handle()
    {
        $domain = $this->argument('domain');
        $secret = $this->option('secret');

        $hash = hash('sha256', $domain . $secret);

        $this->info("Ліцензія для: {$domain}");
        $this->info("Секрет: {$secret}");
        $this->info("Хеш: {$hash}");
    }
}
