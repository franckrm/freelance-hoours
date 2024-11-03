<?php

namespace App\Livewire\Proposals;

use App\Models\Project;
use Livewire\Component;
use Illuminate\Validation\Rule;

class Create extends Component
{
    public Project $project;
    public bool $modal = false;
    public string $email = '';
    public int $hours = 0;

    public bool $agree = false;

    protected function rules()
    {
        return [
            'email' => 'required|email',
            'hours' => 'required|numeric|gt:0',
        ];
    }

    public function save()
    {
        
        $this->validate();

        
        if(!$this->agree){
            $this->addError('agree', 'Você precisa concordar com os termos de uso');
            return;
        }

        $this->project->proposals()->updateOrCreate(
            ['email' => $this->email],
            ['hours' => $this->hours]
        );

        $this->dispatch('proposal::created');

        $this->modal = false;
    }

    public function render()
    {
        return view('livewire.proposals.create');
    }
}
