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

        // First, validate the date components are within reasonable ranges
        $day = (int) $data['dob_day'];
        $month = (int) $data['dob_month'];
        $year = (int) $data['dob_year'];

        // Basic validation for impossible dates
        if (! checkdate($month, $day, $year)) {
            $this->addError('data.dob_day', __('Invalid date provided.'));

            return;
        }

        $dateString = sprintf('%04d-%02d-%02d', $year, $month, $day);

        try {
            $date = new \DateTime($dateString);
            $now = new \DateTime;

            if ($date > $now) {
                // Using addError on the form state path
                $this->addError('data.dob_year', __('Date of birth cannot be in the future.'));

                return;
            }
        } catch (\Exception $e) {
            $this->addError('data.dob_day', __('Invalid date provided.'));

            return;
        }

        // Calculate age using the service
        $age = AgeVerification::calculateAge($dateString);

        // Set age in session/cookie (will throw DomainException if age < 13)
        try {
            AgeVerification::setAge($age, $data['remember_me'] ?? false);
        } catch (\DomainException $e) { // @phpstan-ignore-line
            redirect()->route('age.not-allowed');

            return;
        }

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
