<div class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-900">Jawaban Responden</h3>

    @if($answers && $answers->count() > 0)
        @foreach($answers as $answer)
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="mb-3">
                    <h4 class="font-medium text-gray-900">
                        {{ $loop->iteration }}. {{ $answer->question->question_text }}
                    </h4>
                    @if($answer->question->question_type === 'likert')
                        <p class="text-xs text-gray-500 mt-1">
                            Skala: 1 = Tidak Pernah, 2 = Jarang, 3 = Kadang-kadang, 4 = Sering, 5 = Selalu
                        </p>
                    @endif
                </div>

                <div class="mt-3">
                    @if($answer->question->question_type === 'likert')
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-600">Jawaban:</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $answer->answer >= 4 ? 'bg-red-100 text-red-800' : ($answer->answer >= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                {{ $answer->answer }} -
                                @switch($answer->answer)
                                    @case(1) Tidak Pernah @break
                                    @case(2) Jarang @break
                                    @case(3) Kadang-kadang @break
                                    @case(4) Sering @break
                                    @case(5) Selalu @break
                                    @default {{ $answer->answer }} @break
                                @endswitch
                            </span>
                        </div>
                    @else
                        <div>
                            <span class="text-sm font-medium text-gray-600">Jawaban:</span>
                            <div class="mt-1 p-3 bg-gray-50 rounded border">
                                <p class="text-sm font-bold text-gray-900 whitespace-pre-wrap">{{ $answer->answer }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center py-8">
            <div class="text-gray-400 text-sm">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-2">Tidak ada jawaban yang ditemukan</p>
            </div>
        </div>
    @endif
</div>
