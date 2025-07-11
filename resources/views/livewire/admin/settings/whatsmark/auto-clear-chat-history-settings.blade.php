<div class="mx-auto px-4 md:px-0">
  <x-slot:title>
    {{ t('clear_chat_history') }}
  </x-slot:title>
  <!-- Page Heading -->
  <div class="pb-6">
    <x-settings-heading>{{ t('whatsmark_settings') }}</x-settings-heading>
  </div>

  <div class="flex flex-wrap lg:flex-nowrap gap-4">
    <!-- Sidebar Menu -->
    <div class="w-full lg:w-1/5">
      <x-admin-whatsmark-settings-navigation wire:ignore />
    </div>
    <!-- Main Content -->
    <div class="flex-1 space-y-5">
      <form wire:submit.prevent="save" x-data="{ 'enable_auto_clear_chat': @entangle('enable_auto_clear_chat') }" class="space-y-6">
        <x-card class="rounded-lg">
          <x-slot:header>
            <x-settings-heading>
              {{ t('auto_clear_chat_history') }}
            </x-settings-heading>
            <x-settings-description>
              {{ t('setup_auto_clear_chat') }}
            </x-settings-description>
          </x-slot:header>
          <x-slot:content>
            <div class="grid sm:grid-cols-2">
              <div>
                <x-label for="message" :value="t('activate_auto_clear_chat')" />
                <div class="flex justify-start items-center mt-2">
                  <button type="button"
                    x-on:click="enable_auto_clear_chat = !enable_auto_clear_chat"
                    class="flex-shrink-0 group relative rounded-full inline-flex items-center justify-center h-5 w-10 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-slate-800"
                    role="switch" :aria-checked="enable_auto_clear_chat.toString()"
                    :class="{
                        'bg-indigo-600': enable_auto_clear_chat,
                        'bg-gray-300': !enable_auto_clear_chat
                    }">
                    <span class="sr-only">{{ t('toggle_switch') }}</span>
                    <span aria-hidden="true"
                      class="pointer-events-none absolute w-full h-full rounded-full transition-colors ease-in-out duration-200"></span>
                       <span aria-hidden="true"
                    class="pointer-events-none absolute left-0 inline-block h-5 w-5 border border-slate-200 rounded-full bg-white shadow transform ring-0 transition-transform ease-in-out duration-200"
                    :class="enable_auto_clear_chat ? 'translate-x-5' : 'translate-x-0'">
                    <span
                        class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                        :class="enable_auto_clear_chat ? 'opacity-0 ease-out duration-100' :
                            'opacity-100 ease-in duration-200'">
                        <x-heroicon-m-x-mark class="h-3 w-3 text-gray-400" />
                    </span>
                    <span
                        class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                        :class="enable_auto_clear_chat ? 'opacity-100 ease-in duration-200' :
                            'opacity-0 ease-out duration-100'">
                        <x-heroicon-m-check class="h-3 w-3 text-indigo-600" />
                    </span>
                  </span>
                  </button>
                </div>
              </div>
              <div class="mt-4 sm:mt-0" x-data="{ 'enable_auto_clear_chat': @entangle('enable_auto_clear_chat') }">
                <div class="flex items-center">
                  <span x-show="enable_auto_clear_chat" x-cloak class="text-red-500 mr-1">*</span>
                  <x-label for="auto_clear_history_time" :value="t('auto_clear_history_time')" class=" mb-1" />
                </div>
                <div
                  class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-white dark:bg-gray-800">
                  <input type="number" wire:model.defer="auto_clear_history_time"
                    id="auto_clear_history_time"
                    class=" block w-full border-0 text-slate-900 sm:text-sm disabled:opacity-50 dark:bg-slate-800
                                                dark:placeholder-slate-500 dark:text-slate-200 dark:focus:placeholder-slate-600 px-3 py-2
                                                border-r border-gray-300 focus:outline-none focus:ring-0 focus:border-transparent"
                    min="1" max="365">
                  <span
                    class="px-3  border-gray-300 text-gray-600 dark:text-gray-400 ">{{ t('days') }}</span>
                </div>
                <x-input-error for="auto_clear_history_time" class="mt-1" />
              </div>
            </div>

            <!-- Chat Cleanup Results Display -->
            @if ($showCleanupResults && $cleanupResults)
              <div class="mt-4">
                <div x-data="{ show: true }" x-show="show"
                  class="rounded-md border {{ $cleanupResults['status'] === 'success' ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-700' : 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-700' }}">
                  <div class="p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        @if ($cleanupResults['status'] === 'success')
                          <svg class="h-5 w-5 text-green-400 dark:text-green-500"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd" />
                          </svg>
                        @else
                          <svg class="h-5 w-5 text-red-400 dark:text-red-500"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                              clip-rule="evenodd" />
                          </svg>
                        @endif
                      </div>
                      <div class="ml-3 flex-1">
                        <h3
                          class="text-sm font-medium {{ $cleanupResults['status'] === 'success' ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }}">
                          {{ t('chat_cleanup_completed') }}
                        </h3>
                        <div
                          class="mt-2 text-sm {{ $cleanupResults['status'] === 'success' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                          <ul class="list-disc pl-5 space-y-1">
                            @if ($cleanupResults['status'] === 'success')
                              <li>
                                {{ t('found_messages', ['count' => $cleanupResults['messagesFound'] ?? 0]) }}
                              </li>
                              <li>
                                {{ t('deleted_messages', ['count' => $cleanupResults['messagesDeleted'] ?? 0]) }}
                              </li>
                              <li>
                                {{ t('deleted_conversations', ['count' => $cleanupResults['conversationsDeleted'] ?? 0]) }}
                              </li>
                            @else
                              <li>
                                {{ $cleanupResults['errorMessage'] ?? t('error_during_cleanup') }}
                              </li>
                            @endif
                          </ul>
                        </div>
                      </div>
                      <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                          <button wire:click="dismissResults" type="button"
                            class="inline-flex rounded-md p-1.5 {{ $cleanupResults['status'] === 'success' ? 'text-green-500 hover:bg-green-100 dark:hover:bg-green-800' : 'text-red-500 hover:bg-red-100 dark:hover:bg-red-800' }} focus:outline-none">
                            <span class="sr-only">{{ t('dismiss') }}</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                              viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endif

            <div class="mt-3">
              <x-dynamic-alert type="warning">
                <b>{{ t('note') }}</b>
                {{ t('enabling') }}
                <b>{{ t('auto_clear_chat_history') }}</b>{{ t('auto_clear_note') }} <a
                  href="{{ route('admin.cron-job.settings.view') }}"
                  class="font-semibold cursor-pointer underline">{{ t('cron_job') }}</a>
                {{ t('runs') }}
              </x-dynamic-alert>
            </div>

            <div class="mt-3">
              <x-dynamic-alert type="primary">
                {{ t('cron_job_required') }} <b class="font-semibold">{{ t('cron_job') }}</b>.
                {{ t('cron_job_setup_info') }} <b
                  class="font-semibold">{{ t('documentation') }}</b>
              </x-dynamic-alert>
            </div>

          </x-slot:content>
          <!-- Submit Button -->
          @if (checkPermission('whatsmark_settings.edit'))
            <x-slot:footer class="bg-slate-50 dark:bg-transparent rounded-b-lg">
              <div class="flex items-center justify-end gap-1">
                @if ($enable_auto_clear_chat && !is_null($auto_clear_history_time))
                  <div>
                    <button type="button" wire:click="runCleanup" wire:loading.attr="disabled"
                      wire:target="runCleanup"
                      class="inline-flex items-center px-4 py-2 bg-slate-200 dark:bg-slate-700 border border-transparent rounded-md text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-slate-800"
                      onclick="return confirm('{{ t('confirm_run_cleanup') }}')">

                      <span class="flex items-center" wire:loading.remove wire:target="runCleanup">
                        <x-feathericon-trash-2 class="hidden md:inline h-4 w-4 mr-1" />
                        <span>{{ t('run_cleanup_now') }}</span>
                      </span>

                      <span class="flex items-center" wire:loading wire:target="runCleanup">
                        <svg class="animate-spin h-4 w-4 mr-1 inline-flex"
                          xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                          </path>
                        </svg>
                        <span>{{ t('processing') }}</span>
                      </span>

                    </button>
                  </div>
                @endif
                <x-button.loading-button type="submit" target="save">
                  {{ t('save_changes_button') }}
                </x-button.loading-button>
              </div>
            </x-slot:footer>
          @endif
        </x-card>
      </form>
    </div>
  </div>
</div>
