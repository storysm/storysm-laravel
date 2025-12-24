<?php

namespace App\Livewire;

use App\Facades\AgeVerification;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * @property Form $form
 */
class AgeVerificationForm extends Component implements HasForms
{
    use InteractsWithForms;

    /** @var array{dob: ?string, remember_me: ?bool} */
    public array $data = [
        'dob' => null,
        'remember_me' => null,
    ];

    public int $requiredAge = 0;

    public function mount(int $requiredAge = 0): void
    {
        $this->requiredAge = $requiredAge;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('dob')
                    ->label(__('age-verification.date_of_birth'))
                    ->displayFormat('d/m/Y')
                    ->maxDate(now())
                    ->minDate('1900-01-01')
                    ->required(),
                Checkbox::make('remember_me')
                    ->label(__('age-verification.remember_me')),
            ])
            ->statePath('data');
    }

    public function verify(): void
    {
        /** @var array{dob: string, remember_me: ?bool} $data */
        $data = $this->form->getState();

        // The DatePicker already ensures 'dob' is a valid date string and not in the future.
        $age = AgeVerification::calculateAge($data['dob']);

        try {
            AgeVerification::setAge($age, $data['remember_me'] ?? false);
        } catch (\DomainException $e) { // @phpstan-ignore-line
            redirect()->route('age.not-allowed');

            return;
        }

        if ($age < $this->requiredAge) {
            redirect()->route('content.forbidden');

            return;
        }

        $this->dispatch('ageVerified');
    }

    public function render(): View
    {
        return view('livewire.age-verification-form');
    }
}
