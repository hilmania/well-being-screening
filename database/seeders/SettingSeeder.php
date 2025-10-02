<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Chatbot Settings
        Setting::set(
            'chatbot_url',
            'https://cdn.botpress.cloud/webchat/v3.2/shareable.html?configUrl=https://files.bpcontent.cloud/2025/09/22/15/20250922155518-LPZFIPF0.json',
            'url',
            'chatbot',
            'Botpress Chatbot URL',
            'URL untuk mengintegrasikan chatbot Botpress ke website'
        );

        Setting::set(
            'chatbot_enabled',
            '1',
            'boolean',
            'chatbot',
            'Enable Chatbot',
            'Aktifkan atau nonaktifkan chatbot di website'
        );

        Setting::set(
            'chatbot_title',
            'Assistant Kesehatan Mental',
            'text',
            'chatbot',
            'Chatbot Title',
            'Judul yang ditampilkan di header chatbot'
        );

        Setting::set(
            'chatbot_auto_open_delay',
            '5',
            'number',
            'chatbot',
            'Auto Open Delay (seconds)',
            'Berapa detik setelah halaman load chatbot akan menunjukkan animasi bounce'
        );
    }
}
