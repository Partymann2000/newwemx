<?php

use Livewire\Volt\Component;
use App\Models\AdminNote;
use Illuminate\View\View;

new class extends Component
{
    public $model;

    public $model_id;

    public $noteStatus = 1;

    public $noteContent = '';

    public $noteVisibility = 'public';

    public function mount($model, $model_id)
    {
        $this->model = $model;
        $this->model_id = $model_id;
    }

    public function createNote()
    {
        $this->validate([
            'noteContent' => 'required',
        ]);

        AdminNote::actions()->createNoteAsAdmin([
            'admin_id' => auth()->id(),
            'notable_type' => $this->model,
            'notable_id' => $this->model_id,
            'status' => $this->noteStatus,
            'content' => $this->noteContent,
            'is_private' => $this->noteVisibility == 'private',
        ]);

        $this->noteContent = '';
        $this->noteStatus = 1;
        $this->noteVisibility = 'public';

        $this->dispatch('note-created');
        $this->js("document.getElementById('closeLogModalButton').click();");
    }

    #[\Livewire\Attributes\On('note-created')]
    public function noteCreated()
    {

    }

    public function changeNoteStatus($noteId, $status)
    {
        $note = AdminNote::find($noteId);

        if($note) {
            // set status to status but ensure it's between 1 and 6
            $note->status = max(1, min(6, $status));
            $note->save();
        }
    }

    public function deleteNote($noteId)
    {
        $note = AdminNote::find($noteId);

        if($note) {
            $note->delete();
        }
    }

    public function setNoteState($noteId, $state)
    {
        $note = AdminNote::find($noteId);

        if($note) {
            // check if user owns the note
            if($note->admin_id != auth()->id()) {
                return;
            }

            $note->is_private = $state == 'private';
            $note->save();
        }
    }
}

?>

@php
    // get the notes for the model
    $notes = AdminNote::where('notable_type', $this->model)
        ->where('notable_id', $this->model_id);

    // get all public notes and private notes created by the current user
    $notes = $notes->where(function($query) {
        $query->where('is_private', false)
            ->orWhere('admin_id', auth()->id());
    })->latest()->get();
@endphp


<div class="card">
    <div class="card-header">
        <h3 class="card-title">Notes</h3>
        <div class="card-actions">
            <button type="button" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#createNoteModal">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
            </button>
        </div>
    </div>
    <div class="list-group list-group-flush list-group-hoverable">
        @if($notes->count() == 0)
            <div class="empty">
                <p class="empty-subtitle text-secondary">
                    You haven't created any notes yet.
                </p>
                <div class="empty-action">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNoteModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        Add Note
                    </button>
                </div>
            </div>
        @else
        @foreach($notes as $note)
        <div class="list-group-item">
            <div class="row align-items-center">
                @if($note->admin)
                <div class="col-auto">
                    <a href="{{ route('admin.users.edit', $note->admin->id) }}">
                        <span class="avatar avatar-1" style="background-image: url({{ $note->admin->getAvatarUrl() }})"></span>
                    </a>
                </div>
                @endif
                <div class="col">
                    <div class="d-flex items-center gap-1">
                        @if($note->admin)
                        <a href="{{ route('admin.users.edit', $note->admin->id) }}" class="text-reset d-block">
                            <span>{{ $note->admin->username }}</span>
                        </a>
                        @else
                            <span>System</span>
                        @endif
                        @if($note->status == 1)
                            <div class="badge bg-blue-lt" wire:click="changeNoteStatus({{ $note->id }}, 2)">INFO</div>
                        @elseif($note->status == 2)
                            <div class="badge bg-orange-lt" wire:click="changeNoteStatus({{ $note->id }}, 3)">TODO</div>
                        @elseif($note->status == 3)
                            <div class="badge bg-yellow-lt" wire:click="changeNoteStatus({{ $note->id }}, 4)">DOING</div>
                        @elseif($note->status == 4)
                            <div class="badge bg-green-lt" wire:click="changeNoteStatus({{ $note->id }}, 5)">DONE</div>
                        @elseif($note->status == 5)
                            <div class="badge bg-red-lt" wire:click="changeNoteStatus({{ $note->id }}, 6)">IMPORTANT</div>
                        @elseif($note->status == 6)
                            <div class="badge bg-red-lt" wire:click="changeNoteStatus({{ $note->id }}, 1)">URGENT</div>
                        @endif

                        @if($note->is_private AND $note->admin_id == auth()->id())
                            <div class="badge bg-secondary-lt" wire:click="setNoteState({{ $note->id }}, 'public')">Private</div>
                        @else
                            <div class="badge bg-secondary-lt" wire:click="setNoteState({{ $note->id }}, 'private')">Public</div>
                        @endif
                    </div>
                    <div class="d-block text-secondary mt-n1">
                        {{ $note->content }}
                    </div>
                </div>
                <div class="col-auto">
                    <a href="#" wire:click="deleteNote({{ $note->id }})" wire:confirm="Are you sure you want to delete this note?" class="list-group-item-actions">
                        <!-- Download SVG icon from http://tabler.io/icons/icon/star -->
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
        @endif
    </div>

    <div wire:ignore.self class="modal modal-blur fade" id="createNoteModal" tabindex="-1">
        <div class="modal-dialog" role="document">
            <form wire:submit="createNote">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-selectgroup">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="noteStatus" wire:model="noteStatus" value="1" class="form-selectgroup-input" checked />
                                <span class="form-selectgroup-label">INFO</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="noteStatus" wire:model="noteStatus" value="2" class="form-selectgroup-input" />
                                <span class="form-selectgroup-label">TODO</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="noteStatus" wire:model="noteStatus" value="3" class="form-selectgroup-input" />
                                <span class="form-selectgroup-label">DOING</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="noteStatus" wire:model="noteStatus" value="4" class="form-selectgroup-input" />
                                <span class="form-selectgroup-label">DONE</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="noteStatus" wire:model="noteStatus" value="5" class="form-selectgroup-input" />
                                <span class="form-selectgroup-label">IMPORTANT</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="noteStatus" wire:model="noteStatus" value="6" class="form-selectgroup-input" />
                                <span class="form-selectgroup-label">URGENT</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="example-textarea" wire:model="noteContent" placeholder="Textarea placeholder" required></textarea>
                    </div>
                    <div>
                        <div class="form-selectgroup">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="name" wire:model="noteVisibility" value="public" class="form-selectgroup-input" checked />
                                <span class="form-selectgroup-label">Public</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="name" wire:model="noteVisibility" value="private" class="form-selectgroup-input" />
                                <span class="form-selectgroup-label">Private</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="closeLogModalButton" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Note</button>
                </div>
            </div>
            </form>
        </div>
    </div>

{{--    <script>--}}
{{--        document.addEventListener('livewire:init', () => {--}}
{{--            Livewire.on('note-created', (event) => {--}}
{{--                document.getElementById('closeLogModalButton').click();--}}
{{--            });--}}
{{--        });--}}
{{--    </script>--}}
</div>
