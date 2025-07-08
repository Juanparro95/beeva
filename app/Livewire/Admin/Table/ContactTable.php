<?php

namespace App\Livewire\Admin\Table;

use App\Enums\WhatsAppTemplateRelationType;
use App\Models\Contact;
use App\Models\Source;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class ContactTable extends PowerGridComponent
{
    public string $tableName = 'contact-table-tiybqj-table';

    public bool $showFilters = false;

    public bool $deferLoading = true;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public string $loadingComponent = 'components.custom-loading';

    use WithExport;

    public array $selected = [];

    protected const CACHE_KEY_USERS = 'contacts_table_users_for_filter';

    protected const CACHE_KEY_STATUSES = 'contacts_table_statuses_for_filter';

    protected const CACHE_KEY_SOURCES = 'contacts_table_sources_for_filter';

    protected const CACHE_DURATION = 600; // 10 minutes

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
    }

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::exportable('contacts-list')
                ->striped()
                ->stripTags(true)
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),

            PowerGrid::header()
                ->showToggleColumns()
                ->withoutLoading()
                ->showSearchInput(),

            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function header(): array
    {
        $buttons = [];

        $defaultPositionClassBulkDelete   = 'static sm:absolute md:absolute lg:absolute md:top-[4.75rem] sm:top-[7.75rem] sm:left-[207px] md:left-[211px] lg:left-[182px] mb-2 sm:mb-0 me-1';
        $defaultPositionClassInitiateChat = 'static sm:absolute md:absolute lg:absolute md:top-[0.0rem] sm:top-[7.75rem] sm:left-[338px] md:left-[466px] lg:left-[310px] lg:top-[4.7rem] mb-2 sm:mb-0';

        $alternativePositionClassBulkDelete   = 'static sm:absolute md:absolute lg:absolute md:top-[2.5rem] sm:top-[2.5rem] sm:left-[210px] md:left-[211px] lg:left-[185px] mb-2 sm:mb-0 me-1';
        $alternativePositionClassInitiateChat = 'static sm:absolute md:absolute lg:absolute md:top-[2.5rem] sm:top-[2.5rem] sm:left-[320px] md:left-[320px] mb-2 sm:mb-0';

        if (checkPermission(['contact.create', 'contact.bulk_import'])) {
            $bulkDeleteClass   = $defaultPositionClassBulkDelete;
            $initiateChatClass = $defaultPositionClassInitiateChat;
        } else {
            $bulkDeleteClass   = $alternativePositionClassBulkDelete;
            $initiateChatClass = $alternativePositionClassInitiateChat;
        }

        $contactCount = Cache::remember('contacts_count', 60, function () {
            return Contact::count();
        });

        if (checkPermission('contact.delete')) {
            if ($contactCount > 0) {
                $buttons[] = Button::add('bulk-delete')
                    ->id()
                    ->slot(t('bulk_delete') . '(<span x-text="window.pgBulkActions.count(\'' . $this->tableName . '\')"></span>)')
                    ->class("inline-flex items-center justify-center px-3 py-2 text-sm border border-transparent rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-red-600 text-white hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 whitespace-nowrap $bulkDeleteClass")
                    ->dispatch('bulkDelete.' . $this->tableName, []);
            }
        }

        if ($contactCount > 0) {
            $buttons[] = Button::add('initiate-chat')
                ->id()
                ->slot(t('initiate_chat') . '(<span x-text="window.pgBulkActions.count(\'' . $this->tableName . '\')"></span>)')
                ->class("inline-flex items-center justify-center px-3 py-2 text-sm border border-transparent rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-indigo-600 text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 whitespace-nowrap $initiateChatClass")
                ->dispatch('bulkInitiateChat.' . $this->tableName, []);
        }

        return $buttons;
    }

    public function datasource(): Builder
    {
        $query = Contact::query()
            ->with([
                'user:id,firstname,lastname,profile_image_url',
                'status:id,name,color',
                'source:id,name',
            ]);

        // Filter contacts based on permissions
        if (checkPermission('contact.view')) {
            return $query; // all contacts
        } elseif (checkPermission('contact.view_own')) {
            $staffId = auth()->id();

            return $query->where('assigned_id', $staffId); // show only their assigned contacts
        }
    }

    public function relationSearch(): array
    {
        return [
            'user' => [
                'firstname',
                'lastname',
            ],
            'status' => [
                'name',
            ],
            'source' => [
                'name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('firstname', function ($contact) {

                return view('components.contacts.name-with-actions', [
                    'id'       => $contact->id,
                    'fullName' => $contact->firstname . ' ' . $contact->lastname,
                ])->render();
            })
            ->add('firstname_raw', fn ($contact) => e($contact->firstname . ' ' . $contact->lastname))
            ->add(
                'status_id',
                fn ($contact) => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    style="background-color: ' . e($contact->status->color) . '20; color: ' . e($contact->status->color) . ';">
                    ' . e($contact->status->name) . '</span>'
            )
            ->add(
                'assigned_id',
                function ($contact) {
                    if (! $contact->user) {
                        return t('not_assigned');
                    }

                    $profileImage = ! empty($contact->user->profile_image_url) && Storage::disk('public')->exists($contact->user->profile_image_url)
                        ? Storage::url($contact->user->profile_image_url)
                        : asset('img/user-placeholder.jpg');

                    $fullName = e($contact->user->firstname . ' ' . $contact->user->lastname);

                    return '<div class="relative group flex items-center cursor-pointer">
                        <a href="' . route('admin.users.details', ['userId' => $contact->assigned_id]) . '">
                            <img src="' . $profileImage . '"
                                class="w-9 h-9 rounded-full mx-3 object-cover"
                                data-tippy-content="' . $fullName . '">
                        </a>
                    </div>';
                }
            )
            ->add('initiate_chat', function ($contact) {
                return '
                <button 
                    x-data 
                    @click="$dispatch(\'initiateChat\', { id: ' . $contact->id . ' })"
                    class="inline-flex items-center text-green-500  hover:text-green-700" 
                     data-tippy-content="Initiate Chat"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 32 32" class="h-5 w-5">
                        <path d="M16.01 2.006a13.97 13.97 0 00-12.2 20.96L2 30l7.2-1.8A13.974 13.974 0 1016.01 2.006zm0 25.974c-2.08 0-4.07-.53-5.83-1.53l-.42-.24-4.28 1.07 1.1-4.16-.28-.43A11.96 11.96 0 1116.01 28zm6.41-8.94c-.34-.17-2.01-.99-2.33-1.1-.31-.11-.54-.17-.76.17-.23.34-.88 1.1-1.08 1.32-.2.23-.4.25-.75.08-.34-.17-1.44-.53-2.74-1.7a10.182 10.182 0 01-1.89-2.33c-.2-.34 0-.52.15-.69.15-.16.34-.4.5-.6.17-.2.23-.34.34-.56.12-.23.06-.43 0-.6-.07-.17-.76-1.84-1.04-2.52-.28-.68-.56-.59-.76-.6h-.65c-.22 0-.56.08-.85.4s-1.12 1.1-1.12 2.68 1.15 3.1 1.31 3.32c.17.23 2.27 3.45 5.5 4.83.77.33 1.37.53 1.83.68.77.24 1.46.2 2.01.12.61-.09 1.87-.76 2.13-1.5.27-.74.27-1.37.19-1.5-.07-.13-.3-.2-.63-.36z"/>
                    </svg>
                </button>
            ';
            })
            ->add('source_id', fn ($contact) => $contact->source?->name ?? 'N/A')
            ->add('created_at_formatted', function ($contact) {
                return '<div class="relative group">
                        <span class="cursor-default" data-tippy-content="' . format_date_time($contact->created_at) . '">'
                    . Carbon::parse($contact->created_at)->diffForHumans(['options' => Carbon::JUST_NOW]) . '</span>
                    </div>';
            })
            ->add('type', function ($contact) {
                return t($contact->type);
            });
    }

    public function columns(): array
    {
        return [
            Column::make(t('ids'), 'id')
                ->sortable()
                ->searchable(),

            Column::make(t('name'), 'firstname')
                ->bodyAttribute('class="relative"')
                ->sortable()
                ->searchableRaw("CONCAT(firstname, ' ', lastname) LIKE ?")
                ->searchable()
                ->visibleInExport(false),

            Column::make(t('name'), 'firstname_raw')
                ->hidden()
                ->visibleInExport(true),

            Column::make(t('type'), 'type')
                ->sortable()
                ->searchable(),

            Column::make(t('phone'), 'phone')
                ->sortable()
                ->searchable(),

            Column::make(t('assigned'), 'assigned_id')
                ->sortable()
                ->searchable(),

            Column::make(t('initiate_chat'), 'initiate_chat'),

            Column::make(t('status'), 'status_id')
                ->sortable()
                ->searchable(),

            Column::make(t('source'), 'source_id')
                ->sortable()
                ->searchable(),

            Column::make(t('active'), 'is_enabled')
                ->sortable()
                ->toggleable(hasPermission: true, trueLabel: 1, falseLabel: 0)
                ->bodyAttribute('flex mt-2 mx-3'),

            Column::make(t('created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            // Type filter
            Filter::select('type')
                ->dataSource(collect(WhatsAppTemplateRelationType::getRelationtype())
                    ->map(fn ($value, $key) => ['value' => $key, 'label' => ucfirst($value)])
                    ->values()
                    ->toArray())
                ->optionValue('value')
                ->optionLabel('label'),

            // Assigned User filter - cached
            Filter::select('assigned_id')
                ->dataSource(
                    Cache::remember(self::CACHE_KEY_USERS, self::CACHE_DURATION, function () {
                        return User::query()
                            ->select(['id', 'firstname', 'lastname'])
                            ->get()
                            ->map(fn ($user) => [
                                'id'   => $user->id,
                                'name' => $user->firstname . ' ' . $user->lastname,
                            ]);
                    })
                )
                ->optionValue('id')
                ->optionLabel('name'),

            // Status filter - cached
            Filter::select('status_id')
                ->dataSource(
                    Cache::remember(self::CACHE_KEY_STATUSES, self::CACHE_DURATION, function () {
                        return Status::select(['id', 'name'])->get()->toArray();
                    })
                )
                ->optionValue('id')
                ->optionLabel('name'),

            // Source filter - cached
            Filter::select('source_id')
                ->dataSource(
                    Cache::remember(self::CACHE_KEY_SOURCES, self::CACHE_DURATION, function () {
                        return Source::select(['id', 'name'])->get()->toArray();
                    })
                )
                ->optionValue('id')
                ->optionLabel('name'),

            // Created At date filter
            Filter::datepicker('created_at'),
        ];
    }

    #[On('bulkDelete.{tableName}')]
    public function bulkDelete(): void
    {
        $selectedIds = $this->checkboxValues;
        if (! empty($selectedIds) && count($selectedIds) !== 0) {
            $this->dispatch('confirmDelete', $selectedIds);
            $this->checkboxValues = [];
        } else {
            $this->notify(['type' => 'danger', 'message' => t('no_contact_selected')]);
        }
    }

    #[On('bulkInitiateChat.{tableName}')]
    public function bulkInitiateChat(): void
    {
        $selectedIds = $this->checkboxValues;
        if (! empty($selectedIds) && count($selectedIds) !== 0) {
            $this->dispatch('bulkInitiateChatSending', $selectedIds);
            $this->checkboxValues = [];
        } else {
            $this->notify(['type' => 'danger', 'message' => t('no_contact_selected')]);
        }
    }

    public function onUpdatedToggleable(string $id, string $field, string $value): void
    {
        if (checkPermission('contact.edit')) {
            $this->dispatch('refreshComponent');

            Contact::where('id', $id)->update(['is_enabled' => $value === '1' ? 1 : 0]);

            $this->notify([
                'message' => $value === '1' ? t('contact_enable_successfully') : t('contact_disabled_successfully'),
                'type'    => 'success',
            ]);
        } else {
            $this->notify([
                'message' => t('no_permission_to_perform_action'),
                'type'    => 'warning',
            ]);
        }
    }

    /**
     * Clear relevant cache keys after operations that modify contacts
     */
    protected function clearContactsCache(): void
    {
        Cache::forget('contacts_count');
    }
}
