<?php

namespace App\Livewire\Admin\Modals;

use Illuminate\Support\Facades\File;
use Livewire\Component;

class FileReader extends Component
{
    public $filePath;
    public $showButton = false;
    public $buttonText = '';
    public $fileName = '';
    public $content;

    public $class = 'btn-primary';

    public function mount($filePath): void
    {

        if (!str_starts_with($filePath, '/')) {
            $filePath = base_path(ltrim($filePath, '/'));
        }
        $this->filePath = $filePath;
        $this->showButton = File::exists($this->filePath);

        if ($this->showButton) {
            $this->content = File::get($this->filePath);
            $this->fileName = basename($this->filePath);
        }
    }

    public function render()
    {
        return view('admin::livewire.modals.file-reader');
    }
}
