<?php

namespace App\Livewire\Proposals;

use App\Models\Project;
use App\Models\Proposal;
use App\Actions\ArrangePositions;
use Illuminate\Support\Facades\DB;
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
        DB::transaction(function(){
            $proposal = $this->project->proposals()
            ->updateOrCreate(
                ['email' => $this->email],
                ['hours' => $this->hours]
            );

             $this->arrangePositions($proposal);     
        });
        

        $this->dispatch('proposal::created');

        $this->modal = false;
    }

    public function arrangePositions(Proposal $proposal){
          $query = DB::select("
            select *, row_number() over (order by hours asc) as newPosition 
            from proposals
            where project_id = :project

        ", ['project'=> $proposal->project_id]);

        $position = collect($query)->where('id', '=', $proposal->id)->first();
        $otherProposal = collect($query)->where('position', '=', $position->newPosition)->first(); 
        

        if($otherProposal){
            $proposal->update(['position_status'=>'up']);
            Proposal::query()->where('id', '=', $otherProposal->id)->update(['position_status'=>'down']);
        }

       ArrangePositions::run($proposal->project_id); 

    }

    public function render()
    {
        return view('livewire.proposals.create');
    }
}
