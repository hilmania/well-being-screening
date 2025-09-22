<div class="w-full max-w-4xl mx-auto">
    <div class="bg-white shadow-lg rounded-lg p-8">
        <div class="bg-gray-50 p-6 rounded-lg">

                {{-- <!-- DEBUG: Tombol Submit akan muncul di sini -->
                <div style="background: red; color: white; padding: 10px; margin: 20px 0; text-align: center;">
                    DEBUG: Area tombol submit - jika Anda tidak melihat tombol di bawah ini, ada masalah rendering
                </div>

                <!-- Submit Button -->
                <div class="text-center mt-8 mb-8" style="background: yellow; padding: 20px;">
                    <button
                        type="submit"
                        class="inline-flex items-center px-8 py-4 border-2 border-blue-600 text-lg font-bold rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
                        wire:loading.attr="disabled"
                        style="min-height: 50px; display: block !important; visibility: visible !important; background: blue !important; color: white !important;"
                    >
                        <span wire:loading.remove style="color: white !important; display: inline !important;">
                            🚀 KIRIM SCREENING SEKARANG
                        </span>
                        <span wire:loading style="color: white !important; display: inline !important;">
                            ⏳ Memproses...
                        </span>
                    </button>
                </div>

                <div style="background: green; color: white; padding: 10px; margin: 20px 0; text-align: center;">
                    DEBUG: Tombol submit sudah ditampilkan di atas
                </div> --}}
            <h1 class="text-3xl font-bold text-gray-900">Screening Kesehatan Mental</h1>
            <p class="mt-2 text-gray-600">Silakan isi informasi dan jawab semua pertanyaan dengan jujur</p>
        </div>

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit="submit" class="space-y-8">
                <!-- Informasi Responden -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Informasi Responden</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap *
                            </label>
                            <input
                                type="text"
                                id="name"
                                wire:model="name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan nama lengkap Anda"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email *
                            </label>
                            <input
                                type="email"
                                id="email"
                                wire:model="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan email Anda"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pertanyaan Screening -->
                @if($questions && $questions->isNotEmpty())
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Pertanyaan Screening</h2>
                        <p class="text-gray-600 mb-6">Silakan jawab semua pertanyaan berikut dengan jujur</p>

                        <div class="space-y-6">
                            @foreach($questions as $question)
                                <div class="bg-white p-4 rounded-lg border">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                                        {{ $loop->iteration }}. {{ $question->question_text }}
                                    </h3>

                                    <div class="space-y-2">
                                        @foreach([
                                            1 => 'Sangat Tidak Setuju',
                                            2 => 'Tidak Setuju',
                                            3 => 'Netral',
                                            4 => 'Setuju',
                                            5 => 'Sangat Setuju'
                                        ] as $value => $label)
                                            <label class="flex items-center space-x-3 p-2 rounded hover:bg-gray-50 cursor-pointer">
                                                <input
                                                    type="radio"
                                                    name="answers[{{ $question->id }}]"
                                                    value="{{ $value }}"
                                                    wire:model="answers.{{ $question->id }}"
                                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                                >
                                                <span class="text-gray-700">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                    @error('answers.' . $question->id)
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Submit Button -->
                <div class="text-center mt-8 mb-8">
                    <button
                        type="submit"
                        class="inline-flex items-center px-8 py-4 border-2 border-blue-600 text-lg font-bold rounded-lg text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
                        wire:loading.attr="disabled"
                        style="min-height: 50px; display: block !important; visibility: visible !important; color: white !important;"
                    >
                        <span wire:loading.remove style="color: black !important; display: inline !important;">
                            🚀 KIRIM SCREENING
                        </span>
                        {{-- <span wire:loading style="color: white !important; display: inline !important;">
                            Memproses...
                        </span> --}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
