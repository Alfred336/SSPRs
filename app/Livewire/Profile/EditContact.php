<?php

namespace App\Livewire\Profile;

use App\Services\Sspr\ActiveDirectoryPasswordBroker;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
class EditContact extends Component
{
    public string $email = '';

    public string $phone = '';

    public ?string $status = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->email = (string) $user->email;
        $this->phone = (string) $user->phone;
    }

    public function save(ActiveDirectoryPasswordBroker $activeDirectory): void
    {
        $this->validate([
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();

        try {
            $activeDirectory->updateContactInfo((string) $user->ad_dn, [
                'email' => $this->email,
                'phone' => $this->phone ?: null,
            ]);

            $user->update([
                'email' => $this->email,
                'phone' => $this->phone ?: null,
            ]);

            $this->status = 'Profile updated in Active Directory.';
        } catch (Throwable $exception) {
            report($exception);

            $this->addError('email', 'Unable to update Active Directory. Confirm the service account has write permission for mail and phone attributes.');
        }
    }

    public function render()
    {
        return view('livewire.profile.edit-contact');
    }
}
