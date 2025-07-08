<?php

namespace App\Livewire\Admin\Contact;

use App\Http\Controllers\WhatsApp\ChatController;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Contact;
use App\Models\WhatsappTemplate;
use App\Rules\PurifiedInput;
use App\Services\MergeFields;
use App\Services\PusherService;
use App\Traits\WhatsApp;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ContactList extends Component
{
    use WhatsApp, WithFileUploads;

    public Contact $contact;

    public ?int $contactId = null;

    public $confirmingDeletion = false;

    public $viewContactModal = false;

    public $showInitiateChatModal = false;

    public $contact_id = null;

    public $template_id;

    public $headerInputs = [];

    public $bodyInputs = [];

    public $file;

    public $filename;

    public $footerInputs = [];

    public $contacts = [];

    public $notes = [];

    public bool $isBulckDelete = false;

    protected $listeners = [
        'editContact'             => 'editContact',
        'confirmDelete'           => 'confirmDelete',
        'viewContact'             => 'viewContact',
        'initiateChat'            => 'initiateChat',
        'bulkInitiateChatSending' => 'bulkInitiateChatSending',
        'refreshComponent'        => '$refresh',
    ];

    public $mergeFields;

    public function mount()
    {

        if (! checkPermission(['contact.view', 'contact.view_own'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
    }

    public function createContact()
    {
        $this->redirect(route('admin.contacts.save'));
    }

    public function viewContact($contactId)
    {
        $this->contact               = Contact::with('notes')->findOrFail($contactId);
        $country                     = collect(getCountryList())->firstWhere('id', (string) $this->contact->country_id);
        $this->contact->country_name = $country['short_name'] ?? null;
        $this->notes                 = $this->contact->notes()->latest()->get();
        $this->viewContactModal      = true;
    }

    public function resetForm()
    {
        $this->dispatch('refreshComponent');
        $this->reset('template_id');
        $this->template_id = '';
    }

    public function resetModal()
    {
        $this->template_id           = '';
        $this->showInitiateChatModal = false;
        $this->dispatch('reset-campaign-select');
    }

    public function initiateChat($id)
    {
        $this->template_id = [];
        $this->resetForm();
        $this->contacts = collect([Contact::with('notes')->findOrFail($id)]);
        $this->loadMergeFields('customer');
        $this->showInitiateChatModal = true;
    }

    public function bulkInitiateChatSending($ids)
    {
        $this->resetForm();
        $this->contacts = Contact::with('notes')->findOrFail($ids);
        $this->loadMergeFields('customer');
        $this->showInitiateChatModal = true;
    }

    public function loadMergeFields($group = '')
    {
        $mergeFieldsService = app(MergeFields::class);

        $field = array_merge(
            $mergeFieldsService->getFieldsForTemplate('other-group'),
            ! empty($group) ? $mergeFieldsService->getFieldsForTemplate('contact-group') : []
        );

        $this->reset('mergeFields');

        $this->mergeFields = json_encode(array_map(fn ($value) => [
            'key'   => ucfirst($value['name']),
            'value' => $value['key'],
        ], $field));
    }

    protected function rules()
    {
        return [
            'headerInputs.*' => [new PurifiedInput(t('dynamic_input_error'))],
            'bodyInputs.*'   => [new PurifiedInput(t('dynamic_input_error'))],
            'footerInputs.*' => [new PurifiedInput(t('dynamic_input_error'))],
            'template_id'    => 'required',
            'file'           => 'nullable|file',
        ];
    }

    protected function getFileValidationRules($format)
    {
        return match ($format) {
            'IMAGE'    => ['mimes:jpeg,png', 'max:8192'],
            'DOCUMENT' => ['mimes:pdf,doc,docx,txt,ppt,pptx,xlsx,xls', 'max:102400'],
            'VIDEO'    => ['mimes:mp4,3gp', 'max:16384'],
            'AUDIO'    => ['mimes:mp3,wav,aac,ogg', 'max:16384'],
            default    => ['file', 'max:5120'],
        };
    }

    #[Computed]
    public function templates()
    {
        return WhatsappTemplate::query()->get();
    }

    public function importContact()
    {
        $this->redirect(route('admin.contacts.imports'));
    }

    public function editContact($contactId)
    {
        $this->contact = Contact::findOrFail($contactId);
        $this->redirect(route('admin.contacts.save', ['contactId' => $contactId]));
    }

    public function updatedConfirmingDeletion($value)
    {
        if (! $value) {
            $this->js('window.pgBulkActions.clearAll()');
        }
    }

    public function updatedShowInitiateChatModal($value)
    {
        if (! $value) {
            $this->js('window.pgBulkActions.clearAll()');
            $this->reset('template_id', 'headerInputs', 'bodyInputs', 'footerInputs', 'file', 'filename');
            $this->dispatch('reset-campaign-select');
        }
    }

    public function confirmDelete($contactId)
    {
        $this->contact_id = $contactId;

        $this->isBulckDelete = is_array($this->contact_id) && count($this->contact_id) !== 1 ? true : false;

        $this->confirmingDeletion = true;
    }

    protected function handleFileUpload($format)
    {

        if ($this->filename) {
            create_storage_link();
            Storage::disk('public')->delete($this->filename);
        }

        $directory = match ($format) {
            'IMAGE'    => 'init_chat/images',
            'DOCUMENT' => 'init_chat/documents',
            'VIDEO'    => 'init_chat/videos',
            'AUDIO'    => 'init_chat/audio',
            default    => 'init_chat',
        };

        $this->filename = $this->file->storeAs(
            $directory,
            $this->generateFileName(),
            'public'
        );

        return $this->filename;
    }

    protected function generateFileName()
    {
        $original = str_replace(' ', '_', $this->file->getClientOriginalName());

        return pathinfo($original, PATHINFO_FILENAME) . '_' . time() . '.' . $this->file->extension();
    }

    private function showSuccessNotification()
    {
        $this->notify([
            'type'    => 'success',
            'message' => t('chat_initiated_successfully'),
        ], true);
    }

    public function save()
    {

        $this->validate();
        try {

            $template     = WhatsappTemplate::where('template_id', $this->template_id)->firstOrFail();
            $headerFormat = $template->header_data_format ?? 'TEXT';

            // Handle file upload
            if ($this->file) {
                $filename = $this->handleFileUpload($headerFormat);
            }

            foreach ($this->contacts as $contact) {
                $response = $this->processContactChat($contact, $filename);
            }

            $this->showInitiateChatModal = true;

            if ($response['status']) {
                $this->showInitiateChatModal = false;
                $this->showSuccessNotification();
            } else {
                $this->showInitiateChatModal = false;
                $this->notify([
                    'type'    => 'danger',
                    'message' => trim($response['log_data']['response_data'], '"'),
                ], true);
            }

            return redirect()->route('admin.contacts.list');
        } catch (\Exception $e) {
            whatsapp_log('Error during template sending: ' . $e->getMessage(), 'error', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e);

            $this->notify([
                'type'    => 'danger',
                'message' => t('something_went_wrong') . ': ' . $e->getMessage(),
            ], true);
        }
    }

    protected function processContactChat(Contact $contact, $filename = null)
    {
        $template     = WhatsappTemplate::where('template_id', $this->template_id)->firstOrFail();
        $headerFormat = $template->header_data_format ?? 'TEXT';

        // File validation (only once, or move outside loop if needed)
        if ($headerFormat !== 'TEXT') {
            $this->validate([
                'file' => array_merge([$this->filename ? 'nullable' : 'required', 'file'], $this->getFileValidationRules($headerFormat)),
            ]);
        }

        $rel_data = array_merge(
            [
                'rel_type' => $contact->type,
                'rel_id'   => $contact->id,
            ],
            $template->toArray(),
            [
                'campaign_id'        => 0,
                'header_data_format' => $headerFormat,
                'filename'           => $filename                                                    ?? null,
                'header_params'      => json_encode(array_values(array_filter($this->headerInputs))) ?? null,
                'body_params'        => json_encode(array_values(array_filter($this->bodyInputs)))   ?? null,
                'footer_params'      => json_encode(array_values(array_filter($this->footerInputs))) ?? null,
                'header_message'     => $template['header_data_text']                                ?? null,
                'body_message'       => $template['body_data']                                       ?? null,
                'footer_message'     => $template['footer_data']                                     ?? null,
            ]
        );

        $response = $this->sendTemplate($contact->phone, $rel_data, 'Initiate Chat');

        if (! empty($response['status'])) {
            $header = parseText($rel_data['rel_type'], 'header', $rel_data);
            $body   = parseText($rel_data['rel_type'], 'body', $rel_data);
            $footer = parseText($rel_data['rel_type'], 'footer', $rel_data);

            $buttonHtml = '';
            if (! empty($rel_data['buttons_data']) && is_string($rel_data['buttons_data'])) {
                $buttons = json_decode($rel_data['buttons_data']);
                if (is_array($buttons) || is_object($buttons)) {
                    $buttonHtml = "<div class='flex flex-col mt-2 space-y-2'>";
                    foreach ($buttons as $button) {
                        $buttonHtml .= "<button class='bg-gray-100 text-green-500 px-3 py-2 rounded-lg flex items-center justify-center text-xs space-x-2 w-full
                        dark:bg-gray-800 dark:text-green-400'>" . e($button->text) . '</button>';
                    }
                    $buttonHtml .= '</div>';
                }
            }

            // Header media / text rendering
            $headerData     = '';
            $fileExtensions = get_meta_allowed_extension();

            if (! empty($rel_data['filename'])) {
                $extension = strtolower(pathinfo($rel_data['filename'], PATHINFO_EXTENSION));
                $fileType  = array_key_first(array_filter($fileExtensions, fn ($data) => in_array('.' . $extension, explode(', ', $data['extension']))));

                if ($rel_data['header_data_format'] == 'IMAGE' && $fileType == 'image') {
                    $headerData = "<a href='" . asset('storage/' . $rel_data['filename']) . "'>
                    <img src='" . asset('storage/' . $rel_data['filename']) . "' class='img-responsive rounded-lg object-cover'>
                    </a>";
                } elseif ($rel_data['header_data_format'] == 'VIDEO' && $fileType == 'video') {
                    $headerData = "<a href='" . asset('storage/' . $rel_data['filename']) . "'>
                    <video src='" . asset('storage/' . $rel_data['filename']) . "' class='rounded-lg object-cover' controls>
                    </a>";
                } elseif ($rel_data['header_data_format'] == 'DOCUMENT') {
                    $headerData = "<a href='" . asset('storage/' . $rel_data['filename']) . "' target='_blank' class='btn btn-secondary w-full'>" . t('document') . '</a>';
                }
            }

            if (empty($headerData) && ($rel_data['header_data_format'] == 'TEXT' || empty($rel_data['header_data_format'])) && ! empty($header)) {
                $headerData = "<span class='font-bold mb-3'>" . nl2br(decodeWhatsAppSigns(e($header))) . '</span>';
            }

            // Handle phone format
            $phone = ltrim($contact->phone, '+');

            // Get or create chat
            $chat_id = Chat::where([
                ['receiver_id', '=', $phone],
                ['wa_no', '=', get_setting('whatsapp.wm_default_phone_number')],
                ['wa_no_id', '=', get_setting('whatsapp.wm_default_phone_number_id')],
            ])->value('id');

            if (empty($chat_id)) {
                $chat_id = Chat::insertGetId([
                    'receiver_id'  => $phone,
                    'wa_no'        => get_setting('whatsapp.wm_default_phone_number'),
                    'wa_no_id'     => get_setting('whatsapp.wm_default_phone_number_id'),
                    'name'         => $contact->firstname . ' ' . $contact->lastname,
                    'last_message' => $body ?? '',
                    'time_sent'    => now(),
                    'type'         => $contact->type ?? 'guest',
                    'type_id'      => $contact->id   ?? '',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            $chatMessage = ChatMessage::create([
                'interaction_id' => $chat_id,
                'sender_id'      => get_setting('whatsapp.wm_default_phone_number'),
                'url'            => null,
                'message'        => "
                    $headerData
                    <p>" . nl2br(decodeWhatsAppSigns(e($body ?? ''))) . "</p>
                    <span class='text-gray-500 text-sm'>" . nl2br(decodeWhatsAppSigns(e($footer ?? ''))) . "</span>
                    $buttonHtml
                ",
                'status'     => 'sent',
                'time_sent'  => now()->toDateTimeString(),
                'message_id' => $response['data']->messages[0]->id ?? null,
                'staff_id'   => 0,
                'type'       => 'text',
            ]);

            $chatMessageId = $chatMessage->id;
            Chat::where('id', $chat_id)->update([
                'last_message'  => $body ?? '',
                'last_msg_time' => now(),
            ]);
            if (! empty(get_setting('pusher.app_key')) && ! empty(get_setting('pusher.app_secret')) && ! empty(get_setting('pusher.app_id')) && ! empty(get_setting('pusher.cluster'))) {
                $pusherService = new PusherService;
                $pusherService->trigger('whatsmark-chat-channel', 'whatsmark-chat-event', [
                    'chat' => ChatController::newChatMessage($chat_id, $chatMessageId),
                ]);
            }
        }

        return $response;
    }

    public function delete()
    {
        try {
            if (is_array($this->contact_id) && count($this->contact_id) !== 0) {
                $selectedIds = $this->contact_id;
                dispatch(function () use ($selectedIds) {
                    Contact::whereIn('id', $selectedIds)
                        ->chunk(100, function ($contacts) {
                            foreach ($contacts as $contact) {
                                $contact->delete();
                            }
                        });
                })->afterResponse();
                $this->contact_id = null;
                $this->js('window.pgBulkActions.clearAll()');
                $this->notify([
                    'type'    => 'success',
                    'message' => t('contacts_delete_successfully'),
                ]);
            } else {

                $contact          = Contact::findOrFail($this->contact_id);
                $this->contact_id = null;
                $contact->delete();

                $this->notify([
                    'type'    => 'success',
                    'message' => t('contact_delete_success'),
                ]);
            }

            $this->confirmingDeletion = false;
            $this->dispatch('pg:eventRefresh-contact-table-tiybqj-table');
        } catch (\Exception $e) {

            $this->notify([
                'type'    => 'danger',
                'message' => t('an_error_occured_deleting_contact'),
            ]);
        }
    }

    public function refreshTable()
    {
        $this->dispatch('pg:eventRefresh-contact-table-tiybqj-table');
    }

    public function render()
    {
        return view('livewire.admin.contact.contact-list');
    }
}
