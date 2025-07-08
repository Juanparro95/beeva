@php
  $defaultBg = 'bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500';
  $bgStyle = $announcement->background_color
      ? "background-color: {$announcement->background_color};"
      : '';
  $defaultTextColor = 'text-white';
  $textColor = $announcement->message_color ? "color: {$announcement->message_color};" : '';
  $defaultlinkColor = 'text-purple-500';
  $linktextColor = $announcement->link_text_color ? "color: {$announcement->link_text_color};" : '';
@endphp

@if ($announcement->isEnable)
  <div class="py-3 {{ !$announcement->background_color ? $defaultBg : '' }}"
    style="{{ $bgStyle }}">
    <div
      class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row justify-center items-center gap-2 sm:gap-4">
      <p class="font-medium text-center {{ !$announcement->message_color ? $defaultTextColor : '' }}"
        style="{{ $textColor }}">
        {{ $announcement->message }}
      </p>
      @if ($announcement->link)
        <a href="{{ $announcement->link }}"
          class="px-4 py-1.5 text-sm font-semibold rounded-full {{ !$announcement->link_text_color ? $defaultlinkColor : '' }} bg-white shadow-md hover:shadow-lg transition-all transform hover:scale-105"
          style="{{ $linktextColor }}">
          {{ $announcement->link_text }}
        </a>
      @endif
    </div>
  </div>
@endif
<div class="bg-gray-100 dark:bg-slate-900 p-2">
</div>

<div class="bg-gray-100 dark:bg-slate-900">
  <x-guest-layout>
    <div class="min-h-screen flex flex-col items-center bg-gray-100 dark:bg-slate-900">
      <div class="max-w-6xl p-6 mx-4 lg:mx-auto bg-white dark:bg-slate-800 rounded-lg shadow-md">
        <div class="mb-6">
          <h1 class="text-2xl font-semibold text-slate-700 dark:text-slate-300">{{ $title }}
          </h1>
          @if ($updated_at)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Last updated:
              {{ $updated_at }}</p>
          @endif
        </div>
        <hr class="mb-3">

        <div class="prose dark:prose-invert max-w-none mb-2">
          {!! $content !!}
        </div>

        <a href="{{ route('login') }}" class="">
          <x-button.primary>
            Back to Login
          </x-button.primary>
        </a>
      </div>
    </div>
  </x-guest-layout>
</div>

<div class="bg-gray-100 dark:bg-slate-900 p-2">
</div>
