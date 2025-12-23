<?php

namespace App\Livewire;

use App\Facades\AgeVerification;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
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

    /** @var array<string, string> */
    public array $data = [];

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
                Grid::make(3)
                    ->schema([
                        TextInput::make('dob_day')
                            ->label(__('age-verification.day'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31)
                            ->required()
                            ->placeholder('DD'),
                        TextInput::make('dob_month')
                            ->label(__('age-verification.month'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->required()
                            ->placeholder('MM'),
                        TextInput::make('dob_year')
                            ->label(__('age-verification.year'))
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y'))
                            ->required()
                            ->placeholder('YYYY'),
                    ]),
                Checkbox::make('remember_me')
                    ->label(__('age-verification.remember_me')),
            ])
            ->statePath('data');
    }

    public function verify(): void
    {
        /** @var array{dob_day: string, dob_month: string, dob_year: string, remember_me: ?bool} $data */
        $data = $this->form->getState();

        $dateString = sprintf('%04d-%02d-%02d', $data['dob_year'], $data['dob_month'], $data['dob_day']);

        try {
            $date = new \DateTime($dateString);
            $now = new \DateTime;

            if ($date > $now) {
                // Using addError on the form state path
                $this->addError('data.dob_year', 'Date of birth cannot be in the future.');

                return;
            }
        } catch (\Exception $e) {
            $this->addError('data.dob_day', 'Invalid date provided.');

            return;
        }

        // Calculate age using the service
        $age = AgeVerification::calculateAge($dateString);

        // Store the age using the service
        AgeVerification::setAge($age, $data['remember_me'] ?? false);

        if ($age < $this->requiredAge) {
            // User is too young, redirect to forbidden page
            redirect()->route('content.forbidden');

            return;
        }

        // User is old enough, dispatch event to parent
        $this->dispatch('ageVerified');
    }

    public function render(): View
    {
        return view('livewire.age-verification-form');
    }
}
