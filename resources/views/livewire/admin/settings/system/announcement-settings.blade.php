<div x-data="{
    background_color: @entangle('background_color'),
    link_text_color: @entangle('link_text_color'),
    message_color: @entangle('message_color')

}" class="mx-auto px-4 md:px-0">
  <x-slot:title>
    {{ t('announcement_setting') }}
  </x-slot:title>
  <!-- Page Heading -->
  <div class="flex justify-between">
    <div class="pb-6">
      <x-settings-heading>{{ t('system_setting') }}</x-settings-heading>
    </div>
  </div>
  <div class="flex flex-wrap lg:flex-nowrap gap-4">
    <!-- Sidebar Menu -->
    <div class="w-full lg:w-1/5">
      <x-admin-system-settings-navigation wire:ignore />
    </div>
    <!-- Main Content -->
    <div class="flex-1 space-y-5">
      <form wire:submit="save" class="space-y-6">
        <x-card class="rounded-lg">
          <x-slot:header>
            <x-settings-heading>
              {{ t('announcement_setting') }}
            </x-settings-heading>
            <x-settings-description>
              {{ t('announcement_settings_description') }}
            </x-settings-description>
          </x-slot:header>
          <x-slot:content>
            <div x-data="{ 'isEnable': @entangle('isEnable') }">
              <div>
                <span class=" text-sm font-medium text-slate-900 dark:text-slate-300">
                  {{ t('is_Enable') }}
                </span> <em class='text-slate-400 text-sm'> {{ t('announcement_toggle_note') }}
                </em>

                <div class="mt-4 sm:mt-2">
                  <button type="button" x-on:click="isEnable = !isEnable"
                    class="flex-shrink-0 group relative rounded-full inline-flex items-center justify-center h-5 w-10 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-slate-800"
                    role="switch" :aria-checked="isEnable.toString()"
                    :class="{
                        'bg-indigo-600': isEnable,
                        'bg-gray-300': !isEnable
                    }">
                    <span class="sr-only">{{ t('toggle_switch') }}</span>
                    <span aria-hidden="true"
                      class="pointer-events-none absolute w-full h-full rounded-full transition-colors ease-in-out duration-200"></span>

                    <span aria-hidden="true"
                    class="pointer-events-none absolute left-0 inline-block h-5 w-5 border border-slate-200 rounded-full bg-white shadow transform ring-0 transition-transform ease-in-out duration-200"
                    :class="isEnable ? 'translate-x-5' : 'translate-x-0'">
                      <span
                          class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                          :class="isEnable ? 'opacity-0 ease-out duration-100' :
                              'opacity-100 ease-in duration-200'">
                          <x-heroicon-m-x-mark class="h-3 w-3 text-gray-400" />
                      </span>
                      <span
                          class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                          :class="isEnable ? 'opacity-100 ease-in duration-200' :
                              'opacity-0 ease-out duration-100'">
                          <x-heroicon-m-check class="h-3 w-3 text-indigo-600" />
                      </span>
                    </span>
                  </button>
                </div>
              </div>
              <!-- Link -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-4">
                <div>
                  <x-label for="link" :value="t('link')" />
                  <x-input wire:model.defer="link" id="link" type="text" class="mt-1"
                    placeholder="{{ t('https://') }}" />
                  <x-input-error for="link" class="mt-2" />
                </div>
                <!-- Link Text -->
                <div>
                  <x-label for="link_text" :value="t('link_text')" />
                  <x-input wire:model.defer="link_text" id="link_text" type="text"
                    class="mt-1" />
                  <x-input-error for="link_text" class="mt-2" />
                </div>

              </div>
              <!-- Messages -->
              <div class="my-4" x-data="{ 'isEnable': @entangle('isEnable') }">
                <div class="flex">
                  <span x-show="isEnable" class="text-red-500 mr-1">*</span>
                  <x-label for="message" :value="t('message')" />
                </div>
                <x-textarea wire:model.defer="message" id="message" type="text"
                  wire:blur="validateMessage"></x-textarea>
                <x-input-error for="message" class="mt-2" />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                {{-- Select Background Color --}}
                <div>
                  <div class="flex items-center justify-start gap-1">
                    <x-label for="background_color"
                      class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                      {{ t('set_background_color') }}
                    </x-label>
                  </div>
                  <div class="group relative">
                    <div class="flex items-center gap-3">
                      <x-input x-model="background_color" type="text" id="status-color-text"
                        class="w-full pl-11 pr-4 py-2.5"
                        placeholder="{{ t('status_color_placeholder') }}" />
                      <div class="absolute left-3 top-1/2 -translate-y-1/2">
                        <label for="status-color-picker" class="cursor-pointer">
                          <div
                            class="w-6 h-6 rounded-md border-2 border-slate-200 shadow-sm overflow-hidden transition-transform hover:scale-105 dark:border-slate-600"
                            :style="`background-color: ${background_color}`">
                            <x-input id="status-color-picker" type="color"
                              x-model="background_color"
                              class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" />
                          </div>
                        </label>
                      </div>
                    </div>
                  </div>
                  <x-input-error for="background_color" class="mt-2" />
                </div>

                {{-- Select Message Color --}}
                <div>
                  <div class="flex item-centar justify-start gap-1">
                    <x-label for="message_color"
                      class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                      {{ t('set_link_message_color') }}
                    </x-label>
                  </div>
                  <div class="group relative">
                    <div class="flex items-center gap-3">
                      <x-input wire:model.defer="message_color" x-model="message_color"
                        type="text" id="status-color-text" class="w-full pl-11 pr-4 py-2.5"
                        placeholder="{{ t('status_color_placeholder') }}" />
                      <div class="absolute left-3 top-1/2 -translate-y-1/2">
                        <x-label for="status-color-picker" class="cursor-pointer" />
                        <div
                          class="w-6 h-6 rounded-md border-2 border-slate-200 shadow-sm overflow-hidden transition-transform hover:scale-105 dark:border-slate-600"
                          :style="`background-color: ${message_color}`">
                          <x-input id="status-color-picker" type="color"
                            wire:model.defer="message_color" x-model="message_color"
                            class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" />
                        </div>
                        </label>
                      </div>
                    </div>
                  </div>
                  <x-input-error for="message_color" class="mt-2" />
                </div>

                {{-- Select Link Text Color --}}
                <div>
                  <div class="flex item-centar justify-start gap-1">
                    <x-label for="link_text_color"
                      class="dark:text-gray-300 block text-sm font-medium text-gray-700">
                      {{ t('set_link_text_color') }}
                    </x-label>
                  </div>

                  <div class="group relative">
                    <div class="flex items-center gap-3">
                      <x-input wire:model.defer="link_text_color" x-model="link_text_color"
                        type="text" id="status-color-text" class="w-full pl-11 pr-4 py-2.5"
                        placeholder="{{ t('status_color_placeholder') }}" />
                      <div class="absolute left-3 top-1/2 -translate-y-1/2">
                        <x-label for="status-color-picker" class="cursor-pointer" />
                        <div
                          class="w-6 h-6 rounded-md border-2 border-slate-200 shadow-sm overflow-hidden transition-transform hover:scale-105 dark:border-slate-600"
                          :style="`background-color: ${link_text_color}`">
                          <x-input id="status-color-picker" type="color"
                            wire:model.defer="link_text_color" x-model="link_text_color"
                            class="opacity-0 absolute inset-0 w-full h-full cursor-pointer" />
                        </div>
                        </label>
                      </div>
                    </div>
                  </div>
                  <x-input-error for="link_text_color" class="mt-2" />
                </div>

              </div>
              <div class="mt-5">
                <x-label :value="t('preview')" />

                <div
                  class="mt-1 py-2 border-indigo-100 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"
                  :class="{ 'bg-none': background_color }"
                  :style="{ background: background_color || '' }">
                  <div
                    class="max-w-6xl mx-auto px-4 flex  flex-col sm:flex-row justify-center items-center gap-2 sm:gap-4">
                    <p class="text-md font-medium text-center"
                      :style="{ color: message_color || 'white' };">
                      {{ $message }}
                    </p>
                    @if ($link)
                      <a href="{{ $link }}"
                        class="px-4 py-1.4 text-sm font-semibold rounded-full bg-white shadow-md hover:shadow-lg transition-all transform hover:scale-105"
                        :style="{ color: link_text_color || 'rgb(168, 85, 247)' }" target="blank">
                        {{ $link_text }}
                      </a>
                    @endif
                  </div>
                </div>
              </div>

            </div>
          </x-slot:content>
          <!-- Submit Button -->
          @if (checkPermission('system_settings.edit'))
            <x-slot:footer class="bg-slate-50 dark:bg-transparent rounded-b-lg">
              <div class="flex justify-end">
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
