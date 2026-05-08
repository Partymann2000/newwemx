<?php

namespace App\Actions;

use Illuminate\Support\Facades\Validator;
use App\Models\AdminNote;

class AdminNoteActions extends Action
{
    public static function createNoteAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'admin_id' => ['required', 'exists:users,id'],
            'notable_id' => ['required'],
            'notable_type' => ['required'],
            'content' => ['required'],
            'status' => ['required', 'integer', 'between:1,6'],
            'is_private' => ['required'],
        ])->validate();

        return AdminNote::create($validatedData);
    }
}
