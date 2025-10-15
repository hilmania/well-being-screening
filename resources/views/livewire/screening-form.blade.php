<div class="w-full max-w-4xl mx-auto">
    <div class="bg-white shadow-lg rounded-lg p-8">
        <div class="bg-gray-50 p-6 rounded-lg">                {{-- <!-- DEBUG: Tombol Submit akan muncul di sini -->
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
            <p class="mt-2 text-gray-600">Hallo</p>
            <p class="mt-2 text-gray-600">Salam Sejahtera untuk Bapak/Ibu.
Terima kasih telah berusaha mencari bantuan dengan mencoba mengontak kami.
Kami sangat berharap dapat membantu Bapak/Ibu.</p>
            <p class="mt-2 text-gray-600">Untuk dapat membantu Bapak/Ibu, kami memerlukan beberapa informasi melalui pertanyaan di bawah ini. Mohon Bapak/Ibu mengisi dengan perlahan dan sesuai dengan kondisi yang dihadapi saat ini.</p>
        </div>

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Screening Berhasil!</h3>
                            <div class="mt-1 text-sm text-green-700">
                                {{ session('success') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg shadow-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Terjadi Kesalahan</h3>
                            <div class="mt-1 text-sm text-red-700">
                                {{ session('error') }}
                            </div>
                        </div>
                    </div>
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
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                                Jenis Kelamin *
                            </label>
                            <select
                                id="gender"
                                wire:model="gender"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Pilih jenis kelamin</option>
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                            </select>
                            @error('gender')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Lahir *
                            </label>
                            <input
                                type="date"
                                id="birth_date"
                                wire:model="birth_date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('birth_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Telepon *
                            </label>
                            <input
                                type="tel"
                                id="phone"
                                wire:model="phone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="081234567890 atau +6281234567890"
                                pattern="(\+?62|0)[0-9]{8,13}"
                                title="Gunakan format nomor telepon Indonesia"
                                onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 43"
                                oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
                                onpaste="setTimeout(() => { this.value = this.value.replace(/[^0-9+]/g, ''); }, 1)"
                            >
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Format: 081234567890 atau +6281234567890</p>
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                Alamat Tinggal *
                            </label>
                            <textarea
                                id="address"
                                wire:model="address"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan alamat lengkap Anda"
                            ></textarea>
                            @error('address')
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

                                    @if($question->question_type === 'likert')
                                        <!-- Likert Scale Input -->
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
                                    @else
                                        <!-- Text Input -->
                                        <div class="space-y-2">
                                            <textarea
                                                wire:model="answers.{{ $question->id }}"
                                                class="w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-vertical"
                                                rows="4"
                                                placeholder="{{ $question->placeholder ?: 'Tulis jawaban Anda di sini...' }}"
                                            ></textarea>
                                            <p class="text-sm text-gray-500">Silakan jelaskan jawaban Anda dengan detail</p>
                                        </div>
                                    @endif

                                    @error('answers.' . $question->id)
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Consent Checkbox -->
                <div class="mt-8 mb-6 p-6 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <div class="flex items-center h-5">
                            <input
                                type="checkbox"
                                id="consent"
                                wire:model="consent"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                required
                            >
                        </div>
                        <div class="flex-1">
                            <label for="consent" class="text-sm font-medium text-gray-900 cursor-pointer">
                                Persetujuan Penggunaan Data Pribadi
                            </label>
                            <p class="text-sm text-gray-600 mt-1">
                                Saya menyetujui data pribadi saya digunakan untuk keperluan screening kesehatan mental ini dan diproses sesuai dengan kebijakan privasi yang berlaku. Data akan dijaga kerahasiaan dan hanya digunakan untuk tujuan medis dan penelitian yang telah disetujui. Informasi yang saya berikan akan membantu dalam proses evaluasi dan memberikan rekomendasi yang sesuai.
                            </p>
                            <p class="text-xs text-blue-600 mt-2">
                                <strong>Catatan:</strong> Dengan mencentang kotak ini, Anda mengonfirmasi bahwa Anda telah membaca dan memahami penggunaan data pribadi Anda.
                            </p>
                        </div>
                    </div>
                    @error('consent')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="text-center mt-8 mb-8">
                    <button
                        type="submit"
                        class="px-8 py-3 bg-blue-600 text-white text-lg font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>
                            Kirim Screening
                        </span>
                        <span wire:loading>
                            Memproses...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SweetAlert Event Listener -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('showSweetAlert', (data) => {
                Swal.fire({
                    icon: data.icon || 'info',
                    title: data.title || 'Notifikasi',
                    text: data.text || 'Terima kasih sudah mengisi screening.',
                    confirmButtonText: data.confirmButtonText || 'OK',
                    confirmButtonColor: data.icon === 'success' ? '#10B981' : (data.icon === 'error' ? '#EF4444' : '#3B82F6'),
                    timer: data.timer || null,
                    timerProgressBar: data.timer ? true : false,
                    showConfirmButton: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        popup: 'rounded-lg',
                        title: 'text-lg font-semibold',
                        content: 'text-gray-600',
                        confirmButton: 'px-6 py-2 rounded-lg font-medium'
                    }
                }).then((result) => {
                    // Redirect to landing page when OK is clicked or alert is dismissed
                    if (result.isConfirmed || result.isDismissed) {
                        window.location.href = '/';
                    }
                });
            });
        });
    </script>
</div>
